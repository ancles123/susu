<?php

namespace App\Controllers;

use App\Models\ProductRecipeModel;
use App\Models\ProductVariantModel;
use App\Models\RawMaterialModel;

class RecipeController extends BaseController
{
    public function index()
    {
        return view('recipes/index', [
            'title' => 'Resep / BOM',
            'variants' => (new ProductVariantModel())->orderBy('varian_rasa')->orderBy('ukuran_cup')->findAll(),
        ]);
    }

    public function varianSimpan()
    {
        $model = new ProductVariantModel();
        $id = (int) $this->request->getPost('id');
        $data = [
            'nama_menu' => trim((string) $this->request->getPost('nama_menu')),
            'varian_rasa' => $this->request->getPost('varian_rasa'),
            'ukuran_cup' => $this->request->getPost('ukuran_cup'),
            'harga_jual' => (float) $this->request->getPost('harga_jual'),
            'warna_tema' => $this->request->getPost('warna_tema') ?: null,
            'is_active' => $this->request->getPost('is_active') ? 1 : 0,
        ];
        if ($id > 0) { $data['id'] = $id; }
        if (! $model->save($data)) {
            return redirect()->back()->withInput()->with('error', implode(' ', $model->errors()));
        }
        return redirect()->to(site_url('recipes'))->with('success', 'Varian disimpan.');
    }

    public function varianToggle($id)
    {
        $model = new ProductVariantModel();
        $v = $model->find((int) $id);
        if ($v) {
            $model->update((int) $id, ['is_active' => $v['is_active'] ? 0 : 1]);
        }
        return redirect()->back()->with('success', 'Status varian diubah.');
    }

    /** Editor BOM per varian */
    public function bom($variantId)
    {
        $variant = (new ProductVariantModel())->find((int) $variantId);
        if (! $variant) {
            return redirect()->to(site_url('recipes'))->with('error', 'Varian tidak ditemukan.');
        }
        return view('recipes/bom', [
            'title' => 'BOM: ' . $variant['nama_menu'],
            'variant' => $variant,
            'recipes' => (new ProductRecipeModel())->getByVariant((int) $variantId),
            'bahan' => (new RawMaterialModel())->orderBy('kategori')->orderBy('nama_item')->findAll(),
        ]);
    }

    public function bomSimpan($variantId)
    {
        $rows = $this->request->getPost('rows') ?? [];
        (new ProductRecipeModel())->replaceRecipes((int) $variantId, $rows);
        return redirect()->to(site_url('recipes/bom/' . $variantId))->with('success', 'Resep disimpan.');
    }

    /** FR-06: simulasi HPP tertimbang dari stok aktif outlet */
    public function simulasi($variantId)
    {
        $outletId = $this->currentOutletId();
        $hasil = (new ProductRecipeModel())->simulasiHpp((int) $variantId, $outletId);
        $variant = (new ProductVariantModel())->find((int) $variantId);
        return view('recipes/simulasi', [
            'title' => 'Simulasi HPP: ' . ($variant['nama_menu'] ?? ''),
            'variant' => $variant, 'hasil' => $hasil, 'outletId' => $outletId,
        ]);
    }
}
