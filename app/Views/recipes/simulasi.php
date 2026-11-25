<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-head">
  <div>
    <h1 class="page-title">Simulasi HPP</h1>
    <div class="page-sub"><?= esc($variant['nama_menu']) ?> · dari HPP tertimbang stok aktif</div>
  </div>
  <a href="<?= site_url('recipes/bom/'.$variant['id']) ?>" class="btn btn-glass"><i class="bi bi-pencil me-1"></i> Edit BOM</a>
</div>

<div class="row g-3">
  <div class="col-lg-7">
    <div class="table-apple">
      <table class="table">
        <thead><tr><th>Bahan</th><th class="text-end">Kebutuhan</th><th class="text-end">HPP Satuan</th><th class="text-end">Subtotal</th></tr></thead>
        <tbody>
          <?php foreach ($hasil['items'] as $it): ?>
          <tr>
            <td class="fw-semibold"><?= esc($it['nama_item']) ?></td>
            <td class="text-end"><?= qty_fmt((float)$it['qty_needed'] * (1 + (float)$it['waste_pct']/100), $it['satuan_kecil']) ?></td>
            <td class="text-end"><?= rupiah($it['hpp_satuan_aktif']) ?></td>
            <td class="text-end fw-semibold"><?= rupiah($it['subtotal_hpp']) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <div class="col-lg-5">
    <div class="card-stat mb-3">
      <div class="cs-label">HPP per Gelas</div>
      <div class="cs-value"><?= rupiah($hasil['total_hpp']) ?></div>
    </div>
    <div class="card-stat mb-3">
      <div class="cs-label">Harga Jual</div>
      <div class="cs-value"><?= rupiah((float)$variant['harga_jual']) ?></div>
    </div>
    <?php $laba = (float)$variant['harga_jual'] - $hasil['total_hpp']; ?>
    <div class="card-stat" style="background:<?= $laba>0?'rgba(52,199,89,.1)':'rgba(255,59,48,.1)' ?>">
      <div class="cs-label">Estimasi Laba Kotor</div>
      <div class="cs-value" style="color:<?= $laba>0?'#1e7e34':'#c62828' ?>"><?= rupiah($laba) ?></div>
      <div class="cs-sub"><?= $variant['harga_jual']>0 ? round($laba/$variant['harga_jual']*100,1) : 0 ?>% margin</div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
