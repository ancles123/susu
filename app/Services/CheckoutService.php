<?php

namespace App\Services;

use App\Models\ProductRecipeModel;
use App\Models\TransactionBatchLogModel;
use App\Models\TransactionDetailModel;
use App\Models\TransactionModel;
use Config\Database;

/**
 * Orkestrasi checkout: FIFO deduction -> detail -> log batch -> commit.
 * Mengembalikan ['ok'=>bool, 'message'=>..., 'trx_id'=>?, 'total'=>?, 'kembali'=>?]
 */
class CheckoutService
{
    public function process(array $cart, string $metode, float $uang, string $namaPlg, array $shift): array
    {
        $outletId = (int) $shift['outlet_id'];
        $fifo  = new FifoStockService();
        $needs = $fifo->explodeNeeds(array_map(static fn($c) => ['variant_id' => $c['variant_id'], 'qty' => $c['qty']], $cart));

        $db = Database::connect();
        $trxModel    = new TransactionModel();
        $detailModel = new TransactionDetailModel();
        $logModel    = new TransactionBatchLogModel();
        $recipeModel = new ProductRecipeModel();

        $db->transStart();

        $deductions = [];
        foreach ($needs as $rawMaterialId => $qtyNeed) {
            $hasil = $fifo->deductFifo($outletId, (int) $rawMaterialId, (float) $qtyNeed);
            if (! $hasil['ok']) {
                $db->transRollback();
                return ['ok' => false, 'message' => 'Stok tidak mencukupi: ' . $hasil['nama_bahan']];
            }
            $deductions[(int) $rawMaterialId] = $hasil;
        }

        $allRecipes = $recipeModel->getByVariantIds(array_column($cart, 'variant_id'));
        $totalOmzet = 0.0; $totalHpp = 0.0;
        $detailRows = [];
        foreach ($cart as $c) {
            $vid = $c['variant_id'];
            $hargaUnit = (float) $c['variant']['harga_jual'];
            $hppUnit = 0.0;
            $itemRecipes = [];
            foreach ($allRecipes as $r) {
                if ((int) $r['product_variant_id'] !== $vid) continue;
                $qtyPerCup = (float) $r['qty_needed'] * (1 + ((float) $r['waste_pct'] / 100));
                $hppUnit  += $qtyPerCup * $deductions[(int) $r['raw_material_id']]['rata_hpp'];
                $itemRecipes[] = ['rm' => (int) $r['raw_material_id'], 'qty_per_cup' => $qtyPerCup];
            }
            $subtotalOmzet = $hargaUnit * $c['qty'];
            $subtotalHpp   = $hppUnit * $c['qty'];
            $totalOmzet += $subtotalOmzet;
            $totalHpp   += $subtotalHpp;
            $detailRows[] = [
                'product_variant_id' => $vid, 'qty_jual' => $c['qty'],
                'harga_jual_unit' => $hargaUnit, 'hpp_unit_locked' => round($hppUnit, 4),
                'subtotal_omzet' => $subtotalOmzet, 'subtotal_laba' => round($subtotalOmzet - $subtotalHpp, 2),
                '_recipes' => $itemRecipes,
            ];
        }

        if ($metode === 'tunai' && $uang < $totalOmzet) {
            $db->transRollback();
            return ['ok' => false, 'message' => 'Uang tunai kurang dari total belanja.'];
        }
        $kembali = $metode === 'tunai' ? $uang - $totalOmzet : 0.0;

        $trxId = $trxModel->insert([
            'outlet_id' => $outletId, 'shift_id' => (int) $shift['id'],
            'user_id' => (int) session()->get('user_id'),
            'no_invoice' => $trxModel->genNoInvoice($outletId),
            'nama_pelanggan' => $namaPlg !== '' ? $namaPlg : null,
            'metode_bayar' => $metode,
            'uang_diterima' => $metode === 'tunai' ? $uang : $totalOmzet,
            'uang_kembali' => $kembali, 'total_omzet' => $totalOmzet,
            'total_hpp' => round($totalHpp, 2),
            'laba_kotor' => round($totalOmzet - $totalHpp, 2), 'status' => 'paid',
        ]);

        $pointers = [];
        foreach ($detailRows as $row) {
            $recipes = $row['_recipes'];
            unset($row['_recipes']);
            $row['transaction_id'] = $trxId;
            $detailId = $detailModel->insert($row);
            foreach ($recipes as $rc) {
                $rmId = $rc['rm'];
                $qtyDetail = $rc['qty_per_cup'] * (int) $row['qty_jual'];
                $alok = &$deductions[$rmId]['alokasi'];
                $ptr = $pointers[$rmId] ?? 0;
                $sisa = $qtyDetail;
                while ($sisa > 0.0001 && $ptr < count($alok)) {
                    $avail = $alok[$ptr]['qty'];
                    if ($avail <= 0.0001) { $ptr++; continue; }
                    $take = min($avail, $sisa);
                    $logModel->insert([
                        'transaction_detail_id' => $detailId,
                        'stock_batch_id' => $alok[$ptr]['batch_id'],
                        'qty_terpotong' => round($take, 4),
                        'hpp_batch_unit' => $alok[$ptr]['hpp'],
                    ]);
                    $alok[$ptr]['qty'] -= $take;
                    $sisa -= $take;
                    if ($alok[$ptr]['qty'] <= 0.0001) { $ptr++; }
                }
                $pointers[$rmId] = $ptr;
                unset($alok);
            }
        }

        $db->transComplete();
        if (! $db->transStatus()) {
            return ['ok' => false, 'message' => 'Transaksi gagal disimpan.'];
        }
        $trx = $trxModel->find((int) $trxId);
        return ['ok' => true, 'message' => 'Transaksi berhasil.', 'trx_id' => (int) $trxId,
                'no_invoice' => $trx['no_invoice'], 'total' => $totalOmzet, 'kembali' => $kembali];
    }
}
