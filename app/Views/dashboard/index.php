<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-head fade-in">
  <div>
    <h1 class="page-title">Halo, <?= esc(explode(' ', session('nama'))[0]) ?> 👋</h1>
    <div class="page-sub"><?= tgl_indo(date('Y-m-d')) ?> · <?= esc($outlets[array_search($outletId, array_column($outlets,'id'))]['nama_kios'] ?? '') ?></div>
  </div>
  <?php if ($shift): ?>
    <span class="pill-ios pill-green"><i class="bi bi-circle-fill" style="font-size:8px"></i> Shift Aktif sejak <?= date('H:i', strtotime($shift['waktu_buka'])) ?></span>
  <?php elseif (session('role') === 'kasir'): ?>
    <a href="<?= site_url('shift/open') ?>" class="btn btn-apple">Buka Shift</a>
  <?php endif; ?>
</div>

<!-- Stat cards -->
<div class="row g-3 mb-3">
  <div class="col-6 col-lg-3">
    <div class="card-stat fade-in">
      <div class="cs-icon" style="background:rgba(0,113,227,.12);color:#0071e3"><i class="bi bi-cash-stack"></i></div>
      <div class="cs-label">Omzet Hari Ini</div>
      <div class="cs-value"><?= rupiah($stats['omzet']) ?></div>
      <div class="cs-sub"><?= $stats['trx'] ?> transaksi</div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="card-stat fade-in">
      <div class="cs-icon" style="background:rgba(52,199,89,.12);color:#34c759"><i class="bi bi-graph-up-arrow"></i></div>
      <div class="cs-label">Laba Kotor</div>
      <div class="cs-value"><?= rupiah($stats['laba']) ?></div>
      <div class="cs-sub"><?= $stats['omzet']>0 ? round($stats['laba']/$stats['omzet']*100) : 0 ?>% margin</div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="card-stat fade-in">
      <div class="cs-icon" style="background:rgba(255,149,0,.12);color:#ff9500"><i class="bi bi-truck"></i></div>
      <div class="cs-label">Barang Masuk</div>
      <div class="cs-value"><?= $transferIn ?></div>
      <div class="cs-sub">menunggu diterima</div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="card-stat fade-in">
      <div class="cs-icon" style="background:rgba(255,59,48,.12);color:#ff3b30"><i class="bi bi-trash3"></i></div>
      <div class="cs-label">Rugi Waste Hari Ini</div>
      <div class="cs-value"><?= rupiah($rugiHariIni) ?></div>
    </div>
  </div>
</div>

<!-- Grafik -->
<div class="card-apple mb-3 fade-in">
  <div class="section-title">Omzet 7 Hari Terakhir</div>
  <div style="height:240px"><canvas id="chartOmzet"></canvas></div>
</div>

<div class="row g-3">
  <!-- FR-08: kedaluwarsa H+2 -->
  <div class="col-lg-7">
    <div class="card-apple fade-in h-100">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <div class="section-title mb-0">⚠️ Segera Kedaluwarsa (H+2)</div>
        <span class="pill-ios pill-red"><?= count($expiring) ?> batch</span>
      </div>
      <?php if (empty($expiring)): ?>
        <p class="text-muted-apple small mb-0">Tidak ada batch yang mendekati kedaluwarsa. 👍</p>
      <?php else: ?>
        <?php foreach (array_slice($expiring, 0, 5) as $e): ?>
        <div class="d-flex justify-content-between align-items-center py-2" style="border-bottom:1px solid rgba(0,0,0,.04)">
          <div>
            <div class="fw-semibold small"><?= esc($e['nama_item']) ?></div>
            <div class="text-muted-apple" style="font-size:12px"><?= esc($e['no_batch']) ?> · ED <?= tgl_indo($e['expired_date']) ?></div>
          </div>
          <span class="pill-ios <?= strtotime($e['expired_date']) <= time() ? 'pill-red' : 'pill-orange' ?>"><?= qty_fmt((float)$e['qty_sisa'], $e['satuan_kecil']) ?></span>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
  <!-- Stok menipis -->
  <div class="col-lg-5">
    <div class="card-apple fade-in h-100">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <div class="section-title mb-0">📉 Stok Menipis</div>
        <span class="pill-ios pill-orange"><?= count($lowStock) ?></span>
      </div>
      <?php if (empty($lowStock)): ?>
        <p class="text-muted-apple small mb-0">Semua stok aman. 👍</p>
      <?php else: ?>
        <?php foreach (array_slice($lowStock, 0, 5) as $l): ?>
        <div class="d-flex justify-content-between align-items-center py-2" style="border-bottom:1px solid rgba(0,0,0,.04)">
          <div class="fw-semibold small"><?= esc($l['nama_item']) ?></div>
          <span class="pill-ios <?= $l['stok_total']<=0?'pill-red':'pill-orange' ?>"><?= qty_fmt((float)$l['stok_total'], $l['satuan_kecil']) ?></span>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
fetch('<?= site_url('dashboard/data') ?>').then(r=>r.json()).then(d=>{
  new Chart(document.getElementById('chartOmzet'), {
    type:'line',
    data:{labels:d.labels, datasets:[
      {label:'Omzet', data:d.omzet, borderColor:'#0071e3', backgroundColor:'rgba(0,113,227,.1)', fill:true, tension:.4, borderWidth:2.5, pointRadius:3},
      {label:'Laba', data:d.laba, borderColor:'#34c759', backgroundColor:'rgba(52,199,89,.08)', fill:true, tension:.4, borderWidth:2.5, pointRadius:3}
    ]},
    options:{responsive:true, maintainAspectRatio:false, plugins:{legend:{position:'top', align:'end'}},
      scales:{y:{beginAtZero:true, ticks:{callback:v=>'Rp'+(v/1000)+'k'}}, x:{grid:{display:false}}}}
  });
});
</script>
<?= $this->endSection() ?>
