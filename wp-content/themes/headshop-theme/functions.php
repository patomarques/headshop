<?php

// Theme setup
function headshop_theme_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('woocommerce');

    register_nav_menus([
        'primary' => __('Menu Principal', 'headshop-theme')
    ]);
}
add_action('after_setup_theme', 'headshop_theme_setup');

// Enqueue assets (Tailwind CDN and Swiper)
function headshop_theme_assets() {
    // Prefer built Tailwind if present; fallback to CDN
    $built_css_path = get_template_directory() . '/assets/build/tailwind.css';
    $built_css_uri  = get_template_directory_uri() . '/assets/build/tailwind.css';
    if ( file_exists( $built_css_path ) ) {
        wp_enqueue_style('tailwindcss-built', $built_css_uri, [], filemtime($built_css_path));
    } else {
        wp_enqueue_style('tailwindcss-cdn', 'https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css', [], '2.2.19');
    }

    // Swiper for product carousel
    wp_enqueue_style('swiper', 'https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css', [], '10');
    wp_enqueue_script('swiper', 'https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js', [], '10', true);

    // Localized settings for carousels
    $carousel_settings = [
        'slidesPerView' => [
            'sm' => (int) get_theme_mod('headshop_carousel_sm', 2),
            'md' => (int) get_theme_mod('headshop_carousel_md', 3),
            'lg' => (int) get_theme_mod('headshop_carousel_lg', 4),
            'xl' => (int) get_theme_mod('headshop_carousel_xl', 5),
        ],
        'spaceBetween' => (int) get_theme_mod('headshop_carousel_space', 16),
    ];

    // Theme JS
    wp_enqueue_script('headshop-main', get_template_directory_uri() . '/assets/js/main.js', ['swiper'], '1.1.0', true);
    wp_add_inline_script('headshop-main', 'window.headshopSettings = ' . wp_json_encode($carousel_settings) . ';', 'before');
}
add_action('wp_enqueue_scripts', 'headshop_theme_assets');

// WooCommerce: adjust thumbnail sizes (optional)
add_filter('woocommerce_gallery_thumbnail_size', function () { return 'medium'; });
add_filter('woocommerce_get_image_size_thumbnail', function () { return [ 'width' => 400, 'height' => 400, 'crop' => 1 ]; });

// Register image sizes for banners/categories
add_action('after_setup_theme', function () {
    add_image_size('headshop-banner', 1920, 600, true);
    add_image_size('headshop-category', 600, 400, true);
});

// Customizer: Home settings (banner, CTA, categorias)
add_action('customize_register', function ( $wp_customize ) {
    $section = 'headshop_home';
    $wp_customize->add_section($section, [
        'title' => __('Página Inicial (Headshop)', 'headshop-theme'),
        'priority' => 30,
    ]);

    // Banner image
    $wp_customize->add_setting('headshop_banner_image', [
        'default' => '',
        'sanitize_callback' => 'esc_url_raw',
        'transport' => 'refresh',
    ]);
    $wp_customize->add_control('headshop_banner_image', [
        'label' => __('Imagem do Banner', 'headshop-theme'),
        'section' => $section,
        'settings' => 'headshop_banner_image',
        'type' => 'image',
    ]);

    // Banner title
    $wp_customize->add_setting('headshop_banner_title', [
        'default' => 'Headshop',
        'sanitize_callback' => 'sanitize_text_field',
    ]);
    $wp_customize->add_control('headshop_banner_title', [
        'label' => __('Título do Banner', 'headshop-theme'),
        'type' => 'text',
        'section' => $section,
    ]);

    // Banner subtitle
    $wp_customize->add_setting('headshop_banner_subtitle', [
        'default' => 'Tudo para sua experiência: sedas, bongs, vaporizadores e acessórios.',
        'sanitize_callback' => 'sanitize_text_field',
    ]);
    $wp_customize->add_control('headshop_banner_subtitle', [
        'label' => __('Subtítulo do Banner', 'headshop-theme'),
        'type' => 'text',
        'section' => $section,
    ]);

    // CTA
    $wp_customize->add_setting('headshop_banner_cta_text', [
        'default' => 'Ver loja',
        'sanitize_callback' => 'sanitize_text_field',
    ]);
    $wp_customize->add_control('headshop_banner_cta_text', [
        'label' => __('Texto do CTA', 'headshop-theme'),
        'type' => 'text',
        'section' => $section,
    ]);

    $wp_customize->add_setting('headshop_banner_cta_url', [
        'default' => '',
        'sanitize_callback' => 'esc_url_raw',
    ]);
    $wp_customize->add_control('headshop_banner_cta_url', [
        'label' => __('URL do CTA', 'headshop-theme'),
        'type' => 'url',
        'section' => $section,
    ]);

    // Categoria slugs (separados por vírgula)
    $wp_customize->add_setting('headshop_home_category_slugs', [
        'default' => '',
        'sanitize_callback' => function($v){ return preg_replace('/[^a-z0-9\-,]/', '', strtolower($v)); },
    ]);
    $wp_customize->add_control('headshop_home_category_slugs', [
        'label' => __('Categorias em destaque (slugs, separados por vírgula)', 'headshop-theme'),
        'type' => 'text',
        'section' => $section,
        'description' => __('Ex.: sedas,bongs,acessorios', 'headshop-theme'),
    ]);

    // Carousel settings
    $wp_customize->add_setting('headshop_carousel_sm', [ 'default' => 2, 'sanitize_callback' => 'absint' ]);
    $wp_customize->add_setting('headshop_carousel_md', [ 'default' => 3, 'sanitize_callback' => 'absint' ]);
    $wp_customize->add_setting('headshop_carousel_lg', [ 'default' => 4, 'sanitize_callback' => 'absint' ]);
    $wp_customize->add_setting('headshop_carousel_xl', [ 'default' => 5, 'sanitize_callback' => 'absint' ]);
    $wp_customize->add_setting('headshop_carousel_space', [ 'default' => 16, 'sanitize_callback' => 'absint' ]);

    $wp_customize->add_control('headshop_carousel_sm', [ 'label' => __('Slides (sm)', 'headshop-theme'), 'type' => 'number', 'section' => $section ]);
    $wp_customize->add_control('headshop_carousel_md', [ 'label' => __('Slides (md)', 'headshop-theme'), 'type' => 'number', 'section' => $section ]);
    $wp_customize->add_control('headshop_carousel_lg', [ 'label' => __('Slides (lg)', 'headshop-theme'), 'type' => 'number', 'section' => $section ]);
    $wp_customize->add_control('headshop_carousel_xl', [ 'label' => __('Slides (xl)', 'headshop-theme'), 'type' => 'number', 'section' => $section ]);
    $wp_customize->add_control('headshop_carousel_space', [ 'label' => __('Espaço entre slides (px)', 'headshop-theme'), 'type' => 'number', 'section' => $section ]);
});

// Helper: obter categorias por slugs customizados ou fallback
function headshop_get_featured_categories() {
    $slugs = trim((string) get_theme_mod('headshop_home_category_slugs', ''));
    if ($slugs !== '') {
        $slugs_arr = array_filter(array_map('trim', explode(',', $slugs)));
        if (!empty($slugs_arr)) {
            $terms = get_terms([
                'taxonomy' => 'product_cat',
                'hide_empty' => true,
                'slug' => $slugs_arr,
            ]);
            if (!is_wp_error($terms) && !empty($terms)) {
                return $terms;
            }
        }
    }
    // fallback: top categories
    $fallback = get_terms([
        'taxonomy' => 'product_cat',
        'hide_empty' => true,
        'number' => 8,
    ]);
    return is_wp_error($fallback) ? [] : $fallback;
}


