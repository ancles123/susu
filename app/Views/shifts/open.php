<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="row justify-content-center">
  <div class="col-lg-5">
    <div class="card-apple fade-in text-center py-4">
      <div style="font-size:52px">☀️</div>
      <h2 class="page-title mt-2">Buka Shift</h2>
      <p class="page-sub mb-4">Masukkan modal uang tunai awal laci kasir.</p>
      <form method="post" action="<?= site_url('shift/open') ?>">
        <?= csrf_field() ?>
        <div class="mb-4 text-start">
          <label class="form-label">Modal Awal (Rp)</label>
          <input type="text" name="modal_awal" id="modalAwal" class="form-control text-center fs-4 fw-bold" inputmode="numeric" placeholder="0" required>
        </div>
        <div class="row g-2 mb-4">
          <?php foreach ([50000,100000,200000,500000] as $v): ?>
          <div class="col-6"><button type="button" class="pay-key w-100" data-n="<?= $v ?>"><?= number_format($v/1000,0) ?>rb</button></div>
          <?php endforeach; ?>
        </div>
        <button type="submit" class="btn btn-apple w-100 btn-lg-apple">Mulai Shift</button>
      </form>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
const inp = document.getElementById('modalAwal');
let val = 0;
inp.addEventListener('input', ()=>{
  val = parseInt(inp.value.replace(/\D/g,'')) || 0;
  inp.value = val ? val.toLocaleString('id-ID') : '';
});
document.querySelectorAll('.pay-key').forEach(k=>{
  k.addEventListener('click', ()=>{
    val = parseInt(k.dataset.n);
    inp.value = val.toLocaleString('id-ID');
  });
});
</script>
<?= $this->endSection() ?>
