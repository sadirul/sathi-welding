<?php

declare(strict_types=1);

require_once __DIR__ . '/_bootstrap.php';

Auth::requireAuthPage();

$clientId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($clientId <= 0) {
    header('Location: clients.php');
    exit;
}

$pageTitle = 'Client Details';
$activePage = 'clients';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <title>Client Details — Sathi Welding Karkhana</title>
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

    <main class="app-content" id="client-detail-root" data-client-id="<?= $clientId ?>" data-app-name="<?= htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8') ?>">
      <div class="client-profile" id="client-profile">
        <div class="skeleton skeleton--card" style="height:120px;"></div>
      </div>

      <div class="summary-grid" id="summary-grid"></div>

      <div class="page-header-row">
        <h1 style="font-size:16px;">Transactions</h1>
        <button class="fab" type="button" data-open-sheet="add-payment-sheet">
          <i class="fa-solid fa-plus"></i> Add Payment
        </button>
      </div>

      <div id="transaction-list">
        <div class="skeleton skeleton--card"></div>
        <div class="skeleton skeleton--card"></div>
      </div>
    </main>
  </div>

  <!-- Add Payment bottom sheet -->
  <div class="sheet-overlay" data-close-sheet="add-payment-sheet"></div>
  <div class="bottom-sheet" id="add-payment-sheet">
    <div class="bottom-sheet__handle"></div>
    <div class="bottom-sheet__header">
      <h2>Add Payment</h2>
      <button class="icon-btn" type="button" data-close-sheet="add-payment-sheet" aria-label="Close">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
    <div class="bottom-sheet__body">
      <form id="add-payment-form" autocomplete="off">
        <div class="form-group">
          <label class="form-label" for="payment-amount">Amount</label>
          <div class="amount-input-wrap">
            <span class="currency-prefix">₹</span>
            <input class="form-control" type="text" id="payment-amount" inputmode="decimal" required>
          </div>
          <div class="form-error" id="error-amount"></div>
        </div>

        <div class="form-group">
          <label class="form-label">Type</label>
          <div class="segmented-control">
            <div class="segmented-option">
              <input type="radio" name="type" id="type-paid" value="paid" required>
              <label class="segmented-option--paid" for="type-paid"><i class="fa-solid fa-circle-check"></i> Paid</label>
            </div>
            <div class="segmented-option">
              <input type="radio" name="type" id="type-due" value="due" required>
              <label class="segmented-option--due" for="type-due"><i class="fa-solid fa-hourglass-half"></i> Due</label>
            </div>
          </div>
          <div class="form-error" id="error-type"></div>
        </div>

        <div class="form-group">
          <label class="form-label" for="payment-notes">Notes <span class="optional">(optional)</span></label>
          <textarea class="form-control" id="payment-notes" maxlength="500"></textarea>
          <div class="form-error" id="error-notes"></div>
        </div>

        <div class="sheet-actions">
          <button class="btn btn--secondary" type="button" data-close-sheet="add-payment-sheet">Cancel</button>
          <button class="btn btn--primary" type="submit" id="add-payment-submit">Add Payment</button>
        </div>
      </form>
    </div>
  </div>

  <div id="toast-container"></div>

  <script src="<?= asset_url('assets/js/app.js') ?>"></script>
</body>
</html>
