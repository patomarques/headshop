<?php if ( ! defined( 'ABSPATH' ) ) exit;

$top_cats = get_terms( [
	'taxonomy'   => 'product_cat',
	'hide_empty' => true,
	'orderby'    => 'count',
	'order'      => 'DESC',
	'number'     => 8,
	'exclude'    => get_option( 'default_product_cat' ),
] );

if ( empty( $top_cats ) || is_wp_error( $top_cats ) ) return;

$shop_url = wc_get_page_permalink( 'shop' );
?>
<section class="home-categories">
	<div class="col-full">
		<div class="section-header">
			<h2 class="section-title-display">EXPLORE POR CATEGORIA</h2>
			<a href="<?php echo esc_url( $shop_url ); ?>" class="section-link-all">Ver todas →</a>
		</div>
		<div class="category-cards-grid">
			<?php foreach ( $top_cats as $cat ) :
				$thumb_id  = get_term_meta( $cat->term_id, 'thumbnail_id', true );
				$thumb_url = $thumb_id ? wp_get_attachment_image_url( $thumb_id, 'large' ) : '';
				$cat_url   = get_term_link( $cat );
			?>
			<a href="<?php echo esc_url( $cat_url ); ?>" class="category-card-v2">
				<div class="headshop-categories__image">
					<?php if ( $thumb_url ) : ?>
						<img src="<?php echo esc_url( $thumb_url ); ?>" alt="<?php echo esc_attr( $cat->name ); ?>" loading="lazy">
					<?php else : ?>
						<span class="category-card-icon"><?php echo eletronicos_category_icon( $cat->slug ); ?></span>
					<?php endif; ?>
				</div>
				<div class="category-card-inner">
					<span class="category-card-name"><?php echo esc_html( $cat->name ); ?></span>
					<span class="category-card-count"><?php echo (int) $cat->count; ?> produtos</span>
				</div>
			</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>
