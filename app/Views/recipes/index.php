<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-head">
  <div>
    <h1 class="page-title">Resep / BOM</h1>
    <div class="page-sub"><?= count($variants) ?> varian menu</div>
  </div>
  <button class="btn btn-apple" data-bs-toggle="modal" data-bs-target="#modalVarian" onclick="resetForm()"><i class="bi bi-plus-lg me-1"></i> Varian Baru</button>
</div>

<div class="table-apple">
  <table class="table">
    <thead><tr><th>Menu</th><th>Rasa</th><th>Ukuran</th><th class="text-end">Harga</th><th>Status</th><th></th></tr></thead>
    <tbody>
      <?php foreach ($variants as $v): ?>
      <tr>
        <td class="fw-semibold"><?= esc($v['nama_menu']) ?></td>
        <td><span class="pill-ios" style="background:<?= esc($v['warna_tema'] ?: '#8E8E93') ?>22;color:<?= esc($v['warna_tema'] ?: '#555') ?>"><?= esc($v['varian_rasa']) ?></span></td>
        <td><?= esc($v['ukuran_cup']) ?></td>
        <td class="text-end fw-semibold"><?= rupiah((float)$v['harga_jual']) ?></td>
        <td><span class="pill-ios <?= $v['is_active']?'pill-green':'pill-gray' ?>"><?= $v['is_active']?'AKTIF':'NONAKTIF' ?></span></td>
        <td class="text-end text-nowrap">
          <a href="<?= site_url('recipes/bom/'.$v['id']) ?>" class="btn-icon-soft" title="BOM"><i class="bi bi-journal-text"></i></a>
          <a href="<?= site_url('recipes/simulasi/'.$v['id']) ?>" class="btn-icon-soft" title="Simulasi HPP"><i class="bi bi-calculator"></i></a>
          <button class="btn-icon-soft" onclick='editVarian(<?= json_encode($v) ?>)'><i class="bi bi-pencil"></i></button>
          <form method="post" action="<?= site_url('recipes/varian/toggle/'.$v['id']) ?>" class="d-inline">
            <?= csrf_field() ?>
            <button class="btn-icon-soft <?= $v['is_active']?'text-danger':'text-success' ?>" title="Toggle"><i class="bi bi-power"></i></button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<div class="modal fade" id="modalVarian" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius:24px;border:none">
      <form method="post" action="<?= site_url('recipes/varian/simpan') ?>">
        <?= csrf_field() ?>
        <div class="modal-body p-4">
          <h5 class="fw-bold text-center mb-3">Varian Menu</h5>
          <input type="hidden" name="id" id="vId">
          <div class="mb-3"><label class="form-label">Nama Menu</label><input name="nama_menu" id="vNama" class="form-control" required></div>
          <div class="row g-3 mb-3">
            <div class="col-6">
              <label class="form-label">Rasa</label>
              <select name="varian_rasa" id="vRasa" class="form-select">
                <?php foreach (['Original','Kurma','Jahe Merah','Cokelat','Stroberi','Kopi'] as $r): ?><option><?= $r ?></option><?php endforeach; ?>
              </select>
            </div>
            <div class="col-6">
              <label class="form-label">Ukuran</label>
              <select name="ukuran_cup" id="vUkuran" class="form-select">
                <option>S</option><option selected>M</option><option>L</option>
              </select>
            </div>
          </div>
          <div class="row g-3 mb-3">
            <div class="col-8"><label class="form-label">Harga Jual</label><input type="number" name="harga_jual" id="vHarga" class="form-control" required></div>
            <div class="col-4"><label class="form-label">Warna</label><input type="color" name="warna_tema" id="vWarna" class="form-control form-control-color w-100" value="#8E8E93"></div>
          </div>
          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="is_active" id="vAktif" checked>
            <label class="form-check-label" for="vAktif">Aktif dijual</label>
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
function resetForm(){ vId.value=''; vNama.value=''; vHarga.value=''; vAktif.checked=true; }
function editVarian(v){
  vId.value=v.id; vNama.value=v.nama_menu; vRasa.value=v.varian_rasa; vUkuran.value=v.ukuran_cup;
  vHarga.value=v.harga_jual; vWarna.value=v.warna_tema||'#8E8E93'; vAktif.checked=v.is_active==1;
  new bootstrap.Modal(document.getElementById('modalVarian')).show();
}
</script>
<?= $this->endSection() ?>
