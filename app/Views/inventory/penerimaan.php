<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-head">
  <div>
    <h1 class="page-title">Penerimaan Bahan</h1>
    <div class="page-sub">Catat batch stok baru masuk gudang</div>
  </div>
</div>

<div class="row justify-content-center">
  <div class="col-lg-6">
    <div class="card-apple fade-in">
      <form method="post" action="<?= site_url('inventory/penerimaan') ?>">
        <?= csrf_field() ?>
        <div class="mb-3">
          <label class="form-label">Bahan Baku</label>
          <select name="raw_material_id" class="form-select" required>
            <option value="">— Pilih bahan —</option>
            <?php foreach ($bahan as $b): ?>
              <option value="<?= $b['id'] ?>"><?= esc($b['nama_item']) ?> (<?= $b['satuan_kecil'] ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="row g-3 mb-3">
          <div class="col-6">
            <label class="form-label">Qty Masuk</label>
            <input type="number" step="0.01" name="qty" class="form-control" required>
          </div>
          <div class="col-6">
            <label class="form-label">HPP / satuan (Rp)</label>
            <input type="number" step="0.01" name="hpp_satuan" class="form-control" required>
          </div>
        </div>
        <div class="mb-4">
          <label class="form-label">Tanggal Kedaluwarsa <span class="text-muted-apple fw-normal">(opsional)</span></label>
          <input type="date" name="expired_date" class="form-control">
        </div>
        <button type="submit" class="btn btn-apple w-100 btn-lg-apple">Simpan Batch</button>
      </form>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
