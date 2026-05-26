/* ============================================
   Lost & Found Campus Hub — main.js
   Shared helpers used across all pages
   ============================================ */

// ── TOAST ──
function showToast(msg) {
  let t = document.getElementById('toast');
  if (!t) {
    t = document.createElement('div');
    t.id = 'toast'; t.className = 'toast';
    document.body.appendChild(t);
  }
  t.textContent = msg;
  t.classList.add('show');
  setTimeout(() => t.classList.remove('show'), 2800);
}

// ── MODAL ──
function openModal(id)  { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
function closeModalIf(e, id) {
  if (e.target === document.getElementById(id)) closeModal(id);
}

// ── SETTINGS TOGGLE ──
function toggleSetting(row) {
  const t = row.querySelector('.toggle');
  if (t) t.classList.toggle('on');
}

// ── FORMAT DATE ──
function fmtDate(d) {
  return new Date(d).toLocaleDateString('en-MY', {
    day: 'numeric', month: 'short', year: 'numeric'
  });
}

// ── HIGHLIGHT ACTIVE NAV LINK ──
function setActiveNav() {
  const page = window.location.pathname.split('/').pop();
  document.querySelectorAll('.nav-item').forEach(n => {
    n.classList.remove('active');
    if (n.dataset.page && page.includes(n.dataset.page)) {
      n.classList.add('active');
    }
  });
}
document.addEventListener('DOMContentLoaded', setActiveNav);
