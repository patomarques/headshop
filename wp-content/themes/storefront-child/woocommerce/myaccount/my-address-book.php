<?php
namespace CrossPeakSoftware\WooCommerce\AddressBook\Templates\MyAddressBook;

use function CrossPeakSoftware\WooCommerce\AddressBook\Settings\setting;
use function CrossPeakSoftware\WooCommerce\AddressBook\get_address_book;
use function CrossPeakSoftware\WooCommerce\AddressBook\address_header;
use function CrossPeakSoftware\WooCommerce\AddressBook\get_address_book_endpoint_url;
use function CrossPeakSoftware\WooCommerce\AddressBook\get_current_customer;

defined( 'ABSPATH' ) || exit;

$customer = get_current_customer( 'my-address-book-template' );
if ( ! $customer ) return;

$sections = [];
if ( setting( 'billing_enable' ) === true ) {
	$sections['billing'] = 'Cobrança';
}
if ( setting( 'shipping_enable' ) === true ) {
	$sections['shipping'] = 'Entrega';
}

foreach ( $sections as $type => $label ) :
	$address_book = get_address_book( $customer, $type );
	$save_limit   = $address_book->limit();
	$count        = $address_book->count();
	$under_limit  = $address_book->is_under_limit();

	if ( 1 === $save_limit && $count <= 1 ) continue;
	?>

	<section class="address-section">
		<div class="address-section__head">
			<h3 class="address-section__title">Endereços de <?php echo esc_html( strtolower( $label ) ); ?></h3>
			<?php if ( $under_limit ) : ?>
				<a href="<?php echo esc_url( get_address_book_endpoint_url( 'new', $type ) ); ?>" class="address-section__add wc-address-book-add-<?php echo esc_attr( $type ); ?>-button">
					<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
						<path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
					</svg>
					Adicionar endereço
				</a>
			<?php endif; ?>
		</div>

		<div class="address-grid">
			<?php foreach ( $address_book->addresses() as $key => $fields ) :
				$formatted   = apply_filters( 'woocommerce_my_account_my_address_formatted_address', $fields, $customer->get_id(), $key );
				$html        = WC()->countries->get_formatted_address( $formatted );
				$is_default  = $address_book->is_default( $key );
				$edit_url    = get_address_book_endpoint_url( $key, $type );
			?>
				<div class="address-card <?php echo $is_default ? 'address-card--default' : ''; ?>">
					<div class="address-card__header">
						<span class="address-card__type"><?php echo esc_html( address_header( $formatted ) ); ?></span>
						<?php if ( $is_default ) : ?>
							<span class="address-card__badge">Padrão</span>
						<?php endif; ?>
					</div>
					<address class="address-card__address">
						<?php echo wp_kses( $html, [ 'br' => [] ] ); ?>
					</address>
					<div class="address-card__footer">
						<a href="<?php echo esc_url( $edit_url ); ?>" class="address-card__action">Alterar</a>
						<button
							type="button"
							class="wc-address-book-delete address-card__action address-card__action--danger"
							data-wc-address-type="<?php echo esc_attr( $type ); ?>"
							data-wc-address-name="<?php echo esc_attr( $key ); ?>"
						>Excluir</button>
						<?php if ( ! $is_default ) : ?>
							<button
								type="button"
								class="wc-address-book-make-default address-card__action address-card__action--muted"
								data-wc-address-type="<?php echo esc_attr( $type ); ?>"
								data-wc-address-name="<?php echo esc_attr( $key ); ?>"
							>Tornar padrão</button>
						<?php endif; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</section>

<?php endforeach;
