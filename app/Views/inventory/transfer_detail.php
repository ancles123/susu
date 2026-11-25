<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-head">
  <div>
    <h1 class="page-title"><?= esc($t['no_surat_jalan']) ?></h1>
    <div class="page-sub"><?= esc($t['dari_nama']) ?> → <?= esc($t['ke_nama']) ?></div>
  </div>
  <?php
    $map = ['draft'=>'pill-gray','dikirim'=>'pill-blue','diterima'=>'pill-green','batal'=>'pill-red'];
    echo '<span class="pill-ios '.$map[$t['status']].' fs-6">'.strtoupper($t['status']).'</span>';
  ?>
</div>

<div class="row g-3">
  <div class="col-lg-7">
    <div class="table-apple">
      <table class="table">
        <thead><tr><th>Bahan</th><th class="text-end">Qty Kirim</th><th class="text-end">Qty Terima</th></tr></thead>
        <tbody>
          <?php foreach ($t['items'] as $i): ?>
          <tr>
            <td class="fw-semibold"><?= esc($i['nama_item']) ?></td>
            <td class="text-end"><?= qty_fmt((float)$i['qty_kirim'], $i['satuan_kecil']) ?></td>
            <td class="text-end"><?= $i['qty_terima'] !== null ? qty_fmt((float)$i['qty_terima'], $i['satuan_kecil']) : '—' ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <div class="col-lg-5">
    <div class="card-apple">
      <div class="section-title">Aksi</div>
      <?php if ($t['status']==='draft'): ?>
        <form method="post" action="<?= site_url('inventory/transfer/kirim/'.$t['id']) ?>" onsubmit="return confirm('Kirim barang? Stok pusat akan dipotong FIFO.')">
          <?= csrf_field() ?>
          <button class="btn btn-apple w-100 mb-2"><i class="bi bi-truck me-1"></i> Kirim Sekarang (potong stok FIFO)</button>
        </form>
      <?php endif; ?>
      <?php if (in_array($t['status'], ['draft','dikirim'])): ?>
        <form method="post" action="<?= site_url('inventory/transfer/batal/'.$t['id']) ?>" onsubmit="return confirm('Batalkan surat jalan?')">
          <?= csrf_field() ?>
          <button class="btn btn-glass w-100 text-danger">Batalkan</button>
        </form>
      <?php endif; ?>
      <?php if ($t['status']==='diterima'): ?>
        <div class="alert-ios alert-success-ios mb-0">Diterima kios pada <?= date('d/m/Y H:i', strtotime($t['diterima_at'])) ?>.</div>
      <?php endif; ?>
      <div class="divider"></div>
      <div class="small text-muted-apple">
        Dibuat oleh <?= esc($t['dibuat_oleh_nama']) ?><br>
        <?= date('d M Y H:i', strtotime($t['created_at'])) ?>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
