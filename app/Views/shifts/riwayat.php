<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-head">
  <div>
    <h1 class="page-title">Riwayat Shift</h1>
    <div class="page-sub">Rekap buka/tutup shift & selisih kas</div>
  </div>
</div>

<div class="table-apple">
  <table class="table">
    <thead><tr><th>#</th><th>Kasir</th><th>Buka</th><th>Tutup</th><th class="text-end">Modal</th><th class="text-end">Tunai</th><th class="text-end">QRIS</th><th class="text-end">Selisih</th><th>Status</th></tr></thead>
    <tbody>
      <?php if (empty($riwayat)): ?>
        <tr><td colspan="9" class="text-center text-muted-apple py-4">Belum ada riwayat shift.</td></tr>
      <?php endif; ?>
      <?php foreach ($riwayat as $s): ?>
      <tr>
        <td><?= $s['id'] ?></td>
        <td class="fw-semibold"><?= esc($s['nama_kasir']) ?></td>
        <td><?= date('d/m H:i', strtotime($s['waktu_buka'])) ?></td>
        <td><?= $s['waktu_tutup'] ? date('d/m H:i', strtotime($s['waktu_tutup'])) : '—' ?></td>
        <td class="text-end"><?= rupiah((float)$s['modal_awal']) ?></td>
        <td class="text-end"><?= rupiah((float)$s['penjualan_tunai']) ?></td>
        <td class="text-end"><?= rupiah((float)$s['penjualan_qris']) ?></td>
        <td class="text-end">
          <?php if ($s['status']==='closed'): ?>
            <span class="pill-ios <?= abs((float)$s['selisih_kas']) < 1 ? 'pill-green' : ((float)$s['selisih_kas'] > 0 ? 'pill-blue' : 'pill-red') ?>">
              <?= ($s['selisih_kas'] >= 0 ? '+' : '') . rupiah((float)$s['selisih_kas']) ?>
            </span>
          <?php else: ?>—<?php endif; ?>
        </td>
        <td><span class="pill-ios <?= $s['status']==='open'?'pill-green':'pill-gray' ?>"><?= strtoupper($s['status']) ?></span></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?= $this->endSection() ?>
