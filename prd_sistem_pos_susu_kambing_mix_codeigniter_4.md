# PRODUCT REQUIREMENTS DOCUMENT (PRD)
## Sistem Point of Sale (POS) Multi-Cabang, Manajemen Resep (BOM), & Kontrol Laba FIFO — Usaha Susu Kambing Mix

| Atribut Dokumen | Detail |
| :--- | :--- |
| **Nama Produk** | Susupreneur POS (Multi-Outlet Goat Milk POS & Inventory) |
| **Versi Dokumen** | 1.0 |
| **Tanggal** | 24 November 2026 |
| **Tech Stack Utama** | PHP 8.2+, CodeIgniter 4.5+, MySQL 8.0 / MariaDB 10.6+ (InnoDB), Bootstrap 5 / Tailwind CSS, Alpine.js / jQuery |
| **Integrasi Perangkat** | Bluetooth Thermal Printer (ESC/POS 58mm & 80mm), WhatsApp Click-to-Chat API (`wa.me`) |

---

## 1. Ringkasan Eksekutif & Tujuan Produk
### 1.1 Latar Belakang
Usaha penjualan susu kambing segar yang diracik (*mix*) dengan berbagai varian rasa dan ukuran gelas plastik (*cup* S/M/L) di beberapa kios cabang membutuhkan sistem kasir yang tidak hanya mencatat penjualan, tetapi juga mengontrol persediaan bahan mudah basi (*perishable*) serta kemasan sekali pakai (*cup, lid, sedotan, plastik tentengan*).

### 1.2 Tujuan Utama Sistem
1. **Sinkronisasi Otomatis Modal (HPP) & Laba:** Setiap 1 gelas terjual memotong seluruh komponen bahan baku dan kemasan menggunakan metode **FIFO (*First-In, First-Out*)** dan mengunci nilai HPP pada saat transaksi terjadi.
2. **Kontrol Stok Multi-Cabang Real-Time:** Memisahkan gudang pusat (pembelian & distribusi) dengan stok operasional di masing-masing kios cabang.
3. **Akuntabilitas Kasir & Pencegahan Kebocoran:** Menerapkan mekanisme *Blind Closing* saat pergantian shift, pencatatan barang rusak/basi (*waste*) terpisah, serta otorisasi PIN untuk pembatalan transaksi (*void*).

---

## 2. Peran Pengguna (*User Roles & Permissions*)
Sistem menggunakan *Role-Based Access Control* (RBAC) berbasis *Filter* di CodeIgniter 4:

| Role | Ruang Lingkup Akses | Hak Akses Utama |
| :--- | :--- | :--- |
| **Owner / Superadmin** | Semua Cabang & Gudang Pusat | Dashboard eksekutif, laporan laba rugi konsolidasi, manajemen master menu, harga jual, resep (BOM), manajemen user & cabang, PIN otorisasi *void*. |
| **Admin Gudang** | Gudang Pusat & Distribusi | Input pembelian bahan baku/kemasan (buat *batch* FIFO), kelola tanggal kedaluwarsa, buat Surat Jalan distribusi ke kios cabang, approval *waste* pusat. |
| **Kasir Cabang** | 1 Kios Cabang Terdaftar (`outlet_id`) | Buka/tutup shift (*Blind Closing*), transaksi POS kasir, cetak struk thermal / kirim nota WA, konfirmasi "Terima Barang" dari pusat, input *waste* kios. |

---

## 3. Spesifikasi Fungsional (*Functional Requirements*)

### 3.1 Modul Autentikasi & Manajemen Shift (`Auth` & `Shifts`)
* **FR-01 (Login Multi-Cabang):** Kasir memilih cabang kios (atau terikat otomatis pada akunnya) dan masuk menggunakan `username` + `password` / PIN cepat.
* **FR-02 (Buka Shift):** Sebelum mengakses layar POS, kasir wajib menginput `modal_awal` uang tunai di laci kasir.
* **FR-03 (Blind Closing):** Saat menutup shift, sistem menyembunyikan total penjualan tunai versi sistem. Kasir wajib menghitung dan menginput `kas_fisik_closing`. Sistem kemudian menghitung `selisih_kas = kas_fisik_closing - (modal_awal + penjualan_tunai - kas_keluar)`.

### 3.2 Modul Master Data & Resep / BOM (`Master` & `Recipes`)
* **FR-04 (Master Bahan Baku & Kemasan):** Mendukung satuan terkecil yang presisi (`ml`, `gram`, `pcs`) dengan kategori: `Susu`, `Perasa`, `Cup`, `Lid`, `Plastik_Pendukung`.
* **FR-05 (Master Varian Menu & Ukuran Cup):** Mengelola kombinasi `varian_rasa` (Kurma, Jahe Merah, Cokelat, Stroberi, Kopi, Original) dan `ukuran_cup` (`S` 12oz, `M` 14oz, `L` 16oz) beserta `harga_jual`.
* **FR-06 (Recipe Explosion / BOM):** Setiap `product_variant_id` memiliki daftar `product_recipes` yang menentukan takaran setiap komponen + persentase toleransi susut (`waste_pct`). Halaman ini menampilkan simulasi estimasi HPP dan persentase margin kotor secara langsung.

### 3.3 Modul Gudang FIFO & Distribusi (`Inventory` & `Transfers`)
* **FR-07 (Penerimaan Barang / Batch FIFO):** Setiap pembelian dari peternak/supplier menghasilkan baris baru di `stock_batches` dengan atribut `no_batch`, `qty_awal`, `qty_sisa`, `hpp_satuan`, dan `expired_date`.
* **FR-08 (Peringatan Kedaluwarsa):** Dashboard menampilkan notifikasi untuk *batch* susu dengan `expired_date <= H+2` hari.
* **FR-09 (Transfer Antar-Gudang ke Kios):** Admin gudang membuat dokumen pengiriman. Stok di kios cabang baru bertambah setelah kasir cabang menekan tombol **"Terima Barang"** (mengurangi risiko selisih di perjalanan).

### 3.4 Modul Transaksi POS & Kalkulasi Laba (`POS` & `Checkout`)
* **FR-10 (Antarmuka Kasir Mobile-First):** Filter kategori rasa cepat, tombol pilihan ukuran `S / M / L`, keranjang belanja *real-time*, dan dukungan pembayaran `Tunai` serta `QRIS`.
* **FR-11 (Algoritma Pemotongan Stok FIFO Atomic):**
  1. Membaca seluruh item pada keranjang dan memecahnya menjadi kebutuhan komponen bahan baku & kemasan berdasarkan tabel `product_recipes`.
  2. Menggunakan transaksi database CI4 (`$db->transStart()`) dan penguncian baris (`SELECT ... FOR UPDATE`) pada `stock_batches` milik `outlet_id` terkait, diurutkan berdasarkan `expired_date ASC, id ASC`.
  3. Memotong `qty_sisa` secara berurutan dari *batch* terlama. Jika 1 komponen diambil dari 2 *batch* berbeda dengan HPP berbeda, sistem menghitung rata-rata tertimbangnya dan mencatat jejaknya di `transaction_batch_logs`.
  4. Menyimpan nilai HPP akhir per unit ke kolom `hpp_unit_locked` pada `transaction_details` agar laporan laba historis tidak berubah saat harga bahan naik.
  5. Jika stok salah satu komponen (misal *tutup cup M* atau *susu kambing*) tidak mencukupi, transaksi di-*rollback* otomatis dan menampilkan nama bahan yang habis.
* **FR-12 (Struk Thermal & Nota Digital):** Mendukung pencetakan via Web Bluetooth / RawBT Android ke printer thermal 58mm/80mm serta tombol kirim ringkasan pesanan ke WhatsApp pelanggan.

### 3.5 Modul Susut/Basi (`Waste`) & Laporan (`Reports`)
* **FR-13 (Jurnal Waste):** Pencatatan susu basi, botol bocor, atau gelas cup penyok yang memotong `stock_batches` terkait dan mencatat `nominal_rugi` sebagai pengurang Laba Bersih (bukan selisih kasir).
* **FR-14 (Laporan Laba Rugi Konsolidasi):** Menampilkan Omzet, Total HPP FIFO, Laba Kotor (per cabang, per varian rasa, dan per ukuran cup), Total Biaya Waste, dan Laba Bersih.

---

## 4. Arsitektur Teknis CodeIgniter 4

### 4.1 Struktur Direktori Aplikasi (`app/`)
```text
app/
├── Config/
│   ├── Routes.php              # Grouping route berdasarkan role (/owner, /gudang, /pos)
│   └── Filters.php             # Registrasi AuthFilter & RoleFilter
├── Controllers/
│   ├── AuthController.php      # Login, logout, verifikasi PIN supervisor
│   ├── DashboardController.php # Statistik omzet, alert expired, grafik varian
│   ├── PosController.php       # Layar kasir, proses checkout FIFO, cetak struk
│   ├── ShiftController.php     # Buka shift & blind closing
│   ├── RecipeController.php    # CRUD varian menu, ukuran cup, & kalkulator BOM
│   ├── InventoryController.php # Batch FIFO masuk, transfer stok, terima barang
│   ├── WasteController.php     # Pencatatan barang basi/rusak
│   └── ReportController.php    # Laporan laba kotor/bersih & ekspor Excel/PDF
├── Filters/
│   ├── AuthFilter.php
│   └── RoleFilter.php
├── Models/
│   ├── OutletModel.php
│   ├── UserModel.php
│   ├── CashierShiftModel.php
│   ├── RawMaterialModel.php
│   ├── StockBatchModel.php
│   ├── ProductVariantModel.php
│   ├── ProductRecipeModel.php
│   ├── TransactionModel.php
│   ├── TransactionDetailModel.php
│   ├── TransactionBatchLogModel.php
│   └── StockWasteModel.php
├── Services/
│   ├── FifoStockService.php    # Core logic pemotongan batch FIFO & kalkulasi HPP terkunci
│   └── ReceiptService.php      # Formatter struk thermal ESC/POS & teks WhatsApp
└── Views/
    ├── layouts/                # Template utama (Sidebar Owner/Gudang & Layout Mobile POS)
    ├── pos/                    # Layar kasir, modal pembayaran, preview struk
    ├── recipes/                # Form penyusunan resep & simulasi margin
    └── reports/                # Laporan laba rugi & audit stok
```

### 4.2 Skema Database Inti (Migrations)
1. `outlets`: `id`, `kode_cabang`, `nama_kios`, `alamat`, `status_aktif`, `timestamps`
2. `users`: `id`, `outlet_id` (nullable untuk pusat), `nama`, `username`, `password_hash`, `role` (`owner`, `gudang`, `kasir`), `pin_otorisasi`, `timestamps`
3. `cashier_shifts`: `id`, `outlet_id`, `user_id`, `waktu_buka`, `waktu_tutup`, `modal_awal`, `kas_sistem`, `kas_fisik_closing`, `selisih_kas`, `status` (`open`, `closed`)
4. `raw_materials`: `id`, `kode_bahan`, `nama_item`, `kategori`, `satuan_kecil` (`ml`, `gr`, `pcs`), `stok_minimum`, `timestamps`
5. `stock_batches`: `id`, `outlet_id`, `raw_material_id`, `no_batch`, `qty_awal`, `qty_sisa`, `hpp_satuan` (`DECIMAL(12,4)`), `expired_date`, `created_at`
6. `product_variants`: `id`, `nama_menu`, `varian_rasa`, `ukuran_cup` (`S`, `M`, `L`), `harga_jual` (`DECIMAL(12,2)`), `gambar`, `is_active`
7. `product_recipes`: `id`, `product_variant_id`, `raw_material_id`, `qty_needed` (`DECIMAL(10,2)`), `waste_pct` (`DECIMAL(5,2)`)
8. `transactions`: `id`, `outlet_id`, `shift_id`, `no_invoice`, `metode_bayar` (`tunai`, `qris`), `total_omzet`, `total_hpp`, `laba_kotor`, `status` (`paid`, `void`), `created_at`
9. `transaction_details`: `id`, `transaction_id`, `product_variant_id`, `qty_jual`, `harga_jual_unit`, `hpp_unit_locked`, `subtotal_omzet`, `subtotal_laba`
10. `transaction_batch_logs`: `id`, `transaction_detail_id`, `stock_batch_id`, `qty_terpotong`, `hpp_batch_unit`
11. `stock_wastes`: `id`, `outlet_id`, `stock_batch_id`, `user_id`, `qty_rusak`, `jenis_rusak` (`basi`, `bocor`, `penyok`), `nominal_rugi`, `keterangan`, `created_at`

---

## 5. Persyaratan Non-Fungsional (*Non-Functional Requirements*)
* **Keamanan & Integritas Data:** Seluruh operasi pengurangan stok saat *checkout* dan *transfer* wajib dibungkus dalam *Database Transaction* (`transStart()` / `transComplete()`) dengan `FOR UPDATE` guna mencegah *race condition* atau stok minus.
* **Performa Mobile-First:** Waktu muat halaman POS kasir maksimal `< 1.5 detik` pada koneksi seluler 4G standar dengan ukuran aset ikon terkompresi (WebP/SVG).
* **Keamanan Aplikasi:** Menggunakan proteksi bawaan CodeIgniter 4 (`CSRF Protection`, `XSS Filtering`, *Prepared Statements* pada Query Builder, dan `password_hash()` menggunakan `PASSWORD_BCRYPT`).

---

## 6. Kriteria Penerimaan (*Acceptance Criteria*)
1. Ketika kasir menjual 1 gelas *Susu Kambing Kurma Cup M*, stok susu kambing (ml), sirup kurma (ml), gelas cup M (pcs), tutup cup (pcs), sedotan (pcs), dan plastik tentengan (pcs) otomatis berkurang sesuai takaran resep.
2. Jika harga beli susu kambing pada *batch* baru naik, transaksi yang menggunakan sisa *batch* lama tetap menggunakan HPP lama hingga habis, lalu otomatis beralih ke HPP *batch* baru.
3. Laporan laba rugi hari kemarin tidak berubah angkanya meskipun hari ini admin mengubah harga beli bahan baku maupun takaran resep.
4. Kasir tidak dapat melihat total uang tunai sistem sebelum menekan tombol simpan pada form *Blind Closing*.