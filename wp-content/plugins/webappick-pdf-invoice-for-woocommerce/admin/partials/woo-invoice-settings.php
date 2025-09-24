<?php

/**
 * Provide settings view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link  https://webappick.com
 * @since 1.0.0
 *
 * @package    Woo_Invoice
 * @subpackage Woo_Invoice/admin/partials
 */

// Checkbox Value to compare.
$woo_invoice_current = 1;

$woo_invoice_logo_dir   = plugin_dir_url(dirname(__FILE__)) . 'images/challan-logo.svg';
$woo_invoice_banner_dir = plugin_dir_url(dirname(__FILE__)) . 'images/get-challan-pro.svg';
$woo_invoice_banner_logo_dir = plugin_dir_url(dirname(__FILE__)) . 'images/challan-pro-logo-banner.png';

$woo_invoice_style  = 'max-width:80%;display:block;margin:0 auto;border: 3px solid #0F74A6;';
$woo_invoice_style2 = 'max-width:80%;display:block;margin:0 auto;border: 3px solid #1AA15F;';


if (substr(get_option('wpifw_logo_attachment_id'), 0, 7) === 'http://' || substr(get_option('wpifw_logo_attachment_id'), 0, 8) === 'https://') {
    $woo_invoice_image_id       = attachment_url_to_postid(get_option('wpifw_logo_attachment_id'));
    $woo_invoice_full_size_path = get_attached_file($woo_invoice_image_id);
    update_option('wpifw_logo_attachment_id', $woo_invoice_full_size_path);
    update_option('wpifw_logo_attachment_image_id', $woo_invoice_image_id);
}


// Allow to download invoice from my account base on order status.
$wpifw_invoice_download_check_list = (get_option('wpifw_invoice_download_check_list') == false || is_null(get_option('wpifw_invoice_download_check_list'))) ? array() : get_option('wpifw_invoice_download_check_list');
// Attach Invoice with email based on order status.
$wpifw_email_attach_check_list = (get_option('wpifw_email_attach_check_list') == false || is_null(get_option('wpifw_email_attach_check_list'))) ? array() : get_option('wpifw_email_attach_check_list');



?>


<div class="wrap">
    <h1 class="wp-heading-inline">Challan</h1>
</div><!-- end .wrap -->

<?php

require_once CHALLAN_FREE_ROOT_FILE_PATH . 'admin/partials/templates/font-downloader-template.php';
?>
<div class="woo-invoice-wrap">
    <div class="woo-invoice-dashboard-header btn-group-justify ">
        <div class="woo-invoice-btn-group-left">
            <div>
                <span class="woo-invoice-version"><?php echo 'Version : ' . esc_html(CHALLAN_FREE_VERSION) ?></span>
                <a class="wapk-woo-invoice-free-btn" href="<?php echo esc_url('https://webappick.com/plugin/woocommerce-pdf-invoice-packing-slips/?utm_source=customer_site&utm_medium=free_vs_pro&utm_campaign=woo_invoice_free'); ?>" target="_blank">
                    <img src="<?php echo esc_url($woo_invoice_logo_dir); ?>" alt="Woo Invoice">
                </a>
            </div>
            <div>
                <a class="wapk-woo-invoice-pro-btn" href="<?php echo esc_url('https://webappick.com/plugin/woocommerce-pdf-invoice-packing-slips/?utm_source=customer_site&utm_medium=free_vs_pro&utm_campaign=woo_invoice_free'); ?>" target="_blank">
                    <img src="<?php echo esc_url($woo_invoice_banner_dir); ?>" alt="Woo Invoice">
                </a>
            </div>
            <!-- <span>
                <?php //echo 'Version : ' . esc_html(CHALLAN_FREE_VERSION) 
                ?>
                <a class="wapk-woo-invoice-admin-logo" href="<?php //echo esc_url('https://webappick.com/plugin/woocommerce-pdf-invoice-packing-slips/?utm_source=customer_site&utm_medium=free_vs_pro&utm_campaign=woo_invoice_free'); 
                                                                ?>" target="_blank"><img src="<?php echo esc_url($woo_invoice_logo_dir); ?>" alt="Woo Invoice"></a>
            </span>

            <a class="wapk-woo-invoice-get-product-btn" href="<?php //echo esc_url('https://webappick.com/plugin/woocommerce-pdf-invoice-packing-slips/?utm_source=customer_site&utm_medium=free_vs_pro&utm_campaign=woo_invoice_free'); 
                                                                ?>" target="_blank"><img src="<?php echo esc_url($woo_invoice_banner_dir); ?>" alt="Woo Invoice"></a>
        -->
        </div>

        <div class="woo-invoice-btn-group">
            <!-- <div class="facebook-btn">
                <a target="__balnk" href="<?php //echo esc_url('https://www.facebook.com/groups/chalanpdfinvoice'); 
                                            ?>">
                    <img src="data:image/gif;base64,R0lGODlhIAAVALIAAP////9SAN4AAP//ALUAOQAAAAAAAAAAACH/C05FVFNDQVBFMi4wAwH4fwAh+QQJCgAFACwAAAAAIAAVAAADali63P4wqiCrDNRqFkTeWueFWGme4lilQuu+sBp1g1sP+N3WQv09NJ7OtivKHMEiT9lrtn6cpi7HHD6BTpzTdlsKCBDazmrcgsPbrjbdOqNjcJgbQqjb73iCaw6it/sWenyAD3WEgYeJEQkAIfkECQoABQAsAwABABkAEwAAA2BYutzRMMLwpHWi3htEnlQojp2nKaWnrqx6doMaD/TsxUKswbgt38BPgQfEFXNI0xBpqx19JtgNOs0ZBYRlj5rsZrW/Wu+HXaRaaNVXQWi733CCemORl+kS+RoPafPrFgkAIfkECQoABQAsBwABABkAEwAAA2BYutzRMMLwpHWi3htEnlQojp2nKaWnrqx6doMaD/TsxUKswbgt38BPgQfEFXNI0xBpqx19JtgNOs0ZBYRlj5rsZrW/Wu+HXaRaaNVXQWi733CCemORl+kS+RoPafPrFgkAIfkECQoABQAsAwABABkAEwAAA2BYutzRMMLwpHWi3htEnlQojp2nKaWnrqx6doMaD/TsxUKswbgt38BPgQfEFXNI0xBpqx19JtgNOs0ZBYRlj5rsZrW/Wu+HXaRaaNVXQWi733CCemORl+kS+RoPafPrFgkAIf4fT3B0aW1pemVkIGJ5IFVsZWFkIFNtYXJ0U2F2ZXIhAAA7" alt="<?php echo esc_attr('facebook'); ?>"> <?php echo esc_html__('Join Facebook Group', 'webappick-pdf-invoice-for-woocommerce'); ?>
                </a>
            </div> -->
            <a class="wapk-woo-invoice-btn documentation" target="_blank" href="<?php echo esc_url('https://webappick.com/docs/woo-invoice'); ?>">
                <svg width="14" height="14" viewBox="-2.5 0 32 32" xmlns="http://www.w3.org/2000/svg">
                    <path d="M20 0v4.981c0 1.63 1.142 3.02 2.659 3.02L27 8v21a3 3 0 0 1-3 3H3a3 3 0 0 1-3-3V3a3 3 0 0 1 3-3h17Zm-1 19H8a1 1 0 1 0 0 2h11a1 1 0 1 0 0-2Zm0-4H8a1 1 0 1 0 0 2h11a1 1 0 1 0 0-2Zm0-4H8a1 1 0 1 0 0 2h11a1 1 0 1 0 0-2ZM22.001.259a2 2 0 0 1 .338.24l3.982 3.506a2 2 0 0 1 .679 1.5V6h-4.341C22.343 6 22 5.583 22 4.981l.001-4.722Z" fill="#fff" />
                </svg>
                <span>
                    <?php esc_html_e('Documentation', 'webappick-pdf-invoice-for-woocommerce'); ?>
                </span>
            </a>
            <a class="wapk-woo-invoice-btn support" target="_blank" href="https://wordpress.org/support/plugin/webappick-pdf-invoice-for-woocommerce/#new-topic-0">
                <svg width="15" height="15" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <g fill="none" stroke="#fff" stroke-linecap="round" stroke-linejoin="round" stroke-width="2">
                        <circle cx="12" cy="12" r="4" transform="rotate(-45 11.985 12.036)" />
                        <circle cx="12" cy="12" r="10" transform="rotate(-45 11.985 12.036)" />
                        <path d="m5 5 4 4m6 0 4-4m-4 10 4 4M9 15l-4 4" />
                    </g>
                </svg>
                <span>
                    <?php esc_html_e('Get free support', 'webappick-pdf-invoice-for-woocommerce'); ?>
                </span>
            </a>
        </div>
    </div><!-- end .woo-invoice-dashboard-header -->
    <div class="woo-invoice-dashboard-body" dir="auto">
        <div class="woo-invoice-dashboard-sidebar" style="float: <?php echo is_rtl() ? 'right' : 'left'; ?>">
            <div class="woo-invoice-sidebar-navbar woo-invoice-sidebar-navbar-vertical woo-invoice-fixed-left woo-invoice-sidebar-navbar-expand-md woo-invoice-sidebar-navbar-light" id="woo-invoice-sidebar">
                <div class="container-fluid">
                    <!-- Toggler -->
                    <button class="woo-invoice-sidebar-navbar-toggler" type="button" data-toggle="collapse" data-target="#webappickSidebarCollapse" aria-controls="webappickSidebarCollapse" aria-expanded="false" aria-label="Toggle woo-invoice-navigation">
                        <span class="woo-invoice-sidebar-navbar-toggler-icon"></span>
                    </button>

                    <!-- Brand -->
                    <!-- <a class="woo-invoice-sidebar-navbar-brand" href="https://webappick.com"><img src="../wp-content/plugins/woo-invoice-boilerplate/admin/images/woo-invoice-logo.png" alt="WEBAPPICK" style="width:100px;"></a> -->

                    <!-- Collapse -->
                    <ul class="collapse woo-invoice-sidebar-navbar-collapse" id="webappickSidebarCollapse">

                        <ul class="woo-invoice-sidebar-navbar-nav woo-invoice-mb-md-4">
                            <li class="woo-invoice-sidebar-nav-item">
                                <a class="woo-invoice-sidebar-nav-link" href="#">
                                    <span class="_winvoice-menu-thumbnail">
                                        <img src="data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iaXNvLTg4NTktMSI/Pg0KPCEtLSBHZW5lcmF0b3I6IEFkb2JlIElsbHVzdHJhdG9yIDE5LjAuMCwgU1ZHIEV4cG9ydCBQbHVnLUluIC4gU1ZHIFZlcnNpb246IDYuMDAgQnVpbGQgMCkgIC0tPg0KPHN2ZyB2ZXJzaW9uPSIxLjEiIGlkPSJMYXllcl8xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB4PSIwcHgiIHk9IjBweCINCgkgdmlld0JveD0iMCAwIDQyMy42MjMgNDIzLjYyMyIgc3R5bGU9ImVuYWJsZS1iYWNrZ3JvdW5kOm5ldyAwIDAgNDIzLjYyMyA0MjMuNjIzOyIgeG1sOnNwYWNlPSJwcmVzZXJ2ZSI+DQo8Zz4NCgk8cGF0aCBzdHlsZT0iZmlsbDojNDU0QjU0OyIgZD0iTTQxMS42MjMsMjI0LjQxMmMtNi44LDAtMTItNS4yLTEyLTEyczUuMi0xMiwxMi0xMnMxMiw1LjIsMTIsMTJ2MC40DQoJCUM0MjMuNjIzLDIxOC44MTIsNDE4LjQyMywyMjQuNDEyLDQxMS42MjMsMjI0LjQxMnoiLz4NCgk8cGF0aCBzdHlsZT0iZmlsbDojNDU0QjU0OyIgZD0iTTE5OC40MjMsNDIzLjYxMmMtMC40LDAtMC40LDAtMC44LDBoLTAuNGMtNi44LTAuNC0xMS42LTYtMTEuMi0xMi44YzAuNC02LjgsNi0xMS42LDEyLjgtMTEuMmgwLjQNCgkJYzYuOCwwLjQsMTEuNiw2LjQsMTEuMiwxMi44UzIwNC40MjMsNDIzLjYxMiwxOTguNDIzLDQyMy42MTJ6IE0yMzguODIzLDQyMi4wMTJjLTYsMC0xMS4yLTQuNC0xMi0xMC40Yy0wLjgtNi40LDMuNi0xMi44LDEwLTEzLjYNCgkJaDAuNGM2LjQtMC44LDEyLjgsMy42LDEzLjYsMTAuNGMwLjgsNi40LTMuNiwxMi44LTEwLjQsMTMuNmgtMC40QzI0MC4wMjMsNDIyLjAxMiwyMzkuMjIzLDQyMi4wMTIsMjM4LjgyMyw0MjIuMDEyeg0KCQkgTTE1OC4wMjMsNDE2LjgxMmMtMS4yLDAtMi40LDAtMy4yLTAuNGwzLjItMTEuNmwtMy42LDExLjZjLTYuNC0xLjYtMTAtOC40LTguNC0xNC44czguNC0xMCwxNC44LTguNGgwLjhjNi40LDIsMTAsOC40LDguNCwxNC44DQoJCUMxNjguMDIzLDQxMy4yMTIsMTYzLjIyMyw0MTYuODEyLDE1OC4wMjMsNDE2LjgxMnogTTI3OC40MjMsNDEyLjQxMmMtNC44LDAtOS42LTMuMi0xMS4yLThjLTIuNC02LjQsMC44LTEzLjIsNy4yLTE1LjJoMC40DQoJCWM2LjQtMi40LDEzLjIsMC44LDE1LjIsNy4yYzIuNCw2LjQtMC44LDEzLjItNy4yLDE1LjJoLTAuNEMyODEuMjIzLDQxMi40MTIsMjgwLjAyMyw0MTIuNDEyLDI3OC40MjMsNDEyLjQxMnogTTEyMC4wMjMsNDAyLjAxMg0KCQljLTIsMC0zLjYtMC40LTUuNi0xLjJsLTAuNC0wLjRjLTYtMy4yLTgtMTAuNC00LjgtMTYuNHMxMC40LTgsMTYuNC00LjhzOC40LDEwLjQsNS4yLDE2LjQNCgkJQzEyOC40MjMsMzk5LjYxMiwxMjQuNDIzLDQwMi4wMTIsMTIwLjAyMyw0MDIuMDEyeiBNMzE1LjYyMywzOTQuODEyYy00LDAtOC0yLTEwLjQtNmMtMy42LTUuNi0xLjYtMTMuMiw0LTE2LjQNCgkJYzUuNi0zLjYsMTMuMi0yLDE2LjgsNGMzLjYsNS42LDIsMTIuOC0zLjYsMTYuNGwtMC40LDAuNEMzMTkuNjIzLDM5NC40MTIsMzE3LjYyMywzOTQuODEyLDMxNS42MjMsMzk0LjgxMnogTTg1LjYyMywzNzkuNjEyDQoJCWMtMi44LDAtNS4yLTAuOC03LjYtMi44aC0wLjRjLTUuMi00LTYtMTEuNi0yLTE2LjhjNC01LjIsMTEuNi02LDE2LjgtMmwwLjQsMC40YzUuMiw0LDYsMTEuNiwxLjYsMTYuOA0KCQlDOTIuODIzLDM3OC4wMTIsODkuMjIzLDM3OS42MTIsODUuNjIzLDM3OS42MTJ6IE0zNDguMDIzLDM3MC40MTJjLTMuMiwwLTYuNC0xLjItOC44LTRjLTQuNC00LjgtNC0xMi40LDAuOC0xNi44DQoJCXMxMi40LTQuNCwxNy4yLDAuNGM0LjQsNC44LDQuNCwxMi40LTAuNCwxNi44bC0wLjQsMC40QzM1NC4wMjMsMzY5LjIxMiwzNTAuODIzLDM3MC40MTIsMzQ4LjAyMywzNzAuNDEyeiBNNTYuODIzLDM1MC40MTINCgkJYy0zLjYsMC02LjgtMS42LTkuMi00LjRsMCwwYy00LjQtNS4yLTMuNi0xMi44LDEuNi0xNi44YzUuMi00LjQsMTIuNC0zLjYsMTYuOCwxLjZsMC40LDAuNGM0LjQsNS4yLDMuNiwxMi44LTEuNiwxNi44DQoJCUM2Mi4wMjMsMzQ5LjYxMiw1OS42MjMsMzUwLjQxMiw1Ni44MjMsMzUwLjQxMnogTTM3NC44MjMsMzM5LjYxMmMtMi40LDAtNC44LTAuOC02LjgtMi40Yy01LjItNC02LjgtMTEuMi0yLjgtMTYuOA0KCQljNC01LjYsMTEuMi02LjgsMTYuOC0zLjJjNS42LDQsNi44LDExLjIsMy4yLDE2LjRsLTAuNCwwLjRDMzgyLjQyMywzMzcuNjEyLDM3OC44MjMsMzM5LjYxMiwzNzQuODIzLDMzOS42MTJ6IE0zNC40MjMsMzE2LjQxMg0KCQljLTQuNCwwLTguNC0yLjQtMTAuOC02LjR2LTAuNGMtMy4yLTYtMC44LTEzLjIsNS4yLTE2LjRjNi0zLjIsMTMuMi0wLjgsMTYsNS4ydjAuNGMzLjIsNiwwLjgsMTMuMi01LjIsMTYuNA0KCQlDMzguMDIzLDMxNi4wMTIsMzYuMDIzLDMxNi40MTIsMzQuNDIzLDMxNi40MTJ6IE0zOTQuODIzLDMwNC4wMTJjLTEuNiwwLTMuMi0wLjQtNC40LTAuOGMtNi0yLjQtOS4yLTkuNi02LjQtMTUuNmwwLjQtMC44DQoJCWMyLjgtNiw5LjYtOC44LDE2LTZjNiwyLjgsOC44LDkuNiw2LDE2bC0xMC44LTQuOGwxMC44LDQuOEM0MDQuMDIzLDMwMS4yMTIsMzk5LjYyMywzMDQuMDEyLDM5NC44MjMsMzA0LjAxMnogTTE5LjIyMywyNzguNDEyDQoJCWMtNS4yLDAtMTAtMy42LTExLjYtOC44bDExLjYtMy4ybC0xMS42LDMuMmMtMS42LTYuNCwyLTEyLjgsOC0xNC44YzYuNC0yLDEyLjgsMS42LDE0LjgsOGwwLjQsMC44YzEuNiw2LjQtMiwxMi44LTguNCwxNC44DQoJCUMyMS4yMjMsMjc4LjQxMiwyMC4wMjMsMjc4LjQxMiwxOS4yMjMsMjc4LjQxMnogTTQwNy42MjMsMjY0LjgxMmMtMC44LDAtMS42LDAtMi40LTAuNGMtNi40LTEuMi0xMC44LTcuNi05LjItMTQuNA0KCQljMS4yLTYuNCw3LjYtMTAuOCwxNC40LTkuNmM2LjQsMS4yLDEwLjgsNy42LDkuMiwxNHYwLjRDNDE4LjAyMywyNjEuMjEyLDQxMi44MjMsMjY0LjgxMiw0MDcuNjIzLDI2NC44MTJ6IE0xMi4wMjMsMjM4LjQxMg0KCQljLTYuNCwwLTExLjYtNC44LTEyLTExLjJsMTItMC44bC0xMiwwLjRjLTAuNC02LjQsNC40LTEyLjQsMTEuMi0xMi44YzYuNC0wLjQsMTIuNCw0LjQsMTIuOCwxMC44djAuOGMwLjQsNi44LTQuOCwxMi40LTExLjIsMTIuOA0KCQlDMTIuNDIzLDIzOC40MTIsMTIuNDIzLDIzOC40MTIsMTIuMDIzLDIzOC40MTJ6IE0xMy4yMjMsMTk3LjIxMmMtMC40LDAtMS4yLDAtMS42LDBjLTYuNC0wLjgtMTEuMi03LjItMTAtMTMuNmwxMiwxLjZsLTEyLTINCgkJYzAuOC02LjQsNi44LTExLjIsMTMuNi0xMC40YzYuNCwwLjgsMTEuMiw2LjgsMTAuNCwxMy4ydjAuOEMyNC40MjMsMTkzLjIxMiwxOS4yMjMsMTk3LjIxMiwxMy4yMjMsMTk3LjIxMnogTTIyLjgyMywxNTcuNjEyDQoJCWMtMS4yLDAtMi44LTAuNC00LTAuOGMtNi40LTIuNC05LjYtOS4yLTcuMi0xNS4ybDExLjIsNGwtMTEuMi00LjRjMi02LjQsOS4yLTkuNiwxNS4yLTcuNmM2LjQsMiw5LjYsOC44LDcuNiwxNS4ybC0wLjQsMC44DQoJCUMzMi40MjMsMTU0LjQxMiwyNy42MjMsMTU3LjYxMiwyMi44MjMsMTU3LjYxMnogTTQwLjQyMywxMjAuODEyYy0yLDAtNC40LTAuNC02LjQtMS42Yy01LjYtMy42LTcuNi0xMC44LTQtMTYuNHYtMC40DQoJCWMzLjYtNS42LDEwLjgtNy42LDE2LjQtNHM3LjYsMTAuOCw0LDE2LjRsLTAuNCwwLjRDNDguNDIzLDExOC40MTIsNDQuNDIzLDEyMC44MTIsNDAuNDIzLDEyMC44MTJ6IE02NC44MjMsODguMDEyDQoJCWMtMi44LDAtNi0xLjItOC40LTMuMmMtNC44LTQuNC01LjItMTItMC40LTE2LjhsMCwwYzQuNC00LjgsMTItNS4yLDE2LjgtMC44czUuMiwxMiwwLjgsMTYuOGwwLDANCgkJQzcxLjIyMyw4Ni44MTIsNjguMDIzLDg4LjAxMiw2NC44MjMsODguMDEyeiBNOTUuNjIzLDYwLjgxMmMtMy42LDAtNy42LTEuNi05LjYtNS4yYy00LTUuMi0yLjgtMTIuOCwyLjgtMTYuOGwwLjQtMC40DQoJCWM1LjYtNCwxMi44LTIuOCwxNi44LDIuOGM0LDUuMiwyLjgsMTIuOC0yLjgsMTYuOGwtMC40LDAuNEMxMDAuNDIzLDYwLjQxMiw5OC4wMjMsNjAuODEyLDk1LjYyMyw2MC44MTJ6IE0xMzEuNjIzLDQwLjgxMg0KCQljLTQuOCwwLTkuMi0yLjgtMTEuMi03LjJjLTIuOC02LDAtMTMuMiw2LTE1LjZoMC40YzYtMi44LDEzLjIsMCwxNS42LDYuNGMyLjgsNiwwLDEzLjItNi40LDE1LjYNCgkJQzEzNC44MjMsNDAuNDEyLDEzMy4yMjMsNDAuODEyLDEzMS42MjMsNDAuODEyeiBNMTcwLjAyMywyOC40MTJjLTUuNiwwLTEwLjQtNC0xMS42LTkuNmMtMS4yLTYuNCwyLjgtMTIuOCw5LjItMTRoMC40DQoJCWM2LjQtMS42LDEyLjgsMi44LDE0LjQsOS4yYzEuMiw2LTIuOCwxMi40LTkuMiwxNGgtMC40QzE3MS42MjMsMjguNDEyLDE3MC44MjMsMjguNDEyLDE3MC4wMjMsMjguNDEyeiIvPg0KCTxwYXRoIHN0eWxlPSJmaWxsOiM0NTRCNTQ7IiBkPSJNMjExLjYyMywyNC4wMTJjLTYuOCwwLTEyLTUuMi0xMi0xMnM1LjItMTIsMTItMTJsMCwwYzYuOCwwLDEyLDUuMiwxMiwxMg0KCQlTMjE4LjQyMywyNC4wMTIsMjExLjYyMywyNC4wMTJ6Ii8+DQo8L2c+DQo8cGF0aCBzdHlsZT0iZmlsbDojRjE2Njc3OyIgZD0iTTQxMS42MjMsMjI0LjAxMmMtNi44LDAtMTItNS4yLTEyLTEyYzAtMTAzLjYtODQuNC0xODgtMTg4LTE4OGMtNi44LDAtMTItNS4yLTEyLTEyczUuMi0xMiwxMi0xMg0KCWMxMTYuOCwwLDIxMiw5NS4yLDIxMiwyMTJDNDIzLjYyMywyMTguODEyLDQxOC40MjMsMjI0LjAxMiw0MTEuNjIzLDIyNC4wMTJ6Ii8+DQo8cGF0aCBzdHlsZT0iZmlsbDojRkZGRkZGOyIgZD0iTTM1MS42MjMsMjM1LjIxMnYtNDYuNGgtMzcuNmMtMi44LTExLjYtNy4yLTIyLjgtMTMuNi0zMi40bDI2LjgtMjYuOGwtMzIuOC0zMi44bC0yNi44LDI2LjQNCgljLTEwLTYuNC0yMC44LTEwLjgtMzIuNC0xMy42di0zNy42aC00Ni44djM3LjZjLTExLjYsMi44LTIyLjgsNy4yLTMyLjQsMTMuNmwtMjYuOC0yNi44bC0zMi44LDMyLjhsMjYuOCwyNi44DQoJYy02LjQsMTAtMTAuOCwyMC44LTEzLjYsMzIuNGgtMzh2NDYuOGgzNy42YzIuOCwxMS42LDcuMiwyMi44LDEzLjYsMzIuNGwtMjYuOCwyNi44bDMyLjgsMzIuOGwyNi44LTI2LjgNCgljMTAsNi40LDIwLjgsMTAuOCwzMi40LDEzLjZ2MzhoNDYuOHYtMzcuNmMxMS42LTIuOCwyMi44LTcuMiwzMi40LTEzLjZsMjYuOCwyNi44bDMyLjgtMzIuOGwtMjYuNC0yNi44YzYuNC0xMCwxMC44LTIwLjgsMTMuNi0zMi40DQoJaDM3LjZWMjM1LjIxMnogTTIxMS42MjMsMjU4LjgxMmMtMjUuNiwwLTQ2LjgtMjAuOC00Ni44LTQ2LjhjMC0yNS42LDIwLjgtNDYuOCw0Ni44LTQ2LjhzNDYuOCwyMS4yLDQ2LjgsNDYuOA0KCVMyMzcuMjIzLDI1OC44MTIsMjExLjYyMywyNTguODEyeiIvPg0KPHBhdGggc3R5bGU9ImZpbGw6I0Q3RUNGODsiIGQ9Ik0yMTEuNjIzLDEzNS4yMTJjLTQyLjQsMC03Ni44LDM0LjQtNzYuOCw3Ni44czM0LjQsNzYuOCw3Ni44LDc2LjhzNzYuOC0zNC40LDc2LjgtNzYuOA0KCVMyNTQuMDIzLDEzNS4yMTIsMjExLjYyMywxMzUuMjEyeiBNMjExLjYyMywyNTguODEyYy0yNS42LDAtNDYuOC0yMC44LTQ2LjgtNDYuOGMwLTI1LjYsMjAuOC00Ni44LDQ2LjgtNDYuOHM0Ni44LDIxLjIsNDYuOCw0Ni44DQoJUzIzNy4yMjMsMjU4LjgxMiwyMTEuNjIzLDI1OC44MTJ6Ii8+DQo8cGF0aCBzdHlsZT0iZmlsbDojNDU0QjU0OyIgZD0iTTIzNC44MjMsMzY0LjAxMmgtNDYuNGMtNi44LDAtMTItNS4yLTEyLTEydi0yOC40Yy02LjQtMi0xMi40LTQuNC0xOC40LTcuNmwtMjAsMjANCgljLTQuOCw0LjgtMTIuNCw0LjgtMTYuOCwwbC0zMy42LTMzLjJjLTIuNC0yLjQtMy42LTUuMi0zLjYtOC40YzAtMy4yLDEuMi02LjQsMy42LTguNGwyMC0yMGMtMy4yLTYtNS42LTEyLTcuNi0xOC40aC0yOC40DQoJYy02LjgsMC0xMi01LjItMTItMTJ2LTQ2LjhjMC02LjgsNS4yLTEyLDEyLTEyaDI4LjRjMi02LjQsNC40LTEyLjQsNy42LTE4LjRsLTIwLTIwYy0yLjQtMi40LTMuNi01LjItMy42LTguNHMxLjItNi40LDMuNi04LjQNCglsMzIuOC0zMi44YzQuOC00LjgsMTIuNC00LjgsMTYuOCwwbDIwLDIwYzYtMy4yLDEyLTUuNiwxOC40LTcuNnYtMjkuMmMwLTYuOCw1LjItMTIsMTItMTJoNDYuOGM2LjgsMCwxMiw1LjIsMTIsMTJ2MjguNA0KCWM2LjQsMiwxMi40LDQuNCwxOC40LDcuNmwyMC0yMGM0LjgtNC44LDEyLjQtNC44LDE2LjgsMGwzMi44LDMyLjhjMi40LDIuNCwzLjYsNS4yLDMuNiw4LjRzLTEuMiw2LjQtMy42LDguNGwtMjAsMjANCgljMy4yLDYsNS42LDEyLDcuNiwxOC40aDI5LjZjNi44LDAsMTIsNS4yLDEyLDEydjQ2LjhjMCw2LjgtNS4yLDEyLTEyLDEyaC0yOC40Yy0yLDYuNC00LjQsMTIuNC03LjYsMTguNGwyMCwyMA0KCWMyLjQsMi40LDMuNiw1LjIsMy42LDguNGMwLDMuMi0xLjIsNi40LTMuNiw4LjRsLTMzLjIsMzRjLTQuOCw0LjgtMTIuNCw0LjgtMTYuOCwwbC0yMC0yMGMtNiwzLjItMTIsNS42LTE4LjQsNy42djI4LjQNCglDMjQ2LjgyMywzNTguODEyLDI0MS42MjMsMzY0LjAxMiwyMzQuODIzLDM2NC4wMTJ6IE0yMDAuNDIzLDM0MC4wMTJoMjIuOHYtMjUuNmMwLTUuNiw0LTEwLjQsOS4yLTExLjZjMTAuNC0yLjQsMjAtNi40LDI4LjgtMTINCgljNC44LTIuOCwxMC44LTIuNCwxNC44LDEuNmwxOCwxOGwxNi0xNmwtMTgtMThjLTQtNC00LjgtMTAtMS42LTE0LjhjNS42LTguOCw5LjYtMTguNCwxMi0yOC44YzEuMi01LjYsNi05LjIsMTEuNi05LjJoMjUuNnYtMjIuOA0KCWgtMjUuNmMtNS42LDAtMTAuNC00LTExLjYtOS4yYy0yLjQtMTAuNC02LjQtMjAtMTItMjguOGMtMi44LTQuOC0yLjQtMTAuOCwxLjYtMTQuOGwxOC0xOGwtMTYtMTZsLTE4LDE4Yy00LDQtMTAsNC44LTE0LjgsMS42DQoJYy04LjgtNS42LTE4LjQtOS42LTI4LjgtMTJjLTUuNi0xLjItOS4yLTYtOS4yLTExLjZ2LTI2aC0yMi44djI1LjZjMCw1LjYtNCwxMC40LTkuMiwxMS42Yy0xMC40LDIuNC0yMCw2LjQtMjguOCwxMg0KCWMtNC44LDIuOC0xMC44LDIuNC0xNC44LTEuNmwtMTgtMThsLTE2LDE2bDE4LDE4YzQsNCw0LjgsMTAsMS42LDE0LjhjLTUuNiw4LjgtOS42LDE4LjQtMTIsMjguOGMtMS4yLDUuNi02LDkuMi0xMS42LDkuMmgtMjZ2MjIuOA0KCWgyNS42YzUuNiwwLDEwLjQsNCwxMS42LDkuMmMyLjQsMTAuNCw2LjQsMjAsMTIsMjguOGMyLjgsNC44LDIuNCwxMC44LTEuNiwxNC44bC0xOCwxOGwxNiwxNmwxOC0xOGM0LTQsMTAtNC44LDE0LjgtMS42DQoJYzguOCw1LjYsMTguNCw5LjYsMjguOCwxMmM1LjYsMS4yLDkuMiw2LDkuMiwxMS42djI2SDIwMC40MjN6Ii8+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8L3N2Zz4NCg==" alt="setting">
                                    </span>
                                    <?php esc_html_e('Settings', 'webappick-pdf-invoice-for-woocommerce'); ?>
                                </a>
                            </li>




                            <li class="woo-invoice-sidebar-nav-item">
                                <a class="woo-invoice-sidebar-nav-link" href="#">
                                    <span class="_winvoice-menu-thumbnail">
                                        <img src="data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iaXNvLTg4NTktMSI/Pg0KPCEtLSBHZW5lcmF0b3I6IEFkb2JlIElsbHVzdHJhdG9yIDE5LjAuMCwgU1ZHIEV4cG9ydCBQbHVnLUluIC4gU1ZHIFZlcnNpb246IDYuMDAgQnVpbGQgMCkgIC0tPg0KPHN2ZyB2ZXJzaW9uPSIxLjEiIGlkPSJDYXBhXzEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHg9IjBweCIgeT0iMHB4Ig0KCSB2aWV3Qm94PSIwIDAgNDU2LjggNDU2LjgiIHN0eWxlPSJlbmFibGUtYmFja2dyb3VuZDpuZXcgMCAwIDQ1Ni44IDQ1Ni44OyIgeG1sOnNwYWNlPSJwcmVzZXJ2ZSI+DQo8cGF0aCBzdHlsZT0iZmlsbDojQ0FEQkVBOyIgZD0iTTM4Ni40LDQzMS44aC0zMTZjLTIyLDAtNDAtMTgtNDAtMjBWODVjMC00MiwxOC02MCw0MC02MGgzMTZjMjIsMCw0MCwxOCw0MCw2MHYzMjYuNA0KCUM0MjYuNCw0MTMuOCw0MDguNCw0MzEuOCwzODYuNCw0MzEuOHoiLz4NCjxwYXRoIHN0eWxlPSJmaWxsOiNGRkZGRkY7IiBkPSJNMzg2LjQsNDMxLjhoLTMxNmMtMjIsMC00MC0xOC00MC00MFYxMDVoMzk2djI4Ni40QzQyNi40LDQxMy44LDQwOC40LDQzMS44LDM4Ni40LDQzMS44eiIvPg0KPGc+DQoJPHBhdGggc3R5bGU9ImZpbGw6IzQ0NEI1NDsiIGQ9Ik0zODYuNCw0NDMuOGgtMzE2Yy0yOC44LDAtNTItMjMuMi01Mi01MlYyMDljMC02LjgsNS4yLTEyLDEyLTEyczEyLDUuMiwxMiwxMnYxODIuOA0KCQljMCwxNS42LDEyLjQsMjgsMjgsMjhoMzE2YzE1LjYsMCwyOC0xMi40LDI4LTI4VjIwOWMwLTYuOCw1LjItMTIsMTItMTJzMTIsNS4yLDEyLDEydjE4Mi44QzQzOC40LDQyMC4yLDQxNS4yLDQ0My44LDM4Ni40LDQ0My44eiINCgkJLz4NCgk8cGF0aCBzdHlsZT0iZmlsbDojNDQ0QjU0OyIgZD0iTTQyNi40LDExN2MtNi44LDAtMTItNS4yLTEyLTEyVjY1YzAtMTUuNi0xMi40LTI4LTI4LTI4aC0zMTZjLTE1LjYsMC0yOCwxMi40LTI4LDI4djQwDQoJCWMwLDYuOC01LjIsMTItMTIsMTJzLTEyLTUuMi0xMi0xMlY2NWMwLTI4LjgsMjMuMi01Miw1Mi01MmgzMTZjMjguOCwwLDUyLDIzLjIsNTIsNTJ2NDBDNDM4LjQsMTExLjgsNDMzLjIsMTE3LDQyNi40LDExN3oiLz4NCjwvZz4NCjxnPg0KCTxwYXRoIHN0eWxlPSJmaWxsOiNGRkZGRkY7IiBkPSJNNzAuNCw3N2MtMC44LDAtMS42LDAtMi40LTAuNGMtMC44LDAtMS42LTAuNC0yLjQtMC44Yy0wLjgtMC40LTEuMi0wLjgtMi0xLjINCgkJYy0wLjgtMC40LTEuMi0wLjgtMi0xLjZjLTAuOC0wLjgtMS4yLTEuMi0xLjYtMnMtMC44LTEuMi0xLjItMmMtMC40LTAuOC0wLjQtMS42LTAuOC0yLjRzLTAuNC0xLjYtMC40LTIuNGMwLTMuMiwxLjItNi40LDMuNi04LjQNCgkJYzAuNC0wLjQsMS4yLTEuMiwyLTEuNnMxLjItMC44LDItMS4yYzAuOC0wLjQsMS42LTAuNCwyLjQtMC44YzQtMC44LDgsMC40LDEwLjgsMy4yYzIuNCwyLjQsMy42LDUuMiwzLjYsOC40YzAsMC44LDAsMS42LTAuNCwyLjQNCgkJcy0wLjQsMS42LTAuOCwyLjRzLTAuOCwxLjYtMS4yLDJjLTAuNCwwLjgtMC44LDEuMi0xLjYsMkM3Ni44LDc1LjgsNzMuNiw3Nyw3MC40LDc3eiIvPg0KCTxwYXRoIHN0eWxlPSJmaWxsOiNGRkZGRkY7IiBkPSJNMTEwLjQsNzdjLTMuMiwwLTYuNC0xLjItOC40LTMuNmMtMi40LTIuNC0zLjYtNS4yLTMuNi04LjRjMC0zLjIsMS4yLTYuNCwzLjYtOC40DQoJCWMwLjQtMC40LDEuMi0xLjIsMi0xLjZzMS4yLTAuOCwyLTEuMmMwLjgtMC40LDEuNi0wLjQsMi40LTAuOGMxLjYtMC40LDMuMi0wLjQsNC44LDBjMC44LDAsMS42LDAuNCwyLjQsMC44YzAuOCwwLjQsMS42LDAuOCwyLDEuMg0KCQljMC44LDAuNCwxLjIsMC44LDIsMS42YzIuNCwyLjQsMy42LDUuMiwzLjYsOC40YzAsMy4yLTEuMiw2LjQtMy42LDguNEMxMTYuOCw3NS44LDExMy42LDc3LDExMC40LDc3eiIvPg0KCTxwYXRoIHN0eWxlPSJmaWxsOiNGRkZGRkY7IiBkPSJNMTUwLjQsNzdjLTAuOCwwLTEuNiwwLTIuNC0wLjRjLTAuOCwwLTEuNi0wLjQtMi40LTAuOGMtMC44LTAuNC0xLjItMC44LTItMS4yDQoJCWMtMC44LTAuNC0xLjItMC44LTItMS42Yy0wLjgtMC44LTEuMi0xLjItMS42LTJjLTAuNC0wLjgtMC44LTEuMi0xLjItMnMtMC40LTEuNi0wLjgtMi40Yy0wLjQtMC44LTAuNC0xLjYtMC40LTIuNA0KCQljMC0zLjIsMS4yLTYuNCwzLjYtOC40YzAuNC0wLjQsMS4yLTEuMiwyLTEuNmMwLjgtMC40LDEuMi0wLjgsMi0xLjJjMC44LTAuNCwxLjYtMC40LDIuNC0wLjhjNC0wLjgsOCwwLjQsMTAuOCwzLjINCgkJYzIuNCwyLjQsMy42LDUuMiwzLjYsOC40YzAsMC44LDAsMS42LTAuNCwyLjRzLTAuNCwxLjYtMC44LDIuNGMtMC40LDAuOC0wLjgsMS42LTEuMiwyYy0wLjQsMC44LTAuOCwxLjItMS42LDINCgkJQzE1Ni44LDc1LjgsMTUzLjYsNzcsMTUwLjQsNzd6Ii8+DQo8L2c+DQo8cGF0aCBzdHlsZT0iZmlsbDojNkFDRUY1OyIgZD0iTTE2LDEwNXY0NGMwLDExLjIsOC44LDIwLDIwLDIwaDM4NC44YzExLjIsMCwyMC04LjgsMjAtMjB2LTQ0SDE2eiIvPg0KPHBhdGggc3R5bGU9ImZpbGw6I0YxNzc4NjsiIGQ9Ik0xMiwxMDV2NDRjMCwxMS4yLDguOCwyMCwyMCwyMGgzOTIuOGMxMS4yLDAsMjAtOC44LDIwLTIwdi00NEgxMnoiLz4NCjxyZWN0IHg9IjE1Ni40IiB5PSIxMDguMiIgc3R5bGU9ImZpbGw6I0ZGRkZGRjsiIHdpZHRoPSIyNCIgaGVpZ2h0PSI2MC44Ii8+DQo8cGF0aCBzdHlsZT0iZmlsbDojNDQ0QjU0OyIgZD0iTTE1NiwxODFoMjQuOGM2LjgsMCwxMi01LjIsMTItMTJzLTUuMi0xMi0xMi0xMkgxNTZjLTYuOCwwLTEyLDUuMi0xMiwxMlMxNDkuMiwxODEsMTU2LDE4MXoiLz4NCjxyZWN0IHg9Ijk2LjQiIHk9IjEwOC4yIiBzdHlsZT0iZmlsbDojRkZGRkZGOyIgd2lkdGg9IjI0IiBoZWlnaHQ9IjYwLjgiLz4NCjxwYXRoIHN0eWxlPSJmaWxsOiM0NDRCNTQ7IiBkPSJNOTYsMTgxaDI0LjhjNi44LDAsMTItNS4yLDEyLTEycy01LjItMTItMTItMTJIOTZjLTYuOCwwLTEyLDUuMi0xMiwxMlM4OS4yLDE4MSw5NiwxODF6Ii8+DQo8Zz4NCgk8cmVjdCB4PSIyMTYuNCIgeT0iMTA4LjIiIHN0eWxlPSJmaWxsOiNGRkZGRkY7IiB3aWR0aD0iMjQiIGhlaWdodD0iNjAuOCIvPg0KCTxyZWN0IHg9IjI3Ni40IiB5PSIxMDguMiIgc3R5bGU9ImZpbGw6I0ZGRkZGRjsiIHdpZHRoPSIyNCIgaGVpZ2h0PSI2MC44Ii8+DQoJPHJlY3QgeD0iMzM2LjQiIHk9IjEwOC4yIiBzdHlsZT0iZmlsbDojRkZGRkZGOyIgd2lkdGg9IjI0IiBoZWlnaHQ9IjYwLjgiLz4NCgk8cmVjdCB4PSIzOTYuNCIgeT0iMTA4LjIiIHN0eWxlPSJmaWxsOiNGRkZGRkY7IiB3aWR0aD0iMjQiIGhlaWdodD0iNjAuOCIvPg0KCTxyZWN0IHg9IjM2LjQiIHk9IjEwOC4yIiBzdHlsZT0iZmlsbDojRkZGRkZGOyIgd2lkdGg9IjI0IiBoZWlnaHQ9IjYwLjgiLz4NCjwvZz4NCjxnPg0KCTxwYXRoIHN0eWxlPSJmaWxsOiM0NDRCNTQ7IiBkPSJNNDI0LjgsMTgxYy02LjgsMC0xMi01LjItMTItMTJzNS4yLTEyLDEyLTEyYzQuNCwwLDgtMy42LDgtOHYtMzJIMjR2MzJjMCw0LjQsMy42LDgsOCw4DQoJCWM2LjgsMCwxMiw1LjIsMTIsMTJzLTUuMiwxMi0xMiwxMmMtMTcuNiwwLTMyLTE0LjQtMzItMzJ2LTQ0YzAtNi44LDUuMi0xMiwxMi0xMmg0MzIuOGM2LjgsMCwxMiw1LjIsMTIsMTJ2NDQNCgkJQzQ1Ni44LDE2Ni42LDQ0Mi40LDE4MSw0MjQuOCwxODF6Ii8+DQoJPHBhdGggc3R5bGU9ImZpbGw6IzQ0NEI1NDsiIGQ9Ik0zOTYsMTgxaDI0LjhjNi44LDAsMTItNS4yLDEyLTEycy01LjItMTItMTItMTJIMzk2Yy02LjgsMC0xMiw1LjItMTIsMTJTMzg5LjIsMTgxLDM5NiwxODF6Ii8+DQoJPHBhdGggc3R5bGU9ImZpbGw6IzQ0NEI1NDsiIGQ9Ik0zMzYsMTgxaDI0LjhjNi44LDAsMTItNS4yLDEyLTEycy01LjItMTItMTItMTJIMzM2Yy02LjgsMC0xMiw1LjItMTIsMTJTMzI5LjIsMTgxLDMzNiwxODF6Ii8+DQoJPHBhdGggc3R5bGU9ImZpbGw6IzQ0NEI1NDsiIGQ9Ik0yNzYsMTgxaDI0LjhjNi44LDAsMTItNS4yLDEyLTEycy01LjItMTItMTItMTJIMjc2Yy02LjgsMC0xMiw1LjItMTIsMTJTMjY5LjIsMTgxLDI3NiwxODF6Ii8+DQoJPHBhdGggc3R5bGU9ImZpbGw6IzQ0NEI1NDsiIGQ9Ik0yMTYsMTgxaDI0LjhjNi44LDAsMTItNS4yLDEyLTEycy01LjItMTItMTItMTJIMjE2Yy02LjgsMC0xMiw1LjItMTIsMTJTMjA5LjIsMTgxLDIxNiwxODF6Ii8+DQoJPHBhdGggc3R5bGU9ImZpbGw6IzQ0NEI1NDsiIGQ9Ik0zNiwxODFoMjQuOGM2LjgsMCwxMi01LjIsMTItMTJzLTUuMi0xMi0xMi0xMkgzNmMtNi44LDAtMTIsNS4yLTEyLDEyUzI5LjIsMTgxLDM2LDE4MXoiLz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjwvc3ZnPg0K" alt="seller & buyer">
                                    </span>
                                    <?php esc_html_e('Seller & Buyer', 'webappick-pdf-invoice-for-woocommerce'); ?>
                                </a>
                            </li>
                            <li class="woo-invoice-sidebar-nav-item">
                                <a class="woo-invoice-sidebar-nav-link" href="#">
                                    <span class="_winvoice-menu-thumbnail">
                                        <img src="data:image/svg+xml;base64,PHN2ZyBpZD0iTGF5ZXJfMSIgZW5hYmxlLWJhY2tncm91bmQ9Im5ldyAwIDAgMTI4IDEyOCIgaGVpZ2h0PSI1MTIiIHZpZXdCb3g9IjAgMCAxMjggMTI4IiB3aWR0aD0iNTEyIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPjxwYXRoIGQ9Im0xMTggMTI3aC0xMDZjLTEuNyAwLTMtMS4zLTMtM3YtMThjMC0xLjcgMS4zLTMgMy0zczMgMS4zIDMgM3YxNWgxMDB2LTE1YzAtMS43IDEuMy0zIDMtM3MzIDEuMyAzIDN2MThjMCAxLjctMS4zIDMtMyAzeiIgZmlsbD0iIzQ0NGI1NCIvPjxwYXRoIGQ9Im0xMDQuNyA0OC43Yy0zLjUtMy41LTkuMi0zLjUtMTIuNyAwbC0yMCAyMHYtNjMuN2gtMTZ2NjMuN2wtMjAtMjBjLTMuNS0zLjUtOS4yLTMuNS0xMi43IDAtMy41IDMuNS0zLjUgOS4yIDAgMTIuN2wzNC4zIDM0LjNjMy41IDMuNSA5LjIgMy41IDEyLjcgMGwzNC4zLTM0LjNjMy42LTMuNSAzLjYtOS4yLjEtMTIuN3oiIGZpbGw9IiNkM2Q4ZGQiLz48ZyBmaWxsPSIjNDQ0YjU0Ij48cGF0aCBkPSJtNjQgMTAxLjRjLTMuMiAwLTYuMi0xLjItOC41LTMuNWwtMzQuMy0zNC4zYy0yLjMtMi4zLTMuNS01LjMtMy41LTguNXMxLjItNi4yIDMuNS04LjVjNC43LTQuNyAxMi4zLTQuNyAxNyAwbDE0LjggMTQuOHYtNTYuNGMwLTEuNyAxLjMtMyAzLTNzMyAxLjMgMyAzdjYzLjdjMCAxLjItLjcgMi4zLTEuOSAyLjgtMS4xLjUtMi40LjItMy4zLS43bC0yMC0yMGMtMi4zLTIuMy02LjEtMi4zLTguNSAwLTEuMSAxLjEtMS44IDIuNi0xLjggNC4ycy42IDMuMSAxLjggNC4ybDM0LjMgMzQuM2MyLjMgMi4zIDYuMSAyLjMgOC41IDBsMzQuMy0zNC4zYzIuMy0yLjMgMi4zLTYuMSAwLTguNS0yLjMtMi4zLTYuMS0yLjMtOC41IDBsLTIwIDIwYy0uOS45LTIuMSAxLjEtMy4zLjctLjktLjQtMS42LTEuNS0xLjYtMi43di00My43YzAtMS43IDEuMy0zIDMtM3MzIDEuMyAzIDN2MzYuNGwxNC45LTE0LjljNC43LTQuNyAxMi4zLTQuNyAxNyAwczQuNyAxMi4zIDAgMTdsLTM0LjQgMzQuNGMtMi4zIDIuMy01LjMgMy41LTguNSAzLjV6Ii8+PGNpcmNsZSBjeD0iNzIiIGN5PSI1IiByPSIzIi8+PC9nPjwvc3ZnPg==" alt="bulk download">
                                    </span>
                                    <?php esc_html_e('Bulk download', 'webappick-pdf-invoice-for-woocommerce'); ?>
                                </a>
                            </li>

                            <li class="woo-invoice-sidebar-nav-item">
                                <a class="woo-invoice-sidebar-nav-link" href="#">
                                    <span class="_winvoice-menu-thumbnail">
                                        <img src="data:image/svg+xml,%3Csvg%20width%3D%22151%22%20height%3D%22151%22%20viewBox%3D%220%200%20151%20151%22%20fill%3D%22none%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%0A%3Cpath%20d%3D%22M75.41%207.5122C93.5142%207.5122%20110.535%2014.561%20123.335%2027.365C136.136%2040.1655%20143.188%2057.1858%20143.188%2075.29C143.188%2093.3942%20136.139%20110.415%20123.335%20123.215C110.535%20136.016%2093.5142%20143.068%2075.41%20143.068C57.3058%20143.068%2040.2855%20136.019%2027.485%20123.215C14.6846%20110.415%207.6322%2093.3942%207.6322%2075.29C7.6322%2057.1858%2014.681%2040.1655%2027.485%2027.365C40.2855%2014.5646%2057.3058%207.5122%2075.41%207.5122ZM75.41%200.290039C33.9882%200.290039%200.410034%2033.8682%200.410034%2075.29C0.410034%20116.712%2033.9882%20150.29%2075.41%20150.29C116.832%20150.29%20150.41%20116.712%20150.41%2075.29C150.41%2033.8682%20116.832%200.290039%2075.41%200.290039Z%22%20fill%3D%22%23444B54%22%2F%3E%0A%3Cpath%20d%3D%22M75.4101%2035.27C78.4919%2035.27%2080.9956%2037.7774%2080.9956%2040.8556C80.9956%2043.9374%2078.4882%2046.4411%2075.4101%2046.4411C72.3283%2046.4411%2069.8245%2043.9338%2069.8245%2040.8556C69.8245%2037.7738%2072.3283%2035.27%2075.4101%2035.27ZM75.4101%2028.0443C68.336%2028.0443%2062.5988%2033.7779%2062.5988%2040.8556C62.5988%2047.9296%2068.3324%2053.6669%2075.4101%2053.6669C82.4877%2053.6669%2088.2214%2047.9332%2088.2214%2040.8556C88.2214%2033.7815%2082.4841%2028.0443%2075.4101%2028.0443Z%22%20fill%3D%22%2371C2FF%22%2F%3E%0A%3Cpath%20d%3D%22M75.4101%2072.6526C78.4919%2072.6526%2080.9956%2075.1599%2080.9956%2078.2381V109.721C80.9956%20112.803%2078.4882%20115.306%2075.4101%20115.306C72.3319%20115.306%2069.8245%20112.799%2069.8245%20109.721V78.2381C69.8245%2075.1563%2072.3283%2072.6526%2075.4101%2072.6526ZM75.4101%2065.4304C68.336%2065.4304%2062.5988%2071.1641%2062.5988%2078.2417V109.724C62.5988%20116.799%2068.3324%20122.536%2075.4101%20122.536C82.4841%20122.536%2088.2214%20116.802%2088.2214%20109.724V78.2417C88.2214%2071.1641%2082.4841%2065.4304%2075.4101%2065.4304Z%22%20fill%3D%22%2371C2FF%22%2F%3E%0A%3C%2Fsvg%3E%0A" alt="free vs premium">
                                    </span>
                                    <?php esc_html_e('Status', 'webappick-pdf-invoice-for-woocommerce'); ?>
                                </a>
                            </li>

                            <li class="woo-invoice-sidebar-nav-item">
                                <a class="woo-invoice-sidebar-nav-link" href="#">
                                    <span class="_winvoice-menu-thumbnail">
                                        <img src="data:image/svg+xml;base64,PHN2ZyBpZD0iTGF5ZXJfMSIgZW5hYmxlLWJhY2tncm91bmQ9Im5ldyAwIDAgMTI4IDEyOCIgaGVpZ2h0PSI1MTIiIHZpZXdCb3g9IjAgMCAxMjggMTI4IiB3aWR0aD0iNTEyIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPjxwYXRoIGQ9Im02NCA0Yy0zMy4xIDAtNjAgMjYuOS02MCA2MHMyNi45IDYwIDYwIDYwIDYwLTI2LjkgNjAtNjAtMjYuOS02MC02MC02MHptMCA5MC41Yy0xNi44IDAtMzAuNS0xMy43LTMwLjUtMzAuNXMxMy43LTMwLjUgMzAuNS0zMC41IDMwLjUgMTMuNyAzMC41IDMwLjUtMTMuNyAzMC41LTMwLjUgMzAuNXoiIGZpbGw9IiNmZjU1NzYiLz48ZyBmaWxsPSIjZmZmIj48cGF0aCBkPSJtODYuMyA4MS4xaDIwdjI4LjJoLTIweiIgdHJhbnNmb3JtPSJtYXRyaXgoLjcwNyAtLjcwNyAuNzA3IC43MDcgLTM5LjExNiA5NS45NSkiLz48cGF0aCBkPSJtMjIuOSAxNy42aDIwdjI4LjZoLTIweiIgdHJhbnNmb3JtPSJtYXRyaXgoLjcwNyAtLjcwNyAuNzA3IC43MDcgLTEyLjg4IDMyLjYxMikiLz48cGF0aCBkPSJtNzkuOSAyMi4yaDI5Ljd2MjBoLTI5Ljd6IiB0cmFuc2Zvcm09Im1hdHJpeCguNzA3IC0uNzA3IC43MDcgLjcwNyA0Ljk0MSA3Ni4zOTkpIi8+PHBhdGggZD0ibTE3LjQgODQuN2gyOS43djIwaC0yOS43eiIgdHJhbnNmb3JtPSJtYXRyaXgoLjcwNyAtLjcwNyAuNzA3IC43MDcgLTU3LjUxNiA1MC41MjkpIi8+PC9nPjxwYXRoIGQ9Im02NCAxMjdjLTM0LjcgMC02My0yOC4zLTYzLTYzczI4LjMtNjMgNjMtNjMgNjMgMjguMyA2MyA2My0yOC4zIDYzLTYzIDYzem0wLTEyMGMtMzEuNCAwLTU3IDI1LjYtNTcgNTdzMjUuNiA1NyA1NyA1NyA1Ny0yNS42IDU3LTU3LTI1LjYtNTctNTctNTd6IiBmaWxsPSIjNDQ0YjU0Ii8+PHBhdGggZD0ibTY0IDk3LjVjLTE4LjUgMC0zMy41LTE1LTMzLjUtMzMuNXMxNS0zMy41IDMzLjUtMzMuNSAzMy41IDE1IDMzLjUgMzMuNS0xNSAzMy41LTMzLjUgMzMuNXptMC02MWMtMTUuMiAwLTI3LjUgMTIuMy0yNy41IDI3LjVzMTIuMyAyNy41IDI3LjUgMjcuNSAyNy41LTEyLjMgMjcuNS0yNy41LTEyLjMtMjcuNS0yNy41LTI3LjV6IiBmaWxsPSIjNDQ0YjU0Ii8+PHBhdGggZD0ibTEzLjggNDcuMmMtLjQgMC0uNy0uMS0xLjEtLjItMS41LS42LTIuMy0yLjQtMS43LTMuOSA2LjYtMTYuOCAyMS0yOS41IDM4LjQtMzQuMiAxLjYtLjQgMy4yLjUgMy43IDIuMS40IDEuNi0uNSAzLjItMi4xIDMuNy0xNS42IDQuMS0yOC40IDE1LjYtMzQuMyAzMC42LS42IDEuMi0xLjcgMS45LTIuOSAxLjl6IiBmaWxsPSIjZmZmIi8+PHBhdGggZD0ibTEwIDY0LjljLTEuMyAwLTMtLjktMy0zLjMgMC0yLjMgMS44LTMuMiAzLTMuMiAxLjIgMCAzIC45IDMgMy4ydi4xYy0uMSAyLjQtMS44IDMuMi0zIDMuMnoiIGZpbGw9IiNmZmYiLz48L3N2Zz4=" alt="free vs premium">
                                    </span>
                                    <?php esc_html_e('Free vs Premium', 'webappick-pdf-invoice-for-woocommerce'); ?>
                                </a>
                            </li>
                        </ul>

                    </ul>
                </div>
            </div>
        </div><!-- end .woo-invoice-dashboard-sidebar -->
        <div class="woo-invoice-dashboard-content">
            <?php

            require_once CHALLAN_FREE_ROOT_FILE_PATH . 'admin/partials/tabs/settings-tab.php';
            require_once CHALLAN_FREE_ROOT_FILE_PATH . 'admin/partials/tabs/seller-buyer-tab.php';
            require_once CHALLAN_FREE_ROOT_FILE_PATH . 'admin/partials/tabs/bulk-download-tab.php';
            require_once CHALLAN_FREE_ROOT_FILE_PATH . 'admin/partials/tabs/system-status-tab.php';
            require_once CHALLAN_FREE_ROOT_FILE_PATH . 'admin/partials/tabs/free-vs-pro-tab.php';

            ?>


        </div>
    </div><!-- end .woo-invoice-dashboard-content -->
</div><!-- end .woo-invoice-dashboard-body -->

<div class="woo-invoice-modal woo-invoice-meta-modal" tabindex="-1" role="dialog">
    <div class="woo-invoice-modal-dialog" role="document">
        <div class="woo-invoice-modal-content">
            <div class="woo-invoice-modal-body">
                <p></p>
            </div>
            <div class="woo-invoice-modal-footer">
                <button type="button" class="woo-invoice-btn woo-invoice-btn-secondary woo-invoice-modal-close" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
