<?php
/**
 *  Shipping rates template.
 */

namespace EnDistanceBaseShippingQuoteSettings;

use EnDistanceBaseShippingRatesTemplateSettings\EnDistanceBaseShippingRatesTemplateSettings;
use WC_Shipping_Zone;
use WC_Shipping_Zones;

class EnDistanceBaseShippingQuoteSettings
{

    public $profile_id;
    public $origin_id;
    public $origin_order;
    public $zone_id;
    public $rate_id;
    public $currency;
    public $dim_unit;
    public $wt_unit;

    /**
     * Quote Settings Html
     * @return array
     */
    public function Load()
    {
        $this->currency = get_woocommerce_currency_symbol();
        $this->dim_unit = 'in';
        $this->wt_unit = 'lbs';
        echo '</form><div id="en_distance_base_shipping_quote_settings">';
        $en_profile_type = 'general';
        $template_settings = new EnDistanceBaseShippingRatesTemplateSettings();
        $en_profiles = $template_settings->getAllProfiles();

        $this->getShippingProfilesHtml($en_profiles);
        return [];
    }

    public function getShippingProfilesHtml($profiles)
    {
        ?>

        <div class="en_distance_base_shipping_quote_container">
        <div class="en_distance_base_shipping_quote_error" style="display: block;">
        </div>

        <div class="en_shipping_profile">
            <div class="en_shipping_profile_content">
                <h2>Shipping Profiles</h2>
                <span class="en-add-shipping-profile">
                        <a href="#en_dbsc_add_profile" onclick="en_dbsc_add_profile()" title="Create New Profile">Create new profile</a>
                    </span>
            </div>
        </div>

        <?php
        
        foreach ($profiles as $key => $profile) {
            $this->profile_id = $profile->id;
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
                                    <a href="javascript:;"
                                       onclick="en_dbsc_edit_profile(<?php echo $this->profile_id; ?>)">Edit</a>
                                </li>
                                <?php if ($this->profile_id != 1) { ?>
                                    <li>
                                        <a href="javascript:;"
                                           onclick="en_dbsc_delete_record(<?php echo "0,0," . $this->profile_id . ",'profile'"; ?>)">Delete</a>
                                    </li>
                                <?php } ?>
                            </ul>
                        </div>
                    </div>
                    <h2><?php echo $profile->profile_nickname; ?></h2>
                    <?php $this->getShippingClassesHtml($profile) ?>
                </div>
            </div>
        <?php } ?>

        <div class="en_append_new_profile"></div>

        <?php
        $this->getDeletePopupHtml();
    }

    public function getShippingClassesHtml($profile)
    {
        (isset($profile->profile_id)) ? $this->profile_id = $profile->profile_id : '';
        ?>

        <div class="en_profile_classes" id="en-edit-profile-classes-<?php echo $this->profile_id; ?>">

            <?php
            
            if (isset($profile->classes_details) && !empty($profile->classes_details) && is_array($profile->classes_details)) {
                $names_arr = array_column($profile->classes_details, 'name');
                echo implode(', ', $names_arr);
            } else {
                ?>
                <p>All products not in other profiles</p>
            <?php } ?>
        </div>
        <?php
        $this->getShippingOriginsHtml($profile);
    }

    public function getShippingOriginsHtml($profile)
    {
        // New Change
        if (isset($profile->origin_details) && !empty($profile->origin_details)) {
            $used_origions = [];

            foreach ($profile->origin_details as $key => $origin) {
                $this->origin_id = $origin->id;
                $this->origin_order = $origin->origin_order;
                if (in_array($this->origin_id, $used_origions) || (!$this->origin_order > 0)) {
                    continue;
                }
                ?>
                <!-- New Change -->
                <div class="en_shipping_from en-origin-order-<?php echo $this->origin_order; ?>">
                    <span class="en-common-origin-order" value="<?php echo $this->origin_order; ?>"></span>
                    <h2>Shipping from</h2>
                    <span class="en-add-shipping-origin">
                    <a href="#en_dbsc_add_shipping_origin"
                       onclick="en_dbsc_add_shipping_origin('<?php echo $this->profile_id; ?>','<?php echo $this->origin_id; ?>','<?php echo $this->origin_order; ?>')"
                       title="Add Shipping Origin">Add shipping origin</a>
                </span>
                    <div class="en-shipping-origin-list-<?php echo $this->profile_id; ?>">
                        <?php
                        global $wpdb;
                        $en_table = $wpdb->prefix . 'en_shipping_origins';
                        $origins_order_query = "SELECT * FROM $en_table WHERE profile_id =  $this->profile_id AND origin_order =  $this->origin_order";
                        $origins_list = $wpdb->get_results($origins_order_query);
                        foreach ($origins_list as $origins_list_id => $origins_list_data) {
                            $used_origions[] = $origins_list_data->id;
                            $used_origions[] = $this->origin_id;
                            ?>
                            <div class="en-shipping-origin-list-item">
                                <p class="en-shipping-origin-list-item-<?php echo $origins_list_data->id ?>"><?php echo $origins_list_data->nickname; ?>
                                    <br>
                                    <?php echo $origins_list_data->street_address; ?>
                                    , <?php echo $origins_list_data->city; ?> <?php echo $origins_list_data->state; ?> <?php echo $origins_list_data->postal_code; ?>
                                    ,<?php echo $origins_list_data->country_code; ?>
                                </p>

                                <span class="en-right-sec">
                                    <a href="javascript:;" class="en-menu-popup"
                                       onclick="en_toggle_menu(this)">&hellip;</a>
                                </span>
                                <div class="en-menu-options">
                                    <ul>
                                        <li>
                                            <a href="javascript:;"
                                               onclick="en_edit_dbsc_shipping_origin('<?php echo $this->profile_id; ?>', '<?php echo $origins_list_data->id; ?>', '<?php echo $this->origin_order; ?>')">Edit</a>
                                        </li>
                                        <li><a href="javascript:;"
                                               onclick="en_dbsc_delete_record(<?php echo "0," . $this->profile_id . "," . $origins_list_data->id . ",'origin'"; ?>)">Delete</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <?php
                        }
                        if (isset($origin->zones_details) && !empty($origin->zones_details)) {

                            $this->getShippingZonesHtml($origin->zones_details);
                        } else {
                            $this->getShippingZonesHtml([]);
                        }
                        // New change
                        ?>
                    </div>
                </div>
                <?php
            }
        } else {
            // New Change
            ?>
            <div class="en_shipping_from en_no_ship_location">
                <h2>Shipping from</h2>
                <span class="en-add-shipping-origin">
                    <a href="#en_dbsc_add_shipping_origin"
                       onclick="en_dbsc_add_shipping_origin('<?php echo $this->profile_id; ?>','0','1')"
                       title="Add Shipping Origin">Add shipping origin</a>
                </span>
                <div class="en-shipping-origin-list-<?php echo $this->profile_id; ?>">
                    <p>No shipping location defined</p>
                </div>
            </div>
            <?php
        }
    }

    public function getShippingZonesHtml($zones_details)
    {
        ?>

        <div class="en_shipping_to">
            <h2>Shipping To</h2>
            <span class="en-add-shipping-zone">
                    <a href="#en_dbsc_add_shipping_zone"
                       onclick="en_dbsc_add_shipping_zone(<?php echo "'$this->profile_id', '$this->origin_id'" ?>)"
                       title="Add Shipping Zone">Add shipping zone</a>
                </span>
            <?php
            foreach ($zones_details as $key => $zone) {
                $this->zone_id = $zone->zone_id;
                $WC_Shipping_Zone = new WC_Shipping_Zone($this->zone_id);
                $get_formatted_location = $WC_Shipping_Zone->get_formatted_location();
                ?>

                <div class="en-zone-section en_zone_details<?php echo $this->zone_id ?>">
                    <div class="en-zone-header full-width">
                        <div class="zone_left">
                            <b><?php echo $zone->zone_name ?></b></br><?php echo $get_formatted_location ?>
                        </div>

                        <div class="zone_right">
                                <span>
                                    <a href="javascript:;" class="en-menu-popup"
                                       onclick="en_toggle_menu(this)">&hellip;</a>
                                </span>
                            <div class="en-menu-options">
                                <ul>
                                    <li>
                                        <a href="javascript:;"
                                           onclick="en_dbsc_edit_shipping_zone(<?php echo $this->profile_id . ', ' . $this->origin_id . ', ' . $this->zone_id; ?>)">Edit</a>
                                    </li>
                                    <li><a href="javascript:;"
                                           onclick="en_dbsc_delete_record(<?php echo $this->profile_id . "," . $this->origin_id . "," . $this->zone_id . ",'zone')"; ?>">Delete</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="en_shipping_to_table_container full-width">
                        <table class="en_shipping_rate" id="en_shipping_rate">
                            <tr>
                                <th>Display as</th>
                                <th class="en-align-center">Rate</th>
                                <th class="en-align-center">Distance measured by</th>
                                <th class="en-align-center">Distance</th>
                                <th class="en-align-center">Weight</th>
                                <th class="en-align-center">And </br>/ Or</th>
                                <th class="en-align-center">Length</th>
                                <th class="en-align-center">Quote</th>
                                <th class="en-align-center">Action</th>
                            </tr>
                            <?php
                            
                            if (isset($zone->rate_details) && !empty($zone->rate_details)) {
                                $this->getShippingRatesHtml($zone->rate_details);
                            }
                            ?>
                        </table>
                        
                    </div>
                    <a href="javascript:;" class="button-primary" title="Add Rate"
                       onclick="en_dbsc_add_rate(<?php echo $this->profile_id . "," . $this->zone_id; ?>)">Add rate</a>
                </div>
            <?php } ?>
        </div>
        <?php
    }

    public function getShippingRatesHtml($rate_details)
    {
        foreach ($rate_details as $key => $rate) {
            $this->rate_id = $rate->id;
            $calculate_for = ($rate->calculate_for == 'item_after_quotes') ? 'Item' : ucfirst($rate->calculate_for);
            $calculate_for_show = ($rate->calculate_for == 'flat') ? $this->currency .$rate->rate : $this->currency .$rate->rate.' / '. $calculate_for;
            if(empty($rate->min_quote)){
                $rate->min_quote = '0.00';
            }elseif (is_numeric($rate->min_quote)){
                $rate->min_quote = number_format($rate->min_quote, 2, ".", "");
            }
            $rate->min_length = empty($rate->min_length) ? '0' : $rate->min_length;
            $rate->min_weight = empty($rate->min_weight) ? '0' : $rate->min_weight;
            $rate->min_distance = empty($rate->min_distance) ? '0' : $rate->min_distance;
            ?>

        <tr id="en_zone<?php echo $this->zone_id ?>_rate<?php echo $this->rate_id ?>">
                <td>
                    <span class="en_display_as_collection"><?php echo $rate->display_as . '</span> <div>' . $rate->description ?></div></td>
            <td class="en-align-center"><?php echo $calculate_for_show ?></td>
            <td class="en-align-center"><?php echo $rate->measured_by == 'route' ? 'Route' : 'Straight Line' ?></td>
            <td class="en-align-center"><?php echo $rate->min_distance . " " . $rate->unit . "-" . ($rate->max_distance > 0 ? $rate->max_distance . " " . $rate->unit : 'up') ?></td>
            <td class="en-align-center"><?php echo $rate->min_weight . " " . $this->wt_unit . "-" . ($rate->max_weight > 0 ? $rate->max_weight . " " . $this->wt_unit : 'up')  ?></td>
            <td class="en-align-center"><?php echo ucfirst($rate->rate_condition) ?></td>
            <td class="en-align-center"><?php echo $rate->min_length . " " . $this->dim_unit . "-" . ($rate->max_length > 0 ? $rate->max_length . " " . $this->dim_unit : 'up')  ?></td>
            <td class="en-align-center"><?php echo $rate->min_quote . "-" . ($rate->max_quote > 0 ? $rate->max_quote : 'up') ?></td>
            <td class="en-align-center">
                <div class="edit-rate-popup action-btn">
                            <span>
                                <a href="javascript:;" class="en-menu-popup" onclick="en_toggle_menu(this)">&hellip;</a>
                            </span>
                    <div class="en-menu-options">
                        <ul>
                            <li>
                                <a href="javascript:;"
                                   onclick="en_edit_dbsc_rate(<?php echo $this->profile_id . ', ' . $this->zone_id . ', ' . $this->rate_id; ?>)">Edit</a>
                            </li>
                            <li><a href="javascript:;"
                                   onclick="en_dbsc_delete_record(<?php echo $this->profile_id . "," . $this->zone_id . ',' . $this->rate_id . ",'rate')"; ?>">Delete</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </td>
            </tr>
            <?php
        }
    }

    public function getDeletePopupHtml()
    {
        ?>
        <!-- delete profile popup -->
        <div class="en-add-rate-popup" id="en-delete-confirm-popup" style="display: none;">
            <div class="en-dbsc-popup_container">
                <div class="en-dbsc-popup_header">
                    <h2>Delete confirmation</h2>
                    <a class="close en_hide_popup" href="#">×</a>
                </div>
                <div class="en-dbsc-popup_body">
                    <h3>Are you sure you want to delete the <span class="en_delete_action_to_record">record</span>?</h3>
                    
                    <form method="post" id="en_delete_record_form">
                        <input type="hidden" name="en_profile_id" class="en_hidden_profile_id" value=""/>
                        <input type="hidden" name="en_origin_id" class="en_hidden_origin_id" value=""/>
                        <input type="hidden" name="en_zone_id" class="en_hidden_zone_id" value=""/>
                        <input type="hidden" name="en_rate_id" class="en_hidden_rate_id" value=""/>
                        <input type="hidden" name="en_action_for" class="en_action_for" value=""/>
                    </form>
                </div>
                <div class="en-dbsc-popup_footer">
                    <a href="javascript:;" class="button-primary en-dbsc-cancel-button en_hide_popup">Cancel</a>
                    <a href="javascript:;" class="button-primary en-delete-record"
                       onclick="en_dbsc_delete_record_action()">Delete</a>
                </div>
            </div>
        </div>
        <?php
    }

}
