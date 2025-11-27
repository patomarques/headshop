<?php

/**
 * Get detail from order's object and show order widget detail.
 */

namespace EnDistanceBaseShippingOrderWidget;

/**
 * Show template about order's detail.
 * Class EnDistanceBaseShippingOrderWidget
 * @package EnDistanceBaseShippingOrderWidget
 */
class EnDistanceBaseShippingOrderWidget
{

    public $sender_origin = '';
    public $accessorials = [];
    public $label_sufex = [];
    public $product_name = [];
    public $count;
    public $order_key;
    public $shipping_method_title;
    public $shipping_method_total;
    public $result_details;
    public $currency_symbol;

    /**
     * Hook for load.
     * EnDistanceBaseShippingOrderWidget constructor.
     */
    public function __construct()
    {
        $this->en_call_hooks();
    }

    /**
     * Woocommerce order action hook
     */
    public function en_call_hooks()
    {
        add_action(
            'woocommerce_order_actions', array($this, 'en_assign_order_details'), 11, 2
        );
    }

    /**
     * Get order details from meta data.
     * @param array $actions
     * @return mixed
     */
    function en_assign_order_details($actions, $order)
    {
        $this->label_sufex = $this->accessorials = [];
        $this->order_key = $order->get_order_key();
        $shipping_details = $order->get_items('shipping');
        foreach ($shipping_details as $item_id => $shipping_item_obj) {
            $this->shipping_method_title = $shipping_item_obj->get_method_title();
            $this->shipping_method_total = $shipping_item_obj->get_total();
            $this->result_details = $shipping_item_obj->get_formatted_meta_data();
        }

        /* Add metabox if user selected our service */
        if (!empty($this->result_details) && count($this->result_details) > 0) {
            add_meta_box('en_additional_order_details', __('Additional Order Details', 'woocommerce'), array($this, 'en_add_meta_box_order_widget'), get_current_screen()->id, 'side', 'low', 'core');
        }

        return $actions;
    }

    /**
     * Add order details in metabox.
     */
    public function en_add_meta_box_order_widget()
    {
        $order_details = $this->result_details;
        $this->en_origin_services_details($order_details);
    }

    /**
     * Origin & Services details.
     * @param array $order_data
     */
    function en_origin_services_details($order_data_arr)
    {
        $this->currency_symbol = get_woocommerce_currency_symbol(get_option('woocommerce_currency'));
        $this->count = 0;
        $min_prices_triggered = false;

        foreach ($order_data_arr as $key => $order_data) {
            if (isset($order_data->key) && $order_data->key === "min_prices") {
                $min_prices_triggered = true;
                $eniture_meta_data = json_decode($order_data->value, TRUE);
                foreach ($eniture_meta_data as $ship_num => $services) {
                    $ship_num++;
                    $this->en_order_widget_template($services, $ship_num);
                }
            }
        }

        if (!$min_prices_triggered) {
            $accessorials = $label_sufex = [];
            foreach ($order_data_arr as $key => $value) {
                (isset($value->key) && $value->key == "sender_origin") ? $sender_origin = ucwords($value->value) : "";
                (isset($value->key) && $value->key == "accessorials") ? $accessorials = json_decode($value->value, TRUE) : "";
                (isset($value->key) && $value->key == "label_sufex") ? $label_sufex = json_decode($value->value, TRUE) : "";
                (isset($value->key) && $value->key == "product_name") ? $product_name = $value->value : [];
            }

            $services = [
                'meta_data' => [
                    'sender_origin' => $sender_origin,
                    'product_name' => $product_name,
                    'accessorials' => wp_json_encode(array_merge($accessorials, $label_sufex)),
                ],
                'label' => $this->shipping_method_title,
                'cost' => $this->shipping_method_total,
            ];

            $this->en_order_widget_template($services, 1);
        }
    }

    /**
     * Get data from meta array
     * @param array $meta_data
     */
    public function en_order_widget_template($meta_data, $count)
    {
        if (isset($meta_data['origin'])) {
            $sender_origin = (isset($meta_data['origin'])) ? $meta_data['origin'] : [];
            $origin_address = $sender_origin['addressLine'] . ", " . $sender_origin['city'] . ", " . $sender_origin['state'] . " " . $sender_origin['zipCode'];
        } else if (isset($meta_data['meta_data']['sender_origin'])) {
            $origin_address = $meta_data['meta_data']['sender_origin'];
        }

        $items = (isset($meta_data['items'])) ? $meta_data['items'] : [];
        $product_name = (isset($meta_data['meta_data']['product_name'])) ? json_decode($meta_data['meta_data']['product_name'], true) : [];
        $accessorials = (isset($meta_data['meta_data']['accessorials'])) ? json_decode($meta_data['meta_data']['accessorials'], true) : [];
        $label = (isset($meta_data['label'])) ? $meta_data['label'] : '';
        $cost = (isset($meta_data['cost'])) ? $meta_data['cost'] : 0;

        echo '<h4 class="en-order-heading">Shipment ' . $count . " > Origin & Services </h4>";
        echo '<ul class="en-order-list">';
        echo strlen($origin_address) > 0 ? '<li class="en-order-sender-origin">' . esc_attr($origin_address) . '</li>' : '';
        echo '<li>' . esc_attr($label) . ': ' . $this->en_format_price($cost) . '</li>';
        /* Show accessorials */
        $this->en_show_accessorials(array_unique($accessorials));
        echo "</ul>";

        if (!empty($items)) {
            echo '<h4 class="en-order-heading">Shipment ' . $count . " > items </h4>";
            echo '<ul id="en-order-products-detail" class="en-order-list">';
            foreach (array_filter($items) as $item) {
                echo '<li>' . esc_attr($item['piecesOfLineItem'] . ' x ' . $item['lineItemName']) . '</li>';
            }
            echo '</ul>';
        } else if (!empty($product_name)) {
            echo '<h4 class="en-order-heading">Shipment ' . $count . " > items </h4>";
            echo '<ul id="en-order-products-detail" class="en-order-list">';
            foreach (array_filter($product_name) as $item) {
                echo '<li>' . esc_attr($item) . '</li>';
            }
            echo '</ul>';
        }

        echo '<br/>';
    }

    /**
     * Show accessorial on order detail page.
     */
    public function en_show_accessorials($service_order_data)
    {
        foreach ($service_order_data as $key => $value) {
            echo ($value == "R") ? '<li>Residential delivery</li>' : "";
            echo ($value == "L") ? '<li>Lift gate delivery</li>' : "";
            echo ($value == "H") ? '<li>Hazardous Material</li>' : "";
            echo ($value == "HAT") ? '<li>Hold At Terminal</li>' : "";
        }
    }

    /**
     * Price format.
     * @param int/double/string $dollars
     * @return string
     */
    function en_format_price($dollars)
    {
        return $this->currency_symbol . number_format(sprintf('%0.2f', preg_replace("/[^0-9.]/", "", $dollars)), 2);
    }

}
