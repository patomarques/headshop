<?php
/** @var WC_Order|false $order */
defined( 'ABSPATH' ) || exit;
?>

<div class="woocommerce-order thankyou-wrap">

<?php if ( ! $order ) : ?>
  <div class="thankyou-received">
    <p><?php esc_html_e( 'Thank you. Your order has been received.', 'woocommerce' ); ?></p>
  </div>
<?php elseif ( $order->has_status( 'failed' ) ) : ?>

  <div class="thankyou-card thankyou-card--failed">
    <div class="thankyou-icon thankyou-icon--failed">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
    </div>
    <h1 class="thankyou-title"><?php esc_html_e( 'Pagamento não aprovado', 'woocommerce' ); ?></h1>
    <p class="thankyou-subtitle"><?php esc_html_e( 'Seu pedido não pôde ser processado. Tente novamente ou escolha outra forma de pagamento.', 'woocommerce' ); ?></p>
    <div class="thankyou-actions">
      <a href="<?php echo esc_url( $order->get_checkout_payment_url() ); ?>" class="thankyou-btn thankyou-btn--primary">Tentar novamente</a>
      <a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="thankyou-btn thankyou-btn--secondary">Continuar comprando</a>
    </div>
  </div>

<?php else :
  do_action( 'woocommerce_before_thankyou', $order->get_id() );
  $items        = $order->get_items();
  $shop_url     = wc_get_page_permalink( 'shop' );
  $account_url  = wc_get_page_permalink( 'myaccount' );
?>

  <div class="thankyou-card">

    <div class="thankyou-hero">
      <div class="thankyou-icon">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
      </div>
      <h1 class="thankyou-title">Pedido confirmado!</h1>
      <p class="thankyou-subtitle">Obrigado pela sua compra. Você receberá um e-mail com os detalhes em breve.</p>
      <span class="thankyou-order-num">#<?php echo esc_html( $order->get_order_number() ); ?></span>
    </div>

    <div class="thankyou-meta">
      <div class="thankyou-meta-item">
        <span class="thankyou-meta-label">Data</span>
        <strong class="thankyou-meta-value"><?php echo esc_html( date_i18n( 'd/m/Y', $order->get_date_created()->getTimestamp() ) ); ?></strong>
      </div>
      <div class="thankyou-meta-item">
        <span class="thankyou-meta-label">Total</span>
        <strong class="thankyou-meta-value"><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></strong>
      </div>
      <?php if ( $order->get_billing_email() ) : ?>
      <div class="thankyou-meta-item">
        <span class="thankyou-meta-label">E-mail</span>
        <strong class="thankyou-meta-value"><?php echo esc_html( $order->get_billing_email() ); ?></strong>
      </div>
      <?php endif; ?>
      <?php if ( $order->get_payment_method_title() ) : ?>
      <div class="thankyou-meta-item">
        <span class="thankyou-meta-label">Pagamento</span>
        <strong class="thankyou-meta-value"><?php echo wp_kses_post( $order->get_payment_method_title() ); ?></strong>
      </div>
      <?php endif; ?>
    </div>

    <?php if ( $items ) : ?>
    <div class="thankyou-items">
      <h2 class="thankyou-items-title">Itens do pedido</h2>
      <?php foreach ( $items as $item ) :
        $product  = $item->get_product();
        $thumb    = $product ? get_the_post_thumbnail_url( $product->get_id(), 'thumbnail' ) : '';
        $thumb    = $thumb ?: wc_placeholder_img_src( 'thumbnail' );
      ?>
      <div class="thankyou-item">
        <img src="<?php echo esc_url( $thumb ); ?>" alt="<?php echo esc_attr( $item->get_name() ); ?>" class="thankyou-item-img">
        <div class="thankyou-item-info">
          <span class="thankyou-item-name"><?php echo esc_html( $item->get_name() ); ?></span>
          <span class="thankyou-item-qty">Qtd: <?php echo esc_html( $item->get_quantity() ); ?></span>
        </div>
        <span class="thankyou-item-total"><?php echo wp_kses_post( wc_price( $item->get_total() ) ); ?></span>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="thankyou-actions">
      <a href="<?php echo esc_url( $shop_url ); ?>" class="thankyou-btn thankyou-btn--secondary">Continuar comprando</a>
      <?php if ( is_user_logged_in() ) : ?>
        <a href="<?php echo esc_url( $account_url . 'orders/' ); ?>" class="thankyou-btn thankyou-btn--primary">Ver meus pedidos</a>
      <?php endif; ?>
    </div>

  </div>

  <?php
  remove_action( 'woocommerce_thankyou', 'woocommerce_order_details_table', 10 );

  ob_start();
  do_action( 'woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id() );
  $payment_html = trim( ob_get_clean() );

  do_action( 'woocommerce_thankyou', $order->get_id() );

  $billing_address  = $order->get_formatted_billing_address();
  $shipping_address = $order->get_formatted_shipping_address();
  $show_shipping    = ! wc_ship_to_billing_address_only() && $order->needs_shipping_address() && $shipping_address;
  ?>

  <div class="thankyou-accordion">

    <?php if ( $payment_html ) : ?>
    <details class="thankyou-panel" open>
      <summary class="thankyou-panel__summary">Detalhes do pagamento</summary>
      <div class="thankyou-panel__body">
        <?php echo $payment_html; // phpcs:ignore WordPress.Security.EscapeOutput ?>
      </div>
    </details>
    <?php endif; ?>

    <details class="thankyou-panel" open>
      <summary class="thankyou-panel__summary">Detalhes do pedido</summary>
      <div class="thankyou-panel__body">
        <?php
        $GLOBALS['eletronicos_suppress_customer_details'] = true;
        wc_get_template( 'order/order-details.php', [ 'order_id' => $order->get_id() ] );
        unset( $GLOBALS['eletronicos_suppress_customer_details'] );
        ?>
      </div>
    </details>

    <details class="thankyou-panel">
      <summary class="thankyou-panel__summary">Endereço de cobrança</summary>
      <div class="thankyou-panel__body">
        <address><?php echo wp_kses_post( $billing_address ?: esc_html__( 'N/A', 'woocommerce' ) ); ?></address>
        <?php if ( $order->get_billing_phone() ) : ?>
          <p class="thankyou-panel__phone"><?php echo esc_html( $order->get_billing_phone() ); ?></p>
        <?php endif; ?>
      </div>
    </details>

    <?php if ( $show_shipping ) : ?>
    <details class="thankyou-panel">
      <summary class="thankyou-panel__summary">Endereço de entrega</summary>
      <div class="thankyou-panel__body">
        <address><?php echo wp_kses_post( $shipping_address ); ?></address>
      </div>
    </details>
    <?php endif; ?>

  </div>

<?php endif; ?>
</div>
