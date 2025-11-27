jQuery(document).ready(function ($) {

    /**
     * plan change function for box sizing.
     */
    jQuery("#en_connection_settings_auto_renew_distance_base_shipping").on('change', function () {
        jQuery('.en_distance_base_shipping_err,.box_sizing_package_msg,.en_connection_message').remove();
        en_woo_addons_monthly_packg_dbsc(jQuery(this).val());
        return false;
    });

    /**
     *
     * @param object params
     * @param object response
     * @returns none
     */
    var dbsc_suspend_automatic_detection = function (params, response) {
        var selected_plan = jQuery("#en_connection_settings_auto_renew_distance_base_shipping").val();
        window.existing_plan_dbsc = selected_plan;
        var suspend_automatic = jQuery("#en_connection_settings_suspend_distance_base_shipping").prop("checked");
        var subscription_status = jQuery("#en_connection_settings_subscription_status_distance_base_shipping").attr("placeholder");

        if (subscription_status == "yes") {
            jQuery("#en_connection_settings_suspend_distance_base_shipping").prop('disabled', false);
            jQuery("label[for='en_connection_settings_auto_renew_distance_base_shipping']").text("Auto-renew");
        } else {
            jQuery("label[for='en_connection_settings_auto_renew_distance_base_shipping']").text("Select a plan");
            jQuery("#en_connection_settings_suspend_distance_base_shipping").prop({checked: false, disabled: true});
        }
    };

    dbsc_suspend_automatic_detection();

    /**
     * suspend template.
     * @returns none
     */
    var dbsc_suspend_automatic_detection_params = function () {
        return {
            loading_msg: " <span class='suspend-loading'>Loading ...</span>",
            disabled_id: "en_connection_settings_suspend_distance_base_shipping",
        };
    };

    /**
     * suspend enabled.
     * @returns none
     */
    var dbsc_suspend_automatic_detection_anable = function () {
        return {
            en_connection_settings_suspend_distance_base_shipping: "yes",
            action: "suspend_automatic_detection_dbsc",
            wp_nonce: en_dbsc_admin_popup_script.nonce
        };
    };

    /**
     * suspend disabled.
     * @returns none
     */
    var dbsc_suspend_automatic_detection_disabled = function () {
        var always_include_threed = jQuery(".en_woo_addons_always_include_threed_fee").attr("id");
        return {
            en_connection_settings_suspend_distance_base_shipping: "no",
            action: "suspend_automatic_detection_dbsc",
            wp_nonce: en_dbsc_admin_popup_script.nonce
        };
    };

    /**
     * When click on suspend checkbox.
     */
    jQuery("#en_connection_settings_suspend_distance_base_shipping").on('click', function () {
        var data = "";
        var params = "";
        if (this.checked) {
            data = dbsc_suspend_automatic_detection_anable();
            params = dbsc_suspend_automatic_detection_params();
        } else {
            data = dbsc_suspend_automatic_detection_disabled();
            params = dbsc_suspend_automatic_detection_params();
        }
        ajax_request(params, data, dbsc_suspend_automatic_detection);
    });

    /**
     *
     * @param object params
     * @param onject response
     * @returns none
     */
    var dbsc_monthly_packg_response = function (params, response) {
        var parsRes = JSON.parse(response);

        if (parsRes.severity == "SUCCESS") {
            if (parsRes.subscription_packages_response == "yes") {

                jQuery("#en_connection_settings_current_subscription_distance_base_shipping").next('.description').html(parsRes.current_subscription);
                jQuery("#en_connection_settings_current_usage_distance_base_shipping").next('.description').html(parsRes.current_usage);
                jQuery("#en_connection_settings_subscription_status_distance_base_shipping").attr("placeholder", "yes");
            }

            if (typeof params.message_ph != 'undefined' && params.message_ph.length > 0) {
                jQuery(".en_connection_settings_license_key_distance_base_shipping_tr").closest('table').before('<div class="notice notice-success box_sizing_package_msg"><p><strong>Success! </strong>' + params.message_ph + '</p></div>');
            }

            dbsc_suspend_automatic_detection();

        } else {
            jQuery(".en_connection_settings_license_key_distance_base_shipping_tr").closest('table').before('<div class="notice notice-error box_sizing_package_msg" ><p><strong>Error! </strong>' + parsRes.Message + '</p></div>');
            jQuery('#en_connection_settings_auto_renew_distance_base_shipping').prop('selectedIndex', 0);
        }

        setTimeout(function () {
            jQuery('.box_sizing_package_msg').fadeOut('fast');
        }, 3000);
        jQuery("#box_sizing_plan_auto_renew").focus();
    };

    /**
     * Monthly package select actions.
     * @param string monthly_pckg
     * @returns boolean
     */
    var en_woo_addons_monthly_packg_dbsc = function (monthly_pckg) {
        var data = {selected_plan: monthly_pckg, action: 'en_woo_addons_upgrade_plan_submit_dbsc', wp_nonce: en_dbsc_admin_popup_script.nonce};
        var params = "";
        if (window.existing_plan_dbsc == "disable") {
            en_woo_dbsc_popup_notifi_disabl_to_plan_show_box();
            return false;
        } else if (monthly_pckg == "disable") {

            params = {
                loading_id: "en_connection_settings_auto_renew_distance_base_shipping",
                disabled_id: "en_connection_settings_auto_renew_distance_base_shipping",
                message_ph: "You have disabled the Distance Based shipping Calculator. The plugin will stop working when the current plan is depleted or expires."
            };
        } else {
            params = {
                loading_id: "en_connection_settings_auto_renew_distance_base_shipping",
                disabled_id: "en_connection_settings_auto_renew_distance_base_shipping",
                message_ph: "Your choice of plans has been updated. "
            };
        }

        ajax_request(params, data, dbsc_monthly_packg_response);
    };

    /**
     * When user from disable to plan popup actions.
     * @returns {undefined}
     */
    jQuery(".cancel_plan").on('click', function () {
        en_woo_dbsc_popup_notifi_disabl_to_plan_hide();
        jQuery('#en_connection_settings_auto_renew_distance_base_shipping').prop('selectedIndex', 0);
        return false;
    });
    /**
     * Confirm click function.
     */
    jQuery(".confirm_plan").on('click', function () {
        jQuery('.en_distance_base_shipping_err').remove();
        let input = jQuery('#en_connection_settings_license_key_distance_base_shipping').val();
        let validate = en_validate_string(input);
        if (validate === false || validate === 'empty') {
            jQuery('#en_connection_settings_license_key_distance_base_shipping').after('<span class="en_distance_base_shipping_err">Eniture API Key is required</span>');
            en_woo_dbsc_popup_notifi_disabl_to_plan_hide();
            return false;
        }
        
        var params = "";
        en_woo_dbsc_popup_notifi_disabl_to_plan_hide();
        var monthly_pckg = jQuery("#en_connection_settings_auto_renew_distance_base_shipping").val();
        var plugin_name = jQuery("#en_box_sizing_plugin_name").attr("placeholder");

        var data = {
            plugin_name: plugin_name,
            selected_plan: monthly_pckg,
            action: 'en_woo_addons_upgrade_plan_submit_dbsc',
            wp_nonce: en_dbsc_admin_popup_script.nonce
        };
        params = {
            loading_id: "en_connection_settings_auto_renew_distance_base_shipping",
            message_id: "plan_to_disable_message",
            disabled_id: "en_connection_settings_auto_renew_distance_base_shipping",
            message_ph: "Your choice of plans has been updated. "
        };

        ajax_request(params, data, dbsc_monthly_packg_response);
        return false;
    });

    jQuery(".en_hide_popup").click(function () {
        jQuery('.en-shipping-region-exist-error').remove();
        // This condition checks if the Modal is of Profile and add-class section is visible
        if (this.id === 'en-add-profile-cancel') {
            if (jQuery('#en-add-shipping-profile').attr('add') === 'class') {
                return;
            }
        }
        hide_edit_delete_popup(this);
    });

    jQuery('#en_shipping_zone_postal_codes .tag-i').trigger('click');

    /**
     * Add shipping class fields hide/show
     */
    jQuery('.en-dbsc-add-new-shipping-class').click(function (event) {
        jQuery('.en-dbsc-shipping-class-search-div').hide('slow');
        jQuery('.en_profile_heading h2').text('Add shipping class');
        jQuery('#en-add-shipping-profile').attr('add', 'class');
        jQuery('#en-add-profile-cancel').text('Back');
        jQuery('#en-add-profile-cancel').removeClass('en_hide_popup');
        jQuery('.en-dbsc-add-shipping-class').show('slow');
    });

    jQuery('#en-add-profile-cancel').click(function (event) {
        jQuery('.en-dbsc-add-shipping-class').hide('slow');
        jQuery('.en_profile_heading h2').text('Add shipping profile');
        jQuery('#en-add-shipping-profile').attr('add', 'profile');
        jQuery('#en-add-profile-cancel').text('Cancel');
        jQuery('#en-add-profile-cancel').addClass('en_hide_popup');
        jQuery('.en-dbsc-shipping-class-search-div').show('slow');
        return;
    });
    /**
     * Add shipping origin Popup
     */
    jQuery('#en-add-shipping-origin').click(function (event) {

        en_dbsc_add_origin_address(event, 'YES');
        event.preventDefault();
    });

    /**
     * Add shipping rate Popup
     */
    jQuery('#en-dbsc-add-rate').click(function (event) {

        let form_id = "#en-dbsc-add-rate-form";
        jQuery.validator.addMethod("lettersOnly", function (value, element) {
            return this.optional(element) || /^[a-zA-Z]*$/.test(value);
        }, "Accept only letters.");
        jQuery.validator.addMethod("numbersOnly", function (value, element) {
            return this.optional(element) || /^\d*(?:\.\d\d?)?$/.test(value);
        }, "Accept only letters.");
        jQuery.validator.addMethod("posNegNumbersOnly", function (value, element) {
            return this.optional(element) || /^-?\d+(?:\.\d\d?)?$/.test(value);
        }, "Accept only numbers.");
        jQuery.validator.addMethod("posNegNumbersWithPercentage", function (value, element) {
            return this.optional(element) || /^-?\d+(?:\.\d\d?)?%?$/.test(value);
        }, "Accept only numbers.");
        jQuery.validator.addMethod("customValidationMaxQuote", function (value, element) {
            let min_quote_val = jQuery('#en_dbsc_add_rate_min_quote').val();
            if(jQuery.isNumeric(value) && value > 0 && jQuery.isNumeric(min_quote_val)){
                if(parseInt(min_quote_val) >= parseInt(value)){
                    return false;
                }
            }
            return true;
        }, "Maximum quote must be greater than minimum quote.");

        jQuery.validator.addMethod("customValidationMaxCartValue", function (value, element) {
            let min_quote_val = jQuery('#en_dbsc_add_rate_min_cart_value').val();
            if(jQuery.isNumeric(value) && value > 0 && jQuery.isNumeric(min_quote_val)){
                if(parseInt(min_quote_val) >= parseInt(value)){
                    return false;
                }
            }
            return true;
        }, "Maximum cart value must be greater than minimum cart value.");

        jQuery(form_id).validate({
            rules: {
                en_dbsc_add_rate_display_as: {
                    required: true,
                },
                en_dbsc_add_rate_description: {
                    // required: true,
                },
                en_dbsc_add_rate_base_amount: {
                    numbersOnly: '/^\d*(?:\.\d\d?)?$/',
                },
                en_dbsc_add_rate_per_mile: {
                    required: true,
                    numbersOnly: '/^\d*(?:\.\d\d?)?$/',
                },
                en_dbsc_add_rate_min_distance: {
                    numbersOnly: '/^\d*(?:\.\d\d?)?$/',
                },
                en_dbsc_add_rate_max_distance: {
                    numbersOnly: '/^\d*(?:\.\d\d?)?$/',
                },
                en_dbsc_add_rate_min_weight: {
                    numbersOnly: '/^\d*(?:\.\d\d?)?$/',
                },
                en_dbsc_add_rate_max_weight: {
                    numbersOnly: '/^\d*(?:\.\d\d?)?$/',
                },
                en_dbsc_add_rate_min_length: {
                    numbersOnly: '/^\d*(?:\.\d\d?)?$/',
                },
                en_dbsc_add_rate_max_length: {
                    numbersOnly: '/^\d*(?:\.\d\d?)?$/',
                },
                en_dbsc_add_rate_min_quote: {
                    numbersOnly: '/^\\d+(?:\\.\\d\\d?)?$/',
                },
                en_dbsc_add_rate_max_quote: {
                    numbersOnly: '/^\d*(?:\.\d\d?)?$/',
                    customValidationMaxQuote: '',
                },
                en_dbsc_add_rate_distance_adjustment: {
                    posNegNumbersOnly: '/^-?\\d+(?:\\.\\d\\d?)?$/',
                },
                en_dbsc_add_rate_rate_adjustment: {
                    posNegNumbersWithPercentage: '/^\\d+(?:\\.\\d\\d?)?$/',
                },
                en_dbsc_add_rate_min_cart_value: {
                    numbersOnly: '/^\d*(?:\.\d\d?)?$/',
                },
                en_dbsc_add_rate_max_cart_value: {
                    numbersOnly: '/^\d*(?:\.\d\d?)?$/',
                    customValidationMaxCartValue: '',
                },
                en_dbsc_add_rate_measured_by: {}
            },
            messages: {
                en_dbsc_add_rate_display_as: {
                    required: "Label is required.",
                },
                en_dbsc_add_rate_description: {},
                en_dbsc_add_rate_base_amount: {
                    numbersOnly: 'Accept only numbers or numbers with maximum 2 decimal points e.g 15, 3.24.',
                },
                en_dbsc_add_rate_per_mile: {
                    required: "Rate is required.",
                    numbersOnly: 'Accept only numbers or numbers with maximum 2 decimal points e.g 15, 3.24.',
                },
                en_dbsc_add_rate_min_distance: {
                    required: "Distance: Greater than or equal to is required.",
                    numbersOnly: 'Accept only numbers or numbers with maximum 2 decimal points e.g 15, 3.24.',
                },
                en_dbsc_add_rate_max_distance: {
                    required: "Distance: Less than is required.",
                    numbersOnly: 'Accept only numbers or numbers with maximum 2 decimal points e.g 15, 3.24.',
                },
                en_dbsc_add_rate_min_weight: {
                    required: "Weight: Greater than or equal to is required.",
                    numbersOnly: 'Accept only numbers or numbers with maximum 2 decimal points e.g 15, 3.24.',
                },
                en_dbsc_add_rate_max_weight: {
                    required: "Weight: Less than is required.",
                    numbersOnly: 'Accept only numbers or numbers with maximum 2 decimal points e.g 15, 3.24.',
                },
                en_dbsc_add_rate_min_length: {
                    required: "Length: Greater than or equal to is required.",
                    numbersOnly: 'Accept only numbers or numbers with maximum 2 decimal points e.g 15, 3.24.',
                },
                en_dbsc_add_rate_max_length: {
                    required: "Length: Less than is required.",
                    numbersOnly: 'Accept only numbers or numbers with maximum 2 decimal points e.g 15, 3.24.',
                },
                en_dbsc_add_rate_min_quote: {
                    required: "Minimum shipping quote is required.",
                    numbersOnly: 'Accept only numbers or numbers with maximum 2 decimal points e.g 15, 3.24.',
                },
                en_dbsc_add_rate_max_quote: {
                    required: "Maximum shipping quote is required.",
                    customValidationMaxQuote: 'Maximum quote must be greater than minimum quote.',
                    numbersOnly: 'Accept only numbers or numbers with maximum 2 decimal points e.g 15, 3.24.',
                },
                en_dbsc_add_rate_distance_adjustment: {
                    required: "Minimum shipping quote is required.",
                    posNegNumbersOnly: 'Accept only numbers or numbers with maximum 2 decimal points e.g 15, 3.24.',
                },
                en_dbsc_add_rate_rate_adjustment: {
                    required: "Maximum shipping quote is required.",
                    posNegNumbersWithPercentage: 'Accept only numbers or numbers with maximum 2 decimal points e.g 15, 3.24. or % sign',
                },
                en_dbsc_add_rate_min_cart_value: {
                    numbersOnly: 'Accept only numbers or numbers with maximum 2 decimal points e.g 15, 3.24.',
                },
                en_dbsc_add_rate_max_cart_value: {
                    customValidationMaxCartValue: 'Maximum cart value must be greater than minimum cart value.',
                    numbersOnly: 'Accept only numbers or numbers with maximum 2 decimal points e.g 15, 3.24.',
                },
                en_dbsc_add_rate_measured_by: {}
            }
        });

        if (jQuery(form_id).validate().form()) {

            let form_data = jQuery(form_id).serialize();
            let profile_id = jQuery('#en_profile_id').val();

            jQuery.ajax({
                type: 'POST',
                url: ajaxurl,
                data: {action: 'en_add_zone_rate', form_data: form_data, wp_nonce: en_dbsc_admin_popup_script.nonce},
                dataType: 'json',
                beforeSend: function () {
                    en_loader_to_show();
                },
                success: function (data) {
                    en_loader_to_hide();
                    if (data.response === 'success') {
                        if (data.action === 'update') {
                            jQuery('#' + data.id).replaceWith(data.append);
                        } else if (data.action === 'insert') {
                            jQuery('.en_zone_details' + data.zone_id).find('#en_shipping_rate tr').last().after(data.append);
                        }

                        jQuery(form_id).trigger("reset");
                        jQuery(form_id + ' input[type=hidden]').each(function () {
                            this.value = '';
                        });
                        jQuery("#en-add-rate-popup").hide();
                        show_notice(profile_id, data.message);

                    }
                }
            });
        }

        event.preventDefault();
    });


    /**
     * Add shipping profile Popup
     */
    jQuery('#en-add-shipping-profile').click(function (event) {
        let method = jQuery(this).attr('add');
        jQuery.validator.addMethod("lettersOnly", function (value, element) {
            return this.optional(element) || /^[a-zA-Z]*$/.test(value);
        }, "Accept only letters.");
        jQuery.validator.addMethod("numbersOnly", function (value, element) {
            return this.optional(element) || /^\d+(?:\.\d\d?)?$/.test(value);
        }, "Accept only letters.");

        let en_dbsc_profile_define_by = function () {
            if (jQuery("input[name=en_dbsc_profile_define_by]").length > 0) {
                return jQuery('input[name="en_dbsc_profile_define_by"]:checked').val();
            } else {
                return 'shipping_classes';
            }
        };

        var en_shipping_classes = jQuery('.en_profile_shipping_classes').val();
        if (en_shipping_classes == '' && en_dbsc_profile_define_by() == 'shipping_classes') {
            jQuery('#en_shipping_classes_error_msg').show();
        }

        if(en_dbsc_profile_define_by() == 'product_tags'){
            en_shipping_classes = jQuery('.en_dbsc_profile_product_tags').val();
        }

        var en_add_shipping_profile = jQuery('#en-dbsc-shipping-profile-form').serialize();

        let define_by_gp_condition = function () {
            if (jQuery("input[name=en_general_profile_condition]").length > 0) {
                return jQuery("input[name=en_general_profile_condition]:checked").val();
            } else {
                return 'en_for_specific_products';
            }
        };

        jQuery("#en-dbsc-shipping-profile-form").validate({
            rules: {
                en_dbsc_shipping_profile_nickname: {
                    required: true,
                },
                en_shipping_classes: {
                    required: {
                        depends: function () {
                            return (define_by_gp_condition() === 'en_for_specific_products' && en_dbsc_profile_define_by() === 'shipping_classes');
                        }
                    }
                },
                en_dbsc_product_tags: {
                    required: {
                        depends: function () {
                            return (define_by_gp_condition() === 'en_for_specific_products' && en_dbsc_profile_define_by() === 'product_tags');
                        }
                    }
                },
                en_dbsc_shipping_profile_shipping_class_name: {
                    required: true,
                },
                en_dbsc_shipping_profile_shipping_class_slug: {
                    required: true,
                },
                en_dbsc_shipping_profile_shipping_class_description: {
                    required: true,
                },
            },
            messages: {
                en_dbsc_shipping_profile_nickname: {
                    required: "Nickname is required.",
                },
                en_shipping_classes: {
                    required: 'Shipping class is required.',
                },
                en_dbsc_product_tags: {
                    required: 'Product tag is required.',
                },
                en_dbsc_shipping_profile_shipping_class_name: {
                    required: "Shipping class name is required.",
                },
                en_dbsc_shipping_profile_shipping_class_slug: {
                    required: "Slug is required.",
                },
                en_dbsc_shipping_profile_shipping_class_description: {
                    required: "Description is required.",
                },
            },
            errorPlacement: function(error, element) {
                if (element.attr("name") === "en_shipping_classes") {
                    error.insertAfter('#en_shipping_classes_error_msg');
                }else if(element.attr("name") === "en_dbsc_product_tags"){
                    error.insertAfter('#en_product_tags_error_msg');
                } else {
                    // Default placement for other fields
                    error.insertAfter(element);
                }
            }
        });

        switch (method) {
            case 'profile':
                var data = {
                    action: 'en_update_shipping_profile',
                    form_data: en_add_shipping_profile,
                    selected_classes: en_shipping_classes,
                    wp_nonce: en_dbsc_admin_popup_script.nonce
                }
                break;

            case 'class':
                var data = {
                    action: 'en_add_shipping_class',
                    form_data: en_add_shipping_profile,
                    selected_classes: en_shipping_classes,
                    wp_nonce: en_dbsc_admin_popup_script.nonce
                }
                break;

        }

        if (jQuery('#en-dbsc-shipping-profile-form').validate().form()) {

            jQuery.ajax({
                type: 'POST',
                url: ajaxurl,
                data: data,
                dataType: 'json',
                beforeSend: function () {
                    en_loader_to_show();
                },

                success: function (response) {
                    jQuery('.en-shipping-class-exist-error').remove();
                    en_loader_to_hide();
                    if (response.response == 'success') {

                        if (method === 'class') {
                            jQuery('.en_profile_shipping_classes').append(response.append_options);
                            jQuery('.en-dbsc-shipping-class-search-div').show('slow');
                            jQuery('.en_profile_heading h2').text('Add shipping profile');
                            jQuery('#en-add-shipping-profile').attr('add', 'profile');
                            jQuery('.en-dbsc-add-shipping-class').hide('slow');

                        } else if (method === 'profile') {

                            jQuery('.en-add-shipping-profile-popup').hide();
                            jQuery('.en_append_new_profile').last().after(response.append_html);
                            jQuery('#en-edit-profile-heading-' + response.params.profile_id).next('h2').text(response.params.profile_name);

                            if (response.en_profile_action != 'add_profile') {
                                jQuery('#en-edit-profile-classes-' + response.params.profile_id).html('');
                                jQuery('#en-edit-profile-classes-' + response.params.profile_id).append(response.append_classes);

                                jQuery('.en_distance_base_shipping_quote_error-' + response.params.profile_id).html('');
                                jQuery('.en_distance_base_shipping_quote_error-' + response.params.profile_id).append('<div class="notice notice-success en_shipping_origin_other_success_msg"><p><strong>Success!</strong> ' + response.message + '</p></div>');
                                jQuery('.en_distance_base_shipping_quote_error-' + response.params.profile_id).show('slow').delay(5000).hide('slow');
                                jQuery('html, body').animate({
                                    'scrollTop': jQuery('.en_shipping_origin_other_success_msg').position().top
                                });
                            } else {

                                jQuery('.en_distance_base_shipping_quote_error').html('');
                                jQuery('.en_distance_base_shipping_quote_error').append('<div class="notice notice-success en_shipping_origin_success_msg"><p><strong>Success!</strong> ' + response.message + '</p></div>');
                                jQuery('.en_distance_base_shipping_quote_error').show('slow').delay(5000).hide('slow');
                                jQuery('html, body').animate({
                                    'scrollTop': jQuery('.en_shipping_origin_success_msg').position().top
                                });
                            }
                            if (response.params.class_slug != '') {
                                jQuery('.en_shipping_classes').append(jQuery('<option>', {
                                    value: response.params.class_slug,
                                    text: response.params.class_name
                                }));
                            }
                        }
                    } else if (response.response == 'error') {
                        jQuery('.en_profile_heading').after('<div class="notice notice-error en-shipping-class-exist-error"><p><strong>Error! </strong>' + response.message + '</p></div>');
                    }
                }
            });
        }
        event.preventDefault();
    });


    /**
     * Hide/show create zone popup fields
     */
    jQuery(".en_define_by").change(function () {
        var en_shipping_zone_type = jQuery(this).val();
        if (en_shipping_zone_type == 'by_country') {
            jQuery('.en-dbsc-postal-codes-div').hide('slow');
        } else {
            jQuery('.en-dbsc-postal-codes-div').show('slow');
        }
    });

    /**
     * Insert Shipping zone Popup
     */
    jQuery('#en-add-shipping-zone').click(function (event) {

        jQuery('.en-shipping-region-exist-error').remove();

        let name = jQuery('#en_zone_name').val();
        let define_by = function () {
            return jQuery("input[name='en-shipping-zone-type']:checked").val();
        };
        let locations = jQuery('#en_zone_locations').val();

        let zip_codes = (define_by() === 'by_postal_code') ? jQuery('#en_zone_postcodes').val() : '';
        let profile_id = jQuery('#profile_id').val();
        let origin_id = jQuery('#origin_id').val();
        let zone_id = jQuery('#zone_id').val();
        let origin_order = jQuery('#origin_order').val();
        let en_zone_action = jQuery('#en_zone_action').val();

        jQuery.validator.addMethod("oneCountry", function (value, element) {
            return !(value.length > 1);
        }, 'Only one country could be added');

        jQuery.validator.addMethod("onlyCountry", function (value, element) {
            return !(value[0].indexOf('country:') === -1);
        }, 'Only country could be added');

        jQuery("#en-dbsc-shipping-zone-form").validate({
            rules: {
                en_dbsc_shipping_zone_name: {
                    required: true,
                },
                en_zone_locations: {
                    required: true,

                    oneCountry: {
                        depends: function () {
                            return define_by() === 'by_postal_code';
                        },
                    },
                    onlyCountry: {
                        depends: function () {
                            return define_by() === 'by_postal_code';
                        },
                    }
                },
                en_zone_postcodes: {
                    required: {
                        depends: function () {
                            return define_by() === 'by_postal_code';
                        }
                    }
                }
            },
            messages: {
                en_dbsc_shipping_zone_name: {
                    required: "This field is required.",
                }
            },
            errorPlacement: function (error, element) {
                //Custom position
                if (element.attr("name") === "en_zone_locations") {
                    error.insertAfter(jQuery(element).next('span'));
                } else { // Default position
                    error.insertAfter(element);
                }
            },

        });

        if (jQuery('#en-dbsc-shipping-zone-form').validate().form()) {

            var form_data = {
                name: name,
                define_by: define_by(),
                locations: locations,
                zip_codes: zip_codes,
                profile_id: profile_id,
                origin_id: origin_id,
                zone_id: zone_id,
                origin_order: origin_order,
                en_zone_action: en_zone_action
            };

            jQuery.ajax({
                type: 'POST',
                url: ajaxurl,
                data: { action: 'en_add_shipping_zone', form_data: form_data, wp_nonce: en_dbsc_admin_popup_script.nonce },
                dataType: 'json',
                beforeSend: function () {
                    en_loader_to_show();
                },
                success: function (response) {

                    en_loader_to_hide();

                    if (response.message == 'success') {

                        jQuery('.en_dbsc_success_msg').remove();

                        if (en_zone_action === 'edit_zone') {
                            jQuery('.en_zone_details' + response.zone_id + ' .zone_left').html(response.append);
                            jQuery('#en-add-shipping-zone-popup').hide();
                        }

                        if (en_zone_action === 'add_zone') {
                            jQuery('.en-shipping-origin-list-item-' + origin_id).closest('.en_shipping_from').find('.en_shipping_to').append(response.append);
                            jQuery('#en-add-shipping-zone-popup').hide();
                        }

                        show_notice(profile_id, response.message_to_show);
                        if (jQuery('.en_dbsc_success_msg').length) {
                            jQuery('html, body').animate({
                                'scrollTop': jQuery('.en_dbsc_success_msg').position().top
                            });
                        }
                        let form_id = '#en-dbsc-shipping-zone-form';
                        jQuery(form_id).trigger("reset");
                        jQuery(form_id + ' input[type=hidden]').each(function () {
                            this.value = '';
                        });
                    } else if (response.message == 'error') {
                        jQuery('.en-add-shipping-zone-popup').find('.en-dbsc-popup_header').after('<div class="notice notice-error en-shipping-region-exist-error"><p><strong>Error! </strong>' + response.message_to_show + '</p></div>');
                    }
                }
            });
        }
        event.preventDefault();
    });

    jQuery('input[name="en_dbsc_profile_define_by"]').change(function () {
        var en_profile_define_by = jQuery(this).val();
        if (en_profile_define_by == 'product_tags') {
            jQuery('.en-dbsc-shipping-class-list-div').hide('slow');
            jQuery('.en-dbsc-add-new-shipping-class-button-div').hide('slow');
            jQuery('.en-dbsc-product-tags-list-div').show('slow');
        } else {
            jQuery('.en-dbsc-product-tags-list-div').hide('slow');
            jQuery('.en-dbsc-shipping-class-list-div').show('slow');
            jQuery('.en-dbsc-add-new-shipping-class-button-div').show('slow');
        }
    });
});

function en_dbsc_add_origin_address(event, confirmAddress){

    jQuery.validator.addMethod("lettersOnly", function (value, element) {
        return this.optional(element) || /^[a-zA-Z ]*$/.test(value);
    }, "Accept only letters.");

    jQuery("#en-dbsc-shipping-origin-form").validate({
        rules: {
            en_dbsc_shipping_origin_nickname: {
                required: true,
            },
            en_dbsc_shipping_origin_street_address: {
                required: true,
            },
            en_dbsc_shipping_origin_city: {
                required: true,
                lettersOnly: '/^[a-zA-Z ]*$/',
            },
            en_dbsc_shipping_origin_state: {
                required: true,
                lettersOnly: '/^[a-zA-Z]*$/',
                maxlength: 16
            },
            en_dbsc_shipping_origin_postal_code: {
                required: true,
                maxlength: 8
            },
            en_dbsc_shipping_origin_country_code: {
                required: true,
                maxlength: 16,
                lettersOnly: '/^[a-zA-Z]*$/'
            }
        },
        messages: {
            en_dbsc_shipping_origin_nickname: {
                required: "Nickname is required.",
            },
            en_dbsc_shipping_origin_street_address: "Street address is required.",
            en_dbsc_shipping_origin_city: {
                required: "City is required.",
                lettersOnly: 'Accept only letters e.g Fayetteville.',
            },
            en_dbsc_shipping_origin_state: {
                maxlength: "Enter at least (2) characters e.g GA.",
                required: "State or Province is required.",
                lettersOnly: 'Accept only letters e.g GA.'
            },
            en_dbsc_shipping_origin_postal_code: {
                required: "Postal code is required.",
                maxlength: "Enter at least (8) characters"
            },
            en_dbsc_shipping_origin_country_code: {
                required: "Country code is required.",
                maxlength: "Minimum 2 characters allowed e.g US.",
                lettersOnly: 'Accept only letters e.g US.'
            }
        }
    });

    if (jQuery('#en-dbsc-shipping-origin-form').validate().form()) {
        var shipping_origin_data = jQuery('#en-dbsc-shipping-origin-form').serialize();

        jQuery.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                action: 'en_add_shipping_origin',
                form_data: shipping_origin_data,
                confirmAddress: confirmAddress,
                wp_nonce: en_dbsc_admin_popup_script.nonce
            },
            dataType: 'json',
            beforeSend: function () {
                en_loader_to_show();
            },

            success: function (data) {
                en_loader_to_hide();
                jQuery('.en-dbsc-origin-address-suggestion').remove();
                jQuery('.en_invalid_address').remove();
                if (data.reload) {
                    jQuery('#en-add-shipping-origin-popup').hide();
                    if (data.action_type == 'add_shipping_origin') {
                        // New Change
                        if (data.en_add_the_shipping_origin == 'en_to_this_shipping_from_profile' && jQuery('#edit-profile-id-' + data.profile_id + ' .en-origin-order-' + data.en_origin_order).length) {
                            var en_shipping_to = jQuery('#edit-profile-id-' + data.profile_id + ' .en-origin-order-' + data.en_origin_order).find('.en_shipping_to').html();
                            jQuery('#edit-profile-id-' + data.profile_id + ' .en-origin-order-' + data.en_origin_order).first().replaceWith(data.append_html);
                            jQuery('#edit-profile-id-' + data.profile_id + ' .en-origin-order-' + data.en_origin_order).first().find('.en_shipping_to').html(en_shipping_to);
                        } else if (jQuery('#edit-profile-id-' + data.profile_id + ' .en_shipping_profile_content .en_shipping_from').length) {
                            // en_no_ship_location
                            jQuery('.en-shipping-origin-list-' + data.profile_id + ' .en_shipping_to').last().find('p').remove();
                            // New Change
                            jQuery('#edit-profile-id-' + data.profile_id + ' .en_shipping_profile_content .en_shipping_from').last().after(data.append_html);
                            jQuery('#edit-profile-id-' + data.profile_id + ' .en_no_ship_location').remove();
                        } else if (jQuery('.en-shipping-origin-list-' + data.profile_id).length) {
                            jQuery('.en-shipping-origin-list-' + data.profile_id + ' p').remove();
                            jQuery('.en-shipping-origin-list-' + data.profile_id).after(data.append_html);
                        } else {
                            jQuery('.en-shipping-origin-list').prepend(data.append_html);
                        }
                    } else if (data.action_type == 'update_shipping_origin' && jQuery('.en-shipping-origin-list-item-' + data.origin_id).length) {
                        jQuery('.en-shipping-origin-list-item-' + data.origin_id).html(data.replace_text);
                    }
                } else if (typeof data.address_validation != 'undefined' && typeof data.response != 'undefined' && data.response == 'success') {
                    var en_str = data.address_validation;
                    var en_obj = JSON.parse(en_str);
                    if (typeof en_obj.severity != 'undefined' && typeof en_obj.suggestedAddress != 'undefined' && typeof data.address_is_valid != 'undefined' && en_obj.severity == 'SUCCESS' && data.address_is_valid === false) {
                        let actualAdress = jQuery('#en_dbsc_shipping_origin_street_address').val() + ', ' + jQuery('#en_dbsc_shipping_origin_city').val() + ' ' + jQuery('#en_dbsc_shipping_origin_state').val() + ' ' + jQuery('#en_dbsc_shipping_origin_postal_code').val() + ', ' + jQuery('#en_dbsc_shipping_origin_country_code').val();
                        jQuery('#en-dbsc-shipping-origin-form').prepend('<div class="en-dbsc-origin-address-suggestion"><strong>Actual Address:</strong> <a href="javascript:void(0)" id="en_dbsc_actual_address" >' + actualAdress + '</a>');
                        jQuery('#en-dbsc-shipping-origin-form').prepend('<div class="en-dbsc-origin-address-suggestion"><strong>Suggested Address:</strong> <a href="javascript:void(0)" id="en_address_suggestion" >' + data.complete_Address + '</a>');
                        jQuery("#en-dbsc-shipping-origin-form").prepend('<div class="notice notice-error en_invalid_address" ><p><strong>Error! </strong>We couldn\'t validate the address you entered. Please select the suggested alternative, or confirm the address as entered.</p></div>');
                        jQuery('.en-dbsc-popup_body').animate({
                            'scrollTop': 0
                        });
                        jQuery('body').on('click', '#en_address_suggestion', function () {
                            var en_sugg_address = data.suggestion;
                            var en_address = en_sugg_address.addressLine;
                            var en_city = en_sugg_address.city;
                            var en_state = en_sugg_address.state;
                            var en_zipcode = en_sugg_address.zipcode;
                            var en_lastLine = en_sugg_address.lastLine;
                            jQuery('#en_shipping_suggestion_flag').val('yes');
                            jQuery('#en_dbsc_shipping_origin_street_address').val(en_address);
                            jQuery('#en_dbsc_shipping_origin_city').val(en_city);
                            jQuery('#en_dbsc_shipping_origin_state').val(en_state);
                            jQuery('#en_dbsc_shipping_origin_postal_code').val(en_zipcode);

                            en_dbsc_add_origin_address(event, 'NO');
                        });

                        jQuery('body').on('click', '#en_dbsc_actual_address', function () {
                            en_dbsc_add_origin_address(event, 'NO');
                        });
                    }

                } else if (typeof data.response != 'undefined' && data.response == 'error') {
                    jQuery('.en_invalid_address').remove();
                    jQuery("#en-dbsc-shipping-origin-form").prepend('<div class="notice notice-error en_invalid_address" ><p><strong>Error! </strong>' + data.message + '</p></div>');
                    jQuery('.en-dbsc-popup_body').animate({
                        'scrollTop': 0
                    }, 'slow');
                }

                if (typeof data.profile_id != 'undefined' && typeof data.message != 'undefined') {
                    show_notice(data.profile_id, data.message);
                }
            }
        });
    }
}


/**
 *  Show add shipping rate Popup
 */
function en_dbsc_add_rate(profile_id, zone_id) {

    let form_id = '#en-dbsc-add-rate-form';
    jQuery(form_id + ' #en_profile_id').val(profile_id);
    jQuery(form_id + ' #en_zone_id').val(zone_id)
    jQuery(form_id + ' #en_rate_action').val('add_rate');
    jQuery('#en-add-rate-popup h2').text('Add Rate');
    jQuery('#en-add-rate-popup').show();

    jQuery("#en_dbsc_add_rate_condition_base_and").prop('checked', true);
    jQuery("#en_dbsc_add_rate_calculution_base_origin").prop('checked', true);

    setTimeout(function () {
        jQuery('#en_dbsc_add_rate_display_as').focus();
    }, 100);
}

function en_edit_dbsc_rate(profile_id, zone_id, rate_id) {
    jQuery('.en-menu-options').hide();
    let form_id = '#en-dbsc-add-rate-form';
    jQuery.ajax({
        type: 'POST',
        url: ajaxurl,
        data: {
            action: 'en_get_zone_rate',
            rate_id: rate_id,
            wp_nonce: en_dbsc_admin_popup_script.nonce
        },
        dataType: 'json',
        beforeSend: function () {
            en_loader_to_show();
        },
        success: function (data) {
            en_loader_to_hide();
            if (data.response == 'success') {
                fill_form_data(form_id, data.result);
                jQuery(form_id + ' #en_zone_id').val(zone_id);
                jQuery(form_id + ' #en_profile_id').val(profile_id);
                jQuery(form_id + ' #en_rate_action').val('edit_rate');
                jQuery('#en-add-rate-popup h2').text('Edit Rate');
                jQuery('#en-add-rate-popup').show();
                setTimeout(function () {
                    jQuery(form_id + ' #en_dbsc_add_rate_display_as').focus();
                }, 100);
            }
        }
    });
}

/**
 * Show add shipping profile popup
 */
function en_dbsc_add_profile() {

    jQuery('.en_dbsc_shipping_profile_nickname').removeAttr('readonly');
    jQuery('#en_general_profile_condition').remove();
    jQuery('.en-shipping-class-exist-error').remove();
    jQuery("input[name=en_dbsc_profile_define_by][value=product_tags]").removeAttr('checked');
    jQuery("input[name=en_dbsc_profile_define_by][value=shipping_classes]").prop("checked", true);

    jQuery.ajax({
        type: 'POST',
        url: ajaxurl,
        data: {
            action: 'get_available_classes',
            wp_nonce: en_dbsc_admin_popup_script.nonce
        },
        dataType: 'json',
        beforeSend: function () {
            en_loader_to_show();
        },
        success: function (data) {
            en_loader_to_hide();
            if (typeof data.response != 'undefined' && data.response == 'success') {
                jQuery('.en_profile_shipping_classes').html(data.en_selected_classes_temp).trigger('change');
                jQuery('.en_dbsc_profile_product_tags').html(data.en_selected_tags_temp).trigger('change');
                jQuery('.en_profile_heading h2').text('Add shipping profile');
                jQuery('.en_profile_shipping_classes').val(null).trigger('change');
                jQuery('.en_dbsc_profile_product_tags').val(null).trigger('change');
                jQuery('.en_profile_action').val('add_profile');
                jQuery('.en_dbsc_shipping_profile_nickname').val('');
                jQuery('#en_shipping_classes').val('');
                jQuery('.en_dbsc_shipping_profile_shipping_class_name').val('');
                jQuery('.en_dbsc_shipping_profile_shipping_class_slug').val('');
                jQuery('.en_dbsc_shipping_profile_shipping_class_description').val('');

                jQuery('.en-add-shipping-profile-popup').show();
                setTimeout(function () {
                    jQuery('.en_dbsc_shipping_profile_nickname').focus();
                }, 100);
            }

        }
    });
}

function en_general_profile_condition(en_general_profile_condition) {
    jQuery("input[name=en_general_profile_condition][value=" + en_general_profile_condition + "]").prop('checked', true);
}

/**
 * Show edit shipping profile popup
 */
function en_dbsc_edit_profile(profile_id) {
    jQuery('.en_dbsc_shipping_profile_nickname').removeAttr('readonly');
    jQuery('#en_general_profile_condition').remove();

    jQuery('.en-shipping-class-exist-error').remove();
    jQuery('.en-menu-options').hide();
    jQuery('.en_dbsc_shipping_profile_nickname').val('');
    jQuery('#en_shipping_classes').val('');
    jQuery('.en_dbsc_shipping_profile_shipping_class_name').val('');
    jQuery('.en_dbsc_shipping_profile_shipping_class_slug').val('');
    jQuery('.en_dbsc_shipping_profile_shipping_class_description').val('');

    jQuery.ajax({
        type: 'POST',
        url: ajaxurl,
        data: {
            action: 'en_edit_shipping_profile',
            profile_id: profile_id,
            wp_nonce: en_dbsc_admin_popup_script.nonce
        },
        dataType: 'json',
        beforeSend: function () {
            en_loader_to_show();
        },
        success: function (data) {
            en_loader_to_hide();
            if (typeof data.response != 'undefined' && data.response == 'success') {
                if (profile_id == 1) {
                    jQuery('.en_dbsc_shipping_profile_nickname').attr('readonly', 'readonly');
                    var en_general_profile = '';
                    en_general_profile += '<div class="form-field" id="en_general_profile_condition">';
                    en_general_profile += '<div class="form-field">';
                    en_general_profile += '<input type="radio" id="en_general_profile_condition" name="en_general_profile_condition" value="en_for_all_products" checked="checked">';
                    en_general_profile += 'Use the General Profile for all products not included in another shipping profile';
                    en_general_profile += '</div>';
                    en_general_profile += '<div class="form-field">';
                    en_general_profile += '<input type="radio" id="en_general_profile_condition" name="en_general_profile_condition" value="en_for_specific_products">';
                    en_general_profile += 'Use the General Profile only for products that have the following Shipping Class(es)/Product Tag(s)';
                    en_general_profile += '</div>';
                    en_general_profile += '</div>';
                    jQuery('.en_dbsc_shipping_profile_nickname').closest('div').after(en_general_profile);
                    en_general_profile_condition(data.en_general_profile_condition);

                }
                jQuery('.en_selected_classes_temp').remove();
                jQuery('.en_profile_heading h2').text('Edit shipping profile');
                jQuery('.en_dbsc_shipping_profile_nickname').val(data.en_profile.profile_nickname);
                if(data.en_profile.profile_define_by == 'product_tags'){
                    jQuery('#en_dbsc_profile_define_by_product_tags').prop('checked', true);
                    jQuery('.en_dbsc_profile_product_tags').html(data.en_profile.en_selected_classes_temp).trigger('change');
                    jQuery('.en-dbsc-shipping-class-list-div').hide();
                    jQuery('.en-dbsc-add-new-shipping-class-button-div').hide();
                    jQuery('.en-dbsc-product-tags-list-div').show();
                }else{
                    jQuery('#en_dbsc_profile_define_by_shipping_classes').prop('checked', true);
                    jQuery('.en_profile_shipping_classes').html(data.en_profile.en_selected_classes_temp).trigger('change');
                    jQuery('.en-dbsc-product-tags-list-div').hide();
                    jQuery('.en-dbsc-shipping-class-list-div').show();
                    jQuery('.en-dbsc-add-new-shipping-class-button-div').show();
                }
                jQuery('.en_profile_action').val('edit_profile');
                jQuery('.en_profile_id').val(profile_id);
                jQuery('#en_shipping_classes_error_msg').hide();
                jQuery('.en-add-shipping-profile-popup').show();
                setTimeout(function () {
                    jQuery('.en_dbsc_shipping_profile_nickname').focus();
                }, 100);
            }

        }
    });

}

/**
 * Show shipping profile popup
 */
function en_dbsc_delete_record(profile_id, parent_id, element_id, current_action) {

    jQuery('#en_delete_record_form').find('input[type=hidden]').each(function () {
        this.value = '';
    });

    jQuery('.en_action_for').val(current_action);
    jQuery('.en_delete_action_to_record').text(current_action);

    switch (current_action) {

        case 'profile':
            jQuery('.en_hidden_profile_id').val(element_id);
            break;

        case 'origin':
            jQuery('.en_hidden_profile_id').val(parent_id);
            jQuery('.en_hidden_origin_id').val(element_id);
            break;

        case 'zone':
            jQuery('.en_hidden_profile_id').val(profile_id);
            jQuery('.en_hidden_origin_id').val(parent_id);
            jQuery('.en_hidden_zone_id').val(element_id);
            break;

        case 'rate':
            jQuery('.en_hidden_profile_id').val(profile_id);
            jQuery('.en_hidden_zone_id').val(parent_id);
            jQuery('.en_hidden_rate_id').val(element_id);
            break;

    }
    jQuery('.en-menu-options').hide();
    jQuery('#en-delete-confirm-popup').show();
}

/**
 * Delete shipping profile
 */
function en_dbsc_delete_record_action() {

    var form_data = jQuery('#en_delete_record_form').serialize();
    jQuery.ajax({
        type: 'POST',
        url: ajaxurl,
        data: {
            action: 'en_dbsc_delete_record_action',
            form_data: form_data,
            wp_nonce: en_dbsc_admin_popup_script.nonce
        },
        dataType: 'json',
        beforeSend: function () {
            en_loader_to_show();
        },
        success: function (data) {
            en_loader_to_hide();
            if (data.response == 'success') {

                let message_div = jQuery('.en_distance_base_shipping_quote_error-' + data.profile_id);

                jQuery('#en-delete-confirm-popup').hide();
                jQuery('.en_distance_base_shipping_quote_error').html('');
                message_div.html('');

                switch (data.action) {

                    case 'profile':
                        jQuery('#edit-profile-id-' + data.profile_id).remove();
                        break;

                    case 'origin':
                        // New change

                        let origin_item = jQuery('.en-shipping-origin-list-item-' + data.origin_id).closest('.en-shipping-origin-list-item');
                        if (jQuery('.en-shipping-origin-list-item-' + data.origin_id).closest('.en_shipping_from').find('.en-shipping-origin-list-item').length > 1) {
                            origin_item.remove();
                        } else {
                            let ship_profile_item = jQuery('#edit-profile-id-' + data.profile_id).find('.en_shipping_from');
                            if (ship_profile_item.length > 1) {
                                jQuery('.en-shipping-origin-list-item-' + data.origin_id).closest('.en_shipping_from').remove();
                            } else {
                                let common_origin_order = jQuery('#edit-profile-id-' + data.profile_id).find('.en-common-origin-order').attr('value');
                                jQuery('#edit-profile-id-' + data.profile_id).find('.en-add-shipping-origin').html('<a href="#en_dbsc_add_shipping_origin" onclick="en_dbsc_add_shipping_origin(' + data.profile_id + ',0, ' + common_origin_order + ')" title="Add Shipping Origin">Add shipping origin</a>');
                                origin_item.next('.en_shipping_to').remove();
                                origin_item.remove();
                            }
                        }

                        break;

                    case 'zone':
                        jQuery('.en_zone_details' + data.zone_id).remove();
                        break;

                    case 'rate':
                        jQuery('#en_zone' + data.zone_id + '_rate' + data.rate_id).remove();
                        break;

                }

                message_div.append('<div class="notice notice-success en_dbsc_success_msg"><p><strong>Success!</strong> ' + data.message + '</p></div>').show('slow').delay(5000).hide('slow');
                jQuery('html, body').animate({
                    'scrollTop': jQuery('.en_dbsc_success_msg').position().top
                });
            }

        }
    });
}

function ajax_request(params, data, call_back_function) {
    jQuery.ajax({
        type: "POST",
        url: ajaxurl,
        data: data,
        beforeSend: function () {
            (typeof params.loading_id != 'undefined' && params.loading_id.length > 0) ? jQuery("#" + params.loading_id).css('background', 'rgba(255, 255, 255, 1) url(' + script.pluginsUrl + '/distance-base-shipping-calculator/admin/popup/assets/images/processing.gif) no-repeat scroll 50% 50%') : "";
            (typeof params.disabled_id != 'undefined' && params.disabled_id.length > 0) ? jQuery("#" + params.disabled_id).prop({disabled: true}) : "";
            (typeof params.loading_msg != 'undefined' && params.loading_msg.length > 0 && typeof params.disabled_id != 'undefined' && params.disabled_id.length > 0) ? jQuery("#" + params.disabled_id).after(params.loading_msg) : "";
        },
        success: function (response) {
            jQuery('.notice-dismiss-bin-php').remove();
            (typeof params.loading_id != 'undefined' && params.loading_id.length > 0) ? jQuery("#" + params.loading_id).css('background', '#fff') : "";
            (typeof params.loading_id != 'undefined' && params.loading_id.length > 0) ? jQuery("#" + params.loading_id).focus() : "";
            (typeof params.disabled_id != 'undefined' && params.disabled_id.length > 0) ? jQuery("#" + params.disabled_id).prop({disabled: false}) : "";
            (typeof params.loading_msg != 'undefined' && params.loading_msg.length > 0 && typeof params.disabled_id != 'undefined' && params.disabled_id.length > 0) ? jQuery("#" + params.disabled_id).next('.suspend-loading').remove() : "";
            return call_back_function(params, response);
        },
        error: function () {
        }
    });
}

/**
 * Show add shipping zone popup
 */
function en_dbsc_add_shipping_zone(profile_id, origin_id) {

    let form_id = '#en-dbsc-shipping-zone-form';
    jQuery('.en-dbsc-postal-codes-div').hide();
    jQuery('.en_by_country').prop('checked', true);
    jQuery(form_id + ' #en_zone_locations').val(null).change();
    jQuery('#en-add-shipping-zone-popup').show();
    jQuery('#no-origin-found').hide();

    // TODO
    let origin_order = 1;
    if (jQuery('.en-shipping-origin-list-item-' + origin_id).length) {
        origin_order = jQuery('.en-shipping-origin-list-item-' + origin_id).closest('.en_shipping_from').find('.en-common-origin-order').attr('value');
    }
    jQuery(form_id + ' #origin_order').val(origin_order);
    jQuery(form_id + ' #profile_id').val(profile_id);
    jQuery(form_id + ' #origin_id').val(origin_id);
    jQuery(form_id + ' #zone_id').val('new');
    jQuery(form_id + ' #en_zone_action').val('add_zone');
    setTimeout(function () {
        jQuery('.en_dbsc_shipping_zone_name').focus();
    }, 100);
}


function en_dbsc_edit_shipping_zone(profile_id, origin_id, zone_id) {
    let form_id = '#en-dbsc-shipping-zone-form';

    jQuery('.en-shipping-region-exist-error').remove();

    jQuery.ajax({
        type: 'POST',
        url: ajaxurl,
        data: { action: 'en_get_shipping_zone', zone_id: zone_id, wp_nonce: en_dbsc_admin_popup_script.nonce },
        dataType: 'json',
        beforeSend: function () {
            en_loader_to_show();
        },
        success: function (response) {
            en_loader_to_hide();

            if (response.message == 'success') {
                jQuery(form_id + ' #origin_id').val(origin_id);
                jQuery(form_id + ' #profile_id').val(profile_id);
                jQuery(form_id + ' #zone_id').val(zone_id);
                jQuery(form_id + ' #en_zone_action').val('edit_zone');
                jQuery(form_id + ' #en_zone_name').val(response.name);
                jQuery(form_id + ' .en_by_postal_code,' + form_id + '.en_by_country').removeAttr('checked');

                if (response.define_by === 'by_postal_code') {
                    jQuery(form_id + ' .en_by_postal_code').prop('checked', true);
                    jQuery('.en-dbsc-postal-codes-div').show();
                    jQuery(form_id + ' #en_zone_postcodes').val(response?.zip_codes?.join('\n'));
                } else {
                    jQuery('.en-dbsc-postal-codes-div').hide();
                    jQuery(form_id + ' .en_by_country').prop('checked', true);
                }
                jQuery(form_id + ' #en_zone_locations').val(response.locations).change();
                jQuery('.en-menu-options').hide();
                jQuery('#en-add-shipping-zone-popup').show();
                setTimeout(function () {
                    jQuery('.en_dbsc_shipping_zone_name').focus();
                }, 100);

            }
        }
    });
}

/**
 * Show add shipping origin Popup
 */
function en_dbsc_add_shipping_origin(profile_id, current_origin_id, origin_order) {
    // New Change
    if (current_origin_id > 0) {
        jQuery('.en_add_the_shipping_origin').css({'display': 'block'});
        jQuery('#en_add_the_shipping_origin_id').val(current_origin_id);
    } else {
        jQuery('.en_add_the_shipping_origin').css({'display': 'none'});
        jQuery('#en_add_the_shipping_origin_id').val(0);
    }

    jQuery("input[name=en_add_the_shipping_origin][value=en_as_a_new_shipping_from_profile]").removeAttr('checked');
    jQuery("input[name=en_add_the_shipping_origin][value=en_to_this_shipping_from_profile]").prop("checked", true);

    origin_order = origin_order > 0 ? origin_order : 1;
    jQuery('#en_origin_order').val(origin_order);
    jQuery('.en_invalid_address').remove();
    jQuery('.en-dbsc-origin-address-suggestion').remove();
    jQuery('.en-menu-options').hide();
    jQuery('#en-add-shipping-origin-popup h2').text('Add shipping origin');
    jQuery('.en_dbsc_shipping_origin_nickname').val('');
    jQuery('.en_dbsc_shipping_origin_street_address').val('');
    jQuery('.en_dbsc_shipping_origin_city').val('');
    jQuery('.en_dbsc_shipping_origin_state').val('');
    jQuery('.en_dbsc_shipping_origin_postal_code').val('');
    jQuery('.en_dbsc_shipping_origin_country_code').val('');
    jQuery(".en_dbsc_shipping_origin_available_in_other option:selected").removeAttr("selected");
    jQuery(".en_dbsc_shipping_origin_available_in_other").parent().show();
    jQuery('.en_origin_profile_id').val(profile_id);
    jQuery('.en_shipping_origin_action').val('add_shipping_origin')
    jQuery('#en-add-shipping-origin-popup').show();
    setTimeout(function () {
        jQuery('#en_dbsc_shipping_origin_nickname').focus();
    }, 100);
}
;

/**
 * Show edit shipping origin popup
 */
function en_edit_dbsc_shipping_origin(profile_id, origin_id, origin_order) {

    jQuery('#en_origin_order').val(origin_order);
    jQuery('#en_add_the_shipping_origin_id').val(origin_id);

    jQuery('.en-menu-options').hide();
    // New Change
    jQuery('.en_add_the_shipping_origin').hide();
    jQuery('.en_dbsc_shipping_origin_nickname').val('');
    jQuery('.en_dbsc_shipping_origin_street_address').val('');
    jQuery('.en_dbsc_shipping_origin_city').val('');
    jQuery('.en_dbsc_shipping_origin_state').val('');
    jQuery('.en_dbsc_shipping_origin_postal_code').val('');
    jQuery('.en_dbsc_shipping_origin_country_code').val('');
    jQuery('.en_invalid_address').remove();
    jQuery('.en-dbsc-origin-address-suggestion').remove();
    jQuery(".en_dbsc_shipping_origin_available_in_other option:selected").removeAttr("selected");
    jQuery('.en_origin_profile_id').val(profile_id);
    jQuery('.en_shipping_origin_id').val(origin_id);
    jQuery('.en_shipping_origin_action').val('update_shipping_origin');

    jQuery.ajax({
        type: 'POST',
        url: ajaxurl,
        data: {
            action: 'en_edit_dbsc_shipping_origin',
            profile_id: profile_id,
            origin_id: origin_id,
            en_shipping_origin_action: 'edit_shipping_origin',
            wp_nonce: en_dbsc_admin_popup_script.nonce
        },
        dataType: 'json',
        beforeSend: function () {
            en_loader_to_show();
        },
        success: function (data) {

            en_loader_to_hide();

            if (data.response == 'success') {

                jQuery('#en-add-shipping-origin-popup h2').text('Edit shipping origin');
                jQuery('.en_dbsc_shipping_origin_nickname').val(data.params.nickname);
                jQuery('.en_dbsc_shipping_origin_street_address').val(data.params.street_address);
                jQuery('.en_dbsc_shipping_origin_city').val(data.params.city);
                jQuery('.en_dbsc_shipping_origin_state').val(data.params.state);
                jQuery('.en_dbsc_shipping_origin_postal_code').val(data.params.postal_code);
                jQuery('.en_dbsc_shipping_origin_country_code').val(data.params.country_code);
                jQuery(".en_dbsc_shipping_origin_available_in_other").val(data.params.availability).parent().hide();
                jQuery('.en-add-shipping-origin-popup').show();
                setTimeout(function () {
                    jQuery('.en_dbsc_shipping_origin_nickname').focus();
                }, 100);
            }

        }
    });

}

/**
 * when user switch from disable to plan popup hide
 * @returns {jQuery}
 */
var en_woo_dbsc_popup_notifi_disabl_to_plan_hide = function () {
    jQuery(".sm_notification_disable_to_plan_overlay_dbsc").hide();
    return jQuery(".sm_notification_disable_to_plan_overlay_dbsc").css({visibility: "hidden", opacity: "0"});
};

/**
 * when user switch from disable to plan popup show
 * @returns {jQuery}
 */
var en_woo_dbsc_popup_notifi_disabl_to_plan_show_box = function () {
    var selected_plan = jQuery("#en_connection_settings_auto_renew_distance_base_shipping").find("option:selected").text();
    jQuery(".sm_notification_disable_to_plan_overlay_dbsc").last().find("#selected_plan_popup_box").text(selected_plan);
    jQuery(".sm_notification_disable_to_plan_overlay_dbsc").show();
    return jQuery(".sm_notification_disable_to_plan_overlay_dbsc").css({visibility: "visible", opacity: "1"});
};

/**
 * toggle menu for edit/delete
 */
function en_toggle_menu($this) {
    if (jQuery($this).parent().siblings('.en-menu-options').css("display") == "block") {
        jQuery($this).parent().siblings('.en-menu-options').slideUp();
    } else {
        jQuery('.en-menu-options').hide();
        jQuery($this).parent().siblings('.en-menu-options').slideToggle();
        return false;
    }
}


function fill_form_data(form_id, data) {
    for (let id in data) {
        let value = data[id];
        if (id == '#en_dbsc_add_rate_condition_base' || id == '#en_dbsc_add_rate_calculution_base' 
        || id == '#en_dbsc_add_rate_distance_display_preference' || id == '#en_dbsc_default_unknown_address_type'
        || id == '#en_dbsc_add_rate_cart_value_type') {
            jQuery(form_id + ' ' + id + '_' + value).prop('checked', true);
        } else {
            jQuery(form_id + ' ' + id).val(value);
        }
    }
}


/**
 * This function resets all of the form elements including hidden
 * @param form_id
 */
function reset_form(form_id) {

    let validator = jQuery(form_id).validate();
    validator.resetForm();
    jQuery(form_id).trigger("reset");
    jQuery(form_id + ' input[type=hidden]').each(function () {
        this.value = '';
    });
}

function hide_edit_delete_popup($this) {
    let ref = jQuery($this).parents('.en-dbsc-popup_container');
    let form_id = '#' + ref.find('form').attr("id");
    reset_form(form_id);
    let popup_id = ref.parent().attr("id");
    switch (popup_id) {
        case 'en-add-shipping-profile-popup' :
            jQuery('.en-dbsc-shipping-class-search-div').show();
            jQuery('.en-dbsc-add-shipping-class').hide();
            jQuery('#en-add-profile-cancel').text('Cancel');
            jQuery(form_id + ' .en_profile_shipping_classes').val(null).trigger('change');
            break;

        case 'en-add-shipping-zone-popup' :
            jQuery(form_id).find('#en_zone_locations').val(null).trigger('change');
            jQuery(form_id + ' .en-dbsc-postal-codes-div').hide();
            break;
    }
    jQuery('#' + popup_id).hide();
}

function show_notice(profile_id, message) {
    if (message.length > 0 && profile_id > 0) {
        let notice = jQuery('.en_distance_base_shipping_quote_error-' + profile_id);

        notice.html('');
        notice.append('<div class="notice notice-success en_shipping_origin_other_success_msg"><p><strong>Success!</strong> ' + message + '</p></div>');
        notice.show('slow').delay(5000).hide('slow');
        jQuery('html, body').animate({
            'scrollTop': jQuery('.en_shipping_origin_other_success_msg').position().top
        });
    }
}

function en_loader_to_hide() {
    jQuery(".en_loader_overly_template").css({visibility: "hidden", opacity: "0"});
}

function en_loader_to_show() {
    jQuery(".en_loader_overly_template").css({visibility: "visible", opacity: "0.4"});
}
