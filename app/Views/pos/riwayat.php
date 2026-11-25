<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-head">
  <div>
    <h1 class="page-title">Riwayat Transaksi</h1>
    <div class="page-sub"><?= count($riwayat) ?> transaksi terakhir</div>
  </div>
  <a href="<?= site_url('pos') ?>" class="btn btn-apple"><i class="bi bi-plus-lg me-1"></i> Kasir</a>
</div>

<div class="table-apple">
  <table class="table">
    <thead><tr><th>Invoice</th><th>Waktu</th><th>Kasir</th><th>Metode</th><th class="text-end">Total</th><th>Status</th><th></th></tr></thead>
    <tbody>
      <?php if (empty($riwayat)): ?>
        <tr><td colspan="7" class="text-center text-muted-apple py-4">Belum ada transaksi.</td></tr>
      <?php endif; ?>
      <?php foreach ($riwayat as $t): ?>
      <tr>
        <td class="fw-semibold"><?= esc($t['no_invoice']) ?></td>
        <td><?= date('d/m H:i', strtotime($t['created_at'])) ?></td>
        <td><?= esc($t['nama_kasir']) ?></td>
        <td><span class="pill-ios <?= $t['metode_bayar']==='qris'?'pill-blue':'pill-green' ?>"><?= strtoupper($t['metode_bayar']) ?></span></td>
        <td class="text-end fw-semibold"><?= rupiah((float)$t['total_omzet']) ?></td>
        <td>
          <?php if ($t['status']==='void'): ?>
            <span class="pill-ios pill-red">VOID</span>
          <?php else: ?>
            <span class="pill-ios pill-green">PAID</span>
          <?php endif; ?>
        </td>
        <td class="text-end">
          <a href="<?= site_url('pos/struk/'.$t['id']) ?>" class="btn-icon-soft" title="Struk"><i class="bi bi-receipt"></i></a>
          <?php if ($t['status']==='paid'): ?>
          <button class="btn-icon-soft text-danger btn-void" data-id="<?= $t['id'] ?>" data-inv="<?= esc($t['no_invoice']) ?>" title="Void"><i class="bi bi-x-circle"></i></button>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<!-- Modal void -->
<div class="modal fade" id="modalVoid" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius:24px;border:none">
      <div class="modal-body p-4">
        <h5 class="fw-bold text-center">Void Transaksi</h5>
        <p class="text-center text-muted-apple small" id="voidInvoice"></p>
        <div class="mb-3">
          <label class="form-label">PIN Otorisasi Owner</label>
          <input type="password" id="voidPin" class="form-control text-center" inputmode="numeric" maxlength="6" placeholder="••••••">
        </div>
        <div class="mb-3">
          <label class="form-label">Alasan</label>
          <input type="text" id="voidReason" class="form-control" placeholder="Misal: salah input">
        </div>
        <div class="d-grid gap-2">
          <button class="btn btn-dark-apple" id="btnDoVoid">Void & Kembalikan Stok</button>
          <button class="btn btn-glass" data-bs-dismiss="modal">Batal</button>
        </div>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
let voidId = null;
const modalVoid = new bootstrap.Modal(document.getElementById('modalVoid'));
document.querySelectorAll('.btn-void').forEach(b=>{
  b.addEventListener('click', ()=>{
    voidId = b.dataset.id;
    document.getElementById('voidInvoice').textContent = b.dataset.inv;
    document.getElementById('voidPin').value = '';
    document.getElementById('voidReason').value = '';
    modalVoid.show();
  });
});
document.getElementById('btnDoVoid').addEventListener('click', async ()=>{
  const fd = new FormData();
  fd.append('pin', document.getElementById('voidPin').value);
  fd.append('reason', document.getElementById('voidReason').value);
  fd.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
  const res = await fetch('<?= site_url('pos/void/') ?>' + voidId, {method:'POST', body:fd});
  const data = await res.json();
  alert(data.message);
  if (data.ok) location.reload();
});
</script>
<?= $this->endSection() ?>
