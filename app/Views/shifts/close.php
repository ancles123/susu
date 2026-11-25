<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="row justify-content-center">
  <div class="col-lg-5">
    <div class="card-apple fade-in py-4">
      <div class="text-center">
        <div style="font-size:52px">🌙</div>
        <h2 class="page-title mt-2">Tutup Shift</h2>
        <p class="page-sub mb-4">Blind closing — hitung uang fisik di laci, sistem menghitung selisihnya <em>setelah</em> Anda input.</p>
      </div>
      <form method="post" action="<?= site_url('shift/close') ?>" onsubmit="return confirm('Yakin tutup shift? Data tidak bisa diubah.')">
        <?= csrf_field() ?>
        <div class="mb-3">
          <label class="form-label">Uang Fisik di Laci (Rp)</label>
          <input type="text" name="kas_fisik_closing" class="form-control text-center fs-4 fw-bold rupiah-input" inputmode="numeric" placeholder="0" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Kas Keluar Selama Shift (Rp) <span class="text-muted-apple fw-normal">— misal beli es batu</span></label>
          <input type="text" name="kas_keluar" class="form-control text-center rupiah-input" inputmode="numeric" placeholder="0" value="0">
        </div>
        <div class="mb-4">
          <label class="form-label">Keterangan Kas Keluar</label>
          <input type="text" name="ket_kas_keluar" class="form-control" placeholder="Opsional">
        </div>
        <div class="alert-ios alert-info-ios small">
          <i class="bi bi-eye-slash me-1"></i> Kas sistem <strong>tidak ditampilkan</strong> demi mencegah kecurangan. Selisih dihitung otomatis setelah Anda submit.
        </div>
        <button type="submit" class="btn btn-dark-apple w-100 btn-lg-apple">Tutup Shift Sekarang</button>
      </form>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
document.querySelectorAll('.rupiah-input').forEach(inp=>{
  inp.addEventListener('input', ()=>{
    const v = parseInt(inp.value.replace(/\D/g,'')) || 0;
    inp.value = v ? v.toLocaleString('id-ID') : '';
  });
});
</script>
<?= $this->endSection() ?>
