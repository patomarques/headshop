<?php

/**
 * Receiver address.
 */

namespace EnDistanceBaseShippingReceiverAddress;

/**
 * Get address from cart|checkout page.
 * Class EnDistanceBaseShippingReceiverAddress
 * @package EnDistanceBaseShippingReceiverAddress
 */
class EnDistanceBaseShippingReceiverAddress
{

    static public $woocommerce_version;

    /**
     * Receiver address
     * @return array
     */
    static public function get_address()
    {
        self::en_get_woo_version_number();

        $postcode = WC()->customer->get_shipping_postcode();
        $state = WC()->customer->get_shipping_state();
        $city = WC()->customer->get_shipping_city();
        $country = WC()->customer->get_shipping_country();

        return [
            'receiverZip' => (strlen($country) > 0) ? $postcode : self::get_postcode(),
            'receiverState' => (strlen($country) > 0) ? $state : self::get_state(),
            'receiverCountryCode' => (strlen($country) > 0) ? $country : self::get_country(),
            'receiverCity' => (strlen($country) > 0) ? $city : self::get_city(),
            'addressLine' => WC()->customer->get_shipping_address_1(),
            'addressLine2' => WC()->customer->get_shipping_address_2(),
        ];
    }

    /**
     * Declared woo version publically
     */
    static public function en_get_woo_version_number()
    {
        if (!function_exists('get_plugins')) {
            require_once(ABSPATH . 'wp-admin/includes/plugin.php');
        }
        $plugin_folder = get_plugins('/' . 'woocommerce');
        $plugin_file = 'woocommerce.php';
        (isset($plugin_folder[$plugin_file]['Version'])) ?
            self::$woocommerce_version = $plugin_folder[$plugin_file]['Version'] : '';
    }

    /**
     * Get Postcode
     * @return string
     */
    static public function get_postcode()
    {
        $postcode = "";
        switch (self::$woocommerce_version) {
            case (self::$woocommerce_version <= '2.7'):
                $postcode = WC()->customer->get_postcode();
                break;
            case (self::$woocommerce_version >= '3.0'):
                $postcode = WC()->customer->get_billing_postcode();
                break;
            default:
                $postcode = WC()->customer->get_shipping_postcode();
                break;
        }

        return $postcode;
    }

    /**
     * Get state
     * @return string
     */
    static public function get_state()
    {
        $state = "";
        switch (self::$woocommerce_version) {
            case (self::$woocommerce_version <= '2.7'):
                $state = WC()->customer->get_state();
                break;
            case (self::$woocommerce_version >= '3.0'):
                $state = WC()->customer->get_billing_state();
                break;
            default:
                $state = WC()->customer->get_shipping_state();
                break;
        }
        return $state;
    }

    /**
     * Get city
     * @return string
     */
    static public function get_city()
    {
        $city = "";
        switch (self::$woocommerce_version) {
            case (self::$woocommerce_version <= '2.7'):
                $city = WC()->customer->get_city();
                break;
            case (self::$woocommerce_version >= '3.0'):
                $city = WC()->customer->get_billing_city();
                break;
            default:
                $city = WC()->customer->get_shipping_city();
                break;
        }
        return $city;
    }

    /**
     * Get country
     * @return string
     */
    static public function get_country()
    {
        $country = "";
        switch (self::$woocommerce_version) {
            case (self::$woocommerce_version <= '2.7'):
                $country = WC()->customer->get_country();
                break;
            case (self::$woocommerce_version >= '3.0'):
                $country = WC()->customer->get_billing_country();
                break;
            default:
                $country = WC()->customer->get_shipping_country();
                break;
        }
        return $country;
    }


}
