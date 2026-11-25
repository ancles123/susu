<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductRecipeModel extends Model
{
    protected $table         = 'product_recipes';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['product_variant_id', 'raw_material_id', 'qty_needed', 'waste_pct'];
    protected $useTimestamps = true;

    public function getByVariant(int $variantId): array
    {
        return $this->select('product_recipes.*, raw_materials.nama_item, raw_materials.satuan_kecil, raw_materials.kategori')
            ->join('raw_materials', 'raw_materials.id = product_recipes.raw_material_id')
            ->where('product_recipes.product_variant_id', $variantId)
            ->findAll();
    }

    public function getByVariantIds(array $variantIds): array
    {
        if (empty($variantIds)) return [];
        return $this->whereIn('product_variant_id', $variantIds)->findAll();
    }

    /** Ganti seluruh resep varian (dipakai form BOM) */
    public function replaceRecipes(int $variantId, array $rows): void
    {
        $this->where('product_variant_id', $variantId)->delete();
        foreach ($rows as $r) {
            if (empty($r['raw_material_id']) || (float) $r['qty_needed'] <= 0) continue;
            $this->insert([
                'product_variant_id' => $variantId,
                'raw_material_id'    => (int) $r['raw_material_id'],
                'qty_needed'         => (float) $r['qty_needed'],
                'waste_pct'          => (float) ($r['waste_pct'] ?? 0),
            ]);
        }
    }

    /** Simulasi HPP dari HPP tertimbang stok aktif (FR-06) */
    public function simulasiHpp(int $variantId, int $outletId): array
    {
        $batchModel = new StockBatchModel();
        $items = $this->getByVariant($variantId);
        $total = 0;
        foreach ($items as &$it) {
            $hppSatuan = $batchModel->getHppTertimbang($outletId, (int) $it['raw_material_id']);
            $qtyEfektif = (float) $it['qty_needed'] * (1 + ((float) $it['waste_pct'] / 100));
            $it['hpp_satuan_aktif'] = $hppSatuan;
            $it['subtotal_hpp'] = $qtyEfektif * $hppSatuan;
            $total += $it['subtotal_hpp'];
        }
        return ['items' => $items, 'total_hpp' => $total];
    }
}
