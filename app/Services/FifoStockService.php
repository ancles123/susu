<?php

namespace App\Services;

use App\Models\ProductRecipeModel;
use App\Models\StockBatchModel;
use Config\Database;

/**
 * Core Engine FIFO (FR-11)
 * Memecah keranjang -> kebutuhan bahan -> potong stock_batches secara FIFO
 * dengan FOR UPDATE di dalam transaksi database (anti race-condition).
 */
class FifoStockService
{
    /**
     * Recipe explosion: keranjang -> kebutuhan bahan baku teragregasi.
     * $cart = [['variant_id'=>1,'qty'=>2], ...]
     * return [raw_material_id => qty_dibutuhkan (termasuk waste_pct)]
     */
    public function explodeNeeds(array $cart): array
    {
        $recipeModel = new ProductRecipeModel();
        $variantIds  = array_column($cart, 'variant_id');
        $recipes     = $recipeModel->getByVariantIds($variantIds);

        $qtyPerVariant = [];
        foreach ($cart as $item) {
            $qtyPerVariant[(int) $item['variant_id']] = (int) $item['qty'];
        }

        $needs = [];
        foreach ($recipes as $r) {
            $vid = (int) $r['product_variant_id'];
            if (! isset($qtyPerVariant[$vid])) continue;
            $qty = (float) $r['qty_needed'] * (1 + ((float) $r['waste_pct'] / 100));
            $qty *= $qtyPerVariant[$vid];
            $needs[(int) $r['raw_material_id']] = ($needs[(int) $r['raw_material_id']] ?? 0) + $qty;
        }
        return $needs;
    }

    /**
     * Potong satu bahan secara FIFO. HARUS dipanggil di dalam transaksi aktif.
     * return ['ok'=>bool, 'alokasi'=>[['batch_id','qty','hpp']...], 'rata_hpp'=>float, 'nama_bahan'=>string]
     */
    public function deductFifo(int $outletId, int $rawMaterialId, float $qtyNeeded): array
    {
        $db         = Database::connect();
        $batchModel = new StockBatchModel();

        $batches = $batchModel->lockFifoQueue($outletId, $rawMaterialId);

        $alokasi  = [];
        $totalAmbil = 0.0;
        $totalNilai = 0.0;
        $sisaNeed = $qtyNeeded;

        foreach ($batches as $b) {
            if ($sisaNeed <= 0) break;
            $ambil = min((float) $b['qty_sisa'], $sisaNeed);
            if ($ambil <= 0) continue;

            $db->table('stock_batches')
                ->where('id', (int) $b['id'])
                ->update(['qty_sisa' => (float) $b['qty_sisa'] - $ambil]);

            $alokasi[] = ['batch_id' => (int) $b['id'], 'qty' => $ambil, 'hpp' => (float) $b['hpp_satuan']];
            $totalAmbil += $ambil;
            $totalNilai += $ambil * (float) $b['hpp_satuan'];
            $sisaNeed   -= $ambil;
        }

        if ($sisaNeed > 0.0001) {
            $bahan = $db->table('raw_materials')->where('id', $rawMaterialId)->get()->getRowArray();
            return ['ok' => false, 'nama_bahan' => $bahan['nama_item'] ?? "Bahan #{$rawMaterialId}", 'kurang' => $sisaNeed];
        }

        return [
            'ok'       => true,
            'alokasi'  => $alokasi,
            'rata_hpp' => $totalAmbil > 0 ? $totalNilai / $totalAmbil : 0.0,
            'total_nilai' => $totalNilai,
        ];
    }

    /** Kembalikan stok ke batch asal (dipakai saat void / batal transfer) */
    public function restoreToBatches(array $logs): void
    {
        $db = Database::connect();
        foreach ($logs as $log) {
            $db->query(
                'UPDATE stock_batches SET qty_sisa = qty_sisa + ? WHERE id = ?',
                [(float) $log['qty_terpotong'], (int) $log['stock_batch_id']]
            );
        }
    }
}
