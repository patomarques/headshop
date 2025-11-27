<?php

/**
 * Test connection details.
 */

namespace EnDistanceBaseShippingOtherSettings;

/**
 * Add array for Settings.
 * Class EnDistanceBaseShippingOtherSettings
 * @package EnDistanceBaseShippingOtherSettings
 */
class EnDistanceBaseShippingOtherSettings
{

    static public $get_connection_details = [];
    static public $plugin_name;

    /**
     * Settings template.
     * @return array
     */
    static public function en_load()
    {
        $start_settings = [
            'en_other_settings_distance_base_shipping' => [
                'name' => __('', 'woocommerce-settings-distance-base-shipping'),
                'type' => 'title',
                'id' => 'en_connection_settings_distance_base_shipping',
            ],
        ];

        // App Name Connection Settings Detail
        $eniture_settings = self::en_set_other_settings_detail();

        $end_settings = [
            'en_connection_settings_end_distance_base_shipping' => [
                'type' => 'sectionend',
                'id' => 'en_connection_settings_end_distance_base_shipping'
            ]
        ];

        $settings = array_merge($start_settings, $eniture_settings, $end_settings);

        return $settings;
    }

    
    /**
     * Connection Settings Detail Set
     * @return array
     */
    static public function en_set_other_settings_detail()
    {
        $profile_default = 'un-selected';
        $profile_options = self::get_profiles();

        echo '<div class="en_other_settings_distance_base_shipping">';

        return
            [
                'en_connection_settings_multi_shipment_label_distance_base_shipping' => [
                    'name' => __('Multi-shipment label', 'woocommerce-settings-distance-base-shipping'),
                    'type' => 'text',
                    'desc' => __('Enter the label to use when more than one shipment is required for the order', 'woocommerce-settings-distance-base-shipping'),
                    'id' => 'en_connection_settings_multi_shipment_label_distance_base_shipping'
                ],
                'en_settings_how_to_handle_multi_shipment_distance_base_shipping' => array(
                    'name' => __("In the case of a Cart that will result in multiple shipments", 'woocommerce-settings-distance-base-shipping'),
                    'type' => 'radio',
                    'default' => 'add',
                    'options' => array(
                        'add' => __("Add the calculated shipping rates together and display the total as the shipping rate", 'woocommerce'),
                        'expensive' => __("Only display the most expensive calculated shipping rate and discard the others", 'woocommerce'),
                        'cheapest' => __("Only display the cheapest calculated shipping rate and discard the others", 'woocommerce'),
                        'conditional' => __("If the quoted shipping profiles include...", 'woocommerce'),
                    ),
                    'id' => 'en_settings_how_to_handle_multi_shipment_distance_base_shipping',
                    'class' => 'en_settings_how_to_handle_multi_shipment_distance_base_shipping',
                ),

                'en_settings_distance_based_multi_ship_profile_include' => array(
                    'name' => __('', 'woocommerce-settings-distance-base-shipping'),
                    'type' => 'select',
                    'default' => $profile_default,
                    'id' => 'en_settings_distance_based_multi_ship_profile_include',
                    'class' => 'en_settings_distance_based_multi_ship_profile_include',
                    'desc' => '... exclude from the shipping quote the following shipping profiles...',
                    'options' => $profile_options['include']
                ),

                'en_settings_distance_based_multi_ship_profile_exclude' => array(
                    'name' => __('', 'woocommerce-settings-distance-base-shipping'),
                    'type' => 'select',
                    'default' => $profile_default,
                    'id' => 'en_settings_distance_based_multi_ship_profile_exclude',
                    'class' => 'en_settings_distance_based_multi_ship_profile_exclude',
                    'options' => $profile_options['exclude']
                ),
                
                'en_settings_error_management_not_in_profile_distance_base_shipping' => [
                    'name' => __('Error management', 'woocommerce-settings-distance-base-shipping'),
                    'type' => 'checkbox',
                    'desc' => 'Don\'t quote shipping if one or more items in the Cart are members of a Shipping Profile that will not return a shipping rate',
                    'id' => 'en_settings_error_management_not_in_profile_distance_base_shipping'
                ],
            ];
    }

    public static function get_profiles(){
        global $wpdb;
        $profiles = [
            'include' => [
                'un-selected' => 'Please select a profile'
            ],
            'exclude' => [
                'un-selected' => 'Please select a profile'
            ],
        ];
        $en_table = $wpdb->prefix . 'en_profiles';
        $profiles_query = "select * from $en_table";
        $en_profile_data = $wpdb->get_results($profiles_query);
        if(is_array($en_profile_data) && count($en_profile_data)){
            foreach($en_profile_data as $key => $profile){
                $profiles['include'][$profile->id] = $profile->profile_nickname;
                // if($profile->id !== get_option('en_settings_distance_based_multi_ship_profile_include')){
                    $profiles['exclude'][$profile->id] = $profile->profile_nickname;
                // }
            }
        }

        return $profiles;
    }

}
