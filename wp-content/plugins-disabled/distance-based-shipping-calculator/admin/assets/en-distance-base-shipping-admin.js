jQuery(document).ready(function () {
    var en_display_as_collection = [];
    jQuery('.en_display_as_collection').each(function (ind, obj) {
        en_display_as_collection[ind] = obj.innerText;
    });
    jQuery("#en_dbsc_add_rate_display_as").autocomplete({
        source: en_display_as_collection,
        appendTo: "#en-dbsc-add-rate-form"
    });

    jQuery("#order_shipping_line_items .shipping .display_meta").css('display', 'none');
    jQuery('#en_connection_settings_license_key_distance_base_shipping').closest('form').addClass('en_distance_base_shipping_form');
    jQuery('#en_connection_settings_license_key_distance_base_shipping').closest('table').addClass('en_distance_base_shipping_table');
    jQuery('#en_connection_settings_description_distance_base_shipping').closest('tr').addClass('en_connection_settings_description_distance_base_shipping_tr');
    jQuery('#en_connection_settings_license_key_distance_base_shipping').closest('tr').addClass('en_connection_settings_license_key_distance_base_shipping_tr');
    jQuery('#en_connection_settings_current_subscription_distance_base_shipping').closest('tr').addClass('en_connection_settings_current_subscription_distance_base_shipping_tr');
    jQuery('#en_connection_settings_current_usage_distance_base_shipping').closest('tr').addClass('en_connection_settings_current_usage_distance_base_shipping_tr');
    jQuery('#en_connection_settings_multi_shipment_label_distance_base_shipping').closest('tr').addClass('en_connection_settings_multi_shipment_label_distance_base_shipping_tr');
    jQuery('#en_quote_settings_genera_profile_title_distance_base_shipping').closest('tr').addClass('en_quote_settings_genera_profile_title_distance_base_shipping_tr');
    jQuery('#en_quote_settings_genera_ship_from_title_distance_base_shipping').closest('tr').addClass('en_quote_settings_genera_ship_from_title_distance_base_shipping_tr');
    jQuery('#en_quote_settings_genera_ship_to_title_distance_base_shipping').closest('tr').addClass('en_quote_settings_genera_ship_to_title_distance_base_shipping_tr');
    jQuery('#en_settings_distance_based_multi_ship_profile_include').closest('tr').addClass('en_settings_distance_based_multi_ship_profile_include_tr');
    jQuery('#en_settings_distance_based_multi_ship_profile_exclude').closest('tr').addClass('en_settings_distance_based_multi_ship_profile_exclude_tr');
    jQuery('.en_settings_how_to_handle_multi_shipment_distance_base_shipping').closest('tr').addClass('en_settings_how_to_handle_multi_shipment_distance_base_shipping_tr');

    jQuery(".en_connection_settings_description_distance_base_shipping_tr td").attr("colspan", "2");
    jQuery("#en_connection_settings_current_subscription_distance_base_shipping").attr("data-optional", "1");
    jQuery("#en_connection_settings_description_distance_base_shipping").attr("data-optional", "1");
    jQuery("#en_connection_settings_current_usage_distance_base_shipping").attr("data-optional", "1");

    // Connection Settings Tab
    jQuery(".en_distance_base_shipping_form .submit .woocommerce-save-button").addClass('dbsc_save_settings');
    jQuery(".en_distance_base_shipping_form .submit .dbsc_save_settings").before('<button type="button" class="button-primary dbsc_connection_settings is-primary components-button">Test connection</button>');
    jQuery('#en_connection_settings_license_key_distance_base_shipping').attr('title', 'Eniture API Key');
    jQuery('#en_connection_settings_multi_shipment_label_distance_base_shipping').attr('title', 'Multi-shipment Label');

    jQuery('#en_connection_settings_next_subcribed_package,#en_connection_settings_subscription_status_distance_base_shipping').closest('tr').css("display", "none");

    let next_subcribed_package = jQuery('#en_connection_settings_next_subcribed_package').attr('placeholder');
    jQuery('#en_connection_settings_auto_renew_distance_base_shipping').val(next_subcribed_package);

    jQuery('#en_connection_settings_multi_shipment_label_distance_base_shipping').closest('form').addClass('en_distance_base_shipping_settings_form');

    jQuery('.dbsc_connection_settings, .dbsc_save_settings').on('click', function (event) {
        jQuery('.en_distance_base_shipping_err').remove();
        let input = jQuery('#en_connection_settings_license_key_distance_base_shipping').val();
        let validate = en_validate_string(input);
        if (validate === false || validate === 'empty') {
            jQuery('#en_connection_settings_license_key_distance_base_shipping').after('<span class="en_distance_base_shipping_err">Eniture API Key is required</span>');
            return false;
        }

        if ($(this).hasClass('dbsc_save_settings')) return true;

        let postForm = {
            'action': 'en_distance_base_shipping_test_connection',
            'en_post_data': jQuery('#en_connection_settings_license_key_distance_base_shipping').serializeArray(),
            'wp_nonce': en_dbs_admin_script.nonce
        };

        let params = {
            en_ajax_loading_id: '#en_connection_settings_license_key_distance_base_shipping',
        };

        en_ajax_request(params, postForm, en_action_test_connection);

        return false;
    });

    // fdo va
    jQuery('#fd_online_id_distancebase').click(function (e) {
        var postForm = {
            'action': 'distancebase_fd',
            'company_id': jQuery('#freightdesk_online_id').val(),
            'disconnect': jQuery('#fd_online_id_distancebase').attr("data"),
            'wp_nonce': en_dbs_admin_script.nonce
        }
        var id_lenght = jQuery('#freightdesk_online_id').val();
        var disc_data = jQuery('#fd_online_id_distancebase').attr("data");
        if(typeof (id_lenght) != "undefined" && id_lenght.length < 1) {
            jQuery(".en_connection_message").remove();
            jQuery('.user_guide_fdo').before('<div class="notice notice-error en_connection_message"><p><strong>Error!</strong> FreightDesk Online ID is Required.</p></div>');
            return;
        }
        jQuery.ajax({
            type: "POST",
            url: ajaxurl,
            data: postForm,
            beforeSend: function () {
                jQuery('#freightdesk_online_id').css('background',
                    'rgba(255, 255, 255, 1) url("' + EN_DISTANCE_BASE_SHIPPING_DIR_FILE + '' +
                    '/admin/popup/assets/images/processing.gif") no-repeat scroll 50% 50%');
            },
            success: function (data_response) {
                jQuery('#freightdesk_online_id').css('background', '#fff');
                if(typeof (data_response) == "undefined"){
                    return;
                }
                var fd_data = JSON.parse(data_response);
                jQuery('#freightdesk_online_id').css('background', '#fff');
                jQuery(".en_connection_message").remove();
                if((typeof (fd_data.is_valid) != 'undefined' && fd_data.is_valid == false) || (typeof (fd_data.status) != 'undefined' && fd_data.is_valid == 'ERROR')) {
                    jQuery('.user_guide_fdo').before('<div class="notice notice-error en_connection_message"><p><strong>Error! ' + fd_data.message + '</strong></p></div>');
                }else if(typeof (fd_data.status) != 'undefined' && fd_data.status == 'SUCCESS') {
                    jQuery('.user_guide_fdo').before('<div class="notice notice-success en_connection_message"><p><strong>Success! ' + fd_data.message + '</strong></p></div>');
                    window.location.reload(true);
                }else if(typeof (fd_data.status) != 'undefined' && fd_data.status == 'ERROR') {
                    jQuery('.user_guide_fdo').before('<div class="notice notice-error en_connection_message"><p><strong>Error! ' + fd_data.message + '</strong></p></div>');
                }else if (fd_data.is_valid == 'true') {
                    jQuery('.user_guide_fdo').before('<div class="notice notice-error en_connection_message"><p><strong>Error!</strong> FreightDesk Online ID is not valid.</p></div>');
                } else if (fd_data.is_valid == 'true' && fd_data.is_connected) {
                    jQuery('.user_guide_fdo').before('<div class="notice notice-error en_connection_message"><p><strong>Error!</strong> Your store is already connected with FreightDesk Online.</p></div>');

                } else if (fd_data.is_valid == true && fd_data.is_connected == false && fd_data.redirect_url != null) {
                    window.location = fd_data.redirect_url;
                } else if (fd_data.is_connected == true) {
                    jQuery('#con_dis').empty();
                    jQuery('#con_dis').append('<a href="#" id="fd_online_id_distancebase" data="disconnect" class="button-primary">Disconnect</a>')
                }
            }
        });
        e.preventDefault();
    });

    let multishipRadioVal = jQuery('.en_settings_how_to_handle_multi_shipment_distance_base_shipping:checked').val();
    en_distance_based_shipping_enable_disable_profile_dropdown(multishipRadioVal);

    jQuery('.en_settings_how_to_handle_multi_shipment_distance_base_shipping').change(function(){
        en_distance_based_shipping_enable_disable_profile_dropdown(this.value);
    });

    let excludeProfileValue = jQuery('.en_settings_distance_based_multi_ship_profile_include').val();
    en_distance_based_shipping_unselect_and_disable_exclude_dropdown(excludeProfileValue);

    jQuery('.en_settings_distance_based_multi_ship_profile_include').change(function(){
        $(".en_settings_distance_based_multi_ship_profile_exclude > option").each(function(ind, el) {
            $('#en_settings_distance_based_multi_ship_profile_exclude option[value="'+el.value+'"]').attr("disabled", false);
        });
        en_distance_based_shipping_unselect_and_disable_exclude_dropdown(this.value);
    });

    // Review plugin button
    if (jQuery('.en_dbsc_plan_notice').length > 0 || jQuery('.en_dbsc_wrapper').length > 0) {
        const review_plugin_html = '<div id="en-woo-review-plugin"><a href="https://wordpress.org/support/plugin/distance-based-shipping-calculator/reviews/#new-post" target="_blank" class="button button-primary is-primary">Review this plugin</a></div>';

        if (jQuery('ul.subsubsub').length > 0) {
            jQuery('.subsubsub').after(review_plugin_html);
        } else {
            jQuery('li a:contains("Connection Settings")').closest('ul').after(review_plugin_html);
        }
    }
});

if (typeof is_validate_regex != 'function') {
    function is_validate_regex(is_data, is_data_regex) {
        return jQuery.trim(is_data).match(new RegExp(is_data_regex)) ? true : false;
    }
}

/**
 * ==============================================================
 *  Carrier Tab
 * ==============================================================
 */

/**
 * click on carrier checkbox
 */
if (typeof en_action_test_connection != 'function') {
    function en_action_test_connection(params, response) {
        let en_message = '';
        let data = JSON.parse(response);
        let en_class_name = 'notice notice-error en_connection_message';
        jQuery('.en_connection_message').remove();
        jQuery('.box_sizing_package_msg').remove();
        let data_severity = typeof data.severity !== undefined ? data.severity : '';
        let en_class_message = 'Error! ';
        switch (data_severity) {
            case 'SUCCESS':
                en_message = data.Message;
                en_class_message = 'Success! ';
                en_class_name = 'notice notice-success en_connection_message';
                break;
            case 'ERROR':
                en_message = data.Message;
                break;
            default:
                en_message = 'Unknown error';
                break;
        }

        jQuery('.en_distance_base_shipping_table').before('<div class="' + en_class_name + '"><p><strong>' + en_class_message + '</strong>' + en_message + '</p></div>');
    }
}
/**
 * Validate Input String
 */
if (typeof en_validate_string != 'function') {
    function en_validate_string(string) {
        if (string == '')
            return 'empty';
        else
            return true;
    }
}

/**
 * Variable exist
 */
if (typeof en_is_var_exist != 'function') {
    function en_is_var_exist(index, item) {
        return typeof item[index] != 'undefined' ? true : false;
    }
}
/**
 * Function to enable disable multiship profile dropdown
 */
function en_distance_based_shipping_enable_disable_profile_dropdown(selectedValue){
    if(selectedValue == 'conditional'){
        jQuery('#en_settings_distance_based_multi_ship_profile_include').prop("disabled", false);
        jQuery('#en_settings_distance_based_multi_ship_profile_exclude').prop("disabled", false);
    }else{
        jQuery('#en_settings_distance_based_multi_ship_profile_include').prop("disabled", true);
        jQuery('#en_settings_distance_based_multi_ship_profile_exclude').prop("disabled", true);
    }
}
/**
 * This function unselect multiship profile selection
 */
function en_distance_based_shipping_unselect_and_disable_exclude_dropdown(includeValue){
    let excludeValue = jQuery('#en_settings_distance_based_multi_ship_profile_exclude').val();
    if(includeValue != 'unselected' && includeValue == excludeValue){
        jQuery('#en_settings_distance_based_multi_ship_profile_exclude').val('un-selected');
    }

    $('#en_settings_distance_based_multi_ship_profile_exclude option[value="'+includeValue+'"]').attr("disabled", true);
}

/**
 * Ajax common resource
 * @param params.en_ajax_loading_id The loading Path Id
 * @param params.en_ajax_disabled_id The disabled Path Id
 * @param params.en_ajax_loading_msg_btn The message show on button during load
 */
if (typeof en_ajax_request != 'function') {
    function en_ajax_request(params, data, call_back_function) {

        jQuery.ajax({
            type: "POST",
            url: ajaxurl,
            data: data,
            beforeSend: function () {

                (typeof params.en_ajax_loading_id != 'undefined' &&
                        params.en_ajax_loading_id.length > 0) ?
                        jQuery(params.en_ajax_loading_id).css('background',
                        'rgba(255, 255, 255, 1) url("' + EN_DISTANCE_BASE_SHIPPING_DIR_FILE + '' +
                        '/admin/popup/assets/images/processing.gif") no-repeat scroll 50% 50%') : "";

                (typeof params.en_ajax_disabled_id != 'undefined' &&
                        params.en_ajax_disabled_id.length > 0) ?
                        jQuery(params.en_ajax_disabled_id).prop({disabled: true}) : "";

                (typeof params.en_ajax_loading_msg_btn != 'undefined' &&
                        params.en_ajax_loading_msg_btn.length > 0) ?
                        jQuery(params.en_ajax_loading_msg_btn).addClass('spinner_disable').val("Loading ..") : "";

                (typeof params.en_ajax_loading_msg_ok_btn != 'undefined' &&
                        params.en_ajax_loading_msg_ok_btn.length > 0) ?
                        jQuery(params.en_ajax_loading_msg_ok_btn).addClass('spinner_disable').text("Loading ..") : "";
            },
            success: function (response) {
                jQuery(params.en_ajax_loading_id).css('background', '#fff');
                (typeof params.en_ajax_disabled_id != 'undefined' &&
                        params.en_ajax_disabled_id.length > 0) ?
                        jQuery(params.en_ajax_disabled_id).prop({disabled: false}) : "";

                (typeof params.en_ajax_loading_msg_btn != 'undefined' &&
                        params.en_ajax_loading_msg_btn.length > 0) ?
                        jQuery(params.en_ajax_loading_msg_btn).removeClass('spinner_disable').val("Done") : "";

                (typeof params.en_ajax_loading_msg_ok_btn != 'undefined' &&
                        params.en_ajax_loading_msg_ok_btn.length > 0) ?
                        jQuery(params.en_ajax_loading_msg_ok_btn).removeClass('spinner_disable').text("Ok") : "";

                return call_back_function(params, response);
            },
            error: function (jqXHR, textStatus, errorThrown) {
                jQuery(params.en_ajax_loading_id).css('background', '#fff');
                console.log(textStatus, errorThrown);
            }
        });
    }
}
