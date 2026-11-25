<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-head">
  <div>
    <h1 class="page-title">Laporan Laba Rugi</h1>
    <div class="page-sub"><?= tgl_indo($dari) ?> — <?= tgl_indo($sampai) ?></div>
  </div>
  <a href="<?= site_url('reports/export-csv?dari='.$dari.'&sampai='.$sampai) ?>" class="btn btn-glass"><i class="bi bi-download me-1"></i> Export CSV</a>
</div>

<!-- Filter -->
<div class="card-apple mb-3">
  <form method="get" class="row g-2 align-items-end">
    <div class="col-6 col-md-3"><label class="form-label">Dari</label><input type="date" name="dari" class="form-control" value="<?= $dari ?>"></div>
    <div class="col-6 col-md-3"><label class="form-label">Sampai</label><input type="date" name="sampai" class="form-control" value="<?= $sampai ?>"></div>
    <?php if (session('role')==='owner'): ?>
    <div class="col-6 col-md-3">
      <label class="form-label">Outlet</label>
      <select name="outlet_id" class="form-select">
        <option value="">Semua Outlet</option>
        <?php foreach ($outlets as $o): ?><option value="<?= $o['id'] ?>" <?= $outletId==$o['id']?'selected':'' ?>><?= esc($o['nama_kios']) ?></option><?php endforeach; ?>
      </select>
    </div>
    <?php endif; ?>
    <div class="col-6 col-md-3"><button class="btn btn-apple w-100">Terapkan</button></div>
  </form>
</div>

<!-- Per outlet -->
<div class="table-apple mb-3">
  <table class="table">
    <thead><tr><th>Outlet</th><th class="text-end">Trx</th><th class="text-end">Omzet</th><th class="text-end">HPP</th><th class="text-end">Laba Kotor</th><th class="text-end">Margin</th></tr></thead>
    <tbody>
      <?php
        $tO=0;$tH=0;$tL=0;
        foreach ($perOutlet as $r):
          $tO+=$r['omzet']; $tH+=$r['hpp']; $tL+=$r['laba_kotor'];
      ?>
      <tr>
        <td class="fw-semibold"><?= esc($r['nama_kios']) ?></td>
        <td class="text-end"><?= $r['jml_trx'] ?></td>
        <td class="text-end"><?= rupiah((float)$r['omzet']) ?></td>
        <td class="text-end text-muted-apple"><?= rupiah((float)$r['hpp']) ?></td>
        <td class="text-end fw-semibold" style="color:#1e7e34"><?= rupiah((float)$r['laba_kotor']) ?></td>
        <td class="text-end"><?= $r['omzet']>0?round($r['laba_kotor']/$r['omzet']*100,1):0 ?>%</td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($perOutlet)): ?><tr><td colspan="6" class="text-center text-muted-apple py-4">Tidak ada data pada rentang ini.</td></tr><?php endif; ?>
    </tbody>
    <?php if (!empty($perOutlet)): ?>
    <tfoot>
      <tr class="fw-bold" style="background:rgba(0,0,0,.03)">
        <td>TOTAL</td><td></td>
        <td class="text-end"><?= rupiah($tO) ?></td>
        <td class="text-end"><?= rupiah($tH) ?></td>
        <td class="text-end" style="color:#1e7e34"><?= rupiah($tL) ?></td>
        <td class="text-end"><?= $tO>0?round($tL/$tO*100,1):0 ?>%</td>
      </tr>
    </tfoot>
    <?php endif; ?>
  </table>
</div>

<div class="row g-3">
  <div class="col-lg-6">
    <div class="card-apple h-100">
      <div class="section-title">Laba per Varian Rasa</div>
      <?php foreach ($perRasa as $r): $pct = $tL>0 ? ($r['laba']/$tL*100) : 0; ?>
      <div class="mb-2">
        <div class="d-flex justify-content-between small mb-1">
          <span class="fw-semibold"><?= esc($r['label']) ?> <span class="text-muted-apple">(<?= $r['qty'] ?> gelas)</span></span>
          <span class="fw-semibold"><?= rupiah((float)$r['laba']) ?></span>
        </div>
        <div style="height:8px;border-radius:99px;background:rgba(0,0,0,.06)">
          <div style="height:8px;border-radius:99px;background:#0071e3;width:<?= min(100,$pct) ?>%"></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="card-apple h-100">
      <div class="section-title">Laba per Ukuran Cup</div>
      <div style="height:220px"><canvas id="chartUkuran"></canvas></div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
const dataUkuran = <?= json_encode(array_map(static fn($r)=>['l'=>$r['label'],'v'=>(float)$r['laba']], $perUkuran)) ?>;
new Chart(document.getElementById('chartUkuran'), {
  type:'doughnut',
  data:{labels:dataUkuran.map(d=>d.l), datasets:[{data:dataUkuran.map(d=>d.v), backgroundColor:['#0071e3','#34c759','#ff9500'], borderWidth:0}]},
  options:{responsive:true, maintainAspectRatio:false, cutout:'65%', plugins:{legend:{position:'bottom'}}}
});
</script>
<?= $this->endSection() ?>
