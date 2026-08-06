<?php if ( ! defined( 'ABSPATH' ) ) exit;

$new_query = new WP_Query( [
	'post_type'      => 'product',
	'post_status'    => 'publish',
	'posts_per_page' => 8,
	'orderby'        => 'date',
	'order'          => 'DESC',
] );

if ( ! $new_query->have_posts() ) return;

$shop_url = wc_get_page_permalink( 'shop' );
?>
<section class="home-new-products">
	<div class="col-full">
		<div class="section-header">
			<h2 class="section-title-display">PRODUTOS NOVOS</h2>
			<a href="<?php echo esc_url( $shop_url ); ?>" class="section-link-all">Ver todos →</a>
		</div>
		<div class="product-cards-grid">
			<?php while ( $new_query->have_posts() ) : $new_query->the_post();
				$product = wc_get_product( get_the_ID() );
				$thumb   = get_the_post_thumbnail_url( get_the_ID(), 'medium' )
				           ?: wc_placeholder_img_src( 'medium' );
			?>
			<a href="<?php echo esc_url( get_permalink() ); ?>" class="product-card">
				<div class="product-card-image">
					<span class="badge-new">NOVO</span>
					<img src="<?php echo esc_url( $thumb ); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy">
				</div>
				<div class="product-card-body">
					<div class="product-card-rating"><?php echo wc_get_rating_html( $product->get_average_rating() ); ?></div>
					<h3 class="product-card-name"><?php the_title(); ?></h3>
					<div class="product-card-price"><?php echo $product->get_price_html(); ?></div>
				</div>
			</a>
			<?php endwhile; wp_reset_postdata(); ?>
		</div>
	</div>
</section>
