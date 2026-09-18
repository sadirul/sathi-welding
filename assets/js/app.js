/* ==========================================================================
   Sathi Welding Karkhana — App JS
   Central API helper, toast system, bottom-sheet/drawer helpers, and
   page-specific logic (gated by presence of DOM elements).
   ========================================================================== */

(function () {
  'use strict';

  const API_BASE = 'api/';

  /* ------------------------------------------------------------------ */
  /* Top progress bar (AJAX loading indicator)                          */
  /* ------------------------------------------------------------------ */

  const topProgress = document.createElement('div');
  topProgress.className = 'top-progress';
  document.addEventListener('DOMContentLoaded', () => document.body.appendChild(topProgress));

  let activeRequests = 0;
  function beginLoading() {
    activeRequests++;
    topProgress.classList.add('is-active');
  }
  function endLoading() {
    activeRequests = Math.max(0, activeRequests - 1);
    if (activeRequests === 0) topProgress.classList.remove('is-active');
  }

  /* ------------------------------------------------------------------ */
  /* Central API request helper                                         */
  /* ------------------------------------------------------------------ */

  function csrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.content : '';
  }

  /**
   * api.request('clients.php', { method: 'POST', body: {...} })
   * Returns the parsed JSON `data` payload on success and throws
   * { message, errors, status } on failure (validation/server/network).
   */
  const Api = {
    async request(path, options = {}) {
      const method = options.method || 'GET';
      const headers = { Accept: 'application/json' };
      let body;

      if (options.body !== undefined) {
        headers['Content-Type'] = 'application/json';
        body = JSON.stringify(options.body);
      }

      if (method !== 'GET') {
        headers['X-CSRF-Token'] = csrfToken();
      }

      beginLoading();

      let response;
      try {
        response = await fetch(API_BASE + path, {
          method,
          headers,
          body,
          credentials: 'same-origin',
        });
      } catch (networkError) {
        endLoading();
        throw { message: 'Network error. Please check your connection.', status: 0 };
      }

      endLoading();

      let payload = null;
      try {
        payload = await response.json();
      } catch (parseError) {
        throw { message: 'Unexpected server response.', status: response.status };
      }

      if (response.status === 401) {
        Toast.show('Session expired. Please log in again.', 'warning');
        setTimeout(() => { window.location.href = 'login.php'; }, 1200);
        throw { message: payload.message || 'Session expired', status: 401 };
      }

      if (!response.ok || !payload.success) {
        throw {
          message: (payload && payload.message) || 'Something went wrong',
          errors: (payload && payload.errors) || {},
          status: response.status,
        };
      }

      return payload.data || {};
    },

    get(path) {
      return this.request(path);
    },
    post(path, body) {
      return this.request(path, { method: 'POST', body });
    },
  };

  window.Api = Api;

  /* ------------------------------------------------------------------ */
  /* Toast notifications                                                 */
  /* ------------------------------------------------------------------ */

  const TOAST_ICONS = {
    success: 'fa-solid fa-circle-check',
    error: 'fa-solid fa-circle-exclamation',
    warning: 'fa-solid fa-triangle-exclamation',
    info: 'fa-solid fa-circle-info',
  };

  const Toast = {
    show(message, type = 'info', duration = 3200) {
      const container = document.getElementById('toast-container');
      if (!container || !message) return;

      const toast = document.createElement('div');
      toast.className = `toast toast--${type}`;
      toast.innerHTML = `<i class="${TOAST_ICONS[type] || TOAST_ICONS.info}"></i><span></span>`;
      toast.querySelector('span').textContent = message;

      container.appendChild(toast);
      requestAnimationFrame(() => toast.classList.add('is-visible'));

      setTimeout(() => {
        toast.classList.remove('is-visible');
        setTimeout(() => toast.remove(), 300);
      }, duration);
    },
  };

  window.Toast = Toast;

  /* ------------------------------------------------------------------ */
  /* Currency formatting (Indian numbering, e.g. ₹1,25,000)              */
  /* ------------------------------------------------------------------ */

  function formatCurrency(amount) {
    const value = Number(amount) || 0;
    const formatted = new Intl.NumberFormat('en-IN', {
      maximumFractionDigits: value % 1 === 0 ? 0 : 2,
      minimumFractionDigits: 0,
    }).format(value);
    return '₹' + formatted;
  }
  window.formatCurrency = formatCurrency;

  function formatDate(dateString) {
    const date = new Date(dateString.replace(' ', 'T'));
    if (isNaN(date.getTime())) return dateString;
    return date.toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' });
  }
  window.formatDate = formatDate;

  function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str == null ? '' : String(str);
    return div.innerHTML;
  }
  window.escapeHtml = escapeHtml;

  function initials(name) {
    return (name || '?').trim().split(/\s+/).slice(0, 2).map((w) => w[0]).join('').toUpperCase();
  }
  window.initials = initials;

  /* ------------------------------------------------------------------ */
  /* Bottom sheet helper                                                 */
  /* ------------------------------------------------------------------ */

  const BottomSheet = {
    open(id) {
      const sheet = document.getElementById(id);
      const overlay = sheet ? sheet.parentElement.querySelector('.sheet-overlay') : null;
      if (!sheet) return;
      document.body.style.overflow = 'hidden';
      overlay && overlay.classList.add('is-open');
      sheet.classList.add('is-open');
    },
    close(id) {
      const sheet = document.getElementById(id);
      const overlay = sheet ? sheet.parentElement.querySelector('.sheet-overlay') : null;
      if (!sheet) return;
      document.body.style.overflow = '';
      overlay && overlay.classList.remove('is-open');
      sheet.classList.remove('is-open');
    },
  };
  window.BottomSheet = BottomSheet;

  /* ------------------------------------------------------------------ */
  /* Confirm modal helper (centered Are-you-sure dialog)                 */
  /* ------------------------------------------------------------------ */

  let confirmModalHandler = null;

  const ConfirmModal = {
    open({ title, message, confirmLabel, onConfirm }) {
      const overlay = document.getElementById('confirm-modal-overlay');
      const modal = document.getElementById('confirm-modal');
      const confirmBtn = document.getElementById('confirm-modal-confirm');
      if (!overlay || !modal || !confirmBtn) return;

      document.getElementById('confirm-modal-title').textContent = title || 'Are you sure?';
      document.getElementById('confirm-modal-message').textContent = message || 'This action cannot be undone.';
      confirmBtn.textContent = confirmLabel || 'Delete';

      if (confirmModalHandler) confirmBtn.removeEventListener('click', confirmModalHandler);
      confirmModalHandler = () => {
        ConfirmModal.close();
        onConfirm && onConfirm();
      };
      confirmBtn.addEventListener('click', confirmModalHandler);

      document.body.style.overflow = 'hidden';
      overlay.classList.add('is-open');
      modal.classList.add('is-open');
    },
    close() {
      const overlay = document.getElementById('confirm-modal-overlay');
      const modal = document.getElementById('confirm-modal');
      const confirmBtn = document.getElementById('confirm-modal-confirm');
      document.body.style.overflow = '';
      overlay && overlay.classList.remove('is-open');
      modal && modal.classList.remove('is-open');
      if (confirmBtn && confirmModalHandler) {
        confirmBtn.removeEventListener('click', confirmModalHandler);
        confirmModalHandler = null;
      }
    },
  };
  window.ConfirmModal = ConfirmModal;

  document.addEventListener('DOMContentLoaded', () => {
    const confirmOverlay = document.getElementById('confirm-modal-overlay');
    const confirmCancelBtn = document.getElementById('confirm-modal-cancel');
    confirmOverlay && confirmOverlay.addEventListener('click', () => ConfirmModal.close());
    confirmCancelBtn && confirmCancelBtn.addEventListener('click', () => ConfirmModal.close());
  });

  document.addEventListener('click', (e) => {
    const openTrigger = e.target.closest('[data-open-sheet]');
    if (openTrigger) {
      BottomSheet.open(openTrigger.dataset.openSheet);
      if (openTrigger.dataset.openSheet === 'add-payment-sheet') presetPaymentType();
    }

    const closeTrigger = e.target.closest('[data-close-sheet]');
    if (closeTrigger) BottomSheet.close(closeTrigger.dataset.closeSheet);

    const whatsappTrigger = e.target.closest('[data-whatsapp-btn]');
    if (whatsappTrigger) openWhatsAppReminder(whatsappTrigger.dataset);

    const editTrigger = e.target.closest('[data-edit-client]');
    if (editTrigger) openEditClientSheet(editTrigger.dataset);

    const shareImageTrigger = e.target.closest('[data-share-image-btn]');
    if (shareImageTrigger) {
      shareImageTrigger.disabled = true;
      const originalHtml = shareImageTrigger.innerHTML;
      shareImageTrigger.innerHTML = '<span class="spinner"></span> Preparing...';
      generateAndShareStatementImage().finally(() => {
        shareImageTrigger.disabled = false;
        shareImageTrigger.innerHTML = originalHtml;
      });
    }

    const deleteTrigger = e.target.closest('[data-delete-client]');
    if (deleteTrigger) {
      const { id, name } = deleteTrigger.dataset;
      ConfirmModal.open({
        title: 'Delete this client?',
        message: `Are you sure you want to delete "${name}"?`,
        confirmLabel: 'Delete',
        onConfirm: () => deleteClient(id),
      });
    }
  });

  async function deleteClient(id) {
    try {
      await Api.post('delete-client.php', { id: Number(id) });
      Toast.show('Client deleted successfully', 'success');
      setTimeout(() => { window.location.href = 'clients.php'; }, 600);
    } catch (err) {
      Toast.show(err.message || 'Could not delete client', 'error');
    }
  }

  function openEditClientSheet({ id, name, mobile, address }) {
    const idInput = document.getElementById('edit-client-id');
    if (!idInput) return;

    idInput.value = id;
    document.getElementById('edit-client-name').value = name || '';
    document.getElementById('edit-client-mobile').value = mobile || '';
    document.getElementById('edit-client-address').value = address || '';
    ['edit-error-name', 'edit-error-mobile', 'edit-error-address'].forEach((elId) => {
      const el = document.getElementById(elId);
      if (el) el.textContent = '';
    });

    BottomSheet.open('edit-client-sheet');
  }

  /**
   * Opens WhatsApp (wa.me) with a prefilled due-amount reminder message.
   * mobile is a plain 10-digit Indian number; wa.me needs the country code.
   */
  function openWhatsAppReminder({ mobile, name, due, appName }) {
    const digits = (mobile || '').replace(/\D/g, '');
    const phoneWithCountryCode = digits.length === 10 ? '91' + digits : digits;

    const message = `Hi ${name},\n\nআপনার ${formatCurrency(due)} টাকা বাকি আছে\n\nThanks\n${appName}`;

    window.open(`https://wa.me/${phoneWithCountryCode}?text=${encodeURIComponent(message)}`, '_blank', 'noopener');
  }

  /* ------------------------------------------------------------------ */
  /* Header + sidebar interactions                                       */
  /* ------------------------------------------------------------------ */

  document.addEventListener('DOMContentLoaded', () => {
    const menuBtn = document.getElementById('menu-btn');
    const drawer = document.getElementById('drawer');
    const drawerOverlay = document.getElementById('drawer-overlay');
    const drawerClose = () => { drawer && drawer.classList.remove('is-open'); drawerOverlay && drawerOverlay.classList.remove('is-open'); };
    const drawerOpen = () => { drawer && drawer.classList.add('is-open'); drawerOverlay && drawerOverlay.classList.add('is-open'); };

    menuBtn && menuBtn.addEventListener('click', drawerOpen);
    drawerOverlay && drawerOverlay.addEventListener('click', drawerClose);
    document.querySelectorAll('.drawer__link').forEach((link) => link.addEventListener('click', drawerClose));

    const logoBtn = document.getElementById('logo-btn');
    const headerPopover = document.getElementById('header-popover');
    logoBtn && logoBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      headerPopover && headerPopover.classList.toggle('is-open');
    });
    document.addEventListener('click', () => headerPopover && headerPopover.classList.remove('is-open'));

    const logoutBtn = document.getElementById('logout-btn');
    logoutBtn && logoutBtn.addEventListener('click', async () => {
      try {
        await Api.post('logout.php');
      } catch (err) {
        // Even if the request fails, still send the user back to login.
      }
      window.location.href = 'login.php';
    });
  });

  /* ------------------------------------------------------------------ */
  /* Login page                                                          */
  /* ------------------------------------------------------------------ */

  function initLoginPage() {
    const boxes = Array.from(document.querySelectorAll('.pin-box'));
    if (!boxes.length) return;

    const form = document.getElementById('login-form');
    const submitBtn = document.getElementById('login-btn');

    function currentPin() {
      return boxes.map((b) => b.value).join('');
    }

    function updateButtonState() {
      submitBtn.disabled = currentPin().length !== 6;
    }

    boxes.forEach((box, index) => {
      box.addEventListener('input', () => {
        box.value = box.value.replace(/\D/g, '').slice(0, 1);
        box.classList.toggle('is-filled', box.value !== '');
        if (box.value && index < boxes.length - 1) boxes[index + 1].focus();
        updateButtonState();
      });

      box.addEventListener('keydown', (e) => {
        if (e.key === 'Backspace' && !box.value && index > 0) {
          boxes[index - 1].focus();
        }
      });

      box.addEventListener('paste', (e) => {
        e.preventDefault();
        const digits = (e.clipboardData.getData('text') || '').replace(/\D/g, '').slice(0, 6).split('');
        digits.forEach((d, i) => { if (boxes[i]) { boxes[i].value = d; boxes[i].classList.add('is-filled'); } });
        const next = boxes[Math.min(digits.length, boxes.length - 1)];
        next && next.focus();
        updateButtonState();
      });
    });

    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      const pin = currentPin();
      if (pin.length !== 6) {
        Toast.show('Please enter a 6-digit PIN', 'error');
        return;
      }

      submitBtn.disabled = true;
      submitBtn.innerHTML = '<span class="spinner"></span> Logging in...';

      try {
        await Api.post('login.php', { pin });
        Toast.show('Login successful', 'success');
        setTimeout(() => { window.location.href = 'index.php'; }, 400);
      } catch (err) {
        Toast.show(err.message || 'Invalid PIN', 'error');
        boxes.forEach((b) => { b.value = ''; b.classList.remove('is-filled'); });
        boxes[0].focus();
        submitBtn.disabled = false;
        submitBtn.innerHTML = 'Login';
      }
    });

    boxes[0].focus();
  }

  /* ------------------------------------------------------------------ */
  /* Dashboard page                                                       */
  /* ------------------------------------------------------------------ */

  async function initDashboardPage() {
    const root = document.getElementById('dashboard-stats');
    if (!root) return;

    try {
      const data = await Api.get('dashboard.php');
      root.innerHTML = `
        <div class="stat-card stat-card--total">
          <div class="stat-card__icon"><i class="fa-solid fa-wallet"></i></div>
          <div><div class="stat-card__label">Total</div><div class="stat-card__value">${formatCurrency(data.total)}</div></div>
        </div>
        <div class="stat-card stat-card--paid">
          <div class="stat-card__icon"><i class="fa-solid fa-circle-check"></i></div>
          <div><div class="stat-card__label">Total Paid</div><div class="stat-card__value">${formatCurrency(data.paid)}</div></div>
        </div>
        <div class="stat-card stat-card--due">
          <div class="stat-card__icon"><i class="fa-solid fa-hourglass-half"></i></div>
          <div><div class="stat-card__label">Total Due</div><div class="stat-card__value">${formatCurrency(data.due)}</div></div>
        </div>
        <a href="clients.php" class="stat-card stat-card--clients">
          <div class="stat-card__icon"><i class="fa-solid fa-users"></i></div>
          <div><div class="stat-card__label">Client</div><div class="stat-card__value">${data.client_count}</div></div>
        </a>`;
    } catch (err) {
      root.innerHTML = `<div class="empty-state"><div class="empty-state__icon"><i class="fa-solid fa-triangle-exclamation"></i></div><div class="empty-state__title">Could not load statistics</div><div class="empty-state__subtitle">${escapeHtml(err.message || 'Please try again')}</div></div>`;
    }
  }

  /* ------------------------------------------------------------------ */
  /* Clients page                                                         */
  /* ------------------------------------------------------------------ */

  function clientCardHtml(client) {
    return `
      <div class="client-card">
        <button class="client-card__edit-btn" type="button" data-edit-client
          data-id="${client.id}" data-name="${escapeHtml(client.name)}"
          data-mobile="${escapeHtml(client.mobile || '')}" data-address="${escapeHtml(client.address || '')}"
          aria-label="Edit client">
          <i class="fa-solid fa-pen"></i>
        </button>
        <a class="client-card__link" href="client-details.php?id=${client.id}">
          <div class="client-card__top">
            <div class="client-avatar">${escapeHtml(initials(client.name))}</div>
            <div>
              <div class="client-card__name">${escapeHtml(client.name)}</div>
              ${client.mobile ? `<div class="client-card__mobile"><i class="fa-solid fa-phone"></i> ${escapeHtml(client.mobile)}</div>` : ''}
            </div>
          </div>
          <div class="client-card__stats">
            <div class="client-card__stat"><div class="client-card__stat-label">Total</div><div class="client-card__stat-value">${formatCurrency(client.total)}</div></div>
            <div class="client-card__stat client-card__stat--paid"><div class="client-card__stat-label">Paid</div><div class="client-card__stat-value">${formatCurrency(client.paid)}</div></div>
            <div class="client-card__stat client-card__stat--due"><div class="client-card__stat-label">Due</div><div class="client-card__stat-value">${formatCurrency(client.due)}</div></div>
          </div>
        </a>
      </div>`;
  }

  let allClients = [];

  function renderClientList(clients, searchQuery) {
    const listRoot = document.getElementById('clients-list');
    if (!listRoot) return;

    if (!clients.length) {
      if (searchQuery) {
        listRoot.innerHTML = `
          <div class="empty-state">
            <div class="empty-state__icon"><i class="fa-solid fa-magnifying-glass"></i></div>
            <div class="empty-state__title">No matching clients</div>
            <div class="empty-state__subtitle">No client found for "${escapeHtml(searchQuery)}".</div>
          </div>`;
      } else {
        listRoot.innerHTML = `
          <div class="empty-state">
            <div class="empty-state__icon"><i class="fa-solid fa-users"></i></div>
            <div class="empty-state__title">No clients yet</div>
            <div class="empty-state__subtitle">Add your first client to get started.</div>
            <button class="fab" style="margin:0 auto" data-open-sheet="add-client-sheet"><i class="fa-solid fa-plus"></i> Add Client</button>
          </div>`;
      }
      return;
    }

    listRoot.innerHTML = `<div class="client-list">${clients.map(clientCardHtml).join('')}</div>`;
  }

  function filterClients(query) {
    const trimmed = query.trim().toLowerCase();
    const clearBtn = document.getElementById('client-search-clear');
    if (clearBtn) clearBtn.hidden = trimmed === '';

    if (!trimmed) {
      renderClientList(allClients, '');
      return;
    }

    const filtered = allClients.filter((client) => {
      const name = (client.name || '').toLowerCase();
      const mobile = client.mobile || '';
      return name.includes(trimmed) || mobile.includes(trimmed);
    });

    renderClientList(filtered, query.trim());
  }

  async function loadClients() {
    const listRoot = document.getElementById('clients-list');
    if (!listRoot) return;

    listRoot.innerHTML = '<div class="skeleton skeleton--card"></div><div class="skeleton skeleton--card"></div><div class="skeleton skeleton--card"></div>';

    try {
      const data = await Api.get('clients.php');
      allClients = data.clients || [];

      const searchInput = document.getElementById('client-search');
      const query = searchInput ? searchInput.value.trim() : '';
      if (query) {
        filterClients(query);
      } else {
        renderClientList(allClients, '');
      }
    } catch (err) {
      listRoot.innerHTML = `<div class="empty-state"><div class="empty-state__icon"><i class="fa-solid fa-triangle-exclamation"></i></div><div class="empty-state__title">Could not load clients</div><div class="empty-state__subtitle">${escapeHtml(err.message || 'Please try again')}</div></div>`;
    }
  }

  function initClientsPage() {
    const listRoot = document.getElementById('clients-list');
    if (!listRoot) return;

    loadClients();

    const searchInput = document.getElementById('client-search');
    const searchClearBtn = document.getElementById('client-search-clear');

    searchInput && searchInput.addEventListener('input', () => filterClients(searchInput.value));
    searchClearBtn && searchClearBtn.addEventListener('click', () => {
      searchInput.value = '';
      filterClients('');
      searchInput.focus();
    });

    const form = document.getElementById('add-client-form');
    const submitBtn = document.getElementById('add-client-submit');
    const errorBox = {
      name: document.getElementById('error-name'),
      mobile: document.getElementById('error-mobile'),
      address: document.getElementById('error-address'),
    };

    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      Object.values(errorBox).forEach((el) => el && (el.textContent = ''));

      const name = document.getElementById('client-name').value.trim();
      const mobile = document.getElementById('client-mobile').value.trim();
      const address = document.getElementById('client-address').value.trim();

      submitBtn.disabled = true;
      submitBtn.innerHTML = '<span class="spinner"></span> Adding...';

      try {
        await Api.post('add-client.php', { name, mobile, address });
        Toast.show('Client added successfully', 'success');
        form.reset();
        BottomSheet.close('add-client-sheet');
        loadClients();
      } catch (err) {
        if (err.errors) {
          Object.entries(err.errors).forEach(([field, msg]) => {
            if (errorBox[field]) errorBox[field].textContent = msg;
          });
        }
        Toast.show(err.message || 'Could not add client', 'error');
      } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = 'Add Client';
      }
    });

    const mobileInput = document.getElementById('client-mobile');
    mobileInput && mobileInput.addEventListener('input', () => {
      mobileInput.value = mobileInput.value.replace(/\D/g, '').slice(0, 10);
    });

    const editForm = document.getElementById('edit-client-form');
    const editSubmitBtn = document.getElementById('edit-client-submit');
    const editErrorBox = {
      name: document.getElementById('edit-error-name'),
      mobile: document.getElementById('edit-error-mobile'),
      address: document.getElementById('edit-error-address'),
    };

    editForm && editForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      Object.values(editErrorBox).forEach((el) => el && (el.textContent = ''));

      const id = document.getElementById('edit-client-id').value;
      const name = document.getElementById('edit-client-name').value.trim();
      const mobile = document.getElementById('edit-client-mobile').value.trim();
      const address = document.getElementById('edit-client-address').value.trim();

      editSubmitBtn.disabled = true;
      editSubmitBtn.innerHTML = '<span class="spinner"></span> Saving...';

      try {
        await Api.post('edit-client.php', { id: Number(id), name, mobile, address });
        Toast.show('Client updated successfully', 'success');
        BottomSheet.close('edit-client-sheet');
        loadClients();
      } catch (err) {
        if (err.errors) {
          Object.entries(err.errors).forEach(([field, msg]) => {
            if (editErrorBox[field]) editErrorBox[field].textContent = msg;
          });
        }
        Toast.show(err.message || 'Could not update client', 'error');
      } finally {
        editSubmitBtn.disabled = false;
        editSubmitBtn.innerHTML = 'Save Changes';
      }
    });

    const editMobileInput = document.getElementById('edit-client-mobile');
    editMobileInput && editMobileInput.addEventListener('input', () => {
      editMobileInput.value = editMobileInput.value.replace(/\D/g, '').slice(0, 10);
    });
  }

  /* ------------------------------------------------------------------ */
  /* Client details page                                                  */
  /* ------------------------------------------------------------------ */

  function transactionCardHtml(t) {
    const isDue = t.type === 'due';
    return `
      <div class="transaction-card transaction-card--${t.type}">
        <div class="transaction-card__main">
          <div class="transaction-card__icon"><i class="fa-solid ${isDue ? 'fa-hourglass-half' : 'fa-circle-check'}"></i></div>
          <div class="transaction-card__body">
            <div class="transaction-card__notes">${escapeHtml(t.notes || (isDue ? 'Due amount' : 'Payment received'))}</div>
            <div class="transaction-card__date">${formatDate(t.created_at)}</div>
            <span class="badge badge--${t.type}">${isDue ? 'Due' : 'Paid'}</span>
          </div>
          <div class="transaction-card__amount">${formatCurrency(t.amount)}</div>
        </div>
        ${t.received_by ? `<div class="transaction-card__footer">${isDue ? 'Added by' : 'Received by'}: <strong>${escapeHtml(t.received_by)}</strong></div>` : ''}
      </div>`;
  }

  let currentClientStatement = null;

  /**
   * Snapshots the client details page (profile, summary, transaction history
   * — the same UI the user sees) into a PNG via html2canvas and hands it to
   * the OS share sheet (Android/desktop Chrome/Edge) via the Web Share API.
   * Falls back to a plain download when file sharing isn't supported
   * (e.g. desktop Firefox/Safari).
   */
  async function generateAndShareStatementImage() {
    if (!currentClientStatement) return;
    const { client, appName } = currentClientStatement;

    const root = document.getElementById('client-detail-root');

    // Hide action buttons (Add Payment / WhatsApp / Share) so the shared
    // image is just the statement content, not the interactive controls.
    const controls = root.querySelectorAll('.client-profile__actions, [data-open-sheet]');
    const previouslyHidden = new Set();
    controls.forEach((el) => {
      if (el.hidden) previouslyHidden.add(el);
      el.hidden = true;
    });

    // html2canvas renders CSS box-shadow poorly (heavy dark smears instead
    // of a clean drop shadow) — strip shadows for the capture only. Also
    // kill animations/transitions: html2canvas clones the DOM into an
    // offscreen frame where animations (e.g. .app-content's page-load
    // fade-in) restart from their 0% keyframe, and it rasterizes before
    // they finish — which is why captures came out faded/washed-out.
    const noShadowStyle = document.createElement('style');
    noShadowStyle.textContent = '#client-detail-root, #client-detail-root * { box-shadow: none !important; animation: none !important; transition: none !important; }';
    document.head.appendChild(noShadowStyle);

    let canvas;
    try {
      canvas = await html2canvas(root, {
        backgroundColor: '#f3f4f8',
        scale: 2,
        useCORS: true,
        // Explicit width/height (rather than the default renderer's
        // viewport-based guess) makes sure content taller than the visible
        // screen — a long transaction list — is captured in full, not just
        // what's currently scrolled into view.
        width: root.scrollWidth,
        height: root.scrollHeight,
        windowWidth: root.scrollWidth,
        windowHeight: root.scrollHeight,
      });
    } finally {
      noShadowStyle.remove();
      controls.forEach((el) => {
        if (!previouslyHidden.has(el)) el.hidden = false;
      });
    }

    const blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/png'));
    if (!blob) {
      Toast.show('Could not generate image', 'error');
      return;
    }

    const fileName = `${client.name.replace(/[^a-z0-9]+/gi, '_')}_statement.png`;
    const file = new File([blob], fileName, { type: 'image/png' });

    if (navigator.canShare && navigator.canShare({ files: [file] })) {
      try {
        await navigator.share({
          files: [file],
          title: `${client.name} — Statement`,
          text: `${appName} — payment statement for ${client.name}`,
        });
        return;
      } catch (err) {
        if (err && err.name === 'AbortError') return;
        // Fall through to download if sharing failed for any other reason.
      }
    }

    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = fileName;
    document.body.appendChild(link);
    link.click();
    link.remove();
    URL.revokeObjectURL(url);
    Toast.show('Sharing not supported here — image downloaded instead', 'info');
  }

  async function loadClientDetails(clientId) {
    const profileRoot = document.getElementById('client-profile');
    const summaryRoot = document.getElementById('summary-grid');
    const listRoot = document.getElementById('transaction-list');

    listRoot.innerHTML = '<div class="skeleton skeleton--card"></div><div class="skeleton skeleton--card"></div>';

    try {
      const data = await Api.get('client.php?id=' + encodeURIComponent(clientId));
      const client = data.client;
      const transactions = data.transactions || [];

      document.getElementById('page-title').textContent = client.name;

      const appName = document.getElementById('client-detail-root').dataset.appName || '';
      const showWhatsApp = Boolean(client.mobile) && client.due > 0;
      const showImageShare = client.due > 0;

      currentClientStatement = { client, transactions, appName };

      profileRoot.innerHTML = `
        <button class="client-profile__delete-btn" type="button" data-delete-client
          data-id="${client.id}" data-name="${escapeHtml(client.name)}" aria-label="Delete client">
          <i class="fa-solid fa-trash"></i>
        </button>
        <div class="client-profile__avatar">${escapeHtml(initials(client.name))}</div>
        <div class="client-profile__name">${escapeHtml(client.name)}</div>
        <div class="client-profile__meta">
          ${client.mobile ? `<span><i class="fa-solid fa-phone"></i> ${escapeHtml(client.mobile)}</span>` : ''}
          ${client.address ? `<span><i class="fa-solid fa-location-dot"></i> ${escapeHtml(client.address)}</span>` : ''}
        </div>
        <div class="client-profile__actions">
          ${showWhatsApp ? `
            <button class="btn-whatsapp" type="button" data-whatsapp-btn
              data-mobile="${escapeHtml(client.mobile)}" data-name="${escapeHtml(client.name)}"
              data-due="${client.due}" data-app-name="${escapeHtml(appName)}">
              <i class="fa-brands fa-whatsapp"></i> Send Reminder
            </button>` : ''}
          ${showImageShare ? `
            <button class="btn-share-image" type="button" data-share-image-btn>
            <i class="fa-solid fa-image"></i> Share Image
          </button>` : ''}
          
        </div>`;

      summaryRoot.innerHTML = `
        <div class="summary-card"><div class="summary-card__label">Total</div><div class="summary-card__value">${formatCurrency(client.total)}</div></div>
        <div class="summary-card summary-card--paid"><div class="summary-card__label">Paid</div><div class="summary-card__value">${formatCurrency(client.paid)}</div></div>
        <div class="summary-card summary-card--due"><div class="summary-card__label">Due</div><div class="summary-card__value">${formatCurrency(client.due)}</div></div>`;

      if (!transactions.length) {
        listRoot.innerHTML = `
          <div class="empty-state">
            <div class="empty-state__icon"><i class="fa-solid fa-receipt"></i></div>
            <div class="empty-state__title">No transactions yet</div>
            <div class="empty-state__subtitle">Add a payment to get started.</div>
            <button class="fab" style="margin:0 auto" data-open-sheet="add-payment-sheet"><i class="fa-solid fa-plus"></i> Add Payment</button>
          </div>`;
      } else {
        listRoot.innerHTML = `<div class="transaction-list">${transactions.map(transactionCardHtml).join('')}</div>`;
      }
    } catch (err) {
      listRoot.innerHTML = `<div class="empty-state"><div class="empty-state__icon"><i class="fa-solid fa-triangle-exclamation"></i></div><div class="empty-state__title">Could not load client</div><div class="empty-state__subtitle">${escapeHtml(err.message || 'Please try again')}</div></div>`;
    }
  }

  /**
   * Defaults the Add Payment type to whichever makes sense for the client's
   * current balance: Paid when there's an outstanding due to pay off,
   * Due when there's nothing owed yet (so the next entry is likely new work).
   */
  function presetPaymentType() {
    if (!currentClientStatement) return;
    const dueRadio = document.getElementById('type-due');
    const paidRadio = document.getElementById('type-paid');
    if (!dueRadio || !paidRadio) return;

    if (currentClientStatement.client.due > 0) {
      paidRadio.checked = true;
    } else {
      dueRadio.checked = true;
    }
  }

  function initClientDetailsPage() {
    const root = document.getElementById('client-detail-root');
    if (!root) return;

    const clientId = root.dataset.clientId;
    loadClientDetails(clientId);

    const form = document.getElementById('add-payment-form');
    const submitBtn = document.getElementById('add-payment-submit');
    const errorBox = {
      amount: document.getElementById('error-amount'),
      type: document.getElementById('error-type'),
      notes: document.getElementById('error-notes'),
    };

    const amountInput = document.getElementById('payment-amount');
    amountInput && amountInput.addEventListener('input', () => {
      amountInput.value = amountInput.value.replace(/[^\d.]/g, '');
    });

    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      Object.values(errorBox).forEach((el) => el && (el.textContent = ''));

      const amount = amountInput.value.trim();
      const typeEl = form.querySelector('input[name="type"]:checked');
      const type = typeEl ? typeEl.value : '';
      const notes = document.getElementById('payment-notes').value.trim();

      if (type === 'paid' && currentClientStatement && Number(amount) > currentClientStatement.client.due) {
        const dueText = formatCurrency(currentClientStatement.client.due);
        errorBox.amount.textContent = `Paid amount cannot exceed the due amount (${dueText})`;
        Toast.show(`Paid amount cannot be more than the due amount (${dueText})`, 'error');
        return;
      }

      submitBtn.disabled = true;
      submitBtn.innerHTML = '<span class="spinner"></span> Saving...';

      try {
        await Api.post('add-payment.php', { client_id: Number(clientId), amount, type, notes });
        Toast.show('Payment added successfully', 'success');
        form.reset();
        BottomSheet.close('add-payment-sheet');
        loadClientDetails(clientId);
      } catch (err) {
        if (err.errors) {
          Object.entries(err.errors).forEach(([field, msg]) => {
            if (errorBox[field]) errorBox[field].textContent = msg;
          });
        }
        Toast.show(err.message || 'Could not add payment', 'error');
      } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = 'Add Payment';
      }
    });
  }

  /* ------------------------------------------------------------------ */
  /* PWA install (login page "Install App" button)                       */
  /* ------------------------------------------------------------------ */

  if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
      navigator.serviceWorker.register('sw.js').catch(() => {});
    });
  }

  let deferredInstallPrompt = null;

  window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    deferredInstallPrompt = e;
    const installBtn = document.getElementById('install-app-btn');
    if (installBtn) installBtn.hidden = false;
  });

  window.addEventListener('appinstalled', () => {
    deferredInstallPrompt = null;
    const installBtn = document.getElementById('install-app-btn');
    if (installBtn) installBtn.hidden = true;
    Toast.show('App installed successfully', 'success');
  });

  function initInstallButton() {
    const installBtn = document.getElementById('install-app-btn');
    if (!installBtn) return;

    installBtn.addEventListener('click', async () => {
      if (!deferredInstallPrompt) return;
      installBtn.disabled = true;
      deferredInstallPrompt.prompt();
      const choice = await deferredInstallPrompt.userChoice;
      deferredInstallPrompt = null;
      installBtn.disabled = false;
      installBtn.hidden = true;
      if (choice.outcome !== 'accepted') {
        Toast.show('Installation cancelled', 'info');
      }
    });
  }

  /* ------------------------------------------------------------------ */
  /* Boot                                                                 */
  /* ------------------------------------------------------------------ */

  document.addEventListener('DOMContentLoaded', () => {
    initLoginPage();
    initInstallButton();
    initDashboardPage();
    initClientsPage();
    initClientDetailsPage();
  });
})();
