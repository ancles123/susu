<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// ─── Auth ───
$routes->get('/', static fn() => redirect()->to(session()->get('is_logged_in') ? site_url('dashboard') : site_url('login')));
$routes->get('login', 'AuthController::login');
$routes->post('login', 'AuthController::attempt');
$routes->get('logout', 'AuthController::logout');
$routes->post('auth/verify-pin', 'AuthController::verifyPin');

// ─── Area terproteksi (wajib login) ───
$routes->group('', ['filter' => 'auth'], static function ($routes) {

    $routes->get('dashboard', 'DashboardController::index');
    $routes->get('dashboard/data', 'DashboardController::chartData');

    // Shift
    $routes->get('shift/open', 'ShiftController::openForm');
    $routes->post('shift/open', 'ShiftController::open');
    $routes->get('shift/close', 'ShiftController::closeForm');
    $routes->post('shift/close', 'ShiftController::close');
    $routes->get('shift/riwayat', 'ShiftController::riwayat');

    // POS Kasir
    $routes->get('pos', 'PosController::index', ['filter' => 'role:kasir']);
    $routes->get('pos/menu', 'PosController::menuJson', ['filter' => 'role:kasir']);
    $routes->post('pos/checkout', 'PosController::checkout', ['filter' => 'role:kasir']);
    $routes->get('pos/struk/(:num)', 'PosController::struk/$1', ['filter' => 'role:kasir']);
    $routes->get('pos/struk-data/(:num)', 'PosController::strukData/$1', ['filter' => 'role:kasir']);
    $routes->post('pos/void/(:num)', 'PosController::void/$1', ['filter' => 'role:kasir']);
    $routes->get('pos/riwayat', 'PosController::riwayat', ['filter' => 'role:kasir']);

    // Inventory (admin & owner)
    $routes->group('inventory', ['filter' => 'role:admin'], static function ($routes) {
        $routes->get('/', 'InventoryController::index');
        $routes->get('stok', 'InventoryController::stok');
        $routes->get('penerimaan', 'InventoryController::penerimaanForm');
        $routes->post('penerimaan', 'InventoryController::penerimaan');
        $routes->get('bahan', 'InventoryController::bahan');
        $routes->post('bahan/simpan', 'InventoryController::bahanSimpan');
        $routes->post('bahan/hapus/(:num)', 'InventoryController::bahanHapus/$1');

        // Transfer / Distribusi
        $routes->get('transfer', 'InventoryController::transfer');
        $routes->get('transfer/buat', 'InventoryController::transferForm');
        $routes->post('transfer/simpan', 'InventoryController::transferSimpan');
        $routes->get('transfer/detail/(:num)', 'InventoryController::transferDetail/$1');
        $routes->post('transfer/kirim/(:num)', 'InventoryController::transferKirim/$1');
        $routes->post('transfer/batal/(:num)', 'InventoryController::transferBatal/$1');
    });

    // Terima barang (kasir di kios)
    $routes->get('terima', 'InventoryController::terimaList');
    $routes->post('terima/(:num)', 'InventoryController::terimaProses/$1');

    // Resep / BOM (owner & admin)
    $routes->group('recipes', ['filter' => 'role:admin'], static function ($routes) {
        $routes->get('/', 'RecipeController::index');
        $routes->post('varian/simpan', 'RecipeController::varianSimpan');
        $routes->post('varian/toggle/(:num)', 'RecipeController::varianToggle/$1');
        $routes->get('bom/(:num)', 'RecipeController::bom/$1');
        $routes->post('bom/simpan/(:num)', 'RecipeController::bomSimpan/$1');
        $routes->get('simulasi/(:num)', 'RecipeController::simulasi/$1');
    });

    // Waste
    $routes->get('waste', 'WasteController::index');
    $routes->get('waste/buat', 'WasteController::buat');
    $routes->get('waste/batches/(:num)', 'WasteController::batches/$1');
    $routes->post('waste/simpan', 'WasteController::simpan');

    // Laporan
    $routes->group('reports', static function ($routes) {
        $routes->get('/', 'ReportController::index');
        $routes->get('laba-rugi', 'ReportController::labaRugi');
        $routes->get('stok', 'ReportController::stok');
        $routes->get('export-csv', 'ReportController::exportCsv');
    });

    // Manajemen user & outlet (owner)
    $routes->group('owner', ['filter' => 'role:owner'], static function ($routes) {
        $routes->get('users', 'OwnerController::users');
        $routes->post('users/simpan', 'OwnerController::userSimpan');
        $routes->post('users/toggle/(:num)', 'OwnerController::userToggle/$1');
        $routes->get('outlets', 'OwnerController::outlets');
        $routes->post('outlets/simpan', 'OwnerController::outletSimpan');
    });
});

