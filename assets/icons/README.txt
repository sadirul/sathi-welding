App icon files (generated from the supplied logo artwork):

  icon-512.png   512x512 — manifest.json (large)
  icon-192.png   192x192 — manifest.json (standard) + login page logo
  icon-96.png    96x96   — header account button
  icon-48.png    48x48   — browser tab favicon
  icon-32.png    32x32   — spare/small favicon

To replace the logo later, drop a new square PNG at these file names
(same sizes) and they'll be picked up automatically by manifest.json,
public/login.php and views/partials/header.php.
