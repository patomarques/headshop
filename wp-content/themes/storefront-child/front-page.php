<?php
/**
 * The template for displaying the homepage.
 *
 * This page template will display any functions hooked into the `homepage` action.
 * By default this includes a variety of product displays and the page content itself. To change the order or toggle these components
 * use the Homepage Control plugin.
 * https://wordpress.org/plugins/homepage-control/
 *
 * Template name: Homepage
 *
 * @package storefront-child
 */

get_header(); ?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">

			<?php
			/**
			 * Functions hooked in to homepage action
			 * Removido apenas: storefront_homepage_content (texto "Início")
			 * Mantido: storefront_product_categories (categorias de produtos)
			 *
			 * @hooked storefront_product_categories    - 20
			 * @hooked storefront_recent_products       - 30
			 * @hooked storefront_featured_products     - 40
			 * @hooked storefront_popular_products      - 50
			 * @hooked storefront_on_sale_products      - 60
			 * @hooked storefront_best_selling_products - 70
			 */
			
			// Remover apenas o texto "Início" (homepage content)
			remove_action( 'homepage', 'storefront_homepage_content', 10 );
			
			do_action( 'homepage' );
			?>

		</main><!-- #main -->
	</div><!-- #primary -->
<?php
get_footer();
