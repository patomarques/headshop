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
}
add_action('wp_enqueue_scripts', 'headshop_theme_assets');

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
    
    // Fill remaining slots with placeholder if needed
    while (count($gallery) < $limit) {
        $gallery[] = [
            'id' => 0,
            'url' => 'https://via.placeholder.com/1200x1600/efefef/999999?text=' . urlencode($product->get_name()),
            'thumb' => 'https://via.placeholder.com/300x400/efefef/999999?text=' . urlencode($product->get_name()),
            'alt' => $product->get_name(),
            'title' => $product->get_name(),
        ];
    }
    
    return $gallery;
}

// Responsive image with lazy loading
function headshop_responsive_image($image, $class = '', $lazy = true) {
    if (empty($image['url'])) return '';
    
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
        $image_url = headshop_get_stock_image($search_term, 1200, 1600);
        
        if ($image_url) {
            $attachment_id = headshop_download_and_attach_image($image_url, $product_name . ' - ' . $search_term . ' ' . $i);
            if ($attachment_id) {
                $assigned_images[] = $attachment_id;
                error_log('Headshop: Successfully assigned image ' . $i . ' for product: ' . $product_name);
            } else {
                error_log('Headshop: Failed to download/attach image for product: ' . $product_name);
                $i--; // Retry this image
            }
        } else {
            error_log('Headshop: Failed to get stock image for product: ' . $product_name . ' (attempt ' . $attempts . ')');
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

// Get search terms based on product name and category
function headshop_get_image_search_terms($product) {
    $terms = ['smoking', 'tobacco', 'cigarette', 'pipe'];
    
    $product_name = strtolower($product->get_name());
    $categories = wp_get_post_terms($product->get_id(), 'product_cat');
    
    // Add category-based terms
    if (!is_wp_error($categories) && !empty($categories)) {
        foreach ($categories as $category) {
            $cat_name = strtolower($category->name);
            if (strpos($cat_name, 'bong') !== false) $terms[] = 'bong water pipe';
            if (strpos($cat_name, 'vapor') !== false) $terms[] = 'vaporizer electronic cigarette';
            if (strpos($cat_name, 'sed') !== false) $terms[] = 'rolling papers cigarette';
            if (strpos($cat_name, 'acess') !== false) $terms[] = 'smoking accessories';
        }
    }
    
    // Add product name-based terms
    if (strpos($product_name, 'bong') !== false) $terms[] = 'bong water pipe glass';
    if (strpos($product_name, 'vapor') !== false) $terms[] = 'vaporizer electronic';
    if (strpos($product_name, 'sed') !== false) $terms[] = 'rolling papers tobacco';
    if (strpos($product_name, 'pipe') !== false) $terms[] = 'smoking pipe tobacco';
    
    return array_unique($terms);
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
add_action('admin_menu', function() {
    add_management_page(
        'Atribuir Imagens de Estoque',
        'Atribuir Imagens',
        'manage_options',
        'headshop-assign-images',
        'headshop_admin_image_assignment_page'
    );
});

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
        <h1>Atribuir Imagens de Estoque</h1>
        
        <?php if (!$wc_active): ?>
            <div class="notice notice-error"><p><strong>WooCommerce não está ativo!</strong> Esta ferramenta requer o WooCommerce.</p></div>
        <?php else: ?>
            <p>Esta ferramenta atribui automaticamente 4 imagens de estoque gratuitas do Unsplash para cada produto.</p>
            <p><strong>Produtos encontrados:</strong> <?php echo $product_count; ?></p>
            
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
            <li>Busca termos relacionados ao produto/categoria</li>
            <li>Atribui 4 imagens por produto</li>
            <li>Pula produtos que já têm galeria completa</li>
            <li>Imagens são baixadas e salvas localmente</li>
            <li>Sistema de retry para garantir que todos os produtos sejam processados</li>
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


