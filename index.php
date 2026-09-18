<?php

declare(strict_types=1);

require_once __DIR__ . '/_bootstrap.php';

Auth::requireAuthPage();

$pageTitle = 'Dashboard';
$activePage = 'home';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <title>Dashboard — Sathi Welding Karkhana</title>
  <meta name="csrf-token" content="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
  <link rel="manifest" href="manifest.json">
  <meta name="theme-color" content="#4f46e5">
  <link rel="icon" href="assets/icons/icon-48.png" sizes="48x48" type="image/png">
  <link rel="apple-touch-icon" href="assets/icons/icon-192.png">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <link rel="stylesheet" href="<?= asset_url('assets/css/app.css') ?>">
</head>
<body>
  <div class="app-shell">
    <?php include __DIR__ . '/views/partials/header.php'; ?>
    <?php include __DIR__ . '/views/partials/sidebar.php'; ?>

    <main class="app-content">
      <div class="page-header-row">
        <h1>Overview</h1>
      </div>

      <div class="stat-grid" id="dashboard-stats">
        <div class="skeleton skeleton--stat"></div>
        <div class="skeleton skeleton--stat"></div>
        <div class="skeleton skeleton--stat"></div>
        <div class="skeleton skeleton--stat"></div>
      </div>
    </main>
  </div>

  <div id="toast-container"></div>

  <script src="<?= asset_url('assets/js/app.js') ?>"></script>
</body>
</html>
