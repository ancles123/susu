<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-head">
  <div>
    <h1 class="page-title">Kasir</h1>
    <div class="page-sub"><?= esc($outlet['nama_kios'] ?? '') ?> · Shift #<?= $shift['id'] ?></div>
  </div>
  <a href="<?= site_url('shift/close') ?>" class="btn btn-glass"><i class="bi bi-door-closed me-1"></i> Tutup Shift</a>
</div>

<div class="pos-layout">
  <div>
    <div class="chip-scroll mb-3" id="rasaChips">
      <div class="chip active" data-rasa="">Semua</div>
      <?php foreach ($rasas as $r): ?>
        <div class="chip" data-rasa="<?= esc($r) ?>"><?= esc($r) ?></div>
      <?php endforeach; ?>
    </div>
    <div class="row g-2 g-md-3" id="menuGrid">
      <?php foreach ($grouped as $rasa => $items): ?>
        <?php foreach ($items as $v): ?>
        <div class="col-4 col-md-3 menu-item" data-rasa="<?= esc($rasa) ?>">
          <div class="menu-card" data-id="<?= $v['id'] ?>" data-nama="<?= esc($v['nama_menu']) ?>" data-harga="<?= $v['harga_jual'] ?>">
            <div class="mc-dot" style="background:<?= esc($v['warna_tema'] ?: '#8E8E93') ?>"><?= esc($v['ukuran_cup']) ?></div>
            <div class="mc-name"><?= esc($v['varian_rasa']) ?></div>
            <div class="mc-price"><?= rupiah((float)$v['harga_jual']) ?></div>
          </div>
        </div>
        <?php endforeach; ?>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="cart-panel">
    <div class="card-apple">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <div class="section-title mb-0">Pesanan</div>
        <button class="btn btn-sm btn-glass" id="btnClear"><i class="bi bi-trash3"></i></button>
      </div>
      <input type="text" id="namaPelanggan" class="form-control mb-2" placeholder="Nama pelanggan (opsional)">
      <div id="cartList" style="max-height:38vh;overflow-y:auto"></div>
      <div class="divider"></div>
      <div class="d-flex justify-content-between align-items-center mb-3">
        <span class="fw-semibold">Total</span>
        <span class="fs-4 fw-bold" id="cartTotal">Rp 0</span>
      </div>
      <div class="mb-2">
        <div class="btn-group w-100" role="group">
          <input type="radio" class="btn-check" name="metode" id="mTunai" value="tunai" checked>
          <label class="btn btn-glass" for="mTunai"><i class="bi bi-cash me-1"></i>Tunai</label>
          <input type="radio" class="btn-check" name="metode" id="mQris" value="qris">
          <label class="btn btn-glass" for="mQris"><i class="bi bi-qr-code me-1"></i>QRIS</label>
        </div>
      </div>
      <div id="tunaiArea">
        <input type="text" id="uangDiterima" class="form-control mb-2 text-end fw-bold fs-5" placeholder="Rp 0" inputmode="numeric">
        <div class="row g-2 mb-2">
          <div class="col-3"><button class="pay-key w-100" data-n="5000">5rb</button></div>
          <div class="col-3"><button class="pay-key w-100" data-n="10000">10rb</button></div>
          <div class="col-3"><button class="pay-key w-100" data-n="20000">20rb</button></div>
          <div class="col-3"><button class="pay-key w-100" data-n="50000">50rb</button></div>
          <div class="col-6"><button class="pay-key w-100" data-n="100000">100rb</button></div>
          <div class="col-6"><button class="pay-key w-100" id="btnUangPas">Uang Pas</button></div>
        </div>
        <div class="d-flex justify-content-between mb-3">
          <span class="text-muted-apple">Kembali</span>
          <span class="fw-bold text-success" id="uangKembali">Rp 0</span>
        </div>
      </div>
      <button class="btn btn-apple w-100 btn-lg-apple" id="btnBayar" disabled>
        <i class="bi bi-check2-circle me-1"></i> Bayar
      </button>
    </div>
  </div>
</div>

<div class="modal fade" id="modalSukses" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius:24px;border:none">
      <div class="modal-body text-center p-4">
        <div style="font-size:52px">✅</div>
        <h5 class="fw-bold mt-2">Transaksi Berhasil</h5>
        <div class="text-muted-apple" id="suksesInvoice"></div>
        <div class="fs-4 fw-bold mt-2" id="suksesTotal"></div>
        <div class="text-success fw-semibold" id="suksesKembali"></div>
        <div class="d-grid gap-2 mt-3">
          <a href="#" id="btnStruk" class="btn btn-dark-apple"><i class="bi bi-printer me-1"></i> Lihat / Cetak Struk</a>
          <button class="btn btn-glass" data-bs-dismiss="modal">Transaksi Baru</button>
        </div>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/js/pos.js') ?>"></script>
<script>
POS.init({
  checkoutUrl: '<?= site_url('pos/checkout') ?>',
  strukUrl: '<?= site_url('pos/struk/') ?>',
  csrfName: '<?= csrf_token() ?>',
  csrfHash: '<?= csrf_hash() ?>'
});
</script>
<?= $this->endSection() ?>
