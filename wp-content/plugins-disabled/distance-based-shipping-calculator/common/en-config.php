<?php

/**
 * App Name details.
 */

namespace EnDistanceBaseShippingConfig;

use EnDistanceBaseShippingConnectionSettings\EnDistanceBaseShippingConnectionSettings;
use EnDistanceBaseShippingQuoteSettingsDetail\EnDistanceBaseShippingQuoteSettingsDetail;

/**
 * Config values.
 * Class EnDistanceBaseShippingConfig
 * @package EnDistanceBaseShippingConfig
 */
class EnDistanceBaseShippingConfig
{

    /**
     * Done config settings
     */
    static public function do_config()
    {
        define('EN_DISTANCE_BASE_SHIPPING_PLAN', get_option('EN_DISTANCE_BASE_SHIPPING_plan_number'));
        define('EN_DISTANCE_BASE_SHIPPING_PLAN_MESSAGE', get_option('EN_DISTANCE_BASE_SHIPPING_plan_message'));
        define('EN_DISTANCE_BASE_SHIPPING_NAME', 'Distance Shipping Calculator');
        define('EN_DISTANCE_BASE_SHIPPING_ID', 'distance_base_shipping');
        define('EN_DISTANCE_BASE_SHIPPING_PLUGIN_URL', plugins_url());
        define('EN_DISTANCE_BASE_SHIPPING_ABSPATH', ABSPATH);
        define('EN_DISTANCE_BASE_SHIPPING_DIR', plugins_url(EN_DISTANCE_BASE_SHIPPING_MAIN_DIR));
        define('EN_DISTANCE_BASE_SHIPPING_DIR_FILE', plugin_dir_url(EN_DISTANCE_BASE_SHIPPING_MAIN_FILE));
        define('EN_DISTANCE_BASE_SHIPPING_FILE', plugins_url(EN_DISTANCE_BASE_SHIPPING_MAIN_FILE));
        define('EN_DISTANCE_BASE_SHIPPING_BASE_NAME', plugin_basename(EN_DISTANCE_BASE_SHIPPING_MAIN_FILE));
        define('EN_DISTANCE_BASE_SHIPPING_SERVER_NAME', self::en_get_server_name());
        define('EN_DISTANCE_BASE_SHIPPING_VERSIONS_INFO', self::en_get_woo_version_number());

        define('EN_DISTANCE_BASE_SHIPPING_DECLARED_ZERO', 0);
        define('EN_DISTANCE_BASE_SHIPPING_DECLARED_ONE', 1);
        define('EN_DISTANCE_BASE_SHIPPING_DECLARED_ARRAY', []);
        define('EN_DISTANCE_BASE_SHIPPING_DECLARED_FALSE', false);
        define('EN_DISTANCE_BASE_SHIPPING_DECLARED_TRUE', true);
        define('EN_DISTANCE_BASE_SHIPPING_SHIPPING_NAME', 'distance_base_shipping');
        define('EN_DISTANCE_BASE_SHIPPING_SHIPMENT_WEIGHT_EXCEEDS_PRICE', 150);

        define('EN_DISTANCE_BASE_ROOT_URL', 'https://eniture.com');
       define('EN_DISTANCE_BASE_SHIPPING_ROOT_URL', 'https://ws076v1.eniture.com');

        define('EN_DISTANCE_BASE_ROOT_URL_PRODUCTS', EN_DISTANCE_BASE_ROOT_URL . '/products/');
        define('EN_DISTANCE_BASE_SHIPPING_RAD_URL', EN_DISTANCE_BASE_ROOT_URL . '/woocommerce-residential-address-detection/');
        define('EN_DISTANCE_BASE_SHIPPING_STANDARD_PLAN_URL', EN_DISTANCE_BASE_ROOT_URL . '/plan/woocommerce-worldwide-express-ltl-freight/');
        define('EN_DISTANCE_BASE_SHIPPING_SUPPORT_URL', 'https://support.eniture.com');
        define('EN_DISTANCE_BASE_SHIPPING_ADVANCED_PLAN_URL', EN_DISTANCE_BASE_ROOT_URL . '/plan/woocommerce-worldwide-express-ltl-freight/');
        define('EN_DISTANCE_BASE_SHIPPING_SUBSCRIBE_PLAN_URL', EN_DISTANCE_BASE_ROOT_URL . '/plan/woocommerce-xpo-ltl-freight/');
        define('EN_DISTANCE_BASE_SHIPPING_DOCUMENTATION_URL', EN_DISTANCE_BASE_ROOT_URL . '/woocommerce-distance-based-shipping-calculator');
        define('EN_DISTANCE_BASE_SHIPPING_HITTING_API_URL', EN_DISTANCE_BASE_SHIPPING_ROOT_URL . '/distance-based-shipping/quotes.php');
        define('EN_DISTANCE_BASE_SHIPPING_ADDRESS_HITTING_URL', EN_DISTANCE_BASE_SHIPPING_ROOT_URL . '/addon/google-location.php');
        define('EN_DISTANCE_BASE_SHIPPING_PLAN_HITTING_URL', EN_DISTANCE_BASE_SHIPPING_ROOT_URL . '/web-hooks/subscription-plans/create-plugin-webhook.php?');
        define('EN_DISTANCE_BASE_SHIPPING_SUBSCRIPTION_HITTING_URL', EN_DISTANCE_BASE_SHIPPING_ROOT_URL . '/addon/distance-based/index.php');
        define('EN_DISTANCE_BASE_SHIPPING_ADDRESS_VALIDATION_HITTING_URL', EN_DISTANCE_BASE_SHIPPING_ROOT_URL . '/address-validation/index.php');
        define('EN_DISTANCE_BASE_SHIPPING_ORDER_EXPORT_HITTING_URL', 'https://analytic-data.eniture.com/index.php');

        define('EN_DISTANCE_BASE_SHIPPING_GET_CONNECTION_SETTINGS', wp_json_encode(EnDistanceBaseShippingConnectionSettings::en_get_connection_settings_detail()));
        define('EN_DISTANCE_BASE_SHIPPING_SET_QUOTE_SETTINGS', wp_json_encode(EnDistanceBaseShippingQuoteSettingsDetail::EN_DISTANCE_BASE_SHIPPING_quote_settings()));
        define('EN_DISTANCE_BASE_SHIPPING_GET_QUOTE_SETTINGS', wp_json_encode(EnDistanceBaseShippingQuoteSettingsDetail::EN_DISTANCE_BASE_SHIPPING_get_quote_settings()));

        $en_app_set_quote_settings = json_decode(EN_DISTANCE_BASE_SHIPPING_SET_QUOTE_SETTINGS, true);

        define('EN_DISTANCE_BASE_SHIPPING_ALWAYS_ACCESSORIAL', wp_json_encode(EnDistanceBaseShippingQuoteSettingsDetail::EN_DISTANCE_BASE_SHIPPING_always_accessorials($en_app_set_quote_settings)));
        define('EN_DISTANCE_BASE_SHIPPING_ACCESSORIAL', wp_json_encode(EnDistanceBaseShippingQuoteSettingsDetail::EN_DISTANCE_BASE_SHIPPING_compare_accessorial($en_app_set_quote_settings)));
    }

    /**
     * Get Host
     * @param string $url
     * @return type
     */
    static public function en_get_host($url)
    {
        $parse_url = parse_url(trim($url));
        if (isset($parse_url['host'])) {
            $host = $parse_url['host'];
        } else {
            $path = explode('/', $parse_url['path']);
            $host = $path[0];
        }
        return trim($host);
    }

    /**
     * Get Domain Name
     */
    static public function en_get_server_name()
    {
        global $wp;
        $request = isset($wp->request) ? $wp->request : '';
        $url = home_url($request);
        return self::en_get_host($url);
    }

    /**
     * Return woocomerce and wwe ltl plugin versions
     */
    static public function en_get_woo_version_number()
    {
        if (!function_exists('get_plugins'))
            require_once(ABSPATH . 'wp-admin/includes/plugin.php');

        $plugin_folder = get_plugins('/' . 'woocommerce');
        $plugin_file = 'woocommerce.php';
        $plugin_folders = get_plugins('/' . 'distance-based-shipping-calculator');
        $plugin_folders = !empty($plugin_folders) ? $plugin_folders : get_plugins('/' . 'distance-base-shipping-calculator');
        $plugin_files = 'distance-base-shipping-calculator.php';

        $wc_plugin = (isset($plugin_folder[$plugin_file]['Version'])) ? $plugin_folder[$plugin_file]['Version'] : "";
        $dbsc_plugin = (isset($plugin_folders[$plugin_files]['Version'])) ? $plugin_folders[$plugin_files]['Version'] : "";

        return array(
            'woocommerce_plugin_version' => $wc_plugin,
            'dbsc_plugin_version' => $dbsc_plugin
        );
    }

}
