<?php

namespace EnDistanceBaseShippingZonePopup;

use WC_Shipping_Zone;

class EnDistanceBaseShippingZonePopup extends \WC_Countries
{
    public function __construct()
    {
        $this->shippingZonePopup();
    }

    public function shippingZonePopup()
    {
        $shipping_continents = $this->get_continents();
        $allowed_countries = $this->get_allowed_countries();
	    $zone = new WC_Shipping_Zone();
	    // Prepare locations.
	    $locations = array();
	    $postcodes = array();

	    foreach ( $zone->get_zone_locations() as $location ) {
		    if ( 'postcode' === $location->type ) {
			    $postcodes[] = $location->code;
		    } else {
			    $locations[] = $location->type . ':' . $location->code;
		    }
	    }
        $countries_with_states = [];
        ?>
        <!-- Add Popup for new shipping origin -->
        <div class="en-add-shipping-zone-popup" id="en-add-shipping-zone-popup" style="display: none;">
            <div class="en-dbsc-popup_container">
                <div class="en-dbsc-popup_header">
                    <h2>Create zone</h2>
                    <a class="close en_hide_popup" href="#">×</a>
                </div>
                    <div class="en-dbsc-popup_body">
                        </form>
                        <form method="post" id="en-dbsc-shipping-zone-form">
                            <div class="form-field">
                                <label>Zone name</label>
                                <input type="text" name="en_dbsc_shipping_zone_name"
                                       id="en_zone_name" class="en_dbsc_shipping_zone_name"
                                       placeholder=""/>
                                <span class="description">Customers won't see this</span>
                            </div>
                            <div class="form-field">
                                <label>Define zone by...</label>
                                <div class="en-define-by-zone">
                                    <input type="radio" name="en-shipping-zone-type" value="by_country"
                                           class="en_by_country en_define_by" checked="checked"> Country and/or
                                    State / Province
                                    <input type="radio" name="en-shipping-zone-type" value="by_postal_code"
                                           class="en_by_postal_code en_define_by"> Postal Code by
                                    Country
                                </div>
                            </div>

                            <div class="form-field">
                                <select multiple="multiple" data-attribute="en_zone_locations"
                                        name="en_zone_locations" id="en_zone_locations"
                                        data-placeholder="<?php esc_attr_e('Select regions within this zone', 'woocommerce'); ?>"
                                        class="wc-shipping-zone-region-select chosen_select">
                                    <?php
                                    foreach ($shipping_continents as $continent_code => $continent) {
                                        echo '<option value="continent:' . esc_attr($continent_code) . '"' . wc_selected("continent:$continent_code", $locations) . '>' . esc_html($continent['name']) . '</option>';

                                        $countries = array_intersect(array_keys($allowed_countries), $continent['countries']);

                                        foreach ($countries as $country_code) {
                                            echo '<option value="country:' . esc_attr($country_code) . '"' . wc_selected("country:$country_code", $locations) . '>' . esc_html('&nbsp;&nbsp; ' . $allowed_countries[$country_code]) . '</option>';

                                            $states = $this->get_states($country_code);

                                            if ($states) {
                                                foreach ($states as $state_code => $state_name) {
                                                    echo '<option value="state:' . esc_attr($country_code . ':' . $state_code) . '"' . wc_selected("state:$country_code:$state_code", $locations) . '> ' . esc_html('&nbsp;&nbsp;&nbsp;&nbsp; ' . $state_name . ', ' . $allowed_countries[$country_code]) . '</option>';
                                                }
                                            }
                                        }
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="form-field en-dbsc-postal-codes-div" style="display: none;">
                                <textarea name="en_zone_postcodes" data-attribute="zone_postcodes" id="en_zone_postcodes" placeholder="<?php esc_attr_e( 'List 1 postcode per line', 'woocommerce' ); ?>" class="input-text large-text" cols="25" rows="5"><?php echo esc_textarea( implode( "\n", $postcodes ) ); ?></textarea>
                                <?php /* translators: WooCommerce link to setting up shipping zones */ ?>
                                <span class="description"><?php printf( __( 'Postcodes containing wildcards (e.g. CB23*) or fully numeric ranges (e.g. <code>90210...99000</code>) are also supported. Please see the shipping zones <a href="%s" target="_blank">documentation</a>) for more information.', 'woocommerce' ), 'https://docs.woocommerce.com/document/setting-up-shipping-zones/#section-3' ); ?></span>
                            </div>

                            <input type="hidden" name="en_countries_states" class="en_countries_states"
                                   value="<?php echo json_encode($countries_with_states, TRUE); ?>">
   
                                <input type="hidden" name="origin_order" id="origin_order" />
                                <input type="hidden" name="profile_id" id="profile_id" />
                                <input type="hidden" name="origin_id" id="origin_id" />
                                <input type="hidden" name="zone_id" id="zone_id" />
                                <input type="hidden" name="en_zone_action" id="en_zone_action" />
                        </form>
                    </div>
                    <div class="en-dbsc-popup_footer">
                        <a href="javascript:;" class="button-primary en-dbsc-cancel-button en_hide_popup">Cancel</a>
                        <a href="" class="button-primary en-add-shipping-zone" id="en-add-shipping-zone">Save</a>
                    </div>
            </div>
        </div>
        <?php
    }
}
