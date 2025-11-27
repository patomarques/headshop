<?php

/**
 * App install hook
 */
add_action('admin_enqueue_scripts','dbsc_hide_meta_data');
use EnDistanceBaseShippingConfig\EnDistanceBaseShippingConfig;
function dbsc_hide_meta_data() {
    ?>
    <style>
        #order_shipping_line_items .shipping .display_meta {
            display: none;
        }
    </style>
    <?php
}
function EN_DISTANCE_BASE_SHIPPING_installation()
{
    apply_filters('en_register_activation_hook', false);
}

register_activation_hook(EN_DISTANCE_BASE_SHIPPING_MAIN_FILE, 'EN_DISTANCE_BASE_SHIPPING_installation');

/**
 * Distance Base plugin update now
 */
if (!function_exists('en_distancebase_ltl_update_now')) {

    function en_distancebase_ltl_update_now($upgrader_object, $options)
    {
        $en_distancebase_path_name = plugin_basename( EN_DISTANCE_BASE_SHIPPING_MAIN_FILE );
        if ($options['action'] == 'update' && $options['type'] == 'plugin' && isset( $options['plugins'] ) ) {
            foreach($options['plugins'] as $each_plugin) {
                if ($each_plugin == $en_distancebase_path_name) {
                    EN_DISTANCE_BASE_SHIPPING_installation();
                }
            }
        }
    }
    add_action('upgrader_process_complete', 'en_distancebase_ltl_update_now' , 10, 2);
}
/**
 * App uninstall hook
 */
function EN_DISTANCE_BASE_SHIPPING_uninstall()
{
    apply_filters('en_register_deactivation_hook', false);
}

register_deactivation_hook(EN_DISTANCE_BASE_SHIPPING_MAIN_FILE, 'EN_DISTANCE_BASE_SHIPPING_uninstall');

/**
 * App load admin side files of css and js hook
 */
function EN_DISTANCE_BASE_SHIPPING_admin_enqueue_scripts()
{
    if (isset($_REQUEST['tab']) && $_REQUEST['tab'] == 'distance_base_shipping') {
        wp_enqueue_script('EnDistanceBaseShippingJqueryLatest', 'https://code.jquery.com/jquery-1.12.4.js', []);
        wp_enqueue_script('EnDistanceBaseShippingAutocomplete', 'https://code.jquery.com/ui/1.12.1/jquery-ui.js', []);
        wp_register_style('EnDistanceBaseShippingAutocompleteCss', '//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css', false);
        wp_enqueue_style('EnDistanceBaseShippingAutocompleteCss');

        wp_enqueue_script('EnDistanceBaseShippingAdminJs', EN_DISTANCE_BASE_SHIPPING_DIR_FILE . 'admin/assets/en-distance-base-shipping-admin.js', [], '2.0.4');
        wp_localize_script('EnDistanceBaseShippingAdminJs', 'en_dbs_admin_script', [
            'pluginsUrl' => EN_DISTANCE_BASE_SHIPPING_PLUGIN_URL,
            'nonce' => wp_create_nonce('en_dbsc_admin_nonce'),
        ]);

        wp_enqueue_script('EnDistanceBaseShippingFromValidationsJs', EN_DISTANCE_BASE_SHIPPING_DIR_FILE . 'admin/popup/assets/js/jquery.validate.min.js', [], '1.19.1');
        wp_localize_script('EnDistanceBaseShippingFromValidationsJs', 'script', [
            'pluginsUrl' => EN_DISTANCE_BASE_SHIPPING_PLUGIN_URL,
        ]);

        wp_enqueue_script('EnDistanceBaseShippingFieldTagging', plugin_dir_url(__FILE__) . 'admin/popup/assets/js/tagging.js', array(), '1.0.0');
        wp_localize_script('EnDistanceBaseShippingFieldTagging', 'script', array(
            'pluginsUrl' => EN_DISTANCE_BASE_SHIPPING_PLUGIN_URL,
        ));

        wp_enqueue_script('EnDistanceBaseShippingAdminPopupJs', EN_DISTANCE_BASE_SHIPPING_DIR_FILE . 'admin/popup/assets/js/en-distance-base-shipping-admin-popup.js', [], '2.0.3');
        wp_localize_script('EnDistanceBaseShippingAdminPopupJs', 'en_dbsc_admin_popup_script', [
            'pluginsUrl' => EN_DISTANCE_BASE_SHIPPING_PLUGIN_URL,
            'nonce' => wp_create_nonce('en_dbsc_admin_popup_nonce'),
        ]);

        wp_register_style('EnDistanceBaseShippingAdminCss', EN_DISTANCE_BASE_SHIPPING_DIR_FILE . 'admin/assets/en-distance-base-shipping-admin.css', false, '1.0.4');
        wp_enqueue_style('EnDistanceBaseShippingAdminCss');

        wp_register_style('EnDistanceBaseShippingAdminPopupCss', EN_DISTANCE_BASE_SHIPPING_DIR_FILE . 'admin/popup/assets/css/en-distance-base-shipping-admin-popup.css', false, '2.0.2');
        wp_enqueue_style('EnDistanceBaseShippingAdminPopupCss');
    }
}

add_action('admin_enqueue_scripts', 'EN_DISTANCE_BASE_SHIPPING_admin_enqueue_scripts');

/**
 * App load front-end side files of css and js hook
 */
function EN_DISTANCE_BASE_SHIPPING_frontend_enqueue_scripts()
{
    wp_enqueue_script('EnDistanceBaseShippingFrontEnd', EN_DISTANCE_BASE_SHIPPING_DIR_FILE . '/admin/assets/en-distance-base-shipping-admin-frontend.js', ['jquery'], '1.0.2');
    wp_localize_script('EnDistanceBaseShippingFrontEnd', 'script', [
        'pluginsUrl' => EN_DISTANCE_BASE_SHIPPING_PLUGIN_URL,
    ]);
}

add_action('wp_enqueue_scripts', 'EN_DISTANCE_BASE_SHIPPING_frontend_enqueue_scripts');

/**
 * Load tab file
 * @param $settings
 * @return array
 */
function EN_DISTANCE_BASE_SHIPPING_shipping_sections($settings)
{
    include('admin/tab/en-tab.php');
    return $settings;
}

add_filter('woocommerce_get_settings_pages', 'EN_DISTANCE_BASE_SHIPPING_shipping_sections', 10, 1);

/**
 * Show action links on plugins page
 * @param $actions
 * @param $plugin_file
 * @return array
 */
function EN_DISTANCE_BASE_SHIPPING_freight_action_links($actions, $plugin_file)
{
    static $plugin;
    if (!isset($plugin)) {
        $plugin = EN_DISTANCE_BASE_SHIPPING_BASE_NAME;
    }

    if ($plugin == $plugin_file) {
        $settings = array('settings' => '<a href="admin.php?page=wc-settings&tab=distance_base_shipping">' . __('Settings', 'General') . '</a>');
        $site_link = array('support' => '<a href="' . EN_DISTANCE_BASE_SHIPPING_SUPPORT_URL . '" target="_blank">Support</a>');
        $actions = array_merge($settings, $actions);
        $actions = array_merge($site_link, $actions);
    }

    return $actions;
}

add_filter('plugin_action_links', 'EN_DISTANCE_BASE_SHIPPING_freight_action_links', 10, 2);

/**
 * globally script variable
 */
function EN_DISTANCE_BASE_SHIPPING_admin_inline_js()
{
    ?>
    <script>
        let EN_DISTANCE_BASE_SHIPPING_DIR_FILE = "<?php echo EN_DISTANCE_BASE_SHIPPING_DIR_FILE; ?>";
    </script>
    <?php
}

add_action('admin_print_scripts', 'EN_DISTANCE_BASE_SHIPPING_admin_inline_js');

/**
 * App name action links
 * @staticvar $plugin
 * @param $actions
 * @param $plugin_file
 * @return array
 */
function EN_DISTANCE_BASE_SHIPPING_admin_action_links($actions, $plugin_file)
{
    static $plugin;
    if (!isset($plugin))
        $plugin = plugin_basename(__FILE__);
    if ($plugin == $plugin_file) {
        $settings = array('settings' => '<a href="admin.php?page=wc-settings&tab=distance_base_shipping">' . __('Settings', 'General') . '</a>');
        $site_link = array('support' => '<a href="' . EN_DISTANCE_BASE_SHIPPING_SUPPORT_URL . '" target="_blank">Support</a>');
        $actions = array_merge($settings, $actions);
        $actions = array_merge($site_link, $actions);
    }
    return $actions;
}

add_filter('plugin_action_links_' . EN_DISTANCE_BASE_SHIPPING_BASE_NAME, 'EN_DISTANCE_BASE_SHIPPING_admin_action_links', 10, 2);

/**
 * App name method in woo method list
 * @param $methods
 * @return string
 */
function EN_DISTANCE_BASE_SHIPPING_add_shipping_app($methods)
{
    $methods['distance_base_shipping'] = 'EnDistanceBaseShippingShippingRates';
    return $methods;
}

add_filter('woocommerce_shipping_methods', 'EN_DISTANCE_BASE_SHIPPING_add_shipping_app', 10, 1);

/**
 * The message show when no rates will display on the cart page
 */
if (!function_exists('en_none_shipping_rates')) {

    function en_none_shipping_rates()
    {
        $en_eniture_shipment = apply_filters('en_eniture_shipment', []);
        if (isset($en_eniture_shipment['LTL'])) {
            return esc_html("<div><p>There are no shipping methods available. 
                    Please double check your address, or contact us if you need any help.</p></div>");
        }
    }

    add_filter('woocommerce_cart_no_shipping_available_html', 'en_none_shipping_rates');
}

/**
 * App name plan status
 * @param array $plan_status
 * @return array
 */
function EN_DISTANCE_BASE_SHIPPING_plan_status($plan_status)
{
    $plan_required = '0';
    $hazardous_material_status = 'App name: Enabled.';
    $hazardous_material = apply_filters("distance_base_shipping_plans_suscription_and_features", 'hazardous_material');
    if (is_array($hazardous_material)) {
        $plan_required = '1';
        $hazardous_material_status = 'App name: Upgrade to Standard Plan to enable.';
    }

    $plan_status['hazardous_material']['distance_base_shipping'][] = 'distance_base_shipping';
    $plan_status['hazardous_material']['plan_required'][] = $plan_required;
    $plan_status['hazardous_material']['status'][] = $hazardous_material_status;

    return $plan_status;
}

add_filter('en_app_common_plan_status', 'EN_DISTANCE_BASE_SHIPPING_plan_status', 10, 1);

/**
 * The message show when no rates will display on the cart page
 */

/**
 * Hide third party shipping rates
 * @param mixed $available_methods
 * @return mixed
 */
function EN_DISTANCE_BASE_SHIPPING_hide_shipping($available_methods)
{
    $en_eniture_shipment = apply_filters('en_eniture_shipment', []);
    $en_shipping_applications = apply_filters('en_shipping_applications', []);
    $eniture_old_plugins = get_option('EN_Plugins');
    $eniture_old_plugins = $eniture_old_plugins ? json_decode($eniture_old_plugins, true) : [];
    $en_eniture_apps = array_merge($en_shipping_applications, $eniture_old_plugins);

    if (get_option('en_quote_settings_allow_other_plugins_distance_base_shipping') == 'no' &&
        (isset($en_eniture_shipment['LTL'])) && count($available_methods) > 0) {
        foreach ($available_methods as $index => $method) {
            if (!in_array($method->method_id, $en_eniture_apps)) {
                unset($available_methods[$index]);
            }
        }
    }

    return $available_methods;
}

add_filter('woocommerce_package_rates', 'EN_DISTANCE_BASE_SHIPPING_hide_shipping', 99, 1);

/**
 * Eniture Done app name
 * @param array $en_applications
 * @return array
 */
function EN_DISTANCE_BASE_SHIPPING_shipping_applications($en_applications)
{
    return array_merge($en_applications, ['distance_base_shipping']);
}

add_filter('en_shipping_applications', 'EN_DISTANCE_BASE_SHIPPING_shipping_applications', 10, 1);

/**
 * Custom error message.
 * @param string $message
 * @return string|void
 */
function EN_DISTANCE_BASE_SHIPPING_error_message($message)
{
    $en_eniture_shipment = apply_filters('en_eniture_shipment', []);
    $reasons = apply_filters('EN_DISTANCE_BASE_SHIPPING_reason_quotes_not_returned', []);
    if (isset($en_eniture_shipment['LTL']) || !empty($reasons)) {
        $en_settings = json_decode(EN_DISTANCE_BASE_SHIPPING_SET_QUOTE_SETTINGS, true);
        $message = (isset($en_settings['custom_error_message'])) ? $en_settings['custom_error_message'] : '';
        $custom_error_enabled = (isset($en_settings['custom_error_enabled'])) ? $en_settings['custom_error_enabled'] : '';

        switch ($custom_error_enabled) {
            case 'prevent':
                remove_action('woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20, 2);
                break;
            case 'allow':
                add_action('woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20, 2);
                break;
            default:
                $message = '<div><p>There are no shipping methods available. Please double check your address, or contact us if you need any help.</p></div>';
                break;
        }

        $message = !empty($reasons) ? implode(", ", $reasons) : $message;
    }

    return __($message);
}

add_filter('woocommerce_cart_no_shipping_available_html', 'EN_DISTANCE_BASE_SHIPPING_error_message', 999, 1);

/**
 * @return countries
 */
function en_distance_base_shipping_get_countries()
{

    global $woocommerce;
    $countries_obj = new \WC_Countries();
    $countries = $countries_obj->__get('countries');
    return $countries;
}

add_filter('en_woo_get_all_countries', 'en_distance_base_shipping_get_countries', 10, 1);

/**
 * @return shipping_classes
 */
function en_distance_base_shipping_get_shipping_classes()
{

    global $wpdb;
    return $wpdb->get_results("
        SELECT * FROM {$wpdb->prefix}terms as t
        INNER JOIN {$wpdb->prefix}term_taxonomy as tt ON t.term_id = tt.term_id
        WHERE tt.taxonomy LIKE 'product_shipping_class'
    ");
}

add_filter('en_woo_get_all_shipping_classes', 'en_distance_base_shipping_get_shipping_classes', 10, 1);


/**
 * Show DBSC Plan Notice
 * @return string
 */
function en_dbsc_plan_notice()
{
    $en_dbsc_package_scac = get_option('en_dbsc_package_scac');
    if (strlen($en_dbsc_package_scac) > 0) {
        echo '<div class="notice notice-success is-dismissible en_dbsc_plan_notice">
                     <p> You are currently on the trial plan.</p>
                     </div>';
    }
}

add_action('admin_notices', 'en_dbsc_plan_notice', 999);
// fdo va
add_action('wp_ajax_nopriv_distancebase_fd', 'distancebase_fd_api');
add_action('wp_ajax_distancebase_fd', 'distancebase_fd_api');
/**
 * UPS AJAX Request
 */
function distancebase_fd_api()
{
    if (!(current_user_can('manage_options') || current_user_can('manage_woocommerce')) || !wp_verify_nonce($_POST['wp_nonce'], 'en_dbsc_admin_nonce')) {
        echo wp_json_encode([]);
        return;
    }

    $store_name =  EnDistanceBaseShippingConfig::en_get_server_name();
    $company_id = isset($_POST['company_id']) ? sanitize_text_field(wp_unslash($_POST['company_id'])) : '';
    $data = [
        'plateform'  => 'wp',
        'store_name' => $store_name,
        'company_id' => $company_id,
        'fd_section' => 'tab=distance_base_shipping&section=section-3',
    ];
    if (is_array($data) && count($data) > 0) {
        if($_POST['disconnect'] != 'disconnect') {
            $url =  'https://freightdesk.online/validate-company';
        }else {
            $url = 'https://freightdesk.online/disconnect-woo-connection';
        }
        $response = wp_remote_post($url, [
                'method' => 'POST',
                'timeout' => 60,
                'redirection' => 5,
                'blocking' => true,
                'body' => $data,
            ]
        );
        $response = wp_remote_retrieve_body($response);
    }
    if($_POST['disconnect'] == 'disconnect') {
        $result = json_decode($response);
        if ($result->status == 'SUCCESS') {
            update_option('en_fdo_company_id_status', 0);
        }
    }
    echo $response;
    exit();
}
add_action('rest_api_init', 'en_rest_api_init_status_distancebase');
function en_rest_api_init_status_distancebase()
{
    register_rest_route('fdo-company-id', '/update-status', array(
        'methods' => 'POST',
        'callback' => 'en_distancebase_fdo_data_status',
        'permission_callback' => '__return_true'
    ));
}

/**
 * Update FDO coupon data
 * @param array|WP_REST_Request $request
 * @return array|void
 */
function en_distancebase_fdo_data_status(WP_REST_Request $request)
{
    $authenticated_user = en_dbsc_fdo_va_update_status_authenticate($request);
    if (is_wp_error($authenticated_user)) {
        return $authenticated_user;
    }

    $status_data = $request->get_body();
    $status_data_decoded = json_decode($status_data);
    if (isset($status_data_decoded->connection_status)) {
        update_option('en_fdo_company_id_status', $status_data_decoded->connection_status);
        update_option('en_fdo_company_id', $status_data_decoded->fdo_company_id);
    }
    return true;
}

// fdo / va api calls authentication
function en_dbsc_fdo_va_update_status_authenticate($request) {
    if ( !empty( $_SERVER['HTTP_CONSUMER_KEY'] ) && !empty( $_SERVER['HTTP_CONSUMER_SECRET'] ) ) {
        $consumer_key    = $_SERVER['HTTP_CONSUMER_KEY'];
        $consumer_secret = $_SERVER['HTTP_CONSUMER_SECRET'];
    }else{
        return new WP_Error('rest_authentication_error', __('Invalid Authorization header format.'), array('status' => 401));
    }

    global $wpdb;
    $consumer_key = wc_api_hash( sanitize_text_field( $consumer_key ) );
    $user         = $wpdb->get_row(
        $wpdb->prepare(
            "
        SELECT user_id, permissions
        FROM {$wpdb->prefix}woocommerce_api_keys
        WHERE consumer_key = %s
    ",
            $consumer_key
        )
    );

    if (!$user) {
        return new WP_Error('invalid_keys', __('Invalid consumer key or secret.'), array('status' => 401));
    }

    if (empty($user->permissions) || $user->permissions != 'read_write') {
        return new WP_Error('rest_forbidden', __('User does not have write permissions.'), array('status' => 403));
    }

    return $user;
}

/**
 * To export order
 */
if (!function_exists('en_export_order_on_order_place')) {

    function en_export_order_on_order_place()
    {
        new DistanceBaseShippingOrderExport\DistanceBaseShippingOrderExport();
    }

    en_export_order_on_order_place();
}
