<?php

/**
 * Package array of cart items.
 */

namespace EnDistanceBaseShippingPackage;

use EnDistanceBaseShippingVersionCompact\EnDistanceBaseShippingVersionCompact;
use EnDistanceBaseShippingDistance\EnDistanceBaseShippingDistance;

//use EnDistanceBaseShippingProductDetail\EnDistanceBaseShippingProductDetail;
use EnDistanceBaseShippingReceiverAddress\EnDistanceBaseShippingReceiverAddress;
use EnDistanceBaseShippingDB\EnDistanceBaseShippingDB;
use WC_Shipping_Zones;

/**
 * Get items detail from added product in cart|checkout page.
 * Class EnDistanceBaseShippingPackage
 * @package EnDistanceBaseShippingPackage
 */
class EnDistanceBaseShippingPackage
{

    static public $post_id;
    static public $locations;
    static public $product_key_name;
    static public $origin_zip_code = '';
    static public $shipment_type = '';
    static public $get_minimum_warehouse = '';
    static public $instore_pickup_local_delivery = 0;
    static public $en_step_for_package = [];
    static public $en_request = [];
    static public $receiver_address = [];

    /**
     * Get detail from added product in the cart|checkout page
     * @param array $package
     * @return array
     */
    static public function en_package_converter($package)
    {
        global $wpdb;
        $en_product_fields = [];
        $product_ids_array = [];
        // cart|checkout receiver address
        $receiverZip = $receiverState = $receiverCountryCode = $receiverCity = '';
        self::$receiver_address = EnDistanceBaseShippingReceiverAddress::get_address();
        
        extract(self::$receiver_address);
        $commodity_details = $shipping_classes = $shipping_profiles = [];
        // Shipping rates
        $en_profile_classes = $wpdb->prefix . 'en_profile_classes';
        $en_profiles = $wpdb->prefix . 'en_profiles';
        $en_shipping_origins = $wpdb->prefix . 'en_shipping_origins';
        $en_origin_zones = $wpdb->prefix . 'en_origin_zones';
        $en_zone_rates = $wpdb->prefix . 'en_zone_rates';
        $en_dbsc_rates = $wpdb->prefix . 'en_dbsc_rates';

        // Colums
        $origins = "o.id as originId,o.street_address as addressLine,o.city as city,o.state as state,o.postal_code as zipCode,o.country_code as country,o.origin_order as origin_order";
        $origin_zone = "oz.zone_id as zone_id";
        $profile = "p.profile_nickname as nickname,p.id as id";
        $rates = "r.id as rateId,r.display_as as methodName,r.description as description,r.rate as ratePerUnit,r.unit as distanceUnit,r.measured_by as distancedMeasuredBy,r.min_weight as minWeight,r.max_weight as maxWeight,r.rate_condition as weightDimOperator,r.min_length as minLength,r.max_length as maxLength,r.min_distance as minDistance, r.max_distance as maxDistance,r.min_quote as minQuotes,r.max_quote as maxQuotes, r.min_cart_value as minCartValue,r.max_cart_value as maxCartValue,r.cart_value_type as  checkCartValue,r.calculate_for as rateBaseOn, r.distance_adjustment as distanceAdjustment,r.rate_adjustment as rateAdjustment,r.display_preference as displayPreference, r.address_type as addressType,r.default_address_type as defaultAddressType, r.base_amount as baseAmount";
        $all_classess = $wpdb->get_col("SELECT shipping_classes FROM $en_profile_classes WHERE shipping_classes != -1");
        $profile_condition = get_option('en_general_profile_condition');

        foreach ($package['contents'] as $key => $product) {
            if (isset($product['data'])) {

                $product_data = $product['data'];

                $class_tags_exists_resp = self::check_class_tags_exists_in_profile($product_data);
                $shipping_class_id = $class_tags_exists_resp['shipping_class_id'];
                if (empty($all_classess) || ($profile_condition == 'en_for_all_products' && !$class_tags_exists_resp['belongs_to_profile'])) {
                    $en_gp_where_clause = "p.id = 1";
                    $en_gp_query_insertion = "
                    FROM $en_profiles p
                        
                    INNER JOIN $en_shipping_origins o ON p.id = o.profile_id";
                } else {
                    if(empty($class_tags_exists_resp['query_clause'])){
                        $en_gp_where_clause = "pc.shipping_classes = $shipping_class_id";
                    }else{
                        $en_gp_where_clause = $class_tags_exists_resp['query_clause'];
                    }
                    
                    $en_gp_query_insertion = "
                    FROM $en_profile_classes pc
                        
                    INNER JOIN $en_profiles p ON pc.profile_id = p.id
                        
                    INNER JOIN $en_shipping_origins o ON pc.profile_id = o.profile_id";
                }
                $shipping_class = $product_data->get_shipping_class();
                $dimension_unit = strtolower(get_option('woocommerce_dimension_unit'));
                $calculate_dimension = [
                    'ft' => 12,
                    'cm' => 0.3937007874,
                    'mi' => 63360,
                    'km' => 39370.1,
                ];

                switch ($dimension_unit) {
                    case (isset($calculate_dimension[$dimension_unit])):
                        $get_height = round((float) $product_data->get_height() * $calculate_dimension[$dimension_unit], 2);
                        $get_length = round((float) $product_data->get_length() * $calculate_dimension[$dimension_unit], 2);
                        $get_width = round((float) $product_data->get_width() * $calculate_dimension[$dimension_unit], 2);
                        break;
                    default;
                        $get_height = wc_get_dimension($product_data->get_height(), 'in');
                        $get_length = wc_get_dimension($product_data->get_length(), 'in');
                        $get_width = wc_get_dimension($product_data->get_width(), 'in');
                        break;
                }

                $product_item = [
                    'lineItemHeight' => $get_height,
                    'lineItemLength' => $get_length,
                    'lineItemWidth' => $get_width,
                    'lineItemWeight' => wc_get_weight($product_data->get_weight(), 'lbs'),
                    'piecesOfLineItem' => $product['quantity'],
                    'lineItemPrice' => $product_data->get_price(),
                    'lineItemName' => $product_data->get_title(),
                    'lineItemNameAndQty' => $product['quantity'] . " x " . $product_data->get_title()
                ];

                $product_id = $product_data->get_id();

                $product_ids_array[] = $product_id;

                if (!isset($shipping_classes[$shipping_class_id])) {
                    $profile_id = 0;

                    $shipping_joins = "SELECT $rates, $origins, $profile, $origin_zone
                    
                    $en_gp_query_insertion
                        
                    INNER JOIN $en_origin_zones oz ON o.id = oz.en_origin_id
                        
                    INNER JOIN $en_zone_rates zr ON oz.zone_id = zr.zone_id
                        
                    INNER JOIN $en_dbsc_rates r ON zr.dbsc_rate_id = r.id
                    
                    WHERE $en_gp_where_clause";

                    $shipping_rates = $wpdb->get_results($shipping_joins);

                    foreach ($shipping_rates as $key => $shipping_rate) {

                        $shipping_rate = (array)$shipping_rate;
                        $zone_id = $nickname = $id = $originId = $rateId = $methodName = $description = $ratePerUnit = $distanceUnit = $distancedMeasuredBy = $minWeight = $maxWeight = $weightDimOperator = $minLength = $maxLength = $minDistance = $maxDistance = $minQuotes = $maxQuotes = $rateBaseOn = $city = $state = $zipCode = $country = $origin_order = $distanceAdjustment = $rateAdjustment = $displayPreference = '';
                        extract($shipping_rate);
                        // if($zone_id != $selected_zone_id){
                        //     continue;
                        // }
                        $zone = WC_Shipping_Zones::get_zone(absint($zone_id));

                        if (!$zone) {
                            continue;
                        }

                        $location_specified = false;
                        $postal_codes_array = [];
                        foreach ($zone->get_zone_locations() as $location) {
                            if ('country' === $location->type) {
                                $location->code == $receiverCountryCode ? $location_specified = true : '';
                            } elseif ('state' === $location->type) {
                                $location->code == $receiverCountryCode . ':' . $receiverState ? $location_specified = true : '';
                            } elseif ('postcode' === $location->type) {
                                $postcode_locations = [(object)[
                                    'zone_id' => $zone_id,
                                    'location_code' => $location->code,
                                ]];
                                $postal_codes_array[] = $location->code;
                                if ($postcode_locations) {
                                    $zone_ids_with_postcode_rules = array_map('absint', wp_list_pluck($postcode_locations, 'zone_id'));
                                    $matches = wc_postcode_location_matcher(wc_normalize_postcode(wc_clean($receiverZip)), $postcode_locations, 'zone_id', 'location_code', strtoupper(wc_clean($receiverCountryCode)));
                                    $do_not_match = array_unique(array_diff($zone_ids_with_postcode_rules, array_keys($matches)));
                                    !empty($do_not_match) && (strpos($location->code, wc_normalize_postcode(wc_clean($receiverZip))) !== false) ? $do_not_match = [] : '';
                                    empty($do_not_match) ? $location_specified = true : '';
                                }
                            }
                        }

                        if (!$location_specified) {
                            continue;
                        }

                        if(!empty($postal_codes_array) && !in_array($receiverZip, $postal_codes_array)){
                            continue;
                        }

                        // Shipping from
                        $shipping_from = [
                            'addressLine' => $addressLine,
                            'city' => $city,
                            'state' => $state,
                            'zipCode' => $zipCode,
                            'country' => $country,
                        ];

                        $en_weight_dim_operator = 'AND';
                        switch ($weightDimOperator) {
                            case 'or':
                                $en_weight_dim_operator = 'OR';
                                break;
                            default:
                                $en_weight_dim_operator = 'AND';
                                break;
                        }

                        if($addressType == 'commercial'){
                            $addressTypeAPI = 'c';
                        }else if($addressType == 'residential'){
                            $addressTypeAPI = 'r';
                        }else{
                            $addressTypeAPI = 'cr';
                        }
                        $defaultAddressType = ($defaultAddressType == 'residential') ? 'r' : 'c';
                        $rateBaseOn = ($rateBaseOn == 'item_after_quotes') ? "itemAfterQuotes" : $rateBaseOn;
                        // Shipping methods
                        $shipping_methods = [
                            'methodName' => $methodName,
                            'description' => $description,
                            'ratePerUnit' => $ratePerUnit,
                            'baseAmount' => $baseAmount,
                            'distanceUnit' => $distanceUnit, // m, km (m: meter / km: kilometer)
                            'distancedMeasuredBy' => $distancedMeasuredBy, // route, straightline
                            'minWeight' => $minWeight,
                            'maxWeight' => $maxWeight,
                            'weightDimOperator' => $en_weight_dim_operator, // OR , AND
                            'minLength' => $minLength, // (inches)
                            'maxLength' => $maxLength, // (inches)
                            'minDistance' => $minDistance,
                            'maxDistance' => $maxDistance, 
                            'distanceAdjustment' => $distanceAdjustment,
                            'rateAdjustment' => $rateAdjustment, 
                            'minQuotes' => $minQuotes,
                            'maxQuotes' => ($maxQuotes > 0 ? $maxQuotes : NULL), // null or empty for unlimited
                            'minCartValue' => $minCartValue,
                            'maxCartValue' => $maxCartValue,
                            'checkCartValue' => $checkCartValue,
                            'rateBaseOn' => $rateBaseOn, // origin , item, flat, itemAfterQuotes
                            'displayPreference' => $displayPreference,
                            'addressType' => $addressTypeAPI, // c => Commercial, r = Residential, cr => CommercialResidential 
                            'defaultAddressType' => $defaultAddressType // c => commercial or r => residential
                        ];

                        $shipping_profiles[$id]['nickname'] = $nickname;
                        $shipping_profiles[$id]['shippingFrom'][$originId] = $shipping_from;
                        $shipping_profiles[$id]['shippingMethods'][$rateId] = $shipping_methods;
                        
                        $originIds = [
                            $originId => $originId
                        ];
                        $methodIds = [
                            $rateId => $rateId
                        ];

                        $profile_to_origin_merge_id = $id . $origin_order;
                        if (isset($shipping_profiles[$id]['originMethedsGroups'][$profile_to_origin_merge_id])) {
                            $originMethedsGroups = $shipping_profiles[$id]['originMethedsGroups'][$profile_to_origin_merge_id];
                            (isset($originMethedsGroups['originIds'])) ? $originMethedsGroups['originIds'] = ($originMethedsGroups['originIds'] + $originIds) : '';
                            (isset($originMethedsGroups['methodIds'])) ? $originMethedsGroups['methodIds'] = ($originMethedsGroups['methodIds'] + $methodIds) : '';
                            $shipping_profiles[$id]['originMethedsGroups'][$profile_to_origin_merge_id] = $originMethedsGroups;
                        } else {
                            $shipping_profiles[$id]['originMethedsGroups'][$profile_to_origin_merge_id] = [
                                'originIds' => $originIds,
                                'methodIds' => $methodIds
                            ];
                        }
                        !isset($commodity_details[$id][$product_id]) ? $commodity_details[$id][$product_id] = $product_item : '';

                        $profile_id = $id;
                    }

                    if ($profile_id > 0) {
                        $en_classes_table = $wpdb->prefix . 'en_profile_classes';
                        $en_classes_qry = "SELECT shipping_classes FROM $en_classes_table WHERE profile_id = $profile_id";
                        $en_profiles_classes = $wpdb->get_results($en_classes_qry);
                        $en_get_classes = EnDistanceBaseShippingVersionCompact::en_array_column($en_profiles_classes, 'shipping_classes');
                        $shipping_classes = $shipping_classes + array_fill_keys($en_get_classes, $id);
                    }
                } else if (isset($shipping_classes[$shipping_class_id])) {
                    $id = $shipping_classes[$shipping_class_id];
                    !isset($commodity_details[$id][$product_id]) ? $commodity_details[$id][$product_id] = $product_item : '';
                }

            }
        }

        self::$en_request = array_merge(self::$receiver_address);
        self::$en_request['commodityDetails'] = $commodity_details;
        self::$en_request['shippingProfiles'] = $shipping_profiles;
        self::$en_request['licenseKey'] = get_option('en_connection_settings_license_key_distance_base_shipping');
        self::$en_request['autoResidentials'] = '1';

        // Eniture debug mode
        do_action("eniture_debug_mood", EN_DISTANCE_BASE_SHIPPING_NAME . " QuotesRequest ", self::$en_request);

        return self::check_error_management($product_ids_array);
    }

    static public function check_error_management($product_ids_array){

        $not_in_profile_exception = get_option('en_settings_error_management_not_in_profile_distance_base_shipping');
        self::$en_request['dontQuoteIfProfileMissing'] = (!empty($not_in_profile_exception) && $not_in_profile_exception == 'yes') ? 1 : 0;
                
        if(self::$en_request['dontQuoteIfProfileMissing']){

            $commodities_array = self::$en_request['commodityDetails'];
            if(empty($commodities_array)){
                return [];
            }

            $products_for_quotes = [];
            foreach($commodities_array as $ship_id => $commodities){
                foreach($commodities as $id => $commoditie){
                    $products_for_quotes[] = $id;
                }
            }

            foreach($product_ids_array as $key => $id){
                if(!in_array($id,$products_for_quotes )){
                    return [];
                }
            }
        }

        return self::$en_request;
    }

    public static function check_class_tags_exists_in_profile($product_data)
    {
        global $wpdb;
        $response = [];
        $response['belongs_to_profile'] = false;
        $en_profile_classes = $wpdb->prefix . 'en_profile_classes';
        $en_profiles = $wpdb->prefix . 'en_profiles';
        $product_tags = get_the_terms( $product_data->get_id(), 'product_tag' );
        if ( $product_tags ) {
            $product_tag_ids = array_map(function($tag) { return $tag->term_id; }, $product_tags);
            $product_tags_list = implode(',', $product_tag_ids);

            $tag_query = "SELECT id, pc.profile_id
            FROM $en_profile_classes pc
            WHERE pc.shipping_classes IN ($product_tags_list) 
            AND (
                SELECT COUNT(*)
                FROM $en_profiles p
                WHERE p.id = pc.profile_id
                AND p.profile_define_by = 'product_tags'
            ) > 0";

            $tags_result = $wpdb->get_results($tag_query);
            if(!empty($tags_result) && is_array($tags_result) && count($tags_result) > 0){
                foreach($tags_result as $key => $value){
                    if(!empty($value->id)){
                        $response['belongs_to_profile'] = true;
                        $response['query_clause'] = "pc.id = $value->id";
                        $response['shipping_class_id'] = $value->profile_id;
                        return $response;
                    }
                }
                
            }

        }

        $shipping_class_id = $product_data->get_shipping_class_id();
        $response['shipping_class_id'] = $shipping_class_id;
        if(!empty($shipping_class_id)){
            $classes_query = "SELECT id
            FROM $en_profile_classes pc
            WHERE pc.shipping_classes = $shipping_class_id
            AND (
                SELECT COUNT(*)
                FROM $en_profiles p
                WHERE p.id = pc.profile_id
                AND p.profile_define_by = 'shipping_classes'
            ) > 0";

            $classes_result = $wpdb->get_results($classes_query);
            if(!empty($classes_result) && is_array($classes_result) && count($classes_result) > 0){
                foreach($classes_result as $key => $value){
                    if(!empty($value->id)){
                        $response['belongs_to_profile'] = true;
                        $response['query_clause'] = "pc.id = $value->id";
                        return $response;
                    }
                }
                
            }
        }

        return $response;
    }



}
