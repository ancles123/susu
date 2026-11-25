<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="apple-mobile-web-app-capable" content="yes">
<title><?= esc($title ?? 'Susupreneur') ?> · Susupreneur POS</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="<?= base_url('assets/css/apple.css') ?>" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
</head>
<body>
<div class="app-shell">

  <!-- ═══ Sidebar Desktop ═══ -->
  <aside class="sidebar d-none d-lg-flex" id="sidebar">
    <div class="brand">
      <div class="brand-logo">🥛</div>
      <div><div class="brand-name">Susupreneur</div><div class="brand-sub">Susu Kambing Segar</div></div>
    </div>
    <nav class="side-nav">
      <?php $seg = service('uri')->getSegment(1); $seg2 = service('uri')->getSegment(2); ?>
      <a href="<?= site_url('dashboard') ?>" class="side-link <?= $seg==='dashboard'?'active':'' ?>"><i class="bi bi-grid-1x2-fill"></i> Dashboard</a>
      <?php if (in_array(session('role'), ['kasir','owner'])): ?>
      <a href="<?= site_url('pos') ?>" class="side-link <?= $seg==='pos'&&$seg2!=='riwayat'?'active':'' ?>"><i class="bi bi-bag-fill"></i> Kasir</a>
      <a href="<?= site_url('pos/riwayat') ?>" class="side-link <?= $seg2==='riwayat'?'active':'' ?>"><i class="bi bi-clock-history"></i> Riwayat</a>
      <?php endif; ?>
      <?php if (in_array(session('role'), ['admin','owner'])): ?>
      <div class="side-section">Inventory</div>
      <a href="<?= site_url('inventory/stok') ?>" class="side-link <?= $seg2==='stok'?'active':'' ?>"><i class="bi bi-box-seam-fill"></i> Stok</a>
      <a href="<?= site_url('inventory/penerimaan') ?>" class="side-link <?= $seg2==='penerimaan'?'active':'' ?>"><i class="bi bi-box-arrow-in-down"></i> Penerimaan</a>
      <a href="<?= site_url('inventory/transfer') ?>" class="side-link <?= $seg2==='transfer'?'active':'' ?>"><i class="bi bi-truck"></i> Distribusi</a>
      <a href="<?= site_url('recipes') ?>" class="side-link <?= $seg==='recipes'?'active':'' ?>"><i class="bi bi-journal-text"></i> Resep</a>
      <?php endif; ?>
      <?php if (in_array(session('role'), ['kasir','admin','owner'])): ?>
      <a href="<?= site_url('terima') ?>" class="side-link <?= $seg==='terima'?'active':'' ?>"><i class="bi bi-box-arrow-in-down-left"></i> Terima Barang</a>
      <a href="<?= site_url('waste') ?>" class="side-link <?= $seg==='waste'?'active':'' ?>"><i class="bi bi-trash3-fill"></i> Waste</a>
      <a href="<?= site_url('reports/laba-rugi') ?>" class="side-link <?= $seg==='reports'?'active':'' ?>"><i class="bi bi-graph-up-arrow"></i> Laporan</a>
      <?php endif; ?>
      <?php if (session('role') === 'owner'): ?>
      <div class="side-section">Owner</div>
      <a href="<?= site_url('owner/users') ?>" class="side-link <?= $seg2==='users'?'active':'' ?>"><i class="bi bi-people-fill"></i> Pengguna</a>
      <a href="<?= site_url('owner/outlets') ?>" class="side-link <?= $seg2==='outlets'?'active':'' ?>"><i class="bi bi-shop"></i> Outlet</a>
      <?php endif; ?>
    </nav>
    <div class="side-footer">
      <div class="me-auto"><div class="fw-semibold small"><?= esc(session('nama')) ?></div><div class="text-muted" style="font-size:11px"><?= ucfirst(esc(session('role'))) ?></div></div>
      <a href="<?= site_url('logout') ?>" class="btn-icon-soft" title="Keluar"><i class="bi bi-box-arrow-right"></i></a>
    </div>
  </aside>

  <!-- ═══ Konten ═══ -->
  <main class="content">
    <!-- Topbar mobile -->
    <div class="topbar d-lg-none">
      <div class="d-flex align-items-center gap-2"><span class="fs-4">🥛</span><strong>Susupreneur</strong></div>
      <a href="<?= site_url('logout') ?>" class="btn-icon-soft"><i class="bi bi-box-arrow-right"></i></a>
    </div>

    <div class="page">
      <?php if ($m = session()->getFlashdata('success')): ?><div class="alert-ios alert-success-ios"><i class="bi bi-check-circle-fill me-2"></i><?= esc($m) ?></div><?php endif; ?>
      <?php if ($m = session()->getFlashdata('error')): ?><div class="alert-ios alert-danger-ios"><i class="bi bi-exclamation-triangle-fill me-2"></i><?= esc($m) ?></div><?php endif; ?>
      <?php if ($m = session()->getFlashdata('warning')): ?><div class="alert-ios alert-warning-ios"><i class="bi bi-exclamation-circle-fill me-2"></i><?= esc($m) ?></div><?php endif; ?>
      <?php if ($m = session()->getFlashdata('info')): ?><div class="alert-ios alert-info-ios"><i class="bi bi-info-circle-fill me-2"></i><?= esc($m) ?></div><?php endif; ?>

      <?= $this->renderSection('content') ?>
    </div>

    <!-- ═══ Bottom Nav Mobile ═══ -->
    <nav class="bottom-nav d-lg-none">
      <a href="<?= site_url('dashboard') ?>" class="bn-link <?= $seg==='dashboard'?'active':'' ?>"><i class="bi bi-grid-1x2-fill"></i><span>Home</span></a>
      <?php if (in_array(session('role'), ['kasir','owner'])): ?>
      <a href="<?= site_url('pos') ?>" class="bn-link <?= $seg==='pos'?'active':'' ?>"><i class="bi bi-bag-fill"></i><span>Kasir</span></a>
      <a href="<?= site_url('pos/riwayat') ?>" class="bn-link <?= $seg2==='riwayat'?'active':'' ?>"><i class="bi bi-clock-history"></i><span>Riwayat</span></a>
      <?php endif; ?>
      <a href="<?= site_url('reports/laba-rugi') ?>" class="bn-link <?= $seg==='reports'?'active':'' ?>"><i class="bi bi-graph-up"></i><span>Laporan</span></a>
    </nav>
  </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?= $this->renderSection('scripts') ?>
</body>
</html>
