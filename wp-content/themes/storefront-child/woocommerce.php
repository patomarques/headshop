<?php
/**
 * WooCommerce Compatibility File
 *
 * @package Storefront_Child
 * @version 1.0.0
 */

// Evitar acesso direto
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Verificar se o WooCommerce está ativo
 */
function storefront_child_woocommerce_support() {
    add_theme_support('woocommerce', array(
        'thumbnail_image_width' => 300,
        'single_image_width' => 600,
        'product_grid' => array(
            'default_rows' => 3,
            'min_rows' => 2,
            'max_rows' => 8,
            'default_columns' => 4,
            'min_columns' => 2,
            'max_columns' => 5,
        ),
    ));
    
    add_theme_support('wc-product-gallery-zoom');
    add_theme_support('wc-product-gallery-lightbox');
    add_theme_support('wc-product-gallery-slider');
}
add_action('after_setup_theme', 'storefront_child_woocommerce_support');

/**
 * Remover estilos padrão do WooCommerce
 */
function storefront_child_woocommerce_scripts() {
    wp_dequeue_style('woocommerce-general');
    wp_dequeue_style('woocommerce-layout');
    wp_dequeue_style('woocommerce-smallscreen');
}
add_action('wp_enqueue_scripts', 'storefront_child_woocommerce_scripts', 20);

/**
 * Adicionar estilos customizados do WooCommerce
 */
function storefront_child_woocommerce_custom_styles() {
    wp_enqueue_style('storefront-child-woocommerce', 
        get_stylesheet_directory_uri() . '/assets/css/woocommerce.css',
        array('storefront-child-style'),
        wp_get_theme()->get('Version')
    );
}
add_action('wp_enqueue_scripts', 'storefront_child_woocommerce_custom_styles');

/**
 * Personalizar o número de produtos por página
 */
function storefront_child_products_per_page() {
    return 12;
}
add_filter('loop_shop_per_page', 'storefront_child_products_per_page');

/**
 * Personalizar o número de colunas na loja
 */
function storefront_child_shop_columns() {
    return 4;
}
add_filter('loop_shop_columns', 'storefront_child_shop_columns');

/**
 * Remover breadcrumbs padrão do WooCommerce
 */
remove_action('woocommerce_before_main_content', 'woocommerce_breadcrumb', 20);

/**
 * Adicionar breadcrumbs customizados
 */
function storefront_child_woocommerce_breadcrumbs() {
    if (is_woocommerce() && !is_shop()) {
        woocommerce_breadcrumb(array(
            'delimiter' => ' / ',
            'wrap_before' => '<nav class="woocommerce-breadcrumb storefront-child-breadcrumbs">',
            'wrap_after' => '</nav>',
            'before' => '<span>',
            'after' => '</span>',
            'home' => __('Início', 'storefront-child'),
        ));
    }
}
add_action('woocommerce_before_main_content', 'storefront_child_woocommerce_breadcrumbs', 5);

/**
 * Personalizar o título da página da loja
 */
function storefront_child_woocommerce_page_title($title) {
    if (is_shop()) {
        $title = __('Nossa Loja', 'storefront-child');
    }
    return $title;
}
add_filter('woocommerce_page_title', 'storefront_child_woocommerce_page_title');

/**
 * Adicionar texto personalizado antes dos produtos
 */
function storefront_child_before_shop_loop() {
    if (is_shop()) {
        echo '<div class="storefront-child-shop-intro">';
        echo '<p>' . __('Descubra nossa seleção de produtos de alta qualidade.', 'storefront-child') . '</p>';
        echo '</div>';
    }
}
add_action('woocommerce_before_shop_loop', 'storefront_child_before_shop_loop', 5);

/**
 * Personalizar o botão "Adicionar ao Carrinho"
 */
function storefront_child_add_to_cart_text($text) {
    return __('Adicionar ao Carrinho', 'storefront-child');
}
add_filter('woocommerce_product_add_to_cart_text', 'storefront_child_add_to_cart_text');

/**
 * Personalizar mensagens do carrinho
 */
function storefront_child_add_to_cart_message($message, $product_id) {
    $product = wc_get_product($product_id);
    if ($product) {
        $message = sprintf(
            __('%s foi adicionado ao seu carrinho!', 'storefront-child'),
            $product->get_name()
        );
    }
    return $message;
}
add_filter('woocommerce_add_to_cart_message', 'storefront_child_add_to_cart_message', 10, 2);

/**
 * Personalizar o texto "Fora de Estoque"
 */
function storefront_child_out_of_stock_text($text) {
    return __('Fora de Estoque', 'storefront-child');
}
add_filter('woocommerce_get_availability_text', 'storefront_child_out_of_stock_text');

/**
 * Adicionar informações extras aos produtos
 */
function storefront_child_product_extra_info() {
    global $product;
    
    if ($product->is_on_sale()) {
        echo '<div class="product-sale-badge">';
        echo '<span class="sale-text">' . __('Promoção!', 'storefront-child') . '</span>';
        echo '</div>';
    }
    
    if ($product->is_featured()) {
        echo '<div class="product-featured-badge">';
        echo '<span class="featured-text">' . __('Destaque', 'storefront-child') . '</span>';
        echo '</div>';
    }
}
add_action('woocommerce_before_shop_loop_item_title', 'storefront_child_product_extra_info', 15);

/**
 * Personalizar o formulário de checkout
 */
function storefront_child_checkout_fields($fields) {
    // Personalizar labels
    $fields['billing']['billing_first_name']['label'] = __('Nome', 'storefront-child');
    $fields['billing']['billing_last_name']['label'] = __('Sobrenome', 'storefront-child');
    $fields['billing']['billing_email']['label'] = __('E-mail', 'storefront-child');
    $fields['billing']['billing_phone']['label'] = __('Telefone', 'storefront-child');
    
    // Personalizar placeholders
    $fields['billing']['billing_first_name']['placeholder'] = __('Digite seu nome', 'storefront-child');
    $fields['billing']['billing_last_name']['placeholder'] = __('Digite seu sobrenome', 'storefront-child');
    $fields['billing']['billing_email']['placeholder'] = __('Digite seu e-mail', 'storefront-child');
    $fields['billing']['billing_phone']['placeholder'] = __('Digite seu telefone', 'storefront-child');
    
    return $fields;
}
add_filter('woocommerce_checkout_fields', 'storefront_child_checkout_fields');

/**
 * Adicionar campos customizados ao checkout
 */
function storefront_child_add_checkout_fields($fields) {
    $fields['billing']['billing_birth_date'] = array(
        'label' => __('Data de Nascimento', 'storefront-child'),
        'placeholder' => __('DD/MM/AAAA', 'storefront-child'),
        'required' => false,
        'class' => array('form-row-wide'),
        'type' => 'date',
    );
    
    return $fields;
}
add_filter('woocommerce_checkout_fields', 'storefront_child_add_checkout_fields');

/**
 * Salvar campos customizados do checkout
 */
function storefront_child_save_checkout_fields($order_id) {
    if (!empty($_POST['billing_birth_date'])) {
        update_post_meta($order_id, 'billing_birth_date', sanitize_text_field($_POST['billing_birth_date']));
    }
}
add_action('woocommerce_checkout_update_order_meta', 'storefront_child_save_checkout_fields');

/**
 * Exibir campos customizados no admin
 */
function storefront_child_display_checkout_fields($order) {
    $birth_date = get_post_meta($order->get_id(), 'billing_birth_date', true);
    if ($birth_date) {
        echo '<p><strong>' . __('Data de Nascimento:', 'storefront-child') . '</strong> ' . $birth_date . '</p>';
    }
}
add_action('woocommerce_admin_order_data_after_billing_address', 'storefront_child_display_checkout_fields');

/**
 * Personalizar emails do WooCommerce
 */
function storefront_child_email_styles($css) {
    $css .= '
        .woocommerce-email-header {
            background-color: #e74c3c;
            color: #ffffff;
            padding: 20px;
            text-align: center;
        }
        .woocommerce-email-footer {
            background-color: #2c3e50;
            color: #ffffff;
            padding: 20px;
            text-align: center;
        }
    ';
    return $css;
}
add_filter('woocommerce_email_styles', 'storefront_child_email_styles');

/**
 * Adicionar suporte a produtos virtuais
 */
function storefront_child_virtual_product_support() {
    add_theme_support('wc-product-gallery-zoom');
    add_theme_support('wc-product-gallery-lightbox');
    add_theme_support('wc-product-gallery-slider');
}
add_action('after_setup_theme', 'storefront_child_virtual_product_support');

/**
 * Personalizar a página de conta
 */
function storefront_child_my_account_menu_items($items) {
    // Reordenar itens do menu
    $new_items = array();
    
    if (isset($items['dashboard'])) {
        $new_items['dashboard'] = $items['dashboard'];
    }
    if (isset($items['orders'])) {
        $new_items['orders'] = $items['orders'];
    }
    if (isset($items['downloads'])) {
        $new_items['downloads'] = $items['downloads'];
    }
    if (isset($items['edit-address'])) {
        $new_items['edit-address'] = $items['edit-address'];
    }
    if (isset($items['edit-account'])) {
        $new_items['edit-account'] = $items['edit-account'];
    }
    if (isset($items['customer-logout'])) {
        $new_items['customer-logout'] = $items['customer-logout'];
    }
    
    return $new_items;
}
add_filter('woocommerce_account_menu_items', 'storefront_child_my_account_menu_items');

/**
 * Adicionar suporte a produtos agrupados
 */
function storefront_child_grouped_product_support() {
    add_theme_support('wc-product-gallery-zoom');
    add_theme_support('wc-product-gallery-lightbox');
    add_theme_support('wc-product-gallery-slider');
}
add_action('after_setup_theme', 'storefront_child_grouped_product_support');

/**
 * Personalizar a página de produtos relacionados
 */
function storefront_child_related_products_args($args) {
    $args['posts_per_page'] = 4;
    $args['columns'] = 4;
    return $args;
}
add_filter('woocommerce_output_related_products_args', 'storefront_child_related_products_args');

/**
 * Adicionar suporte a produtos externos
 */
function storefront_child_external_product_support() {
    add_theme_support('wc-product-gallery-zoom');
    add_theme_support('wc-product-gallery-lightbox');
    add_theme_support('wc-product-gallery-slider');
}
add_action('after_setup_theme', 'storefront_child_external_product_support');
