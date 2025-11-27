<?php

namespace EnDistanceBaseAddRatePopup;

class EnDistanceBaseAddRatePopup {

    public function __construct() {
        $this->shippingAddRate();
    }

    public function shippingAddRate() {
        ?>
        <!-- Add Popup for new add rate -->
        <div class="en-add-rate-popup" id="en-add-rate-popup" style="display: none;">
            <div class="en-dbsc-popup_container">
                <div class="en-dbsc-popup_header">
                    <h2>Add rate</h2>
                    <a class="close en_hide_popup" href="#">×</a>
                </div>
                <div class="en-dbsc-popup_body">
                    </form>
                    <form method="post" id="en-dbsc-add-rate-form">

                        <div class="form-field">
                            <label>Display as</label>
                            <input type="text" name="en_dbsc_add_rate_display_as"
                                   id="en_dbsc_add_rate_display_as" class="en_dbsc_add_rate_display_as"
                                   title="Display As" placeholder=""/>
                            <span>Customers will see this at checkout</span>
                        </div>

                        <div class="form-field mt-15">
                            <label>Rate description preferences:</label>
                            <input type="radio" id="en_dbsc_add_rate_distance_display_preference_no" name="en_dbsc_add_rate_distance_display_preference" checked="checked"
                                   value="no"/>Don't display a description with the Display As label.
                        </div>
                        <div class="form-field">
                            <input type="radio" id="en_dbsc_add_rate_distance_display_preference_distance" name="en_dbsc_add_rate_distance_display_preference" value="distance"/>Display the distance between the ship-from and ship-to address.
                        </div>
                        <div class="form-field">
                            <input type="radio" id="en_dbsc_add_rate_distance_display_preference_description" name="en_dbsc_add_rate_distance_display_preference" value="description"/>Display the custom description entered in the field below.
                        </div>

                        <div class="form-field">
                            <label>Description</label>
                            <textarea name="en_dbsc_add_rate_description" id="en_dbsc_add_rate_description"
                                      class="en_dbsc_add_rate_description" title="Description"></textarea>
                        </div>
                        <div class="en-dbsc-inline-div">
                            <div class="en-dbsc-first-section-div">
                                <div class="form-field">
                                    <label>Address Type</label>
                                    <select name="en_dbsc_address_type"
                                            id="en_dbsc_address_type" class="en_dbsc_address_type" title="Address Type">
                                        <option value="both"> Commercial and residential</option>
                                        <option value="commercial"> Commercial</option>
                                        <option value="residential"> Residential</option>
                                    </select>
                                </div>
                            </div>
                            <div class="en-dbsc-second-section-div en-dbsc-default-address-type-rb">
                                <div class="form-field">
                                    <label>Default unknown address type to</label>
                                    <input type="radio" id="en_dbsc_default_unknown_address_type_commercial" name="en_dbsc_default_unknown_address_type" value="commercial" checked="checked"/>
                                    Commercial
                            &nbsp;&nbsp;<input type="radio" id="en_dbsc_default_unknown_address_type_residential" name="en_dbsc_default_unknown_address_type" value="residential"/> Residential
                                </div>
                            </div>
                        </div>
                        <div class="en-dbsc-inline-div">
                            <div class="en-dbsc-first-section-div">
                                <div class="en-dbsc-first-section-div">
                                    <div class="form-field">
                                        <label>Base Amount</label>
                                        <input type="text" name="en_dbsc_add_rate_base_amount"
                                            id="en_dbsc_add_rate_base_amount" class="en_dbsc_add_rate_base_amount"
                                            title="Base Amount" placeholder=""/>
                                    </div>
                                </div>
                                <div class="en-dbsc-second-section-div">
                                    <div class="form-field">
                                        <label>Rate</label>
                                        <input type="text" name="en_dbsc_add_rate_per_mile"
                                                id="en_dbsc_add_rate_per_mile" class="en_dbsc_add_rate_per_mile"
                                                title="Rate" placeholder=""/>
                                    </div>
                                </div>
                            </div>
                            <div class="en-dbsc-second-section-div">
                                <div class="en-dbsc-first-section-div">
                                <div class="form-field">
                                        <label>Distance unit</label>
                                        <select name="en_dbsc_add_rate_unit"
                                                id="en_dbsc_add_rate_unit" class="en_dbsc_add_rate_unit" title="Distance unit">
                                            <option value="mi"> Mile</option>
                                            <option value="km"> Kilometer</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="en-dbsc-second-section-div">
                                    <div class="form-field">
                                        <label>Distance measured by</label>
                                        <select name="en_dbsc_add_rate_measured_by"
                                                id="en_dbsc_add_rate_measured_by" class="en_dbsc_add_rate_measured_by" title="Distance measured by">
                                            <option value="route">Route</option>
                                            <option value="straightline"> Straight Line</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="en-dbsc-inline-div">
                            <div class="en-dbsc-first-section-div">
                                <div class="form-field">
                                    <label>Distance: Greater than or equal to</label>
                                    <input type="text" name="en_dbsc_add_rate_min_distance"
                                           id="en_dbsc_add_rate_min_distance" class="en_dbsc_add_rate_min_distance"
                                           title="Distance: Greater than or equal to" placeholder=""/>
                                </div>
                            </div>
                            <div class="en-dbsc-second-section-div">
                                <div class="form-field">
                                    <label>Distance: Less than</label>
                                    <input type="text" name="en_dbsc_add_rate_max_distance"
                                           id="en_dbsc_add_rate_max_distance" class="en_dbsc_add_rate_max_distance"
                                           title="Distance: Less than" placeholder=""/>
                                </div>
                            </div>
                        </div>

                        <div class="en-dbsc-inline-div">
                            <div class="en-dbsc-first-section-div">
                                <div class="form-field">
                                    <label>Weight: Greater than or equal to</label>
                                    <input type="text" name="en_dbsc_add_rate_min_weight"
                                           id="en_dbsc_add_rate_min_weight" class="en_dbsc_add_rate_min_weight"
                                           title="Weight: Greater than or equal to" placeholder=""/>
                                </div>
                            </div>
                            <div class="en-dbsc-second-section-div">
                                <div class="form-field">
                                    <label>Weight: Less than</label>
                                    <input type="text" name="en_dbsc_add_rate_max_weight"
                                           id="en_dbsc_add_rate_max_weight" class="en_dbsc_add_rate_max_weight"
                                           title="Weight: Less than" placeholder=""/>
                                </div>
                            </div>
                        </div>
                        <div class="form-field en-dbsc-add-rate-radio">
                            <input type="radio" id="en_dbsc_add_rate_condition_base_and" name="en_dbsc_add_rate_condition_base" value="And" checked="checked"/>
                            And
                            &nbsp;&nbsp;<input type="radio" id="en_dbsc_add_rate_condition_base_or" name="en_dbsc_add_rate_condition_base" value="Or"/> Or
                        </div>
                        <div class="en-dbsc-inline-div">
                            <div class="en-dbsc-first-section-div">
                                <div class="form-field">
                                    <label>Length: Greater than or equal to</label>
                                    <input type="text" name="en_dbsc_add_rate_min_length"
                                           id="en_dbsc_add_rate_min_length" class="en_dbsc_add_rate_min_length"
                                           title="Length: Greater than or equal to" placeholder=""/>
                                </div>
                            </div>
                            <div class="en-dbsc-second-section-div">
                                <div class="form-field">
                                    <label>Length: Less than</label>
                                    <input type="text" name="en_dbsc_add_rate_max_length"
                                           id="en_dbsc_add_rate_max_length" class="en_dbsc_add_rate_max_length"
                                           title="Length: Less than" placeholder=""/>
                                </div>
                            </div>
                        </div>

                        <div class="en-dbsc-inline-div">
                            <div class="en-dbsc-first-section-div">
                                <div class="form-field">
                                    <label>Distance adjustment</label>
                                    <input type="text" name="en_dbsc_add_rate_distance_adjustment"
                                           id="en_dbsc_add_rate_distance_adjustment" class="en_dbsc_add_rate_distance_adjustment"
                                           title="Distance adjustment"/>
                                </div>
                            </div>
                            <div class="en-dbsc-second-section-div">
                                <div class="form-field">
                                    <label>Rate adjustment</label>
                                    <input type="text" name="en_dbsc_add_rate_rate_adjustment"
                                           id="en_dbsc_add_rate_rate_adjustment" class="en_dbsc_add_rate_rate_adjustment"
                                           title="Rate adjustment"/>
                                </div>
                            </div>
                        </div>

                        <div class="en-dbsc-inline-div">
                            <div class="en-dbsc-first-section-div">
                                <div class="form-field">
                                    <label>Minimum shipping quote</label>
                                    <input type="text" name="en_dbsc_add_rate_min_quote"
                                           id="en_dbsc_add_rate_min_quote" class="en_dbsc_add_rate_min_quote"
                                           title="Minimum shipping quote"/>
                                </div>
                            </div>
                            <div class="en-dbsc-second-section-div">
                                <div class="form-field">
                                    <label>Maximum shipping quote</label>
                                    <input type="text" name="en_dbsc_add_rate_max_quote"
                                           id="en_dbsc_add_rate_max_quote" class="en_dbsc_add_rate_max_quote"
                                           title="Maximum shipping quote"/>
                                </div>
                            </div>
                        </div>

                        <h3 class="en_dbsc_rate_section_heading">Cart Value Settings</h3>
                        <span>When populated, the value of the Cart will be evaluated to determine if the calculated shipping rate should be presented provided that another setting does not disqualify it.</span>

                        <div class="en-dbsc-inline-div">
                            <div class="en-dbsc-first-section-div">
                                <div class="form-field">
                                    <label>Minimum Value Of Cart</label>
                                    <input type="text" name="en_dbsc_add_rate_min_cart_value"
                                           id="en_dbsc_add_rate_min_cart_value" class="en_dbsc_add_rate_min_cart_value"
                                           title="Minimum Value Of Cart"/>
                                </div>
                            </div>
                            <div class="en-dbsc-second-section-div">
                                <div class="form-field">
                                    <label>Maximum Value Of Cart</label>
                                    <input type="text" name="en_dbsc_add_rate_max_cart_value"
                                           id="en_dbsc_add_rate_max_cart_value" class="en_dbsc_add_rate_max_cart_value"
                                           title="Maximum Value Of Cart"/>
                                </div>
                            </div>
                        </div>

                        <div class="form-field mt-15">
                            <input type="radio" id="en_dbsc_add_rate_cart_value_type_total" name="en_dbsc_add_rate_cart_value_type" checked="checked"
                                   value="total"/>The value of all the items in the Cart
                        </div>
                        <div class="form-field">
                            <input type="radio" id="en_dbsc_add_rate_cart_value_type_profile" name="en_dbsc_add_rate_cart_value_type" value="profile"/>Only the value of the items in the Cart associated with this shipping method
                        </div>

                        <!-- Second/different group of radio buttons -->
                        <h3 class="en_dbsc_rate_section_heading">How to apply the calculated shipping rate</h3>
                        <span>Choose how to apply the calculated shipping rate when a calculated shipping rate is identified.</span>

                        <div class="form-field">
                            <input type="radio" id="en_dbsc_add_rate_calculution_base_origin" name="en_dbsc_add_rate_calculution_base" checked="checked"
                                   value="origin"/>The calculated shipping rate is for the contents of the Cart
                        </div>
                        <div class="form-field">
                            <input type="radio" id="en_dbsc_add_rate_calculution_base_item" name="en_dbsc_add_rate_calculution_base" value="item"/>Multiply the calculated shipping rate by the number of items in the Cart before applying the minimum and maximum quote values
                        </div>
                        <div class="form-field">
                            <input type="radio" id="en_dbsc_add_rate_calculution_base_item_after_quotes" name="en_dbsc_add_rate_calculution_base" value="item_after_quotes"/>Multiply the calculated shipping rate by the number of items in the Cart after applying the minimum and maximum quote values
                        </div>
                        <div class="form-field">
                            <input type="radio" id="en_dbsc_add_rate_calculution_base_flat" name="en_dbsc_add_rate_calculution_base" value="flat"/>Just show flat rate, do not calculate rates based on distance
                        </div>

                        <input type="hidden" id="en_zone_id" name="en_zone_id">
                        <input type="hidden" id="en_profile_id" name="en_profile_id">
                        <input type="hidden" id="en_rate_id" name="en_rate_id">
                        <input type="hidden" id="en_rate_action" name="en_rate_action" value="add_rate">
                    </form>
                </div>
                <div class="en-dbsc-popup_footer">
                    <a href="javascript:;" class="button-primary en-dbsc-cancel-button en_hide_popup">Cancel</a>
                    <a href="" class="button-primary en-dbsc-Done-button" id="en-dbsc-add-rate">Save</a>
                </div>
            </div>
        </div>
        <?php
    }

}
