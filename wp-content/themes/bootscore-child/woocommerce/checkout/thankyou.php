<?php
/**
 * Thankyou page — Headshop "ticket" layout.
 *
 * Overrides WooCommerce's default checkout/thankyou.php. The "no order"
 * and "order failed" branches below are copied unchanged from WooCommerce
 * core (checkout/thankyou.php, template version 8.1.0) — only the happy
 * path (valid, non-failed order) is redesigned. woocommerce_before_thankyou,
 * woocommerce_thankyou_{payment_method} and woocommerce_thankyou still fire
 * in the same order core fires them: woo-asaas hooks the payment-method
 * action to render the Pix QR code / boleto link there — that's required
 * to complete payment, not decorative, so it must never be skipped.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 *
 * @var WC_Order $order
 */

defined('ABSPATH') || exit;
?>

<div class="woocommerce-order headshop-order">

<?php if ($order) :

    do_action('woocommerce_before_thankyou', $order->get_id());

    if ($order->has_status('failed')) :
        ?>
        <p class="woocommerce-notice woocommerce-notice--error woocommerce-thankyou-order-failed"><?php esc_html_e('Unfortunately your order cannot be processed as the originating bank/merchant has declined your transaction. Please attempt your purchase again.', 'woocommerce'); ?></p>

        <p class="woocommerce-notice woocommerce-notice--error woocommerce-thankyou-order-failed-actions">
            <a href="<?php echo esc_url($order->get_checkout_payment_url()); ?>" class="button pay"><?php esc_html_e('Pay', 'woocommerce'); ?></a>
            <?php if (is_user_logged_in()) : ?>
                <a href="<?php echo esc_url(wc_get_page_permalink('myaccount')); ?>" class="button pay"><?php esc_html_e('My account', 'woocommerce'); ?></a>
            <?php endif; ?>
        </p>
        <?php
    else :
        $is_pickup = false;
        foreach ($order->get_shipping_methods() as $shipping_method) {
            if ('local_pickup' === $shipping_method->get_method_id()) {
                $is_pickup = true;
                break;
            }
        }

        $status_badges = [
            'processing' => ['label' => 'Pagamento confirmado', 'class' => 'is-confirmed'],
            'completed'  => ['label' => 'Pagamento confirmado', 'class' => 'is-confirmed'],
            'pending'    => ['label' => 'Aguardando pagamento', 'class' => 'is-pending'],
            'on-hold'    => ['label' => 'Aguardando pagamento', 'class' => 'is-pending'],
            'cancelled'  => ['label' => 'Pedido cancelado', 'class' => 'is-declined'],
            'refunded'   => ['label' => 'Pedido reembolsado', 'class' => 'is-pending'],
        ];
        $status = $order->get_status();
        $badge  = $status_badges[$status] ?? ['label' => ucfirst($status), 'class' => 'is-pending'];
        ?>

        <section class="headshop-order-hero">
            <div class="headshop-order-hero__check" aria-hidden="true">
                <svg viewBox="0 0 24 24"><polyline points="4 12.5 10 18.5 20 6.5" /></svg>
            </div>
            <h1>Pedido confirmado</h1>
            <?php
            // Só mostra o e-mail pra quem está logado como dono do pedido —
            // mesma checagem que o core faz na order-overview list (thankyou.php)
            // antes de expor o e-mail a quem só tem a URL+key do pedido.
            $show_billing_email = is_user_logged_in() && $order->get_user_id() === get_current_user_id() && $order->get_billing_email();
            ?>
            <?php if ($show_billing_email) : ?>
                <p>Obrigado! Enviamos o comprovante para <strong><?= esc_html($order->get_billing_email()); ?></strong>.</p>
            <?php else : ?>
                <p>Obrigado! Seu pedido foi recebido.</p>
            <?php endif; ?>
        </section>

        <section class="headshop-order-ticket" aria-label="Resumo do pedido">
            <div class="headshop-order-ticket__top">
                <div class="headshop-order-ticket__num">
                    <span class="headshop-order-eyebrow">Pedido nº</span>
                    <strong><?= esc_html($order->get_order_number()); ?></strong>
                </div>
                <span class="headshop-order-badge <?= esc_attr($badge['class']); ?>"><?= esc_html($badge['label']); ?></span>
            </div>

            <div class="headshop-order-ticket__meta">
                <div>
                    <span class="headshop-order-eyebrow">Data</span>
                    <div class="headshop-order-ticket__val"><?= esc_html(wc_format_datetime($order->get_date_created())); ?></div>
                </div>
                <?php if ($order->get_payment_method_title()) : ?>
                    <div>
                        <span class="headshop-order-eyebrow">Pagamento</span>
                        <div class="headshop-order-ticket__val"><?= wp_kses_post($order->get_payment_method_title()); ?></div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="headshop-order-ticket__items">
                <?php foreach ($order->get_items() as $item_id => $item) :
                    $product    = $item->get_product();
                    $is_visible = $product && $product->is_visible();
                    ?>
                    <div class="headshop-order-ticket__item">
                        <span>
                            <?= wp_kses_post(apply_filters('woocommerce_order_item_name', esc_html($item->get_name()), $item, $is_visible)); ?>
                            <span class="headshop-order-ticket__qty">&times; <?= esc_html($item->get_quantity()); ?></span>
                            <?php wc_display_item_meta($item); ?>
                        </span>
                        <span class="headshop-order-ticket__price"><?= wp_kses_post($order->get_formatted_line_subtotal($item)); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if ($order->get_shipping_method()) : ?>
                <div class="headshop-order-ticket__shipping">
                    <span class="headshop-order-eyebrow">Entrega</span>
                    <div class="headshop-order-ticket__val">
                        <?php if ($is_pickup) : ?>
                            Retirada no local
                        <?php elseif ($order->get_shipping_total() > 0) : ?>
                            <?= wp_kses_post(wc_price($order->get_shipping_total())); ?>
                        <?php else : ?>
                            Entrega grátis
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="headshop-order-ticket__totals">
                <div class="headshop-order-ticket__row headshop-order-ticket__row--grand">
                    <span class="headshop-order-ticket__label">Total</span>
                    <span class="headshop-order-ticket__amount"><?= wp_kses_post($order->get_formatted_order_total()); ?></span>
                </div>
            </div>
        </section>

        <?php if ($is_pickup) : ?>
            <section class="headshop-order-pickup" aria-label="Informações de retirada">
                <span class="headshop-order-eyebrow">Retirada no local</span>
                <h2>Te esperamos na loja</h2>
                <address>
                    Rua Tupy, 147 &mdash; Salgado<br>
                    Caruaru &mdash; PE &middot; 55016-080
                </address>
                <p class="headshop-order-pickup__hours">
                    <b>Segunda a sexta</b> &mdash; 9h30 às 19h<br>
                    <b>Sábado</b> &mdash; 9h30 às 16h
                </p>
                <a class="headshop-order-btn-whats" href="<?= esc_url('https://wa.me/5581996366201?text=' . rawurlencode('Olá! Estou a caminho para retirar meu pedido #' . $order->get_order_number() . '.')); ?>" target="_blank" rel="noopener noreferrer">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2zm0 18.2c-1.5 0-3-.4-4.3-1.2l-.3-.2-3 .8.8-2.9-.2-.3A8.2 8.2 0 1 1 12 20.2zm4.6-6.1c-.3-.1-1.5-.7-1.7-.8-.2-.1-.4-.1-.6.1-.2.3-.7.8-.8 1-.1.2-.3.2-.5.1a6.7 6.7 0 0 1-3.3-2.9c-.3-.4 0-.5.1-.7l.4-.5c.1-.2.1-.3 0-.5l-.8-1.9c-.2-.5-.4-.4-.6-.4h-.5c-.2 0-.5.1-.7.3-.9.9-1.1 2.2-.2 3.7 1.2 2 2.9 3.5 5 4.4 1.9.8 2.7.6 3.4.3.6-.2 1.2-.8 1.4-1.4.2-.6.2-1.1.1-1.2-.1-.1-.3-.2-.7-.4z" /></svg>
                    Avisar que estou a caminho
                </a>
            </section>
        <?php else : ?>
            <section class="headshop-order-delivery" aria-label="Endereço de entrega">
                <span class="headshop-order-eyebrow">Entrega</span>
                <h2>Enviaremos para</h2>
                <address><?= wp_kses_post($order->get_formatted_shipping_address(esc_html__('N/A', 'woocommerce'))); ?></address>
            </section>
        <?php endif; ?>

        <?php
    endif;

    // Only the payment-method-specific hook fires here — WooCommerce core's
    // own generic 'woocommerce_thankyou' action is hooked to
    // woocommerce_order_details_table(), which renders its own "Order
    // details" table AND billing/shipping address block (order-details.php
    // + order-details-customer.php). This template already has its own
    // equivalent UI for both (the ticket above, the billing <details>
    // below) — firing the generic hook would duplicate them.
    //
    // The payment-method hook itself is skipped for the credit-card
    // gateway ('asaas-credit-card'): its own thankyou template only
    // prints a redundant "Payment details / Status: confirmed" block —
    // the same information the badge in the ticket above already shows.
    // Pix and boleto (any other payment method) still fire it: those
    // render a QR code / bank-slip link there, which is functionally
    // required to complete payment, not decorative.
    if ('asaas-credit-card' !== $order->get_payment_method()) {
        do_action('woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id());
    }

    if (!$order->has_status('failed')) :
        ?>
        <a class="headshop-order-back-link" href="<?= esc_url(home_url('/')); ?>">&larr; Ir para página inicial</a>

        <p class="headshop-order-note">Dúvidas sobre o pedido? Fale com a gente no <a href="https://wa.me/5581996366201" target="_blank" rel="noopener noreferrer">WhatsApp</a>.</p>
        <?php
    endif;

else :

    wc_get_template('checkout/order-received.php', ['order' => false]);

endif; ?>

</div>
