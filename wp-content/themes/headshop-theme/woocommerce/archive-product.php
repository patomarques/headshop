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

<!-- Page Header -->
<header class="mb-8">
    <?php if ( apply_filters( 'woocommerce_show_page_title', true ) ) : ?>
        <h1 class="text-4xl font-bold text-gray-900 mb-4"><?php woocommerce_page_title(); ?></h1>
    <?php endif; ?>
    
    <?php
    /**
     * Hook: woocommerce_archive_description.
     *
     * @hooked woocommerce_taxonomy_archive_description - 10
     * @hooked woocommerce_product_archive_description - 10
     */
    do_action( 'woocommerce_archive_description' );
    ?>
</header>

<?php if ( woocommerce_product_loop() ) : ?>
    
    <!-- Toolbar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8 space-y-4 sm:space-y-0">
        <div class="flex items-center space-x-4">
            <?php
            /**
             * Hook: woocommerce_before_shop_loop.
             *
             * @hooked woocommerce_output_all_notices - 10
             * @hooked woocommerce_result_count - 20
             * @hooked woocommerce_catalog_ordering - 30
             */
            do_action( 'woocommerce_before_shop_loop' );
            ?>
        </div>
    </div>
    
    <!-- Products Grid -->
    <?php
    woocommerce_product_loop_start();
    
    if ( wc_get_loop_prop( 'total' ) ) {
        while ( have_posts() ) {
            the_post();
            
            /**
             * Hook: woocommerce_shop_loop.
             */
            do_action( 'woocommerce_shop_loop' );
            
            wc_get_template_part( 'content', 'product' );
        }
    }
    
    woocommerce_product_loop_end();
    ?>
    
    <!-- Pagination -->
    <div class="flex justify-center">
        <?php
        /**
         * Hook: woocommerce_after_shop_loop.
         *
         * @hooked woocommerce_pagination - 10
         */
        do_action( 'woocommerce_after_shop_loop' );
        ?>
    </div>
    
<?php else : ?>
    
    <!-- No products found -->
    <div class="text-center py-16">
        <div class="mb-8">
            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2 2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
            </svg>
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Nenhum produto encontrado</h2>
            <p class="text-gray-600 mb-8">
                Não encontramos produtos que correspondam aos seus critérios de busca.
            </p>
        </div>
        
        <div class="space-x-4">
            <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" 
               class="inline-block px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-medium">
                Ver todos os produtos
            </a>
            <a href="<?php echo esc_url(home_url('/')); ?>" 
               class="inline-block px-6 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors font-medium">
                Voltar ao início
            </a>
        </div>
    </div>
    
    <?php
    /**
     * Hook: woocommerce_no_products_found.
     *
     * @hooked wc_no_products_found - 10
     */
    do_action( 'woocommerce_no_products_found' );
    ?>
    
<?php endif; ?>

<?php get_footer( 'shop' ); ?>
