<?php
defined('ABSPATH') || exit;

$settings = \CUW\App\Helpers\Config::getSettings();
$shortcodes = \CUW\App\Controllers\Common\Shortcodes::get();
$compatibilities = \CUW\App\Helpers\Compatibility::getListToDisplay();
$email_enabled = \CUW\App\Helpers\Config::getEmailSettings('weekly_report', 'enabled', 'yes');
$email_recipient = \CUW\App\Helpers\Config::getEmailSettings('weekly_report', 'recipient', get_option('admin_email'));

$default_tab = apply_filters('cuw_settings_default_tab', 'campaigns');
?>

<div id="cuw-settings">
    <div class="d-flex title-container align-items-center justify-content-between">
        <h5><?php esc_html_e("Settings", 'checkout-upsell-and-order-bumps'); ?></h5>
        <button id="settings-save" class="btn btn-primary px-3">
            <i class="cuw-icon-tick-circle text-white mx-1"></i>
            <?php esc_html_e("Save Changes", 'checkout-upsell-and-order-bumps'); ?>
        </button>
    </div>
    <form id="settings-form" class="" action="" method="POST" enctype="multipart/form-data">
        <?php do_action('cuw_before_settings', $settings); ?>

        <div class="row mx-auto " style="">
            <div class="settings-tabs col-md-2 p-0">
                <ul class="nav nav-tabs tabs-v">
                    <?php do_action('cuw_before_settings_tabs', $settings); ?>

                    <li class="nav-item">
                        <a class="nav-link <?php if ($default_tab == 'campaigns') echo 'active'; ?>" data-toggle="tab"
                           href="#settings-campaigns">
                            <?php esc_html_e("Campaigns", 'checkout-upsell-and-order-bumps'); ?>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#settings-templates">
                            <?php esc_html_e("Templates", 'checkout-upsell-and-order-bumps'); ?>
                        </a>
                    </li>

                    <?php do_action('cuw_after_campaigns_setting_tab', $settings); ?>

                    <?php if (!empty($shortcodes)) { ?>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#settings-shortcodes">
                                <?php esc_html_e("Shortcodes", 'checkout-upsell-and-order-bumps'); ?>
                            </a>
                        </li>
                    <?php } ?>

                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab"
                           href="#settings-compatibility"><?php esc_html_e("Compatibility", 'checkout-upsell-and-order-bumps'); ?></a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab"
                           href="#settings-statics">
                            <?php esc_html_e("Weekly digest", 'checkout-upsell-and-order-bumps'); ?>
                        </a>
                    </li>

                    <?php do_action('cuw_after_settings_tabs', $settings); ?>
                </ul>
            </div>

            <div class="settings-content col-md-10">
                <div class="tab-content">
                    <?php do_action('cuw_before_settings_tab_contents', $settings); ?>

                    <div class="tab-pane fade pb-3 <?php if ($default_tab == 'campaigns') echo 'show active'; ?>"
                         id="settings-campaigns">
                        <h5 class="mb-n2 text-primary"><?php esc_html_e("General", 'checkout-upsell-and-order-bumps'); ?></h5>
                        <div class="mt-3 row align-items-center">
                            <div class="col-md-5">
                                <label class="font-weight-semibold text-dark form-label"><?php esc_html_e("Show product details", 'checkout-upsell-and-order-bumps'); ?></label>
                                <p class="form-text"><?php esc_html_e("Useful to show upsell product details in a popup or product page when click upsell product title or image.", 'checkout-upsell-and-order-bumps'); ?></p>
                            </div>
                            <div class="col-md-5">
                                <select class="form-control" name="show_product_details">
                                    <option value="in_popup" <?php if ($settings['show_product_details'] == 'in_popup') echo "selected"; ?>><?php esc_html_e("In a popup", 'checkout-upsell-and-order-bumps'); ?></option>
                                    <option value="in_new_tab" <?php if ($settings['show_product_details'] == 'in_new_tab') echo "selected"; ?>><?php esc_html_e("In a new tab", 'checkout-upsell-and-order-bumps'); ?></option>
                                    <option value="in_current_tab" <?php if ($settings['show_product_details'] == 'in_current_tab') echo "selected"; ?>><?php esc_html_e("In the current tab", 'checkout-upsell-and-order-bumps'); ?></option>
                                    <option value="disable" <?php if ($settings['show_product_details'] == 'disable') echo "selected"; ?>><?php esc_html_e("Disable", 'checkout-upsell-and-order-bumps'); ?></option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-3 row align-items-center">
                            <div class="col-md-5">
                                <label class="font-weight-semibold text-dark form-label"><?php esc_html_e("Discount calculate from", 'checkout-upsell-and-order-bumps'); ?></label>
                                <p class="form-text"><?php esc_html_e("You can choose either the regular price or the sale price to calculate a discount.", 'checkout-upsell-and-order-bumps'); ?></p>
                            </div>
                            <div class="col-md-5">
                                <select class="form-control" name="calculate_discount_from">
                                    <option value="regular_price" <?php if ($settings['calculate_discount_from'] == 'regular_price') echo "selected"; ?>>
                                        <?php esc_html_e("Regular price", 'checkout-upsell-and-order-bumps'); ?>
                                    </option>
                                    <option value="sale_price" <?php if ($settings['calculate_discount_from'] == 'sale_price') echo "selected"; ?>>
                                        <?php esc_html_e("Sale price", 'checkout-upsell-and-order-bumps'); ?>
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-3 row align-items-center">
                            <div class="col-md-5">
                                <label class="font-weight-semibold text-dark form-label"><?php esc_html_e("Exclude coupon discounts", 'checkout-upsell-and-order-bumps'); ?></label>
                                <p class="form-text"><?php esc_html_e("Useful to exclude discounted upsell products in the cart from applying WooCommerce coupon discounts.", 'checkout-upsell-and-order-bumps'); ?></p>
                            </div>
                            <div class="col-md-5">
                                <div class="custom-control custom-switch custom-switch-md mb-2">
                                    <input type="checkbox" name="exclude_coupon_discounts" value="1"
                                           class="custom-control-input"
                                           id="exclude-offer-from-discounts" <?php if ($settings['exclude_coupon_discounts']) echo "checked"; ?>>
                                    <label class="custom-control-label pl-2" for="exclude-offer-from-discounts"></label>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3 row align-items-center">
                            <div class="col-md-5">
                                <label class="font-weight-semibold text-dark form-label"><?php esc_html_e("Smart upsell products display", 'checkout-upsell-and-order-bumps'); ?></label>
                                <p class="form-text"><?php esc_html_e("Useful to hide upsell products that are already in the cart.", 'checkout-upsell-and-order-bumps'); ?></p>
                            </div>
                            <div class="col-md-5">
                                <div class="custom-control custom-switch custom-switch-md mb-2">
                                    <input type="checkbox" name="smart_products_display" value="1"
                                           class="custom-control-input"
                                           id="smart-upsells-display" <?php if ($settings['smart_products_display']) echo "checked"; ?>>
                                    <label class="custom-control-label pl-2" for="smart-upsells-display"></label>
                                </div>
                            </div>
                        </div>

                        <?php do_action('cuw_before_campaigns_settings', $settings); ?>

                        <h5 class="mt-3 mb-n2 text-primary"><?php esc_html_e("Cart & Checkout Upsells", 'checkout-upsell-and-order-bumps'); ?></h5>
                        <div class="mt-3 row align-items-center">
                            <div class="col-md-5">
                                <label class="font-weight-semibold text-dark form-label"><?php esc_html_e("Dynamic offer display", 'checkout-upsell-and-order-bumps'); ?></label>
                                <p class="form-text"><?php esc_html_e("Useful when you are using coupon conditions.", 'checkout-upsell-and-order-bumps'); ?></p>
                            </div>
                            <div class="col-md-5">
                                <div class="custom-control custom-switch custom-switch-md mb-2">
                                    <input type="checkbox" name="dynamic_offer_display" value="1"
                                           class="custom-control-input"
                                           id="dynamic-offer-display" <?php if ($settings['dynamic_offer_display']) echo "checked"; ?>>
                                    <label class="custom-control-label pl-2" for="dynamic-offer-display"></label>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3 row align-items-center">
                            <div class="col-md-5">
                                <label class="font-weight-semibold text-dark form-label"><?php esc_html_e("Offer display mode", 'checkout-upsell-and-order-bumps'); ?></label>
                                <p class="form-text"><?php esc_html_e("You can specify which campaign offers should be displayed in certain location.", 'checkout-upsell-and-order-bumps'); ?></p>
                            </div>
                            <div class="col-md-5">
                                <select class="form-control" name="offer_display_mode">
                                    <option value="first_matched" <?php if ($settings['offer_display_mode'] == 'first_matched') echo "selected"; ?>><?php esc_html_e("First matched campaign offers", 'checkout-upsell-and-order-bumps'); ?></option>
                                    <option value="all_matched" <?php if ($settings['offer_display_mode'] == 'all_matched') echo "selected"; ?>><?php esc_html_e("All matched campaign offers", 'checkout-upsell-and-order-bumps'); ?></option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-3 row align-items-center">
                            <div class="col-md-5">
                                <label class="font-weight-semibold text-dark form-label"><?php esc_html_e("Maximum number of offers a customer can pick at a time", 'checkout-upsell-and-order-bumps'); ?></label>
                                <p class="form-text"><?php esc_html_e("Useful when you want to limit a customer from picking only x number of offers. Eg, Let's say you have 2 offers showing at checkout. But you only want to allow the customer to pick only 1 among the 2.", 'checkout-upsell-and-order-bumps'); ?></p>
                            </div>
                            <div class="col-md-5">
                                <select class="form-control" name="offer_add_limit">
                                    <option value="" <?php if (empty($settings['offer_add_limit'])) echo "selected"; ?>><?php esc_html_e("Unlimited", 'checkout-upsell-and-order-bumps'); ?></option>
                                    <?php CUW()->view('Admin/Components/LimitOptions', ['selected_limit' => $settings['offer_add_limit'], 'to' => 10]); ?>
                                </select>
                            </div>
                        </div>
                        <div class="mt-3 row align-items-center">
                            <div class="col-md-5">
                                <label class="font-weight-semibold text-dark form-label"><?php esc_html_e("Offer added notice message", 'checkout-upsell-and-order-bumps'); ?></label>
                                <p class="form-text"><?php esc_html_e("You can edit your own offer added notice text.", 'checkout-upsell-and-order-bumps'); ?></p>
                            </div>
                            <div class="col-md-5">
                                <input type="text" class="form-control" name="offer_added_notice_message"
                                       value="<?php echo esc_attr($settings['offer_added_notice_message']) ?>"/>
                            </div>
                        </div>

                        <h5 class="mt-3 mb-n2 text-primary"><?php esc_html_e("Checkout Upsells", 'checkout-upsell-and-order-bumps'); ?></h5>
                        <div class="mt-3 row align-items-center">
                            <div class="col-md-5">
                                <label class="font-weight-semibold text-dark form-label"><?php esc_html_e("Always display offer", 'checkout-upsell-and-order-bumps'); ?></label>
                                <p class="form-text"><?php esc_html_e("Useful to display an offer even after the offer is added to the cart.", 'checkout-upsell-and-order-bumps'); ?></p>
                            </div>
                            <div class="col-md-5">
                                <div class="custom-control custom-switch custom-switch-md mb-2">
                                    <input type="checkbox" name="always_display_offer" value="1"
                                           class="custom-control-input"
                                           id="always-display-offer" <?php if ($settings['always_display_offer']) echo "checked"; ?>>
                                    <label class="custom-control-label pl-2" for="always-display-offer"></label>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3 row align-items-center cuw-offer-notice-position"
                             style="<?php if (!empty($settings['always_display_offer'])) echo "display: none;"; ?>">
                            <div class="col-md-5">
                                <label class="font-weight-semibold text-dark form-label"><?php esc_html_e("Offer added notice position", 'checkout-upsell-and-order-bumps'); ?></label>
                                <p class="form-text"><?php esc_html_e("You can choose the position where offer added notice should be displayed.", 'checkout-upsell-and-order-bumps'); ?></p>
                            </div>
                            <div class="col-md-5">
                                <select class="form-control" name="offer_notice_display_location">
                                    <option value="default" <?php if ($settings['offer_notice_display_location'] == 'default') echo "selected"; ?>><?php esc_html_e("Top of the page", 'checkout-upsell-and-order-bumps'); ?></option>
                                    <option value="offer_location" <?php if ($settings['offer_notice_display_location'] == 'offer_location') echo "selected"; ?>><?php esc_html_e("Within offer location", 'checkout-upsell-and-order-bumps'); ?></option>
                                </select>
                            </div>
                        </div>

                        <h5 class="mt-3 mb-n2 text-primary text"><?php esc_html_e("Frequently Bought Together", 'checkout-upsell-and-order-bumps'); ?></h5>
                        <div class="mt-3 row align-items-center">
                            <div class="col-md-5">
                                <label class="font-weight-semibold text-dark form-label"><?php esc_html_e("Maximum number of products to display", 'checkout-upsell-and-order-bumps'); ?></label>
                                <p class="form-text"><?php esc_html_e("Useful to limit frequently bought together products (excluding main product) that are display on a product page.", 'checkout-upsell-and-order-bumps'); ?></p>
                            </div>
                            <div class="col-md-5">
                                <select class="form-control" name="fbt_products_display_limit">
                                    <?php CUW()->view('Admin/Components/LimitOptions', ['selected_limit' => $settings['fbt_products_display_limit']]); ?>
                                </select>
                            </div>
                        </div>

                        <?php do_action('cuw_after_campaigns_settings', $settings); ?>
                    </div>

                    <div class="tab-pane fade pb-3" id="settings-templates">
                        <h5 class="mb-n2 text-primary"><?php esc_html_e("Product variants", 'checkout-upsell-and-order-bumps'); ?></h5>
                        <div class="mt-3 row align-items-center">
                            <div class="col-md-5">
                                <label class="font-weight-semibold text-dark form-label"><?php esc_html_e("Product variant select template", 'checkout-upsell-and-order-bumps'); ?></label>
                                <p class="form-text"><?php esc_html_e("Useful to change product variation select template that allows customers to choose upsell product variations in different ways.", 'checkout-upsell-and-order-bumps'); ?></p>
                            </div>
                            <div class="col-md-5">
                                <select class="form-control" name="variant_select_template">
                                    <option value="variant-select" <?php if ($settings['variant_select_template'] == 'variant-select') echo "selected"; ?>><?php esc_html_e("Direct select", 'checkout-upsell-and-order-bumps'); ?></option>
                                    <option value="attributes-select" <?php if ($settings['variant_select_template'] == 'attributes-select') echo "selected"; ?>><?php esc_html_e("Attributes select", 'checkout-upsell-and-order-bumps'); ?></option>
                                </select>
                            </div>
                        </div>

                        <h5 class="mb-n2 text-primary mt-3"><?php esc_html_e("Product quantity", 'checkout-upsell-and-order-bumps'); ?></h5>
                        <div class="mt-3 row align-items-center">
                            <div class="col-md-5">
                                <label class="font-weight-semibold text-dark form-label"><?php esc_html_e("Product quantity option template", 'checkout-upsell-and-order-bumps'); ?></label>
                                <p class="form-text"><?php esc_html_e("Useful to change product quantity option template that allows customers to customize templates in different ways.", 'checkout-upsell-and-order-bumps'); ?></p>
                            </div>
                            <div class="col-md-5">
                                <select class="form-control" name="quantity_template">
                                    <option value="quantity-input" <?php if ($settings['quantity_template'] == 'quantity-input') echo "selected"; ?>><?php esc_html_e("Flat quantity selector", 'checkout-upsell-and-order-bumps'); ?></option>
                                    <option value="quantity-input-2" <?php if ($settings['quantity_template'] == 'quantity-input-2') echo "selected"; ?>><?php esc_html_e("Outlined quantity selector", 'checkout-upsell-and-order-bumps'); ?></option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($shortcodes)) {
                        $grouped_shortcodes = [];
                        foreach ($shortcodes as $key => $shortcode) {
                            if (isset($shortcode['group']) && $group = $shortcode['group']) {
                                $grouped_shortcodes[$group][$key] = $shortcode;
                            }
                        }
                        ?>
                        <div class="tab-pane fade" id="settings-shortcodes">
                            <?php foreach ($grouped_shortcodes as $group => $shortcodes) { ?>
                                <h5 class="text-primary mb-n2 text"><?php echo esc_html($group); ?></h5>
                                <?php foreach ($shortcodes as $key => $shortcode) { ?>
                                    <div class="mt-3 row align-items-center <?php if (!next($shortcodes)) echo 'mb-3'; ?>">
                                        <div class="col-md-5">
                                            <label class="font-weight-semibold text-dark form-label"><?php echo esc_html($shortcode['title']); ?></label>
                                            <p class="form-text"><?php echo wp_kses_post($shortcode['description']); ?></p>
                                        </div>
                                        <div class="col-md-5">
                                            <pre class="cuw-copy px-3 py-2 bg-light rounded"><?php echo esc_html($shortcode['code']); ?></pre>
                                        </div>
                                    </div>
                                <?php } ?>
                            <?php } ?>
                        </div>
                    <?php } ?>

                    <div class="tab-pane fade" id="settings-compatibility">
                        <?php if (empty($compatibilities)) { ?>
                            <div class="mt-4 row align-items-center">
                                <div class="col-11">
                                    <?php esc_html_e('This section will list plugins and theme that require compatibility with our plugin to resolve conflicts.', 'checkout-upsell-and-order-bumps'); ?>
                                </div>
                            </div>
                        <?php } else {
                            $plugins = array_filter($compatibilities, function ($package) {
                                return $package['type'] == 'plugin';
                            });
                            $themes = array_filter($compatibilities, function ($package) {
                                return $package['type'] == 'theme';
                            });

                            if (!empty($plugins)) { ?>
                                <h5 class="text-primary text"><?php esc_html_e('Plugins', 'checkout-upsell-and-order-bumps'); ?></h5>
                                <?php CUW()->view('Admin/Components/Compatibility', ['packages' => $plugins]); ?>
                            <?php }
                            if (!empty($themes)) { ?>
                                <h5 class="mt-2 text-primary text"><?php esc_html_e('Theme', 'checkout-upsell-and-order-bumps'); ?></h5>
                                <?php CUW()->view('Admin/Components/Compatibility', ['packages' => $themes]);
                            }
                        } ?>
                    </div>

                    <div class="tab-pane fade" id="settings-statics">
                        <h5 class="mb-n2 text-primary"><?php esc_html_e("Weekly report", 'checkout-upsell-and-order-bumps'); ?></h5>
                        <div class="mt-3 row align-items-center">
                            <div class="col-md-5">
                                <label class="font-weight-semibold text-dark form-label"><?php esc_html_e('Enable/Disable', 'checkout-upsell-and-order-bumps'); ?></label>
                                <p class="form-text"><?php esc_html_e("Enable this email notification", 'checkout-upsell-and-order-bumps'); ?></p>
                            </div>
                            <div class="col-md-5">
                                <div class="custom-control custom-switch custom-switch-md mb-2">
                                    <input type="checkbox" name="send_weekly_report" value="1"
                                           class="custom-control-input"
                                           id="send-weekly-report" <?php if ($email_enabled == 'yes') echo "checked"; ?>>
                                    <label class="custom-control-label pl-2" for="send-weekly-report"></label>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3 row align-items-center cuw-email-block"
                             style="<?php if ($email_enabled != 'yes') echo esc_attr('display: none;'); ?>">
                            <div class="col-md-5">
                                <label class="font-weight-semibold text-dark form-label"><?php esc_html_e("Recipient(s)", 'checkout-upsell-and-order-bumps'); ?></label>
                                <p class="form-text">
                                    <?php
                                        echo esc_html(sprintf(
                                        // translators: %s email.
                                                __('Enter recipients (comma separated) for this email. Defaults to %s.', 'checkout-upsell-and-order-bumps'),
                                                esc_attr(get_option('admin_email'))
                                        ));
                                    ?>
                                </p>
                            </div>
                            <div class="col-md-5">
                                <input type="text" class="form-control" name="report_recipient"
                                       value="<?php echo esc_attr($email_recipient) ?>"/>
                            </div>
                        </div>
                    </div>

                    <?php do_action('cuw_after_settings_tab_contents', $settings); ?>
                </div>
            </div>
        </div>

        <?php do_action('cuw_after_settings', $settings); ?>
    </form>
</div>