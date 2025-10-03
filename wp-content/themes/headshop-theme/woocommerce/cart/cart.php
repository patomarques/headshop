<?php
/**
 * Cart Page
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_cart' ); ?>

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

<h1 class="text-3xl font-bold text-gray-900 mb-8">Carrinho de Compras</h1>

<form class="woocommerce-cart-form" action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post">
    <?php do_action( 'woocommerce_before_cart_table' ); ?>

    <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-8">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-4 text-left text-sm font-medium text-gray-900">Produto</th>
                    <th class="px-6 py-4 text-left text-sm font-medium text-gray-900">Preço</th>
                    <th class="px-6 py-4 text-center text-sm font-medium text-gray-900">Quantidade</th>
                    <th class="px-6 py-4 text-right text-sm font-medium text-gray-900">Subtotal</th>
                    <th class="px-6 py-4"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php do_action( 'woocommerce_before_cart_contents' ); ?>

                <?php
                foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
                    $_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
                    $product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

                    if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
                        $product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
                        ?>
                        <tr class="cart_item">
                            <!-- Product -->
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-4">
                                    <div class="w-16 h-16 bg-gray-100 rounded overflow-hidden">
                                        <?php
                                        $thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image('thumbnail'), $cart_item, $cart_item_key );
                                        
                                        // If no thumbnail, use category-specific placeholder
                                        if (empty($thumbnail) || !$_product->get_image_id()) {
                                            $thumbnail = '<img src="' . headshop_get_category_placeholder($_product->get_id(), 64, 64) . '" alt="Imagem do produto" class="w-full h-full object-cover">';
                                        }
                                        
                                        if ( ! $product_permalink ) {
                                            echo $thumbnail;
                                        } else {
                                            printf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $thumbnail );
                                        }
                                        ?>
                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-900">
                                            <?php
                                            if ( ! $product_permalink ) {
                                                echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key ) . '&nbsp;' );
                                            } else {
                                                echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', sprintf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $_product->get_name() ), $cart_item, $cart_item_key ) );
                                            }

                                            do_action( 'woocommerce_after_cart_item_name', $cart_item, $cart_item_key );

                                            // Meta data.
                                            echo wc_get_formatted_cart_item_data( $cart_item );

                                            // Backorder notification.
                                            if ( $_product->backorders_require_notification() && $_product->is_on_backorder( $cart_item['quantity'] ) ) {
                                                echo wp_kses_post( apply_filters( 'woocommerce_cart_item_backorder_notification', '<p class="backorder_notification">' . esc_html__( 'Available on backorder', 'woocommerce' ) . '</p>', $product_id ) );
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Price -->
                            <td class="px-6 py-4 text-gray-900">
                                <?php echo apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key ); ?>
                            </td>

                            <!-- Quantity -->
                            <td class="px-6 py-4">
                                <div class="flex justify-center">
                                    <?php
                                    if ( $_product->is_sold_individually() ) {
                                        $product_quantity = sprintf( '1 <input type="hidden" name="cart[%s][qty]" value="1" />', $cart_item_key );
                                    } else {
                                        $product_quantity = woocommerce_quantity_input(
                                            array(
                                                'input_name'   => "cart[{$cart_item_key}][qty]",
                                                'input_value'  => $cart_item['quantity'],
                                                'max_value'    => $_product->get_max_purchase_quantity(),
                                                'min_value'    => '0',
                                                'product_name' => $_product->get_name(),
                                            ),
                                            $_product,
                                            false
                                        );
                                    }

                                    echo apply_filters( 'woocommerce_cart_item_quantity', $product_quantity, $cart_item_key, $cart_item );
                                    ?>
                                </div>
                            </td>

                            <!-- Subtotal -->
                            <td class="px-6 py-4 text-right font-medium text-gray-900">
                                <?php echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ); ?>
                            </td>

                            <!-- Remove -->
                            <td class="px-6 py-4">
                                <?php
                                echo apply_filters(
                                    'woocommerce_cart_item_remove_link',
                                    sprintf(
                                        '<a href="%s" class="text-red-600 hover:text-red-800" aria-label="%s" data-product_id="%s" data-product_sku="%s">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </a>',
                                        esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
                                        esc_html__( 'Remove this item', 'woocommerce' ),
                                        esc_attr( $product_id ),
                                        esc_attr( $_product->get_sku() )
                                    ),
                                    $cart_item_key
                                );
                                ?>
                            </td>
                        </tr>
                        <?php
                    }
                }
                ?>

                <?php do_action( 'woocommerce_cart_contents' ); ?>

                <tr>
                    <td colspan="6" class="px-6 py-4">
                        <div class="flex justify-between items-center">
                            <div>
                                <?php if ( wc_coupons_enabled() ) { ?>
                                    <div class="flex items-center space-x-2">
                                        <input type="text" name="coupon_code" class="px-3 py-2 border border-gray-300 rounded-md" placeholder="<?php esc_attr_e( 'Coupon code', 'woocommerce' ); ?>" value="" />
                                        <button type="submit" class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700 transition-colors" name="apply_coupon" value="<?php esc_attr_e( 'Apply coupon', 'woocommerce' ); ?>"><?php esc_html_e( 'Apply coupon', 'woocommerce' ); ?></button>
                                        <?php do_action( 'woocommerce_cart_coupon' ); ?>
                                    </div>
                                <?php } ?>
                            </div>
                            
                            <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded-md hover:bg-green-700 transition-colors" name="update_cart" value="<?php esc_attr_e( 'Update cart', 'woocommerce' ); ?>"><?php esc_html_e( 'Update cart', 'woocommerce' ); ?></button>
                        </div>
                    </td>
                </tr>

                <?php do_action( 'woocommerce_after_cart_contents' ); ?>
            </tbody>
        </table>
    </div>

    <?php do_action( 'woocommerce_after_cart_table' ); ?>
</form>

<?php do_action( 'woocommerce_before_cart_collaterals' ); ?>

<div class="grid lg:grid-cols-3 gap-8">
    <div class="lg:col-span-2">
        <?php
        /**
         * Cart collaterals hook.
         *
         * @hooked woocommerce_cross_sell_display
         * @hooked woocommerce_cart_totals - 10
         */
        do_action( 'woocommerce_cart_collaterals' );
        ?>
    </div>
    
    <div>
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Resumo do Pedido</h3>
            
            <?php woocommerce_cart_totals(); ?>
            
            <div class="mt-6">
                <a href="<?php echo esc_url( wc_get_checkout_url() ); ?>" class="block w-full bg-green-600 text-white text-center py-3 px-6 rounded-lg hover:bg-green-700 transition-colors font-medium">
                    <?php esc_html_e( 'Proceed to checkout', 'woocommerce' ); ?>
                </a>
            </div>
        </div>
    </div>
</div>

<?php do_action( 'woocommerce_after_cart' ); ?>
