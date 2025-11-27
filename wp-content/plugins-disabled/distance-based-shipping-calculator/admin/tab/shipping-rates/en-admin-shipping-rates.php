<?php

namespace EnDistanceBaseShippingRatesTemplateSettings;

use EnDistanceBaseShippingVersionCompact\EnDistanceBaseShippingVersionCompact;

class EnDistanceBaseShippingRatesTemplateSettings
{

    /**
     * @param array $shipping_classes
     * @return array $en_profiles
     */
    public function getAllProfiles($shipping_classes = [])
    {
        global $wpdb;
        $en_table = $wpdb->prefix . 'en_profiles';
        $en_profile_classes_table = $wpdb->prefix . 'en_profile_classes';
        $profiles_query = "SELECT * FROM $en_table";
        $en_profiles = $wpdb->get_results($profiles_query);

        if (isset($en_profiles) && !empty($en_profiles)) {
            foreach ($en_profiles as $key => $profile) {
                $profile_id = (isset($profile->id)) ? $profile->id : 0;
                $profiles_classes_query = "SELECT shipping_classes FROM $en_profile_classes_table WHERE profile_id=$profile_id";
                $en_profiles_classes = $wpdb->get_results($profiles_classes_query);
                $en_profiles_classes_arr = EnDistanceBaseShippingVersionCompact::en_array_column($en_profiles_classes, 'shipping_classes');

                $profile->classes_details = (!empty($en_profiles_classes_arr)) ? $this->enGetSelectedClassesDetails($en_profiles_classes_arr, $profile->profile_define_by) : [];
                $profile->origin_details = $this->enGetShippingOrigins($profile_id);
            }
        }

        return $en_profiles;
    }

    /**
     * return selected shipping classes array
     */
    public function enGetSelectedClassesDetails($en_shipping_classes, $profile_defined_by = 'shipping_classes')
    {
        $class_detail = [];
        $count = 0;
        if($profile_defined_by == 'product_tags'){
            $en_woo_shipping_classes = get_tags( array( 'taxonomy' => 'product_tag' ) );
        }else{
            $en_woo_shipping_classes = apply_filters('en_woo_get_all_shipping_classes', []);
        }
        if (isset($en_woo_shipping_classes) && !empty($en_woo_shipping_classes)) {
            foreach ($en_woo_shipping_classes as $key => $class) {
                if (in_array($class->term_taxonomy_id, $en_shipping_classes)) {
                    $class_detail[$count]['term_id'] = $class->term_id;
                    $class_detail[$count]['name'] = $class->name;
                    $class_detail[$count]['slug'] = $class->slug;
                    $class_detail[$count]['term_taxonomy_id'] = $class->term_taxonomy_id;
                    $class_detail[$count]['description'] = $class->description;
                    $count++;
                }
            }
        }
        return $class_detail;
    }

    /**
     * return shipping origin array
     */
    public function enGetShippingOrigins($profile_id)
    {

        global $wpdb;
        $origin_table = $wpdb->prefix . 'en_shipping_origins';
        $o_z_table = $wpdb->prefix . 'en_origin_zones';
        $zone_table = $wpdb->prefix . 'woocommerce_shipping_zones';

        $origins_query = "SELECT * FROM $origin_table WHERE profile_id = " . $profile_id;

        $en_shipping_origins = $wpdb->get_results($origins_query);


        foreach ($en_shipping_origins as $key => $origin) {

            $zones_query = "SELECT wcz.zone_id, wcz.zone_name FROM $zone_table wcz
						WHERE wcz.zone_id IN (SELECT oz.zone_id FROM $o_z_table oz WHERE oz.en_origin_id=$origin->id)";

            $origin->zones_details = $wpdb->get_results($zones_query);

            foreach ($origin->zones_details as $item => $zone) {
                $this->enGetShippingRates($zone);
            }
        }
        return $en_shipping_origins;
    }

    /**
     * return shipping rate methods
     */
    public function enGetShippingRates($zone)
    {
        global $wpdb;
        $en_z_r_table = $wpdb->prefix . 'en_zone_rates';
        $en_rates_table = $wpdb->prefix . 'en_dbsc_rates';

        $query = "SELECT * FROM $en_rates_table
                    WHERE id IN (SELECT dbsc_rate_id FROM `$en_z_r_table` WHERE zone_id = $zone->zone_id)";
        $en_rates = $wpdb->get_results($query);
        $zone->rate_details = $en_rates;
    }

}
