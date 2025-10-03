<?php
/**
 * Checkout Form
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

do_action( 'woocommerce_before_checkout_form', $checkout );

// If checkout registration is disabled and not logged in, the user cannot checkout.
if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
    echo esc_html( apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'You must be logged in to checkout.', 'woocommerce' ) ) );
    return;
}
?>

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

<h1 class="text-3xl font-bold text-gray-900 mb-8">Finalizar Compra</h1>

<form name="checkout" method="post" class="checkout woocommerce-checkout" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data">

    <div class="grid lg:grid-cols-2 gap-12">
        <div>
            <?php if ( $checkout->get_checkout_fields() ) : ?>

                <?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>

                <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
                    <h2 class="text-xl font-semibold text-gray-900 mb-6">Detalhes de Cobrança</h2>
                    
                    <?php do_action( 'woocommerce_checkout_billing' ); ?>
                </div>

                <?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>

            <?php endif; ?>
            
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-6">Informações Adicionais</h2>
                
                <?php do_action( 'woocommerce_checkout_before_order_review_heading' ); ?>
                
                <?php if ( wc_coupons_enabled() ) { ?>
                    <div class="mb-6">
                        <label for="coupon_code" class="block text-sm font-medium text-gray-700 mb-2">Cupom de Desconto</label>
                        <div class="flex">
                            <input type="text" name="coupon_code" class="flex-1 px-3 py-2 border border-gray-300 rounded-l-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent" placeholder="Digite o código do cupom" id="coupon_code" value="" />
                            <button type="submit" class="bg-gray-600 text-white px-4 py-2 rounded-r-md hover:bg-gray-700 transition-colors" name="apply_coupon" value="<?php esc_attr_e( 'Apply coupon', 'woocommerce' ); ?>"><?php esc_html_e( 'Aplicar', 'woocommerce' ); ?></button>
                        </div>
                    </div>
                <?php } ?>
                
                <?php do_action( 'woocommerce_checkout_order_review' ); ?>
            </div>
        </div>
        
        <div>
            <div class="bg-white rounded-lg shadow-sm p-6 sticky top-8">
                <h2 class="text-xl font-semibold text-gray-900 mb-6">Seu Pedido</h2>
                
                <?php do_action( 'woocommerce_checkout_before_order_review' ); ?>

                <div id="order_review" class="woocommerce-checkout-review-order">
                    <?php do_action( 'woocommerce_checkout_order_review' ); ?>
                </div>

                <?php do_action( 'woocommerce_checkout_after_order_review' ); ?>
            </div>
        </div>
    </div>

</form>

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>
