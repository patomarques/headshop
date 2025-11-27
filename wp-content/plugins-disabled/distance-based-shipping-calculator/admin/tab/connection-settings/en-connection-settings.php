<?php

/**
 * Test connection details.
 */

namespace EnDistanceBaseShippingConnectionSettings;

/**
 * Add array for test connection.
 * Class EnDistanceBaseShippingConnectionSettings
 * @package EnDistanceBaseShippingConnectionSettings
 */
class EnDistanceBaseShippingConnectionSettings
{

    static public $get_connection_details = [];
    static public $lastUsageTime;
    static public $next_subcribed_package;
    static public $subscriptionInfo;
    static public $subscribedPackage;
    static public $subscribedPackageHitsStatus;
    static public $nextSubcribedPackage;
    static public $statusRequestTime;
    static public $subscriptionStatus;
    static public $subscription_packages_response;
    static public $subscription_details;
    static public $status;
    static public $plugin_name;
    static public $append_with_current_status = '';

    /**
     * Connection settings template.
     * @return array
     */
    static public function en_load()
    {
        $start_settings = [
            'en_connection_settings_distance_base_shipping' => [
                'name' => __('', 'woocommerce-settings-distance-base-shipping'),
                'type' => 'title',
                'id' => 'en_connection_settings_distance_base_shipping',
            ],
            'en_connection_settings_start_distance_base_shipping' => [
                'name' => __('', 'woocommerce-settings-distance-base-shipping'),
                'type' => 'title',
                'id' => 'en_connection_settings_distance_base_shipping',
            ],
        ];

        // App Name Connection Settings Detail
        $eniture_settings = self::en_set_connection_settings_detail();

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
     * Connection Settings Detail
     * @return array
     */
    static public function en_get_connection_settings_detail()
    {
        $connection_request = self::en_static_request_detail();
        return $connection_request;
    }

    /**
     * Saving reasons to show proper error message on the cart or checkout page
     * When quotes are not returning
     * @param array $reasons
     * @return array
     */
    static public function EN_DISTANCE_BASE_SHIPPING_reason_quotes_not_returned($reasons)
    {
        return empty(self::$get_connection_details) ? array_merge($reasons, [EN_DBSC_711]) : $reasons;
    }

    /**
     * Static Detail Set
     * @return array
     */
    static public function en_static_request_detail()
    {
        $pluginVersions = EN_DISTANCE_BASE_SHIPPING_VERSIONS_INFO;

        return
            [
                'serverName' => EN_DISTANCE_BASE_SHIPPING_SERVER_NAME,
                'platform' => 'WordPress',
                'carrierType' => 'TL',
                'carrierName' => 'distanceBasedShippingCalculator',
                'carrierMode' => 'pro',
                'requestVersion' => '2.0',
                'requestKey' => '1146dfQ1344623112YT33Es34d',
                'requestKey' => time(),
                'pluginVersion' => $pluginVersions["dbsc_plugin_version"],
                'wordpressVersion' => get_bloginfo('version'),
                'woocommerceVersion' => $pluginVersions["woocommerce_plugin_version"]
            ];
    }

    /**
     * Function detect site contains folder.
     */
    static public function en_check_url_contains_folder()
    {
        $url = get_site_url();
        $url = preg_replace('#^https?://#', '', $url);
        $urlArr = explode("/", $url);
        if (isset($urlArr[1]) && !empty($urlArr[1])) {
            return true;
        }
        return false;
    }

    /**
     * Smart street curl API response from server
     * @param type $action
     * @return type|string
     */
    static public function smart_street_api_curl_request($action, $selected_plan = "")
    {
        $plugin_license_key = get_option('en_connection_settings_license_key_distance_base_shipping');
        $pluginVersions = EN_DISTANCE_BASE_SHIPPING_VERSIONS_INFO;
        $postArr = array(
            'platform' => 'wordpress',
            'request_key' => md5(microtime() . rand()),
            'action' => $action,
            'package' => (isset($selected_plan) && (!empty($selected_plan))) ? $selected_plan : "",
            'domain_name' => EN_DISTANCE_BASE_SHIPPING_SERVER_NAME,
            'license_key' => $plugin_license_key,
            'plugin_version' => $pluginVersions["dbsc_plugin_version"],
            'woocommerce_version' => $pluginVersions["woocommerce_plugin_version"],
            'wordpress_version' => get_bloginfo('version'),
        );

        /* Check if URL contains folder */
        if (self::en_check_url_contains_folder()) {
            $postArr['webHookUrl'] = get_site_url();
        }
        $field_string = json_encode($postArr);
        //set url
        $url = esc_url(EN_DISTANCE_BASE_SHIPPING_SUBSCRIPTION_HITTING_URL);
        if (!empty($url) && !empty($field_string)) {
            //set response
            $response = wp_remote_post($url, array(
                    'method' => 'POST',
                    'timeout' => 60,
                    'redirection' => 5,
                    'blocking' => true,
                    'body' => $field_string,
                )
            );
            //get response 
            $output = wp_remote_retrieve_body($response);
            return $output;
        }
    }

    /**
     * Smart street api response curl from server
     * @return array type
     */
    static public function customer_subscription_status()
    {
        self::en_trial_activation_bin();
        $status = self::smart_street_api_curl_request("s");
        $status = json_decode($status, true);

        return $status;
    }

    /**
     * Trial activation of 3dbin.
     */
    static public function en_trial_activation_bin()
    {
        $trial_status = '';
        /* Trial activation code */
        $trial_status_3dbin = get_option('en_trial_dbsc_flag');
        if (!$trial_status_3dbin) {
            $trial_status = self::smart_street_api_curl_request("c", 'TR');
            $response_status = json_decode($trial_status);
            /* Trial Package activated succesfully */
            $subscribed_package = isset($response_status->status->subscribedPackage->subscribedPackageName) && !empty($response_status->status->subscribedPackage->subscribedPackageName);
            if ((isset($response_status->severity) && $response_status->severity == "SUCCESS") || $subscribed_package) {
                update_option('en_trial_dbsc_flag', 1);
            }
            /* Error response */
            if (isset($response_status->severity) && $response_status->severity == "ERROR") {
                /* Do anthing in case of error */
            }
        }
    }

    /**
     * Plans for SBS
     * @param array $packages_list
     * @return string
     */
    static public function packages_list($packages_list)
    {
        $packages_list_arr = array();
        if (isset($packages_list) && (!empty($packages_list))) {

            $packages_list_arr['options']['disable'] = 'Disable (default)';
            foreach ($packages_list as $key => $value) {
                $value['pPeriod'] = (isset($value['pPeriod']) && ($value['pPeriod'] == "Month")) ? "mo" : $value['pPeriod'];
                $value['pHits'] = is_numeric($value['pHits']) ? number_format($value['pHits']) : $value['pHits'];
                $value['pCost'] = is_numeric($value['pCost']) ? number_format($value['pCost'], 2, '.', '') : $value['pCost'];
                $trial = (isset($value['pSCAC']) && $value['pSCAC'] == "TR") ? "(Trial)" : "";
                $packages_list_arr['options'][$value['pSCAC']] = $value['pHits'] . "/" . $value['pPeriod'] . " ($" . number_format($value['pCost']) . ".00)" . " " . $trial;
            }
        }
        return $packages_list_arr;
    }

    /**
     * Ui display for next plan
     * @return string type
     */
    static public function next_subcribed_package()
    {
        self::$next_subcribed_package = (isset(self::$nextSubcribedPackage['nextToBeChargedStatus']) && self::$nextSubcribedPackage['nextToBeChargedStatus'] == 1) ? self::$nextSubcribedPackage['nextSubscriptionSCAC'] : "disable";
        return self::$next_subcribed_package;
    }

    /**
     * Get plan data
     * @return array
     */
    static public function subscribed_package()
    {

        $subscribed_package = self::$subscribedPackage;
        $subscribed_package['packageDuration'] = (isset($subscribed_package['packageDuration']) && ($subscribed_package['packageDuration'] == "Month")) ? "mo" : $subscribed_package['packageDuration'];
        $subscribed_package['packageHits'] = is_numeric($subscribed_package['packageHits']) ? number_format($subscribed_package['packageHits']) : $subscribed_package['packageHits'];
        $subscribed_package['packageCost'] = is_numeric($subscribed_package['packageCost']) ? number_format($subscribed_package['packageCost'], 2, '.', '') : $subscribed_package['packageCost'];
        return $subscribed_package['packageHits'] . "/" . $subscribed_package['packageDuration'] . " ($" . number_format($subscribed_package['packageCost']) . ".00)";
    }

    /**
     * Response from smart street api and set in public attributes
     */
    static function set_curl_res_attributes()
    {

        self::$subscriptionInfo = (isset(self::$status['status']['subscriptionInfo'])) ? self::$status['status']['subscriptionInfo'] : "";
        self::$lastUsageTime = (isset(self::$status['status']['lastUsageTime'])) ? self::$status['status']['lastUsageTime'] : "";
        self::$subscribedPackage = (isset(self::$status['status']['subscribedPackage'])) ? self::$status['status']['subscribedPackage'] : "";
        self::$subscriptionStatus = (isset(self::$status['status']['subscriptionInfo']['subscriptionStatus']) && (self::$status['status']['subscriptionInfo']['subscriptionStatus'] == 1)) ? "yes" : "no";
        self::$subscribedPackageHitsStatus = (isset(self::$status['status']['subscribedPackageHitsStatus'])) ? self::$status['status']['subscribedPackageHitsStatus'] : "";
        self::$nextSubcribedPackage = (isset(self::$status['status']['nextSubcribedPackage'])) ? self::$status['status']['nextSubcribedPackage'] : "";
        self::$statusRequestTime = (isset(self::$status['statusRequestTime'])) ? self::$status['statusRequestTime'] : "";
    }

    /**
     * UI display Current Subscription & Current Usage
     * @param array type $status
     * @return array type
     */
    static public function en_dbsc_subscription($status = array())
    {

        if (isset($status) && (!empty($status)) && (is_array($status))) {
            self::$status = $status;
        } else { /* onload */
            self::$status = self::customer_subscription_status();
            // All plans for 3dbin 
            
            if (isset(self::$status['ListOfPackages']['Info']) 
                && (!empty(self::$status['ListOfPackages']['Info'])) 
                && is_array(self::$status['ListOfPackages']['Info'])) {
                $packages_list = self::packages_list(self::$status['ListOfPackages']['Info']);
            } else {
                $packages_list = array(
                    'options' => array(
                        'disable' => 'Disable (default)'
                    )
                );
            }
        }

        if (isset(self::$status['severity']) && (self::$status['severity'] == "SUCCESS")) {
            self::set_curl_res_attributes();
            if (self::$lastUsageTime == '0000-00-00 00:00:00') {
                self::$lastUsageTime = 'yyyy-mm-dd hrs-min-sec';
            }
            $subscription_time = (isset(self::$subscriptionInfo) && (!empty(self::$subscriptionInfo['subscriptionTime']))) ? "Start date: " . self::formate_date_time(self::$subscriptionInfo['subscriptionTime']) : "NA";
            $status_request_time = (isset(self::$lastUsageTime) && (!empty(self::$lastUsageTime))) ? '(' . self::$lastUsageTime . ')' : "NA";
            $expiry_time = (isset(self::$subscriptionInfo) && (!empty(self::$subscriptionInfo['expiryTime']))) ? "End date: " . self::formate_date_time(self::$subscriptionInfo['expiryTime']) : "NA";
            $subscribed_package = (isset(self::$subscribedPackage) && (!empty(self::$subscribedPackage))) ? self::subscribed_package() : "NA";
            $consumed_hits = (isset(self::$subscribedPackageHitsStatus) && (!empty(self::$subscribedPackageHitsStatus['consumedHits']))) ? self::$subscribedPackageHitsStatus['consumedHits'] : "";
            $available_hits = (isset(self::$subscribedPackageHitsStatus) && (!empty(self::$subscribedPackageHitsStatus['availableHits']))) ? self::$subscribedPackageHitsStatus['availableHits'] . "/" : "NA";
            $consumedHits = (isset(self::$subscribedPackageHitsStatus) && (!empty(self::$subscribedPackageHitsStatus['consumedHits']))) ? self::$subscribedPackageHitsStatus['consumedHits'] . "/" : "0/";
            $consumed_hits_prcent = (isset(self::$subscribedPackageHitsStatus) && (!empty(self::$subscribedPackageHitsStatus['consumedHitsPrcent']))) ? self::$subscribedPackageHitsStatus['consumedHitsPrcent'] . "%" : "0%";
            $package_hits = (isset(self::$subscribedPackageHitsStatus) && (!empty(self::$subscribedPackageHitsStatus['packageHits']))) ? self::$subscribedPackageHitsStatus['packageHits'] : "/NA";
            $next_subcribed_package = (isset(self::$nextSubcribedPackage) && (!empty(self::$nextSubcribedPackage))) ? self::next_subcribed_package() : "NA";
            if (self::$subscriptionStatus == "yes") {
                $current_subscription = '<span id="subscribed_package">' . $subscribed_package . '</span>'
                    . '&nbsp;&nbsp;&nbsp; '
                    . '<span id="subscription_time">' . $subscription_time . '</span>'
                    . '&nbsp;&nbsp;&nbsp;'
                    . '<span id="expiry_time">' . $expiry_time . '</span><br>' . self::$append_with_current_status;
                $current_usage = '<span id="subscribed_package_status">' . $consumedHits . $package_hits . '</span> '
                    . '&nbsp;&nbsp;&nbsp;'
                    . '<span id="consumed_hits_prcent">' . $consumed_hits_prcent . '</span>'
                    . '&nbsp;&nbsp;&nbsp;'
                    . '<span id="status_request_time">' . $status_request_time . '</span>';
                self::$subscription_packages_response = "yes";
            } else {
                $current_subscription = '<span id="subscribed_package">Your current subscription is expired.</span><br>';
                $current_usage = 'Not available.<br>';
                self::$subscription_packages_response = "no";
            }
        } else {
            $current_subscription = '<span id="subscribed_package">Not subscribed.</span><br>'. self::$append_with_current_status;
            $current_usage = 'Not available.<br>';
//          when no plan exist plan go to dislable 
            $next_subcribed_package = "disable";
            self::$subscription_packages_response = "no";
        }

        update_option("subscription_packages_response", self::$subscription_packages_response);

        self::$subscription_details = array('current_usage' => (isset($current_usage)) ? $current_usage : "",
            'current_subscription' => (isset($current_subscription)) ? $current_subscription : "",
            'next_subcribed_package' => (isset($next_subcribed_package)) ? $next_subcribed_package : "",
            'packages_list' => (isset($packages_list)) ? $packages_list : "",
            'subscription_packages_response' => (isset(self::$subscription_packages_response)) ? self::$subscription_packages_response : "");
        return self::$subscription_details;
    }

    /**
     * Formate the given date time @param $datetime like in sow
     * @param datetime $datetime
     * @return string
     */
    static public function formate_date_time($datetime)
    {

        $date = date_create($datetime);
        return date_format($date, "M. d, Y");
    }

    /**
     * Connection Settings Detail Set
     * @return array
     */
    static public function en_set_connection_settings_detail()
    {
        // don't load the settings for other plugins
        if (isset($_GET['tab']) && $_GET['tab'] != EN_DISTANCE_BASE_SHIPPING_ID) {
            return [];
        }

        extract(self::en_dbsc_subscription());
        if (!empty(self::$status['statusRequestDomain']) && 'staging' == self::$status['statusRequestDomain']) {
            $auto_renew_desc = 'Plan change is only allowed on your production site. You are currently on the staging site. Kindly switch to your production environment to select a plan.';
        }else{
            $auto_renew_desc = '';
        }
        return
            [
                'en_connection_settings_license_key_distance_base_shipping' => [
                    'eniture_action' => 'licenseKey',
                    'name' => __('Eniture API Key ', 'woocommerce-settings-distance-base-shipping'),
                    'type' => 'text',
                    'id' => 'en_connection_settings_license_key_distance_base_shipping'
                ],
                'en_connection_settings_description_distance_base_shipping' => [
                    'eniture_action' => '',
                    'name' => __('', 'woocommerce-settings-distance-base-shipping'),
                    'type' => 'text',
                    'class' => 'hidden',
                    'desc' => __('The plugin will retrieve the distance between the ship-from and ship-to address. The usage of your current subscription is incremented each time a lookup is performed. The next subscription begins when the current one expires or is depleted, which ever comes first. Refer to the <a href="https://eniture.com/woocommerce-distance-based-shipping-calculator/#documentation" target="_blank">User Guide</a> for more detailed information.', 'woocommerce-settings-distance-base-shipping'),
                    'id' => 'en_connection_settings_description_distance_base_shipping'
                ],
                'en_connection_settings_auto_renew_distance_base_shipping' => [
                    'eniture_action' => '',
                    'name' => __('Auto-renew', 'woocommerce-settings-distance-base-shipping'),
                    'type' => 'select',
                    'default' => $next_subcribed_package,
                    'id' => 'en_connection_settings_auto_renew_distance_base_shipping',
                    'class' => 'en_connection_settings_auto_renew_distance_base_shipping',
                    'desc' => $auto_renew_desc,
                    'options' => $packages_list['options']
                ],
                'en_connection_settings_current_subscription_distance_base_shipping' => [
                    'eniture_action' => '',
                    'name' => __('Current plan', 'woocommerce-settings-distance-base-shipping'),
                    'type' => 'text',
                    'class' => 'hidden',
                    'desc' => '100/mo   ($5.00)&nbsp;&nbsp;&nbsp; Start date: Feb. 13, 2020 &nbsp;&nbsp;&nbsp; End date: Mar. 13, 2020',
                    'desc' => $current_subscription,
                    'id' => 'en_connection_settings_current_subscription_distance_base_shipping'
                ],
                'en_connection_settings_current_usage_distance_base_shipping' => [
                    'eniture_action' => '',
                    'name' => __('Current usage', 'woocommerce-settings-distance-base-shipping'),
                    'type' => 'text',
                    'class' => 'hidden',
                    'desc' => '25/100 &nbsp;&nbsp;&nbsp; 25.00% &nbsp;&nbsp;&nbsp;(2020-02-10 14:05:15)',
                    'desc' => $current_usage,
                    'id' => 'en_connection_settings_current_usage_distance_base_shipping'
                ],
                'en_connection_settings_suspend_distance_base_shipping' => [
                    'eniture_action' => '',
                    'name' => __('Suspend use', 'woocommerce-settings-distance-base-shipping'),
                    'type' => 'checkbox',
                    'id' => 'en_connection_settings_suspend_distance_base_shipping',
                    'desc' => __(' ', 'woocommerce-settings-distance-base-shipping'),
                    'class' => 'en_connection_settings_suspend_distance_base_shipping'
                ],
                'en_connection_settings_next_subcribed_package' => [
                    'eniture_action' => '',
                    'name' => __('', 'woocommerce-settings-distance-base-shipping'),
                    'type' => 'text',
                    'class' => "hidden",
                    'placeholder' => $next_subcribed_package,
                    'id' => "en_connection_settings_next_subcribed_package",
                ],
                'en_connection_settings_subscription_status_distance_base_shipping' => [
                    'eniture_action' => '',
                    'name' => __('', 'woocommerce-settings-distance-base-shipping'),
                    'type' => 'text',
                    'class' => "hidden",
                    'placeholder' => self::$subscriptionStatus,
                    'id' => "en_connection_settings_subscription_status_distance_base_shipping",
                ]
            ];
    }

}
