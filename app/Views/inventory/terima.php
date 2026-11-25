<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-head">
  <div>
    <h1 class="page-title">Terima Barang</h1>
    <div class="page-sub">Konfirmasi penerimaan dari gudang pusat</div>
  </div>
</div>

<div class="table-apple">
  <table class="table">
    <thead><tr><th>No. SJ</th><th>Dari</th><th>Dikirim</th><th>Status</th><th></th></tr></thead>
    <tbody>
      <?php if (empty($masuk)): ?><tr><td colspan="5" class="text-center text-muted-apple py-4">Tidak ada kiriman masuk.</td></tr><?php endif; ?>
      <?php foreach ($masuk as $t): ?>
      <tr>
        <td class="fw-semibold"><?= esc($t['no_surat_jalan']) ?></td>
        <td><?= esc($t['dari_nama']) ?></td>
        <td><?= $t['dikirim_at'] ? date('d/m H:i', strtotime($t['dikirim_at'])) : '—' ?></td>
        <td>
          <?php $map = ['draft'=>'pill-gray','dikirim'=>'pill-blue','diterima'=>'pill-green','batal'=>'pill-red'];
          echo '<span class="pill-ios '.$map[$t['status']].'">'.strtoupper($t['status']).'</span>'; ?>
        </td>
        <td class="text-end">
          <?php if ($t['status']==='dikirim'): ?>
          <form method="post" action="<?= site_url('terima/'.$t['id']) ?>" class="d-inline" onsubmit="return confirm('Terima barang & masukkan ke stok kios?')">
            <?= csrf_field() ?>
            <button class="btn btn-apple btn-sm"><i class="bi bi-check-lg me-1"></i> Terima</button>
          </form>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?= $this->endSection() ?>
