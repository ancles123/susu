<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-head">
  <div>
    <h1 class="page-title">Waste / Barang Rusak</h1>
    <div class="page-sub">Total kerugian: <strong class="text-danger"><?= rupiah($totalRugi) ?></strong></div>
  </div>
  <a href="<?= site_url('waste/buat') ?>" class="btn btn-apple"><i class="bi bi-plus-lg me-1"></i> Catat Waste</a>
</div>

<div class="table-apple">
  <table class="table">
    <thead><tr><th>Tanggal</th><th>Bahan</th><th>Batch</th><th class="text-end">Qty</th><th>Jenis</th><th class="text-end">Rugi</th><th>Oleh</th></tr></thead>
    <tbody>
      <?php if (empty($daftar)): ?><tr><td colspan="7" class="text-center text-muted-apple py-4">Belum ada catatan waste. 🎉</td></tr><?php endif; ?>
      <?php foreach ($daftar as $w): ?>
      <tr>
        <td><?= date('d/m H:i', strtotime($w['created_at'])) ?></td>
        <td class="fw-semibold"><?= esc($w['nama_item']) ?></td>
        <td class="text-muted-apple small"><?= esc($w['no_batch']) ?></td>
        <td class="text-end"><?= qty_fmt((float)$w['qty_rusak'], $w['satuan_kecil']) ?></td>
        <td><span class="pill-ios pill-red"><?= ucfirst($w['jenis_rusak']) ?></span></td>
        <td class="text-end fw-semibold text-danger"><?= rupiah((float)$w['nominal_rugi']) ?></td>
        <td><?= esc($w['nama_user']) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?= $this->endSection() ?>
