<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-head">
  <div>
    <h1 class="page-title">Outlet</h1>
    <div class="page-sub">Gudang pusat & kios cabang</div>
  </div>
  <button class="btn btn-apple" data-bs-toggle="modal" data-bs-target="#modalOutlet" onclick="resetForm()"><i class="bi bi-plus-lg me-1"></i> Tambah</button>
</div>

<div class="row g-3">
  <?php foreach ($outlets as $o): ?>
  <div class="col-md-6 col-lg-4">
    <div class="card-apple fade-in">
      <div class="d-flex justify-content-between align-items-start mb-2">
        <div class="brand-logo" style="width:38px;height:38px;font-size:18px"><?= $o['is_pusat']?'🏭':'🏪' ?></div>
        <span class="pill-ios <?= $o['status_aktif']?'pill-green':'pill-gray' ?>"><?= $o['status_aktif']?'AKTIF':'NONAKTIF' ?></span>
      </div>
      <div class="fw-bold fs-5"><?= esc($o['nama_kios']) ?></div>
      <div class="text-muted-apple small mb-1"><?= esc($o['kode_cabang']) ?><?= $o['is_pusat']?' · PUSAT':'' ?></div>
      <div class="small text-muted-apple mb-3"><?= esc($o['alamat'] ?? '—') ?></div>
      <button class="btn btn-glass btn-sm w-100" onclick='editOutlet(<?= json_encode($o) ?>)'><i class="bi bi-pencil me-1"></i> Edit</button>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<div class="modal fade" id="modalOutlet" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius:24px;border:none">
      <form method="post" action="<?= site_url('owner/outlets/simpan') ?>">
        <?= csrf_field() ?>
        <div class="modal-body p-4">
          <h5 class="fw-bold text-center mb-3">Outlet</h5>
          <input type="hidden" name="id" id="oId">
          <div class="row g-3 mb-3">
            <div class="col-5"><label class="form-label">Kode</label><input name="kode_cabang" id="oKode" class="form-control" required></div>
            <div class="col-7"><label class="form-label">Nama Kios</label><input name="nama_kios" id="oNama" class="form-control" required></div>
          </div>
          <div class="mb-3"><label class="form-label">Alamat</label><textarea name="alamat" id="oAlamat" class="form-control" rows="2"></textarea></div>
          <div class="d-flex gap-4">
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" name="is_pusat" id="oPusat">
              <label class="form-check-label" for="oPusat">Gudang Pusat</label>
            </div>
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" name="status_aktif" id="oAktif" checked>
              <label class="form-check-label" for="oAktif">Aktif</label>
            </div>
          </div>
        </div>
        <div class="modal-footer border-0 pt-0">
          <button type="button" class="btn btn-glass" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-apple">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
function resetForm(){ oId.value=''; oKode.value=''; oNama.value=''; oAlamat.value=''; oPusat.checked=false; oAktif.checked=true; }
function editOutlet(o){
  oId.value=o.id; oKode.value=o.kode_cabang; oNama.value=o.nama_kios; oAlamat.value=o.alamat||'';
  oPusat.checked=o.is_pusat==1; oAktif.checked=o.status_aktif==1;
  new bootstrap.Modal(document.getElementById('modalOutlet')).show();
}
</script>
<?= $this->endSection() ?>
