<?php
/**
 * Get rates from function calculate shipping through standard plugin.
 */

namespace EnDistanceBaseShippingOrderRates;

/**
 * Order page rates.
 * Class EnDistanceBaseShippingOrderRates
 * @package EnDistanceBaseShippingOrderRates
 */
class EnDistanceBaseShippingOrderRates
{
    /**
     * Hook in ajax handlers.
     * EnDistanceBaseShippingOrderRates constructor.
     */
    public function __construct()
    {
        add_action('wp_ajax_nopriv_en_transportation_insight_admin_order_quotes', array($this, 'en_transportation_insight_admin_order_quotes'));
        add_action('wp_ajax_en_transportation_insight_admin_order_quotes', array($this, 'en_transportation_insight_admin_order_quotes'));
    }

    /**
     * Get quotes from calculate shipping forcefully
     */
    public function en_transportation_insight_admin_order_quotes()
    {
        global $woocommerce;
        $errors = array();

        $order_id = (isset($_POST['order_id'])) ? sanitize_text_field($_POST['order_id']) : '';
        $bill_zip = (isset($_POST['bill_zip'])) ? sanitize_text_field($_POST['bill_zip']) : '';
        $ship_zip = (isset($_POST['ship_zip'])) ? sanitize_text_field($_POST['ship_zip']) : '';

        (strlen($ship_zip) > 0 || strlen($bill_zip) > 0) ? '' : $errors[] = "Please enter billing or shipping address.";

        $order = wc_get_order($order_id);

        $items = $order->get_items();

        (isset($woocommerce->cart) && !empty($woocommerce->cart)) ? $woocommerce->cart->empty_cart() : '';

        foreach ($items as $item) {
            $product_id = (isset($item['variation_id']) && !empty($item['variation_id'])) ?
                $item['variation_id'] : $item['product_id'];
            $woocommerce->cart->add_to_cart($product_id, $item['qty']);
            $cart = array('contents' => $woocommerce->cart->get_cart($product_id));

        }

        ((isset($cart['contents'])) && empty($cart['contents']) || (empty($items))) ? $errors[] = "Empty shipping cart content." : '';

        if (!empty($errors)) {
            echo wp_json_encode(array('errors' => $errors));
            exit();
        }

        $shipping_class = new \EnDistanceBaseShippingShippingRates();
        $response = $shipping_class->calculate_shipping($cart);
        $response = current($response);

        echo wp_json_encode(isset($response['cost'], $response['label']) ?
            array('label' => $response['label'], 'cost' => $response['cost']) :
            array('errors' => "No Quotes return."));

        exit();
    }
}