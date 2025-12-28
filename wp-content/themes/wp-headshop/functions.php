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

	if ( is_front_page() && get_post_type() !== 'banner' ) {
		wp_enqueue_script( 'storefront-child-banner-slider', get_stylesheet_directory_uri() . '/assets/js/banner-slider.js', array(), null, true );
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

function storefront_child_homepage_banner_slider() {
	if ( ! is_front_page() ) { return; }

	$banners = new WP_Query( array(
		'post_type'      => 'banner',
		'posts_per_page' => -1,
		'orderby'        => 'menu_order',
		'order'          => 'ASC',
		'post_status'    => 'publish',
	) );

	if ( ! $banners->have_posts() ) { return; }

	echo '<div class="banner-slider-wrapper">';
	echo '<div class="banner-slider">';
	while ( $banners->have_posts() ) {
		$banners->the_post();
		$img_id  = get_post_thumbnail_id();
		$img_url = $img_id ? wp_get_attachment_image_url( $img_id, 'full' ) : '';
		if ( ! $img_url ) { continue; }

		echo '<div class="banner-slide" style="background-image: url(' . esc_url( $img_url ) . ');"></div>';
	}
	echo '</div>';
	echo '<button class="banner-nav banner-prev" aria-label="Anterior">‹</button>';
	echo '<button class="banner-nav banner-next" aria-label="Próximo">›</button>';
	echo '<div class="banner-dots"></div>';
	echo '</div>';
	wp_reset_postdata();
}
add_action( 'storefront_before_content', 'storefront_child_homepage_banner_slider', 5 );

function storefront_child_register_settings() {
	add_menu_page(
		'Configurações da Home',
		'Home Config',
		'manage_options',
		'storefront-child-home-config',
		'storefront_child_home_config_page',
		'dashicons-admin-home',
		61
	);
}
add_action( 'admin_menu', 'storefront_child_register_settings' );

function storefront_child_home_config_page() {
	if ( ! current_user_can( 'manage_options' ) ) { return; }

	if ( isset( $_POST['storefront_child_home_cats_nonce'] ) && wp_verify_nonce( $_POST['storefront_child_home_cats_nonce'], 'save_home_categories' ) ) {
		$selected_cats = isset( $_POST['home_categories_ordered'] ) ? $_POST['home_categories_ordered'] : '';
		$ordered_array = array_filter( array_map( 'intval', explode( ',', $selected_cats ) ) );
		update_option( 'storefront_child_home_categories', $ordered_array );
		echo '<div class="notice notice-success is-dismissible"><p>Configurações salvas com sucesso!</p></div>';
	}

	$saved_cats = get_option( 'storefront_child_home_categories', array() );
	$all_categories = get_terms( array(
		'taxonomy'   => 'product_cat',
		'hide_empty' => false,
	) );

	function storefront_child_sort_categories_hierarchical( $categories ) {
		$sorted = array();
		$by_parent = array();

		foreach ( $categories as $cat ) {
			if ( ! isset( $by_parent[ $cat->parent ] ) ) {
				$by_parent[ $cat->parent ] = array();
			}
			$by_parent[ $cat->parent ][] = $cat;
		}

		function add_children( &$sorted, $by_parent, $parent_id = 0, $level = 0 ) {
			if ( ! isset( $by_parent[ $parent_id ] ) ) { return; }
			foreach ( $by_parent[ $parent_id ] as $cat ) {
				$cat->level = $level;
				$sorted[] = $cat;
				add_children( $sorted, $by_parent, $cat->term_id, $level + 1 );
			}
		}

		add_children( $sorted, $by_parent );
		return $sorted;
	}

	$all_categories = storefront_child_sort_categories_hierarchical( $all_categories );

	?>
	<div class="wrap">
		<h1>Configurações da Home</h1>
		<div style="max-width: 1200px;">
			<form method="post" action="">
				<?php wp_nonce_field( 'save_home_categories', 'storefront_child_home_cats_nonce' ); ?>
				<input type="hidden" name="home_categories_ordered" id="homeCategoriesOrdered" value="<?php echo esc_attr( implode( ',', $saved_cats ) ); ?>" />
				
				<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin: 20px 0;">
					<div>
						<h2>Categorias Disponíveis</h2>
						<p>Arraste as categorias para a direita para exibir na home:</p>
						<div id="availableCategories" class="category-sortable-list" style="background: #f9f9f9; border: 2px dashed #ccc; border-radius: 8px; padding: 15px; min-height: 400px;">
							<?php foreach ( $all_categories as $cat ) : 
								if ( in_array( $cat->term_id, $saved_cats ) ) continue;
							?>
								<div class="category-item" data-id="<?php echo esc_attr( $cat->term_id ); ?>" draggable="true" style="background: #fff; border: 1px solid #ddd; border-radius: 4px; padding: 12px; margin-bottom: 8px; cursor: move; display: flex; align-items: center; gap: 10px;">
									<span class="dashicons dashicons-move" style="color: #999;"></span>
									<span style="flex: 1;">
										<?php echo str_repeat( '<span style="color: #ccc;">└</span> ', $cat->level ); ?>
										<?php echo esc_html( $cat->name ); ?>
										<small style="color: #999;">(<?php echo $cat->count; ?>)</small>
									</span>
								</div>
							<?php endforeach; ?>
						</div>
					</div>

					<div>
						<h2>Categorias na Home <span style="font-size: 14px; font-weight: normal; color: #999;">(ordem de exibição)</span></h2>
						<p>Arraste para reordenar ou remova arrastando para a esquerda:</p>
						<div id="selectedCategories" class="category-sortable-list" style="background: #e8f5e9; border: 2px solid #4caf50; border-radius: 8px; padding: 15px; min-height: 400px;">
							<?php 
							foreach ( $saved_cats as $cat_id ) :
								$cat = get_term( $cat_id, 'product_cat' );
								if ( ! $cat || is_wp_error( $cat ) ) continue;
								$level = 0;
								$parent_id = $cat->parent;
								while ( $parent_id > 0 ) {
									$parent = get_term( $parent_id, 'product_cat' );
									$parent_id = $parent->parent;
									$level++;
								}
							?>
								<div class="category-item" data-id="<?php echo esc_attr( $cat_id ); ?>" draggable="true" style="background: #fff; border: 1px solid #4caf50; border-radius: 4px; padding: 12px; margin-bottom: 8px; cursor: move; display: flex; align-items: center; gap: 10px;">
									<span class="dashicons dashicons-move" style="color: #4caf50;"></span>
									<span style="flex: 1;">
										<?php echo str_repeat( '<span style="color: #ccc;">└</span> ', $level ); ?>
										<?php echo esc_html( $cat->name ); ?>
										<small style="color: #999;">(<?php echo $cat->count; ?>)</small>
									</span>
								</div>
							<?php endforeach; ?>
						</div>
					</div>
				</div>

				<?php submit_button( 'Salvar Configurações', 'primary', 'submit', false ); ?>
			</form>
		</div>
	</div>

	<style>
		.category-item:hover {
			box-shadow: 0 2px 8px rgba(0,0,0,0.1);
			transform: translateY(-2px);
			transition: all 0.2s ease;
		}
		.category-sortable-list.drag-over {
			background: #fff3cd !important;
			border-color: #ffc107 !important;
		}
		.category-item.dragging {
			opacity: 0.5;
		}
	</style>

	<script>
	(function() {
		const available = document.getElementById('availableCategories');
		const selected = document.getElementById('selectedCategories');
		const input = document.getElementById('homeCategoriesOrdered');
		let draggedElement = null;

		function setupDragAndDrop(container) {
			const items = container.querySelectorAll('.category-item');
			items.forEach(item => {
				item.addEventListener('dragstart', function(e) {
					draggedElement = this;
					this.classList.add('dragging');
					e.dataTransfer.effectAllowed = 'move';
				});

				item.addEventListener('dragend', function() {
					this.classList.remove('dragging');
					available.classList.remove('drag-over');
					selected.classList.remove('drag-over');
				});
			});
		}

		[available, selected].forEach(container => {
			container.addEventListener('dragover', function(e) {
				e.preventDefault();
				e.dataTransfer.dropEffect = 'move';
				this.classList.add('drag-over');
			});

			container.addEventListener('dragleave', function() {
				this.classList.remove('drag-over');
			});

			container.addEventListener('drop', function(e) {
				e.preventDefault();
				this.classList.remove('drag-over');
				if (draggedElement) {
					this.appendChild(draggedElement);
					updateHiddenInput();
				}
			});

			setupDragAndDrop(container);
		});

		function updateHiddenInput() {
			const selectedItems = selected.querySelectorAll('.category-item');
			const ids = Array.from(selectedItems).map(item => item.getAttribute('data-id'));
			input.value = ids.join(',');
		}
	})();
	</script>
	<?php
}

function storefront_child_homepage_categories_section() {
	if ( ! is_front_page() || ! class_exists( 'WooCommerce' ) ) { return; }

	$selected_cats = get_option( 'storefront_child_home_categories', array() );

	if ( empty( $selected_cats ) ) {
		$categories = get_terms( array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => true,
			'parent'     => 0,
		) );
	} else {
		$categories = get_terms( array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => false,
			'include'    => $selected_cats,
			'orderby'    => 'include',
		) );
	}

	if ( empty( $categories ) || is_wp_error( $categories ) ) { return; }

	$placeholder_url = wc_placeholder_img_src( 'woocommerce_thumbnail' );

	echo '<section class="categories-section">';
	echo '<div class="categories-grid">';

	foreach ( $categories as $category ) {
		$cat_id      = $category->term_id;
		$cat_name    = $category->name;
		$cat_link    = get_term_link( $cat_id, 'product_cat' );
		$thumbnail_id = get_term_meta( $cat_id, 'thumbnail_id', true );
		$cat_img     = $thumbnail_id ? wp_get_attachment_url( $thumbnail_id ) : $placeholder_url;

		echo '<a href="' . esc_url( $cat_link ) . '" class="category-card">';
		echo '<div class="category-image" style="background-image: url(' . esc_url( $cat_img ) . ');"></div>';
		echo '<h3 class="category-name">' . esc_html( $cat_name ) . '</h3>';
		echo '</a>';
	}

	echo '</div>';
	echo '</section>';
}
add_action( 'storefront_before_content', 'storefront_child_homepage_categories_section', 10 );

// Fullscreen search overlay (rendered near footer to sit above everything)
function storefront_child_search_overlay() {
    $action = esc_url( home_url( '/' ) );
    $is_wc  = class_exists( 'WooCommerce' );

    echo '<div id="headerSearchOverlay" class="search-overlay" aria-hidden="true">';
    echo '  <div class="search-overlay-inner">';
    echo '    <form role="search" method="get" class="search-overlay-form" action="' . $action . '">';
    echo '      <input id="headerSearchInput" class="search-overlay-input" type="search" name="s" placeholder="Pesquisar" autocomplete="off" />';
    if ( $is_wc ) {
        echo '      <input type="hidden" name="post_type" value="product" />';
    }
    echo '      <button class="search-overlay-submit" type="submit">Buscar</button>';
    echo '    </form>';
    echo '  </div>';
    echo '</div>';
}
add_action( 'wp_footer', 'storefront_child_search_overlay', 5 );

// Enqueue minimal script to toggle the search form
function storefront_child_enqueue_search_toggle_script() {
    $script = "document.addEventListener('DOMContentLoaded',function(){var btn=document.getElementById('searchToggleBtn');var overlay=document.getElementById('headerSearchOverlay');var inner=overlay?overlay.querySelector('.search-overlay-inner'):null;var input=document.getElementById('headerSearchInput');if(!btn||!overlay) return;function openOverlay(){overlay.classList.add('is-open');overlay.setAttribute('aria-hidden','false');document.body.classList.add('search-overlay-open');if(input){setTimeout(function(){input.focus();},0);}}function closeOverlay(){overlay.classList.remove('is-open');overlay.setAttribute('aria-hidden','true');document.body.classList.remove('search-overlay-open');}btn.addEventListener('click',function(e){e.preventDefault();if(overlay.classList.contains('is-open')){closeOverlay();}else{openOverlay();}});overlay.addEventListener('click',function(){closeOverlay();});if(inner){inner.addEventListener('click',function(e){e.stopPropagation();});}document.addEventListener('keydown',function(e){if(e.key==='Escape'&&overlay.classList.contains('is-open')){closeOverlay();}});});";
    wp_add_inline_script( 'storefront-child-scripts', $script );
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

    $bag_url = get_stylesheet_directory_uri() . '/assets/img/bag.png';

    echo '<div class="site-header-cart header-actions d-flex align-items-center justify-content-end" id="headerActions">';
    echo '  <button id="searchToggleBtn" class="header-icon-btn header-search-btn" aria-label="Search" title="Pesquisar" type="button">';
    echo '    <span class="header-search-icon" aria-hidden="true"></span>';
    echo '  </button>';

    echo '  <div class="custom-header-cart d-inline-block position-relative" id="customHeaderCart">';
    echo '    <a class="cart-icon-link" href="' . esc_url( $cart_url ) . '" aria-label="Carrinho">';
    echo '      <img class="cart-icon-img" src="' . esc_url( $bag_url ) . '" alt="" width="40" height="40" loading="lazy" />';
    echo '      <span class="cart-count">' . intval( $count ) . '</span>';
    echo '    </a>';
    echo '    <div class="cart-dropdown position-absolute end-0 top-100 mt-2" id="cartDropdown" style="display:none;" data-ajax-url="' . esc_url( admin_url( 'admin-ajax.php' ) ) . '" data-nonce="' . esc_attr( $nonce ) . '">';
    echo '      <div class="cart-dropdown-inner p-0">';
    storefront_child_render_cart_dropdown_inner();
    echo '      </div>';
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

    echo '<div class="cart-dropdown-layout">';
    echo '  <div class="cart-dropdown-body p-3">';

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

    echo '  </div>';

    echo '  <div class="cart-dropdown-footer p-3">';

    // Total row (full total incl. coupons/discounts)
    $total_html = WC()->cart->get_total();
    echo '    <div class="d-flex justify-content-between fw-semibold cart-total-row">';
    echo '      <span>Total</span>';
    echo '      <span>' . wp_kses_post( $total_html ) . '</span>';
    echo '    </div>';
    $coupon_discount = (float) WC()->cart->get_discount_total() + (float) WC()->cart->get_discount_tax();
    $economy = $coupon_discount > 0 ? $coupon_discount : $saved_total;
    if ( $economy > 0 ) {
        echo '    <div class="text-muted small mt-1">Você economizou ' . wp_kses_post( wc_price( $economy ) ) . '!</div>';
    }

    // Action buttons
    echo '    <div class="mt-3 d-grid gap-2">';
    echo '      <a class="btn btn-lg cart-action-btn cart-action-cart fw-bold text-uppercase d-flex align-items-center justify-content-center gap-2" href="' . esc_url( $cart_url ) . '">';
    echo '        <span class="dashicons dashicons-cart"></span> Ver carrinho';
    echo '      </a>';
    echo '      <a class="btn btn-lg cart-action-btn cart-action-checkout fw-bold text-uppercase d-flex align-items-center justify-content-center gap-2" href="' . esc_url( $checkout_url ) . '">';
    echo '        <span class="dashicons dashicons-lock"></span> Finalizar compra';
    echo '      </a>';
    echo '    </div>';

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

function storefront_child_register_banner_cpt() {
	$labels = array(
		'name'                  => 'Banners',
		'singular_name'         => 'Banner',
		'menu_name'             => 'Banners',
		'name_admin_bar'        => 'Banner',
		'add_new'               => 'Adicionar Novo',
		'add_new_item'          => 'Adicionar Novo Banner',
		'new_item'              => 'Novo Banner',
		'edit_item'             => 'Editar Banner',
		'view_item'             => 'Ver Banner',
		'all_items'             => 'Todos os Banners',
		'search_items'          => 'Pesquisar Banners',
		'not_found'             => 'Nenhum banner encontrado.',
		'not_found_in_trash'    => 'Nenhum banner encontrado na lixeira.',
	);

	$args = array(
		'labels'                => $labels,
		'public'                => false,
		'publicly_queryable'    => false,
		'show_ui'               => true,
		'show_in_menu'          => true,
		'query_var'             => false,
		'rewrite'               => false,
		'capability_type'       => 'post',
		'has_archive'           => false,
		'hierarchical'          => false,
		'menu_position'         => 5,
		'menu_icon'             => 'dashicons-images-alt2',
		'supports'              => array( 'title', 'thumbnail' ),
	);

	register_post_type( 'banner', $args );
}
add_action( 'init', 'storefront_child_register_banner_cpt' );

/**
 * Remove homepage sections and title via theme PHP
 */
add_action( 'init', function() {
	// Remove the child theme categories section from the homepage output
	remove_action( 'storefront_before_content', 'storefront_child_homepage_categories_section', 10 );
});

/**
 * Hide the post/page title on the front page (remove "Início")
 */
add_filter( 'the_title', function( $title, $post_id ) {
	if ( is_admin() ) {
		return $title;
	}

	// Only alter the main loop title on the front page
	if ( ( is_front_page() || is_home() ) && in_the_loop() ) {
		return '';
	}

	return $title;
}, 10, 2 );

/**
 * Strip homepage sections that have explicit headings so they won't render
 * (targets headings like "Compre por categoria", "Compre por marca", "Favoritos dos fãs").
 */
add_filter( 'the_content', function( $content ) {
	if ( ! is_front_page() ) {
		return $content;
	}

	$labels = array(
		'Compre por categoria',
		'Compre por marca',
		'Favoritos dos fãs',
		'Favoritos dos fa\xE7', // fallback without diacritics encoded if needed
	);

	$escaped = array_map( function( $s ) { return preg_quote( $s, '/' ); }, $labels );
	$regex_label = implode( '|', $escaped );

	// Remove from the heading up to the next heading or end of content (non-greedy)
	$pattern = '/<h[1-6][^>]*>\s*(?:' . $regex_label . ')\s*<\/h[1-6]>.*?(?=(?:<h[1-6][^>]*>)|$)/is';

	$content = preg_replace( $pattern, '', $content );

	return $content;
}, 20 );
