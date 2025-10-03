<?php
/**
 * The template for displaying product content within loops
 */

defined( 'ABSPATH' ) || exit;

global $product;

// Ensure visibility.
if ( empty( $product ) || ! $product->is_visible() ) {
    return;
}

$gallery = headshop_get_product_gallery($product->get_id(), 5);
$link = get_permalink($product->get_id());
?>

<li <?php wc_product_class( 'bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow overflow-hidden', $product ); ?>>
    <div class="aspect-square bg-gray-100 relative group">
        <a href="<?php echo esc_url($link); ?>" class="block h-full">
            <?php echo headshop_responsive_image($gallery[0], 'w-full h-full object-cover group-hover:scale-105 transition-transform duration-300', true, $product->get_id()); ?>
        </a>
        
        <!-- Quick view thumbnails on hover -->
        <?php if (count(array_filter($gallery, function($img) { return $img['id'] > 0; })) > 1): ?>
            <div class="absolute bottom-2 left-2 right-2 flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                <?php for ($i = 1; $i < 4 && $i < count($gallery); $i++) : ?>
                    <?php if (isset($gallery[$i]) && $gallery[$i]['id'] > 0) : ?>
                        <div class="flex-1 h-6 rounded overflow-hidden">
                            <?php echo headshop_responsive_image($gallery[$i], 'w-full h-full object-cover', false, $product->get_id()); ?>
                        </div>
                    <?php endif; ?>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
        
        <!-- Sale badge -->
        <?php if ($product->is_on_sale()): ?>
            <div class="absolute top-2 right-2 bg-red-500 text-white text-xs px-2 py-1 rounded">
                OFERTA
            </div>
        <?php endif; ?>
        
        <!-- Image count indicator -->
        <div class="absolute top-2 left-2 bg-black bg-opacity-50 text-white text-xs px-2 py-1 rounded">
            <?php echo count(array_filter($gallery, function($img) { return $img['id'] > 0; })); ?> fotos
        </div>
    </div>
    
    <div class="p-4">
        <h2 class="font-semibold text-gray-900 mb-2 line-clamp-2">
            <a href="<?php echo esc_url($link); ?>" class="hover:text-green-700 transition-colors">
                <?php echo esc_html($product->get_name()); ?>
            </a>
        </h2>
        
        <!-- Price -->
        <div class="font-bold text-green-700 mb-3">
            <?php echo wp_kses_post($product->get_price_html()); ?>
        </div>
        
        <!-- Add to cart button -->
        <div class="mt-auto">
            <?php
            /**
             * Hook: woocommerce_after_shop_loop_item.
             *
             * @hooked woocommerce_template_loop_add_to_cart - 10
             */
            do_action( 'woocommerce_after_shop_loop_item' );
            ?>
        </div>
    </div>
</li>
