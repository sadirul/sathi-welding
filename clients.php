<?php

declare(strict_types=1);

require_once __DIR__ . '/_bootstrap.php';

Auth::requireAuthPage();

$pageTitle = 'Clients';
$activePage = 'clients';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <title>Clients — Sathi Welding Karkhana</title>
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
        <h1>Clients</h1>
        <button class="fab" type="button" data-open-sheet="add-client-sheet">
          <i class="fa-solid fa-plus"></i> Add Client
        </button>
      </div>

      <div class="search-box">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" id="client-search" class="form-control" placeholder="Search clients by name or mobile" autocomplete="off">
        <button type="button" id="client-search-clear" class="search-box__clear" aria-label="Clear search" hidden>
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>

      <div id="clients-list">
        <div class="skeleton skeleton--card"></div>
        <div class="skeleton skeleton--card"></div>
        <div class="skeleton skeleton--card"></div>
      </div>
    </main>
  </div>

  <!-- Add Client bottom sheet -->
  <div class="sheet-overlay" data-close-sheet="add-client-sheet"></div>
  <div class="bottom-sheet" id="add-client-sheet">
    <div class="bottom-sheet__handle"></div>
    <div class="bottom-sheet__header">
      <h2>Add Client</h2>
      <button class="icon-btn" type="button" data-close-sheet="add-client-sheet" aria-label="Close">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
    <div class="bottom-sheet__body">
      <form id="add-client-form" autocomplete="off">
        <div class="form-group">
          <label class="form-label" for="client-name">Name</label>
          <input class="form-control" type="text" id="client-name" maxlength="150" required>
          <div class="form-error" id="error-name"></div>
        </div>
        <div class="form-group">
          <label class="form-label" for="client-mobile">Mobile <span class="optional">(optional)</span></label>
          <input class="form-control" type="tel" id="client-mobile" inputmode="numeric" maxlength="10" placeholder="10-digit mobile number">
          <div class="form-error" id="error-mobile"></div>
        </div>
        <div class="form-group">
          <label class="form-label" for="client-address">Address <span class="optional">(optional)</span></label>
          <textarea class="form-control" id="client-address" maxlength="500"></textarea>
          <div class="form-error" id="error-address"></div>
        </div>
        <div class="sheet-actions">
          <button class="btn btn--secondary" type="button" data-close-sheet="add-client-sheet">Cancel</button>
          <button class="btn btn--primary" type="submit" id="add-client-submit">Add Client</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Edit Client bottom sheet -->
  <div class="sheet-overlay" data-close-sheet="edit-client-sheet"></div>
  <div class="bottom-sheet" id="edit-client-sheet">
    <div class="bottom-sheet__handle"></div>
    <div class="bottom-sheet__header">
      <h2>Edit Client</h2>
      <button class="icon-btn" type="button" data-close-sheet="edit-client-sheet" aria-label="Close">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
    <div class="bottom-sheet__body">
      <form id="edit-client-form" autocomplete="off">
        <input type="hidden" id="edit-client-id">
        <div class="form-group">
          <label class="form-label" for="edit-client-name">Name</label>
          <input class="form-control" type="text" id="edit-client-name" maxlength="150" required>
          <div class="form-error" id="edit-error-name"></div>
        </div>
        <div class="form-group">
          <label class="form-label" for="edit-client-mobile">Mobile <span class="optional">(optional)</span></label>
          <input class="form-control" type="tel" id="edit-client-mobile" inputmode="numeric" maxlength="10" placeholder="10-digit mobile number">
          <div class="form-error" id="edit-error-mobile"></div>
        </div>
        <div class="form-group">
          <label class="form-label" for="edit-client-address">Address <span class="optional">(optional)</span></label>
          <textarea class="form-control" id="edit-client-address" maxlength="500"></textarea>
          <div class="form-error" id="edit-error-address"></div>
        </div>
        <div class="sheet-actions">
          <button class="btn btn--secondary" type="button" data-close-sheet="edit-client-sheet">Cancel</button>
          <button class="btn btn--primary" type="submit" id="edit-client-submit">Save Changes</button>
        </div>
      </form>
    </div>
  </div>

  <div id="toast-container"></div>

  <script src="<?= asset_url('assets/js/app.js') ?>"></script>
</body>
</html>
