<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-head">
  <div>
    <h1 class="page-title">Pengguna</h1>
    <div class="page-sub"><?= count($users) ?> akun</div>
  </div>
  <button class="btn btn-apple" data-bs-toggle="modal" data-bs-target="#modalUser" onclick="resetForm()"><i class="bi bi-plus-lg me-1"></i> Tambah</button>
</div>

<div class="table-apple">
  <table class="table">
    <thead><tr><th>Nama</th><th>Username</th><th>Role</th><th>Outlet</th><th>Status</th><th></th></tr></thead>
    <tbody>
      <?php foreach ($users as $u): ?>
      <tr>
        <td class="fw-semibold"><?= esc($u['nama']) ?></td>
        <td class="text-muted-apple"><?= esc($u['username']) ?></td>
        <td><span class="pill-ios <?= ['owner'=>'pill-blue','admin'=>'pill-orange','kasir'=>'pill-green'][$u['role']] ?>"><?= ucfirst($u['role']) ?></span></td>
        <td><?= esc($u['nama_kios'] ?? 'Semua') ?></td>
        <td><span class="pill-ios <?= $u['is_active']?'pill-green':'pill-gray' ?>"><?= $u['is_active']?'AKTIF':'NONAKTIF' ?></span></td>
        <td class="text-end">
          <button class="btn-icon-soft" onclick='editUser(<?= json_encode($u) ?>)'><i class="bi bi-pencil"></i></button>
          <form method="post" action="<?= site_url('owner/users/toggle/'.$u['id']) ?>" class="d-inline">
            <?= csrf_field() ?>
            <button class="btn-icon-soft <?= $u['is_active']?'text-danger':'text-success' ?>"><i class="bi bi-power"></i></button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<div class="modal fade" id="modalUser" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius:24px;border:none">
      <form method="post" action="<?= site_url('owner/users/simpan') ?>">
        <?= csrf_field() ?>
        <div class="modal-body p-4">
          <h5 class="fw-bold text-center mb-3">Pengguna</h5>
          <input type="hidden" name="id" id="uId">
          <div class="mb-3"><label class="form-label">Nama Lengkap</label><input name="nama" id="uNama" class="form-control" required></div>
          <div class="row g-3 mb-3">
            <div class="col-6"><label class="form-label">Username</label><input name="username" id="uUsername" class="form-control" required></div>
            <div class="col-6">
              <label class="form-label">Role</label>
              <select name="role" id="uRole" class="form-select">
                <option value="kasir">Kasir</option><option value="admin">Admin</option><option value="owner">Owner</option>
              </select>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Outlet</label>
            <select name="outlet_id" id="uOutlet" class="form-select">
              <option value="">— Pusat / Semua —</option>
              <?php foreach ($outlets as $o): ?><option value="<?= $o['id'] ?>"><?= esc($o['nama_kios']) ?></option><?php endforeach; ?>
            </select>
          </div>
          <div class="row g-3 mb-3">
            <div class="col-6"><label class="form-label">Password <span class="text-muted-apple fw-normal">(kosong = tetap)</span></label><input type="password" name="password" class="form-control"></div>
            <div class="col-6"><label class="form-label">PIN Otorisasi (owner)</label><input type="password" name="pin_otorisasi" class="form-control" maxlength="6" inputmode="numeric"></div>
          </div>
          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="is_active" id="uAktif" checked>
            <label class="form-check-label" for="uAktif">Aktif</label>
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
function resetForm(){ uId.value=''; uNama.value=''; uUsername.value=''; uAktif.checked=true; }
function editUser(u){
  uId.value=u.id; uNama.value=u.nama; uUsername.value=u.username; uRole.value=u.role;
  uOutlet.value=u.outlet_id||''; uAktif.checked=u.is_active==1;
  new bootstrap.Modal(document.getElementById('modalUser')).show();
}
</script>
<?= $this->endSection() ?>
