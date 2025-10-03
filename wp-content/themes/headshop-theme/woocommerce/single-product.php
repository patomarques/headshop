<?php
/**
 * The Template for displaying product archives, including the main shop page which is a post type archive
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' ); ?>

<!-- Breadcrumb -->
<nav class="mb-6">
    <?php
    if ( function_exists('woocommerce_breadcrumb') ) {
        woocommerce_breadcrumb([
            'delimiter'   => ' <span class="text-gray-400">/</span> ',
            'wrap_before' => '<div class="text-sm text-gray-600">',
            'wrap_after'  => '</div>',
            'before'      => '<span>',
            'after'       => '</span>',
            'home'        => _x( 'Início', 'breadcrumb', 'woocommerce' ),
        ]);
    }
    ?>
</nav>

<?php while ( have_posts() ) : ?>
    <?php the_post(); ?>
    
    <?php
    global $product;
    $gallery_images = $product->get_gallery_image_ids();
    $main_image_id = $product->get_image_id();
    $all_images = array_merge([$main_image_id], $gallery_images);
    $all_images = array_filter($all_images); // Remove empty values
    ?>
    
    <div class="grid lg:grid-cols-2 gap-12 mb-12">
        <!-- Product Images -->
        <div class="space-y-4">
            <!-- Main Image -->
            <div class="aspect-square bg-gray-100 rounded-lg overflow-hidden">
                <?php if (!empty($all_images)): ?>
                    <img id="main-product-image" 
                         src="<?php echo wp_get_attachment_image_url($all_images[0], 'large'); ?>" 
                         alt="<?php echo esc_attr($product->get_name()); ?>"
                         class="w-full h-full object-cover cursor-pointer"
                         data-lightbox="product-gallery"
                         data-title="<?php echo esc_attr($product->get_name()); ?>">
                <?php else: ?>
                    <img id="main-product-image" 
                         src="<?php echo headshop_get_category_placeholder($product->get_id(), 600, 600); ?>" 
                         alt="Imagem do produto"
                         class="w-full h-full object-cover">
                <?php endif; ?>
            </div>
            
            <!-- Thumbnail Gallery -->
            <?php if (count($all_images) > 1): ?>
                <div class="grid grid-cols-4 gap-2">
                    <?php foreach ($all_images as $index => $image_id): ?>
                        <button class="aspect-square bg-gray-100 rounded overflow-hidden border-2 border-transparent hover:border-green-500 transition-colors thumbnail-btn <?php echo $index === 0 ? 'border-green-500' : ''; ?>"
                                data-image="<?php echo wp_get_attachment_image_url($image_id, 'large'); ?>"
                                data-lightbox="product-gallery"
                                data-title="<?php echo esc_attr($product->get_name() . ' - Imagem ' . ($index + 1)); ?>">
                            <img src="<?php echo wp_get_attachment_image_url($image_id, 'medium'); ?>" 
                                 alt="<?php echo esc_attr($product->get_name()); ?>"
                                 class="w-full h-full object-cover">
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Product Info -->
        <div class="space-y-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-4"><?php the_title(); ?></h1>
                
                <!-- Price -->
                <div class="text-2xl font-bold text-green-700 mb-6">
                    <?php echo $product->get_price_html(); ?>
                </div>
                
                <!-- Short Description -->
                <?php if ($product->get_short_description()): ?>
                    <div class="text-gray-600 mb-6">
                        <?php echo $product->get_short_description(); ?>
                    </div>
                <?php endif; ?>
                
                <!-- Add to Cart Form -->
                <form class="cart" action="<?php echo esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ) ); ?>" method="post" enctype='multipart/form-data'>
                    <?php do_action( 'woocommerce_before_add_to_cart_button' ); ?>
                    
                    <?php if ( ! $product->is_sold_individually() ) : ?>
                        <div class="flex items-center space-x-4 mb-6">
                            <label for="quantity" class="text-sm font-medium text-gray-700">Quantidade:</label>
                            <input type="number" 
                                   id="quantity" 
                                   name="quantity" 
                                   value="<?php echo esc_attr( isset( $_POST['quantity'] ) ? wc_stock_amount( wp_unslash( $_POST['quantity'] ) ) : 1 ); ?>" 
                                   min="1" 
                                   max="<?php echo esc_attr( $product->get_max_purchase_quantity() ); ?>"
                                   class="w-20 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        </div>
                    <?php endif; ?>
                    
                    <button type="submit" 
                            name="add-to-cart" 
                            value="<?php echo esc_attr( $product->get_id() ); ?>" 
                            class="w-full bg-green-600 text-white py-3 px-6 rounded-lg hover:bg-green-700 transition-colors font-medium text-lg">
                        <?php echo esc_html( $product->single_add_to_cart_text() ); ?>
                    </button>
                    
                    <?php do_action( 'woocommerce_after_add_to_cart_button' ); ?>
                </form>
                
                <!-- Product Meta -->
                <div class="border-t pt-6 space-y-2 text-sm text-gray-600">
                    <?php if ( $product->get_sku() ) : ?>
                        <div><strong>SKU:</strong> <?php echo $product->get_sku(); ?></div>
                    <?php endif; ?>
                    
                    <?php
                    $categories = get_the_terms( $product->get_id(), 'product_cat' );
                    if ( $categories && ! is_wp_error( $categories ) ) :
                        $cat_names = array();
                        foreach ( $categories as $category ) {
                            $cat_names[] = '<a href="' . get_term_link( $category ) . '" class="text-green-700 hover:text-green-800">' . $category->name . '</a>';
                        }
                        ?>
                        <div><strong>Categorias:</strong> <?php echo implode( ', ', $cat_names ); ?></div>
                    <?php endif; ?>
                    
                    <?php
                    $tags = get_the_terms( $product->get_id(), 'product_tag' );
                    if ( $tags && ! is_wp_error( $tags ) ) :
                        $tag_names = array();
                        foreach ( $tags as $tag ) {
                            $tag_names[] = '<a href="' . get_term_link( $tag ) . '" class="text-green-700 hover:text-green-800">' . $tag->name . '</a>';
                        }
                        ?>
                        <div><strong>Tags:</strong> <?php echo implode( ', ', $tag_names ); ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Product Tabs -->
    <div class="border-t pt-12">
        <div class="mb-8">
            <nav class="flex space-x-8 border-b">
                <button class="tab-button active py-2 px-1 border-b-2 border-green-500 text-green-600 font-medium" data-tab="description">
                    Descrição
                </button>
                <?php if ( $product->has_attributes() || apply_filters( 'wc_product_enable_dimensions_display', $product->has_weight() || $product->has_dimensions() ) ) : ?>
                    <button class="tab-button py-2 px-1 border-b-2 border-transparent text-gray-500 hover:text-gray-700" data-tab="additional">
                        Informações Adicionais
                    </button>
                <?php endif; ?>
                <?php if ( comments_open() ) : ?>
                    <button class="tab-button py-2 px-1 border-b-2 border-transparent text-gray-500 hover:text-gray-700" data-tab="reviews">
                        Avaliações (<?php echo $product->get_review_count(); ?>)
                    </button>
                <?php endif; ?>
            </nav>
        </div>
        
        <!-- Tab Content -->
        <div class="tab-content">
            <!-- Description -->
            <div id="description" class="tab-pane active">
                <div class="prose max-w-none">
                    <?php the_content(); ?>
                </div>
            </div>
            
            <!-- Additional Information -->
            <?php if ( $product->has_attributes() || apply_filters( 'wc_product_enable_dimensions_display', $product->has_weight() || $product->has_dimensions() ) ) : ?>
                <div id="additional" class="tab-pane hidden">
                    <table class="w-full border-collapse">
                        <?php if ( $product->has_weight() ) : ?>
                            <tr class="border-b">
                                <td class="py-3 font-medium text-gray-900">Peso</td>
                                <td class="py-3 text-gray-600"><?php echo $product->get_weight() . ' ' . get_option( 'woocommerce_weight_unit' ); ?></td>
                            </tr>
                        <?php endif; ?>
                        
                        <?php if ( $product->has_dimensions() ) : ?>
                            <tr class="border-b">
                                <td class="py-3 font-medium text-gray-900">Dimensões</td>
                                <td class="py-3 text-gray-600"><?php echo $product->get_dimensions(); ?></td>
                            </tr>
                        <?php endif; ?>
                        
                        <?php
                        $attributes = $product->get_attributes();
                        foreach ( $attributes as $attribute ) :
                            if ( $attribute->get_variation() ) continue;
                            ?>
                            <tr class="border-b">
                                <td class="py-3 font-medium text-gray-900"><?php echo wc_attribute_label( $attribute->get_name() ); ?></td>
                                <td class="py-3 text-gray-600">
                                    <?php
                                    if ( $attribute->is_taxonomy() ) {
                                        $values = wc_get_product_terms( $product->get_id(), $attribute->get_name(), array( 'fields' => 'names' ) );
                                        echo implode( ', ', $values );
                                    } else {
                                        echo $attribute->get_options()[0];
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            <?php endif; ?>
            
            <!-- Reviews -->
            <?php if ( comments_open() ) : ?>
                <div id="reviews" class="tab-pane hidden">
                    <?php comments_template(); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Related Products -->
    <?php
    $related_products = wc_get_products([
        'limit' => 4,
        'exclude' => [$product->get_id()],
        'category' => wp_get_post_terms($product->get_id(), 'product_cat', ['fields' => 'slugs'])
    ]);
    
    if (!empty($related_products)): ?>
        <div class="border-t pt-12 mt-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-8">Produtos Relacionados</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                <?php foreach ($related_products as $related_product): ?>
                        <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow overflow-hidden">
                            <a href="<?php echo get_permalink($related_product->get_id()); ?>" class="block">
                                <div class="aspect-square bg-gray-100">
                                    <?php if ($related_product->get_image_id()): ?>
                                        <img src="<?php echo wp_get_attachment_image_url($related_product->get_image_id(), 'medium'); ?>" 
                                             alt="<?php echo esc_attr($related_product->get_name()); ?>"
                                             class="w-full h-full object-cover">
                                    <?php else: ?>
                                        <img src="<?php echo headshop_get_category_placeholder($related_product->get_id(), 300, 300); ?>" 
                                             alt="Imagem do produto"
                                             class="w-full h-full object-cover">
                                    <?php endif; ?>
                                </div>
                            </a>
                        <div class="p-4">
                            <h3 class="font-semibold text-gray-900 mb-2 line-clamp-2">
                                <a href="<?php echo get_permalink($related_product->get_id()); ?>" class="hover:text-green-700 transition-colors">
                                    <?php echo $related_product->get_name(); ?>
                                </a>
                            </h3>
                            <div class="font-bold text-green-700">
                                <?php echo $related_product->get_price_html(); ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

<?php endwhile; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Thumbnail gallery functionality
    const thumbnailBtns = document.querySelectorAll('.thumbnail-btn');
    const mainImage = document.getElementById('main-product-image');
    
    thumbnailBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            // Remove active class from all thumbnails
            thumbnailBtns.forEach(b => b.classList.remove('border-green-500'));
            // Add active class to clicked thumbnail
            this.classList.add('border-green-500');
            
            // Update main image
            const newImageSrc = this.dataset.image;
            if (mainImage && newImageSrc) {
                mainImage.src = newImageSrc;
            }
        });
    });
    
    // Product tabs functionality
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabPanes = document.querySelectorAll('.tab-pane');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetTab = this.dataset.tab;
            
            // Remove active classes
            tabButtons.forEach(btn => {
                btn.classList.remove('active', 'border-green-500', 'text-green-600');
                btn.classList.add('border-transparent', 'text-gray-500');
            });
            
            tabPanes.forEach(pane => {
                pane.classList.add('hidden');
                pane.classList.remove('active');
            });
            
            // Add active classes
            this.classList.add('active', 'border-green-500', 'text-green-600');
            this.classList.remove('border-transparent', 'text-gray-500');
            
            const targetPane = document.getElementById(targetTab);
            if (targetPane) {
                targetPane.classList.remove('hidden');
                targetPane.classList.add('active');
            }
        });
    });
});
</script>

<?php get_footer( 'shop' ); ?>
