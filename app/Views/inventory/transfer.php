<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-head">
  <div>
    <h1 class="page-title">Distribusi Stok</h1>
    <div class="page-sub">Surat jalan dari gudang ke kios</div>
  </div>
  <a href="<?= site_url('inventory/transfer/buat') ?>" class="btn btn-apple"><i class="bi bi-plus-lg me-1"></i> Buat Surat Jalan</a>
</div>

<div class="table-apple">
  <table class="table">
    <thead><tr><th>No. SJ</th><th>Tujuan</th><th>Dibuat</th><th>Status</th><th></th></tr></thead>
    <tbody>
      <?php if (empty($daftar)): ?><tr><td colspan="5" class="text-center text-muted-apple py-4">Belum ada surat jalan.</td></tr><?php endif; ?>
      <?php foreach ($daftar as $t): ?>
      <tr>
        <td class="fw-semibold"><?= esc($t['no_surat_jalan']) ?></td>
        <td><?= esc($t['ke_nama']) ?></td>
        <td><?= date('d/m H:i', strtotime($t['created_at'])) ?></td>
        <td>
          <?php
            $map = ['draft'=>'pill-gray','dikirim'=>'pill-blue','diterima'=>'pill-green','batal'=>'pill-red'];
            echo '<span class="pill-ios '.$map[$t['status']].'">'.strtoupper($t['status']).'</span>';
          ?>
        </td>
        <td class="text-end"><a href="<?= site_url('inventory/transfer/detail/'.$t['id']) ?>" class="btn-icon-soft"><i class="bi bi-chevron-right"></i></a></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?= $this->endSection() ?>
