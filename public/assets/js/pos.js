/* Susupreneur POS — logika kasir */
const POS = (() => {
  const cart = new Map();
  let cfg = {};
  let uangVal = 0;
  const $ = id => document.getElementById(id);
  const rupiah = n => 'Rp ' + Math.round(n).toLocaleString('id-ID');
  const totalBelanja = () => { let t = 0; cart.forEach(i => t += i.harga * i.qty); return t; };

  function renderCart() {
    const list = $('cartList');
    list.innerHTML = '';
    let total = 0;
    cart.forEach(item => {
      total += item.harga * item.qty;
      list.insertAdjacentHTML('beforeend', `
        <div class="cart-item">
          <div class="flex-grow-1">
            <div class="fw-semibold small">${item.nama}</div>
            <div class="text-muted-apple" style="font-size:12px">${rupiah(item.harga)}</div>
          </div>
          <button class="qty-btn" data-act="dec" data-id="${item.id}">−</button>
          <span class="fw-bold" style="min-width:22px;text-align:center">${item.qty}</span>
          <button class="qty-btn" data-act="inc" data-id="${item.id}">+</button>
        </div>`);
    });
    $('cartTotal').textContent = rupiah(total);
    $('btnBayar').disabled = cart.size === 0;
    hitungKembali();
  }

  function ubahQty(id, d) {
    const it = cart.get(id);
    if (!it) return;
    it.qty += d;
    if (it.qty <= 0) cart.delete(id);
    renderCart();
  }

  function hitungKembali() {
    const k = uangVal - totalBelanja();
    $('uangKembali').textContent = rupiah(Math.max(0, k));
  }

  function bind() {
    // filter rasa
    document.querySelectorAll('#rasaChips .chip').forEach(c => {
      c.addEventListener('click', () => {
        document.querySelectorAll('#rasaChips .chip').forEach(x => x.classList.remove('active'));
        c.classList.add('active');
        const rasa = c.dataset.rasa;
        document.querySelectorAll('.menu-item').forEach(m => {
          m.style.display = (!rasa || m.dataset.rasa === rasa) ? '' : 'none';
        });
      });
    });
    // klik menu
    document.querySelectorAll('.menu-card').forEach(card => {
      card.addEventListener('click', () => {
        const id = card.dataset.id;
        if (cart.has(id)) cart.get(id).qty++;
        else cart.set(id, { id, nama: card.dataset.nama, harga: parseFloat(card.dataset.harga), qty: 1 });
        renderCart();
      });
    });
    // qty buttons (delegasi)
    $('cartList').addEventListener('click', e => {
      const btn = e.target.closest('.qty-btn');
      if (!btn) return;
      ubahQty(btn.dataset.id, btn.dataset.act === 'inc' ? 1 : -1);
    });
    $('btnClear').addEventListener('click', () => { cart.clear(); renderCart(); });
    // uang
    $('uangDiterima').addEventListener('input', e => {
      uangVal = parseInt(e.target.value.replace(/\D/g, '')) || 0;
      e.target.value = uangVal ? uangVal.toLocaleString('id-ID') : '';
      hitungKembali();
    });
    document.querySelectorAll('.pay-key[data-n]').forEach(k => {
      k.addEventListener('click', () => {
        uangVal += parseInt(k.dataset.n);
        $('uangDiterima').value = uangVal.toLocaleString('id-ID');
        hitungKembali();
      });
    });
    $('btnUangPas').addEventListener('click', () => {
      uangVal = totalBelanja();
      $('uangDiterima').value = uangVal.toLocaleString('id-ID');
      hitungKembali();
    });
    document.querySelectorAll('input[name="metode"]').forEach(r => {
      r.addEventListener('change', () => {
        $('tunaiArea').style.display = $('mTunai').checked ? '' : 'none';
      });
    });
    $('btnBayar').addEventListener('click', bayar);
  }

  async function bayar() {
    const btn = $('btnBayar');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Memproses...';
    const metode = document.querySelector('input[name="metode"]:checked').value;
    const payload = {
      items: [...cart.values()].map(i => ({ variant_id: i.id, qty: i.qty })),
      metode_bayar: metode,
      uang_diterima: metode === 'tunai' ? uangVal : totalBelanja(),
      nama_pelanggan: $('namaPelanggan').value,
    };
    payload[cfg.csrfName] = cfg.csrfHash;
    try {
      const res = await fetch(cfg.checkoutUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': cfg.csrfHash },
        body: JSON.stringify(payload)
      });
      const data = await res.json();
      if (!res.ok || !data.ok) {
        alert('⚠️ ' + (data.message || 'Transaksi gagal.'));
      } else {
        $('suksesInvoice').textContent = data.no_invoice;
        $('suksesTotal').textContent = rupiah(data.total);
        $('suksesKembali').textContent = 'Kembali ' + rupiah(data.kembali);
        $('btnStruk').href = cfg.strukUrl + data.trx_id;
        new bootstrap.Modal($('modalSukses')).show();
        cart.clear(); renderCart();
        uangVal = 0; $('uangDiterima').value = ''; hitungKembali();
        $('namaPelanggan').value = '';
      }
    } catch (e) {
      alert('⚠️ Koneksi bermasalah.');
    }
    btn.disabled = false;
    btn.innerHTML = '<i class="bi bi-check2-circle me-1"></i> Bayar';
  }

  return {
    init(config) { cfg = config; bind(); renderCart(); }
  };
})();
