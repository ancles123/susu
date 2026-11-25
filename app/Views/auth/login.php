<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Masuk · Susupreneur POS</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
<link href="<?= base_url('assets/css/apple.css') ?>" rel="stylesheet">
</head>
<body>
<div class="login-wrap">
  <div class="login-card fade-in">
    <div class="login-logo">🥛</div>
    <div class="login-title">Susupreneur</div>
    <div class="login-sub">Sistem POS & Inventory Susu Kambing</div>

    <?php if ($m = session()->getFlashdata('error')): ?>
      <div class="alert-ios alert-danger-ios text-start"><i class="bi bi-exclamation-triangle-fill me-2"></i><?= esc($m) ?></div>
    <?php endif; ?>
    <?php if ($m = session()->getFlashdata('success')): ?>
      <div class="alert-ios alert-success-ios text-start"><?= esc($m) ?></div>
    <?php endif; ?>

    <form method="post" action="<?= site_url('login') ?>" class="text-start">
      <?= csrf_field() ?>
      <div class="mb-3">
        <label class="form-label">Username</label>
        <input type="text" name="username" class="form-control" value="<?= old('username') ?>" required autofocus autocomplete="username">
      </div>
      <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" required autocomplete="current-password">
      </div>
      <?php if (! empty($outlets)): ?>
      <div class="mb-4">
        <label class="form-label">Lokasi Kerja <span class="text-muted-apple fw-normal">(khusus owner/gudang)</span></label>
        <select name="outlet_id" class="form-select">
          <?php foreach ($outlets as $o): ?>
            <option value="<?= $o['id'] ?>"><?= esc($o['nama_kios']) ?><?= $o['is_pusat'] ? ' (Pusat)' : '' ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <?php endif; ?>
      <button type="submit" class="btn btn-apple w-100 btn-lg-apple">Masuk</button>
    </form>
    <div class="mt-4 small text-muted-apple">Susu Kambing Segar · Setiap Hari</div>
  </div>
</div>
</body>
</html>
