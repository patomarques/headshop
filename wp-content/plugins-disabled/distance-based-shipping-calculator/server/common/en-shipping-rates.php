<?php
/**
 * App Name settings.
 */

namespace EnDistanceBaseShippingQuoteSettingsDetail;

/**
 * Get and Done settings.
 * Class EnDistanceBaseShippingQuoteSettingsDetail
 * @package EnDistanceBaseShippingQuoteSettingsDetail
 */
class EnDistanceBaseShippingQuoteSettingsDetail
{
    static public $EN_DISTANCE_BASE_SHIPPING_accessorial = [];

    /**
     * Set quote settings detail
     */
    static public function EN_DISTANCE_BASE_SHIPPING_get_quote_settings()
    {
        $accessorials = [];
        $en_settings = json_decode(EN_DISTANCE_BASE_SHIPPING_SET_QUOTE_SETTINGS, true);

        return $accessorials;
    }

    /**
     * Set quote settings detail
     */
    static public function EN_DISTANCE_BASE_SHIPPING_always_accessorials()
    {
        $accessorials = [];
        $en_settings = self::EN_DISTANCE_BASE_SHIPPING_quote_settings();
        $en_settings['liftgate_delivery'] == 'yes' ? $accessorials[] = 'L' : "";
        $en_settings['residential_delivery'] == 'yes' ? $accessorials[] = 'R' : "";

        return $accessorials;
    }

    /**
     * Set quote settings detail
     */
    static public function EN_DISTANCE_BASE_SHIPPING_quote_settings()
    {
        $enable_carriers = get_option('EN_DISTANCE_BASE_SHIPPING_carriers');
        $enable_carriers = (isset($enable_carriers) && strlen($enable_carriers) > 0) ?
            json_decode($enable_carriers, true) : [];
        $rating_method = get_option('en_quote_settings_rating_method_distance_base_shipping');
        $quote_settings_label = get_option('en_quote_settings_custom_label_distance_base_shipping');

        $quote_settings =
            [
                'transit_days' => get_option('en_quote_settings_show_delivery_estimate_distance_base_shipping'),
                'own_freight' => get_option('en_quote_settings_own_arrangment_distance_base_shipping'),
                'own_freight_label' => get_option('en_quote_settings_text_for_own_arrangment_distance_base_shipping'),
                'total_carriers' => get_option('en_quote_settings_number_of_options_distance_base_shipping'),
                'rating_method' => (strlen($rating_method)) > 0 ? $rating_method : "Cheapest",
                'en_settings_label' => ($rating_method == "average_rate" || $rating_method == "Cheapest") ? $quote_settings_label : "",
                'handling_unit_weight' => get_option('en_quote_settings_handling_unit_weight_distance_base_shipping'),
                'handling_fee' => get_option('en_quote_settings_handling_fee_distance_base_shipping'),
                'enable_carriers' => $enable_carriers,
                'liftgate_delivery' => get_option('en_quote_settings_liftgate_delivery_distance_base_shipping'),
                'liftgate_delivery_option' => get_option('distance_base_shipping_liftgate_delivery_as_option'),
                'residential_delivery' => get_option('en_quote_settings_residential_delivery_distance_base_shipping'),
                'liftgate_resid_delivery' => get_option('en_woo_addons_liftgate_with_auto_residential'),
                'custom_error_message' => get_option('en_quote_settings_checkout_error_message_distance_base_shipping'),
                'custom_error_enabled' => get_option('en_quote_settings_option_select_when_unable_retrieve_shipping_distance_base_shipping'),
            ];

        return $quote_settings;
    }

    /**
     * Get quote settings detail
     * @param array $en_settings
     * @return array
     */
    static public function EN_DISTANCE_BASE_SHIPPING_compare_accessorial($en_settings)
    {
        self::$EN_DISTANCE_BASE_SHIPPING_accessorial[] = ['S'];
        $en_settings['liftgate_delivery_option'] == 'yes' ? self::$EN_DISTANCE_BASE_SHIPPING_accessorial[] = ['L'] : "";

        return self::$EN_DISTANCE_BASE_SHIPPING_accessorial;
    }
}