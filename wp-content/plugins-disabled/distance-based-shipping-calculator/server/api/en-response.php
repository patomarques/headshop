<?php

/**
 * Customize the api response.
 */

namespace EnDistanceBaseShippingResponse;

use EnDistanceBaseShippingFilterQuotes\EnDistanceBaseShippingFilterQuotes;
use EnDistanceBaseShippingOtherRates\EnDistanceBaseShippingOtherRates;
use EnDistanceBaseShippingVersionCompact\EnDistanceBaseShippingVersionCompact;

/**
 * Compile the rates.
 * Class EnDistanceBaseShippingResponse
 * @package EnDistanceBaseShippingResponse
 */
class EnDistanceBaseShippingResponse
{

    static public $en_step_for_rates = [];
    static public $en_small_package_quotes = [];
    static public $en_step_for_sender_origin = [];
    static public $en_step_for_product_name = [];
    static public $en_quotes_info_api = [];
    static public $en_accessorial = [];
    static public $en_always_accessorial = [];
    static public $en_settings = [];
    static public $en_package = [];
    static public $en_origin_address = [];
    static public $en_is_shipment = '';
    static public $en_auto_residential_status = '';
    static public $rates;
    static public $same_method_name = TRUE;

    /**
     * Address set for order widget
     * @param array $sender_origin
     * @return string
     */
    static public function en_step_for_sender_origin($sender_origin)
    {
        return $sender_origin['senderLocation'] . ": " . $sender_origin['senderCity'] . ", " . $sender_origin['senderState'] . " " . $sender_origin['senderZip'];
    }

    /**
     * filter detail for order widget detail
     * @param array $en_package
     * @param mixed $key
     */
    static public function en_save_detail_for_order_widget($en_package, $key)
    {
        self::$en_step_for_sender_origin = self::en_step_for_sender_origin($en_package['originAddress'][$key]);
        self::$en_step_for_product_name = (isset($en_package['product_name'][$key])) ? $en_package['product_name'][$key] : [];
    }

    /**
     * Shipping rates
     * @param array $response
     * @param array $en_package
     * @return array
     */
    static public function en_rates($response, $en_package)
    {
        self::$rates = [];
        $en_response = (!empty($response) && is_array($response)) ? $response : [];
        $profiles = self::en_is_shipment_api_response($en_response);
        $profiles = (isset($profiles['q'])) ? $profiles['q'] : [];
        $services = $last_label = [];

        // Eniture debug mode
        do_action("eniture_debug_mood", EN_DISTANCE_BASE_SHIPPING_NAME . " Profiles ", $profiles);

        foreach ($profiles as $profile_id => $origin) {

            $service_label = $eniture_meta_data = [];
            $origin_key = key($origin);

            $origin_address = (isset($en_package['shippingProfiles'][$profile_id]['shippingFrom'][$origin_key])) ? $en_package['shippingProfiles'][$profile_id]['shippingFrom'][$origin_key] : [];
            $line_items = (isset($en_package['commodityDetails'][$profile_id])) ? $en_package['commodityDetails'][$profile_id] : [];
            self::$en_step_for_sender_origin = $origin_address;
            self::$en_step_for_product_name = $line_items;

            $rates = (isset($origin[$origin_key])) ? $origin[$origin_key] : '';

            foreach ($rates as $rate_id => $rate) {

                $rate_cost = isset($rate['totalNetCharges']) ? $rate['totalNetCharges'] : 'undefined';
                $rate_distance = isset($rate['totalDistance']) ? $rate['totalDistance'] : '';

                $method_name = (isset($en_package['shippingProfiles'][$profile_id]['shippingMethods'][$rate_id]['methodName'])) ? trim($en_package['shippingProfiles'][$profile_id]['shippingMethods'][$rate_id]['methodName']) : '';
                $description = (isset($en_package['shippingProfiles'][$profile_id]['shippingMethods'][$rate_id]['description'])) ? trim($en_package['shippingProfiles'][$profile_id]['shippingMethods'][$rate_id]['description']) : '';
                $displayPreference = (isset($en_package['shippingProfiles'][$profile_id]['shippingMethods'][$rate_id]['displayPreference'])) ? trim($en_package['shippingProfiles'][$profile_id]['shippingMethods'][$rate_id]['displayPreference']) : '';
                $service_label[] = $method_name;
                // make data for order widget detail
                $sender_origin = self::$en_step_for_sender_origin;

                if(empty($displayPreference) || $displayPreference == 'no'){
                    $displayText = '';
                }elseif($displayPreference == 'distance'){
                    $displayText = $rate_distance;
                }elseif($displayPreference == 'description'){    
                    $displayText = $description;
                }

                $en_meta_data = [
                    'label' => $method_name,
                    'cost' => $rate_cost,
                    'distance' => $rate_distance,
                    'origin' => $sender_origin,
                    // for ws
                    'address' => $sender_origin,
                    'receiver_address' => [
                        'city' => ($en_package['receiverCity']) ? $en_package['receiverCity'] : '',
                        'state' => ($en_package['receiverState']) ? $en_package['receiverState'] : '',
                        'zip' => ($en_package['receiverZip']) ? $en_package['receiverZip'] : '',
                        'country' => ($en_package['receiverCountryCode']) ? $en_package['receiverCountryCode'] : ''
                    ],
                    'description' => $displayText,
                    'items' => self::$en_step_for_product_name,
                ];

                $meta_data['meta_data'] = [
                    'sender_origin' => $sender_origin['addressLine'] . ", " . $sender_origin['city'] . ", " . $sender_origin['state'] . " " . $sender_origin['zipCode'],
                    'product_name' => wp_json_encode(EnDistanceBaseShippingVersionCompact::en_array_column($line_items, 'lineItemNameAndQty')),
                ];

                $eniture_meta_data = array_merge($en_meta_data, $meta_data);
                $pattern = '/[&%#\/\\\$£]/';
                $append_str = preg_replace($pattern, '', $method_name);
                $append_str = str_replace(' ', '', esc_attr($append_str));
                $id = 'en_dbsc_'.$append_str;
                $services[$profile_id][] = [
                    'id' => $id,
                    'label' => $method_name,
                    'cost' => $rate_cost,
                    'description' => $displayText,
                    'eniture_meta_data' => [$eniture_meta_data],
                ];
            }

            self::$same_method_name && !empty($last_label) && !empty($service_label) && (!empty(array_diff($last_label, $service_label)) || count($last_label) != count($service_label)) ? self::$same_method_name = FALSE : '';
            $last_label = $service_label;
        }

        if (!empty($services)) {
            $en_is_shipment = self::$en_is_shipment;
            self::$en_is_shipment($services, 'standard');
        }

        return self::$rates;
    }

    /**
     * sort array
     * @param array type $rate
     * @return array type
     */
    static public function sort_asec_order_arr($rate, $index)
    {
        $price_sorted_key = array();
        foreach ($rate as $key => $cost_carrier) {
            $price_sorted_key[$key] = (isset($cost_carrier[$index])) ? $cost_carrier[$index] : 0;
        }
        array_multisort($price_sorted_key, SORT_ASC, $rate);

        return $rate;
    }

    /**
     * Multi shipment query
     * @param array $en_rates
     * @param string $accessorial
     */
    static public function en_multi_shipment($services, $accessorial)
    {
        $multi_ship_handler = get_option('en_settings_how_to_handle_multi_shipment_distance_base_shipping');
        $multi_shipment_label = get_option('en_connection_settings_multi_shipment_label_distance_base_shipping');
        $label = !self::$same_method_name && strlen($multi_shipment_label) > 0 ? $multi_shipment_label : 'Shipping';

        if($multi_ship_handler == 'expensive' || $multi_ship_handler == 'cheapest'){

            self::calculate_exp_or_chea_rate_from_multiship($services, $accessorial, $label, $multi_ship_handler);

        }else{

            $description = 'Undefined';
            foreach ($services as $key => $en_rates) {
                if (self::$same_method_name) {
                    foreach ($en_rates as $same_method_key => $same_method_rate) {
                        $label = $accessorial = $same_method_rate['label'];
                        $en_calculated_cost = $same_method_rate['cost'];
                        $eniture_meta_data = $same_method_rate['eniture_meta_data'];
                        self::en_multi_shipment_merge_rates($accessorial, $en_calculated_cost, $eniture_meta_data, $label);
                        if($description == 'Undefined'){
                            $description = trim($same_method_rate['description']);
                        }else{
                            if(!empty($description) && (empty($same_method_rate['description']) || trim($same_method_rate['description']) != $description)){
                                $description == '';
                            }
                        }
                        
                    }
                } else {
                    
                    $en_rates = self::sort_asec_order_arr($en_rates, 'cost');
                    
                    $en_rates = (isset($en_rates) && (is_array($en_rates))) ? array_slice($en_rates, 0, 1) : [];
                    $en_calculated_cost = array_sum(EnDistanceBaseShippingVersionCompact::en_array_column($en_rates, 'cost'));
                    $en_rates_reset = reset($en_rates);
                    $eniture_meta_data = (isset($en_rates_reset['eniture_meta_data'])) ? $en_rates_reset['eniture_meta_data'] : [];
                    if($description == 'Undefined'){
                        $description = trim($en_rates_reset['description']);
                    }else{
                        if(!empty($description) && (empty($en_rates_reset['description']) || trim($en_rates_reset['description']) != $description)){
                            $description = '';
                        }
                    }
                    self::en_multi_shipment_merge_rates($accessorial, $en_calculated_cost, $eniture_meta_data, $label);
                }

            }

            if(!empty($description) && $description != 'Undefined'){
                self::$rates[$accessorial]['description'] = $description;
            }
        }
    }

    /**
     * Multi shipment query for merge rates.
     * @param string $accessorial
     * @param string $en_calculated_cost
     * @param array $eniture_meta_data
     * @param string $label
     */
    static public function en_multi_shipment_merge_rates($accessorial, $en_calculated_cost, $eniture_meta_data, $label)
    {
        if (isset(self::$rates[$accessorial])) {
            if(!empty($label)){
                $pattern = '/[&%#\/\\\$£]/';
                $append_str = preg_replace($pattern, '', $label);
                $append_str = str_replace(' ', '', esc_attr($append_str));
                $id = 'en_dbsc_'.$append_str;
            }else{
                $id = EnDistanceBaseShippingFilterQuotes::rand_string();
            }
            self::$rates[$accessorial]['id'] = $id;
            self::$rates[$accessorial]['cost'] += $en_calculated_cost;
            self::$rates[$accessorial]['eniture_meta_data'] = array_merge(self::$rates[$accessorial]['eniture_meta_data'], $eniture_meta_data);
        } else {
            self::$rates[$accessorial] = [
                'id' => $accessorial,
                'label' => $label,
                'cost' => $en_calculated_cost,
                'shipment' => 'multiple',
                'eniture_meta_data' => $eniture_meta_data,
            ];
        }
    }

    /**
     * Single shipment query
     * @param array $en_rates
     * @param string $accessorial
     */
    static public function en_single_shipment($en_rates, $accessorial)
    {
        $en_rates = reset($en_rates);
        self::$rates = array_merge(self::$rates, $en_rates);
    }

    /**
     * Sanitize the value from array
     * @param string $index
     * @param dynamic $is_not_matched
     * @return dynamic mixed
     */
    static public function en_sanitize_rate($index, $is_not_matched)
    {
        return (isset(self::$en_step_for_rates[$index])) ? self::$en_step_for_rates[$index] : $is_not_matched;
    }

    /**
     * There is single or multiple shipment
     * @param array $en_response
     */
    static public function en_is_shipment_api_response($en_response)
    {
        if (isset($en_response['debug'])) {
            self::$en_quotes_info_api = $en_response['debug'];
            unset($en_response['debug']);
        }

        // Multishipment check if conditional radio sellected
        return self::identify_shipment_and_apply_multi_shipment_conditional_check_if_needed($en_response);

    }

    static public function calculate_exp_or_chea_rate_from_multiship($services, $accessorial, $label, $multi_ship_handler){
        
        $rate_data = [];
        $final_rate = null;
        foreach ($services as $key => $en_rates) {
            
            foreach ($en_rates as $method_key => $method_rate) {
                if (self::$same_method_name) {
                    $label = $accessorial = $method_rate['label'];
                }
                
                $en_calculated_cost = $method_rate['cost'];
                
                if($final_rate == null || ($multi_ship_handler == 'expensive' && $en_calculated_cost > $final_rate) 
                || ($multi_ship_handler == 'cheapest' && $en_calculated_cost < $final_rate)){
                    $final_rate = $en_calculated_cost;
                    $rate_data = $method_rate;
                }
                
            }

        }

        $eniture_meta_data = (isset($rate_data['eniture_meta_data'])) ? $rate_data['eniture_meta_data'] : [];
        
        self::en_multi_shipment_merge_rates($accessorial, $final_rate, $eniture_meta_data, $label);

        if(!empty($en_rates_reset['description'])){
            self::$rates[$accessorial]['description'] = $en_rates_reset['description'];
        }

    }

    /**
     * This function Identifies shipment and filter rates for multiship conditional check if needed
     */
    public static function identify_shipment_and_apply_multi_shipment_conditional_check_if_needed($en_response){

        if(isset($en_response['q']) && is_array($en_response['q']) && count($en_response['q']) > 1){
            self::$en_is_shipment = 'en_multi_shipment';
            $multi_ship_handler = get_option('en_settings_how_to_handle_multi_shipment_distance_base_shipping');
            if($multi_ship_handler == 'conditional'){
                $multi_ship_profile_include = get_option('en_settings_distance_based_multi_ship_profile_include');
                $multi_ship_profile_exclude = get_option('en_settings_distance_based_multi_ship_profile_exclude');
                if(!empty($multi_ship_profile_include) && !empty($multi_ship_profile_exclude) && array_key_exists($multi_ship_profile_include,$en_response['q']) && array_key_exists($multi_ship_profile_exclude,$en_response['q']) ){
                    unset($en_response['q'][$multi_ship_profile_exclude]);
                }
            }
        }else{
            self::$en_is_shipment = 'en_single_shipment';
        }

        // Eniture debug mode
        do_action("eniture_debug_mood", EN_DISTANCE_BASE_SHIPPING_NAME . " Shipment Type ", self::$en_is_shipment);

        return $en_response;

    }

}
