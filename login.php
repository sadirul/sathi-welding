<?php

declare(strict_types=1);

require_once __DIR__ . '/_bootstrap.php';

// Already logged in? Skip straight to the dashboard.
if (Auth::check()) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <title>Login — Sathi Welding Karkhana</title>
  <link rel="manifest" href="manifest.json">
  <meta name="theme-color" content="#4f46e5">
  <link rel="icon" href="assets/icons/icon-48.png" sizes="48x48" type="image/png">
  <link rel="apple-touch-icon" href="assets/icons/icon-192.png">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <link rel="stylesheet" href="<?= asset_url('assets/css/app.css') ?>">
</head>
<body>
  <div class="app-shell">
    <div class="login-page">
      <div class="login-logo">
        <img src="assets/icons/icon-192.png" alt="Sathi Welding Karkhana">
      </div>
      <div class="login-title">Sathi Welding Karkhana</div>
      <div class="login-subtitle">Enter your 6-digit PIN to continue</div>

      <form id="login-form" autocomplete="off">
        <div class="pin-inputs">
          <input class="pin-box" type="password" inputmode="numeric" pattern="\d*" maxlength="1" autocomplete="off">
          <input class="pin-box" type="password" inputmode="numeric" pattern="\d*" maxlength="1" autocomplete="off">
          <input class="pin-box" type="password" inputmode="numeric" pattern="\d*" maxlength="1" autocomplete="off">
          <input class="pin-box" type="password" inputmode="numeric" pattern="\d*" maxlength="1" autocomplete="off">
          <input class="pin-box" type="password" inputmode="numeric" pattern="\d*" maxlength="1" autocomplete="off">
          <input class="pin-box" type="password" inputmode="numeric" pattern="\d*" maxlength="1" autocomplete="off">
        </div>

        <div class="login-actions">
          <button id="login-btn" class="btn btn--primary btn--block" type="submit" disabled>Login</button>
        </div>
      </form>

    </div>
  </div>

  <button id="install-app-btn" class="install-app-btn" type="button" hidden>
    <i class="fa-solid fa-download"></i>
    <span>Install App</span>
  </button>

  <div id="toast-container"></div>

  <script src="<?= asset_url('assets/js/app.js') ?>"></script>
</body>
</html>
