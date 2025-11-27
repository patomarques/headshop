<?php
/**
 * Storefront Child Theme Functions
 *
 * @package Storefront_Child
 * @version 1.0.0
 */

// Evitar acesso direto
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Configuração do tema filho
 */
function storefront_child_setup() {
    // Carregar textdomain para traduções
    load_child_theme_textdomain('storefront-child', get_stylesheet_directory() . '/languages');

    // Adicionar suporte a recursos do WordPress
    add_theme_support('post-thumbnails');
    add_theme_support('html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
    ));

    // Adicionar suporte ao WooCommerce
    add_theme_support('woocommerce');
    add_theme_support('wc-product-gallery-zoom');
    add_theme_support('wc-product-gallery-lightbox');
    add_theme_support('wc-product-gallery-slider');
}
add_action('after_setup_theme', 'storefront_child_setup');

/**
 * Enfileirar estilos e scripts do tema filho
 */
function storefront_child_enqueue_styles() {
    wp_enqueue_style( 'storefront-parent-style', get_template_directory_uri() . '/style.css' );
    wp_enqueue_style( 'storefront-child-style', get_stylesheet_uri() );
}
add_action( 'wp_enqueue_scripts', 'storefront_child_enqueue_styles' );
function storefront_child_scripts() {
    // Enfileirar estilos do tema pai
    wp_enqueue_style('storefront-style', get_template_directory_uri() . '/style.css');

    // Enfileirar estilos do tema filho
    wp_enqueue_style('storefront-child-style',
        get_stylesheet_directory_uri() . '/style.css',
        array('storefront-style'),
        wp_get_theme()->get('Version')
    );

    // Enfileirar scripts customizados
    wp_enqueue_script('storefront-child-script',
        get_stylesheet_directory_uri() . '/assets/js/child-theme.js',
        array('jquery'),
        wp_get_theme()->get('Version'),
        true
    );

    // Localizar script para AJAX
    wp_localize_script('storefront-child-script', 'storefront_child_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('storefront_child_nonce'),
        'loading_text' => __('Carregando...', 'storefront-child'),
        'error_text' => __('Ocorreu um erro. Tente novamente.', 'storefront-child'),
    ));
}
add_action('wp_enqueue_scripts', 'storefront_child_scripts');

/**
 * Personalizar o logo do site
 */
function storefront_child_custom_logo() {
    $logo_id = get_theme_mod('custom_logo');
    if ($logo_id) {
        $logo = wp_get_attachment_image_src($logo_id, 'full');
        if ($logo) {
            echo '<style type="text/css">';
            echo '.site-header .site-branding .custom-logo { max-height: 60px; width: auto; }';
            echo '</style>';
        }
    }
}
add_action('wp_head', 'storefront_child_custom_logo');

/**
 * Adicionar classes customizadas ao body
 */
function storefront_child_body_classes($classes) {
    // Adicionar classe para identificar o tema filho
    $classes[] = 'storefront-child-theme';

    // Adicionar classe baseada na página atual
    if (is_woocommerce()) {
        $classes[] = 'woocommerce-page';
    }

    if (is_cart()) {
        $classes[] = 'woocommerce-cart-page';
    }

    if (is_checkout()) {
        $classes[] = 'woocommerce-checkout-page';
    }

    return $classes;
}
add_filter('body_class', 'storefront_child_body_classes');

/**
 * Personalizar o texto do rodapé
 */
function storefront_child_footer_text() {
    $footer_text = sprintf(
        __('© %s %s. Todos os direitos reservados. Desenvolvido com %s', 'storefront-child'),
        date('Y'),
        get_bloginfo('name'),
        '<span class="heart">♥</span>'
    );

    return $footer_text;
}

/**
 * Adicionar widgets customizados
 */
function storefront_child_widgets_init() {
    // Área de widget customizada para o rodapé
    register_sidebar(array(
        'name' => __('Área Customizada do Rodapé', 'storefront-child'),
        'id' => 'custom-footer-widget',
        'description' => __('Adicione widgets aqui para aparecerem na área customizada do rodapé.', 'storefront-child'),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget' => '</div>',
        'before_title' => '<h3 class="widget-title">',
        'after_title' => '</h3>',
    ));

    // Área de widget para página de produtos
    register_sidebar(array(
        'name' => __('Sidebar de Produtos', 'storefront-child'),
        'id' => 'products-sidebar',
        'description' => __('Adicione widgets aqui para aparecerem na sidebar das páginas de produtos.', 'storefront-child'),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget' => '</div>',
        'before_title' => '<h3 class="widget-title">',
        'after_title' => '</h3>',
    ));
}
add_action('widgets_init', 'storefront_child_widgets_init');

/**
 * Personalizar o menu de navegação
 */
function storefront_child_nav_menu_args($args) {
    // Adicionar classes customizadas ao menu
    if (isset($args['theme_location']) && $args['theme_location'] === 'primary') {
        $args['menu_class'] = 'nav-menu custom-nav-menu';
        $args['container_class'] = 'custom-nav-container';
    }

    return $args;
}
add_filter('wp_nav_menu_args', 'storefront_child_nav_menu_args');

/**
 * Personalizar o loop de produtos do WooCommerce
 */
function storefront_child_woocommerce_loop_product_title() {
    echo '<h2 class="woocommerce-loop-product__title custom-product-title">' . get_the_title() . '</h2>';
}
remove_action('woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title', 10);
add_action('woocommerce_shop_loop_item_title', 'storefront_child_woocommerce_loop_product_title', 10);

/**
 * Adicionar botão customizado aos produtos
 */
function storefront_child_add_custom_button() {
    global $product;

    if ($product && $product->is_purchasable()) {
        echo '<div class="custom-product-actions">';
        echo '<a href="' . esc_url($product->get_permalink()) . '" class="button custom-view-button">';
        echo __('Ver Detalhes', 'storefront-child');
        echo '</a>';
        echo '</div>';
    }
}
add_action('woocommerce_after_shop_loop_item', 'storefront_child_add_custom_button', 15);

/**
 * Personalizar mensagens do WooCommerce
 */
function storefront_child_woocommerce_messages() {
    // Personalizar mensagens de sucesso
    add_filter('woocommerce_add_to_cart_message', function($message, $product_id) {
        $product = wc_get_product($product_id);
        if ($product) {
            $message = sprintf(
                __('%s foi adicionado ao seu carrinho!', 'storefront-child'),
                $product->get_name()
            );
        }
        return $message;
    }, 10, 2);
}
add_action('init', 'storefront_child_woocommerce_messages');

/**
 * Adicionar suporte a cores customizadas
 */
function storefront_child_customize_register($wp_customize) {
    // Seção de cores customizadas
    $wp_customize->add_section('storefront_child_colors', array(
        'title' => __('Cores Customizadas', 'storefront-child'),
        'description' => __('Personalize as cores do seu site.', 'storefront-child'),
        'priority' => 30,
    ));

    // Cor primária
    $wp_customize->add_setting('storefront_child_primary_color', array(
        'default' => '#e74c3c',
        'sanitize_callback' => 'sanitize_hex_color',
    ));

    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'storefront_child_primary_color', array(
        'label' => __('Cor Primária', 'storefront-child'),
        'section' => 'storefront_child_colors',
        'settings' => 'storefront_child_primary_color',
    )));

    // Cor secundária
    $wp_customize->add_setting('storefront_child_secondary_color', array(
        'default' => '#2c3e50',
        'sanitize_callback' => 'sanitize_hex_color',
    ));

    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'storefront_child_secondary_color', array(
        'label' => __('Cor Secundária', 'storefront-child'),
        'section' => 'storefront_child_colors',
        'settings' => 'storefront_child_secondary_color',
    )));
}
add_action('customize_register', 'storefront_child_customize_register');

/**
 * Aplicar cores customizadas
 */
function storefront_child_custom_colors() {
    $primary_color = get_theme_mod('storefront_child_primary_color', '#e74c3c');
    $secondary_color = get_theme_mod('storefront_child_secondary_color', '#2c3e50');

    if ($primary_color !== '#e74c3c' || $secondary_color !== '#2c3e50') {
        echo '<style type="text/css">';
        echo ':root {';
        echo '--primary-color: ' . esc_attr($primary_color) . ';';
        echo '--secondary-color: ' . esc_attr($secondary_color) . ';';
        echo '}';
        echo '</style>';
    }
}
add_action('wp_head', 'storefront_child_custom_colors');

/**
 * Adicionar suporte a SEO
 */
function storefront_child_seo_meta() {
    if (is_single() || is_page()) {
        global $post;

        $description = '';
        if (has_excerpt($post->ID)) {
            $description = get_the_excerpt($post->ID);
        } else {
            $description = wp_trim_words($post->post_content, 20);
        }

        if ($description) {
            echo '<meta name="description" content="' . esc_attr($description) . '">' . "\n";
        }
    }
}
add_action('wp_head', 'storefront_child_seo_meta');

/**
 * Otimizar performance
 */
function storefront_child_optimize_performance() {
    // Remover emojis se não necessário
    if (!is_admin()) {
        remove_action('wp_head', 'print_emoji_detection_script', 7);
        remove_action('wp_print_styles', 'print_emoji_styles');
    }

    // Remover versão do WordPress do head
    remove_action('wp_head', 'wp_generator');

    // Remover links desnecessários
    remove_action('wp_head', 'wlwmanifest_link');
    remove_action('wp_head', 'rsd_link');
}
add_action('init', 'storefront_child_optimize_performance');

/**
 * Adicionar suporte a lazy loading
 */
function storefront_child_lazy_loading($content) {
    if (is_admin() || is_feed() || is_preview()) {
        return $content;
    }

    // Adicionar loading="lazy" às imagens
    $content = preg_replace('/<img(.*?)src=/', '<img$1loading="lazy" src=', $content);

    return $content;
}
add_filter('the_content', 'storefront_child_lazy_loading');

/**
 * Personalizar o título da página
 */
function storefront_child_document_title_parts($title) {
    if (is_woocommerce()) {
        $title['title'] = get_bloginfo('name') . ' - ' . __('Loja Online', 'storefront-child');
    }

    return $title;
}
add_filter('document_title_parts', 'storefront_child_document_title_parts');

/**
 * Remover título/entry-header na Home
 * - Remove o page header da Storefront via hook
 * - Garante via CSS que o .entry-header não apareça na página inicial
 */
function storefront_child_hide_home_entry_header_setup() {
    if (is_front_page()) {
        // Remove o header padrão da Storefront antes do conteúdo
        remove_action('storefront_before_content', 'storefront_page_header', 10);
        // Adiciona CSS para esconder qualquer header gerado por templates de página
        add_action('wp_head', function () {
            echo '<style>.home .entry-header{display:none!important}.home .entry-title{display:none!important}</style>';
        });
    }
}
add_action('wp', 'storefront_child_hide_home_entry_header_setup');

/**
 * Adicionar breadcrumbs customizados
 */
function storefront_child_breadcrumbs() {
    if (function_exists('woocommerce_breadcrumb')) {
        woocommerce_breadcrumb(array(
            'delimiter' => ' / ',
            'wrap_before' => '<nav class="woocommerce-breadcrumb custom-breadcrumbs">',
            'wrap_after' => '</nav>',
            'before' => '<span>',
            'after' => '</span>',
            'home' => __('Início', 'storefront-child'),
        ));
    }
}

/**
 * Hook para adicionar breadcrumbs
 */
function storefront_child_add_breadcrumbs() {
    if (is_woocommerce() && !is_shop()) {
        add_action('woocommerce_before_main_content', 'storefront_child_breadcrumbs', 5);
    }
}
add_action('template_redirect', 'storefront_child_add_breadcrumbs');

/**
 * Incluir arquivo de verificação do tema
 */
require_once get_stylesheet_directory() . '/VERIFICACAO.php';

/**
 * Remover título "Shop by Category" das categorias de produtos
 */
function storefront_child_remove_category_title( $args ) {
    $args['title'] = '';
    return $args;
}
add_filter( 'storefront_product_categories_args', 'storefront_child_remove_category_title' );

/**
 * Sobrescrever a função de busca do Storefront para criar busca com ícone
 */
function storefront_product_search() {
    if (storefront_is_woocommerce_activated()) {
        ?>
        <div class="site-search custom-search">
            <div class="search-toggle">
                <button type="button" class="search-icon" aria-label="<?php esc_attr_e('Abrir busca', 'storefront-child'); ?>">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M21 21L16.514 16.506L21 21ZM19 10.5C19 15.194 15.194 19 10.5 19C5.806 19 2 15.194 2 10.5C2 5.806 5.806 2 10.5 2C15.194 2 19 5.806 19 10.5Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>
            <div class="search-form-container" style="display: none;">
                <form role="search" method="get" class="woocommerce-product-search" action="<?php echo esc_url(home_url('/')); ?>">
                    <label class="screen-reader-text" for="woocommerce-product-search-field-0"><?php esc_html_e('Buscar produtos:', 'storefront-child'); ?></label>
                    <input type="search" id="woocommerce-product-search-field-0" class="search-field" placeholder="<?php esc_attr_e('Buscar produtos...', 'storefront-child'); ?>" value="<?php echo get_search_query(); ?>" name="s" />
                    <button type="submit" value="<?php esc_attr_e('Buscar', 'storefront-child'); ?>" class="search-submit">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M21 21L16.514 16.506L21 21ZM19 10.5C19 15.194 15.194 19 10.5 19C5.806 19 2 15.194 2 10.5C2 5.806 5.806 2 10.5 2C15.194 2 19 5.806 19 10.5Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                    <input type="hidden" name="post_type" value="product" />
                    <button type="button" class="search-close" aria-label="<?php esc_attr_e('Fechar busca', 'storefront-child'); ?>">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
        <?php
    }
}
