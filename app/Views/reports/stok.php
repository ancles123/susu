<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-head">
  <div>
    <h1 class="page-title">Laporan Stok</h1>
    <div class="page-sub">Seluruh batch beserta sisa & HPP</div>
  </div>
</div>

<?php if (session('role')==='owner'): ?>
<div class="card-apple mb-3">
  <form method="get" class="row g-2 align-items-end">
    <div class="col-8 col-md-4">
      <label class="form-label">Outlet</label>
      <select name="outlet_id" class="form-select">
        <?php foreach ($outlets as $o): ?><option value="<?= $o['id'] ?>" <?= $outletId==$o['id']?'selected':'' ?>><?= esc($o['nama_kios']) ?></option><?php endforeach; ?>
      </select>
    </div>
    <div class="col-4 col-md-2"><button class="btn btn-apple w-100">Lihat</button></div>
  </form>
</div>
<?php endif; ?>

<div class="table-apple">
  <table class="table">
    <thead><tr><th>Batch</th><th>Bahan</th><th>Kategori</th><th class="text-end">Awal</th><th class="text-end">Sisa</th><th class="text-end">HPP</th><th>ED</th></tr></thead>
    <tbody>
      <?php foreach ($batches as $b): ?>
      <tr>
        <td class="text-muted-apple small"><?= esc($b['no_batch']) ?></td>
        <td class="fw-semibold"><?= esc($b['nama_item']) ?></td>
        <td><span class="pill-ios pill-gray"><?= esc($b['kategori']) ?></span></td>
        <td class="text-end"><?= qty_fmt((float)$b['qty_awal'], $b['satuan_kecil']) ?></td>
        <td class="text-end fw-semibold"><?= qty_fmt((float)$b['qty_sisa'], $b['satuan_kecil']) ?></td>
        <td class="text-end"><?= rupiah((float)$b['hpp_satuan']) ?></td>
        <td>
          <?php if ($b['expired_date']): ?>
            <span class="pill-ios <?= strtotime($b['expired_date'])<=strtotime('+2 days')?'pill-red':'pill-gray' ?>"><?= tgl_indo($b['expired_date']) ?></span>
          <?php else: ?>—<?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?= $this->endSection() ?>
