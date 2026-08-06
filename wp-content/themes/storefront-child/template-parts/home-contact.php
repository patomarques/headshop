<?php if ( ! defined( 'ABSPATH' ) ) exit;

$phone    = get_theme_mod( 'contact_phone', '' );
$whatsapp = get_theme_mod( 'contact_whatsapp', '' );
$wa_msg   = get_theme_mod( 'contact_whatsapp_message', 'Olá! Gostaria de saber mais sobre os produtos.' );
$address  = get_theme_mod( 'contact_address', '' );
$maps_url = get_theme_mod( 'contact_maps_url', '' );
$fallback = 'Configure em Aparência &gt; Personalizar';

$icons_uri = get_stylesheet_directory_uri() . '/assets/img/icons/';
?>
<section class="home-contact">
	<div class="col-full">
		<div class="row g-0">

			<div class="col-12 col-md-4 contact-item contact-item--phone">
				<div class="contact-icon"><img src="<?php echo esc_url( $icons_uri . 'call.png' ); ?>" alt="Telefone"></div>
				<div class="contact-label">Compre por telefone</div>
				<div class="contact-value">
					<?php if ( $phone ) : ?>
						<a href="tel:<?php echo esc_attr( preg_replace( '/\D/', '', $phone ) ); ?>" class="contact-btn">Ligar agora</a>
					<?php else : ?>
						<?php echo $fallback; ?>
					<?php endif; ?>
				</div>
			</div>

			<div class="col-12 col-md-4 contact-item contact-item--whatsapp">
				<div class="contact-icon"><img src="<?php echo esc_url( $icons_uri . 'whatsapp.png' ); ?>" alt="WhatsApp"></div>
				<div class="contact-label">Fale por WhatsApp</div>
				<div class="contact-value">
					<?php if ( $whatsapp ) :
						$wa_number = preg_replace( '/\D/', '', $whatsapp );
						$wa_url    = 'https://wa.me/' . $wa_number . '?text=' . rawurlencode( $wa_msg );
					?>
						<a href="<?php echo esc_url( $wa_url ); ?>" class="contact-btn contact-btn--whatsapp" target="_blank" rel="noopener noreferrer">Chamar no WhatsApp</a>
					<?php else : ?>
						<?php echo $fallback; ?>
					<?php endif; ?>
				</div>
			</div>

			<div class="col-12 col-md-4 contact-item contact-item--store">
				<div class="contact-icon"><img src="<?php echo esc_url( $icons_uri . 'location.png' ); ?>" alt="Localização"></div>
				<div class="contact-label">Nossa loja física</div>
				<div class="contact-value">
					<?php if ( $address ) :
						$resolved_maps_url = $maps_url ?: 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode( $address );
					?>
						<a href="<?php echo esc_url( $resolved_maps_url ); ?>" class="contact-btn" target="_blank" rel="noopener noreferrer">Ver no Maps</a>
					<?php else : ?>
						<?php echo $fallback; ?>
					<?php endif; ?>
				</div>
			</div>

		</div>
	</div>
</section>
