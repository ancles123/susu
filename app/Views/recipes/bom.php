<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-head">
  <div>
    <h1 class="page-title">BOM: <?= esc($variant['nama_menu']) ?></h1>
    <div class="page-sub">Komposisi bahan per gelas · harga <?= rupiah((float)$variant['harga_jual']) ?></div>
  </div>
  <a href="<?= site_url('recipes/simulasi/'.$variant['id']) ?>" class="btn btn-glass"><i class="bi bi-calculator me-1"></i> Simulasi HPP</a>
</div>

<div class="row justify-content-center">
  <div class="col-lg-7">
    <div class="card-apple fade-in">
      <form method="post" action="<?= site_url('recipes/bom/simpan/'.$variant['id']) ?>">
        <?= csrf_field() ?>
        <div id="bomRows">
          <?php if (empty($recipes)): ?>
            <p class="text-muted-apple small">Belum ada komposisi. Tambah baris di bawah.</p>
          <?php endif; ?>
          <?php foreach ($recipes as $i => $r): ?>
          <div class="row g-2 mb-2 bom-row">
            <div class="col-6">
              <select name="rows[<?= $i ?>][raw_material_id]" class="form-select" required>
                <?php foreach ($bahan as $b): ?>
                  <option value="<?= $b['id'] ?>" <?= $b['id']==$r['raw_material_id']?'selected':'' ?>><?= esc($b['nama_item']) ?> (<?= $b['satuan_kecil'] ?>)</option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-3"><input type="number" step="0.01" name="rows[<?= $i ?>][qty_needed]" class="form-control" value="<?= $r['qty_needed'] ?>" placeholder="Qty" required></div>
            <div class="col-2"><input type="number" step="0.01" name="rows[<?= $i ?>][waste_pct]" class="form-control" value="<?= $r['waste_pct'] ?>" placeholder="Susut%"></div>
            <div class="col-1"><button type="button" class="btn-icon-soft text-danger btn-del"><i class="bi bi-x-lg"></i></button></div>
          </div>
          <?php endforeach; ?>
        </div>
        <button type="button" class="btn btn-glass btn-sm" id="btnAdd"><i class="bi bi-plus-lg me-1"></i> Tambah Bahan</button>
        <div class="divider"></div>
        <button type="submit" class="btn btn-apple w-100 btn-lg-apple">Simpan Resep</button>
      </form>
    </div>
  </div>
</div>

<template id="rowTpl">
  <div class="row g-2 mb-2 bom-row">
    <div class="col-6">
      <select name="rows[__i__][raw_material_id]" class="form-select" required>
        <?php foreach ($bahan as $b): ?><option value="<?= $b['id'] ?>"><?= esc($b['nama_item']) ?> (<?= $b['satuan_kecil'] ?>)</option><?php endforeach; ?>
      </select>
    </div>
    <div class="col-3"><input type="number" step="0.01" name="rows[__i__][qty_needed]" class="form-control" placeholder="Qty" required></div>
    <div class="col-2"><input type="number" step="0.01" name="rows[__i__][waste_pct]" class="form-control" placeholder="Susut%" value="0"></div>
    <div class="col-1"><button type="button" class="btn-icon-soft text-danger btn-del"><i class="bi bi-x-lg"></i></button></div>
  </div>
</template>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
let idx = <?= count($recipes) ?>;
const wrap = document.getElementById('bomRows');
document.getElementById('btnAdd').addEventListener('click', ()=>{
  wrap.insertAdjacentHTML('beforeend', document.getElementById('rowTpl').innerHTML.replaceAll('__i__', idx++));
});
wrap.addEventListener('click', e=>{ const d=e.target.closest('.btn-del'); if(d) d.closest('.bom-row').remove(); });
</script>
<?= $this->endSection() ?>
