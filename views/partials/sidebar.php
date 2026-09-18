<?php
/** Expects: $activePage (string: 'home' | 'clients'). Included inside <body> of every authenticated page. */
?>
<div id="drawer-overlay" class="drawer-overlay"></div>
<nav id="drawer" class="drawer">
  <div class="drawer__brand">
    <div class="drawer__brand-name">Sathi Welding Karkhana</div>
    <div class="drawer__brand-sub">Client &amp; Payment Manager</div>
  </div>
  <div class="drawer__nav">
    <a href="index.php" class="drawer__link<?= ($activePage ?? '') === 'home' ? ' is-active' : '' ?>">
      <i class="fa-solid fa-house"></i> Home
    </a>
    <a href="clients.php" class="drawer__link<?= ($activePage ?? '') === 'clients' ? ' is-active' : '' ?>">
      <i class="fa-solid fa-users"></i> Clients
    </a>
  </div>
</nav>
