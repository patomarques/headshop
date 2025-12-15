<?php
// Minimal Storefront child theme functions

if (!defined('ABSPATH')) { exit; }
add_action('wp_enqueue_scripts', function() {
    // Bootstrap CSS (needed for markup classes used in header mini-cart)
    wp_enqueue_style(
        'storefront-child-bootstrap',
        'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css',
        [],
        '5.3.3'
    );

    // Enqueue parent theme stylesheet first
    wp_enqueue_style('storefront-parent-style', get_template_directory_uri() . '/style.css', [], null);
    // Then enqueue child theme stylesheet
    wp_enqueue_style('storefront-child-style', get_stylesheet_uri(), ['storefront-parent-style'], null);
    // Ensure WordPress dashicons available on frontend for icons
    wp_enqueue_style('dashicons');

    // Script handle for inline header/cart toggles
    wp_register_script( 'storefront-child-scripts', '', [], null, true );
    wp_enqueue_script( 'storefront-child-scripts' );
    // Enqueue compiled Sass output if present
    $compiled_rel = '/assets/css/main.css';
    $compiled_path = get_stylesheet_directory() . $compiled_rel;
    $compiled_url  = get_stylesheet_directory_uri() . $compiled_rel;
    if ( file_exists( $compiled_path ) ) {
        $ver = @filemtime( $compiled_path ) ?: null;
        wp_enqueue_style( 'storefront-child-compiled', $compiled_url, ['storefront-child-style'], $ver );
    } else {
        // Optional: warn in console to remind compiling SCSS
        wp_add_inline_script( 'storefront-child-scripts', "console.warn('Storefront Child: assets/css/main.css not found. Run npm run build.');" );
    }
});
/**
 * Use Bootstrap classes to make checkout full-width aligned with menu
 */
add_filter('woocommerce_checkout_form_class', 'storefront_child_checkout_form_class');

/**
 * Header search: hide input, show search icon next to cart, toggle on click
 */
function storefront_child_customize_header_search_setup() {
    // Remove Storefront's default header search output if present
    remove_action( 'storefront_header', 'storefront_product_search', 40 );
}
add_action( 'init', 'storefront_child_customize_header_search_setup' );

// Output a search icon next to the cart and a hidden search form
function storefront_child_header_search_icon() {
    echo '<div class="header-search-toggle d-inline-block align-middle" style="margin-left:8px;">
            <button id="searchToggleBtn" class="p-0" aria-label="Search" title="Pesquisar" type="button" style="background:transparent;border:0;">
                <span class="dashicons dashicons-search" style="font-size:20px;line-height:1;"></span>
            </button>
          </div>';
}
// Place search icon near cart (Storefront uses hook priority 60 for cart)
add_action( 'storefront_header', 'storefront_child_header_search_icon', 62 );

// Render hidden search form just below the header
function storefront_child_header_search_form() {
    // Hidden container for the search form (product search if WooCommerce active)
    echo '<div id="headerSearchContainer" class="container" style="display:none;">
            <div class="row justify-content-center">
              <div class="col-12 col-md-8">
                <div class="py-2">';
    if ( function_exists( 'get_product_search_form' ) ) {
        get_product_search_form();
    } else {
        get_search_form();
    }
    echo '        </div>
              </div>
            </div>
          </div>';
}
add_action( 'storefront_before_content', 'storefront_child_header_search_form', 5 );

// Enqueue minimal script to toggle the search form
function storefront_child_enqueue_search_toggle_script() {
    $script = "document.addEventListener('DOMContentLoaded',function(){var btn=document.getElementById('searchToggleBtn');var box=document.getElementById('headerSearchContainer');if(btn&&box){btn.addEventListener('click',function(){var s=box.style.display==='none' || box.style.display==='';box.style.display=s?'block':'none';if(box.style.display==='block'){var input=box.querySelector('input[type=search],input[type=text]');if(input){input.focus();}}});}});";
    wp_add_inline_script( 'storefront-child-scripts', $script );
    // Basic styles to align icons and hide default search spacing
    $css = '#masthead .site-header-cart+.header-search-toggle{margin-left:8px;} #headerSearchContainer .search-form, #headerSearchContainer .woocommerce-product-search{width:100%;} .header-search-toggle button{background:transparent;border:0;}';
    wp_add_inline_style( 'storefront-child-style', $css );
}
add_action( 'wp_enqueue_scripts', 'storefront_child_enqueue_search_toggle_script', 20 );

/**
 * Header cart: icon + item count, dropdown mini-cart on hover/click
 */
function storefront_child_replace_header_cart_setup() {
    // Remove default Storefront header cart to inject our custom
    remove_action( 'storefront_header', 'storefront_header_cart', 60 );
}
add_action( 'init', 'storefront_child_replace_header_cart_setup' );

/**
 * Remove Storefront sidebar (#secondary) on Cart and Checkout pages.
 */
add_action( 'wp', function () {
    if ( function_exists( 'is_cart' ) && ( is_cart() || is_checkout() ) ) {
        remove_action( 'storefront_sidebar', 'storefront_get_sidebar', 10 );
    }
} );

function storefront_child_header_cart() {
    if ( ! class_exists( 'WooCommerce' ) ) { return; }
    $count = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
    $cart_url = wc_get_cart_url();
    $nonce = wp_create_nonce( 'storefront_child_cart' );
    echo '<div class="site-header-cart custom-header-cart d-inline-block position-relative" id="customHeaderCart">';
    echo '  <a class="cart-icon-link d-inline-flex align-items-center" href="' . esc_url( $cart_url ) . '" aria-label="Carrinho">';
    echo '    <span class="dashicons dashicons-cart" style="font-size:20px;line-height:1;"></span>';
    echo '    <span class="cart-count badge bg-dark ms-1">' . intval( $count ) . '</span>';
    echo '  </a>';
    // Dropdown container with mini-cart
    echo '  <div class="cart-dropdown position-absolute end-0 top-100 mt-2" id="cartDropdown" style="display:none;" data-ajax-url="' . esc_url( admin_url( 'admin-ajax.php' ) ) . '" data-nonce="' . esc_attr( $nonce ) . '">';
    echo '    <div class="cart-dropdown-inner p-0">';
    storefront_child_render_cart_dropdown_inner();
    echo '    </div>';
    echo '  </div>';
    echo '</div>';
}
add_action( 'storefront_header', 'storefront_child_header_cart', 60 );

function storefront_child_render_cart_dropdown_inner() {
    if ( ! class_exists( 'WooCommerce' ) || ! WC()->cart ) {
        echo '<div class="p-4">Carrinho indisponível.</div>';
        return;
    }

    $items = WC()->cart->get_cart();
    if ( empty( $items ) ) {
        echo '<div class="p-4 text-center">Seu carrinho está vazio.</div>';
        return;
    }

    $saved_total = 0.0;

    $cart_url     = wc_get_cart_url();
    $checkout_url = wc_get_checkout_url();

    echo '<div class="p-3">';

    foreach ( $items as $key => $cart_item ) {
        $product = $cart_item['data'] ?? null;
        if ( ! $product || ! $product->exists() ) {
            continue;
        }

        $product_name = $product->get_name();
        $qty          = (int) ( $cart_item['quantity'] ?? 1 );

        $thumb_src = '';
        $thumb_id  = $product->get_image_id();
        if ( $thumb_id ) {
            $thumb_src = wp_get_attachment_image_url( $thumb_id, 'thumbnail' ) ?: '';
        }
        if ( ! $thumb_src ) {
            $thumb_src = wc_placeholder_img_src( 'thumbnail' );
        }

        $price_now     = (float) $product->get_price();
        $price_regular = (float) $product->get_regular_price();
        $line_now      = $price_now * $qty;
        $line_regular  = ( $price_regular > 0 ? $price_regular : $price_now ) * $qty;
        $saved_total  += max( 0, $line_regular - $line_now );

        echo '  <div class="d-flex gap-3 align-items-start py-3 border-bottom">';
        echo '    <img class="cart-thumb border rounded-2 flex-shrink-0" src="' . esc_url( $thumb_src ) . '" alt="" width="56" height="56" loading="lazy" />';
        echo '    <div class="flex-grow-1">';
        echo '      <div class="cart-item-title fw-semibold">' . esc_html( $product_name ) . '</div>';
        $meta = wc_get_formatted_cart_item_data( $cart_item );
        if ( $meta ) {
            echo '    <div class="cart-item-meta text-muted small">' . wp_kses_post( $meta ) . '</div>';
        }
        echo '      <div class="mt-2">';
        echo '        <span class="cart-price-now fw-semibold">' . wp_kses_post( wc_price( $line_now ) ) . '</span>';
        if ( $line_regular > $line_now ) {
            echo '      <span class="cart-price-regular text-muted text-decoration-line-through ms-2">' . wp_kses_post( wc_price( $line_regular ) ) . '</span>';
        }
        echo '      </div>';
        echo '    </div>';
        echo '    <div class="text-end ms-2">';
        echo '      <div class="small text-muted">' . intval( $qty ) . '×</div>';
        echo '      <button type="button" class="btn btn-link p-0 mt-2 small text-muted cart-remove" data-cart-item-key="' . esc_attr( $key ) . '">Remover</button>';
        echo '    </div>';
        echo '  </div>';
    }

    // Total row (full total incl. coupons/discounts)
    $total_html = WC()->cart->get_total();
    echo '  <div class="d-flex justify-content-between fw-semibold cart-total-row pt-3">';
    echo '    <span>Total</span>';
    echo '    <span>' . wp_kses_post( $total_html ) . '</span>';
    echo '  </div>';
    $coupon_discount = (float) WC()->cart->get_discount_total() + (float) WC()->cart->get_discount_tax();
    $economy = $coupon_discount > 0 ? $coupon_discount : $saved_total;
    if ( $economy > 0 ) {
        echo '  <div class="text-muted small mt-1">Você economizou ' . wp_kses_post( wc_price( $economy ) ) . '!</div>';
    }

    // Action buttons
    echo '  <div class="mt-3 d-grid gap-2">';
    echo '    <a class="btn btn-lg cart-action-btn cart-action-cart fw-bold text-uppercase d-flex align-items-center justify-content-center gap-2" href="' . esc_url( $cart_url ) . '">';
    echo '      <span class="dashicons dashicons-cart"></span> Ver carrinho';
    echo '    </a>';
    echo '    <a class="btn btn-lg cart-action-btn cart-action-checkout fw-bold text-uppercase d-flex align-items-center justify-content-center gap-2" href="' . esc_url( $checkout_url ) . '">';
    echo '      <span class="dashicons dashicons-lock"></span> Finalizar compra';
    echo '    </a>';
    echo '  </div>';

    echo '</div>';
}

function storefront_child_cart_toggle_script() {
        $script = "document.addEventListener('DOMContentLoaded',function(){
    var cart=document.getElementById('customHeaderCart');
    if(!cart) return;
    var dd=document.getElementById('cartDropdown');
    var link=cart.querySelector('.cart-icon-link');
    var countEl=cart.querySelector('.cart-count');

    var hideTimer=null;

    function show(){
        if(hideTimer){ clearTimeout(hideTimer); hideTimer=null; }
        if(dd){ dd.style.display='block'; }
    }
    function hide(){ if(dd){ dd.style.display='none'; } }
    function scheduleHide(){
        if(hideTimer){ clearTimeout(hideTimer); }
        hideTimer=setTimeout(hide, 350);
    }

    if(link){
        link.addEventListener('click',function(e){
            e.preventDefault();
            if(dd){ dd.style.display=(dd.style.display==='none'||dd.style.display==='')?'block':'none'; }
        });
    }
    cart.addEventListener('mouseenter',show);
    cart.addEventListener('mouseleave',scheduleHide);
    if(dd){
        dd.addEventListener('mouseenter',show);
        dd.addEventListener('mouseleave',scheduleHide);
    }

    function ajaxPost(action, data){
        if(!dd) return Promise.reject('missing dropdown');
        var url=dd.getAttribute('data-ajax-url');
        var nonce=dd.getAttribute('data-nonce');
        var body=new URLSearchParams();
        body.append('action', action);
        body.append('_ajax_nonce', nonce);
        Object.keys(data||{}).forEach(function(k){ body.append(k, data[k]); });
        return fetch(url,{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded; charset=UTF-8'},body:body.toString(),credentials:'same-origin'})
            .then(function(r){return r.json();});
    }

    function replaceInner(html){
        var inner=dd ? dd.querySelector('.cart-dropdown-inner') : null;
        if(inner){ inner.innerHTML=html; }
    }
    function setCount(c){ if(countEl){ countEl.textContent=String(c||0); } }

    // Delegate clicks for qty +/- and remove
    if(dd){
        dd.addEventListener('click',function(e){
            var remove=e.target.closest('.cart-remove');

            if(remove){
                e.preventDefault();
                var key2=remove.getAttribute('data-cart-item-key');
                ajaxPost('storefront_child_cart_remove_item',{cart_item_key:key2})
                    .then(function(res){
                        if(res && res.success){
                            replaceInner(res.data.html);
                            setCount(res.data.count);
                            show();
                        }
                    });
                return;
            }
        });
    }
});";
    wp_add_inline_script( 'storefront-child-scripts', $script );
    $css = '.custom-header-cart .cart-count{font-size:12px; line-height:1;}.custom-header-cart .widget_shopping_cart_content{max-height:360px; overflow:auto;}';
    wp_add_inline_style( 'storefront-child-style', $css );
}
add_action( 'wp_enqueue_scripts', 'storefront_child_cart_toggle_script', 21 );

function storefront_child_cart_ajax_check() {
        if ( ! class_exists( 'WooCommerce' ) || ! WC()->cart ) {
                wp_send_json_error( array( 'message' => 'WooCommerce cart unavailable' ) );
        }
}

function storefront_child_cart_ajax_render() {
        ob_start();
        storefront_child_render_cart_dropdown_inner();
        return ob_get_clean();
}

function storefront_child_cart_remove_item_ajax() {
        check_ajax_referer( 'storefront_child_cart' );
        storefront_child_cart_ajax_check();

        $key = isset( $_POST['cart_item_key'] ) ? wc_clean( wp_unslash( $_POST['cart_item_key'] ) ) : '';
        if ( ! $key || ! isset( WC()->cart->get_cart()[ $key ] ) ) {
                wp_send_json_error( array( 'message' => 'Invalid cart item' ) );
        }

        WC()->cart->remove_cart_item( $key );
        WC()->cart->calculate_totals();

        wp_send_json_success(
                array(
                        'count' => WC()->cart->get_cart_contents_count(),
                        'html'  => storefront_child_cart_ajax_render(),
                )
        );
}
add_action( 'wp_ajax_storefront_child_cart_remove_item', 'storefront_child_cart_remove_item_ajax' );
add_action( 'wp_ajax_nopriv_storefront_child_cart_remove_item', 'storefront_child_cart_remove_item_ajax' );
