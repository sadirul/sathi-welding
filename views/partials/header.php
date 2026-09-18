<?php
/** Expects: $pageTitle (string). Included inside <body> of every authenticated page. */
?>
<header class="app-header">
  <button id="menu-btn" class="icon-btn" aria-label="Open menu" type="button">
    <i class="fa-solid fa-bars"></i>
  </button>
  <div class="app-header__title" id="page-title"><?= htmlspecialchars($pageTitle ?? '', ENT_QUOTES, 'UTF-8') ?></div>
  <div style="position: relative;">
    <button id="logo-btn" class="app-logo" aria-label="Account menu" type="button">
      <img src="assets/icons/icon-96.png" alt="Sathi Welding Karkhana">
    </button>
    <div id="header-popover" class="header-popover">
      <button id="logout-btn" type="button">
        <i class="fa-solid fa-right-from-bracket"></i> Logout
      </button>
    </div>
  </div>
</header>
