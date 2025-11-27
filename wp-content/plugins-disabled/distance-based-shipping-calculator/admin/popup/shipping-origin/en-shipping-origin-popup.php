<?php

namespace EnDistanceBaseShippingOriginPopup;

class EnDistanceBaseShippingOriginPopup
{

    public function __construct()
    {
        $this->shippingOriginPopup();
    }

    public function shippingOriginPopup()
    {
        $eniture_plugins = get_option('EN_Plugins');
        $eniture_plugins_dec = $eniture_plugins ? json_decode($eniture_plugins, true) : [];
        if (($dbsc_key = array_search('distance_base_shipping', $eniture_plugins_dec)) !== false) {
            unset($eniture_plugins_dec[$dbsc_key]);
        }
        ?>
        <!-- Add Popup for new shipping origin -->
        <div class="en-add-shipping-origin-popup" id="en-add-shipping-origin-popup" style="display: none;">
            <div class="en-dbsc-popup_container">
                <div class="en-dbsc-popup_header">
                    <h2>Add shipping origin</h2>
                    <a class="close en_hide_popup" href="#">×</a>
                </div>
                <div class="en-dbsc-popup_body">
                    </form>
                    <form method="post" id="en-dbsc-shipping-origin-form">
                        <div class="form-field">
                            <label>Nickname</label>
                            <input type="text" name="en_dbsc_shipping_origin_nickname"
                                   id="en_dbsc_shipping_origin_nickname" class="en_dbsc_shipping_origin_nickname"
                                   title="Nickname" placeholder=""/>
                        </div>
                        <div class="form-field">
                            <label>Street address</label>
                            <input type="text" name="en_dbsc_shipping_origin_street_address"
                                   id="en_dbsc_shipping_origin_street_address"
                                   class="en_dbsc_shipping_origin_street_address" title="Street Address"
                                   placeholder="6180 Buffington Road"/>
                        </div>
                        <div class="form-field">
                            <label>City</label>
                            <input type="text" name="en_dbsc_shipping_origin_city" id="en_dbsc_shipping_origin_city"
                                   class="en_dbsc_shipping_origin_city" title="City" placeholder="Atlanta"/>
                        </div>
                        <div class="form-field">
                            <label>State or Province</label>
                            <input type="text" name="en_dbsc_shipping_origin_state"
                                   id="en_dbsc_shipping_origin_state"
                                   class="en_dbsc_shipping_origin_state" title="State or Province" maxlength="16"
                                   placeholder="GA"/>
                        </div>
                        <div class="form-field">
                            <label>Postal code</label>
                            <input type="text" name="en_dbsc_shipping_origin_postal_code"
                                   id="en_dbsc_shipping_origin_postal_code"
                                   class="en_dbsc_shipping_origin_postal_code" title="Postal code" maxlength="8"
                                   placeholder="30349"/>
                        </div>
                        <div class="form-field">
                            <label>Country code</label>
                            <input type="text" name="en_dbsc_shipping_origin_country_code"
                                   id="en_dbsc_shipping_origin_country_code"
                                   class="en_dbsc_shipping_origin_country_code" title="Country code" placeholder="US"/>
                        </div>
                        <!-- New Change -->
                        <div class="form-field en_add_the_shipping_origin">
                            <label>Add the shipping origin</label>
                            <input type="radio" name="en_add_the_shipping_origin"
                                   value="en_to_this_shipping_from_profile" title=""/> To this Shipping From profile
                            <br>
                            <input type="radio" name="en_add_the_shipping_origin"
                                   value="en_as_a_new_shipping_from_profile" title="As a new Shipping From profile"/> As
                            a new
                            Shipping From profile
                        </div>
                        <input type="hidden" id="en_add_the_shipping_origin_id" name="en_add_the_shipping_origin_id"
                               value="0">
                        <input type="hidden" id="en_origin_order" name="en_origin_order" value="0">
                        <!-- New Change End -->

                        <?php if (is_array($eniture_plugins_dec) && count($eniture_plugins_dec) > 0) { ?>
                            <div class="form-field">
                                <label>Availability in other plugins by Eniture Technology</label>
                                <select name="en_dbsc_shipping_origin_available_in_other"
                                        class="en_dbsc_shipping_origin_available_in_other">
                                    <option value="not_available" selected="selected">Not available</option>
                                    <option value="available_in_warehouse">Available as a warehouse</option>
                                    <option value="available_in_dropship">Available as a drop ship location</option>
                                </select>
                            </div>
                        <?php } ?>
                        <input type="hidden" name="en_shipping_suggestion_flag" id="en_shipping_suggestion_flag"
                               class="en_shipping_suggestion_flag" value="">
                        <input type="hidden" name="en_origin_profile_id" class="en_origin_profile_id" value="">
                        <input type="hidden" name="en_shipping_origin_action" class="en_shipping_origin_action"
                               value="">
                        <input type="hidden" name="en_shipping_origin_id" class="en_shipping_origin_id" value="">
                    </form>
                </div>
                <div class="en-dbsc-popup_footer">
                    <a href="javascript:;" class="button-primary en-dbsc-cancel-button en_hide_popup">Cancel</a>
                    <a href="" class="button-primary en-dbsc-Done-button" id="en-add-shipping-origin">Save</a>
                </div>
            </div>
        </div>
        <?php
    }
}
