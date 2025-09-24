// JS code related to WP admin integrations of the WooCommerce Moneybird plugin.
// This script is only loaded on the following WP admin pages:
// - WooCommerce settings pages
// - Product edit pages


function wcmb_init_multichecks() {
    // Initialize the multi-checks field on the Moneybird settings page

    // If 'all' checkbox is checked, make sure the specific ones are checked as well
    jQuery('.woocommerce_page_wc-settings .payment_method_triggers').each(function() {
        var all_input = jQuery(this).children('.pmf_all').first();
        if (all_input.is(":checked")) {
            jQuery(this).children('input[type=checkbox]').prop("checked", true);
        }
    });

    // Show/hide payment method filters
    jQuery('.woocommerce_page_wc-settings .trigger_multichecks_input').change(function() {
        if (jQuery(this).is(":checked")) {
            // Enabling an order status trigger automatically checks all payment methods
            jQuery('#' + jQuery(this).attr('id') + '_payment_triggers input[type=checkbox]').prop("checked", true);
            jQuery('#' + jQuery(this).attr('id') + '_payment_triggers').show();
        } else {
            // Disabling an order status trigger automatically unchecks all payment methods
            jQuery('#' + jQuery(this).attr('id') + '_payment_triggers input[type=checkbox]').prop("checked", false);
            jQuery('#' + jQuery(this).attr('id') + '_payment_triggers').hide();
        }
    });

    // Conditional logic for 'all' checkbox
    jQuery('.woocommerce_page_wc-settings .payment_method_trigger_filter').change(function() {
        if (jQuery(this).hasClass('pmf_all')) {
            if (jQuery(this).is(":checked")) {
                jQuery(this).parent().children('input[type=checkbox]').prop("checked", true);
            }
        } else {
            if (!jQuery(this).is(":checked")) {
                jQuery(this).parent().children('input[type=checkbox].pmf_all').prop("checked", false);
            }
        }
    });
}


function wcmb_fix_popup_height() {
    if (window.innerHeight > 720) {
        jQuery('#mb_auth_button').attr('href', jQuery('#mb_auth_button').attr('href') + '&height=700');
    }
}

function update_conditional_display(el) {
    // Show or hide a jQuery element based on whether the
    // selector in data-display-if matches one or more elements.
    if (el.length != 1) {
        return;
    }
    const display_if = el.data('display-if');
    let parent_el = null;
    if (el.data('display-parent-selector')) {
        parent_el = el.parents(el.data('display-parent-selector')).first();
    }
    if (display_if) {
        const conditions_met = (jQuery(display_if).length > 0);
        if (jQuery(display_if).length > 0) {
            el.show();
            if (parent_el) {
                parent_el.show();
            }
        } else {
            el.hide();
            if (parent_el) {
                parent_el.hide();
            }
        }
    }
}


function init_conditional_display() {
    // Initialize conditional display elements.
    // This function should be called from jQuery(document).ready or equivalent.
    // It attaches listeners to the dependencies and performs initial calls
    // to update_conditional_display for all conditional display elements.
    const elements = jQuery('.conditional_display[data-display-dependency]');
    elements.each(function() {
        const el = jQuery(this);
        const dependencies = jQuery(el.data('display-dependency'));
        if (dependencies.length < 1) {
            return;
        }
        var dep_trigger = el.data('display-dependency-trigger');
        if (typeof dep_trigger == 'undefined') {
            dep_trigger = 'change';
        }
        dependencies.on(dep_trigger, function() {
            update_conditional_display(el);
        });
        update_conditional_display(el);
    });
}


jQuery(document).ready(function() {
    if (jQuery('body').hasClass('woocommerce_page_wc-settings')) {
        wcmb_fix_popup_height();
        wcmb_init_multichecks();
        let estimates_checkbox = jQuery('.wcmb-estimates-enabled');
        if (estimates_checkbox.length === 1) {
            if (!estimates_checkbox.is(':checked')) {
                estimates_checkbox.parents('tbody').find('tr').not(':first').hide();
            }
            estimates_checkbox.on('change',function() {
                let estimates_checkbox = jQuery('.wcmb-estimates-enabled');
                if (estimates_checkbox.is(':checked')) {
                    estimates_checkbox.parents('tbody').find('tr').not(':first').show();
                } else {
                    estimates_checkbox.parents('tbody').find('tr').not(':first').hide();
                }
            });
        }
    }
    init_conditional_display();
});