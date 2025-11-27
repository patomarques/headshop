<?php

namespace EnDistanceBaseShippingPopupAjax;

use EnDistanceBaseShippingCurl\EnDistanceBaseShippingCurl;
use EnDistanceBaseShippingVersionCompact\EnDistanceBaseShippingVersionCompact;
use EnDistanceBaseShippingConnectionSettings\EnDistanceBaseShippingConnectionSettings;
use EnDistanceBaseShippingRatesTemplateSettings\EnDistanceBaseShippingRatesTemplateSettings;
use EnDistanceBaseShippingQuoteSettings\EnDistanceBaseShippingQuoteSettings;
use WC_Shipping_Zone;
use WC_Shipping_Zones;

class EnDistanceBaseShippingPopupAjax
{

    public function __construct()
    {
        add_action('wp_ajax_en_update_shipping_profile', array($this, 'en_update_shipping_profile'));
        add_action('wp_ajax_en_add_shipping_class', array($this, 'en_add_shipping_class'));
        add_action('wp_ajax_en_add_shipping_zone', array($this, 'en_add_shipping_zone'));
        add_action('wp_ajax_en_get_shipping_zone', array($this, 'en_get_shipping_zone'));
        add_action('wp_ajax_en_edit_shipping_profile', array($this, 'en_edit_shipping_profile'));
        add_action('wp_ajax_get_available_classes', array($this, 'get_available_classes'));
        add_action('wp_ajax_en_dbsc_delete_record_action', array($this, 'en_dbsc_delete_record_action'));
        add_action('wp_ajax_en_add_shipping_origin', array($this, 'en_add_shipping_origin'));
        add_action('wp_ajax_en_edit_dbsc_shipping_origin', array($this, 'en_edit_shipping_origin'));
        add_action('wp_ajax_en_add_zone_rate', array($this, 'en_add_zone_rate'));
        add_action('wp_ajax_en_get_zone_rate', array($this, 'en_get_zone_rate'));
        // handle the action from subscription plan.
        add_action('wp_ajax_en_woo_addons_upgrade_plan_submit_dbsc', array($this, 'en_woo_addons_upgrade_plan_submit_dbsc'));
        // handle the suspend action from subscription plan.
        add_action('wp_ajax_suspend_automatic_detection_dbsc', array($this, 'suspend_automatic_detection_dbsc'));
    }

    public function suspend_automatic_detection_dbsc()
    {
        if (!(current_user_can('manage_options') || current_user_can('manage_woocommerce')) || !wp_verify_nonce($_POST['wp_nonce'], 'en_dbsc_admin_popup_nonce')) {
            echo wp_json_encode([]);
            return;
        }

        $options = array();
        $en_connection_settings_suspend_distance_base_shipping = (isset($_POST['en_connection_settings_suspend_distance_base_shipping'])) ? sanitize_text_field($_POST['en_connection_settings_suspend_distance_base_shipping']) : '';
        if (isset($en_connection_settings_suspend_distance_base_shipping) && !empty($en_connection_settings_suspend_distance_base_shipping)) {
            $options["en_connection_settings_suspend_distance_base_shipping"] = $en_connection_settings_suspend_distance_base_shipping;
            update_option('en_connection_settings_suspend_distance_base_shipping', $en_connection_settings_suspend_distance_base_shipping);
        }

        echo json_encode($options);
        die();
    }

    /**
     * Auto detect box sizing ajax request.
     */
    public function en_woo_addons_upgrade_plan_submit_dbsc()
    {
        if (!(current_user_can('manage_options') || current_user_can('manage_woocommerce')) || !wp_verify_nonce($_POST['wp_nonce'], 'en_dbsc_admin_popup_nonce')) {
            echo wp_json_encode([]);
            return;
        }

        $selected_plan = $packgInd = (isset($_POST['selected_plan'])) ? sanitize_text_field($_POST['selected_plan']) : '';
        $action = isset($packgInd) && ($packgInd == "disable") ? "d" : "c";
        $status = EnDistanceBaseShippingConnectionSettings::smart_street_api_curl_request($action, $selected_plan);

        $status = json_decode($status, true);
        if (isset($status['severity']) && $status['severity'] == "SUCCESS") {

            $status = EnDistanceBaseShippingConnectionSettings::en_dbsc_subscription($status);
            $status['severity'] = "SUCCESS";
        }
        echo json_encode($status);
        die();
    }

    public function en_add_shipping_zone()
    {
        if (!(current_user_can('manage_options') || current_user_can('manage_woocommerce')) || !wp_verify_nonce($_POST['wp_nonce'], 'en_dbsc_admin_popup_nonce')) {
            echo wp_json_encode([]);
            return;
        }

        $form_data = isset($_POST['form_data']) ? $_POST['form_data'] : [];
        if (!empty($form_data)) {
            global $wpdb;
            $append = '';
            $action = (isset($form_data['en_zone_action'])) ? wc_clean(wp_unslash($form_data['en_zone_action'])) : '';
            $origin_id = (isset($form_data['origin_id'])) ? intval(wc_clean($form_data['origin_id'])) : 0;
            $profile_id = (isset($form_data['profile_id'])) ? intval(wc_clean($form_data['profile_id'])) : '';
            $origin_order = (isset($form_data['origin_order'])) ? wc_clean(wp_unslash($form_data['origin_order'])) : '';
            $zone_id = wc_clean($form_data['zone_id']);
            $zone_id = $zone_id == 'new' ? $zone_id : intval($zone_id);
            $zone = new WC_Shipping_Zone($zone_id);
            $changes = wp_unslash($form_data);

            $postcode_locations_trim = trim(strtoupper(str_replace(chr(226) . chr(128) . chr(166), '...', $changes['zip_codes'])));
            $split_postcode_locations = preg_split('/[\ \n\,]+/', $postcode_locations_trim);

            if (isset($changes['name'])) {
                $append = '<b>' . wc_clean($changes["name"]) . '</b><br>' . wc_clean($changes["name"]);
                $zone->set_zone_name(wc_clean($changes['name']));
            }

            $en_shipping_origins = $wpdb->prefix . 'en_shipping_origins';
            $en_origin_zones = $wpdb->prefix . 'en_origin_zones';

            $zone_ids = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT oz.zone_id as zone_id FROM $en_origin_zones oz WHERE oz.en_origin_id = %d",
                    $origin_id
                )
            );
            if (!empty($zone_ids)) {
                $valid_region = TRUE;
                $en_define_by = (!empty($changes['zip_codes'])) ? 'by_postal_code' : 'other_regions';
                foreach ($zone_ids as $zone_key => $zone_value) {
                    $zones_data = WC_Shipping_Zones::get_zone(absint($zone_value->zone_id));
                    if ($zones_data) {
                        $get_zone_locations = $zones_data->get_zone_locations();
                        foreach ($get_zone_locations as $zone_locations_key => $zone_location) {
                            if ($en_define_by == 'other_regions') {
                                $region_code = (isset($zone_location->code)) ? $zone_location->code : '';
                                $region_type = (isset($zone_location->type)) ? $zone_location->type : '';
                                $arrange_region = $region_type . ':' . $region_code;
                                in_array($arrange_region, $changes['locations']) && ($action == 'add_zone' || ($action != 'add_zone' && $zone_id != $zone_value->zone_id)) ? $valid_region = FALSE : '';
                            } elseif ($en_define_by == 'by_postal_code') {
                                $explode_locations = explode(':', $changes['locations'][0]);
                                if ('postcode' === $zone_location->type) {
                                    foreach ($split_postcode_locations as $key => $split_postcode) {
                                        $postcode_locations = [(object)[
                                            'zone_id' => $zone_value->zone_id,
                                            'location_code' => $zone_location->code,
                                        ]];
                                        if ($postcode_locations) {
                                            $zone_ids_with_postcode_rules = array_map('absint', wp_list_pluck($postcode_locations, 'zone_id'));
                                            $matches = wc_postcode_location_matcher(wc_normalize_postcode(wc_clean($split_postcode)), $postcode_locations, 'zone_id', 'location_code', strtoupper(wc_clean($explode_locations[1])));
                                            $do_not_match = array_unique(array_diff($zone_ids_with_postcode_rules, array_keys($matches)));
                                            !empty($do_not_match) && (strpos($zone_location->code, wc_normalize_postcode(wc_clean($split_postcode))) !== false) ? $do_not_match = [] : '';
                                            empty($do_not_match) && ($action == 'add_zone' || ($action != 'add_zone' && $zone_id != $zone_value->zone_id)) ? $valid_region = FALSE : '';
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                if (!$valid_region) {
                    echo wp_json_encode(
                        array(
                            'message' => 'error',
                            'message_to_show' => 'Some of the region already exists in same “Shipping From” profile.',
                            'zone_id' => $zone_id,
                            'append' => '',
                        )
                    );
                    exit;
                }
            }

            if (isset($changes['locations'])) {
                $zone->clear_locations(array('state', 'country', 'continent'));
                $locations = array_filter(array_map('wc_clean', (array)$changes['locations']));
                foreach ($locations as $location) {
                    // Each posted location will be in the format type:code.
                    $location_parts = explode(':', $location);
                    switch ($location_parts[0]) {
                        case 'state':
                            $zone->add_location($location_parts[1] . ':' . $location_parts[2], 'state');
                            break;
                        case 'country':
                            $zone->add_location($location_parts[1], 'country');
                            break;
                        case 'continent':
                            $zone->add_location($location_parts[1], 'continent');
                            break;
                    }
                }
            }

            if (isset($changes['zip_codes'])) {
                $zone->clear_locations('postcode');
                $postcodes = array_filter(array_map('strtoupper', array_map('wc_clean', explode("\n", $changes['zip_codes']))));
                foreach ($postcodes as $postcode) {
                    $zone->add_location($postcode, 'postcode');
                }
            }

            if ($action == 'add_zone') {
                $zone->add_shipping_method('distance_base_shipping');
            }
            $zone->save();
            $get_formatted_location = $zone->get_formatted_location();
            if ($action == 'add_zone') {
                $en_oz_table = $wpdb->prefix . 'en_origin_zones';
                $zone_id = $zone->get_id();
                $zone_name = $zone->get_zone_name();

                $en_shipping_origins = $wpdb->prefix . 'en_shipping_origins';
                $en_shipping_origin_zones = $wpdb->get_results(
                    $wpdb->prepare(
                        "SELECT id FROM $en_shipping_origins WHERE profile_id = %d AND origin_order = %s", 
                        $profile_id, 
                        $origin_order
                    )
                );

                foreach ($en_shipping_origin_zones as $en_shipping_origin_zones_id => $en_shipping_origin_zones_value) {
                    $wpdb->insert($en_oz_table, ['en_origin_id' => $en_shipping_origin_zones_value->id, 'zone_id' => $zone_id]);
                }

                $append = "<div class='en-zone-section en_zone_details$zone_id'>";
                $append .= "<div class='en-zone-header full-width'>";
                $append .= "<div class='zone_left'>";
                $append .= "<b>$zone_name</b></br>$get_formatted_location";
                $append .= "</div>";
                $append .= "<div class='zone_right' style='float: right'>";
                $append .= "<span>";
                $append .= "<a href='javascript:;' class='en-menu-popup' onclick='en_toggle_menu(this)'>&hellip;</a>";
                $append .= "</span>";
                $append .= "<div class='en-menu-options'>";
                $append .= "<ul>";
                $append .= "<li>";
                $append .= "<a href='javascript:;' onclick='en_dbsc_edit_shipping_zone($profile_id,$origin_id,$zone_id)'>Edit</a>";
                $append .= "</li>";
                $append .= "<li>";
                $append .= "<a href='javascript:;' onclick='en_dbsc_delete_record($profile_id,$origin_id,$zone_id,\"zone\")'>Delete</a>";
                $append .= "</li>";
                $append .= "</ul>";
                $append .= "</div>";
                $append .= "</div>";
                $append .= "</div>";
                $append .= "<div class='en_shipping_to_table_container full-width'>";
                $append .= "<table class='en_shipping_rate' id='en_shipping_rate'>";
                $append .= "<tr>";
                $append .= "<th>Display as</th>";
                $append .= "<th class='en-align-center'>Rate</th>";
                $append .= "<th class='en-align-center'>Distance measured by</th>";
                $append .= "<th class='en-align-center'>Distance</th>";
                $append .= "<th class='en-align-center'>Weight</th>";
                $append .= "<th class='en-align-center'>And </br>/ Or</th>";
                $append .= "<th class='en-align-center'>Length</th>";
                $append .= "<th class='en-align-center'>Quote</th>";
                $append .= "<th class='en-align-center'>Action</th>";
                $append .= "</tr>";
                $append .= "</table>";
                $append .= "</div>";
                $append .= "<a href='javascript:;' class='button-primary' title='Add Rate' onclick='en_dbsc_add_rate($profile_id,$zone_id)'>Add rate</a>";
                $append .= "</div>";
                $message = 'Shipping zone has been added successfully.';
            } else if ($action == 'edit_zone') {
                $message = 'Shipping zone has been updated successfully.';
                $get_zone_name = $zone->get_zone_name();
                $append = '<b>' . $get_zone_name . '</b><br>' . $get_formatted_location;
            }

            echo wp_json_encode(
                array(
                    'message' => 'success',
                    'message_to_show' => $message,
                    'zone_id' => $zone_id,
                    'profile_id' => $profile_id,
                    'append' => $append,
                )
            );
        }


        exit;
    }

    public function en_get_shipping_zone()
    {
        if (!(current_user_can('manage_options') || current_user_can('manage_woocommerce')) || !wp_verify_nonce($_POST['wp_nonce'], 'en_dbsc_admin_popup_nonce')) {
            echo wp_json_encode([]);
            return;
        }

        $zone_id = isset($_POST['zone_id']) ? intval(wc_clean($_POST['zone_id'])) : 0;
        if ($zone_id) {
            $zone = WC_Shipping_Zones::get_zone(absint($zone_id));
            if (!$zone) {
                wp_die(esc_html__('Zone does not exist!', 'woocommerce'));
            }
            $allowed_countries = WC()->countries->get_shipping_countries();
            $shipping_continents = WC()->countries->get_shipping_continents();

            // Prepare locations.
            $locations = array();
            $postcodes = array();

            foreach ($zone->get_zone_locations() as $location) {
                if ('postcode' === $location->type) {
                    $postcodes[] = $location->code;
                } else {
                    $locations[] = $location->type . ':' . $location->code;
                }
            }

            $define_by = !empty($postcodes) ? 'by_postal_code' : 'by_country';
            $name = $zone->get_zone_name();

            echo wp_json_encode([
                'message' => 'success',
                'name' => $name,
                'define_by' => $define_by,
                'locations' => $locations,
                'zip_codes' => $postcodes
            ]);
            exit;
        }
    }

    public function en_add_shipping_class()
    {
        if (!(current_user_can('manage_options') || current_user_can('manage_woocommerce')) || !wp_verify_nonce($_POST['wp_nonce'], 'en_dbsc_admin_popup_nonce')) {
            echo wp_json_encode([]);
            return;
        }

        parse_str($_POST['form_data'], $data);

        $update_args = array();

        if (isset($data['en_dbsc_shipping_profile_shipping_class_name'])) {
            $update_args['name'] = wc_clean($data['en_dbsc_shipping_profile_shipping_class_name']);
        }

        if (isset($data['en_dbsc_shipping_profile_shipping_class_slug'])) {
            $update_args['slug'] = wc_clean($data['en_dbsc_shipping_profile_shipping_class_slug']);
        }

        if (isset($data['en_dbsc_shipping_profile_shipping_class_description'])) {
            $update_args['description'] = wc_clean($data['en_dbsc_shipping_profile_shipping_class_description']);
        }

        $is_exist = term_exists($update_args['slug'], 'product_shipping_class');

        if (is_null($is_exist)) {
            $inserted_term = wp_insert_term($update_args['name'], 'product_shipping_class', $update_args);
            $term_id = is_wp_error($inserted_term) ? 0 : $inserted_term['term_id'];

            if ($term_id > 0) {
                $append = "<option value='" . esc_attr($term_id) . "'>" . esc_html($update_args['name']) . "</option>";
            }
            echo wp_json_encode([
                'response' => 'success',
                'append_options' => $append,
            ]);
        } else {
            echo wp_json_encode([
                'response' => 'error',
                'message' => 'Shipping class is already exist.',
            ]);
        }
        exit;
    }

    /**
     * Update shipping profile
     */
    public function en_update_shipping_profile()
    {
        if (!(current_user_can('manage_options') || current_user_can('manage_woocommerce')) || !wp_verify_nonce($_POST['wp_nonce'], 'en_dbsc_admin_popup_nonce')) {
            echo wp_json_encode([]);
            return;
        }

        global $wpdb;

        $en_table = $wpdb->prefix . 'en_profiles';
        $en_profile_classes_table = $wpdb->prefix . 'en_profile_classes';
        $profile_data = [];
        $success_message = '';
        $en_add_new_profile_html = '';
        $en_classes_html = '';
        if (isset($_POST['form_data']) && !empty($_POST['form_data'])) {

            $en_selected_classes = (isset($_POST['selected_classes']) && !empty($_POST['selected_classes'])) ? $_POST['selected_classes'] : '';

            parse_str($_POST['form_data'], $form_data);

            $profile_name = (isset($form_data['en_dbsc_shipping_profile_nickname']) && !empty($form_data['en_dbsc_shipping_profile_nickname'])) ? sanitize_text_field($form_data['en_dbsc_shipping_profile_nickname']) : '';
            $class_name = (isset($form_data['en_dbsc_shipping_profile_shipping_class_name']) && !empty($form_data['en_dbsc_shipping_profile_shipping_class_name'])) ? sanitize_text_field($form_data['en_dbsc_shipping_profile_shipping_class_name']) : '';
            $class_slug = (isset($form_data['en_dbsc_shipping_profile_shipping_class_slug']) && !empty($form_data['en_dbsc_shipping_profile_shipping_class_slug'])) ? sanitize_text_field($form_data['en_dbsc_shipping_profile_shipping_class_slug']) : '';
            $class_description = (isset($form_data['en_dbsc_shipping_profile_shipping_class_description']) && !empty($form_data['en_dbsc_shipping_profile_shipping_class_description'])) ? sanitize_text_field($form_data['en_dbsc_shipping_profile_shipping_class_description']) : '';
            $en_profile_action = (isset($form_data['en_profile_action']) && !empty($form_data['en_profile_action'])) ? sanitize_text_field($form_data['en_profile_action']) : '';
            $en_profile_id = (isset($form_data['en_profile_id']) && !empty($form_data['en_profile_id'])) ? intval(sanitize_text_field($form_data['en_profile_id'])) : '';
            $en_general_profile_condition = (isset($form_data['en_general_profile_condition']) && !empty($form_data['en_general_profile_condition'])) ? sanitize_text_field($form_data['en_general_profile_condition']) : 'en_for_all_products';
            $en_dbsc_profile_define_by = (isset($form_data['en_dbsc_profile_define_by']) && !empty($form_data['en_dbsc_profile_define_by'])) ? sanitize_text_field($form_data['en_dbsc_profile_define_by']) : 'shipping_classes';

            $en_profile_exist = $wpdb->get_row(
                $wpdb->prepare(
                    "select * from $en_table where profile_nickname = %s",
                    $profile_name
                )
            );

            if (!empty($en_profile_exist) && ($en_profile_action == 'add_profile' || ($en_profile_action != 'add_profile' && $en_profile_id != $en_profile_exist->id))) {
                echo wp_json_encode([
                    'response' => 'error',
                    'message' => 'Shipping profile is already exist.',
                ]);
                exit;
            }

            if (empty($en_selected_classes) && strlen($class_slug) > 0 && $en_dbsc_profile_define_by == 'shipping_classes') {
                // insert shipping class
                $shipping_class_taxonomy = wp_insert_term(
                    $class_name, 'product_shipping_class', array(
                        'description' => $class_description,
                        'slug' => $class_slug
                    )
                );
                $profile_data = [
                    'profile_nickname' => $profile_name,
                    'profile_define_by' => $en_dbsc_profile_define_by
                ];
                $shipping_classes = explode(',', $shipping_class_taxonomy['term_taxonomy_id']);
            } else {
                $profile_data = [
                    'profile_nickname' => $profile_name,
                    'profile_define_by' => $en_dbsc_profile_define_by
                ];
                $shipping_classes = $en_selected_classes;
            }

            $shipping_classes_arr = (!empty($shipping_classes)) ? $shipping_classes : [];

            if ($en_profile_action == 'add_profile') {
                //  insert profile
                $wpdb->insert($en_table, $profile_data);

                $en_profile_id = $wpdb->insert_id;

                // shipping profile classes 
                foreach ($shipping_classes_arr as $sc_key => $sc_data) {
                    $profile_classes_data = [
                        'profile_id' => $en_profile_id,
                        'shipping_classes' => trim($sc_data),
                    ];
                    $wpdb->insert($en_profile_classes_table, $profile_classes_data);
                }

                $success_message = 'Profile has been created successfully.';
                $en_add_new_profile_html = $this->en_add_new_profile_template($shipping_classes_arr, $en_profile_id, $profile_name, $profile_classes_data, $en_dbsc_profile_define_by);
            } else {

                if ($en_profile_id == 1) {

                    $profile_data = [
                        'profile_nickname' => 'General Profile',
                        'profile_define_by' => $en_dbsc_profile_define_by
                    ];

                    update_option('en_general_profile_condition', $en_general_profile_condition);
                }

                //  update profile
                $wpdb->update($en_table, $profile_data, array('id' => $en_profile_id));

                // shipping profile classes 
                $wpdb->delete($en_profile_classes_table, array('profile_id' => $en_profile_id));

                foreach ($shipping_classes_arr as $sc_key => $sc_data) {
                    $profile_classes_data = [
                        'profile_id' => $en_profile_id,
                        'shipping_classes' => trim($sc_data),
                    ];
                    $wpdb->insert($en_profile_classes_table, $profile_classes_data);
                }

                $success_message = 'Profile has been updated successfully.';
                $en_classes_html = $this->en_classes_html($shipping_classes_arr, $en_dbsc_profile_define_by);
            }
            echo wp_json_encode([
                'response' => 'success',
                'message' => $success_message,
                'en_profile_action' => $en_profile_action,
                'append_html' => $en_add_new_profile_html,
                'append_classes' => $en_classes_html,
                'params' => [
                    'profile_id' => $en_profile_id,
                    'profile_name' => $profile_name,
                    'profile_define_by' => $en_dbsc_profile_define_by,
                    'class_name' => $class_name,
                    'class_slug' => $class_slug
                ],
            ]);

            exit;
        }
    }

    /**
     * Edit shipping profile
     */
    public function en_edit_shipping_profile()
    {
        if (!(current_user_can('manage_options') || current_user_can('manage_woocommerce')) || !wp_verify_nonce($_POST['wp_nonce'], 'en_dbsc_admin_popup_nonce')) {
            echo wp_json_encode([]);
            return;
        }

        global $wpdb;
        $en_classes_details = [];
        $en_selected_classes = [];
        $en_selected_classes_temp = '';
        $profile_id = (isset($_POST['profile_id']) && !empty($_POST['profile_id'])) ? intval(sanitize_text_field($_POST['profile_id'])) : 0;

        $en_table = $wpdb->prefix . 'en_profiles';
        $en_profile = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $en_table WHERE id = %d", $profile_id)
        );

        $en_profile_classes_table = $wpdb->prefix . 'en_profile_classes';
        $en_profiles_classes = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT shipping_classes FROM $en_profile_classes_table WHERE profile_id = %d",
                $profile_id
            )
        );
        $en_profiles_classes_arr = EnDistanceBaseShippingVersionCompact::en_array_column($en_profiles_classes, 'shipping_classes');
        $enDBSCRatesTemplateObj = new EnDistanceBaseShippingRatesTemplateSettings();

        if(isset($en_profile->profile_define_by) && $en_profile->profile_define_by == 'product_tags'){
            $wc_shipping_classes = get_tags( array( 'taxonomy' => 'product_tag' ) );
        }else{
            $wc_shipping_classes = apply_filters('en_woo_get_all_shipping_classes', []);
        }

        $all_classess = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT shipping_classes FROM `$en_profile_classes_table` WHERE shipping_classes != %d",
                -1
            )
        );

        if (isset($en_profile) && !empty($en_profile)) {
            $en_classes_details = (isset($en_profiles_classes_arr) && !empty($en_profiles_classes_arr)) ?
                $enDBSCRatesTemplateObj->enGetSelectedClassesDetails($en_profiles_classes_arr, $en_profile->profile_define_by) : [];
            if (isset($en_classes_details) && !empty($en_classes_details)) {
                foreach ($en_classes_details as $key => $class) {
                    $en_selected_classes[] = $class['term_taxonomy_id'];
                    $en_selected_classes_temp .= "<option selected='selected' class='en_selected_classes_temp' value='" . esc_attr($class['term_taxonomy_id']) . "'>" . esc_html($class['name']) . "</option>";
                }
                $en_profile->shipping_classes = $en_selected_classes;
            }

            if (isset($wc_shipping_classes) && !empty($wc_shipping_classes)) {
                foreach ($wc_shipping_classes as $key => $class) {
                    if (!in_array($class->term_id, $all_classess)) {
                        $en_selected_classes_temp .= "<option value='" . esc_attr($class->term_taxonomy_id) . "'>" . esc_html($class->name) . "</option>";
                    }
                }
            }

            $en_profile->en_selected_classes_temp = $en_selected_classes_temp;
        }

        if ($en_profile) {
            echo wp_json_encode([
                'response' => 'success',
                'en_profile' => $en_profile,
                'en_general_profile_condition' => get_option('en_general_profile_condition'),
            ]);
        }
        exit;
    }

    public function get_available_classes()
    {
        if (!(current_user_can('manage_options') || current_user_can('manage_woocommerce')) || !wp_verify_nonce($_POST['wp_nonce'], 'en_dbsc_admin_popup_nonce')) {
            echo wp_json_encode([]);
            return;
        }

        global $wpdb;
        $en_selected_classes_temp = $en_selected_tags_temp = '';
        $wc_shipping_classes = apply_filters('en_woo_get_all_shipping_classes', []);
        $products_tags = get_tags( array( 'taxonomy' => 'product_tag' ) );
        $en_profile_classes_table = $wpdb->prefix . 'en_profile_classes';
        $all_classess = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT shipping_classes FROM `$en_profile_classes_table` WHERE shipping_classes != %d",
                -1
            )
        );
        if (isset($wc_shipping_classes) && !empty($wc_shipping_classes)) {
            foreach ($wc_shipping_classes as $key => $class) {
                if (!in_array($class->term_id, $all_classess)) {
                    $en_selected_classes_temp .= "<option value='" . esc_attr($class->term_taxonomy_id) . "'>" . esc_html($class->name) . "</option>";
                }
            }
        }

        if (isset($products_tags) && !empty($products_tags)) {
            foreach ($products_tags as $key => $class) {
                if (!in_array($class->term_id, $all_classess)) {
                    $en_selected_tags_temp .= "<option value='" . esc_attr($class->term_taxonomy_id) . "'>" . esc_html($class->name) . "</option>";
                }
            }
        }

        echo wp_json_encode([
            'response' => 'success',
            'en_selected_classes_temp' => $en_selected_classes_temp,
            'en_selected_tags_temp' => $en_selected_tags_temp
        ]);
        exit;
    }

    /**
     * return add new profile html
     */
    public function en_add_new_profile_template($en_selected_classes, $en_profile_id, $profile_name, $profile_classes_data, $profile_define_by = 'shipping_classes')
    {
        if (!class_exists('EnDistanceBaseShippingQuoteSettings')) {
            require_once EN_DISTANCE_BASE_SHIPPING_MAIN_DIR . '/admin/tab/shipping-rates/en-shipping-rates-template.php';
        }
        $settings = new EnDistanceBaseShippingQuoteSettings();
        $profile = (object)$profile_classes_data;

        $enDBSCRatesTemplateObj = new EnDistanceBaseShippingRatesTemplateSettings();
        $en_classes_details = (isset($en_selected_classes) && !empty($en_selected_classes)) ? $enDBSCRatesTemplateObj->enGetSelectedClassesDetails($en_selected_classes, $profile_define_by) : [];
        $profile->classes_details = $en_classes_details;
        $this->profile_id = $profile->profile_id;
        ob_start();
        ?>
        <div class="en_distance_base_shipping_quote_error-<?php echo $this->profile_id; ?>" style="display: block;">
        </div>

        <div class="en_shipping_profile en_edit_profile" id="edit-profile-id-<?php echo $this->profile_id; ?>">
            <div class="en_shipping_profile_content">
                <div class="en-align-center en-dbsc-menu"
                     id="en-edit-profile-heading-<?php echo $this->profile_id; ?>">
                    <span>
                        <a href="javascript:;" class="en-menu-popup" onclick="en_toggle_menu(this)">&hellip;</a>
                    </span>
                    <div class="en-menu-options">
                        <ul>
                            <li>
                                <a href="javascript:;" onclick="en_dbsc_edit_profile(<?php echo $this->profile_id; ?>)">Edit</a>
                            </li>
                            <li>
                                <a href="javascript:;"
                                   onclick="en_dbsc_delete_record(<?php echo "0,0," . $this->profile_id . ",'profile'"; ?>)">Delete</a>
                            </li>
                        </ul>
                    </div>
                </div>
                <h2><?php echo $profile_name; ?></h2>
                <?php $settings->getShippingClassesHtml($profile) ?>
            </div>
        </div>

        <div class="en_append_new_profile"></div>

        <?php
        $html = ob_get_clean();
        return $html;

        $profile_type = "'other'";
        $profile_current = "'profile'";
        $origin_id = "''";
        $enDBSCRatesTemplateObj = new EnDistanceBaseShippingRatesTemplateSettings();
        $en_classes_details = (isset($en_selected_classes) && !empty($en_selected_classes)) ?
            $enDBSCRatesTemplateObj->enGetSelectedClassesDetails($en_selected_classes, $profile_define_by) : [];
        $html = '<div class="en_distance_base_shipping_quote_error-' . $en_profile_id . '" style="display: block;"></div>';
        $html .= '<div class="en_shipping_profile en_edit_profile" id="edit-profile-id-' . $en_profile_id . '">';
        $html .= '<div class="en_shipping_profile_content">';
        $html .= '<div class="en-align-center en-dbsc-menu" id="en-edit-profile-heading-' . $en_profile_id . '">';
        $html .= '<span>';
        $html .= '<a href="javascript:;" class="en-menu-popup" onclick="en_toggle_menu(this)">&hellip;</a>';
        $html .= '</span>';
        $html .= '<div class="en-menu-options">';
        $html .= '<ul>';
        $html .= '<li><a href="javascript:;" onclick="en_dbsc_edit_profile(' . $en_profile_id . ')">Edit</a></li>';
        $html .= '<li><a href="javascript:;"  onclick="en_dbsc_delete_record(0, 0, ' . $en_profile_id . ', \'profile\')">Delete</a></li>';
        $html .= '</ul>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '<h2>' . $profile_name . '</h2>';
        $html .= '<div class="en_profile_classes" id="en-edit-profile-classes-' . $en_profile_id . '">';

        if (isset($en_classes_details) && !empty($en_classes_details) && is_array($en_classes_details)) {
            $names_arr = array_column($en_classes_details, 'name');
            $html .= '<p>' . implode(', ', $names_arr) . '</p>';
        }

        $html .= '</div>';
        $html .= '</div>';

        $html .= '<div class="en_shipping_from en_no_ship_location">';
        $html .= '<h2>Shipping from</h2>';
        $html .= '<span class="en-add-shipping-origin">';
        $html .= '<a href="#en_dbsc_add_shipping_origin" onclick="en_dbsc_add_shipping_origin(' . $en_profile_id . ',0,1)" title="Add Shipping Origin">Add shipping origin</a></span>';
        $html .= '<p>No shipping location defined</p>';
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    /**
     * return classes html
     *
     * @param $en_selected_classes
     *
     * @return string
     */
    public function en_classes_html($en_selected_classes, $profile_define_by = 'shipping_classes')
    {
        $html = '';
        $enDBSCRatesTemplateObj = new EnDistanceBaseShippingRatesTemplateSettings();
        $en_classes_details = (isset($en_selected_classes) && !empty($en_selected_classes)) ?
            $enDBSCRatesTemplateObj->enGetSelectedClassesDetails($en_selected_classes, $profile_define_by) : [];

        if (isset($en_classes_details) && !empty($en_classes_details) && is_array($en_classes_details)) {
            $names_arr = array_column($en_classes_details, 'name');
            $html .= '<p>' . implode(', ', $names_arr) . '</p>';
        }

        return $html;
    }

    /**
     * Create Shipping origin
     *
     */
    public function en_add_shipping_origin()
    {
        if (!(current_user_can('manage_options') || current_user_can('manage_woocommerce')) || !wp_verify_nonce($_POST['wp_nonce'], 'en_dbsc_admin_popup_nonce')) {
            echo wp_json_encode([]);
            return;
        }

        global $wpdb;
        $en_table = $wpdb->prefix . 'en_shipping_origins';
        $shipping_origin_data = [];
        $append_html = '';

        if (isset($_POST['form_data']) && !empty($_POST['form_data'])) {

            $confirm_address = (isset($_POST['confirmAddress']) && !empty($_POST['confirmAddress'])) ? $_POST['confirmAddress'] : 'YES';

            parse_str($_POST['form_data'], $form_data);

            $nickname = (isset($form_data['en_dbsc_shipping_origin_nickname']) && !empty($form_data['en_dbsc_shipping_origin_nickname'])) ? sanitize_text_field($form_data['en_dbsc_shipping_origin_nickname']) : '';

            $street_address = (isset($form_data['en_dbsc_shipping_origin_street_address']) && !empty($form_data['en_dbsc_shipping_origin_street_address'])) ? sanitize_text_field($form_data['en_dbsc_shipping_origin_street_address']) : '';
            $city = (isset($form_data['en_dbsc_shipping_origin_city']) && !empty($form_data['en_dbsc_shipping_origin_city'])) ? sanitize_text_field($form_data['en_dbsc_shipping_origin_city']) : '';

            $state = (isset($form_data['en_dbsc_shipping_origin_state']) && !empty($form_data['en_dbsc_shipping_origin_state'])) ? sanitize_text_field($form_data['en_dbsc_shipping_origin_state']) : '';

            $postal_code = (isset($form_data['en_dbsc_shipping_origin_postal_code']) && !empty($form_data['en_dbsc_shipping_origin_postal_code'])) ? sanitize_text_field($form_data['en_dbsc_shipping_origin_postal_code']) : '';

            $country_code = (isset($form_data['en_dbsc_shipping_origin_country_code']) && !empty($form_data['en_dbsc_shipping_origin_country_code'])) ? sanitize_text_field($form_data['en_dbsc_shipping_origin_country_code']) : '';

            $availability = (isset($form_data['en_dbsc_shipping_origin_available_in_other']) && !empty($form_data['en_dbsc_shipping_origin_available_in_other'])) ? sanitize_text_field($form_data['en_dbsc_shipping_origin_available_in_other']) : '';

            $en_shipping_origin_action = (isset($form_data['en_shipping_origin_action']) && !empty($form_data['en_shipping_origin_action'])) ? sanitize_text_field($form_data['en_shipping_origin_action']) : '';

            $en_shipping_origin_id = (isset($form_data['en_shipping_origin_id']) && !empty($form_data['en_shipping_origin_id'])) ? intval(sanitize_text_field($form_data['en_shipping_origin_id'])) : '';

            $profile_id = (isset($form_data['en_origin_profile_id']) && !empty($form_data['en_origin_profile_id'])) ? intval(sanitize_text_field($form_data['en_origin_profile_id'])) : '';

            // New change
            $en_add_the_shipping_origin_id = (isset($form_data['en_add_the_shipping_origin_id']) && !empty($form_data['en_add_the_shipping_origin_id'])) ? intval(sanitize_text_field($form_data['en_add_the_shipping_origin_id'])) : '';
            $en_add_the_shipping_origin = (isset($form_data['en_add_the_shipping_origin']) && !empty($form_data['en_add_the_shipping_origin'])) ? sanitize_text_field($form_data['en_add_the_shipping_origin']) : 'en_to_this_shipping_from_profile';
            $en_origin_order = (isset($form_data['en_origin_order']) && $form_data['en_origin_order'] > 0) ? sanitize_text_field($form_data['en_origin_order']) : 1;
            if ($en_shipping_origin_action != 'add_shipping_origin' || !$en_add_the_shipping_origin_id > 0) {
                $en_add_the_shipping_origin = 'en_to_this_shipping_from_profile';
            }

            $en_shipping_suggestion_flag = (isset($form_data['en_shipping_suggestion_flag']) && !empty($form_data['en_shipping_suggestion_flag'])) ? sanitize_text_field($form_data['en_shipping_suggestion_flag']) : '';

            if ($en_add_the_shipping_origin_id > 0 && $en_add_the_shipping_origin == 'en_as_a_new_shipping_from_profile') {
                $max_origins_order = $wpdb->get_results(
                    $wpdb->prepare(
                        "SELECT origin_order FROM $en_table WHERE profile_id =  %d ORDER BY origin_order DESC LIMIT 1",
                        $profile_id
                    )
                );
                if (!empty($max_origins_order)) {
                    $max_origins_order = reset($max_origins_order);
                    $en_origin_order = (isset($max_origins_order->origin_order) && $max_origins_order->origin_order > 0) ? $max_origins_order->origin_order + 1 : 1;
                }
            }

            $shipping_origin_data = [
                'nickname' => $nickname,
                'street_address' => $street_address,
                'city' => $city,
                'state' => $state,
                'postal_code' => $postal_code,
                'country_code' => $country_code,
                'availability' => $availability,
                'profile_id' => $profile_id,
                'origin_order' => $en_origin_order,
            ];
        }

        if ($confirm_address == 'YES') {
            // Validate address
            $plugin_license_key = get_option('en_connection_settings_license_key_distance_base_shipping');
            $post_data = [
                'licenseKey' => $plugin_license_key,
                'serverName' => EN_DISTANCE_BASE_SHIPPING_SERVER_NAME,
                'platform' => 'WordPress',
                'plugin' => 'distanceBasedShippingCalculator',
                'request' => 'addressValidation',
                'requestVersion' => '1.0',
                'requestKey' => time(),
                'action' => 'va', // va: Validate Address, s: status ...
                'region' => 'US', //
                'address' => [
                    'addressLine1' => $street_address,
                    'addressLine2' => '',
                    'city' => $city,
                    'state' => $state,
                    'zipcode' => $postal_code,
                    'country' => $country_code
                ],
            ];

            if ( $country_code == 'US') {
                $address_validation = EnDistanceBaseShippingCurl::EN_DISTANCE_BASE_SHIPPING_sent_http_request(EN_DISTANCE_BASE_SHIPPING_ADDRESS_VALIDATION_HITTING_URL, $post_data, 'POST');
                $en_decoded_address = json_decode($address_validation, TRUE);

                if (isset($en_decoded_address['severity'], $en_decoded_address['Message']) && $en_decoded_address['severity'] == 'ERROR') {
                    if(!empty($en_decoded_address['Message']) && $en_decoded_address['Message'] == 'Invalid Address'){
                        $en_decoded_address['Message'] = 'Invalid Address: Please verify the address you entered and try again.';
                    }
                    echo wp_json_encode([
                        'response' => 'error',
                        'message' => $en_decoded_address['Message'],
                    ]);
                    exit;
                }
                $en_suggested_address_array = (isset($en_decoded_address['suggestedAddress'])) ? $en_decoded_address['suggestedAddress'] : '';

            }
            $form_address = [
                'addressLine' => $street_address,
                'city' => $city,
                'state' => $state,
                'zipcode' => $postal_code,
                'country' => $country_code];


            $address_is_valid = TRUE;
            $complete_Address = '';
            if ( $country_code == 'US') {

                foreach ($en_suggested_address_array as $key => $value) {
                    (isset($form_address[$key]) && strtolower($value) != strtolower($form_address[$key])) ? $address_is_valid = FALSE : '';
                }

                $complete_Address = $en_suggested_address_array['addressLine'] . ', '. $en_suggested_address_array['city'] . ' ' . $en_suggested_address_array['state'] . ' '. $en_suggested_address_array['zipcode'] . ', ' . $country_code;

                if (!$address_is_valid && strlen($complete_Address) > 0) {
                        echo wp_json_encode([
                            'response' => 'success',
                            'action_type' => $en_shipping_origin_action,
                            'profile_id' => $profile_id,
                            'origin_id' => $en_shipping_origin_id,
                            'suggestion' => $en_suggested_address_array,
                            'address_is_valid' => $address_is_valid,
                            'complete_Address' => $complete_Address,
                            'address_validation' => $address_validation,
                        ]);

                        exit;
                }
            }
        }
        if ($en_shipping_origin_action == 'add_shipping_origin') {

            //  insert shipping origin
            $wpdb->insert($en_table, $shipping_origin_data);
            $en_shipping_origin_id = $wpdb->insert_id;

            // New change
            if ($en_add_the_shipping_origin == 'en_to_this_shipping_from_profile') {
                $o_z_table = $wpdb->prefix . 'en_origin_zones';

                $finded_zone = $wpdb->get_results(
                    $wpdb->prepare(
                        "SELECT * FROM $en_table o INNER JOIN $o_z_table oz ON o.id = oz.en_origin_id WHERE o.profile_id =  %d AND o.origin_order = %d",
                        $profile_id, $en_origin_order
                    )
                );
                $added_zone = [];

                foreach ($finded_zone as $en_shipping_origin_zones_id => $en_shipping_origin_zones_value) {
                    $insert_origin_zone_data = [
                        'en_origin_id' => $en_shipping_origin_id,
                        'zone_id' => $en_shipping_origin_zones_value->zone_id,
                    ];
                    if (!in_array($en_shipping_origin_zones_value->zone_id, $added_zone)) {
                        $wpdb->insert($o_z_table, $insert_origin_zone_data);
                        $added_zone[] = $en_shipping_origin_zones_value->zone_id;
                    }
                }
            }

            if(!empty($shipping_origin_data['availability']) && ($shipping_origin_data['availability'] == 'available_in_warehouse' || $shipping_origin_data['availability'] == 'available_in_dropship')){
                $this->en_dbsc_add_warehouse_dropship($shipping_origin_data);
            }

            $success_message = 'Shipping origin has been added successfully.';
        } elseif ($en_shipping_origin_action == 'update_shipping_origin') {
            //  update shipping origin
            $wpdb->update($en_table, $shipping_origin_data, array('id' => $en_shipping_origin_id, 'profile_id' => $profile_id));
            $success_message = 'Shipping origin has been updated successfully.';
        }

        $current_action = "'shipping_origin'";
        // New change
        $append_html = '<div class="en_shipping_from en-origin-order-' . $en_origin_order . '">';
        $append_html .= '<span class="en-common-origin-order" value="' . $en_origin_order . '"></span>';
        $append_html .= '<h2>Shipping from</h2>';
        $append_html .= '<span class="en-add-shipping-origin"><a href="#en_dbsc_add_shipping_origin" onclick="en_dbsc_add_shipping_origin(' . $profile_id . ',' . $en_shipping_origin_id . ',' . $en_origin_order . ')" title="Add Shipping Origin">Add shipping origin</a></span>';
        $append_html .= '<div class="en-shipping-origin-list-' . $profile_id . '">';

        $origins_list = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $en_table WHERE profile_id =  %d AND origin_order =  %d",
                $profile_id, $en_origin_order
            )
        );
        foreach ($origins_list as $origins_list_id => $origins_list_data) {

            $append_html .= '<div class="en-shipping-origin-list-item">';
            $append_html .= '<p class="en-shipping-origin-list-item-' . $origins_list_data->id . '">' . $origins_list_data->nickname . ' <br>' . $origins_list_data->street_address . ', ' . $origins_list_data->city . ' ' . $origins_list_data->state . ' ' . $origins_list_data->postal_code . ', ' . $origins_list_data->country_code . '</p>';
            $append_html .= "<span>";
            $append_html .= '<a href="javascript:;" class="en-menu-popup" onclick="en_toggle_menu(this)">&hellip;</a>';
            $append_html .= '</span>';
            $append_html .= '<div class="en-menu-options">';
            $append_html .= '<ul>';
            $append_html .= '<li><a href="javascript:;" onclick="en_edit_dbsc_shipping_origin(' . $profile_id . ', ' . $origins_list_data->id . ', ' . $en_origin_order . ')">Edit</a></li>';
            $append_html .= "<li><a href='javascript:;' onclick='en_dbsc_delete_record(0,$profile_id,$origins_list_data->id, \"origin\")'>Delete</a></li>";
            $append_html .= '</ul>';
            $append_html .= '</div>';
            $append_html .= '</div>';

            // New change
        }

        if ($en_shipping_origin_action == 'add_shipping_origin') {
            $append_html .= '<div class="en_shipping_to">';
            $append_html .= '<h2>Shipping To</h2>';
            $append_html .= '<span class="en-add-shipping-zone">';
            $append_html .= '<a href="#en_dbsc_add_shipping_zone" onclick="en_dbsc_add_shipping_zone(' . $profile_id . ',' . $origins_list_data->id . ')" title="Add Shipping Zone">Add shipping zone</a>';
            $append_html .= '</span>';
            $append_html .= '</div>';
        }

        $append_html .= '</div>';
        $append_html .= '</div>';

        $append_text = $nickname . ' <br>' . $street_address . ', ' . $city . ' ' . $state . ' ' . $postal_code . ', ' . $country_code;

        echo wp_json_encode([
            'response' => 'success',
            'message' => $success_message,
            'reload' => true,
            'action_type' => $en_shipping_origin_action,
            'profile_id' => $profile_id,
            'origin_id' => $en_shipping_origin_id,
            'replace_text' => $append_text,
            'append_html' => $append_html,
            'en_add_the_shipping_origin' => $en_add_the_shipping_origin,
            'en_origin_order' => $en_origin_order,
        ]);
        exit;
    }

    /**
     * Edit shipping origin
     */
    public
    function en_edit_shipping_origin()
    {
        if (!(current_user_can('manage_options') || current_user_can('manage_woocommerce')) || !wp_verify_nonce($_POST['wp_nonce'], 'en_dbsc_admin_popup_nonce')) {
            echo wp_json_encode([]);
            return;
        }

        global $wpdb;

        $profile_id = (isset($_POST['profile_id']) && !empty($_POST['profile_id'])) ? intval(sanitize_text_field($_POST['profile_id'])) : '';
        $origin_id = (isset($_POST['origin_id']) && !empty($_POST['origin_id'])) ? intval(sanitize_text_field($_POST['origin_id'])) : '';

        $en_table = $wpdb->prefix . 'en_shipping_origins';
        $en_shipping_origin = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM `$en_table` WHERE `id` = %d AND `profile_id` = %d", $origin_id, $profile_id)
        );

        if ($en_shipping_origin) {
            echo wp_json_encode([
                'response' => 'success',
                'params' => $en_shipping_origin
            ]);
        }
        exit;
    }

    public function en_add_zone_rate()
    {
        if (!(current_user_can('manage_options') || current_user_can('manage_woocommerce')) || !wp_verify_nonce($_POST['wp_nonce'], 'en_dbsc_admin_popup_nonce')) {
            echo wp_json_encode([]);
            return;
        }

        global $wpdb;
        $rate_table = $wpdb->prefix . 'en_dbsc_rates';
        $zone_rate_table = $wpdb->prefix . 'en_zone_rates';
        $message = $action = '';
        $form_data = $rates_data = $zone_rate_data = $where = [];
        $inserted = $updated = false;

        if (isset($_POST['form_data']) && !empty($_POST['form_data'])) {

            $currency = get_woocommerce_currency_symbol();
            $dim_unit = 'in';
            $wt_unit = 'lbs';

            parse_str($_POST['form_data'], $form_data);

            $display_as = (isset($form_data['en_dbsc_add_rate_display_as']) && !empty($form_data['en_dbsc_add_rate_display_as'])) ? $form_data['en_dbsc_add_rate_display_as'] : '';

            $description = (isset($form_data['en_dbsc_add_rate_description']) && !empty($form_data['en_dbsc_add_rate_description'])) ? $form_data['en_dbsc_add_rate_description'] : '';

            $address_type = (isset($form_data['en_dbsc_address_type']) && !empty($form_data['en_dbsc_address_type'])) ? $form_data['en_dbsc_address_type'] : '';

            $default_address_type = (isset($form_data['en_dbsc_default_unknown_address_type']) && !empty($form_data['en_dbsc_default_unknown_address_type'])) ? $form_data['en_dbsc_default_unknown_address_type'] : '';

            $rate = (isset($form_data['en_dbsc_add_rate_per_mile']) && !empty($form_data['en_dbsc_add_rate_per_mile'])) ? $form_data['en_dbsc_add_rate_per_mile'] : '0';

            $base_amount = (isset($form_data['en_dbsc_add_rate_base_amount']) && !empty($form_data['en_dbsc_add_rate_base_amount'])) ? $form_data['en_dbsc_add_rate_base_amount'] : '0';
            
            $unit = (isset($form_data['en_dbsc_add_rate_unit']) && !empty($form_data['en_dbsc_add_rate_unit'])) ? $form_data['en_dbsc_add_rate_unit'] : '';

            $measured_by = (isset($form_data['en_dbsc_add_rate_measured_by']) && !empty($form_data['en_dbsc_add_rate_measured_by'])) ? $form_data['en_dbsc_add_rate_measured_by'] : '';

            $min_weight = (isset($form_data['en_dbsc_add_rate_min_weight']) && !empty($form_data['en_dbsc_add_rate_min_weight'])) ? $form_data['en_dbsc_add_rate_min_weight'] : null;

            $max_weight = (isset($form_data['en_dbsc_add_rate_max_weight']) && !empty($form_data['en_dbsc_add_rate_max_weight'])) ? $form_data['en_dbsc_add_rate_max_weight'] : null;

            $rate_condition = (isset($form_data['en_dbsc_add_rate_condition_base']) && !empty($form_data['en_dbsc_add_rate_condition_base'])) ? $form_data['en_dbsc_add_rate_condition_base'] : '';

            $min_distance = (isset($form_data['en_dbsc_add_rate_min_distance']) && !empty($form_data['en_dbsc_add_rate_min_distance'])) ? $form_data['en_dbsc_add_rate_min_distance'] : null;

            $max_distance = (isset($form_data['en_dbsc_add_rate_max_distance']) && !empty($form_data['en_dbsc_add_rate_max_distance'])) ? $form_data['en_dbsc_add_rate_max_distance'] : null;

            $min_length = (isset($form_data['en_dbsc_add_rate_min_length']) && !empty($form_data['en_dbsc_add_rate_min_length'])) ? $form_data['en_dbsc_add_rate_min_length'] : null;

            $max_length = (isset($form_data['en_dbsc_add_rate_max_length']) && !empty($form_data['en_dbsc_add_rate_max_length'])) ? $form_data['en_dbsc_add_rate_max_length'] : null;

            $distance_adjustment = (isset($form_data['en_dbsc_add_rate_distance_adjustment']) && !empty($form_data['en_dbsc_add_rate_distance_adjustment'])) ? $form_data['en_dbsc_add_rate_distance_adjustment'] : '';

            $rate_adjustment = (isset($form_data['en_dbsc_add_rate_rate_adjustment']) && !empty($form_data['en_dbsc_add_rate_rate_adjustment'])) ? $form_data['en_dbsc_add_rate_rate_adjustment'] : '';

            $min_quote = (isset($form_data['en_dbsc_add_rate_min_quote']) && !empty($form_data['en_dbsc_add_rate_min_quote'])) ? $form_data['en_dbsc_add_rate_min_quote'] : null;

            $max_quote = (isset($form_data['en_dbsc_add_rate_max_quote']) && !empty($form_data['en_dbsc_add_rate_max_quote'])) ? $form_data['en_dbsc_add_rate_max_quote'] : null;
            
            $min_cart_value = (isset($form_data['en_dbsc_add_rate_min_cart_value']) && !empty($form_data['en_dbsc_add_rate_min_cart_value'])) ? $form_data['en_dbsc_add_rate_min_cart_value'] : null;

            $max_cart_value = (isset($form_data['en_dbsc_add_rate_max_cart_value']) && !empty($form_data['en_dbsc_add_rate_max_cart_value'])) ? $form_data['en_dbsc_add_rate_max_cart_value'] : null;

            $calculate_for = (isset($form_data['en_dbsc_add_rate_calculution_base']) && !empty($form_data['en_dbsc_add_rate_calculution_base'])) ? $form_data['en_dbsc_add_rate_calculution_base'] : '';

            $cart_value_type = (isset($form_data['en_dbsc_add_rate_cart_value_type']) && !empty($form_data['en_dbsc_add_rate_cart_value_type'])) ? $form_data['en_dbsc_add_rate_cart_value_type'] : '';

            $display_preference = (isset($form_data['en_dbsc_add_rate_distance_display_preference']) && !empty($form_data['en_dbsc_add_rate_distance_display_preference'])) ? $form_data['en_dbsc_add_rate_distance_display_preference'] : 'no';

            $en_profile_id = (isset($form_data['en_profile_id']) && !empty($form_data['en_profile_id'])) ? intval(wc_clean($form_data['en_profile_id'])) : '';
            $zone_id = (isset($form_data['en_zone_id']) && !empty($form_data['en_zone_id'])) ? intval(wc_clean($form_data['en_zone_id'])) : '';
            $rate_id = (isset($form_data['en_rate_id']) && !empty($form_data['en_rate_id'])) ? intval(wc_clean($form_data['en_rate_id'])) : '';
            $perform = (isset($form_data['en_rate_action']) && !empty($form_data['en_rate_action'])) ? $form_data['en_rate_action'] : '';

            $rates_data = ['display_as' => $display_as,
                'description' => $description,
                'rate' => $rate,
                'unit' => $unit,
                'measured_by' => $measured_by,
                'min_weight' => $min_weight,
                'max_weight' => $max_weight,
                'rate_condition' => $rate_condition,
                'min_distance' => $min_distance,
                'max_distance' => $max_distance,
                'min_length' => $min_length,
                'max_length' => $max_length,
                'distance_adjustment' => $distance_adjustment,
                'rate_adjustment' => $rate_adjustment,
                'min_quote' => $min_quote,
                'max_quote' => $max_quote,
                'min_cart_value' => $min_cart_value,
                'max_cart_value' => $max_cart_value,
                'cart_value_type' => $cart_value_type,
                'calculate_for' => $calculate_for,
                'display_preference' => $display_preference,
                'address_type' => $address_type,
                'default_address_type' => $default_address_type,
                'base_amount' => $base_amount
            ];

            switch ($perform) {

                case 'edit_rate':
                    $where = ['id' => $rate_id];
                    if ($wpdb->update($rate_table, $rates_data, $where)) {
                        $updated = true;
                        $action = 'update';
                        $message = 'Shipping rate has been updated successfully.';
                    }
                    break;

                default:
                case 'add_rate':
                    if ($wpdb->insert($rate_table, $rates_data)) {
                        $rate_id = $wpdb->insert_id;
                        $zone_rate_data = [
                            'zone_id' => $zone_id,
                            'dbsc_rate_id' => $rate_id
                        ];

                        $wpdb->insert($zone_rate_table, $zone_rate_data);
                        $inserted = true;
                        $action = 'insert';
                        $message = 'Shipping rate has been added successfully.';
                    }
                    break;
            }

            if ($inserted || $updated) {
                $rate = is_numeric($rate) ? number_format($rate, 2, ".", "") : $rate;
                $min_quote = is_numeric($min_quote) ? number_format($min_quote, 2, ".", "") : $min_quote;
                $min_quote = empty($min_quote) ? '0.00' : $min_quote;
                $max_quote = !empty($max_quote) ? number_format($max_quote, 2, ".", "") : 'up';
                $measured_by = $measured_by == 'route' ? 'Route' : 'Straight Line';
                $tr_id = "en_zone" . $zone_id . "_rate$rate_id";
                $currency_symbol = get_woocommerce_currency_symbol();

                $max_distance_show = empty($max_distance) ? 'up' : $max_distance . ' ' . $unit;
                $max_weight_show = empty($max_weight) ? 'up' : $max_weight . ' ' . $wt_unit;
                $max_length_show = empty($max_length) ? 'up' : $max_length . ' ' . $dim_unit;

                $min_distance = empty($min_distance) ? '0' : $min_distance;
                $min_length = empty($min_length) ? '0' : $min_length;
                $min_weight = empty($min_weight) ? '0' : $min_weight;
                $min_quote = empty($min_quote) ? '0' : $min_quote;

                $calculate_for_label = ($calculate_for == 'item_after_quotes') ? 'Item' : ucfirst($calculate_for);
                $calculate_for_show = ($calculate_for == 'flat') ? $currency_symbol . $rate : $currency_symbol . $rate . ' / ' . $calculate_for_label;

                $append = "<tr id='$tr_id'>";
                $append .= "<td><span class='en_display_as_collection'>$display_as</span><div>$description </div></td>";
                $append .= "<td class='en-align-center'>$calculate_for_show</td>";
                $append .= "<td class='en-align-center'>$measured_by</td>";
                $append .= "<td class='en-align-center'>$min_distance $unit - $max_distance_show</td>";
                $append .= "<td class='en-align-center'>$min_weight $wt_unit - $max_weight_show</td>";
                $append .= "<td class='en-align-center'>" . ucfirst($rate_condition) . " </td>";
                $append .= "<td class='en-align-center'>$min_length $dim_unit-$max_length_show</td>";
                $append .= "<td class='en-align-center'>$min_quote-$max_quote</td>";
                $append .= "<td class='en-align-center'>";
                $append .= "<div class='edit-rate-popup action-btn'>";
                $append .= "<span>";
                $append .= "<a href='javascript:;' class='en-menu-popup' onclick='en_toggle_menu(this)'>&hellip;</a>";
                $append .= "</span>";
                $append .= "<div class='en-menu-options'>";
                $append .= "<ul>";
                $append .= "<li>";
                $append .= "<a href='javascript:;' onclick='en_edit_dbsc_rate($en_profile_id,$zone_id, $rate_id)'>Edit</a>";
                $append .= "</li>";
                $append .= "<li>";
                $append .= "<a href='javascript:;' onclick='en_dbsc_delete_record($en_profile_id,$zone_id, $rate_id, \"rate\")'>Delete</a>";
                $append .= "</li>";
                $append .= "</ui>";
                $append .= "</div>";
                $append .= "</div>";
                $append .= "</td>";
                $append .= "</tr>";
            }

            echo wp_json_encode([
                'response' => 'success',
                'message' => $message,
                'append' => $append,
                'action' => $action,
                'zone_id' => $zone_id,
                'id' => $tr_id]);
            exit;
        }
    }

    public function en_get_zone_rate()
    {
        if (!(current_user_can('manage_options') || current_user_can('manage_woocommerce')) || !wp_verify_nonce($_POST['wp_nonce'], 'en_dbsc_admin_popup_nonce')) {
            echo wp_json_encode([]);
            return;
        }

        global $wpdb;
        $result = [];

        $rate_id = isset($_POST['rate_id']) && !empty($_POST['rate_id']) ? intval(sanitize_text_field($_POST['rate_id'])) : 0;

        if ($rate_id) {
            $rate_table = $wpdb->prefix . 'en_dbsc_rates';
            $rate = $wpdb->get_row(
                $wpdb->prepare("SELECT * FROM $rate_table WHERE id = %d", $rate_id)
            );

            $dom_arr = ['#en_rate_id' => 'id',
                '#en_dbsc_add_rate_display_as' => 'display_as',
                '#en_dbsc_add_rate_description' => 'description',
                '#en_dbsc_add_rate_per_mile' => 'rate',
                '#en_dbsc_add_rate_unit' => 'unit',
                '#en_dbsc_add_rate_measured_by' => 'measured_by',
                '#en_dbsc_add_rate_min_distance' => 'min_distance',
                '#en_dbsc_add_rate_max_distance' => 'max_distance',
                '#en_dbsc_add_rate_min_weight' => 'min_weight',
                '#en_dbsc_add_rate_max_weight' => 'max_weight',
                '#en_dbsc_add_rate_condition_base' => 'rate_condition',
                '#en_dbsc_add_rate_min_length' => 'min_length',
                '#en_dbsc_add_rate_max_length' => 'max_length',
                '#en_dbsc_add_rate_min_quote' => 'min_quote',
                '#en_dbsc_add_rate_max_quote' => 'max_quote',
                '#en_dbsc_add_rate_min_cart_value' => 'min_cart_value',
                '#en_dbsc_add_rate_max_cart_value' => 'max_cart_value',
                '#en_dbsc_add_rate_cart_value_type' => 'cart_value_type',
                '#en_dbsc_add_rate_calculution_base' => 'calculate_for',
                '#en_dbsc_add_rate_distance_adjustment' => 'distance_adjustment',
                '#en_dbsc_add_rate_rate_adjustment' => 'rate_adjustment',
                '#en_dbsc_add_rate_distance_display_preference' => 'display_preference',
                '#en_dbsc_address_type' => 'address_type',
                '#en_dbsc_default_unknown_address_type' => 'default_address_type',
                '#en_dbsc_add_rate_base_amount' => 'base_amount'
            ];

            foreach ($dom_arr as $key => $value) {
                $result[$key] = isset($rate->$value) ? $rate->$value : '';
            }

            $success = !empty($result) ? 'success' : 'failed';

            echo wp_json_encode([
                'response' => $success,
                'result' => $result]);
            exit;
        }
    }

    public function en_dbsc_delete_record_action()
    {
        if (!(current_user_can('manage_options') || current_user_can('manage_woocommerce')) || !wp_verify_nonce($_POST['wp_nonce'], 'en_dbsc_admin_popup_nonce')) {
            echo json_encode([]);
            return;
        }

        global $wpdb;
        $success_message = '';
        $form_data = [];

        if (isset($_POST['form_data']) && !empty($_POST['form_data'])) {

            parse_str($_POST['form_data'], $form_data);

            $action = (isset($form_data['en_action_for']) && !empty($form_data['en_action_for'])) ? sanitize_text_field($form_data['en_action_for']) : '';

            $profile_id = (isset($form_data['en_profile_id']) && !empty($form_data['en_profile_id'])) ? intval(sanitize_text_field($form_data['en_profile_id'])) : '';

            $origin_id = (isset($form_data['en_origin_id']) && !empty($form_data['en_origin_id'])) ? intval(sanitize_text_field($form_data['en_origin_id'])) : '';

            $zone_id = (isset($form_data['en_zone_id']) && !empty($form_data['en_zone_id'])) ? intval(sanitize_text_field($form_data['en_zone_id'])) : '';

            $rate_id = (isset($form_data['en_rate_id']) && !empty($form_data['en_rate_id'])) ? intval(sanitize_text_field($form_data['en_rate_id'])) : '';
            switch ($action) {

                case 'profile':
                    // delete profile
                    $this->delete_profile($profile_id);
                    $success_message = 'Profile has been deleted successfully.';

                    break;

                case 'origin':
                    // delete shipping origin
                    $this->delete_origin($origin_id);
                    $success_message = 'Shipping origin has been deleted successfully.';

                    break;

                case 'zone':
                    // delete shipping zone
                    $this->delete_zone($zone_id, $origin_id);
                    $success_message = 'Shipping zone has been deleted successfully.';

                    break;

                case 'rate':
                    // delete shipping rate
                    $this->delete_rate($rate_id, $zone_id);
                    $success_message = 'Shipping rate has been deleted successfully.';

                    break;
            }
        }

        echo wp_json_encode([
            'response' => 'success',
            'message' => $success_message,
            'profile_id' => $profile_id,
            'origin_id' => $origin_id,
            'zone_id' => $zone_id,
            'rate_id' => $rate_id,
            'action' => $action
        ]);
        exit;
    }

    public function delete_profile($profile_id)
    {
        global $wpdb;
        $profile_table = $wpdb->prefix . 'en_profiles';
        $wpdb->delete($profile_table, array('id' => $profile_id));

        // delete profile shipping classes
        $en_profile_classes_table = $wpdb->prefix . 'en_profile_classes';
        $wpdb->delete($en_profile_classes_table, array('profile_id' => $profile_id));

        $this->delete_origin(0, $profile_id);
    }

    public function delete_origin($origin_id, $profile_id = 0)
    {
        global $wpdb;
        $origin_table = $wpdb->prefix . 'en_shipping_origins';
        if ($profile_id) {
            $origin_ids = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT id FROM $origin_table WHERE profile_id = %d",
                    $profile_id
                )
            );
            foreach ($origin_ids as $origin_id) {
                $this->delete_zone(0, $origin_id->id);
            }
            $wpdb->delete($origin_table, array('profile_id' => $profile_id));
            return;
        } else if ($origin_id) {
            $this->delete_zone(0, $origin_id);
        }
        $wpdb->delete($origin_table, array('id' => $origin_id));
    }

    public function delete_zone($zone_id, $origin_id = 0)
    {
        global $wpdb;
        $origin_zone_table = $wpdb->prefix . 'en_origin_zones';
        if (!$zone_id > 0) {

            $zone_ids = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT zone_id FROM $origin_zone_table WHERE en_origin_id = %d",
                    $origin_id
                )
            );
            foreach ($zone_ids as $zone_id) {
                // New Change
                $zones_data = WC_Shipping_Zones::get_zone(absint($zone_id->zone_id));
                if ($zones_data) {
                    $compare_zone_id = $wpdb->get_results(
                        $wpdb->prepare(
                            "SELECT zone_id FROM $origin_zone_table WHERE zone_id = %d",
                            $zone_id->zone_id
                        )
                    );
                    if (count($compare_zone_id) == 1) {
                        $this->delete_rate(0, $zone_id->zone_id);
                        WC_Shipping_Zones::delete_zone($zone_id->zone_id);
                    }
                }
            }
            $wpdb->delete($origin_zone_table, ['en_origin_id' => $origin_id]);
            return;
        }

        // New Change
        $zones_data = WC_Shipping_Zones::get_zone(absint($zone_id));
        if ($zones_data) {
            WC_Shipping_Zones::delete_zone($zone_id);
        }
        $wpdb->delete($origin_zone_table, ['zone_id' => $zone_id]);
    }

    public function delete_rate($rate_id, $zone_id = 0)
    {

        global $wpdb;
        $rate_ids = [];
        $zone_rate_table = $wpdb->prefix . 'en_zone_rates';
        $rate_table = $wpdb->prefix . 'en_dbsc_rates';

        if (!$rate_id) {
            $rate_ids = $wpdb->get_col(
                $wpdb->prepare(
                    "SELECT dbsc_rate_id FROM $zone_rate_table WHERE zone_id = %d",
                    $zone_id
                )
            );

            foreach ($rate_ids as $rate_id) {
                $wpdb->delete($rate_table, ['id' => $rate_id]);
            }
            $wpdb->delete($zone_rate_table, ['zone_id' => $zone_id]);
            return;
        }
        $wpdb->delete($rate_table, ['id' => $rate_id]);
        $wpdb->delete($zone_rate_table, ['zone_id' => $zone_id, 'dbsc_rate_id' => $rate_id]);
    }
    /**
     * This functions adds data in the warehouse table
     */
    public function en_dbsc_add_warehouse_dropship($origin_data){
        global $wpdb;
        $warehouse_table = $wpdb->prefix . "warehouse";
        if ($wpdb->query("SHOW TABLES LIKE '" . $warehouse_table . "'") === 1 && !empty($origin_data['postal_code'])) {
            $origin_type = ($origin_data['availability'] == 'available_in_dropship') ? 'dropship' : 'warehouse';

            $warehouse_data = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT COUNT(*) as count FROM `$warehouse_table` WHERE zip = %s AND location = %s",
                    $origin_data['postal_code'],
                    $origin_type
                )
            );

            if(isset($warehouse_data[0]->count) && $warehouse_data[0]->count == 0){
                $data_arr = [
                    'city' => $origin_data['city'],
                    'state' => $origin_data['state'],
                    'zip' => $origin_data['postal_code'],
                    'country' => $origin_data['country_code'],
                    'address' => $origin_data['street_address'],
                    'nickname' => $origin_data['nickname'],
                    'location' => $origin_type,
                    'enable_store_pickup' => '0',
                ];

                $wpdb->insert($warehouse_table, $data_arr);
            }

        }

    }

}
