// Ativa tooltip Bootstrap no link do autor na footer
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTrigger = document.querySelector('.footer-dev-link[data-bs-toggle="tooltip"]');
    if (tooltipTrigger && window.bootstrap && bootstrap.Tooltip) {
        new bootstrap.Tooltip(tooltipTrigger);
    }
});
// Remove .bg-body-tertiary from .wp-breadcrumb if present
document.addEventListener('DOMContentLoaded', function() {
    var breadcrumb = document.querySelector('.wp-breadcrumb.bg-body-tertiary');
    if (breadcrumb) {
        breadcrumb.classList.remove('bg-body-tertiary');
    }
});
jQuery(function ($) {

    /* =================================================================
       HEADER SCROLL EFFECT
       - Home: igual wp-headshop (muda após rolar)
       - Internas: já carrega fixo/compacto
       ================================================================= */
    (function () {
        var header = document.getElementById('masthead');
        if (!header) return;

        var body   = document.body;
        var isHome = body.classList.contains('home') ||
                     body.classList.contains('front-page');

        var banner = null;
        var nextSection = null;
        var triggerPoint = null;

        function recalcRefs() {
            if (!isHome) return;
            banner = document.querySelector('.headshop-banner');
            nextSection = document.querySelector('section.headshop-categories') || document.querySelector('.headshop-categories');
            var headerHeight = header ? header.offsetHeight : 0;

            if (nextSection) {
                var nextSectionTop = nextSection.getBoundingClientRect().top + window.pageYOffset;
                triggerPoint = Math.max(0, nextSectionTop - headerHeight);
                return;
            }

            if (banner) {
                var bannerBottom = banner.getBoundingClientRect().bottom + window.pageYOffset;
                triggerPoint = Math.max(0, bannerBottom - headerHeight);
                return;
            }

            triggerPoint = 100;
        }

        function updateHeader() {
            if (!isHome) {
                header.classList.add('headshop-header--scrolled');
            } else {
                if (triggerPoint === null) {
                    recalcRefs();
                }
                var scrolled = window.pageYOffset || document.documentElement.scrollTop || 0;
                var compactHeader = scrolled > 0 && scrolled >= triggerPoint;
                header.classList.toggle('headshop-header--scrolled', compactHeader);
            }
        }

        recalcRefs();
        updateHeader();
        window.addEventListener('load', function() {
            recalcRefs();
            updateHeader();
        });
        setTimeout(function () {
            recalcRefs();
            updateHeader();
        }, 300);
        window.addEventListener('resize', function() {
            recalcRefs();
            updateHeader();
        });
        window.addEventListener('orientationchange', function() {
            recalcRefs();
            updateHeader();
        });
        window.addEventListener('scroll', updateHeader);
    })();


    /* =================================================================
       SEARCH OVERLAY TOGGLE
       ================================================================= */
    (function () {
        var btn        = document.getElementById('searchToggleBtn');
        var overlay    = document.getElementById('headerSearchOverlay');
        var closeBtn   = document.getElementById('searchOverlayClose');
        var inner      = overlay ? overlay.querySelector('.headshop-search-overlay__inner') : null;
        var input      = document.getElementById('headerSearchInput');
        if (!btn || !overlay) return;

        function open() {
            overlay.classList.add('is-open');
            overlay.setAttribute('aria-hidden', 'false');
            document.body.classList.add('search-overlay-open');
            if (input) setTimeout(function () { input.focus(); }, 100);
        }

        function close() {
            overlay.classList.remove('is-open');
            overlay.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('search-overlay-open');
        }

        btn.addEventListener('click', function (e) {
            e.preventDefault();
            overlay.classList.contains('is-open') ? close() : open();
        });

        if (closeBtn) {
            closeBtn.addEventListener('click', close);
        }

        overlay.addEventListener('click', close);

        if (inner) {
            inner.addEventListener('click', function (e) { e.stopPropagation(); });
        }

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && overlay.classList.contains('is-open')) close();
        });
    })();


    /* =================================================================
       CART DROPDOWN — hover / click + AJAX remove
       ================================================================= */
    (function () {
        var cart = document.getElementById('headshopCart');
        if (!cart) return;

        var dd      = document.getElementById('cartDropdown');
        var link    = cart.querySelector('.headshop-cart__link');
        var countEl = cart.querySelector('.headshop-cart__count');
        var timer   = null;

        function show() {
            if (timer) { clearTimeout(timer); timer = null; }
            if (dd) dd.style.display = 'block';
        }

        function hide() { if (dd) dd.style.display = 'none'; }

        function scheduleHide() {
            if (timer) clearTimeout(timer);
            timer = setTimeout(hide, 350);
        }

        function refreshCartState() {
            if (typeof headshopAjax === 'undefined') return;

            var body = new URLSearchParams();
            body.append('action', 'headshop_cart_refresh');
            body.append('_ajax_nonce', headshopAjax.nonce);

            fetch(headshopAjax.url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
                body: body.toString(),
                credentials: 'same-origin',
            })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (!res || !res.success) return;
                var inner = dd ? dd.querySelector('.headshop-cart__dropdown-inner') : null;
                if (inner) inner.innerHTML = res.data.html;
                if (countEl) countEl.textContent = String(res.data.count || 0);
            });
        }

        if (link) {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                if (dd) dd.style.display = (dd.style.display === 'none' || dd.style.display === '') ? 'block' : 'none';
            });
        }

        cart.addEventListener('mouseenter', show);
        cart.addEventListener('mouseleave', scheduleHide);
        if (dd) {
            dd.addEventListener('mouseenter', show);
            dd.addEventListener('mouseleave', scheduleHide);
        }

        // AJAX remove
        if (dd && typeof headshopAjax !== 'undefined') {
            dd.addEventListener('click', function (e) {
                var remove = e.target.closest('.cart-remove');
                if (!remove) return;
                e.preventDefault();

                var key  = remove.getAttribute('data-cart-item-key');
                var body = new URLSearchParams();
                body.append('action', 'headshop_cart_remove');
                body.append('_ajax_nonce', headshopAjax.nonce);
                body.append('cart_item_key', key);

                fetch(headshopAjax.url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
                    body: body.toString(),
                    credentials: 'same-origin',
                })
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    if (res && res.success) {
                        var inner = dd.querySelector('.headshop-cart__dropdown-inner');
                        if (inner) inner.innerHTML = res.data.html;
                        if (countEl) countEl.textContent = String(res.data.count || 0);
                        show();
                    }
                });
            });
        }

        // Sync count/dropdown after WooCommerce AJAX add/remove events
        if (typeof jQuery !== 'undefined') {
            jQuery(document.body).on('added_to_cart removed_from_cart wc_fragments_refreshed', function () {
                refreshCartState();
            });
        }
    })();

}); // jQuery End
