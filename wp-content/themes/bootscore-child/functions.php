<?php
/**
 * Bootscore Child — Headshop
 *
 * Replaces the default bootscore-child functions.php with all
 * homepage features ported from the wp-headshop Storefront child theme,
 * now using Bootstrap 5 natively.
 *
 * @package Bootscore Child
 * @version 6.0.0
 */

defined('ABSPATH') || exit;


/* =====================================================================
   1. ENQUEUE STYLES & SCRIPTS
   ===================================================================== */

// Desativa o compilador SCSS do Bootscore para evitar erro fatal
// e usar apenas o CSS já pré-compilado em assets/css/main.css.
if (!defined('BOOTSCORE_SCSS_DISABLE_COMPILER')) {
    define('BOOTSCORE_SCSS_DISABLE_COMPILER', true);
}
add_filter('bootscore/scss/disable_compiler', '__return_true');

// Google Fonts preconnect hints
add_action('wp_head', function () {
    echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
    echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
}, 1);

add_action('wp_enqueue_scripts', 'headshop_enqueue_assets');
function headshop_enqueue_assets() {
    // Parent style
    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');

    // Google Fonts — Syne display
    wp_enqueue_style(
        'headshop-google-fonts',
        'https://fonts.googleapis.com/css2?family=Syne:wght@700;800&display=swap',
        array(),
        null
    );

    // Compiled child main.css (Bootstrap + custom SCSS)
    $css_path = get_stylesheet_directory() . '/assets/css/main.css';
    $css_ver  = file_exists($css_path) ? date('YmdHi', filemtime($css_path)) : null;
    wp_enqueue_style('headshop-main', get_stylesheet_directory_uri() . '/assets/css/main.css', array('parent-style', 'headshop-google-fonts'), $css_ver);

    // Dashicons (for cart icons)
    wp_enqueue_style('dashicons');

    // Custom JS
    $js_path = get_stylesheet_directory() . '/assets/js/custom.js';
    $js_ver  = file_exists($js_path) ? date('YmdHi', filemtime($js_path)) : null;
    wp_enqueue_script('headshop-custom', get_stylesheet_directory_uri() . '/assets/js/custom.js', array('jquery'), $js_ver, true);

    // Localize for AJAX
    if (class_exists('WooCommerce')) {
        wp_localize_script('headshop-custom', 'headshopAjax', array(
            'url'   => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('headshop_cart'),
        ));
    }
}


/* =====================================================================
   2. REGISTER ADDITIONAL NAV MENUS
   ===================================================================== */

add_action('after_setup_theme', 'headshop_register_nav_menus');
function headshop_register_nav_menus() {
    register_nav_menu('menu-bars', 'Menu Bars (fullscreen overlay)');
}


/* =====================================================================
   3. HIDE ADMIN BAR ON FRONT PAGE
   ===================================================================== */

// Oculta a admin bar na parte pública para usuários logados (exceto admin)
add_filter('show_admin_bar', function ($show) {
    if (!is_admin()) {
        return false;
    }
    return $show;
});


/* =====================================================================
   3. DISABLE BOOTSCORE SKIP-CART REDIRECT
   ===================================================================== */

add_filter('bootscore/skip_cart', '__return_false');


/* =====================================================================
   4. MY ACCOUNT / CHECKOUT — REMOVE SIDEBAR
   ===================================================================== */

add_filter('sidebars_widgets', function ($sidebars_widgets) {
    if (is_account_page() || is_checkout()) {
        $sidebars_widgets['sidebar-1'] = [];
    }
    return $sidebars_widgets;
});


/* =====================================================================
   5. BANNER CPT
   ===================================================================== */

add_action('init', 'headshop_register_banner_cpt');
function headshop_register_banner_cpt() {
    register_post_type('banner', array(
        'labels' => array(
            'name'               => 'Banners',
            'singular_name'      => 'Banner',
            'menu_name'          => 'Banners',
            'add_new'            => 'Adicionar Novo',
            'add_new_item'       => 'Adicionar Novo Banner',
            'edit_item'          => 'Editar Banner',
            'view_item'          => 'Ver Banner',
            'all_items'          => 'Todos os Banners',
            'search_items'       => 'Pesquisar Banners',
            'not_found'          => 'Nenhum banner encontrado.',
            'not_found_in_trash' => 'Nenhum banner encontrado na lixeira.',
        ),
        'public'             => false,
        'publicly_queryable' => false,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => false,
        'rewrite'            => false,
        'capability_type'    => 'post',
        'has_archive'        => false,
        'hierarchical'       => false,
        'menu_position'      => 5,
        'menu_icon'          => 'dashicons-images-alt2',
        'supports'           => array('title', 'thumbnail'),
    ));
}


/* =====================================================================
   5a. BANNER — METABOX IMAGEM MOBILE
   ===================================================================== */

add_action('add_meta_boxes', function () {
    add_meta_box(
        'banner_mobile_image',
        'Imagem Mobile',
        'headshop_banner_mobile_metabox',
        'banner',
        'side',
        'default'
    );
    add_meta_box(
        'banner_text_overlay',
        'Texto sobre a imagem',
        'headshop_banner_text_metabox',
        'banner',
        'normal',
        'high'
    );
});

function headshop_banner_text_metabox($post) {
    $title    = get_post_meta($post->ID, '_banner_text_title', true);
    $subtitle = get_post_meta($post->ID, '_banner_text_subtitle', true);
    wp_nonce_field('headshop_banner_text_nonce', 'headshop_banner_text_nonce');

    $editor_settings = array(
        'media_buttons' => false,
        'quicktags'     => false,
        'tinymce'       => array(
            'toolbar1' => 'bold,italic,underline,strikethrough,|,alignleft,aligncenter,alignright,|,removeformat',
            'toolbar2' => '',
        ),
    );
    ?>
    <p style="font-weight:600;margin-bottom:4px;">Título</p>
    <?php wp_editor($title, 'banner_text_title', array_merge($editor_settings, array(
        'textarea_name' => 'banner_text_title',
        'textarea_rows' => 3,
    ))); ?>

    <p style="font-weight:600;margin:16px 0 4px;">Subtítulo</p>
    <?php wp_editor($subtitle, 'banner_text_subtitle', array_merge($editor_settings, array(
        'textarea_name' => 'banner_text_subtitle',
        'textarea_rows' => 3,
    ))); ?>
    <p class="description" style="margin-top:8px;">Deixe em branco para não exibir texto sobre o banner.</p>
    <?php
}

function headshop_banner_mobile_metabox($post) {
    $mobile_id  = (int) get_post_meta($post->ID, '_banner_mobile_image', true);
    $mobile_url = $mobile_id ? wp_get_attachment_image_url($mobile_id, 'medium') : '';
    wp_nonce_field('headshop_banner_mobile_nonce', 'headshop_banner_mobile_nonce');
    ?>
    <div id="headshop-mobile-wrap">
      <?php if ($mobile_url) : ?>
        <img id="headshop-mobile-preview" src="<?= esc_url($mobile_url); ?>"
             style="max-width:100%;height:auto;display:block;margin-bottom:8px;" />
      <?php else : ?>
        <img id="headshop-mobile-preview" src="" style="max-width:100%;height:auto;display:none;margin-bottom:8px;" />
      <?php endif; ?>
      <input type="hidden" id="headshop_banner_mobile_id" name="headshop_banner_mobile_id"
             value="<?= esc_attr($mobile_id ?: ''); ?>" />
      <button type="button" id="headshop-mobile-select" class="button button-secondary" style="width:100%;">
        <?= $mobile_id ? 'Trocar imagem mobile' : 'Selecionar imagem mobile'; ?>
      </button>
      <button type="button" id="headshop-mobile-remove" class="button"
              style="width:100%;margin-top:4px;color:#b32d2e;<?= $mobile_id ? '' : 'display:none;'; ?>">
        Remover
      </button>
      <p class="description" style="margin-top:8px;font-size:11px;">
        Se não definida, usa a imagem desktop.
      </p>
    </div>
    <script>
    jQuery(function ($) {
        var frame;
        var $preview = $('#headshop-mobile-preview');
        var $input   = $('#headshop_banner_mobile_id');
        var $select  = $('#headshop-mobile-select');
        var $remove  = $('#headshop-mobile-remove');

        $select.on('click', function () {
            if (frame) { frame.open(); return; }
            frame = wp.media({
                title: 'Selecionar imagem mobile',
                multiple: false,
                library: { type: 'image' },
                button:  { text: 'Usar esta imagem' }
            });
            frame.on('select', function () {
                var att = frame.state().get('selection').first().toJSON();
                $input.val(att.id);
                $preview.attr('src', att.url).show();
                $select.text('Trocar imagem mobile');
                $remove.show();
            });
            frame.open();
        });

        $remove.on('click', function () {
            $input.val('');
            $preview.attr('src', '').hide();
            $select.text('Selecionar imagem mobile');
            $remove.hide();
        });
    });
    </script>
    <?php
}

add_action('save_post_banner', function ($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    // Mobile image
    if (isset($_POST['headshop_banner_mobile_nonce']) &&
        wp_verify_nonce($_POST['headshop_banner_mobile_nonce'], 'headshop_banner_mobile_nonce')) {
        $img_id = absint($_POST['headshop_banner_mobile_id'] ?? 0);
        if ($img_id) {
            update_post_meta($post_id, '_banner_mobile_image', $img_id);
        } else {
            delete_post_meta($post_id, '_banner_mobile_image');
        }
    }

    // Text overlay
    if (isset($_POST['headshop_banner_text_nonce']) &&
        wp_verify_nonce($_POST['headshop_banner_text_nonce'], 'headshop_banner_text_nonce')) {
        $title    = wp_kses_post(wp_unslash($_POST['banner_text_title'] ?? ''));
        $subtitle = wp_kses_post(wp_unslash($_POST['banner_text_subtitle'] ?? ''));
        update_post_meta($post_id, '_banner_text_title', $title);
        update_post_meta($post_id, '_banner_text_subtitle', $subtitle);
    }
});

// Enqueue wp.media no admin do banner
add_action('admin_enqueue_scripts', function ($hook) {
    global $post;
    if (($hook === 'post-new.php' || $hook === 'post.php') &&
        isset($post) && $post->post_type === 'banner') {
        wp_enqueue_media();
    }
});


/* =====================================================================
   5. BANNER SLIDER (Bootstrap Carousel)
   ===================================================================== */

function headshop_banner_slider() {
    if (!is_front_page()) return;

    $banners = new WP_Query(array(
        'post_type'      => 'banner',
        'posts_per_page' => -1,
        'orderby'        => 'menu_order',
        'order'          => 'ASC',
        'post_status'    => 'publish',
    ));

    if (!$banners->have_posts()) return;

    $slides = array();
    while ($banners->have_posts()) {
        $banners->the_post();
        $desktop_id  = get_post_thumbnail_id();
        $desktop_url = $desktop_id ? wp_get_attachment_image_url($desktop_id, 'full') : '';
        if (!$desktop_url) continue;

        $mobile_id  = (int) get_post_meta(get_the_ID(), '_banner_mobile_image', true);
        $mobile_url = $mobile_id ? wp_get_attachment_image_url($mobile_id, 'full') : $desktop_url;

        $slides[] = array(
            'desktop'  => $desktop_url,
            'mobile'   => $mobile_url,
            'title'    => get_post_meta(get_the_ID(), '_banner_text_title', true),
            'subtitle' => get_post_meta(get_the_ID(), '_banner_text_subtitle', true),
        );
    }
    wp_reset_postdata();

    if (empty($slides)) return;
    ?>
    <div id="bannerCarousel" class="carousel slide headshop-banner" data-bs-ride="carousel" data-bs-interval="5000">
      <!-- Indicators -->
      <div class="carousel-indicators">
        <?php foreach ($slides as $i => $slide) : ?>
          <button type="button" data-bs-target="#bannerCarousel" data-bs-slide-to="<?= $i; ?>"<?php if ($i === 0) echo ' class="active" aria-current="true"'; ?> aria-label="Slide <?= $i + 1; ?>"></button>
        <?php endforeach; ?>
      </div>

      <!-- Slides -->
      <div class="carousel-inner h-100">
        <?php foreach ($slides as $i => $slide) : ?>
          <div class="carousel-item h-100<?php if ($i === 0) echo ' active'; ?>">
            <div class="headshop-banner__slide"
                 style="--img-desktop:url('<?= esc_url($slide['desktop']); ?>');--img-mobile:url('<?= esc_url($slide['mobile']); ?>');">
              <?php if (!empty($slide['title'])) : ?>
              <div class="headshop-banner__caption">
                <div class="headshop-banner__caption-title"><?= wp_kses_post(wpautop($slide['title'])); ?></div>
                <?php if (!empty($slide['subtitle'])) : ?>
                <div class="headshop-banner__caption-sub"><?= wp_kses_post(wpautop($slide['subtitle'])); ?></div>
                <?php endif; ?>
              </div>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Controls -->
      <button class="carousel-control-prev" type="button" data-bs-target="#bannerCarousel" data-bs-slide="prev">
        <span class="headshop-banner__nav-icon" aria-hidden="true">&#8249;</span>
        <span class="visually-hidden">Anterior</span>
      </button>
      <button class="carousel-control-next" type="button" data-bs-target="#bannerCarousel" data-bs-slide="next">
        <span class="headshop-banner__nav-icon" aria-hidden="true">&#8250;</span>
        <span class="visually-hidden">Próximo</span>
      </button>
    </div>
    <?php
}


/* =====================================================================
   5. ADMIN — HOME CONFIG (Categories)
   ===================================================================== */

add_action('admin_menu', 'headshop_register_settings');
function headshop_register_settings() {
    add_menu_page(
        'Configurações da Home',
        'Home Config',
        'manage_options',
        'headshop-home-config',
        'headshop_home_config_page',
        'dashicons-admin-home',
        61
    );
}

function headshop_home_config_page() {
    if (!current_user_can('manage_options')) return;

    if (isset($_POST['headshop_cats_nonce']) && wp_verify_nonce($_POST['headshop_cats_nonce'], 'save_home_categories')) {
        $selected = isset($_POST['home_categories_ordered']) ? $_POST['home_categories_ordered'] : '';
        $ordered  = array_filter(array_map('intval', explode(',', $selected)));
        update_option('headshop_home_categories', $ordered);
        echo '<div class="notice notice-success is-dismissible"><p>Configurações salvas com sucesso!</p></div>';
    }

    $saved_cats     = get_option('headshop_home_categories', array());
    // Fallback: read old option key from wp-headshop
    if (empty($saved_cats)) {
        $saved_cats = get_option('storefront_child_home_categories', array());
    }
    $all_categories = get_terms(array('taxonomy' => 'product_cat', 'hide_empty' => false));

    // Sort hierarchically
    $sorted    = array();
    $by_parent = array();
    if (!is_wp_error($all_categories)) {
        foreach ($all_categories as $cat) {
            $by_parent[$cat->parent][] = $cat;
        }
    }
    $add_children = function (&$sorted, $by_parent, $pid = 0, $lvl = 0) use (&$add_children) {
        if (!isset($by_parent[$pid])) return;
        foreach ($by_parent[$pid] as $c) {
            $c->level = $lvl;
            $sorted[] = $c;
            $add_children($sorted, $by_parent, $c->term_id, $lvl + 1);
        }
    };
    $add_children($sorted, $by_parent);
    ?>
    <div class="wrap">
        <h1>Configurações da Home</h1>
        <div style="max-width:1200px">
            <form method="post">
                <?php wp_nonce_field('save_home_categories', 'headshop_cats_nonce'); ?>
                <input type="hidden" name="home_categories_ordered" id="homeCategoriesOrdered" value="<?= esc_attr(implode(',', $saved_cats)); ?>" />

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin:20px 0;">
                    <div>
                        <h2>Categorias Disponíveis</h2>
                        <p>Arraste as categorias para a direita para exibir na home:</p>
                        <div id="availableCategories" class="category-sortable-list" style="background:#f9f9f9; border:2px dashed #ccc; border-radius:8px; padding:15px; min-height:400px;">
                            <?php foreach ($sorted as $cat) :
                                if (in_array($cat->term_id, $saved_cats)) continue; ?>
                                <div class="category-item" data-id="<?= esc_attr($cat->term_id); ?>" draggable="true" style="background:#fff; border:1px solid #ddd; border-radius:4px; padding:12px; margin-bottom:8px; cursor:move; display:flex; align-items:center; gap:10px;">
                                    <span class="dashicons dashicons-move" style="color:#999;"></span>
                                    <span style="flex:1;">
                                        <?= str_repeat('<span style="color:#ccc;">└</span> ', $cat->level); ?>
                                        <?= esc_html($cat->name); ?>
                                        <small style="color:#999;">(<?= $cat->count; ?>)</small>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div>
                        <h2>Categorias na Home <span style="font-size:14px; font-weight:normal; color:#999;">(ordem de exibição)</span></h2>
                        <p>Arraste para reordenar ou remova arrastando para a esquerda:</p>
                        <div id="selectedCategories" class="category-sortable-list" style="background:#e8f5e9; border:2px solid #4caf50; border-radius:8px; padding:15px; min-height:400px;">
                            <?php foreach ($saved_cats as $cid) :
                                $cat = get_term($cid, 'product_cat');
                                if (!$cat || is_wp_error($cat)) continue;
                                $lvl = 0; $pid = $cat->parent;
                                while ($pid > 0) { $p = get_term($pid, 'product_cat'); $pid = $p->parent; $lvl++; }
                            ?>
                                <div class="category-item" data-id="<?= esc_attr($cid); ?>" draggable="true" style="background:#fff; border:1px solid #4caf50; border-radius:4px; padding:12px; margin-bottom:8px; cursor:move; display:flex; align-items:center; gap:10px;">
                                    <span class="dashicons dashicons-move" style="color:#4caf50;"></span>
                                    <span style="flex:1;">
                                        <?= str_repeat('<span style="color:#ccc;">└</span> ', $lvl); ?>
                                        <?= esc_html($cat->name); ?>
                                        <small style="color:#999;">(<?= $cat->count; ?>)</small>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <?php submit_button('Salvar Configurações', 'primary', 'submit', false); ?>
            </form>
        </div>
    </div>

    <style>
        .category-item:hover { box-shadow:0 2px 8px rgba(0,0,0,.1); transform:translateY(-2px); transition:all .2s ease; }
        .category-sortable-list.drag-over { background:#fff3cd !important; border-color:#ffc107 !important; }
        .category-item.dragging { opacity:.5; }
    </style>

    <script>
    (function(){
        var available=document.getElementById('availableCategories');
        var selected=document.getElementById('selectedCategories');
        var input=document.getElementById('homeCategoriesOrdered');
        var dragged=null;

        function setupDD(c){
            c.querySelectorAll('.category-item').forEach(function(el){
                el.addEventListener('dragstart',function(e){dragged=this;this.classList.add('dragging');e.dataTransfer.effectAllowed='move';});
                el.addEventListener('dragend',function(){this.classList.remove('dragging');available.classList.remove('drag-over');selected.classList.remove('drag-over');});
            });
        }

        [available,selected].forEach(function(c){
            c.addEventListener('dragover',function(e){e.preventDefault();e.dataTransfer.dropEffect='move';this.classList.add('drag-over');});
            c.addEventListener('dragleave',function(){this.classList.remove('drag-over');});
            c.addEventListener('drop',function(e){e.preventDefault();this.classList.remove('drag-over');if(dragged){this.appendChild(dragged);update();}});
            setupDD(c);
        });

        function update(){
            var ids=Array.from(selected.querySelectorAll('.category-item')).map(function(el){return el.getAttribute('data-id');});
            input.value=ids.join(',');
        }
    })();
    </script>
    <?php
}


/* =====================================================================
   6. CATEGORIES SECTION
   ===================================================================== */

function headshop_categories_section() {
    if (!is_front_page() || !class_exists('WooCommerce')) return;

    $selected = get_option('headshop_home_categories', array());
    // Fallback to old option
    if (empty($selected)) {
        $selected = get_option('storefront_child_home_categories', array());
    }

    if (empty($selected)) {
        $categories = get_terms(array(
            'taxonomy'   => 'product_cat',
            'hide_empty' => true,
            'parent'     => 0,
        ));
    } else {
        $categories = get_terms(array(
            'taxonomy'   => 'product_cat',
            'hide_empty' => false,
            'include'    => $selected,
            'orderby'    => 'include',
        ));
    }

    if (empty($categories) || is_wp_error($categories)) return;

    $placeholder = wc_placeholder_img_src('woocommerce_thumbnail');
    ?>
    <section class="headshop-categories py-5">
      <div class="container" style="max-width:1400px;">
        <div class="headshop-categories__grid">
          <?php foreach ($categories as $cat) :
              $tid  = $cat->term_id;
              $link = get_term_link($tid, 'product_cat');
              $thid = get_term_meta($tid, 'thumbnail_id', true);
              $img  = $thid ? wp_get_attachment_url($thid) : $placeholder;
          ?>
            <a href="<?= esc_url($link); ?>" class="headshop-categories__card">
              <div class="headshop-categories__image" style="background-image: url('<?= esc_url($img); ?>');"></div>
              <h3 class="headshop-categories__name"><?= esc_html($cat->name); ?></h3>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
    <?php
}


/* =====================================================================
   7. SEARCH OVERLAY
   ===================================================================== */

function headshop_search_overlay() {
    $action = esc_url(home_url('/'));
    $is_wc  = class_exists('WooCommerce');
    ?>
    <div id="headerSearchOverlay" class="headshop-search-overlay" aria-hidden="true">
      <button id="searchOverlayClose" class="headshop-search-overlay__close" type="button" aria-label="Fechar busca">
        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
        </svg>
      </button>
      <div class="headshop-search-overlay__inner">
        <form role="search" method="get" class="headshop-search-overlay__form" action="<?= $action; ?>">
          <input id="headerSearchInput" class="headshop-search-overlay__input" type="search" name="s" placeholder="Pesquisar" autocomplete="off" />
          <?php if ($is_wc) : ?>
            <input type="hidden" name="post_type" value="product" />
          <?php endif; ?>
          <button class="headshop-search-overlay__submit" type="submit" aria-label="Buscar">
            <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
            </svg>
          </button>
        </form>
      </div>
    </div>
    <?php
}

// Add search overlay to all pages via footer
add_action('wp_footer', function () {
    if (!is_front_page()) {
        headshop_search_overlay();
    }
}, 5);


/* =====================================================================
   8. CART DROPDOWN (AJAX)
   ===================================================================== */

function headshop_render_cart_dropdown() {
    if (!class_exists('WooCommerce') || !WC()->cart) {
        echo '<div class="p-4">Carrinho indisponível.</div>';
        return;
    }

    $items = WC()->cart->get_cart();
    if (empty($items)) {
        echo '<div class="p-4 text-center">Seu carrinho está vazio.</div>';
        return;
    }

    $saved_total  = 0.0;
    $cart_url     = wc_get_cart_url();
    $checkout_url = wc_get_checkout_url();

    echo '<div class="headshop-cart__layout">';
    echo '  <div class="headshop-cart__body p-3">';

    foreach ($items as $key => $item) {
        $product = $item['data'] ?? null;
        if (!$product || !$product->exists()) continue;

        $name = $product->get_name();
        $qty  = (int)($item['quantity'] ?? 1);

        $thumb_src = '';
        $thumb_id  = $product->get_image_id();
        if ($thumb_id) $thumb_src = wp_get_attachment_image_url($thumb_id, 'thumbnail') ?: '';
        if (!$thumb_src) $thumb_src = wc_placeholder_img_src('thumbnail');

        $price_now     = (float)$product->get_price();
        $price_regular = (float)$product->get_regular_price();
        $line_now      = $price_now * $qty;
        $line_regular  = ($price_regular > 0 ? $price_regular : $price_now) * $qty;
        $saved_total  += max(0, $line_regular - $line_now);

        echo '<div class="d-flex gap-3 align-items-start py-3 border-bottom">';
        echo '  <img class="rounded-2 flex-shrink-0 border" src="' . esc_url($thumb_src) . '" alt="" width="56" height="56" loading="lazy" />';
        echo '  <div class="flex-grow-1">';
        echo '    <div class="fw-semibold text-secondary">' . esc_html($name) . '</div>';
        $meta = wc_get_formatted_cart_item_data($item);
        if ($meta) echo '<div class="text-muted small">' . wp_kses_post($meta) . '</div>';
        echo '    <div class="mt-2">';
        echo '      <span class="headshop-cart__price fw-semibold">' . wp_kses_post(wc_price($line_now)) . '</span>';
        if ($line_regular > $line_now) {
            echo '    <span class="text-muted text-decoration-line-through ms-2">' . wp_kses_post(wc_price($line_regular)) . '</span>';
        }
        echo '    </div>';
        echo '  </div>';
        echo '  <div class="text-end ms-2">';
        echo '    <div class="small text-muted">' . intval($qty) . '×</div>';
        echo '    <button type="button" class="btn btn-link p-0 mt-2 small text-muted cart-remove" data-cart-item-key="' . esc_attr($key) . '">Remover</button>';
        echo '  </div>';
        echo '</div>';
    }

    echo '  </div>';

    echo '<div class="headshop-cart__footer p-3 border-top">';
    $total = WC()->cart->get_total();
    echo '  <div class="d-flex justify-content-between fw-semibold">';
    echo '    <span>Total</span><span>' . wp_kses_post($total) . '</span>';
    echo '  </div>';

    $coupon = (float)WC()->cart->get_discount_total() + (float)WC()->cart->get_discount_tax();
    $economy = $coupon > 0 ? $coupon : $saved_total;
    if ($economy > 0) {
        echo '<div class="text-muted small mt-1">Você economizou ' . wp_kses_post(wc_price($economy)) . '!</div>';
    }

    echo '  <div class="mt-3 d-grid gap-2">';
    echo '    <a class="btn headshop-cart__action-btn fw-bold text-uppercase" href="' . esc_url($cart_url) . '"><span class="dashicons dashicons-cart"></span> Ver carrinho</a>';
    echo '    <a class="btn headshop-cart__action-btn fw-bold text-uppercase" href="' . esc_url($checkout_url) . '"><span class="dashicons dashicons-lock"></span> Finalizar compra</a>';
    echo '  </div>';
    echo '</div>';
    echo '</div>';
}

// AJAX remove item
function headshop_cart_remove_ajax() {
    check_ajax_referer('headshop_cart');
    if (!class_exists('WooCommerce') || !WC()->cart) {
        wp_send_json_error(array('message' => 'WooCommerce indisponível'));
    }
    $key = isset($_POST['cart_item_key']) ? wc_clean(wp_unslash($_POST['cart_item_key'])) : '';
    if (!$key || !isset(WC()->cart->get_cart()[$key])) {
        wp_send_json_error(array('message' => 'Item inválido'));
    }
    WC()->cart->remove_cart_item($key);
    WC()->cart->calculate_totals();
    ob_start();
    headshop_render_cart_dropdown();
    $html = ob_get_clean();
    wp_send_json_success(array(
        'count' => WC()->cart->get_cart_contents_count(),
        'html'  => $html,
    ));
}
add_action('wp_ajax_headshop_cart_remove', 'headshop_cart_remove_ajax');
add_action('wp_ajax_nopriv_headshop_cart_remove', 'headshop_cart_remove_ajax');


/* =====================================================================
   9. RECENT PRODUCTS SECTION
   ===================================================================== */

function headshop_recent_products() {
    if (!is_front_page() || !class_exists('WooCommerce')) return;

    $products = wc_get_products(array(
        'status'  => 'publish',
        'limit'   => 8,
        'orderby' => 'date',
        'order'   => 'DESC',
        'visibility' => 'visible',
    ));

    if (empty($products)) return;

    $placeholder = wc_placeholder_img_src('woocommerce_thumbnail');
    ?>
    <section class="headshop-recent-products py-5">
      <div class="container" style="max-width:1400px;">
        <h2 class="headshop-recent-products__title text-center mb-4">Produtos Recentes</h2>
        <div class="headshop-recent-products__grid">
          <?php foreach ($products as $product) :
              $id        = $product->get_id();
              $name      = $product->get_name();
              $link      = get_permalink($id);
              $img_id    = $product->get_image_id();
              $img_url   = $img_id ? wp_get_attachment_image_url($img_id, 'woocommerce_thumbnail') : $placeholder;
              $price_html = $product->get_price_html();
              $on_sale    = $product->is_on_sale();
          ?>
            <a href="<?= esc_url($link); ?>" class="headshop-recent-products__card">
              <?php if ($on_sale) : ?>
                <span class="headshop-recent-products__badge">Oferta</span>
              <?php endif; ?>
              <div class="headshop-recent-products__image-wrap">
                <img src="<?= esc_url($img_url); ?>" alt="<?= esc_attr($name); ?>" class="headshop-recent-products__image" loading="lazy" />
              </div>
              <div class="headshop-recent-products__info">
                <h3 class="headshop-recent-products__name"><?= esc_html($name); ?></h3>
                <div class="headshop-recent-products__price"><?= wp_kses_post($price_html); ?></div>
              </div>
            </a>
          <?php endforeach; ?>
        </div>
        <div class="text-center mt-4">
          <a href="<?= esc_url(get_permalink(wc_get_page_id('shop'))); ?>" class="btn headshop-recent-products__btn">Ver todos os produtos</a>
        </div>
      </div>
    </section>
    <?php
}


/* =====================================================================
   10. SALE PRODUCTS SECTION
   ===================================================================== */

function headshop_sale_products() {
    if (!is_front_page() || !class_exists('WooCommerce')) return;

    $on_sale_ids = wc_get_product_ids_on_sale();
    if (empty($on_sale_ids)) return;

    $products = wc_get_products(array(
        'status'     => 'publish',
        'limit'      => 8,
        'orderby'    => 'rand',
        'visibility' => 'visible',
        'include'    => $on_sale_ids,
    ));

    if (empty($products)) return;

    $placeholder = wc_placeholder_img_src('woocommerce_thumbnail');
    ?>
    <section class="headshop-sale-products py-5">
      <div class="container" style="max-width:1400px;">
        <div class="headshop-section-title">
          <span class="headshop-section-title__eyebrow">— Destaques da semana</span>
          <div class="headshop-section-title__row">
            <h2 class="headshop-section-title__text">EM OFERTA</h2>
            <div class="headshop-section-title__rule"></div>
          </div>
        </div>
        <div class="headshop-products-carousel">
          <button class="headshop-products-carousel__btn headshop-products-carousel__btn--prev" aria-label="Anterior">&#8592;</button>
          <div class="headshop-sale-products__grid headshop-products-carousel__track">
            <?php foreach ($products as $product) :
                $id         = $product->get_id();
                $name       = $product->get_name();
                $link       = get_permalink($id);
                $img_id     = $product->get_image_id();
                $img_url    = $img_id ? wp_get_attachment_image_url($img_id, 'woocommerce_thumbnail') : $placeholder;
                $price_html = $product->get_price_html();
                $regular    = (float) $product->get_regular_price();
                $sale       = (float) $product->get_sale_price();
                $discount   = $regular > 0 ? round((1 - $sale / $regular) * 100) : 0;
            ?>
              <a href="<?= esc_url($link); ?>" class="headshop-sale-products__card">
                <?php if ($discount > 0) : ?>
                  <span class="headshop-sale-products__badge">-<?= (int) $discount; ?>%</span>
                <?php else : ?>
                  <span class="headshop-sale-products__badge">OFERTA</span>
                <?php endif; ?>
                <div class="headshop-sale-products__image-wrap">
                  <img src="<?= esc_url($img_url); ?>" alt="<?= esc_attr($name); ?>" class="headshop-sale-products__image" loading="lazy" />
                </div>
                <div class="headshop-sale-products__info">
                  <h3 class="headshop-sale-products__name"><?= esc_html($name); ?></h3>
                  <div class="headshop-sale-products__price"><?= wp_kses_post($price_html); ?></div>
                </div>
              </a>
            <?php endforeach; ?>
          </div>
          <button class="headshop-products-carousel__btn headshop-products-carousel__btn--next" aria-label="Próximo">&#8594;</button>
        </div>
        <div class="text-center mt-4">
          <a href="<?= esc_url(get_permalink(wc_get_page_id('shop'))); ?>" class="btn headshop-sale-products__btn">Ver todos →</a>
        </div>
      </div>
    </section>
    <?php
}


/* =====================================================================
   11. NEW PRODUCTS SECTION
   ===================================================================== */

function headshop_new_products() {
    if (!is_front_page() || !class_exists('WooCommerce')) return;

    $products = wc_get_products(array(
        'status'     => 'publish',
        'limit'      => 8,
        'orderby'    => 'date',
        'order'      => 'DESC',
        'visibility' => 'visible',
    ));

    if (empty($products)) return;

    $placeholder = wc_placeholder_img_src('woocommerce_thumbnail');
    ?>
    <section class="headshop-new-products py-5">
      <div class="container" style="max-width:1400px;">
        <div class="headshop-section-title">
          <span class="headshop-section-title__eyebrow">— Acabou de chegar</span>
          <div class="headshop-section-title__row">
            <h2 class="headshop-section-title__text">NOVIDADES</h2>
            <div class="headshop-section-title__rule"></div>
          </div>
        </div>
        <div class="headshop-products-carousel">
          <button class="headshop-products-carousel__btn headshop-products-carousel__btn--prev" aria-label="Anterior">&#8592;</button>
          <div class="headshop-new-products__grid headshop-products-carousel__track">
            <?php foreach ($products as $product) :
                $id         = $product->get_id();
                $name       = $product->get_name();
                $link       = get_permalink($id);
                $img_id     = $product->get_image_id();
                $img_url    = $img_id ? wp_get_attachment_image_url($img_id, 'woocommerce_thumbnail') : $placeholder;
                $price_html = $product->get_price_html();
            ?>
              <a href="<?= esc_url($link); ?>" class="headshop-new-products__card">
                <span class="headshop-new-products__badge">NOVO</span>
                <div class="headshop-new-products__image-wrap">
                  <img src="<?= esc_url($img_url); ?>" alt="<?= esc_attr($name); ?>" class="headshop-new-products__image" loading="lazy" />
                </div>
                <div class="headshop-new-products__info">
                  <h3 class="headshop-new-products__name"><?= esc_html($name); ?></h3>
                  <div class="headshop-new-products__price"><?= wp_kses_post($price_html); ?></div>
                </div>
              </a>
            <?php endforeach; ?>
          </div>
          <button class="headshop-products-carousel__btn headshop-products-carousel__btn--next" aria-label="Próximo">&#8594;</button>
        </div>
        <div class="text-center mt-4">
          <a href="<?= esc_url(get_permalink(wc_get_page_id('shop'))); ?>" class="btn headshop-new-products__btn">Ver todos →</a>
        </div>
      </div>
    </section>
    <?php
}


/* =====================================================================
   12. IMPORTAR IMAGENS DE PRODUTOS (admin tool)
   ===================================================================== */

add_action('admin_menu', 'headshop_register_image_import_page');
function headshop_register_image_import_page() {
    add_submenu_page(
        'woocommerce',
        'Importar Imagens de Produtos',
        'Importar Imagens',
        'manage_woocommerce',
        'headshop-import-images',
        'headshop_image_import_page'
    );
}

function headshop_image_import_page() {
    if (!current_user_can('manage_woocommerce')) return;

    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    $results = array();

    if (
        isset($_POST['headshop_import_nonce']) &&
        wp_verify_nonce($_POST['headshop_import_nonce'], 'headshop_import_images') &&
        !empty($_POST['headshop_image_list'])
    ) {
        $lines = explode("\n", sanitize_textarea_field(wp_unslash($_POST['headshop_image_list'])));

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || strpos($line, '#') === 0) continue;

            $parts = preg_split('/[\|,;\t]+/', $line, 2);
            if (count($parts) < 2) {
                $results[] = array('line' => $line, 'status' => 'error', 'msg' => 'Formato inválido — use: sku_ou_id|url');
                continue;
            }

            $identifier = trim($parts[0]);
            $img_url    = trim($parts[1]);

            if (empty($identifier) || empty($img_url)) {
                $results[] = array('line' => $line, 'status' => 'error', 'msg' => 'Identificador ou URL vazio');
                continue;
            }

            // Resolve product
            if (is_numeric($identifier)) {
                $post = get_post(intval($identifier));
                $product_id = ($post && $post->post_type === 'product') ? $post->ID : 0;
            } else {
                $product_id = wc_get_product_id_by_sku($identifier);
            }

            if (!$product_id) {
                $results[] = array('line' => $identifier, 'status' => 'error', 'msg' => 'Produto não encontrado');
                continue;
            }

            // Skip if already has image and overwrite not checked
            if (empty($_POST['headshop_overwrite']) && has_post_thumbnail($product_id)) {
                $results[] = array('line' => $identifier, 'status' => 'skip', 'msg' => 'Já possui imagem (ignorado)');
                continue;
            }

            // Download and attach image
            $attachment_id = media_sideload_image($img_url, $product_id, null, 'id');

            if (is_wp_error($attachment_id)) {
                $results[] = array('line' => $identifier, 'status' => 'error', 'msg' => $attachment_id->get_error_message());
                continue;
            }

            set_post_thumbnail($product_id, $attachment_id);
            $product_name = get_the_title($product_id);
            $results[] = array('line' => $identifier, 'status' => 'ok', 'msg' => 'OK — ' . esc_html($product_name));
        }
    }
    ?>
    <div class="wrap">
        <h1>Importar Imagens de Produtos</h1>
        <p>Cole abaixo uma linha por produto no formato <code>sku_ou_id|url_da_imagem</code>. Linhas começando com <code>#</code> são ignoradas.</p>

        <?php if (!empty($results)) : ?>
        <div style="background:#f9f9f9;border:1px solid #ddd;border-radius:6px;padding:16px;margin-bottom:20px;max-height:300px;overflow-y:auto;">
            <strong>Resultados:</strong>
            <table style="width:100%;border-collapse:collapse;margin-top:10px;">
                <thead><tr style="background:#e8e8e8;">
                    <th style="text-align:left;padding:6px 10px;">Produto</th>
                    <th style="text-align:left;padding:6px 10px;">Status</th>
                    <th style="text-align:left;padding:6px 10px;">Mensagem</th>
                </tr></thead>
                <tbody>
                <?php foreach ($results as $r) :
                    $color = $r['status'] === 'ok' ? '#4caf50' : ($r['status'] === 'skip' ? '#ff9800' : '#f44336');
                ?>
                <tr style="border-bottom:1px solid #eee;">
                    <td style="padding:6px 10px;"><?= esc_html($r['line']); ?></td>
                    <td style="padding:6px 10px;color:<?= $color; ?>;font-weight:bold;"><?= esc_html(strtoupper($r['status'])); ?></td>
                    <td style="padding:6px 10px;"><?= esc_html($r['msg']); ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <form method="post">
            <?php wp_nonce_field('headshop_import_images', 'headshop_import_nonce'); ?>
            <table class="form-table">
                <tr>
                    <th><label for="headshop_image_list">Lista de imagens</label></th>
                    <td>
                        <textarea id="headshop_image_list" name="headshop_image_list" rows="20" cols="80" class="large-text code"
                            placeholder="# Exemplo:&#10;meu-sku-001|https://site.com/imagem1.jpg&#10;123|https://site.com/imagem2.png"><?= isset($_POST['headshop_image_list']) ? esc_textarea(wp_unslash($_POST['headshop_image_list'])) : ''; ?></textarea>
                        <p class="description">Separadores aceitos: <code>|</code> (pipe), <code>,</code> (vírgula), <code>;</code> (ponto e vírgula) ou TAB.</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="headshop_overwrite">Substituir imagem existente</label></th>
                    <td>
                        <input type="checkbox" id="headshop_overwrite" name="headshop_overwrite" value="1" />
                        <label for="headshop_overwrite">Sobrescrever produtos que já possuem imagem</label>
                    </td>
                </tr>
            </table>
            <?php submit_button('Importar Imagens', 'primary large'); ?>
        </form>
    </div>
    <?php
}


/* =====================================================================
   13. HIDE HOMEPAGE TITLE
   ===================================================================== */

add_filter('the_title', function ($title, $post_id) {
    if (is_admin()) return $title;
    if ((is_front_page() || is_home()) && in_the_loop()) return '';
    return $title;
}, 10, 2);

// Strip default WC homepage sections
add_filter('the_content', function ($content) {
    if (!is_front_page()) return $content;
    $labels = array('Compre por categoria', 'Compre por marca', 'Favoritos dos fãs');
    $escaped = array_map(function ($s) { return preg_quote($s, '/'); }, $labels);
    $regex   = implode('|', $escaped);
    return preg_replace('/<h[1-6][^>]*>\s*(?:' . $regex . ')\s*<\/h[1-6]>.*?(?=(?:<h[1-6][^>]*>)|$)/is', '', $content);
}, 20);
