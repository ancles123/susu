<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-head">
  <div>
    <h1 class="page-title">Catat Waste</h1>
    <div class="page-sub">Stok dipotong dari batch & dicatat sebagai kerugian HPP</div>
  </div>
</div>

<div class="row justify-content-center">
  <div class="col-lg-6">
    <div class="card-apple fade-in">
      <form method="post" action="<?= site_url('waste/simpan') ?>">
        <?= csrf_field() ?>
        <div class="mb-3">
          <label class="form-label">Bahan</label>
          <select id="selBahan" class="form-select" required>
            <option value="">— Pilih bahan —</option>
            <?php foreach ($bahan as $b): ?><option value="<?= $b['id'] ?>"><?= esc($b['nama_item']) ?> (<?= $b['satuan_kecil'] ?>)</option><?php endforeach; ?>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label">Batch (FIFO urut kedaluwarsa)</label>
          <select name="stock_batch_id" id="selBatch" class="form-select" required disabled>
            <option value="">— Pilih bahan dulu —</option>
          </select>
          <div class="small text-muted-apple mt-1" id="batchInfo"></div>
        </div>
        <div class="row g-3 mb-3">
          <div class="col-6">
            <label class="form-label">Qty Rusak</label>
            <input type="number" step="0.01" name="qty_rusak" class="form-control" required>
          </div>
          <div class="col-6">
            <label class="form-label">Jenis</label>
            <select name="jenis_rusak" class="form-select">
              <option value="basi">Basi / Kedaluwarsa</option>
              <option value="bocor">Bocor</option>
              <option value="penyok">Penyok</option>
              <option value="lainnya">Lainnya</option>
            </select>
          </div>
        </div>
        <div class="mb-4">
          <label class="form-label">Keterangan</label>
          <input type="text" name="keterangan" class="form-control" placeholder="Opsional">
        </div>
        <button type="submit" class="btn btn-dark-apple w-100 btn-lg-apple">Simpan Waste</button>
      </form>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
const selBahan = document.getElementById('selBahan');
const selBatch = document.getElementById('selBatch');
selBahan.addEventListener('change', async ()=>{
  selBatch.innerHTML = '<option value="">Memuat...</option>';
  selBatch.disabled = true;
  const res = await fetch('<?= site_url('waste/batches/') ?>' + selBahan.value);
  const data = await res.json();
  selBatch.innerHTML = '';
  if (!data.data.length) {
    selBatch.innerHTML = '<option value="">Tidak ada batch berstok</option>';
    return;
  }
  data.data.forEach(b=>{
    const opt = document.createElement('option');
    opt.value = b.id;
    opt.textContent = `${b.no_batch} — sisa ${parseFloat(b.qty_sisa)} (ED ${b.expired_date ?? '-'})`;
    selBatch.appendChild(opt);
  });
  selBatch.disabled = false;
});
</script>
<?= $this->endSection() ?>
