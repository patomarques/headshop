// Ativa tooltips Bootstrap na footer
document.addEventListener('DOMContentLoaded', function() {
    if (!window.bootstrap || !bootstrap.Tooltip) return;
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function(el) {
        new bootstrap.Tooltip(el);
    });
});
// Remove .bg-body-tertiary from .wp-breadcrumb if present
document.addEventListener('DOMContentLoaded', function() {
    var breadcrumb = document.querySelector('.wp-breadcrumb.bg-body-tertiary');
    if (breadcrumb) {
        breadcrumb.classList.remove('bg-body-tertiary');
    }
});
/* =================================================================
   ADD TO CART — carousel cards
   ================================================================= */
(function () {
    function showToast(message, isError) {
        var toast = document.createElement('div');
        toast.className = 'headshop-toast' + (isError ? ' headshop-toast--error' : '');
        toast.textContent = message;
        document.body.appendChild(toast);

        requestAnimationFrame(function () {
            requestAnimationFrame(function () { toast.classList.add('headshop-toast--visible'); });
        });

        setTimeout(function () {
            toast.classList.remove('headshop-toast--visible');
            setTimeout(function () { toast.remove(); }, 300);
        }, 3000);
    }

    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.headshop-carousel__add-btn');
        if (!btn) return;
        if (typeof headshopAjax === 'undefined') return;

        e.preventDefault();

        var productId = btn.getAttribute('data-product_id');
        if (!productId) return;

        btn.classList.add('loading');

        var body = new URLSearchParams();
        body.append('action',      'headshop_add_to_cart');
        body.append('_ajax_nonce', headshopAjax.nonce);
        body.append('product_id',  productId);
        body.append('quantity',    '1');

        fetch(headshopAjax.url, {
            method:      'POST',
            headers:     { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
            body:        body.toString(),
            credentials: 'same-origin',
        })
        .then(function (r) { return r.json(); })
        .then(function (res) {
            btn.classList.remove('loading');

            if (res && res.success) {
                showToast(res.data.message || 'Adicionado ao carrinho!', false);

                var countEl = document.querySelector('.headshop-cart__count');
                if (countEl && res.data.count !== undefined) {
                    countEl.textContent = String(res.data.count);
                }
            } else {
                var msg = (res && res.data && res.data.message) ? res.data.message : 'Erro ao adicionar ao carrinho.';
                showToast(msg, true);
            }
        })
        .catch(function () {
            btn.classList.remove('loading');
            showToast('Erro de conexão. Tente novamente.', true);
        });
    });
})();


/* =================================================================
   PRODUCTS CAROUSEL
   ================================================================= */
document.addEventListener('DOMContentLoaded', function () {
    var mobileSearchBtn = document.getElementById('mobileSearchBtn');
    if (mobileSearchBtn) {
        mobileSearchBtn.addEventListener('click', function () {
            var offcanvasEl = document.getElementById('offcanvasMenu');
            if (offcanvasEl && window.bootstrap) {
                bootstrap.Offcanvas.getInstance(offcanvasEl)?.hide();
            }
            setTimeout(function () {
                var searchBtn = document.getElementById('searchToggleBtn');
                if (searchBtn) searchBtn.click();
            }, 320);
        });
    }
});

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.headshop-products-carousel').forEach(function (carousel) {
        var track = carousel.querySelector('.headshop-products-carousel__track');
        var prev  = carousel.querySelector('.headshop-products-carousel__btn--prev');
        var next  = carousel.querySelector('.headshop-products-carousel__btn--next');
        if (!track) return;

        // Clone all items to enable seamless infinite loop
        Array.from(track.children).forEach(function (item) {
            track.appendChild(item.cloneNode(true));
        });

        function cardWidth() {
            var card = track.firstElementChild;
            return card ? card.offsetWidth + 24 : track.offsetWidth * 0.8;
        }

        // Instant position jump without animation (used for loop reset)
        function jump(newLeft) {
            track.style.scrollSnapType = 'none';
            track.style.scrollBehavior = 'auto';
            track.scrollLeft = newLeft;
            requestAnimationFrame(function () {
                track.style.scrollBehavior = '';
                track.style.scrollSnapType = '';
            });
        }

        // Eased scroll animation (duration in ms)
        function smoothScroll(delta, duration) {
            var start     = track.scrollLeft;
            var target    = start + delta;
            var startTime = null;

            function ease(t) { return t < 0.5 ? 2 * t * t : -1 + (4 - 2 * t) * t; }

            function step(now) {
                if (!startTime) startTime = now;
                var t = Math.min(1, (now - startTime) / duration);
                track.scrollLeft = start + (target - start) * ease(t);
                if (t < 1) requestAnimationFrame(step);
            }

            requestAnimationFrame(step);
        }

        // After smooth scroll settles, reset to equivalent position in originals if in clone zone
        var scrollTimer;
        track.addEventListener('scroll', function () {
            clearTimeout(scrollTimer);
            scrollTimer = setTimeout(function () {
                var half = track.scrollWidth / 2;
                if (track.scrollLeft >= half) {
                    jump(track.scrollLeft - half);
                }
            }, 50);
        });

        function advance() {
            smoothScroll(cardWidth(), 900);
        }

        var timer = setInterval(advance, 4000);

        carousel.addEventListener('mouseenter', function () { clearInterval(timer); });
        carousel.addEventListener('mouseleave', function () { timer = setInterval(advance, 4000); });

        if (prev) prev.addEventListener('click', function () {
            if (track.scrollLeft < cardWidth()) {
                jump(track.scrollLeft + track.scrollWidth / 2);
            }
            smoothScroll(-cardWidth(), 700);
        });

        if (next) next.addEventListener('click', function () {
            smoothScroll(cardWidth(), 700);
        });
    });
});

/* =================================================================
   FULLSCREEN NAV OVERLAY (bar icon)
   ================================================================= */
(function () {
    var btn      = document.getElementById('navBarsBtn');
    var overlay  = document.getElementById('navBarsOverlay');
    var closeBtn = document.getElementById('navBarsClose');
    if (!btn || !overlay) return;

    function open() {
        overlay.classList.add('is-open');
        overlay.setAttribute('aria-hidden', 'false');
        document.body.classList.add('nav-overlay-open');
    }

    function close() {
        overlay.classList.remove('is-open');
        overlay.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('nav-overlay-open');
    }

    btn.addEventListener('click', function () {
        overlay.classList.contains('is-open') ? close() : open();
    });

    if (closeBtn) closeBtn.addEventListener('click', close);

    // Accordion: toggle subcategories on chevron click
    overlay.addEventListener('click', function (e) {
        var toggle = e.target.closest('.headshop-overlay-item__toggle');
        if (!toggle) return;

        var item = toggle.closest('.headshop-overlay-item--has-sub');
        if (!item) return;

        var sub     = item.querySelector('.headshop-overlay-item__sub');
        var isOpen  = toggle.getAttribute('aria-expanded') === 'true';

        toggle.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
        sub.style.maxHeight = isOpen ? '0' : sub.scrollHeight + 'px';
    });
})();


/* =================================================================
   HEADER SCROLL EFFECT
   - Home: fixo transparente → fixo branco após scroll > 80vh
   - Internas: sempre fixo branco
   ================================================================= */
(function () {
    var header = document.getElementById('masthead');
    if (!header) return;

    var SCROLLED = 'headshop-header--scrolled';
    var isHome   = document.body.classList.contains('home') ||
                   document.body.classList.contains('front-page');

    if (!isHome) {
        header.classList.add(SCROLLED);
        return;
    }

    function tick() {
        var y = document.body.scrollTop || window.pageYOffset || 0;
        header.classList.toggle(SCROLLED, y > window.innerHeight * 0.8);
    }

    tick();
    document.body.addEventListener('scroll', tick, { passive: true });
})();

jQuery(function () {


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

        // Hover só para mouse (pointerType evita disparar em touch)
        cart.addEventListener('pointerenter', function (e) {
            if (e.pointerType === 'mouse') show();
        });
        cart.addEventListener('pointerleave', function (e) {
            if (e.pointerType === 'mouse') scheduleHide();
        });
        if (dd) {
            dd.addEventListener('pointerenter', function (e) {
                if (e.pointerType === 'mouse') show();
            });
            dd.addEventListener('pointerleave', function (e) {
                if (e.pointerType === 'mouse') scheduleHide();
            });
        }

        if (link) {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                show();
            });
        }

        // Fechar ao clicar fora
        document.addEventListener('click', function (e) {
            if (dd && dd.style.display === 'block' && !cart.contains(e.target)) {
                hide();
            }
        });

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


    /* =================================================================
       CHECKOUT — CEP autocomplete (ViaCEP)
       ================================================================= */
    (function () {
        function digitsOnly(v) { return (v || '').replace(/\D/g, ''); }

        function fillAddress(prefix, data) {
            var fields = {
                address_1:    data.logradouro,
                neighborhood: data.bairro,
                city:         data.localidade,
            };
            Object.keys(fields).forEach(function (suffix) {
                var $el = jQuery('#' + prefix + '_' + suffix);
                if ($el.length && fields[suffix]) {
                    $el.val(fields[suffix]).trigger('change');
                }
            });

            var $state = jQuery('#' + prefix + '_state');
            if ($state.length && data.uf) {
                $state.val(data.uf).trigger('change');
            }

            var $number = jQuery('#' + prefix + '_number');
            if ($number.length && !$number.val()) $number.trigger('focus');
        }

        function lookupCep(prefix) {
            var $cep = jQuery('#' + prefix + '_postcode');
            if (!$cep.length) return;

            var cep = digitsOnly($cep.val());
            if (cep.length !== 8) return;

            $cep.addClass('headshop-cep-loading');

            fetch('https://viacep.com.br/ws/' + cep + '/json/')
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (!data.erro) fillAddress(prefix, data);
                })
                .catch(function () {})
                .then(function () { $cep.removeClass('headshop-cep-loading'); });
        }

        // Native blur/focus don't bubble, so a delegated jQuery "blur" handler
        // never fires here — trigger the lookup as soon as 8 digits are typed instead.
        jQuery(document).on('input', '#billing_postcode, #shipping_postcode', function () {
            if (digitsOnly(this.value).length === 8) {
                lookupCep(this.id.replace('_postcode', ''));
            }
        });
    })();


    /* =================================================================
       CHECKOUT — fill fictitious data (testing helper)
       Only active when WP_DEBUG is on (see headshopAjax.debug).
       ================================================================= */
    if (typeof headshopAjax !== 'undefined' && headshopAjax.debug) {
        (function () {
            var form = document.querySelector('form.woocommerce-checkout');
            if (!form) return;

            var TEST_DATA = {
                billing_first_name:  'João',
                billing_last_name:   'da Silva',
                billing_persontype:  '1', // Pessoa Física
                billing_cpf:         '529.982.247-25', // CPF de teste com dígitos válidos
                // Endereço dentro de Pernambuco — a loja só entrega nesse estado,
                // um CEP de fora não mostra opção de frete no checkout (não é bug).
                billing_postcode:    '55016-080', // dispara o autocomplete de CEP
                billing_address_1:   'Rua Tupy',
                billing_number:      '147',
                billing_neighborhood: 'Salgado',
                billing_city:        'Caruaru',
                billing_state:       'PE',
                billing_phone:       '(11) 99999-0000',
                billing_email:       'teste@example.com',
                // Cartão de teste (sandbox) — ajuste se o gateway usar outro número de teste
                asaas_cc_name:              'JOAO DA SILVA',
                asaas_cc_number:            '4111 1111 1111 1111',
                asaas_cc_expiration_month:  '12',
                asaas_cc_expiration_year:   '2030',
                asaas_cc_security_code:     '123',
            };

            function fillField(name, value) {
                var el = form.querySelector('[name="' + name + '"]');
                if (!el) return;
                el.value = value;
                el.dispatchEvent(new Event('input', { bubbles: true }));
                jQuery(el).trigger('change');
            }

            function fillAll() {
                Object.keys(TEST_DATA).forEach(function (name) {
                    fillField(name, TEST_DATA[name]);
                });
            }

            var btn = document.createElement('button');
            btn.type = 'button';
            btn.textContent = 'Preencher dados de teste';
            btn.className = 'headshop-debug-fill-btn';
            btn.addEventListener('click', fillAll);
            document.body.appendChild(btn);
        })();
    }

}); // jQuery End
