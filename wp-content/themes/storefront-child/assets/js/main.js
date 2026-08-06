(function () {
  var masthead = document.getElementById('masthead');
  if (!masthead) return;
  function updateOffset() {
    document.documentElement.style.setProperty('--header-h', masthead.offsetHeight + 'px');
  }
  updateOffset();
  window.addEventListener('resize', updateOffset);
})();

(function () {
  var btn = document.getElementById('search-toggle');
  var panel = document.getElementById('header-search');
  if (!btn || !panel) return;

  btn.addEventListener('click', function () {
    var open = panel.hasAttribute('hidden') ? true : false;
    if (open) {
      panel.removeAttribute('hidden');
      btn.setAttribute('aria-expanded', 'true');
      var input = panel.querySelector('input[type="search"]');
      if (input) input.focus();
    } else {
      panel.setAttribute('hidden', '');
      btn.setAttribute('aria-expanded', 'false');
    }
  });

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && !panel.hasAttribute('hidden')) {
      panel.setAttribute('hidden', '');
      btn.setAttribute('aria-expanded', 'false');
      btn.focus();
    }
  });

  document.addEventListener('click', function (e) {
    if (!panel.hasAttribute('hidden') && !panel.contains(e.target) && e.target !== btn && !btn.contains(e.target)) {
      panel.setAttribute('hidden', '');
      btn.setAttribute('aria-expanded', 'false');
    }
  });
})();

(function () {
  var MOBILE_BP = 768;
  var toggle = document.getElementById('nav-toggle');
  var nav    = document.getElementById('site-nav');
  if (!toggle || !nav) return;

  var overlay = document.createElement('div');
  overlay.className = 'nav-overlay';
  document.body.appendChild(overlay);

  var drawerHeader = document.createElement('div');
  drawerHeader.className = 'nav-drawer-header';
  drawerHeader.innerHTML =
    '<button class="nav-drawer-close" type="button" aria-label="Fechar menu">' +
    '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" width="22" height="22" aria-hidden="true">' +
    '<path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>' +
    '</svg></button>';
  nav.insertBefore(drawerHeader, nav.firstChild);
  drawerHeader.querySelector('.nav-drawer-close').addEventListener('click', closeMenu);

  nav.querySelectorAll('.menu-item-has-children').forEach(function (item) {
    var link    = item.querySelector(':scope > a');
    var subMenu = item.querySelector(':scope > .sub-menu');
    if (!link || !subMenu) return;

    var btn = document.createElement('button');
    btn.className = 'sub-menu-toggle';
    btn.type = 'button';
    btn.setAttribute('aria-expanded', 'false');
    btn.setAttribute('aria-label', 'Abrir submenu');
    btn.innerHTML =
      '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">' +
      '<path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>' +
      '</svg>';
    link.insertAdjacentElement('afterend', btn);

    btn.addEventListener('click', function () {
      var isOpen = subMenu.classList.toggle('is-open');
      btn.classList.toggle('is-active', isOpen);
      btn.setAttribute('aria-expanded', String(isOpen));
    });
  });

  function openMenu() {
    nav.classList.add('is-open');
    toggle.classList.add('is-active');
    toggle.setAttribute('aria-expanded', 'true');
    overlay.classList.add('is-visible');
    requestAnimationFrame(function () { overlay.classList.add('is-active'); });
    document.body.style.overflow = 'hidden';
  }

  function closeMenu() {
    nav.classList.remove('is-open');
    toggle.classList.remove('is-active');
    toggle.setAttribute('aria-expanded', 'false');
    overlay.classList.remove('is-active');
    setTimeout(function () { overlay.classList.remove('is-visible'); }, 320);
    document.body.style.overflow = '';
  }

  toggle.addEventListener('click', function () {
    nav.classList.contains('is-open') ? closeMenu() : openMenu();
  });

  overlay.addEventListener('click', closeMenu);

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && nav.classList.contains('is-open')) closeMenu();
  });

  window.addEventListener('resize', function () {
    if (window.innerWidth > MOBILE_BP && nav.classList.contains('is-open')) closeMenu();
  });
})();

(function () {
  var track = document.getElementById('promo-track');
  if (!track) return;

  var clones = Array.from(track.children).map(function (el) {
    return el.cloneNode(true);
  });
  clones.forEach(function (clone) { track.appendChild(clone); });

  var speed = 0.5;
  var pos = 0;
  var isDragging = false;
  var dragStartX = 0;
  var dragStartPos = 0;
  var paused = false;

  function getHalfWidth() {
    return track.scrollWidth / 2;
  }

  function step() {
    if (!paused && !isDragging) {
      pos += speed;
      if (pos >= getHalfWidth()) pos -= getHalfWidth();
      track.style.transform = 'translateX(-' + pos + 'px)';
    }
    requestAnimationFrame(step);
  }

  requestAnimationFrame(step);

  track.addEventListener('mouseenter', function () { paused = true; });
  track.addEventListener('mouseleave', function () { paused = false; });

  track.addEventListener('mousedown', function (e) {
    isDragging = true;
    dragStartX = e.clientX;
    dragStartPos = pos;
  });

  document.addEventListener('mousemove', function (e) {
    if (!isDragging) return;
    var delta = dragStartX - e.clientX;
    pos = dragStartPos + delta;
    if (pos < 0) pos += getHalfWidth();
    if (pos >= getHalfWidth()) pos -= getHalfWidth();
    track.style.transform = 'translateX(-' + pos + 'px)';
  });

  document.addEventListener('mouseup', function () {
    isDragging = false;
    paused = false;
  });

  track.addEventListener('touchstart', function (e) {
    dragStartX = e.touches[0].clientX;
    dragStartPos = pos;
    paused = true;
  }, { passive: true });

  track.addEventListener('touchmove', function (e) {
    var delta = dragStartX - e.touches[0].clientX;
    pos = dragStartPos + delta;
    if (pos < 0) pos += getHalfWidth();
    if (pos >= getHalfWidth()) pos -= getHalfWidth();
    track.style.transform = 'translateX(-' + pos + 'px)';
  }, { passive: true });

  track.addEventListener('touchend', function () { paused = false; });
})();
