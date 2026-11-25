<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-head">
  <div>
    <h1 class="page-title">Stok Bahan</h1>
    <div class="page-sub"><?= esc($outlet['nama_kios'] ?? '') ?></div>
  </div>
  <a href="<?= site_url('inventory/penerimaan') ?>" class="btn btn-apple"><i class="bi bi-plus-lg me-1"></i> Penerimaan</a>
</div>

<div class="table-apple">
  <table class="table">
    <thead><tr><th>Kode</th><th>Bahan</th><th>Kategori</th><th class="text-end">Stok</th><th class="text-end">Min.</th><th>Status</th></tr></thead>
    <tbody>
      <?php foreach ($bahan as $b): ?>
      <tr>
        <td class="text-muted-apple"><?= esc($b['kode_bahan']) ?></td>
        <td class="fw-semibold"><?= esc($b['nama_item']) ?></td>
        <td><span class="pill-ios pill-gray"><?= esc($b['kategori']) ?></span></td>
        <td class="text-end fw-semibold"><?= qty_fmt((float)$b['stok_total'], $b['satuan_kecil']) ?></td>
        <td class="text-end text-muted-apple"><?= qty_fmt((float)$b['stok_minimum'], $b['satuan_kecil']) ?></td>
        <td>
          <?php if ($b['stok_total'] <= 0): ?><span class="pill-ios pill-red">HABIS</span>
          <?php elseif ($b['stok_total'] <= $b['stok_minimum']): ?><span class="pill-ios pill-orange">MENIPIS</span>
          <?php else: ?><span class="pill-ios pill-green">AMAN</span><?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?= $this->endSection() ?>
