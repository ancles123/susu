<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-head">
  <div>
    <h1 class="page-title">Struk</h1>
    <div class="page-sub"><?= esc($trx['no_invoice']) ?> · <?= date('d/m/Y H:i', strtotime($trx['created_at'])) ?></div>
  </div>
  <a href="<?= site_url('pos') ?>" class="btn btn-glass"><i class="bi bi-plus-lg me-1"></i> Transaksi Baru</a>
</div>

<div class="row justify-content-center">
  <div class="col-lg-6">
    <div id="print-area" class="receipt-paper mb-3"><?= esc($strukText) ?></div>
    <div class="d-grid gap-2 d-lg-flex justify-content-lg-center">
      <button onclick="window.print()" class="btn btn-dark-apple"><i class="bi bi-printer me-1"></i> Cetak Browser</button>
      <button id="btnBluetooth" class="btn btn-apple"><i class="bi bi-bluetooth me-1"></i> Printer Thermal</button>
      <a class="btn btn-glass" target="_blank" href="https://wa.me/?text=<?= rawurlencode($waText) ?>"><i class="bi bi-whatsapp me-1"></i> Kirim WA</a>
    </div>
    <p class="text-center small text-muted-apple mt-2">Printer thermal: butuh Chrome + printer Bluetooth ESC/POS, atau gunakan aplikasi RawBT.</p>
  </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
document.getElementById('btnBluetooth').addEventListener('click', async () => {
  try {
    const res = await fetch('<?= site_url('pos/struk-data/' . $trx['id']) ?>');
    const data = await res.json();
    const bytes = Uint8Array.from(atob(data.escpos), c => c.charCodeAt(0));
    const device = await navigator.bluetooth.requestDevice({ filters: [{ services: ['000018f0-0000-1000-8000-00805f9b34fb'] }] });
    const server = await device.gatt.connect();
    const service = await server.getPrimaryService('000018f0-0000-1000-8000-00805f9b34fb');
    const char = await service.getCharacteristic('00002af1-0000-1000-8000-00805f9b34fb');
    // kirim per-chunk 128 byte
    for (let i = 0; i < bytes.length; i += 128) {
      await char.writeValue(bytes.slice(i, i + 128));
    }
    alert('✅ Struk terkirim ke printer.');
  } catch (e) {
    alert('Bluetooth print tidak tersedia: ' + e.message);
  }
});
</script>
<?= $this->endSection() ?>
