<?php get_header(); ?>

<main>
    <!-- Banner -->
    <section class="relative">
        <div class="h-72 md:h-96 bg-cover bg-center" style="background-image:url('<?php echo esc_url( get_theme_mod('headshop_banner_image') ?: 'https://images.unsplash.com/photo-1600948836101-f9ffda59d250?q=80&w=2000&auto=format&fit=crop' ); ?>')"></div>
        <div class="absolute inset-0 bg-black bg-opacity-30 flex items-center">
            <div class="container mx-auto px-4">
                <h1 class="text-white text-3xl md:text-5xl font-extrabold"><?php echo esc_html( get_theme_mod('headshop_banner_title', 'Headshop') ); ?></h1>
                <p class="text-white/90 mt-2 max-w-xl"><?php echo esc_html( get_theme_mod('headshop_banner_subtitle', 'Tudo para sua experiência: sedas, bongs, vaporizadores e acessórios.') ); ?></p>
                <?php
                $cta_text = trim((string) get_theme_mod('headshop_banner_cta_text', 'Ver loja'));
                $cta_url  = trim((string) get_theme_mod('headshop_banner_cta_url', ''));
                if ($cta_text !== '') {
                    $href = $cta_url !== '' ? $cta_url : ( function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : '#' );
                    echo '<a class="inline-block mt-4 px-5 py-2 bg-green-600 text-white rounded hover:bg-green-700" href="' . esc_url($href) . '">' . esc_html($cta_text) . '</a>';
                }
                ?>
            </div>
        </div>
    </section>

    <!-- Categorias -->
    <section class="container mx-auto px-4 py-12">
        <h2 class="text-2xl font-bold mb-6">Categorias</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <?php
            if ( function_exists('headshop_get_featured_categories') ) {
                $product_cats = headshop_get_featured_categories();
                foreach ($product_cats as $cat) :
                    $thumbnail_id = get_term_meta($cat->term_id, 'thumbnail_id', true);
                    $image = $thumbnail_id ? wp_get_attachment_image_url($thumbnail_id, 'headshop-category') : 'https://via.placeholder.com/600x400?text=Categoria';
                    ?>
                    <a href="<?php echo esc_url( get_term_link($cat) ); ?>" class="group block rounded-lg overflow-hidden border bg-white">
                        <div class="aspect-[3/2] bg-gray-100 bg-cover bg-center" style="background-image:url('<?php echo esc_url($image); ?>')"></div>
                        <div class="p-3">
                            <h3 class="font-semibold group-hover:text-green-700 transition-colors"><?php echo esc_html($cat->name); ?></h3>
                        </div>
                    </a>
                <?php endforeach; } ?>
        </div>
    </section>

    <!-- Carrossel de Produtos -->
    <section class="bg-white py-12">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold">Destaques</h2>
                <?php
                $shop_link = '#';
                if ( function_exists('wc_get_page_permalink') ) {
                    $shop_link = wc_get_page_permalink('shop');
                } elseif ( function_exists('get_permalink') && function_exists('wc_get_page_id') ) {
                    $shop_id = wc_get_page_id('shop');
                    if ( $shop_id && $shop_id > 0 ) {
                        $shop_link = get_permalink($shop_id);
                    }
                }
                ?>
                <a href="<?php echo esc_url( $shop_link ); ?>" class="text-green-700 hover:underline">Ver loja</a>
            </div>

            <div class="swiper product-swiper">
                <div class="swiper-wrapper">
                    <?php
                    if ( class_exists('WooCommerce') ) {
                        $products = wc_get_products([
                            'limit' => 12,
                            'status' => 'publish',
                            'orderby' => 'date',
                            'order' => 'DESC',
                        ]);
                        foreach ($products as $product) {
                            $gallery = headshop_get_product_gallery($product->get_id(), 5);
                            $link = get_permalink($product->get_id());
                            ?>
                            <div class="swiper-slide">
                                <div class="border rounded-lg overflow-hidden bg-white">
                                    <div class="aspect-[3/4] bg-gray-100 relative group">
                                        <!-- Main image -->
                                        <a href="<?php echo esc_url($link); ?>" class="block h-full">
                                            <?php echo headshop_responsive_image($gallery[0], 'w-full h-full object-cover group-hover:scale-105 transition-transform duration-300', true); ?>
                                        </a>
                                        
                                        <!-- Gallery thumbnails (4 additional images) -->
                                        <div class="absolute bottom-2 left-2 right-2 flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                            <?php for ($i = 1; $i < 5; $i++) : ?>
                                                <?php if (isset($gallery[$i])) : ?>
                                                    <a href="<?php echo esc_url($gallery[$i]['url']); ?>" 
                                                       data-lightbox="product-<?php echo $product->get_id(); ?>" 
                                                       data-title="<?php echo esc_attr($gallery[$i]['title']); ?>"
                                                       class="flex-1 h-8 rounded overflow-hidden">
                                                        <?php echo headshop_responsive_image($gallery[$i], 'w-full h-full object-cover', false); ?>
                                                    </a>
                                                <?php endif; ?>
                                            <?php endfor; ?>
                                        </div>
                                        
                                        <!-- Gallery indicator -->
                                        <div class="absolute top-2 right-2 bg-black bg-opacity-50 text-white text-xs px-2 py-1 rounded">
                                            <?php echo count(array_filter($gallery, function($img) { return $img['id'] > 0; })); ?>/5
                                        </div>
                                    </div>
                                    <div class="p-3">
                                        <h3 class="font-semibold text-sm line-clamp-2"><?php echo esc_html($product->get_name()); ?></h3>
                                        <div class="mt-2 font-bold text-green-700"><?php echo wp_kses_post($product->get_price_html()); ?></div>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                    } else {
                        echo '<p class="text-gray-600">Instale e ative o WooCommerce para ver os produtos.</p>';
                    }
                    ?>
                </div>
                <div class="swiper-pagination"></div>
                <div class="swiper-button-prev"></div>
                <div class="swiper-button-next"></div>
            </div>
        </div>
    </section>

    <!-- Novidades -->
    <section class="bg-white py-12">
        <div class="container mx-auto px-4">
            <h2 class="text-2xl font-bold mb-6">Novidades</h2>
            <div class="swiper product-swiper">
                <div class="swiper-wrapper">
                    <?php if ( class_exists('WooCommerce') ) {
                        $new = wc_get_products([
                            'limit' => 12,
                            'orderby' => 'date',
                            'order' => 'DESC',
                        ]);
                        foreach ($new as $product) { 
                            $gallery = headshop_get_product_gallery($product->get_id(), 5);
                            $link = get_permalink($product->get_id());
                            ?>
                            <div class="swiper-slide">
                                <div class="border rounded-lg overflow-hidden bg-white">
                                    <div class="aspect-[3/4] bg-gray-100 relative group">
                                        <a href="<?php echo esc_url($link); ?>" class="block h-full">
                                            <?php echo headshop_responsive_image($gallery[0], 'w-full h-full object-cover group-hover:scale-105 transition-transform duration-300', true); ?>
                                        </a>
                                        <div class="absolute bottom-2 left-2 right-2 flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                            <?php for ($i = 1; $i < 5; $i++) : ?>
                                                <?php if (isset($gallery[$i])) : ?>
                                                    <a href="<?php echo esc_url($gallery[$i]['url']); ?>" 
                                                       data-lightbox="product-<?php echo $product->get_id(); ?>" 
                                                       data-title="<?php echo esc_attr($gallery[$i]['title']); ?>"
                                                       class="flex-1 h-8 rounded overflow-hidden">
                                                        <?php echo headshop_responsive_image($gallery[$i], 'w-full h-full object-cover', false); ?>
                                                    </a>
                                                <?php endif; ?>
                                            <?php endfor; ?>
                                        </div>
                                        <div class="absolute top-2 right-2 bg-black bg-opacity-50 text-white text-xs px-2 py-1 rounded">
                                            <?php echo count(array_filter($gallery, function($img) { return $img['id'] > 0; })); ?>/5
                                        </div>
                                    </div>
                                    <div class="p-3">
                                        <h3 class="font-semibold text-sm line-clamp-2"><?php echo esc_html($product->get_name()); ?></h3>
                                        <div class="mt-2 font-bold text-green-700"><?php echo wp_kses_post($product->get_price_html()); ?></div>
                                    </div>
                                </div>
                            </div>
                        <?php } } ?>
                </div>
                <div class="swiper-pagination"></div>
                <div class="swiper-button-prev"></div>
                <div class="swiper-button-next"></div>
            </div>
        </div>
    </section>

    <!-- Promoções -->
    <section class="bg-white py-12">
        <div class="container mx-auto px-4">
            <h2 class="text-2xl font-bold mb-6">Promoções</h2>
            <div class="swiper product-swiper">
                <div class="swiper-wrapper">
                    <?php if ( class_exists('WooCommerce') ) {
                        $on_sale_ids = wc_get_product_ids_on_sale();
                        $sale = wc_get_products([
                            'limit' => 12,
                            'include' => $on_sale_ids,
                        ]);
                        foreach ($sale as $product) { 
                            $gallery = headshop_get_product_gallery($product->get_id(), 5);
                            $link = get_permalink($product->get_id());
                            ?>
                            <div class="swiper-slide">
                                <div class="border rounded-lg overflow-hidden bg-white">
                                    <div class="aspect-[3/4] bg-gray-100 relative group">
                                        <a href="<?php echo esc_url($link); ?>" class="block h-full">
                                            <?php echo headshop_responsive_image($gallery[0], 'w-full h-full object-cover group-hover:scale-105 transition-transform duration-300', true); ?>
                                        </a>
                                        <div class="absolute bottom-2 left-2 right-2 flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                            <?php for ($i = 1; $i < 5; $i++) : ?>
                                                <?php if (isset($gallery[$i])) : ?>
                                                    <a href="<?php echo esc_url($gallery[$i]['url']); ?>" 
                                                       data-lightbox="product-<?php echo $product->get_id(); ?>" 
                                                       data-title="<?php echo esc_attr($gallery[$i]['title']); ?>"
                                                       class="flex-1 h-8 rounded overflow-hidden">
                                                        <?php echo headshop_responsive_image($gallery[$i], 'w-full h-full object-cover', false); ?>
                                                    </a>
                                                <?php endif; ?>
                                            <?php endfor; ?>
                                        </div>
                                        <div class="absolute top-2 right-2 bg-red-500 text-white text-xs px-2 py-1 rounded">
                                            PROMOÇÃO
                                        </div>
                                        <div class="absolute top-2 left-2 bg-black bg-opacity-50 text-white text-xs px-2 py-1 rounded">
                                            <?php echo count(array_filter($gallery, function($img) { return $img['id'] > 0; })); ?>/5
                                        </div>
                                    </div>
                                    <div class="p-3">
                                        <h3 class="font-semibold text-sm line-clamp-2"><?php echo esc_html($product->get_name()); ?></h3>
                                        <div class="mt-2 font-bold text-green-700"><?php echo wp_kses_post($product->get_price_html()); ?></div>
                                    </div>
                                </div>
                            </div>
                        <?php } } ?>
                </div>
                <div class="swiper-pagination"></div>
                <div class="swiper-button-prev"></div>
                <div class="swiper-button-next"></div>
            </div>
        </div>
    </section>

    <!-- Mais vendidos -->
    <section class="bg-white py-12">
        <div class="container mx-auto px-4">
            <h2 class="text-2xl font-bold mb-6">Mais vendidos</h2>
            <div class="swiper product-swiper">
                <div class="swiper-wrapper">
                    <?php if ( class_exists('WooCommerce') ) {
                        // Query best sellers
                        $args = [
                            'status' => 'publish',
                            'limit' => 12,
                            'orderby' => 'meta_value_num',
                            'meta_key' => 'total_sales',
                            'order' => 'DESC',
                        ];
                        $bests = wc_get_products($args);
                        foreach ($bests as $product) { 
                            $gallery = headshop_get_product_gallery($product->get_id(), 5);
                            $link = get_permalink($product->get_id());
                            ?>
                            <div class="swiper-slide">
                                <div class="border rounded-lg overflow-hidden bg-white">
                                    <div class="aspect-[3/4] bg-gray-100 relative group">
                                        <a href="<?php echo esc_url($link); ?>" class="block h-full">
                                            <?php echo headshop_responsive_image($gallery[0], 'w-full h-full object-cover group-hover:scale-105 transition-transform duration-300', true); ?>
                                        </a>
                                        <div class="absolute bottom-2 left-2 right-2 flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                            <?php for ($i = 1; $i < 5; $i++) : ?>
                                                <?php if (isset($gallery[$i])) : ?>
                                                    <a href="<?php echo esc_url($gallery[$i]['url']); ?>" 
                                                       data-lightbox="product-<?php echo $product->get_id(); ?>" 
                                                       data-title="<?php echo esc_attr($gallery[$i]['title']); ?>"
                                                       class="flex-1 h-8 rounded overflow-hidden">
                                                        <?php echo headshop_responsive_image($gallery[$i], 'w-full h-full object-cover', false); ?>
                                                    </a>
                                                <?php endif; ?>
                                            <?php endfor; ?>
                                        </div>
                                        <div class="absolute top-2 right-2 bg-yellow-500 text-white text-xs px-2 py-1 rounded">
                                            BEST
                                        </div>
                                        <div class="absolute top-2 left-2 bg-black bg-opacity-50 text-white text-xs px-2 py-1 rounded">
                                            <?php echo count(array_filter($gallery, function($img) { return $img['id'] > 0; })); ?>/5
                                        </div>
                                    </div>
                                    <div class="p-3">
                                        <h3 class="font-semibold text-sm line-clamp-2"><?php echo esc_html($product->get_name()); ?></h3>
                                        <div class="mt-2 font-bold text-green-700"><?php echo wp_kses_post($product->get_price_html()); ?></div>
                                    </div>
                                </div>
                            </div>
                        <?php } } ?>
                </div>
                <div class="swiper-pagination"></div>
                <div class="swiper-button-prev"></div>
                <div class="swiper-button-next"></div>
            </div>
        </div>
    </section>
</main>

<?php get_footer(); ?>


