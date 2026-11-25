# 🥛 Susupreneur POS & Inventory

Sistem Point of Sale + Inventory terintegrasi untuk bisnis **Susu Kambing** — dibangun dengan **CodeIgniter 4**, **MySQL**, dan **Bootstrap 5** bertema **Apple** (clean, frosted glass, mobile-first).

## ✨ Fitur Utama

| Modul | Kemampuan |
|-------|-----------|
| **Kasir POS** | Grid menu sentuh mobile-first, pembayaran Tunai/QRIS, uang pas & kalkulator kembalian, struk thermal (ESC/POS via Bluetooth) & WhatsApp |
| **FIFO Engine** | Pemotongan stok otomatis per batch (urut `expired_date`), HPP terkunci saat transaksi, anti race-condition (`FOR UPDATE` + transaksi DB) |
| **Resep / BOM** | Bill of Materials per varian (6 rasa × 3 ukuran), toleransi susut, simulasi HPP tertimbang real-time |
| **Inventory** | Multi-batch, penerimaan barang, stok minimum, peringatan kedaluwarsa H+2 |
| **Distribusi** | Surat jalan pusat→kios, potong stok FIFO saat kirim, kios terima → batch baru dengan HPP sama |
| **Shift & Blind Closing** | Modal awal, kas keluar, selisih kas dihitung **di server setelah** kasir input (anti kecurangan) |
| **Void** | Wajib PIN otorisasi owner, stok otomatis kembali ke batch asal |
| **Laporan** | Laba rugi konsolidasi per outlet, breakdown rasa & ukuran cup, grafik 7 hari, export CSV |
| **Waste** | Catat barang rusak/basi per batch, nominal rugi dari HPP |

## 🔐 Akun Demo

| Username | Password | Role | Outlet |
|----------|----------|------|--------|
| `owner` | `password123` | Owner (akses semua) | — |
| `admin` | `password123` | Admin Pusat (inventory/resep, tanpa kasir) | Gudang Pusat |
| `kasir` | `password123` | Kasir | Kios A |
| `kasirb` | `password123` | Kasir | Kios B |

> **PIN otorisasi void (owner):** `123456`

## 🚀 Instalasi

```bash
# 1. Letakkan di htdocs XAMPP, aktifkan Apache + MySQL
# 2. Buat database
mysql -u root -e "CREATE DATABASE susupreneur_pos CHARACTER SET utf8mb4"

# 3. Konfigurasi .env (sudah disediakan, sesuaikan jika perlu)
# 4. Migrasi + seed
php spark migrate
php spark db:seed DemoSeeder

# 5. Akses
# http://localhost/susu/public/
```

## 🧪 Bukti FIFO (Acceptance Criteria)

Checkout 21 gelas Susu Kurma M (21 × 250 ml = 5.250 ml) di Kios A:

| Batch | HPP | Sebelum | Sesudah | Keterangan |
|-------|-----|---------|---------|------------|
| B-SUSU-001 | Rp28/ml | 5.000 ml | **0 ml** | Terpotong habis duluan (ED lebih awal) |
| B-SUSU-002 | Rp32/ml | 10.000 ml | **9.645 ml** | Sisanya 355 ml diambil dari sini |

`transaction_batch_logs` merekam jejak per batch, dan `hpp_unit_locked` di detail transaksi menghitung HPP tertimbang akurat → laporan laba rugi konsolidasi valid per outlet & per varian.

## 🏗 Arsitektur

```
app/
├── Controllers/        # Auth, Pos, Inventory, Shift, Recipe, Waste, Report, Owner, Dashboard
├── Database/
│   ├── Migrations/     # 13 tabel
│   └── Seeds/          # DemoSeeder (3 outlet, 4 user, 15 bahan, 37 batch, 18 varian, 108 resep)
├── Filters/            # AuthFilter, RoleFilter
├── Models/             # 14 model
├── Services/           # FifoStockService, CheckoutService, ReceiptService (core engine)
└── Views/              # Tema Apple (frosted glass, mobile-first, bottom-nav)
```

### Prinsip inti
- **Single source of truth** stok = `stock_batches.qty_sisa` per outlet — tidak ada kolom stok agregat yang bisa selisih.
- **HPP locked** saat transaksi (`hpp_unit_locked`) → laporan historis tidak berubah saat harga beli berubah.
- **Atomic** — checkout, void, transfer, waste selalu dalam transaksi DB; kegagalan apa pun memicu rollback penuh.
- **Blind closing** — kasir tidak pernah melihat kas sistem sebelum input kas fisik.

## 📱 Cetak Struk Thermal

Mendukung printer Bluetooth ESC/POS (58mm/80mm) langsung dari Chrome Android via Web Bluetooth API, atau aplikasi **RawBT** sebagai fallback. Format struk 32 kolom, siap potong kertas.
