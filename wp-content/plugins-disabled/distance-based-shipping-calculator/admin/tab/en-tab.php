<?php

/**
 * App Name tabs.
 */

use EnDistanceBaseShippingConnectionSettings\EnDistanceBaseShippingConnectionSettings;
use EnDistanceBaseShippingCarriers\EnDistanceBaseShippingCarriers;
use EnDistanceBaseShippingOtherSettings\EnDistanceBaseShippingOtherSettings;

if (!class_exists('EnDistanceBaseShippingTab')) {

    /**
     * Tabs show on admin side.
     * Class EnDistanceBaseShippingTab
     */
    class EnDistanceBaseShippingTab extends WC_Settings_Page
    {

        /**
         * Hook for call.
         */
        public function en_load()
        {
            $this->id = 'distance_base_shipping';
            add_filter('woocommerce_settings_tabs_array', [$this, 'add_settings_tab'], 50);
            add_action('woocommerce_sections_' . $this->id, [$this, 'output_sections']);
            add_action('woocommerce_settings_' . $this->id, [$this, 'output']);
            add_action('woocommerce_settings_save_' . $this->id, [$this, 'save']);
            add_action('woocommerce_settings_tabs_array', [$this, 'en_woo_addons_popup_notifi_disabl_to_plan_dbsc'], 10);
        }

        /**
         * Setting Tab For Woocommerce
         * @param $settings_tabs
         * @return string
         */
        public function add_settings_tab($settings_tabs)
        {
            $settings_tabs[$this->id] = __('Distance Shipping Calculator', 'woocommerce-settings-distance-base-shipping');
            return $settings_tabs;
        }

        /**
         * Setting Sections
         * @return array
         */
        public function get_sections()
        {
            $sections = array(
                '' => __('Connection Settings', 'woocommerce-settings-distance-base-shipping'),
                'section-1' => __('Shipping Rates', 'woocommerce-settings-distance-base-shipping'),
                'section-2' => __('Other Settings', 'woocommerce-settings-distance-base-shipping'),
                // fdo va
                'section-3' => __('FreightDesk Online', 'woocommerce-settings-distance-base-shipping'),
                'section-4' => __('Validate Addresses', 'woocommerce-settings-distance-base-shipping'),
                'section-5' => __('User Guide', 'woocommerce-settings-distance-base-shipping'),
            );

            return apply_filters('woocommerce_get_sections_' . $this->id, $sections);
        }

        /**
         * Display all pages on wc settings tabs
         * @param $section
         * @return array
         */
        public function get_settings($section = null)
        {
            ob_start();
            switch ($section) {
                case 'section-1' :
                    echo '<div class="en_dbsc_shipping_rates en_dbsc_wrapper">';
                    new \EnDistanceBaseShippingZonePopup\EnDistanceBaseShippingZonePopup();
                    new \EnDistanceBaseShippingOriginPopup\EnDistanceBaseShippingOriginPopup();
                    new \EnDistanceBaseAddRatePopup\EnDistanceBaseAddRatePopup();
                    new \EnDistanceBaseShippingProfilePopup\EnDistanceBaseShippingProfilePopup();
                    $settings = (new EnDistanceBaseShippingQuoteSettings\EnDistanceBaseShippingQuoteSettings)->Load();
                    break;

                case 'section-2' :
                    echo '<div class="en_dbsc_other_settings en_dbsc_wrapper">';
                    $settings = EnDistanceBaseShippingOtherSettings::en_load();
                    break;
                // fdo va
                case 'section-3' :
                    echo '<div class="en_dbsc_freightdesk_online en_dbsc_wrapper">';
                    \EnDistanceBaseFreightdeskonline\EnDistanceBaseFreightdeskonline::en_load();
                    $settings = [];
                    break;

                case 'section-4' :
                    echo '<div class="en_dbsc_validate_address en_dbsc_wrapper">';
                    \EnDistanceBaseValidateaddress\EnDistanceBaseValidateaddress::en_load();
                    $settings = [];
                    break;

                case 'section-5' :
                    \EnDistanceBaseShippingUserGuide\EnDistanceBaseShippingUserGuide::en_load();
                    $settings = [];
                    break;
                default:
                    echo '<div class="en_dbsc_connection_settings en_dbsc_wrapper">';
                    $settings = EnDistanceBaseShippingConnectionSettings::en_load();
                    break;
            }

            return apply_filters('woocommerce-settings-distance-base-shipping', $settings, $section);
        }

        /**
         * Popup notification for using notification show during disable to plan through using jquery
         * @return html
         */
        public function en_woo_addons_popup_notifi_disabl_to_plan_dbsc()
        {
            ?>
            <div id="plan_confirmation_popup" class="sm_notification_disable_to_plan_overlay_dbsc"
                 style="display: none;">
                <div class="en_dbsc_notifi_disabl_to_plan_box">
                    <h2 class="del_hdng">
                        Note!
                    </h2>
                    <p class="confirmation_p">
                        Note! You have selected to enable the Distance base shipping method. By confirming this selection
                        you will be charged for the <span id="selected_plan_popup_box">[plan]</span> plan. To ensure
                        service continuity the plan will automatically renew each month, or when the plan is depleted,
                        whichever comes first. You can change which plan is put into effect on the next renewal date by
                        updating the selection on this page at anytime.
                    </p>
                    <div class="confirmation_btns">
                        <a style="cursor: pointer" class="cancel_plan">Cancel</a>
                        <a style="cursor: pointer" class="confirm_plan">OK</a>
                    </div>
                </div>
            </div>
            <?php
        }

        /**
         * WooCommerce Settings Tabs
         * @global $current_section
         */
        public function output()
        {
            global $current_section;
            $settings = $this->get_settings($current_section);
            WC_Admin_Settings::output_fields($settings);
        }

        /**
         * Woocommerce save Settings
         * @global $current_section
         */
        public function save()
        {
            global $current_section;
            $settings = $this->get_settings($current_section);
            WC_Admin_Settings::save_fields($settings);
        }

    }

    $en_tab = new EnDistanceBaseShippingTab();
    return $en_tab->en_load();
}
