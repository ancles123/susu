<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-head">
  <div>
    <h1 class="page-title">Buat Surat Jalan</h1>
    <div class="page-sub">Kirim stok dari gudang pusat ke kios</div>
  </div>
</div>

<div class="row justify-content-center">
  <div class="col-lg-7">
    <div class="card-apple fade-in">
      <form method="post" action="<?= site_url('inventory/transfer/simpan') ?>">
        <?= csrf_field() ?>
        <div class="mb-3">
          <label class="form-label">Kios Tujuan</label>
          <select name="ke_outlet_id" class="form-select" required>
            <option value="">— Pilih kios —</option>
            <?php foreach ($kios as $k): ?><option value="<?= $k['id'] ?>"><?= esc($k['nama_kios']) ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label">Item Dikirim</label>
          <div id="itemRows"></div>
          <button type="button" class="btn btn-glass btn-sm mt-2" id="btnAddRow"><i class="bi bi-plus-lg me-1"></i> Tambah Item</button>
        </div>
        <button type="submit" class="btn btn-apple w-100 btn-lg-apple mt-2">Simpan Draft Surat Jalan</button>
      </form>
    </div>
  </div>
</div>

<template id="rowTpl">
  <div class="row g-2 mb-2 item-row">
    <div class="col-7">
      <select name="items[__i__][raw_material_id]" class="form-select" required>
        <option value="">— Bahan —</option>
        <?php foreach ($bahan as $b): ?><option value="<?= $b['id'] ?>"><?= esc($b['nama_item']) ?> (<?= $b['satuan_kecil'] ?>)</option><?php endforeach; ?>
      </select>
    </div>
    <div class="col-4"><input type="number" step="0.01" name="items[__i__][qty]" class="form-control" placeholder="Qty" required></div>
    <div class="col-1"><button type="button" class="btn-icon-soft text-danger btn-del"><i class="bi bi-x-lg"></i></button></div>
  </div>
</template>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
let idx = 0;
const rows = document.getElementById('itemRows');
function addRow(){
  const html = document.getElementById('rowTpl').innerHTML.replaceAll('__i__', idx++);
  rows.insertAdjacentHTML('beforeend', html);
}
document.getElementById('btnAddRow').addEventListener('click', addRow);
rows.addEventListener('click', e=>{ const d=e.target.closest('.btn-del'); if(d) d.closest('.item-row').remove(); });
addRow();
</script>
<?= $this->endSection() ?>
