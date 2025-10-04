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
    // Force Tailwind CSS to load with high priority
    if ( file_exists( $built_css_path ) ) {
        wp_enqueue_style('tailwindcss-built', $built_css_uri, [], filemtime($built_css_path), 'all');
    } else {
        // Use unpkg CDN which has better MIME type handling
        wp_enqueue_style('tailwindcss-cdn', 'https://unpkg.com/tailwindcss@3.4.0/dist/tailwind.min.css', [], '3.4.0-v5', 'all');
    }
    
    // Add !important to critical Tailwind classes to override plugin conflicts
    $critical_css = '
        .container { max-width: 1280px !important; margin-left: auto !important; margin-right: auto !important; padding-left: 1rem !important; padding-right: 1rem !important; }
        .mx-auto { margin-left: auto !important; margin-right: auto !important; }
        .px-4 { padding-left: 1rem !important; padding-right: 1rem !important; }
        .py-8 { padding-top: 2rem !important; padding-bottom: 2rem !important; }
        .bg-white { background-color: #ffffff !important; }
        .text-gray-900 { color: #111827 !important; }
        .rounded-2xl { border-radius: 1rem !important; }
        .shadow-lg { box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05) !important; }
        .hover\\:shadow-xl:hover { box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04) !important; }
        .transition-all { transition-property: all !important; transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1) !important; transition-duration: 150ms !important; }
        .duration-300 { transition-duration: 300ms !important; }
        .hover\\:scale-105:hover { transform: scale(1.05) !important; }
    ';
    
    wp_add_inline_style('tailwindcss-cdn', $critical_css);
    wp_add_inline_style('tailwindcss-built', $critical_css);

    // Swiper for product carousel
    wp_enqueue_style('swiper', 'https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css', [], '10');
    wp_enqueue_script('swiper', 'https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js', [], '10', true);
    
    // Lightbox for product gallery
    wp_enqueue_style('lightbox', 'https://cdn.jsdelivr.net/npm/lightbox2@2.11.4/dist/css/lightbox.min.css', [], '2.11.4');
    wp_enqueue_script('lightbox', 'https://cdn.jsdelivr.net/npm/lightbox2@2.11.4/dist/js/lightbox.min.js', ['jquery'], '2.11.4', true);

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
    
    // Add custom CSS for better styling
    $custom_css = '
        /* Line clamp utility */
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        /* Prose styling for content */
        .prose {
            color: #374151;
            line-height: 1.75;
        }
        
        .prose h1, .prose h2, .prose h3, .prose h4 {
            color: #111827;
            font-weight: 600;
            margin-top: 2rem;
            margin-bottom: 1rem;
        }
        
        .prose p {
            margin-bottom: 1.25rem;
        }
        
        .prose ul, .prose ol {
            margin-bottom: 1.25rem;
            padding-left: 1.5rem;
        }
        
        .prose li {
            margin-bottom: 0.5rem;
        }
        
        /* WooCommerce specific styles */
        .woocommerce-result-count {
            color: #6b7280;
            font-size: 0.875rem;
        }
        
        .woocommerce-ordering select {
            padding: 0.5rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            background-color: white;
            font-size: 0.875rem;
        }
        
        .woocommerce-pagination {
            margin-top: 2rem;
        }
        
        .woocommerce-pagination .page-numbers {
            display: inline-block;
            padding: 0.5rem 0.75rem;
            margin: 0 0.25rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            color: #374151;
            text-decoration: none;
            transition: all 0.2s;
        }
        
        .woocommerce-pagination .page-numbers:hover,
        .woocommerce-pagination .page-numbers.current {
            background-color: #059669;
            color: white;
            border-color: #059669;
        }
        
        /* Breadcrumb styling */
        .woocommerce-breadcrumb {
            margin-bottom: 1.5rem;
        }
        
        .woocommerce-breadcrumb a {
            color: #059669;
            text-decoration: none;
        }
        
        .woocommerce-breadcrumb a:hover {
            color: #047857;
            text-decoration: underline;
        }
        
        /* Form styling */
        .woocommerce form .form-row {
            margin-bottom: 1rem;
        }
        
        .woocommerce form .form-row label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #374151;
        }
        
        .woocommerce form input[type="text"],
        .woocommerce form input[type="email"],
        .woocommerce form input[type="password"],
        .woocommerce form input[type="number"],
        .woocommerce form select,
        .woocommerce form textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            font-size: 0.875rem;
        }
        
        .woocommerce form input:focus,
        .woocommerce form select:focus,
        .woocommerce form textarea:focus {
            outline: none;
            border-color: #059669;
            box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.1);
        }
        
        /* Button styling */
        .woocommerce .button,
        .woocommerce button.button,
        .woocommerce input.button {
            background-color: #059669;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 0.375rem;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        
        .woocommerce .button:hover,
        .woocommerce button.button:hover,
        .woocommerce input.button:hover {
            background-color: #047857;
        }
        
        /* Price styling */
        .woocommerce .price {
            color: #059669;
            font-weight: 700;
        }
        
        .woocommerce .price del {
            color: #9ca3af;
            font-weight: 400;
        }
        
        /* Star rating */
        .woocommerce .star-rating {
            color: #fbbf24;
        }
        
        /* Notice styling */
        .woocommerce-message,
        .woocommerce-info,
        .woocommerce-error {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 0.375rem;
            border-left: 4px solid;
        }
        
        .woocommerce-message {
            background-color: #ecfdf5;
            border-left-color: #059669;
            color: #065f46;
        }
        
        .woocommerce-info {
            background-color: #eff6ff;
            border-left-color: #2563eb;
            color: #1e40af;
        }
        
        .woocommerce-error {
            background-color: #fef2f2;
            border-left-color: #dc2626;
            color: #991b1b;
        }
        
        /* Mobile menu animation */
        #mobile-menu {
            transition: all 0.3s ease-in-out;
        }
        
        /* Smooth scrolling */
        html {
            scroll-behavior: smooth;
        }
        
        /* Focus styles for accessibility */
        button:focus,
        a:focus,
        input:focus,
        select:focus,
        textarea:focus {
            outline: 2px solid #059669;
            outline-offset: 2px;
        }
        
        /* WooCommerce Product Grid */
        .woocommerce ul.products {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
            list-style: none;
            margin: 0;
            padding: 0;
        }
        
        @media (min-width: 768px) {
            .woocommerce ul.products {
                grid-template-columns: repeat(3, 1fr);
            }
        }
        
        @media (min-width: 1024px) {
            .woocommerce ul.products {
                grid-template-columns: repeat(4, 1fr);
            }
        }
        
        .woocommerce ul.products li.product {
            margin: 0;
            padding: 0;
            width: auto;
            float: none;
        }
        
        /* Custom add to cart button styling */
        .woocommerce ul.products li.product .button {
            display: block;
            width: 100%;
            text-align: center;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            font-weight: 500;
            margin-top: 0;
        }
        
        /* Hide default WooCommerce styling that might interfere */
        .woocommerce ul.products li.product .woocommerce-loop-product__link {
            display: block;
        }
        
        .woocommerce ul.products li.product .woocommerce-loop-product__title {
            font-size: inherit;
            margin: 0;
            padding: 0;
        }
        
        .woocommerce ul.products li.product .price {
            margin: 0;
        }
    ';
    
    // Add custom CSS to both possible handles
    wp_add_inline_style('tailwindcss-cdn', $custom_css);
    wp_add_inline_style('tailwindcss-built', $custom_css);
}
add_action('wp_enqueue_scripts', 'headshop_theme_assets', 20);

// Remove only problematic styles, keep WooCommerce functionality
function headshop_remove_conflicting_styles() {
    // Remove only WordPress block styles that might conflict
    wp_dequeue_style('wp-block-library');
    wp_dequeue_style('wp-block-library-theme');
}
add_action('wp_enqueue_scripts', 'headshop_remove_conflicting_styles', 100);

// WooCommerce: adjust thumbnail sizes (optional)
add_filter('woocommerce_gallery_thumbnail_size', function () { return 'medium'; });
add_filter('woocommerce_get_image_size_thumbnail', function () { return [ 'width' => 400, 'height' => 400, 'crop' => 1 ]; });

// Register image sizes for banners/categories
add_action('after_setup_theme', function () {
    add_image_size('headshop-banner', 1920, 600, true);
    add_image_size('headshop-category', 600, 400, true);
    // Product gallery sizes
    add_image_size('headshop-product-thumb', 300, 400, true);
    add_image_size('headshop-product-medium', 600, 800, true);
    add_image_size('headshop-product-large', 1200, 1600, true);
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

// Product gallery with 5 images (main + 4 additional)
function headshop_get_product_gallery($product_id, $limit = 5) {
    if (!class_exists('WooCommerce')) return [];
    
    $product = wc_get_product($product_id);
    if (!$product) return [];
    
    $gallery = [];
    
    // Main product image
    $main_image_id = $product->get_image_id();
    if ($main_image_id) {
        $gallery[] = [
            'id' => $main_image_id,
            'url' => wp_get_attachment_image_url($main_image_id, 'headshop-product-large'),
            'thumb' => wp_get_attachment_image_url($main_image_id, 'headshop-product-thumb'),
            'alt' => get_post_meta($main_image_id, '_wp_attachment_image_alt', true) ?: $product->get_name(),
            'title' => get_the_title($main_image_id) ?: $product->get_name(),
        ];
    }
    
    // Gallery images
    $gallery_ids = $product->get_gallery_image_ids();
    $remaining = $limit - count($gallery);
    
    if ($remaining > 0 && !empty($gallery_ids)) {
        $gallery_ids = array_slice($gallery_ids, 0, $remaining);
        foreach ($gallery_ids as $img_id) {
            $gallery[] = [
                'id' => $img_id,
                'url' => wp_get_attachment_image_url($img_id, 'headshop-product-large'),
                'thumb' => wp_get_attachment_image_url($img_id, 'headshop-product-thumb'),
                'alt' => get_post_meta($img_id, '_wp_attachment_image_alt', true) ?: $product->get_name(),
                'title' => get_the_title($img_id) ?: $product->get_name(),
            ];
        }
    }
    
    // If no images at all, add a category-specific placeholder as the first image
    if (empty($gallery)) {
        $gallery[] = [
            'id' => 0,
            'url' => headshop_get_category_placeholder($product_id, 600, 600),
            'thumb' => headshop_get_category_placeholder($product_id, 300, 300),
            'alt' => 'Imagem do produto',
            'title' => 'Imagem do produto',
        ];
    }
    
    // Fill remaining slots with category-specific placeholders to ensure visuals
    while (count($gallery) < $limit) {
        $gallery[] = [
            'id' => 0,
            'url' => headshop_get_category_placeholder($product_id, 600, 600),
            'thumb' => headshop_get_category_placeholder($product_id, 300, 300),
            'alt' => 'Imagem do produto',
            'title' => $product->get_name(),
        ];
    }
    
    return $gallery;
}

// Generate category-specific placeholder images
function headshop_get_category_placeholder($product_id, $width = 400, $height = 400) {
    $product = wc_get_product($product_id);
    if (!$product) return headshop_get_placeholder_image($width, $height);
    
    $categories = wp_get_post_terms($product_id, 'product_cat');
    $product_name = strtolower($product->get_name());
    
    // Determine product type based on categories and name
    $icon_path = '';
    $bg_color = '#f3f4f6';
    $text = 'Produto';
    
    // Check categories first
    if (!empty($categories)) {
        foreach ($categories as $category) {
            $cat_name = strtolower($category->name);
            if (strpos($cat_name, 'bong') !== false || strpos($cat_name, 'water') !== false) {
                return headshop_generate_realistic_product_image($product->get_name(), $category->name, $width, $height);
            }
            if (strpos($cat_name, 'vapor') !== false || strpos($cat_name, 'vape') !== false) {
                return headshop_generate_realistic_product_image($product->get_name(), $category->name, $width, $height);
            }
            if (strpos($cat_name, 'seda') !== false || strpos($cat_name, 'paper') !== false) {
                return headshop_generate_realistic_product_image($product->get_name(), $category->name, $width, $height);
            }
            if (strpos($cat_name, 'pipe') !== false) {
                return headshop_generate_realistic_product_image($product->get_name(), $category->name, $width, $height);
            }
            if (strpos($cat_name, 'grind') !== false || strpos($cat_name, 'moedor') !== false) {
                return headshop_generate_realistic_product_image($product->get_name(), $category->name, $width, $height);
            }
        }
    }
    
    // Check product name if no category match
    if (strpos($product_name, 'bong') !== false) {
        return headshop_generate_realistic_product_image($product->get_name(), '', $width, $height);
    }
    if (strpos($product_name, 'vapor') !== false || strpos($product_name, 'vape') !== false) {
        return headshop_generate_realistic_product_image($product->get_name(), '', $width, $height);
    }
    if (strpos($product_name, 'seda') !== false || strpos($product_name, 'paper') !== false) {
        return headshop_generate_realistic_product_image($product->get_name(), '', $width, $height);
    }
    if (strpos($product_name, 'pipe') !== false) {
        return headshop_generate_realistic_product_image($product->get_name(), '', $width, $height);
    }
    if (strpos($product_name, 'grind') !== false || strpos($product_name, 'moedor') !== false) {
        return headshop_generate_realistic_product_image($product->get_name(), '', $width, $height);
    }
    
    // Default generic placeholder
    return headshop_get_placeholder_image($width, $height, 'Produto');
}

// Generate realistic product images using external services
function headshop_generate_realistic_product_image($product_name, $category, $width = 400, $height = 400) {
    // This function would integrate with AI image generation services
    // For now, we'll create more detailed placeholders
    
    $search_terms = [
        'bong' => 'glass water pipe smoking',
        'vaporizer' => 'electronic vaporizer device',
        'vape' => 'vape pen electronic cigarette',
        'seda' => 'rolling papers cigarette',
        'paper' => 'rolling papers tobacco',
        'pipe' => 'smoking pipe tobacco',
        'grinder' => 'herb grinder metal',
        'moedor' => 'grinder smoking accessories'
    ];
    
    $search_term = 'smoking accessories';
    foreach ($search_terms as $key => $term) {
        if (strpos(strtolower($product_name), $key) !== false || strpos(strtolower($category), $key) !== false) {
            $search_term = $term;
            break;
        }
    }
    
    // Determine product type and colors based on search terms
    $type = 'bong';
    $bg_color = '#f3f4f6';
    $text = 'Produto';
    
    if (strpos($search_term, 'vaporizer') !== false || strpos($search_term, 'vape') !== false) {
        $type = 'vaporizer';
        $bg_color = '#f3e8ff';
        $text = 'Vaporizador';
    } elseif (strpos($search_term, 'rolling papers') !== false || strpos($search_term, 'cigarette') !== false) {
        $type = 'papers';
        $bg_color = '#fef3c7';
        $text = 'Sedas';
    } elseif (strpos($search_term, 'pipe') !== false) {
        $type = 'pipe';
        $bg_color = '#fde68a';
        $text = 'Pipe';
    } elseif (strpos($search_term, 'grinder') !== false) {
        $type = 'grinder';
        $bg_color = '#d1fae5';
        $text = 'Grinder';
    } elseif (strpos($search_term, 'water pipe') !== false) {
        $type = 'bong';
        $bg_color = '#e0f2fe';
        $text = 'Bong';
    }
    
    // For now, return a more detailed placeholder
    // In a real implementation, this would call an AI service
    return headshop_create_detailed_placeholder($width, $height, $type, $bg_color, $text, $search_term);
}

// Create more detailed placeholder with product-specific elements
function headshop_create_detailed_placeholder($width, $height, $type, $bg_color, $text, $search_term = '') {
    $icon_size = min(80, $width * 0.2);
    $font_size = min(18, $width * 0.045);
    
    $icons = [
        'bong' => '<path d="M8 2C8 1.45 8.45 1 9 1h6c.55 0 1 .45 1 1v2h2c1.1 0 2 .9 2 2v10c0 1.1-.9 2-2 2H6c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2h2V2zm2 2h4V3h-4v1zm-4 3v9h12V7H6zm2 2h8v5H8V9z"/>',
        'vaporizer' => '<path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.94-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v-.07zM17.9 17.39c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/>',
        'papers' => '<path d="M14 2H6c-1.1 0-2 .9-2 2v16c0 1.1.89 2 2 2h12c1.1 0 2-.9 2-2V8l-6-6zm4 18H6V4h7v5h5v11z"/><path d="M8 12h8v2H8zm0 4h8v2H8zm0-8h5v2H8z"/>',
        'pipe' => '<path d="M18.5 2c-.83 0-1.5.67-1.5 1.5 0 .83.67 1.5 1.5 1.5s1.5-.67 1.5-1.5c0-.83-.67-1.5-1.5-1.5zM16 6c-2.21 0-4 1.79-4 4 0 .89.29 1.71.78 2.38L6.5 18.5c-.39.39-.39 1.02 0 1.41.39.39 1.02.39 1.41 0l6.12-6.12C14.71 13.29 15.11 13 16 13c2.21 0 4-1.79 4-4s-1.79-4-4-4z"/>',
        'grinder' => '<path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/><path d="M12 6c-3.31 0-6 2.69-6 6s2.69 6 6 6 6-2.69 6-6-2.69-6-6-6zm0 10c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4z"/><circle cx="12" cy="12" r="2"/>'
    ];
    
    $icon = isset($icons[$type]) ? $icons[$type] : $icons['bong'];
    
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $width . '" height="' . $height . '" viewBox="0 0 ' . $width . ' ' . $height . '">
        <defs>
            <linearGradient id="bg-' . $type . '" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" style="stop-color:' . $bg_color . ';stop-opacity:1" />
                <stop offset="100%" style="stop-color:' . $bg_color . ';stop-opacity:0.8" />
            </linearGradient>
            <filter id="shadow" x="-50%" y="-50%" width="200%" height="200%">
                <feDropShadow dx="2" dy="2" stdDeviation="3" flood-color="#000000" flood-opacity="0.3"/>
            </filter>
        </defs>
        <rect width="100%" height="100%" fill="url(#bg-' . $type . ')" stroke="#e5e7eb" stroke-width="2" rx="12"/>
        
        <!-- Product shadow -->
        <ellipse cx="' . ($width/2) . '" cy="' . ($height - 20) . '" rx="' . ($width * 0.3) . '" ry="8" fill="#000000" opacity="0.1"/>
        
        <!-- Main product icon -->
        <g transform="translate(' . ($width/2) . ',' . ($height/2 - 30) . ')" filter="url(#shadow)">
            <g transform="translate(' . (-$icon_size/2) . ',' . (-$icon_size/2) . ')">
                <svg width="' . $icon_size . '" height="' . $icon_size . '" fill="#374151" viewBox="0 0 24 24">
                    ' . $icon . '
                </svg>
            </g>
        </g>
        
        <!-- Product name -->
        <text x="50%" y="' . ($height/2 + $font_size + 20) . '" text-anchor="middle" fill="#374151" font-family="-apple-system, BlinkMacSystemFont, Segoe UI, Roboto, sans-serif" font-size="' . $font_size . '" font-weight="600">' . htmlspecialchars($text) . '</text>
        
        <!-- Search term hint -->
        <text x="50%" y="' . ($height/2 + $font_size + 45) . '" text-anchor="middle" fill="#6b7280" font-family="-apple-system, BlinkMacSystemFont, Segoe UI, Roboto, sans-serif" font-size="' . ($font_size * 0.7) . '" font-weight="400">Buscar: ' . htmlspecialchars($search_term) . '</text>
        
        <!-- Quality indicator -->
        <circle cx="' . ($width - 20) . '" cy="20" r="8" fill="#10b981" opacity="0.8"/>
        <text x="' . ($width - 20) . '" y="25" text-anchor="middle" fill="white" font-family="Arial, sans-serif" font-size="10" font-weight="bold">HD</text>
    </svg>';
    
    return 'data:image/svg+xml;base64,' . base64_encode($svg);
}

// Create specific placeholder for product types
function headshop_create_specific_placeholder($width, $height, $type, $bg_color, $text) {
    $icon_size = min(64, $width * 0.16);
    $font_size = min(16, $width * 0.04);
    
    $icons = [
        'bong' => '<path d="M8 2C8 1.45 8.45 1 9 1h6c.55 0 1 .45 1 1v2h2c1.1 0 2 .9 2 2v10c0 1.1-.9 2-2 2H6c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2h2V2zm2 2h4V3h-4v1zm-4 3v9h12V7H6zm2 2h8v5H8V9z"/>',
        'vaporizer' => '<path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.94-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v-.07zM17.9 17.39c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/>',
        'papers' => '<path d="M14 2H6c-1.1 0-2 .9-2 2v16c0 1.1.89 2 2 2h12c1.1 0 2-.9 2-2V8l-6-6zm4 18H6V4h7v5h5v11z"/><path d="M8 12h8v2H8zm0 4h8v2H8zm0-8h5v2H8z"/>',
        'pipe' => '<path d="M18.5 2c-.83 0-1.5.67-1.5 1.5 0 .83.67 1.5 1.5 1.5s1.5-.67 1.5-1.5c0-.83-.67-1.5-1.5-1.5zM16 6c-2.21 0-4 1.79-4 4 0 .89.29 1.71.78 2.38L6.5 18.5c-.39.39-.39 1.02 0 1.41.39.39 1.02.39 1.41 0l6.12-6.12C14.71 13.29 15.11 13 16 13c2.21 0 4-1.79 4-4s-1.79-4-4-4z"/>',
        'grinder' => '<path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/><path d="M12 6c-3.31 0-6 2.69-6 6s2.69 6 6 6 6-2.69 6-6-2.69-6-6-6zm0 10c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4z"/><circle cx="12" cy="12" r="2"/>'
    ];
    
    $icon = isset($icons[$type]) ? $icons[$type] : $icons['bong'];
    
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $width . '" height="' . $height . '" viewBox="0 0 ' . $width . ' ' . $height . '">
        <defs>
            <linearGradient id="bg-' . $type . '" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" style="stop-color:' . $bg_color . ';stop-opacity:1" />
                <stop offset="100%" style="stop-color:' . $bg_color . ';stop-opacity:0.8" />
            </linearGradient>
        </defs>
        <rect width="100%" height="100%" fill="url(#bg-' . $type . ')" stroke="#e5e7eb" stroke-width="2" rx="8"/>
        <g transform="translate(' . ($width/2) . ',' . ($height/2 - $font_size) . ')">
            <g transform="translate(' . (-$icon_size/2) . ',' . (-$icon_size/2) . ')">
                <svg width="' . $icon_size . '" height="' . $icon_size . '" fill="#374151" viewBox="0 0 24 24">
                    ' . $icon . '
                </svg>
            </g>
        </g>
        <text x="50%" y="' . ($height/2 + $font_size + 15) . '" text-anchor="middle" fill="#374151" font-family="-apple-system, BlinkMacSystemFont, Segoe UI, Roboto, sans-serif" font-size="' . $font_size . '" font-weight="600">' . htmlspecialchars($text) . '</text>
    </svg>';
    
    return 'data:image/svg+xml;base64,' . base64_encode($svg);
}

// Generate placeholder image URL
function headshop_get_placeholder_image($width = 400, $height = 400, $text = 'Sem imagem') {
    // Calculate responsive icon and text sizes
    $icon_size = min(48, $width * 0.12);
    $font_size = min(14, $width * 0.035);
    
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $width . '" height="' . $height . '" viewBox="0 0 ' . $width . ' ' . $height . '">
        <defs>
            <linearGradient id="bg" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" style="stop-color:#f9fafb;stop-opacity:1" />
                <stop offset="100%" style="stop-color:#f3f4f6;stop-opacity:1" />
            </linearGradient>
        </defs>
        <rect width="100%" height="100%" fill="url(#bg)" stroke="#e5e7eb" stroke-width="1"/>
        <g transform="translate(' . ($width/2) . ',' . ($height/2 - $font_size) . ')">
            <g transform="translate(' . (-$icon_size/2) . ',' . (-$icon_size/2) . ')">
                <svg width="' . $icon_size . '" height="' . $icon_size . '" fill="#9ca3af" viewBox="0 0 24 24">
                    <path d="M4 4h16v12l-4-4-4 4-4-4-4 4V4zm16-2H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z"/>
                    <circle cx="8.5" cy="8.5" r="1.5"/>
                </svg>
            </g>
        </g>
        <text x="50%" y="' . ($height/2 + $font_size + 10) . '" text-anchor="middle" fill="#6b7280" font-family="-apple-system, BlinkMacSystemFont, Segoe UI, Roboto, sans-serif" font-size="' . $font_size . '" font-weight="500">' . htmlspecialchars($text) . '</text>
    </svg>';
    
    return 'data:image/svg+xml;base64,' . base64_encode($svg);
}

// Responsive image with lazy loading
function headshop_responsive_image($image, $class = '', $lazy = true, $product_id = null) {
    // If no image or invalid image, return placeholder
    if (empty($image['url']) || $image['id'] <= 0) {
        // Try to get category-specific placeholder if product_id is available
        if ($product_id) {
            $placeholder_url = headshop_get_category_placeholder($product_id, 400, 400);
        } else {
            $placeholder_url = headshop_get_placeholder_image(400, 400);
        }
        
        $loading = $lazy ? 'loading="lazy"' : '';
        
        return sprintf(
            '<img src="%s" alt="Imagem do produto" class="%s" %s decoding="async">',
            esc_url($placeholder_url),
            esc_attr($class),
            $loading
        );
    }
    
    $loading = $lazy ? 'loading="lazy"' : '';
    $srcset = '';
    
    // Generate srcset for different sizes
    if ($image['id'] && $image['id'] > 0) {
        $small = wp_get_attachment_image_url($image['id'], 'headshop-product-thumb');
        $medium = wp_get_attachment_image_url($image['id'], 'headshop-product-medium');
        $large = wp_get_attachment_image_url($image['id'], 'headshop-product-large');
        
        if ($small && $medium && $large) {
            $srcset = sprintf(
                '%s 300w, %s 600w, %s 1200w',
                esc_url($small),
                esc_url($medium),
                esc_url($large)
            );
        }
    }
    
    return sprintf(
        '<img src="%s" %s alt="%s" title="%s" class="%s" sizes="(max-width: 640px) 300px, (max-width: 1024px) 600px, 1200px" %s>',
        esc_url($image['url']),
        $srcset ? 'srcset="' . esc_attr($srcset) . '"' : '',
        esc_attr($image['alt']),
        esc_attr($image['title']),
        esc_attr($class),
        $loading
    );
}

// Auto-assign stock images to products (optimized)
function headshop_assign_stock_images($product_id, $count = 4) {
    if (!class_exists('WooCommerce')) return false;
    
    $product = wc_get_product($product_id);
    if (!$product) return false;
    
    $product_name = $product->get_name();
    $existing_gallery = $product->get_gallery_image_ids();
    
    // Skip if product already has enough gallery images
    if (count($existing_gallery) >= $count) return true;
    
    $assigned_images = [];
    $attempts = 0;
    $max_attempts = $count * 2; // Reduced from 3 to 2 attempts per image
    
    // Define search terms based on product name/category
    $search_terms = headshop_get_image_search_terms($product);
    
    for ($i = 1; $i <= $count && $attempts < $max_attempts; $i++) {
        $attempts++;
        $search_term = $search_terms[($i - 1) % count($search_terms)];
        
        error_log('Headshop: Searching for image ' . $i . ' with term: "' . $search_term . '" for product: ' . $product_name);
        
        $image_url = headshop_get_stock_image($search_term, 1200, 1600);
        
        if ($image_url) {
            error_log('Headshop: Found image URL: ' . $image_url);
            $attachment_id = headshop_download_and_attach_image($image_url, $product_name . ' - ' . $search_term . ' ' . $i);
            if ($attachment_id) {
                $assigned_images[] = $attachment_id;
                error_log('Headshop: Successfully assigned image ' . $i . ' for product: ' . $product_name . ' (ID: ' . $attachment_id . ')');
            } else {
                error_log('Headshop: Failed to download/attach image for product: ' . $product_name);
                $i--; // Retry this image
            }
        } else {
            error_log('Headshop: Failed to get stock image for product: ' . $product_name . ' with term: "' . $search_term . '" (attempt ' . $attempts . ')');
            $i--; // Retry this image
        }
        
        // Reduced delay to speed up processing
        usleep(250000); // 0.25 seconds instead of 0.5
    }
    
    // Add assigned images to product gallery
    if (!empty($assigned_images)) {
        $current_gallery = $product->get_gallery_image_ids();
        $new_gallery = array_merge($current_gallery, $assigned_images);
        $product->set_gallery_image_ids($new_gallery);
        $product->save();
        
        error_log('Headshop: Added ' . count($assigned_images) . ' images to product: ' . $product_name);
        return count($assigned_images);
    }
    
    error_log('Headshop: No images were assigned to product: ' . $product_name);
    return false;
}

// Get search terms based on product name and category (improved)
function headshop_get_image_search_terms($product) {
    $terms = [];
    $product_name = strtolower($product->get_name());
    $categories = wp_get_post_terms($product->get_id(), 'product_cat');
    
    // Extract keywords from product name
    $name_keywords = headshop_extract_keywords($product_name);
    $terms = array_merge($terms, $name_keywords);
    
    // Add category-based terms
    if (!is_wp_error($categories) && !empty($categories)) {
        foreach ($categories as $category) {
            $cat_name = strtolower($category->name);
            $cat_keywords = headshop_extract_keywords($cat_name);
            $terms = array_merge($terms, $cat_keywords);
        }
    }
    
    // Add specific product type terms based on keywords found
    $terms = headshop_add_specific_terms($terms, $product_name);
    
    // Remove duplicates and empty values
    $terms = array_filter(array_unique($terms));
    
    // If no specific terms found, use generic smoking-related terms
    if (empty($terms)) {
        $terms = ['smoking', 'tobacco', 'cigarette', 'pipe'];
    }
    
    return $terms;
}

// Extract meaningful keywords from product name
function headshop_extract_keywords($text) {
    $keywords = [];
    
    // Common smoking/headshop related words to look for
    $smoking_terms = [
        'bong', 'water pipe', 'pipe', 'vaporizer', 'vape', 'vapor',
        'rolling papers', 'papers', 'sedas', 'cigarette', 'cigar',
        'tobacco', 'smoking', 'smoke', 'ash', 'ashtray', 'lighter',
        'grinder', 'grind', 'grinding', 'filter', 'tip', 'tips',
        'cone', 'cones', 'blunt', 'blunts', 'joint', 'joints',
        'hookah', 'shisha', 'hookah', 'bubbler', 'dab', 'dabbing',
        'wax', 'concentrate', 'concentrates', 'oil', 'oils',
        'glass', 'ceramic', 'wood', 'metal', 'silicon', 'silicone',
        'portable', 'desktop', 'electric', 'electronic', 'battery',
        'rechargeable', 'usb', 'wireless', 'bluetooth'
    ];
    
    // Check for each term in the product name
    foreach ($smoking_terms as $term) {
        if (strpos($text, $term) !== false) {
            $keywords[] = $term;
        }
    }
    
    // Extract individual words (2+ characters) that might be relevant
    $words = preg_split('/[\s\-\_]+/', $text);
    foreach ($words as $word) {
        $word = trim($word);
        if (strlen($word) >= 3 && !is_numeric($word)) {
            // Skip common words
            $common_words = ['the', 'and', 'or', 'for', 'with', 'from', 'this', 'that', 'are', 'was', 'were', 'been', 'have', 'has', 'had', 'will', 'would', 'could', 'should', 'may', 'might', 'can', 'must', 'shall', 'do', 'does', 'did', 'done', 'get', 'got', 'give', 'gave', 'take', 'took', 'make', 'made', 'come', 'came', 'go', 'went', 'see', 'saw', 'know', 'knew', 'think', 'thought', 'look', 'looked', 'want', 'wanted', 'need', 'needed', 'use', 'used', 'find', 'found', 'work', 'worked', 'call', 'called', 'try', 'tried', 'ask', 'asked', 'feel', 'felt', 'leave', 'left', 'put', 'put', 'mean', 'meant', 'keep', 'kept', 'let', 'let', 'begin', 'began', 'seem', 'seemed', 'help', 'helped', 'talk', 'talked', 'turn', 'turned', 'start', 'started', 'show', 'showed', 'hear', 'heard', 'play', 'played', 'run', 'ran', 'move', 'moved', 'live', 'lived', 'believe', 'believed', 'hold', 'held', 'bring', 'brought', 'happen', 'happened', 'write', 'wrote', 'provide', 'provided', 'sit', 'sat', 'stand', 'stood', 'lose', 'lost', 'pay', 'paid', 'meet', 'met', 'include', 'included', 'continue', 'continued', 'set', 'set', 'learn', 'learned', 'change', 'changed', 'lead', 'led', 'understand', 'understood', 'watch', 'watched', 'follow', 'followed', 'stop', 'stopped', 'create', 'created', 'speak', 'spoke', 'read', 'read', 'allow', 'allowed', 'add', 'added', 'spend', 'spent', 'grow', 'grew', 'open', 'opened', 'walk', 'walked', 'win', 'won', 'offer', 'offered', 'remember', 'remembered', 'love', 'loved', 'consider', 'considered', 'appear', 'appeared', 'buy', 'bought', 'wait', 'waited', 'serve', 'served', 'die', 'died', 'send', 'sent', 'expect', 'expected', 'build', 'built', 'stay', 'stayed', 'fall', 'fell', 'cut', 'cut', 'reach', 'reached', 'kill', 'killed', 'remain', 'remained', 'suggest', 'suggested', 'raise', 'raised', 'pass', 'passed', 'sell', 'sold', 'require', 'required', 'report', 'reported', 'decide', 'decided', 'pull', 'pulled'];
            
            if (!in_array($word, $common_words)) {
                $keywords[] = $word;
            }
        }
    }
    
    return $keywords;
}

// Add specific search terms based on keywords found
function headshop_add_specific_terms($terms, $product_name) {
    $specific_terms = [];
    
    // Bong/Water pipe related
    if (headshop_contains_any($product_name, ['bong', 'water pipe', 'bubbler'])) {
        $specific_terms = array_merge($specific_terms, [
            'bong water pipe glass smoking',
            'water pipe smoking device',
            'glass bong smoking',
            'bong smoking accessories'
        ]);
    }
    
    // Vaporizer related
    if (headshop_contains_any($product_name, ['vaporizer', 'vape', 'vapor', 'electronic'])) {
        $specific_terms = array_merge($specific_terms, [
            'vaporizer electronic cigarette',
            'vape pen electronic smoking',
            'portable vaporizer device',
            'electronic smoking device'
        ]);
    }
    
    // Rolling papers related
    if (headshop_contains_any($product_name, ['papers', 'sedas', 'rolling', 'cigarette'])) {
        $specific_terms = array_merge($specific_terms, [
            'rolling papers cigarette tobacco',
            'cigarette papers smoking',
            'tobacco rolling papers',
            'smoking papers cigarette'
        ]);
    }
    
    // Pipe related
    if (headshop_contains_any($product_name, ['pipe', 'smoking pipe'])) {
        $specific_terms = array_merge($specific_terms, [
            'smoking pipe tobacco',
            'wooden pipe smoking',
            'glass pipe smoking',
            'tobacco pipe smoking'
        ]);
    }
    
    // Grinder related
    if (headshop_contains_any($product_name, ['grinder', 'grind', 'grinding'])) {
        $specific_terms = array_merge($specific_terms, [
            'herb grinder smoking',
            'grinder smoking accessories',
            'metal grinder smoking',
            'grinding device smoking'
        ]);
    }
    
    // Hookah related
    if (headshop_contains_any($product_name, ['hookah', 'shisha'])) {
        $specific_terms = array_merge($specific_terms, [
            'hookah shisha smoking',
            'hookah pipe smoking',
            'shisha smoking device',
            'hookah smoking accessories'
        ]);
    }
    
    // Lighter related
    if (headshop_contains_any($product_name, ['lighter', 'lighter', 'fire', 'flame'])) {
        $specific_terms = array_merge($specific_terms, [
            'lighter smoking accessories',
            'cigarette lighter smoking',
            'flame lighter smoking',
            'smoking lighter device'
        ]);
    }
    
    // Ashtray related
    if (headshop_contains_any($product_name, ['ashtray', 'ash', 'tray'])) {
        $specific_terms = array_merge($specific_terms, [
            'ashtray smoking accessories',
            'smoking ashtray device',
            'ash tray smoking',
            'cigarette ashtray smoking'
        ]);
    }
    
    // If no specific terms found, add generic smoking terms
    if (empty($specific_terms)) {
        $specific_terms = [
            'smoking accessories tobacco',
            'tobacco smoking products',
            'smoking device tobacco',
            'cigarette smoking accessories'
        ];
    }
    
    return array_merge($terms, $specific_terms);
}

// Helper function to check if string contains any of the given terms
function headshop_contains_any($text, $terms) {
    foreach ($terms as $term) {
        if (strpos($text, $term) !== false) {
            return true;
        }
    }
    return false;
}

// Get random image from stock photo services
function headshop_get_stock_image($query, $width = 1200, $height = 1600) {
    // Try multiple services in order of preference
    
    // 1. Picsum (Lorem Picsum) - reliable, no API key needed
    $picsum_url = 'https://picsum.photos/' . $width . '/' . $height . '?random=' . rand(1, 1000);
    $response = wp_remote_get($picsum_url, ['timeout' => 15]);
    if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
        $body = wp_remote_retrieve_body($response);
        if (!empty($body) && strlen($body) > 1000) { // Ensure it's a real image
            return $picsum_url;
        }
    }
    
    // 2. Unsplash Source API (backup)
    $unsplash_url = 'https://source.unsplash.com/' . $width . 'x' . $height . '/?' . urlencode($query);
    $response = wp_remote_get($unsplash_url, ['timeout' => 15]);
    if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
        $body = wp_remote_retrieve_body($response);
        if (!empty($body) && strlen($body) > 1000) {
            return $unsplash_url;
        }
    }
    
    // 3. Picsum with different random seed
    $picsum_url2 = 'https://picsum.photos/' . $width . '/' . $height . '?random=' . rand(1001, 2000);
    $response = wp_remote_get($picsum_url2, ['timeout' => 15]);
    if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
        $body = wp_remote_retrieve_body($response);
        if (!empty($body) && strlen($body) > 1000) {
            return $picsum_url2;
        }
    }
    
    // 4. Another Picsum attempt
    $picsum_url3 = 'https://picsum.photos/' . $width . '/' . $height;
    $response = wp_remote_get($picsum_url3, ['timeout' => 15]);
    if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
        $body = wp_remote_retrieve_body($response);
        if (!empty($body) && strlen($body) > 1000) {
            return $picsum_url3;
        }
    }
    
    return false;
}

// Download and attach image to WordPress
function headshop_download_and_attach_image($image_url, $title) {
    $image_data = wp_remote_get($image_url, ['timeout' => 30]);
    
    if (is_wp_error($image_data)) return false;
    
    $image_body = wp_remote_retrieve_body($image_data);
    if (empty($image_body)) return false;
    
    $upload_dir = wp_upload_dir();
    if ($upload_dir['error'] !== false) return false;
    
    $filename = sanitize_file_name($title) . '.jpg';
    $file_path = $upload_dir['path'] . '/' . $filename;
    
    if (!file_exists($upload_dir['path'])) {
        wp_mkdir_p($upload_dir['path']);
    }
    
    if (file_put_contents($file_path, $image_body)) {
        $attachment = [
            'post_mime_type' => 'image/jpeg',
            'post_title' => $title,
            'post_content' => '',
            'post_status' => 'inherit'
        ];
        
        $attachment_id = wp_insert_attachment($attachment, $file_path);
        if ($attachment_id && !is_wp_error($attachment_id)) {
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            $attachment_data = wp_generate_attachment_metadata($attachment_id, $file_path);
            wp_update_attachment_metadata($attachment_id, $attachment_data);
            
            return $attachment_id;
        }
    }
    
    return false;
}

// Process single batch of products (for AJAX)
function headshop_process_batch($batch_number = 1, $batch_size = 3) {
    if (!class_exists('WooCommerce')) return false;
    
    // Get products for this batch
    $offset = ($batch_number - 1) * $batch_size;
    $products = wc_get_products([
        'limit' => $batch_size,
        'offset' => $offset,
        'status' => 'publish',
    ]);
    
    if (empty($products)) {
        return ['completed' => true, 'processed' => 0, 'assigned' => 0, 'failed' => 0];
    }
    
    $processed = 0;
    $assigned = 0;
    $failed = 0;
    
    error_log('Headshop: Processing batch ' . $batch_number . ' with ' . count($products) . ' products');
    
    foreach ($products as $product) {
        $product_name = $product->get_name();
        error_log('Headshop: Processing product: ' . $product_name);
        
        $result = headshop_assign_stock_images($product->get_id(), 4);
        $processed++;
        
        if ($result && $result > 0) {
            $assigned += $result;
            error_log('Headshop: Successfully processed product: ' . $product_name . ' (assigned ' . $result . ' images)');
        } else {
            $failed++;
            error_log('Headshop: Failed to process product: ' . $product_name);
        }
        
        // Small delay between products
        usleep(100000); // 0.1 seconds
    }
    
    return [
        'completed' => false,
        'processed' => $processed,
        'assigned' => $assigned,
        'failed' => $failed,
        'next_batch' => $batch_number + 1
    ];
}

// Get total product count
function headshop_get_total_products() {
    if (!class_exists('WooCommerce')) return 0;
    
    $products = wc_get_products([
        'limit' => -1,
        'status' => 'publish',
        'return' => 'ids'
    ]);
    
    return count($products);
}

// Bulk assign images to all products (legacy function for single run)
function headshop_bulk_assign_images($batch_size = 3) {
    if (!class_exists('WooCommerce')) return false;
    
    $total_products = headshop_get_total_products();
    $total_batches = ceil($total_products / $batch_size);
    
    $total_processed = 0;
    $total_assigned = 0;
    $total_failed = 0;
    
    error_log('Headshop: Starting bulk image assignment for ' . $total_products . ' products in ' . $total_batches . ' batches');
    
    for ($batch = 1; $batch <= $total_batches; $batch++) {
        $result = headshop_process_batch($batch, $batch_size);
        
        $total_processed += $result['processed'];
        $total_assigned += $result['assigned'];
        $total_failed += $result['failed'];
        
        // Add delay between batches
        if ($batch < $total_batches) {
            sleep(1);
        }
    }
    
    error_log('Headshop: Bulk assignment completed. Processed: ' . $total_processed . ', Assigned: ' . $total_assigned . ', Failed: ' . $total_failed);
    
    return [
        'processed' => $total_processed,
        'assigned' => $total_assigned,
        'failed' => $total_failed
    ];
}

// AJAX handlers for progressive processing
add_action('wp_ajax_headshop_process_batch', 'headshop_ajax_process_batch');
add_action('wp_ajax_headshop_get_progress', 'headshop_ajax_get_progress');

function headshop_ajax_process_batch() {
    check_ajax_referer('headshop_nonce', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized');
    }
    
    $batch_number = (int) $_POST['batch_number'];
    $batch_size = (int) $_POST['batch_size'];
    
    $result = headshop_process_batch($batch_number, $batch_size);
    
    wp_send_json_success($result);
}

function headshop_ajax_get_progress() {
    check_ajax_referer('headshop_nonce', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized');
    }
    
    $total_products = headshop_get_total_products();
    $batch_size = (int) $_POST['batch_size'];
    $total_batches = ceil($total_products / $batch_size);
    
    wp_send_json_success([
        'total_products' => $total_products,
        'total_batches' => $total_batches,
        'batch_size' => $batch_size
    ]);
}

// Admin interface for image assignment
// Remove all product images function
function headshop_remove_all_product_images() {
    if (!class_exists('WooCommerce')) return false;
    
    $products = wc_get_products(['limit' => -1]);
    $removed_count = 0;
    
    foreach ($products as $product) {
        $product_id = $product->get_id();
        
        // Remove featured image
        if (has_post_thumbnail($product_id)) {
            delete_post_thumbnail($product_id);
            $removed_count++;
        }
        
        // Remove gallery images
        $gallery_ids = $product->get_gallery_image_ids();
        if (!empty($gallery_ids)) {
            update_post_meta($product_id, '_product_image_gallery', '');
            $removed_count += count($gallery_ids);
        }
        
        // Clear WooCommerce cache
        wc_delete_product_transients($product_id);
    }
    
    return $removed_count;
}

add_action('admin_menu', function() {
    add_management_page(
        'Gerenciar Imagens dos Produtos',
        'Imagens dos Produtos',
        'manage_options',
        'headshop-manage-images',
        'headshop_admin_image_management_page'
    );
});

function headshop_admin_image_management_page() {
    // Handle remove all images
    if (isset($_POST['remove_all_images']) && wp_verify_nonce($_POST['_wpnonce'], 'headshop_remove_images')) {
        $removed_count = headshop_remove_all_product_images();
        if ($removed_count > 0) {
            echo '<div class="notice notice-success"><p>Removidas ' . $removed_count . ' imagens de todos os produtos!</p></div>';
        } else {
            echo '<div class="notice notice-info"><p>Nenhuma imagem encontrada para remover.</p></div>';
        }
    }

function headshop_admin_image_assignment_page() {
    if (isset($_POST['assign_images']) && wp_verify_nonce($_POST['_wpnonce'], 'headshop_assign_images')) {
        $batch_size = isset($_POST['batch_size']) ? (int)$_POST['batch_size'] : 5;
        $result = headshop_bulk_assign_images($batch_size);
        if ($result) {
            echo '<div class="notice notice-success"><p>';
            printf('Processados %d produtos. Atribuídas %d imagens. Falharam: %d produtos.', 
                   $result['processed'], $result['assigned'], $result['failed']);
            echo '</p></div>';
            
            if ($result['failed'] > 0) {
                echo '<div class="notice notice-warning"><p>';
                echo 'Alguns produtos falharam. Verifique os logs de debug para detalhes.';
                echo '</p></div>';
            }
        } else {
            echo '<div class="notice notice-error"><p>Erro ao processar produtos.</p></div>';
        }
    }
    
    if (isset($_POST['test_single']) && wp_verify_nonce($_POST['_wpnonce'], 'headshop_test_single')) {
        $products = wc_get_products(['limit' => 1]);
        if (!empty($products)) {
            $product = $products[0];
            $search_terms = headshop_get_image_search_terms($product);
            echo '<div class="notice notice-info"><p><strong>Termos de busca para "' . $product->get_name() . '":</strong> ' . implode(', ', $search_terms) . '</p></div>';
            
            $result = headshop_assign_stock_images($product->get_id(), 2);
            if ($result) {
                echo '<div class="notice notice-success"><p>Teste bem-sucedido! Atribuídas ' . $result . ' imagens para o produto "' . $product->get_name() . '"</p></div>';
            } else {
                echo '<div class="notice notice-error"><p>Teste falhou para o produto "' . $product->get_name() . '".</p></div>';
            }
        }
    }
    
    $wc_active = class_exists('WooCommerce');
    $product_count = $wc_active ? wc_get_products(['limit' => -1, 'return' => 'ids']) : 0;
    $product_count = is_array($product_count) ? count($product_count) : 0;
    
    ?>
    <div class="wrap">
        <h1>Gerenciar Imagens dos Produtos</h1>
        
        <?php if (!$wc_active): ?>
            <div class="notice notice-error"><p><strong>WooCommerce não está ativo!</strong> Esta ferramenta requer o WooCommerce.</p></div>
        <?php else: ?>
            <p><strong>Produtos encontrados:</strong> <?php echo $product_count; ?></p>
            
            <!-- Remove All Images Section -->
            <div class="card" style="max-width: none; margin: 20px 0; padding: 20px; background: #fff; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
                <h2 style="color: #d63638; margin-top: 0;">🗑️ Remover Todas as Imagens</h2>
                <p>Remove todas as imagens (principais e galeria) de todos os produtos. <strong>Esta ação não pode ser desfeita!</strong></p>
                
                <form method="post" style="margin: 15px 0;" onsubmit="return confirm('Tem certeza que deseja remover TODAS as imagens de TODOS os produtos? Esta ação não pode ser desfeita!');">
                    <?php wp_nonce_field('headshop_remove_images'); ?>
                    <button type="submit" name="remove_all_images" class="button button-secondary" style="background: #d63638; color: white; border-color: #d63638;">
                        Remover Todas as Imagens
                    </button>
                </form>
            </div>
            
            <!-- Real Solutions Section -->
            <div class="card" style="max-width: none; margin: 20px 0; padding: 20px; background: #f0f6fc; border: 1px solid #0073aa; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
                <h2 style="color: #0073aa; margin-top: 0;">🎯 Soluções REAIS para Imagens de Produtos</h2>
                
                <div style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                    <strong>⚠️ Problema atual:</strong> Os placeholders não têm relação visual real com os produtos. Precisamos de imagens reais!
                </div>
                
                <h3>🏆 SOLUÇÃO RECOMENDADA: Upload Manual de Imagens Reais</h3>
                <div style="background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin: 15px 0;">
                    <p><strong>Como fazer:</strong></p>
                    <ol>
                        <li>Acesse <strong>Produtos → Todos os Produtos</strong></li>
                        <li>Clique em <strong>"Editar"</strong> no produto desejado</li>
                        <li>Na seção <strong>"Imagem do produto"</strong>, clique em <strong>"Definir imagem do produto"</strong></li>
                        <li>Faça upload de uma foto real do produto</li>
                        <li>Adicione imagens na <strong>"Galeria do produto"</strong> se quiser múltiplas fotos</li>
                        <li>Clique em <strong>"Atualizar"</strong></li>
                    </ol>
                </div>
                
                <h3>🛠️ FERRAMENTAS RECOMENDADAS para obter imagens:</h3>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin: 20px 0;">
                    <div style="border: 1px solid #ddd; padding: 15px; border-radius: 5px;">
                        <h4 style="color: #28a745; margin-top: 0;">📸 Fotos Próprias</h4>
                        <p><strong>Melhor opção!</strong></p>
                        <ul>
                            <li>Fotografe os produtos reais</li>
                            <li>Use boa iluminação (luz natural)</li>
                            <li>Fundo neutro (branco/cinza)</li>
                            <li>Múltiplos ângulos</li>
                        </ul>
                    </div>
                    
                    <div style="border: 1px solid #ddd; padding: 15px; border-radius: 5px;">
                        <h4 style="color: #007bff; margin-top: 0;">🌐 Bancos de Imagens</h4>
                        <p><strong>Gratuitos:</strong></p>
                        <ul>
                            <li><strong>Unsplash.com</strong> - Busque "bong", "vaporizer", etc.</li>
                            <li><strong>Pexels.com</strong> - Imagens de alta qualidade</li>
                            <li><strong>Pixabay.com</strong> - Sem copyright</li>
                        </ul>
                    </div>
                </div>
                
                <h3>🤖 SOLUÇÃO AUTOMÁTICA: Gerador de Imagens com IA</h3>
                <div style="background: #e7f3ff; border: 1px solid #b3d9ff; padding: 15px; border-radius: 5px; margin: 15px 0;">
                    <p><strong>Ferramentas de IA para gerar imagens:</strong></p>
                    <ul>
                        <li><strong>DALL-E 3</strong> (OpenAI) - Via ChatGPT Plus</li>
                        <li><strong>Midjourney</strong> - Discord bot</li>
                        <li><strong>Stable Diffusion</strong> - Gratuito, local</li>
                        <li><strong>Leonardo.ai</strong> - Interface web</li>
                    </ul>
                    <p><strong>Prompt exemplo:</strong> "Professional product photo of a glass bong on white background, studio lighting, high quality, e-commerce style"</p>
                </div>
                
                <h3>⚡ SOLUÇÃO RÁPIDA: Placeholders Melhorados</h3>
                <div style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 15px 0;">
                    <p>Se quiser manter placeholders temporariamente, posso criar versões mais realistas:</p>
                    <ul>
                        <li>Imagens 3D renderizadas de produtos</li>
                        <li>Silhuetas mais detalhadas</li>
                        <li>Cores e formas mais específicas</li>
                    </ul>
                </div>
                
                <div style="background: #d1ecf1; border: 1px solid #bee5eb; padding: 15px; border-radius: 5px; margin-top: 20px;">
                    <h4 style="color: #0c5460; margin-top: 0;">💡 DICA PROFISSIONAL</h4>
                    <p>Para um headshop profissional, <strong>imagens reais são essenciais</strong>. Os clientes precisam ver exatamente o que estão comprando. Placeholders são apenas temporários!</p>
                </div>
            </div>
            
            <form method="post" style="margin: 20px 0;">
                <?php wp_nonce_field('headshop_test_single'); ?>
                <p class="submit">
                    <input type="submit" name="test_single" class="button" value="Testar com 1 Produto">
                </p>
            </form>
            
            <form method="post" id="legacy-form">
                <?php wp_nonce_field('headshop_assign_images'); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row">Tamanho do Lote</th>
                        <td>
                            <select name="batch_size" id="legacy-batch-size">
                                <option value="2">2 produtos por lote (mais seguro)</option>
                                <option value="3" selected>3 produtos por lote (recomendado)</option>
                                <option value="5">5 produtos por lote (mais rápido)</option>
                            </select>
                            <p class="description">Lotes menores evitam timeout, mas processam mais devagar.</p>
                        </td>
                    </tr>
                </table>
                <p class="submit">
                    <input type="submit" name="assign_images" class="button button-primary" value="Atribuir Imagens (Método Tradicional)" onclick="return confirm('Isso pode demorar alguns minutos. Continuar?');">
                </p>
            </form>
            
            <hr>
            
            <div id="progressive-processing">
                <h3>Processamento Progressivo (Recomendado)</h3>
                <p>Este método processa os produtos em lotes pequenos via AJAX, evitando timeouts.</p>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">Tamanho do Lote</th>
                        <td>
                            <select id="progressive-batch-size">
                                <option value="2">2 produtos por lote</option>
                                <option value="3" selected>3 produtos por lote</option>
                                <option value="5">5 produtos por lote</option>
                            </select>
                        </td>
                    </tr>
                </table>
                
                <p class="submit">
                    <button type="button" id="start-progressive" class="button button-primary">Iniciar Processamento Progressivo</button>
                    <button type="button" id="stop-progressive" class="button button-secondary" style="display:none;">Parar Processamento</button>
                </p>
                
                <div id="progress-container" style="display:none;">
                    <h4>Progresso</h4>
                    <div id="progress-bar" style="width: 100%; background-color: #f0f0f0; border-radius: 5px; overflow: hidden;">
                        <div id="progress-fill" style="height: 20px; background-color: #0073aa; width: 0%; transition: width 0.3s ease;"></div>
                    </div>
                    <p id="progress-text">Preparando...</p>
                    <div id="progress-details"></div>
                </div>
            </div>
        <?php endif; ?>
        
    <div class="card">
        <h2>Como Funciona</h2>
        <ul>
            <li>Usa imagens gratuitas do Picsum (Lorem Picsum)</li>
            <li><strong>Busca inteligente:</strong> Extrai palavras-chave do título do produto</li>
            <li><strong>Termos específicos:</strong> Reconhece bongs, vaporizadores, sedas, etc.</li>
            <li>Atribui 4 imagens por produto</li>
            <li>Pula produtos que já têm galeria completa</li>
            <li>Imagens são baixadas e salvas localmente</li>
            <li>Sistema de retry para garantir que todos os produtos sejam processados</li>
        </ul>
    </div>
    
    <div class="card">
        <h2>Tipos de Produtos Reconhecidos</h2>
        <ul>
            <li><strong>Bongs/Water Pipes:</strong> "bong water pipe glass smoking"</li>
            <li><strong>Vaporizadores:</strong> "vaporizer electronic cigarette"</li>
            <li><strong>Sedas/Papéis:</strong> "rolling papers cigarette tobacco"</li>
            <li><strong>Pipes:</strong> "smoking pipe tobacco"</li>
            <li><strong>Moedores:</strong> "herb grinder smoking"</li>
            <li><strong>Hookah:</strong> "hookah shisha smoking"</li>
            <li><strong>Isqueiros:</strong> "lighter smoking accessories"</li>
            <li><strong>Cinzeiros:</strong> "ashtray smoking accessories"</li>
        </ul>
    </div>
    
    <div class="card">
        <h2>Melhorias Implementadas</h2>
        <ul>
            <li>✅ Processamento em lotes para evitar timeout</li>
            <li>✅ Aumento do limite de execução (5 minutos)</li>
            <li>✅ Múltiplas tentativas por imagem (até 2x)</li>
            <li>✅ Verificação de qualidade da imagem (tamanho mínimo)</li>
            <li>✅ Delays otimizados entre requisições</li>
            <li>✅ Logs detalhados para debug</li>
            <li>✅ Relatório de produtos que falharam</li>
            <li>✅ Sistema de fallback com múltiplos serviços</li>
        </ul>
    </div>
    
    <div class="card">
        <h2>Dicas para Evitar Timeout</h2>
        <ul>
            <li>Use lotes menores (3-5 produtos) para servidores com limitações</li>
            <li>Execute em horários de menor tráfego</li>
            <li>Monitore os logs para acompanhar o progresso</li>
            <li>Se houver timeout, execute novamente - produtos já processados serão pulados</li>
        </ul>
    </div>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        let isProcessing = false;
        let currentBatch = 1;
        let totalBatches = 0;
        let totalProcessed = 0;
        let totalAssigned = 0;
        let totalFailed = 0;
        
        $('#start-progressive').click(function() {
            if (isProcessing) return;
            
            const batchSize = $('#progressive-batch-size').val();
            isProcessing = true;
            currentBatch = 1;
            totalProcessed = 0;
            totalAssigned = 0;
            totalFailed = 0;
            
            $('#start-progressive').hide();
            $('#stop-progressive').show();
            $('#progress-container').show();
            $('#progress-fill').css('width', '0%');
            $('#progress-text').text('Iniciando...');
            $('#progress-details').html('');
            
            // Get total products and start processing
            $.post(ajaxurl, {
                action: 'headshop_get_progress',
                batch_size: batchSize,
                nonce: '<?php echo wp_create_nonce('headshop_nonce'); ?>'
            }, function(response) {
                if (response.success) {
                    totalBatches = response.data.total_batches;
                    $('#progress-text').text('Processando lote 1 de ' + totalBatches + '...');
                    processNextBatch(batchSize);
                } else {
                    alert('Erro ao obter informações dos produtos');
                    resetUI();
                }
            });
        });
        
        $('#stop-progressive').click(function() {
            isProcessing = false;
            resetUI();
        });
        
        function processNextBatch(batchSize) {
            if (!isProcessing) return;
            
            $.post(ajaxurl, {
                action: 'headshop_process_batch',
                batch_number: currentBatch,
                batch_size: batchSize,
                nonce: '<?php echo wp_create_nonce('headshop_nonce'); ?>'
            }, function(response) {
                if (response.success) {
                    const data = response.data;
                    totalProcessed += data.processed;
                    totalAssigned += data.assigned;
                    totalFailed += data.failed;
                    
                    const progress = Math.round((currentBatch / totalBatches) * 100);
                    $('#progress-fill').css('width', progress + '%');
                    $('#progress-text').text('Processando lote ' + currentBatch + ' de ' + totalBatches + '...');
                    
                    let details = 'Processados: ' + totalProcessed + ' | Atribuídas: ' + totalAssigned + ' | Falharam: ' + totalFailed;
                    if (data.processed > 0) {
                        details += '<br>Último lote: ' + data.processed + ' produtos, ' + data.assigned + ' imagens atribuídas';
                    }
                    $('#progress-details').html(details);
                    
                    if (data.completed) {
                        // All done
                        $('#progress-text').text('Concluído! Processados: ' + totalProcessed + ' produtos, ' + totalAssigned + ' imagens atribuídas');
                        $('#progress-fill').css('width', '100%');
                        resetUI();
                    } else {
                        // Continue with next batch
                        currentBatch++;
                        setTimeout(function() {
                            processNextBatch(batchSize);
                        }, 1000); // 1 second delay between batches
                    }
                } else {
                    alert('Erro ao processar lote ' + currentBatch);
                    resetUI();
                }
            }).fail(function() {
                alert('Erro de conexão ao processar lote ' + currentBatch);
                resetUI();
            });
        }
        
        function resetUI() {
            isProcessing = false;
            $('#start-progressive').show();
            $('#stop-progressive').hide();
        }
    });
    </script>
    <?php
}
}



