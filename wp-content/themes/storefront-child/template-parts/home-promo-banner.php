<?php if ( ! defined( 'ABSPATH' ) ) exit;

$pb_title    = get_theme_mod( 'promo_banner_title', '' );
$pb_subtitle = get_theme_mod( 'promo_banner_subtitle', '' );
$pb_btn_text = get_theme_mod( 'promo_banner_btn_text', '' );
$pb_btn_link = get_theme_mod( 'promo_banner_btn_link', '' );
$pb_has_bg   = (bool) get_theme_mod( 'promo_banner_image', '' );

if ( ! $pb_title && ! $pb_has_bg ) return;
?>
<section class="home-promo-banner">
	<div class="col-full">
		<?php if ( $pb_title ) : ?>
			<h2 class="promo-banner-title"><?php echo esc_html( $pb_title ); ?></h2>
		<?php endif; ?>
		<?php if ( $pb_subtitle ) : ?>
			<p class="promo-banner-subtitle"><?php echo esc_html( $pb_subtitle ); ?></p>
		<?php endif; ?>
		<?php if ( $pb_btn_link && $pb_btn_text ) : ?>
			<a href="<?php echo esc_url( $pb_btn_link ); ?>" class="btn-promo"><?php echo esc_html( $pb_btn_text ); ?></a>
		<?php endif; ?>
	</div>
</section>
