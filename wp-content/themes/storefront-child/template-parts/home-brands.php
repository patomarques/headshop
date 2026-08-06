<?php if ( ! defined( 'ABSPATH' ) ) exit;

$brands = get_posts( [
	'post_type'      => 'brand',
	'posts_per_page' => 20,
	'orderby'        => 'menu_order',
	'order'          => 'ASC',
] );

$fallback = [ 'Arduino', 'Raspberry Pi', 'Espressif', 'Minipa', 'Dremel' ];
$items    = ! empty( $brands ) ? $brands : null;
?>
<section class="home-brands">
	<div class="brands-marquee-wrapper">
		<div class="brands-marquee-track">
			<?php
			$render_items = function( $source ) use ( $items, $fallback ) {
				if ( $items ) {
					foreach ( $items as $brand ) {
						$logo = get_the_post_thumbnail_url( $brand->ID, 'medium' );
						echo '<div class="brand-item">';
						if ( $logo ) {
							echo '<img src="' . esc_url( $logo ) . '" alt="' . esc_attr( $brand->post_title ) . '">';
						} else {
							echo '<span class="brand-name-text">' . esc_html( $brand->post_title ) . '</span>';
						}
						echo '</div>';
					}
				} else {
					foreach ( $fallback as $name ) {
						echo '<div class="brand-item"><span class="brand-name-text">' . esc_html( $name ) . '</span></div>';
					}
				}
			};
			$render_items( 'a' );
			$render_items( 'b' );
			?>
		</div>
	</div>
</section>
