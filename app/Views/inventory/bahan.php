<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-head">
  <div>
    <h1 class="page-title">Master Bahan</h1>
    <div class="page-sub"><?= count($bahan) ?> bahan terdaftar</div>
  </div>
  <button class="btn btn-apple" data-bs-toggle="modal" data-bs-target="#modalBahan" onclick="resetForm()"><i class="bi bi-plus-lg me-1"></i> Tambah</button>
</div>

<div class="table-apple">
  <table class="table">
    <thead><tr><th>Kode</th><th>Nama</th><th>Kategori</th><th>Satuan</th><th class="text-end">Stok Min</th><th></th></tr></thead>
    <tbody>
      <?php foreach ($bahan as $b): ?>
      <tr>
        <td class="text-muted-apple"><?= esc($b['kode_bahan']) ?></td>
        <td class="fw-semibold"><?= esc($b['nama_item']) ?></td>
        <td><span class="pill-ios pill-gray"><?= esc($b['kategori']) ?></span></td>
        <td><?= esc($b['satuan_kecil']) ?></td>
        <td class="text-end"><?= qty_fmt((float)$b['stok_minimum'], $b['satuan_kecil']) ?></td>
        <td class="text-end">
          <button class="btn-icon-soft" onclick='editBahan(<?= json_encode($b) ?>)'><i class="bi bi-pencil"></i></button>
          <form method="post" action="<?= site_url('inventory/bahan/hapus/'.$b['id']) ?>" class="d-inline" onsubmit="return confirm('Hapus bahan ini?')">
            <?= csrf_field() ?>
            <button class="btn-icon-soft text-danger"><i class="bi bi-trash3"></i></button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<div class="modal fade" id="modalBahan" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius:24px;border:none">
      <form method="post" action="<?= site_url('inventory/bahan/simpan') ?>">
        <?= csrf_field() ?>
        <div class="modal-body p-4">
          <h5 class="fw-bold text-center mb-3">Bahan Baku</h5>
          <input type="hidden" name="id" id="fId">
          <div class="row g-3">
            <div class="col-5"><label class="form-label">Kode</label><input name="kode_bahan" id="fKode" class="form-control" required></div>
            <div class="col-7"><label class="form-label">Nama</label><input name="nama_item" id="fNama" class="form-control" required></div>
            <div class="col-6">
              <label class="form-label">Kategori</label>
              <select name="kategori" id="fKategori" class="form-select">
                <?php foreach (['Susu','Perasa','Pemanis','Cup','Lid','Plastik_Pendukung'] as $k): ?><option><?= $k ?></option><?php endforeach; ?>
              </select>
            </div>
            <div class="col-6">
              <label class="form-label">Satuan</label>
              <select name="satuan_kecil" id="fSatuan" class="form-select">
                <option value="ml">ml</option><option value="gr">gr</option><option value="pcs" selected>pcs</option>
              </select>
            </div>
            <div class="col-12"><label class="form-label">Stok Minimum</label><input type="number" step="0.01" name="stok_minimum" id="fMin" class="form-control" value="0"></div>
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
function resetForm(){ fId.value=''; fKode.value=''; fNama.value=''; fMin.value=0; }
function editBahan(b){ fId.value=b.id; fKode.value=b.kode_bahan; fNama.value=b.nama_item; fKategori.value=b.kategori; fSatuan.value=b.satuan_kecil; fMin.value=b.stok_minimum; new bootstrap.Modal(document.getElementById('modalBahan')).show(); }
</script>
<?= $this->endSection() ?>
