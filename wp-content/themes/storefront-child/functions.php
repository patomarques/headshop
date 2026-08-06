<?php
add_action('after_setup_theme', function () {
    add_image_size('banner_mobile', 768, 500, true);
});

add_action('wp_head', function () {
    echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
    echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
}, 1);

require_once get_stylesheet_directory() . '/inc-banner-cpt.php';
require_once get_stylesheet_directory() . '/inc-google-auth.php';
require_once get_stylesheet_directory() . '/inc-cpts.php';
require_once get_stylesheet_directory() . '/inc-customizer.php';
require_once get_stylesheet_directory() . '/inc-helpers.php';
require_once get_stylesheet_directory() . '/inc-newsletter.php';

add_filter('show_admin_bar', '__return_false');

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('bootstrap-cdn', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css');
    $css_ver = filemtime( get_stylesheet_directory() . '/assets/css/main.css' );
    $js_ver  = filemtime( get_stylesheet_directory() . '/assets/js/main.js' );
    wp_enqueue_style('storefront-child-main', get_stylesheet_directory_uri() . '/assets/css/main.css', ['storefront-style', 'bootstrap-cdn'], $css_ver);
    wp_enqueue_style('google-fonts', 'https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@400;500;600;700&display=swap', [], null);
    wp_enqueue_script('bootstrap-cdn', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js', [], null, true);
    wp_enqueue_script('storefront-child-main', get_stylesheet_directory_uri() . '/assets/js/main.js', [], $js_ver, true);

    if (is_checkout()) {
        wp_enqueue_script('storefront-checkout', get_stylesheet_directory_uri() . '/assets/js/checkout.js', ['jquery'], '1.0.0', true);
        if (defined('WP_DEBUG') && WP_DEBUG) {
            wp_enqueue_script('storefront-checkout-dev', get_stylesheet_directory_uri() . '/assets/js/dev-checkout-fill.js', ['jquery'], time(), true);
        }
    }
});

add_filter('woocommerce_add_to_cart_fragments', function ($fragments) {
    $count = WC()->cart->get_cart_contents_count();
    ob_start(); ?>
    <span class="cart-count"><?php echo $count > 0 ? esc_html($count) : ''; ?></span>
    <?php $fragments['.header-cart-link .cart-count'] = ob_get_clean();
    return $fragments;
});

add_action('admin_enqueue_scripts', function ($hook) {
    if (in_array($hook, ['post.php', 'post-new.php'])) {
        $screen = get_current_screen();
        if ($screen && $screen->post_type === 'banner_home') {
            wp_enqueue_style('storefront-child-admin', get_stylesheet_directory_uri() . '/assets/css/admin.css');
        }
    }
});

add_action('widgets_init', function () {
    unregister_sidebar('sidebar-1');
    unregister_sidebar('sidebar-2');
    unregister_sidebar('homepage');
}, 20);

add_action('init', function () {
    if (get_option('eletronicos_pages_v1_created')) return;
    foreach ([
        'condicoes-de-uso'       => 'Condições de uso do site',
        'politica-de-entrega'    => 'Política de entrega',
        'trocas-e-devolucoes'    => 'Trocas e devoluções',
        'direitos-do-consumidor' => 'Direitos do consumidor',
    ] as $slug => $title) {
        if (!get_page_by_path($slug)) {
            wp_insert_post([
                'post_title'   => $title,
                'post_name'    => $slug,
                'post_status'  => 'publish',
                'post_type'    => 'page',
                'post_content' => '',
            ]);
        }
    }
    update_option('eletronicos_pages_v1_created', true);
}, 1);

add_filter('woocommerce_checkout_get_value', function ($value, $input) {
    if ($value || !is_user_logged_in()) return $value;
    $user = wp_get_current_user();
    $map  = [
        'billing_first_name' => $user->first_name,
        'billing_last_name'  => $user->last_name,
        'billing_email'      => $user->user_email,
    ];
    return $map[$input] ?? $value;
}, 10, 2);

add_filter('woocommerce_cancel_unpaid_order', function ($cancel, $order) {
    return $order->get_payment_method() === 'asaas-pix' ? false : $cancel;
}, 10, 2);

add_filter('gettext', function ($translation, $text, $domain) {
    if ($domain === 'woocommerce' && $text === 'Username or email') {
        return 'Usuário ou email';
    }
    return $translation;
}, 10, 3);

add_filter('woocommerce_account_menu_items', function ($items) {
    if (isset($items['edit-address'])) {
        $items['edit-address'] = 'Endereços';
    }
    return $items;
}, 20);


add_action('init', function () {
    remove_action('woocommerce_after_shop_loop', 'storefront_sorting_wrapper', 9);
    remove_action('woocommerce_after_shop_loop', 'woocommerce_catalog_ordering', 10);
    remove_action('woocommerce_after_shop_loop', 'woocommerce_result_count', 20);
    remove_action('woocommerce_after_shop_loop', 'storefront_sorting_wrapper_close', 31);
}, 20);

add_filter('woocommerce_breadcrumb_defaults', function ($defaults) {
    $defaults['delimiter'] = '<span class="breadcrumb-sep" aria-hidden="true">›</span>';
    return $defaults;
}, 20);

add_filter('woocommerce_checkout_registration_required', '__return_true');
add_filter('pre_option_woocommerce_enable_guest_checkout', fn() => 'no');

remove_action('woocommerce_checkout_terms_and_conditions', 'wc_checkout_privacy_policy_text', 20);

add_action('woocommerce_review_order_after_submit', function () {
    $privacy_page_id = wc_privacy_policy_page_id();
    $link = $privacy_page_id
        ? '<a href="' . esc_url(get_permalink($privacy_page_id)) . '" target="_blank">política de privacidade</a>'
        : 'política de privacidade';
    echo '<p class="checkout-privacy-notice">Seus dados pessoais serão usados para processar seu pedido, oferecer suporte à sua experiência em todo este site e para outros fins descritos em nossa ' . $link . '.</p>';
});

add_action('woocommerce_thankyou_asaas-pix', function ($order_id) {
    $order = wc_get_order($order_id);
    if (!$order || !$order->has_status(['processing', 'completed'])) return;
    $gateways = WC()->payment_gateways()->payment_gateways();
    if (!isset($gateways['asaas-pix'])) return;
    remove_action('woocommerce_thankyou_asaas-pix', [$gateways['asaas-pix'], 'append_html_to_thankyou_page'], 10);
}, 1);

add_action('woocommerce_view_order', function ($order_id) {
    $order = wc_get_order($order_id);
    if (!$order || !$order->has_status(['processing', 'completed'])) return;
    $gateways = WC()->payment_gateways()->payment_gateways();
    if (!isset($gateways['asaas-pix'])) return;
    remove_action('woocommerce_view_order', [$gateways['asaas-pix'], 'append_html_to_thankyou_page'], 10);
}, 1);

add_action('woocommerce_register_form_start', function () {
    if (!empty($_GET['email'])) {
        $prefill = sanitize_email(wp_unslash($_GET['email']));
        if (is_email($prefill)) {
            echo '<script>document.addEventListener("DOMContentLoaded",function(){var f=document.getElementById("reg_email");if(f&&!f.value)f.value=' . wp_json_encode($prefill) . ';});</script>';
        }
    }
});

add_filter( 'woocommerce_endpoint_order-received_title', '__return_empty_string' );

add_action( 'woocommerce_account_dashboard', function () {
    $current_user = wp_get_current_user();
    if ( ! $current_user->exists() ) return;

    $display_name = $current_user->first_name ?: $current_user->display_name ?: $current_user->user_login;
    $items = [
        [
            'url'   => wc_get_account_endpoint_url( 'orders' ),
            'title' => 'Meus pedidos',
            'text'  => 'Veja o status, rastreie e repita compras com facilidade.',
            'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 0 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />',
        ],
        [
            'url'   => wc_get_account_endpoint_url( 'edit-address' ),
            'title' => 'Endereços',
            'text'  => 'Atualize seu endereço de entrega e cobrança em poucos cliques.',
            'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 0 1 15 0Z" />',
        ],
        [
            'url'   => wc_get_account_endpoint_url( 'edit-account' ),
            'title' => 'Dados da conta',
            'text'  => 'Altere sua senha, nome e informações pessoais rapidamente.',
            'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />',
        ],
        [
            'url'   => wc_get_account_endpoint_url( 'downloads' ),
            'title' => 'Downloads',
            'text'  => 'Acesse seus produtos digitais sempre que precisar.',
            'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />',
        ],
        [
            'url'   => wc_get_account_endpoint_url( 'customer-logout' ),
            'title' => 'Sair',
            'text'  => 'Faça logout com segurança quando terminar.',
            'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75" />',
        ],
    ];
    ?>
    <section class="account-panel">
        <div class="account-panel__hero">
            <p class="account-panel__label">Bem-vindo de volta</p>
            <h2 class="account-panel__name">Olá, <?php echo esc_html( $display_name ); ?></h2>
            <p class="account-panel__subtitle">Seu espaço pessoal está organizado para você acompanhar pedidos, endereços e dados com mais agilidade.</p>
        </div>
        <div class="account-panel__grid">
            <?php foreach ( $items as $item ) : ?>
                <a href="<?php echo esc_url( $item['url'] ); ?>" class="account-card">
                    <div class="account-card__icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <?php echo $item['icon']; // phpcs:ignore WordPress.Security.EscapeOutput ?>
                        </svg>
                    </div>
                    <div class="account-card__body">
                        <strong class="account-card__title"><?php echo esc_html( $item['title'] ); ?></strong>
                        <span class="account-card__text"><?php echo esc_html( $item['text'] ); ?></span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </section>
    <?php
} );

add_action( 'template_redirect', function () {
    if ( is_wc_endpoint_url( 'order-received' ) ) {
        remove_action( 'storefront_page', 'storefront_page_header', 10 );
    }
} );
