<?php
defined('ABSPATH') || exit();

$offer_data = \CUW\App\Pro\Modules\PostPurchase\Templates::getDefaultTemplateData();

$campaign = $campaign ?? [];
$has_uuid = isset($uuid);
$uuid = $uuid ?? '{uuid}';
$offer = $offers[$uuid] ?? [];
$offers_map = $offers_map ?? [];

if (!empty($offer)) {
    $offer_data = array_merge($offer_data, $offer['data'] ?? []);
}
$offer_id = $offer['id'] ?? 0;
$parent_uuid = $offers_map[$uuid]['parent_uuid'] ?? (!$has_uuid ? '{parent_uuid}' : '');
$accept_uuid = $offers_map[$uuid]['accept_uuid'] ?? '';
$decline_uuid = $offers_map[$uuid]['decline_uuid'] ?? '';
$depth = $offers_map[$uuid]['depth'] ?? (!$has_uuid ? '{depth}' : '');
$position = $offers_map[$uuid]['position'] ?? (!$has_uuid ? '{position}' : '');
$order_details_notice = $offer_data['order_details']['notice_type'] ?? '';

$product_id = $offer['product']['id'] ?? '';
$product_qty = $offer['product']['qty'] ?? '';

$discount_type = $offer['discount']['type'] ?? 'no_discount';
$discount_value = $offer['discount']['value'] ?? 0;

$usage_limit = $offer['usage_limit'] ?? '';
$usage_limit_per_user = $offer['usage_limit_per_user'] ?? '';

$template_id = $campaign['data']['page']['template'] ?? CUW()->input->get('template', '', 'query');
$template_edit_url = !empty($template_id) ? \CUW\App\Pro\Modules\PostPurchase\Templates::getEditUrl($template_id) : '#';
?>
<input type="hidden" name="offers[<?php echo esc_attr($uuid); ?>][id]"
       value="<?php echo esc_attr($offer_id); ?>">
<input type="hidden" name="offers[<?php echo esc_attr($uuid); ?>][uuid]"
       value="<?php echo esc_attr($uuid); ?>">
<input type="hidden" class="offer-uuid" name="data[offers_map][<?php echo esc_attr($uuid); ?>][uuid]"
       value="<?php echo esc_attr($uuid); ?>">
<input type="hidden" class="parent-offer-uuid"
       name="data[offers_map][<?php echo esc_attr($uuid); ?>][parent_uuid]"
       value='<?php echo esc_attr($parent_uuid); ?>'>
<input type="hidden" class="accept-offer-uuid"
       name="data[offers_map][<?php echo esc_attr($uuid); ?>][accept_uuid]"
       value='<?php echo esc_attr($accept_uuid); ?>'>
<input type="hidden" class="decline-offer-uuid"
       name="data[offers_map][<?php echo esc_attr($uuid); ?>][decline_uuid]"
       value='<?php echo esc_attr($decline_uuid); ?>'>
<input type="hidden" name="data[offers_map][<?php echo esc_attr($uuid); ?>][depth]"
       value='<?php echo esc_attr($depth); ?>'>
<input type="hidden" name="data[offers_map][<?php echo esc_attr($uuid); ?>][position]"
       value='<?php echo esc_attr($position); ?>'>
<div id="cuw-offer-data">
    <div id="cuw-option-tab">
        <div class="px-4 py-3 border-bottom border-gray-light">
            <div id="offer-product" class="post-purchase-offer d-flex flex-column form-group" style="gap: 8px;">
                <label class="form-label"><?php esc_html_e("Choose an Offer Product", 'checkout-upsell-woocommerce'); ?></label>
                <select class="select2-list reload-preview form-control"
                        name="offers[<?php echo esc_attr($uuid); ?>][product_id]"
                        data-list="products"
                        data-placeholder="<?php esc_attr_e("Choose product", 'checkout-upsell-woocommerce'); ?>">
                    <?php if (!empty($product_id)) { ?>
                        <option value="<?php echo esc_attr($product_id); ?>"
                                selected><?php echo esc_html(CUW()->wc->getProductTitle($product_id, true)); ?></option>
                    <?php } ?>
                </select>
                <input type="hidden" class="cuw-product-name"
                       name="offers[<?php echo esc_attr($uuid); ?>][product_name]"
                       value="<?php if (!empty($offer_data['product_title'])) echo esc_attr($offer_data['product_title']); ?>">
            </div>
            <div class="post-purchase-offer-quantity">
                <label for="post-purchase-offer-qty" class="form-label">
                    <?php esc_html_e("Quantity", 'checkout-upsell-woocommerce'); ?>
                    <?php esc_html_e("(optional)", 'checkout-upsell-woocommerce'); ?>
                </label>
                <input type="number" class="reload-preview form-control" id="post-purchase-offer-qty"
                       name="offers[<?php echo esc_attr($uuid); ?>][product_qty]" min="0"
                       value="<?php echo esc_attr($product_qty); ?>"
                       placeholder="<?php esc_attr_e("Custom", 'checkout-upsell-woocommerce'); ?>">
            </div>
        </div>
        <div>
            <div class="d-flex mx-4">
                <div id="cuw-content-section-tab"
                     class="d-flex cuw-offer-customize-tab justify-content-center w-100 cuw-active-tab p-3 cursor-pointer"
                     data-target="#cuw-content-section">
                    <?php esc_html_e("Content", 'checkout-upsell-woocommerce'); ?>
                </div>
                <div id="cuw-design-section-tab"
                     class="d-flex cuw-offer-customize-tab justify-content-center w-100 p-3 cursor-pointer"
                     data-target="#cuw-design-section">
                    <?php esc_html_e("Design", 'checkout-upsell-woocommerce'); ?>
                </div>
            </div>
            <div id="cuw-content-section"
                 class="cuw-custom-section p-4 border-top border-gray-light d-flex flex-column mb-3 mx-1"
                 style="gap: 12px; height: 50vh; overflow-y: scroll;">
                <div class="border border-gray-extra-light rounded pt-3">
                    <div class="cuw-custom-option d-flex justify-content-between cursor-pointer px-3 pb-3 border-gray-light"
                         data-target="#cuw-content-offer-notice">
                        <div class="d-flex" style="gap: 10px;">
                            <i class="cuw-icon-message-text mx-1"></i>
                            <h4 class="cuw-option-title">
                                <?php esc_html_e("Order details", 'checkout-upsell-woocommerce'); ?>
                                <span class="offer-index"></span>
                            </h4>
                        </div>
                        <div class="accordion-icon">
                            <i class="cuw-icon-accordion-open"></i>
                        </div>
                    </div>

                    <div id="cuw-content-offer-notice" class="cuw-text-section px-3" style="display: none;">
                        <div class="d-flex flex-column pb-4 pt-3" style="gap: 8px;">
                            <div class="custom-control custom-checkbox">
                                <input id="offer-notice-enabled" type="checkbox" class="custom-control-input"
                                       data-section=".cuw-ppu-order-details" style="z-index: 1;"
                                       name="offers[<?php echo esc_attr($uuid); ?>][data][order_details][enabled]"
                                       value="<?php if (!empty($offer_data['order_details']['enabled'])) echo '1' ?>" <?php if (!empty($offer_data['order_details']['enabled'])) echo 'checked'; ?>/>
                                <label class="custom-control-label" for="offer-notice-enabled">
                                </label>
                                <label class="font-weight-medium"><?php esc_html_e("Enable order details", 'checkout-upsell-woocommerce'); ?></label>
                            </div>
                            <div id="order-details-section" class="flex-column"
                                 style="<?php if (empty($offer_data['order_details']['enabled'])) echo 'display: none;'; ?>">
                                <label for="offer-title"
                                       class="form-label"><?php esc_html_e("Notice type", 'checkout-upsell-woocommerce'); ?></label>
                                <select class="reload-preview form-control" id="offer-details-notice"
                                        name="offers[<?php echo esc_attr($uuid); ?>][data][order_details][notice_type]"
                                        data-section=".cuw-ppu-order-details">
                                    <option value="custom" <?php selected('custom', $order_details_notice); ?>>
                                        <?php esc_html_e("Custom notice", 'checkout-upsell-woocommerce'); ?></option>
                                    <option value="wc_notice" <?php selected('wc_notice', $order_details_notice); ?>>
                                        <?php esc_html_e("WooCommerce notice", 'checkout-upsell-woocommerce'); ?></option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="border border-gray-extra-light rounded pt-3 w-100">
                    <div class="cuw-custom-option d-flex justify-content-between cursor-pointer px-3 pb-3 border-gray-light"
                         data-target="#cuw-content-title-description">
                        <div class="d-flex" style="gap: 10px;">
                            <i class="cuw-icon-smallcaps mx-1"></i>
                            <h4 class="cuw-option-title">
                                <?php esc_html_e("Title & Description", 'checkout-upsell-woocommerce'); ?>
                                <span class="offer-index"></span>
                            </h4>
                        </div>
                        <div class="accordion-icon">
                            <i class="cuw-icon-accordion-open"></i>
                        </div>
                    </div>

                    <div id="cuw-content-title-description" class="cuw-text-section px-3" style="display: none;">
                        <div class="d-flex flex-column pb-4 pt-3" style="gap: 20px;">
                            <div class="d-flex flex-column">
                                <label for="offer-title"
                                       class="form-label"><?php esc_html_e("Offer title", 'checkout-upsell-woocommerce'); ?></label>
                                <input type="text" id="offer-title" class="form-control"
                                       name="offers[<?php echo esc_attr($uuid); ?>][data][title]"
                                       data-target=".cuw-ppu-offer-title" data-section="offer_title"
                                       value="<?php echo esc_attr($offer_data['title']); ?>">
                            </div>
                            <div class="d-flex flex-column form-group">
                                <label for="offer-description"
                                       class="form-label"><?php esc_html_e("Offer description", 'checkout-upsell-woocommerce'); ?></label>
                                <textarea id="offer-description" rows="3" class="form-control"
                                          name="offers[<?php echo esc_attr($uuid); ?>][data][description]"
                                          data-target=".cuw-ppu-offer-description"><?php echo esc_html($offer_data['description']); ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="border border-gray-extra-light rounded pt-3">
                    <div class="cuw-custom-option d-flex justify-content-between px-3 pb-3 cursor-pointer border-gray-light"
                         data-target="#cuw-content-image">
                        <div class="d-flex" style="gap: 10px;">
                            <i class="cuw-icon-product mx-1"></i>
                            <h4 class="cuw-option-title">
                                <?php esc_html_e("Product Image", 'checkout-upsell-woocommerce'); ?>
                                <span class="offer-index"></span>
                            </h4>
                        </div>
                        <div class="accordion-icon">
                            <i class="cuw-icon-accordion-open"></i>
                        </div>
                    </div>
                    <div id="cuw-content-image" class="cuw-text-section px-3" style="display: none;">
                        <div class="pb-4 pt-3">
                            <div id="offer-image-type" class="d-flex" style="gap: 12px;">
                                <select class="reload-preview form-control" id="offer-image-id"
                                        data-section="offer_title"
                                        name="offers[<?php echo esc_attr($uuid); ?>][data][image_id]">
                                    <option id="custom-image"
                                            value="<?php echo (!empty($offer_data['image_id'])) ? esc_attr($offer_data['image_id']) : '' ?>" <?php selected('', $offer_data['image_id']); ?>>
                                        <?php esc_html_e("Custom image", 'checkout-upsell-woocommerce'); ?></option>
                                    <option value="0" <?php selected('0', $offer_data['image_id']); ?>>
                                        <?php esc_html_e("Product image", 'checkout-upsell-woocommerce'); ?></option>
                                </select>
                            </div>
                            <div class="d-flex">
                                <button style="gap:8px; align-items: center; justify-content: center; <?php if (empty($offer_data['image_id'])) echo 'display: none;'; ?>"
                                        type="button"
                                        class="btn btn-outline-primary w-100 btn-sm px-3 mt-3 "
                                        id="select-image">
                                    <i class="cuw-icon-image inherit-color mx-1"></i><?php esc_html_e("Select / Change image", 'checkout-upsell-woocommerce'); ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="border border-gray-extra-light rounded pt-3">
                    <div class="cuw-custom-option d-flex justify-content-between pb-3 px-3 cursor-pointer border-gray-light"
                         data-target="#cuw-content-discount">
                        <div class="d-flex" style="gap: 10px;">
                            <i class="cuw-icon-discount mx-1"></i>
                            <h4 class="cuw-option-title">
                                <?php esc_html_e("Discount", 'checkout-upsell-woocommerce'); ?>
                                <span class="offer-index"></span>
                            </h4>
                        </div>
                        <div class="accordion-icon">
                            <i class="cuw-icon-accordion-open"></i>
                        </div>
                    </div>
                    <div id="cuw-content-discount" class="cuw-text-section px-3" style="display: none;">
                        <div class="pb-4 pt-3">
                            <div class="offer-discount-type">
                                <label for="offer-discount-type"
                                       class="form-label"><?php esc_html_e("Discount type", 'checkout-upsell-woocommerce'); ?></label>
                                <select class="reload-preview form-control" id="offer-discount-type"
                                        data-section="offer_title"
                                        name="offers[<?php echo esc_attr($uuid); ?>][discount_type]">
                                    <option value="percentage" <?php selected('percentage', $discount_type); ?>>
                                        <?php esc_html_e("Percentage discount", 'checkout-upsell-woocommerce'); ?></option>
                                    <option value="fixed_price" <?php selected('fixed_price', $discount_type); ?>>
                                        <?php esc_html_e("Fixed discount", 'checkout-upsell-woocommerce'); ?></option>
                                    <option value="free" <?php selected('free', $discount_type); ?>>
                                        <?php esc_html_e("Free", 'checkout-upsell-woocommerce'); ?></option>
                                    <option value="no_discount" <?php selected('no_discount', $discount_type); ?>>
                                        <?php esc_html_e("No discount", 'checkout-upsell-woocommerce'); ?></option>
                                </select>
                            </div>
                            <div class="offer-discount-value pt-3"
                                 style="<?php if (!in_array($discount_type, ['percentage', 'fixed_price'])) echo 'display: none;' ?>">
                                <label for="offer-discount-value"
                                       class="form-label"><?php esc_html_e("Discount value", 'checkout-upsell-woocommerce'); ?></label>
                                <input class="reload-preview form-control" type="number" id="offer-discount-value"
                                       data-section="offer_title"
                                       name="offers[<?php echo esc_attr($uuid); ?>][discount_value]" min="0"
                                       value="<?php echo esc_attr($discount_value); ?>"
                                       placeholder="<?php esc_attr_e("Value", 'checkout-upsell-woocommerce'); ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="border border-gray-extra-light rounded pt-3">
                    <div class="cuw-custom-option d-flex justify-content-between px-3 pb-3 cursor-pointer border-gray-light"
                         data-target="#cuw-content-button">
                        <div class="d-flex" style="gap: 10px;">
                            <i class="cuw-icon-row-vertical mx-1"></i>
                            <h4 class="cuw-option-title">
                                <?php esc_html_e("Buttons", 'checkout-upsell-woocommerce'); ?>
                                <span class="offer-index"></span>
                            </h4>
                        </div>
                        <div class="accordion-icon">
                            <i class="cuw-icon-accordion-open"></i>
                        </div>
                    </div>
                    <div id="cuw-content-button" class="cuw-text-section px-3" style="display: none">
                        <div class="pb-4 pt-3 d-flex flex-column" style="gap: 12px;">
                            <div>
                                <label for="button-text"
                                       class="form-label"><?php esc_html_e("Accept button text", 'checkout-upsell-woocommerce'); ?></label>
                                <input type="text" name="offers[<?php echo esc_attr($uuid); ?>][data][accept_text]"
                                       class="form-control"
                                       data-target=".cuw-ppu-accept-text"
                                       value="<?php echo esc_attr($offer_data['accept_text']) ?>">
                            </div>
                            <div>
                                <label for="button-text"
                                       class="form-label"><?php esc_html_e("Decline button text", 'checkout-upsell-woocommerce'); ?></label>
                                <input type="text" name="offers[<?php echo esc_attr($uuid); ?>][data][decline_text]"
                                       class="form-control"
                                       data-target=".cuw-ppu-decline-text"
                                       value="<?php echo esc_attr($offer_data['decline_text']) ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="border border-gray-extra-light rounded pt-3">
                    <div class="cuw-custom-option d-flex justify-content-between px-3 pb-3 cursor-pointer border-gray-light"
                         data-target="#cuw-content-timer">
                        <div class="d-flex" style="gap: 10px;">
                            <i class="cuw-icon-clock mx-1"></i>
                            <h4 class="cuw-option-title">
                                <?php esc_html_e("Timer", 'checkout-upsell-woocommerce'); ?>
                                <span class="offer-index"></span>
                            </h4>
                        </div>
                        <div class="accordion-icon">
                            <i class="cuw-icon-accordion-open"></i>
                        </div>
                    </div>
                    <div id="cuw-content-timer" class="cuw-text-section px-3" style="display: none">
                        <div class="pb-4 pt-3">
                            <?php
                            $timer = $offer_data['timer'] ?? [];
                            $notice_type = $timer['notice_type'] ?? '';
                            $minutes = $timer['minutes'] ?? '5';
                            $seconds = $timer['seconds'] ?? '0';
                            $message = !empty($timer['message']) ? $timer['message'] : "Offer expires in: <strong>{minutes}:{seconds}</strong>"
                            ?>
                            <div class="custom-control custom-checkbox">
                                <input id="offer-timer-enabled" type="checkbox" class="custom-control-input"
                                       style="z-index: 1;"
                                       name="offers[<?php echo esc_attr($uuid); ?>][data][timer][enabled]"
                                       value="<?php if (!empty($timer['enabled'])) echo '1' ?>" <?php if (!empty($timer['enabled'])) echo 'checked'; ?>/>
                                <label class="custom-control-label" for="offer-timer-enabled">
                                </label>
                                <label class="font-weight-medium"><?php esc_html_e("Enable timer notice", 'checkout-upsell-woocommerce'); ?></label>
                            </div>
                            <span class="d-block secondary small">
                                <?php esc_html_e('If the timer has expired, the offer will be skipped and the order processed further.', 'checkout-upsell-woocommerce'); ?>
                            </span>
                            <div id="timer-details-section" class="pt-2"
                                 style="<?php if (empty($timer['enabled'])) echo 'display: none;'; ?>">
                                <div class="d-flex flex-column form-group">
                                    <label for="timer-display-type"
                                           class="form-label"><?php esc_html_e("Notice type", 'checkout-upsell-woocommerce'); ?></label>
                                    <select class="reload-preview form-control" id="timer-notice-type"
                                            name="offers[<?php echo esc_attr($uuid); ?>][data][timer][notice_type]"
                                            data-section=".cuw-ppu-offer-timer">
                                        <option value="custom" <?php selected('custom', $notice_type); ?>>
                                            <?php esc_html_e("Custom notice", 'checkout-upsell-woocommerce'); ?></option>
                                        <option value="wc_notice" <?php selected('wc_notice', $notice_type); ?>>
                                            <?php esc_html_e("WooCommerce notice", 'checkout-upsell-woocommerce'); ?></option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="timer-expires"
                                           class="form-label"><?php esc_html_e("Duration", 'checkout-upsell-woocommerce'); ?>
                                        (MM:SS)</label>
                                    <div style="width: 128px;">
                                        <div class="input-group">
                                            <select class="form-control" id="timer-minutes"
                                                    name="offers[<?php echo esc_attr($uuid); ?>][data][timer][minutes]"
                                                    data-section="timer">
                                                <?php for ($i = 0; $i < 60; $i++) {
                                                    echo '<option value="' . esc_attr($i) . '" ' . ($i == $minutes ? 'selected' : '') . '>' . esc_html($i < 10 ? '0' . $i : $i) . '</option>';
                                                } ?>
                                            </select>
                                            <select class="form-control" id="timer-seconds"
                                                    name="offers[<?php echo esc_attr($uuid); ?>][data][timer][seconds]"
                                                    data-section="timer">
                                                <?php for ($i = 0; $i < 60; $i++) {
                                                    echo '<option value="' . esc_attr($i) . '" ' . ($i == $seconds ? 'selected' : '') . '>' . esc_html($i < 10 ? '0' . $i : $i) . '</option>';
                                                } ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group mb-0">
                                    <label for="timer-message"
                                           class="form-label"><?php esc_html_e("Message", 'checkout-upsell-woocommerce'); ?></label>
                                    <input type="text" class="form-control" id="timer-message"
                                           name="offers[<?php echo esc_attr($uuid); ?>][data][timer][message]"
                                           data-section="timer" data-target=".cuw-ppu-offer-timer"
                                           value="<?php echo esc_attr($message); ?>"
                                           placeholder="Offer expires in: <strong>{minutes}:{seconds}</strong>">
                                    <span class="d-block small mt-1" style="opacity: 0.8;">
                                        <?php esc_html_e('Available shortcodes', 'checkout-upsell-woocommerce'); ?>: {minutes}, {seconds}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="border border-gray-extra-light rounded pt-3">
                    <div class="cuw-custom-option d-flex justify-content-between px-3 pb-3 cursor-pointer border-gray-light"
                         data-target="#cuw-content-order-totals">
                        <div class="d-flex" style="gap: 10px;">
                            <i class="cuw-icon-note-text mx-1"></i>
                            <h4 class="cuw-option-title">
                                <?php esc_html_e("Order totals", 'checkout-upsell-woocommerce'); ?>
                                <span class="offer-index"></span>
                            </h4>
                        </div>
                        <div class="accordion-icon">
                            <i class="cuw-icon-accordion-open"></i>
                        </div>
                    </div>
                    <div id="cuw-content-order-totals" class="cuw-text-section px-3" style="display: none">
                        <div class="pb-4 pt-3">
                            <div class="custom-control custom-checkbox">
                                <input id="order-totals-enabled" type="checkbox" class="custom-control-input"
                                       data-section=".cuw-ppu-order-totals" style="z-index: 1;"
                                       name="offers[<?php echo esc_attr($uuid); ?>][data][order_totals][enabled]"
                                       value="<?php if (!empty($offer_data['order_totals']['enabled'])) echo '1' ?>" <?php if (!empty($offer_data['order_totals']['enabled'])) echo 'checked'; ?>/>
                                <label class="custom-control-label" for="order-totals-enabled">
                                </label>
                                <label class="font-weight-medium"><?php esc_html_e("Enable order totals", 'checkout-upsell-woocommerce'); ?></label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="border border-gray-extra-light rounded pt-3">
                    <div class="cuw-custom-option d-flex justify-content-between px-3 pb-3 cursor-pointer border-gray-light"
                         data-target="#cuw-content-usage-limit">
                        <div class="d-flex" style="gap: 10px;">
                            <i class="cuw-icon-usage-limit mx-1"></i>
                            <h4 class="cuw-option-title">
                                <?php esc_html_e("Usage Limits", 'checkout-upsell-woocommerce'); ?>
                                <span class="offer-index"></span>
                            </h4>
                        </div>
                        <div class="accordion-icon">
                            <i class="cuw-icon-accordion-open"></i>
                        </div>
                    </div>
                    <div id="cuw-content-usage-limit" class="cuw-text-section px-3" style="display: none;">
                        <div class="pb-4 pt-3">
                            <div class="offer-limit form-group">
                                <label for="offer-limit" class="form-label">
                                    <?php esc_html_e("Overall usage limit", 'checkout-upsell-woocommerce'); ?>
                                    <?php esc_html_e("(optional)", 'checkout-upsell-woocommerce'); ?>
                                </label>
                                <input class="form-control" type="number" step="1" id="offer-limit"
                                       name="offers[<?php echo esc_attr($uuid); ?>][limit]"
                                       min="0" value="<?php echo esc_attr($usage_limit); ?>"
                                       placeholder="<?php esc_attr_e("Unlimited usage", 'checkout-upsell-woocommerce'); ?>">
                            </div>
                            <div class="">
                                <label for="offer-limit-per-user" class="form-label">
                                    <?php esc_html_e("Usage limit per customer", 'checkout-upsell-woocommerce'); ?>
                                    <?php esc_html_e("(optional)", 'checkout-upsell-woocommerce'); ?>
                                </label>
                                <input class="form-control" type="number" step="1" id="offer-limit-per-user"
                                       name="offers[<?php echo esc_attr($uuid); ?>][limit_per_user]"
                                       min="0" value="<?php echo esc_attr($usage_limit_per_user); ?>"
                                       placeholder="<?php esc_attr_e("Unlimited usage", 'checkout-upsell-woocommerce'); ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!--Design section-->

            <div id="cuw-design-section"
                 class="cuw-custom-section p-4 border-top border-gray-light d-none flex-column mb-3 mx-1"
                 style="gap: 12px; height: 50vh; overflow: scroll;">
                <div class="border border-gray-extra-light rounded pt-3">
                    <div class="cuw-custom-option d-flex justify-content-between px-3 pb-3 cursor-pointer border-gray-light"
                         data-target="#cuw-design-template">
                        <div class="d-flex" style="gap: 10px;">
                            <i class="cuw-icon-campaigns mx-1"></i>
                            <h4 class="cuw-option-title">
                                <?php esc_html_e("Template", 'checkout-upsell-woocommerce'); ?>
                                <span class="offer-index"></span>
                            </h4>
                        </div>
                        <div class="accordion-icon">
                            <i class="cuw-icon-accordion-open"></i>
                        </div>
                    </div>
                    <div id="cuw-design-template" class="cuw-design-section px-3" style="display: none;">
                        <div class="pb-4 pt-3 d-flex flex-column" style="gap: 12px;">
                            <div class="d-flex" style="gap: 12px;">
                                <div class="w-100">
                                    <label class="form-label"><?php esc_html_e("Background color", 'checkout-upsell-woocommerce'); ?></label>
                                    <div class="cuw-color-inputs input-group position-relative"
                                         style="gap: 8px;">
                                        <input type="text" class="cuw-color-input form-control w-50"
                                               name="offers[<?php echo esc_attr($uuid); ?>][data][styles][template][background-color]"
                                               maxlength="7"
                                               value="<?php echo esc_attr($offer_data['styles']['template']['background-color']); ?>"
                                               placeholder="<?php esc_html_e("Default", 'checkout-upsell-woocommerce'); ?>"
                                               data-name="background-color" data-target=".cuw-offer">
                                        <input style="top:0; right: 0; height: 36px; width: 48px;" type="color"
                                               class="cuw-color-picker color-picker-container border-left-0 rounded-right position-absolute form-control"/>
                                    </div>
                                </div>
                                <div class="w-100">
                                    <label class="form-label"><?php esc_html_e("Padding", 'checkout-upsell-woocommerce'); ?></label>
                                    <div class="input-group" style="gap: 12px;">
                                        <select class="form-control"
                                                name="offers[<?php echo esc_attr($uuid); ?>][data][styles][template][padding]"
                                                data-name="padding" data-target=".cuw-offer">
                                            <option value="" <?php selected('', $offer_data['styles']['template']['padding']); ?>>
                                                <?php esc_html_e("Default", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="8px" <?php selected('8px', $offer_data['styles']['template']['padding']); ?>>
                                                <?php esc_html_e("8px", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="12px" <?php selected('12px', $offer_data['styles']['template']['padding']); ?>>
                                                <?php esc_html_e("12px", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="14px" <?php selected('14px', $offer_data['styles']['template']['padding']); ?>>
                                                <?php esc_html_e("14px", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="16px" <?php selected('16px', $offer_data['styles']['template']['padding']); ?>>
                                                <?php esc_html_e("16px", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="18px" <?php selected('18px', $offer_data['styles']['template']['padding']); ?>>
                                                <?php esc_html_e("18px", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="20px" <?php selected('20px', $offer_data['styles']['template']['padding']); ?>>
                                                <?php esc_html_e("20px", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="24px" <?php selected('24px', $offer_data['styles']['template']['padding']); ?>>
                                                <?php esc_html_e("24px", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="32px" <?php selected('32px', $offer_data['styles']['template']['padding']); ?>>
                                                <?php esc_html_e("32px", 'checkout-upsell-woocommerce'); ?></option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="w-100 cuw-template-border">
                                <label class="form-label"><?php esc_html_e("Border", 'checkout-upsell-woocommerce'); ?></label>
                                <div class="input-group" style="gap:8px;">
                                    <select class="form-control cuw-border-width"
                                            name="offers[<?php echo esc_attr($uuid); ?>][data][styles][template][border-width]"
                                            data-name="border-width" data-target=".cuw-offer">
                                        <option value="0" <?php selected('0', $offer_data['styles']['template']['border-width']); ?>><?php esc_html_e("None", 'checkout-upsell-woocommerce'); ?></option>
                                        <option value="thin" <?php selected('thin', $offer_data['styles']['template']['border-width']); ?>><?php esc_html_e("Thin", 'checkout-upsell-woocommerce'); ?></option>
                                        <option value="medium" <?php selected('medium', $offer_data['styles']['template']['border-width']); ?>><?php esc_html_e("Medium", 'checkout-upsell-woocommerce'); ?></option>
                                        <option value="thick" <?php selected('thick', $offer_data['styles']['template']['border-width']); ?>><?php esc_html_e("Thick", 'checkout-upsell-woocommerce'); ?></option>
                                    </select>
                                    <select class="form-control cuw-border-style"
                                            name="offers[<?php echo esc_attr($uuid); ?>][data][styles][template][border-style]"
                                            data-name="border-style" data-target=".cuw-offer"
                                            style="<?php if (empty($offer_data['styles']['template']['border-width'])) echo 'display: none;'; ?>">
                                        <option value="solid" <?php selected('solid', $offer_data['styles']['template']['border-style']); ?>><?php esc_html_e("Solid", 'checkout-upsell-woocommerce'); ?></option>
                                        <option value="double" <?php selected('double', $offer_data['styles']['template']['border-style']); ?>><?php esc_html_e("Double", 'checkout-upsell-woocommerce'); ?></option>
                                        <option value="dotted" <?php selected('dotted', $offer_data['styles']['template']['border-style']); ?>><?php esc_html_e("Dotted", 'checkout-upsell-woocommerce'); ?></option>
                                        <option value="dashed" <?php selected('dashed', $offer_data['styles']['template']['border-style']); ?>><?php esc_html_e("Dashed", 'checkout-upsell-woocommerce'); ?></option>
                                    </select>
                                    <div class="cuw-border-color"
                                         style="<?php if (empty($offer_data['styles']['template']['border-width'])) echo 'display: none;'; ?>">
                                        <div class="cuw-color-inputs position-relative input-group">
                                            <input type="text" class="cuw-color-input form-control w-50"
                                                   name="offers[<?php echo esc_attr($uuid); ?>][data][styles][template][border-color]"
                                                   data-name="border-color" data-target=".cuw-offer"
                                                   value="<?php echo esc_attr($offer_data['styles']['template']['border-color']); ?>"
                                                   maxlength="7"
                                                   placeholder="<?php esc_html_e("Default", 'checkout-upsell-woocommerce'); ?>">
                                            <input type="color"
                                                   style="top:0; right: 0; height: 36px; width: 48px;"
                                                   class="cuw-color-picker color-picker-container border-left-0 rounded-right position-absolute form-control">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="border border-gray-extra-light rounded pt-3">
                    <div class="cuw-custom-option d-flex justify-content-between px-3 pb-3 cursor-pointer border-gray-light"
                         data-target="#cuw-design-order-details">
                        <div class="d-flex" style="gap: 10px;">
                            <i class="cuw-icon-message-text mx-1"></i>
                            <h4 class="cuw-option-title">
                                <?php esc_html_e("Order details", 'checkout-upsell-woocommerce'); ?>
                                <span class="offer-index"></span>
                            </h4>
                        </div>
                        <div class="accordion-icon">
                            <i class="cuw-icon-accordion-open"></i>
                        </div>
                    </div>
                    <div id="cuw-design-order-details" class="cuw-design-section px-3" style="display: none;">
                        <div class="pb-4 pt-3 d-flex flex-column" style="gap: 12px;">
                            <div class="d-flex flex-column" style="gap: 12px;">
                                <div class="form-group m-0">
                                    <label class="form-label"><?php esc_html_e("Font size", 'checkout-upsell-woocommerce'); ?></label>
                                    <div class="input-group " style="gap: 12px;">
                                        <select class="form-control"
                                                name="offers[<?php echo esc_attr($uuid); ?>][data][styles][order_details][font-size]"
                                                data-name="font-size" data-target=".cuw-ppu-order-details">
                                            <option value="" <?php selected('', $offer_data['styles']['order_details']['font-size']); ?>>
                                                <?php esc_html_e("Default", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="8px" <?php selected('8px', $offer_data['styles']['order_details']['font-size']); ?>>
                                                <?php esc_html_e("8px", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="12px" <?php selected('12px', $offer_data['styles']['order_details']['font-size']); ?>>
                                                <?php esc_html_e("12px", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="14px" <?php selected('14px', $offer_data['styles']['order_details']['font-size']); ?>>
                                                <?php esc_html_e("14px", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="16px" <?php selected('16px', $offer_data['styles']['order_details']['font-size']); ?>>
                                                <?php esc_html_e("16px", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="18px" <?php selected('18px', $offer_data['styles']['order_details']['font-size']); ?>>
                                                <?php esc_html_e("18px", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="20px" <?php selected('20px', $offer_data['styles']['order_details']['font-size']); ?>>
                                                <?php esc_html_e("20px", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="24px" <?php selected('24px', $offer_data['styles']['order_details']['font-size']); ?>>
                                                <?php esc_html_e("24px", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="32px" <?php selected('32px', $offer_data['styles']['order_details']['font-size']); ?>>
                                                <?php esc_html_e("32px", 'checkout-upsell-woocommerce'); ?></option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex" style="gap: 10px;">
                                <div class="d-flex flex-column">
                                    <label class="form-label"><?php esc_html_e("Font color", 'checkout-upsell-woocommerce'); ?></label>
                                    <div class="cuw-color-inputs input-group position-relative"
                                         style="gap: 8px;">
                                        <input type="text" class="cuw-color-input form-control w-50"
                                               name="offers[<?php echo esc_attr($uuid); ?>][data][styles][order_details][color]"
                                               maxlength="7"
                                               value="<?php echo esc_attr($offer_data['styles']['order_details']['color']); ?>"
                                               placeholder="<?php esc_html_e("Default", 'checkout-upsell-woocommerce'); ?>"
                                               data-name="color" data-target=".cuw-ppu-order-details">
                                        <input type="color"
                                               style="top:0; right: 0; height: 36px; width: 48px;"
                                               class="cuw-color-picker color-picker-container border-left-0 rounded-right position-absolute form-control">
                                    </div>
                                </div>
                                <div class="d-flex flex-column">
                                    <label class="form-label"><?php esc_html_e("Background color", 'checkout-upsell-woocommerce'); ?></label>
                                    <div class="cuw-color-inputs input-group position-relative"
                                         style="gap: 8px;">
                                        <input type="text" class="cuw-color-input form-control w-50"
                                               name="offers[<?php echo esc_attr($uuid); ?>][data][styles][order_details][background-color]"
                                               maxlength="7"
                                               value="<?php echo esc_attr($offer_data['styles']['order_details']['background-color']); ?>"
                                               placeholder="<?php esc_html_e("Default", 'checkout-upsell-woocommerce'); ?>"
                                               data-name="background-color" data-target=".cuw-ppu-order-details">
                                        <input type="color" style="top:0; right: 0; height: 36px; width: 48px;"
                                               class="cuw-color-picker color-picker-container border-left-0 rounded-right position-absolute form-control">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="border border-gray-extra-light rounded pt-3">
                    <div class="cuw-custom-option d-flex justify-content-between px-3 pb-3 cursor-pointer border-gray-light"
                         data-target="#cuw-design-title-description">
                        <div class="d-flex" style="gap: 10px;">
                            <i class="cuw-icon-smallcaps mx-1"></i>
                            <h4 class="cuw-option-title">
                                <?php esc_html_e("Title & Description", 'checkout-upsell-woocommerce'); ?>
                                <span class="offer-index"></span>
                            </h4>
                        </div>
                        <div class="accordion-icon">
                            <i class="cuw-icon-accordion-open"></i>
                        </div>
                    </div>
                    <div id="cuw-design-title-description" class="cuw-design-section px-3" style="display: none;">
                        <div class="pb-4 pt-3 d-flex flex-column" style="gap: 12px;">
                            <label class="form-label font-weight-semibold"><?php esc_html_e("Title", 'checkout-upsell-woocommerce'); ?></label>
                            <div class="d-flex flex-column" style="gap: 12px;">
                                <div class="form-group m-0">
                                    <label class="form-label"><?php esc_html_e("Font size", 'checkout-upsell-woocommerce'); ?></label>
                                    <div class="input-group" style="gap: 12px;">
                                        <select class="form-control"
                                                name="offers[<?php echo esc_attr($uuid); ?>][data][styles][title][font-size]"
                                                data-name="font-size" data-target=".cuw-ppu-offer-title">
                                            <option value="" <?php selected('', $offer_data['styles']['title']['font-size']); ?>>
                                                <?php esc_html_e("Default", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="8px" <?php selected('8px', $offer_data['styles']['title']['font-size']); ?>>
                                                <?php esc_html_e("8px", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="12px" <?php selected('12px', $offer_data['styles']['title']['font-size']); ?>>
                                                <?php esc_html_e("12px", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="14px" <?php selected('14px', $offer_data['styles']['title']['font-size']); ?>>
                                                <?php esc_html_e("14px", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="16px" <?php selected('16px', $offer_data['styles']['title']['font-size']); ?>>
                                                <?php esc_html_e("16px", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="18px" <?php selected('18px', $offer_data['styles']['title']['font-size']); ?>>
                                                <?php esc_html_e("18px", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="20px" <?php selected('20px', $offer_data['styles']['title']['font-size']); ?>>
                                                <?php esc_html_e("20px", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="24px" <?php selected('24px', $offer_data['styles']['title']['font-size']); ?>>
                                                <?php esc_html_e("24px", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="32px" <?php selected('32px', $offer_data['styles']['title']['font-size']); ?>>
                                                <?php esc_html_e("32px", 'checkout-upsell-woocommerce'); ?></option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex" style="gap: 10px;">
                                <div class="d-flex flex-column">
                                    <label class="form-label"><?php esc_html_e("Font color", 'checkout-upsell-woocommerce'); ?></label>
                                    <div class="cuw-color-inputs input-group position-relative"
                                         style="gap: 8px;">
                                        <input type="text" class="cuw-color-input form-control w-50"
                                               name="offers[<?php echo esc_attr($uuid); ?>][data][styles][title][color]"
                                               maxlength="7"
                                               value="<?php echo esc_attr($offer_data['styles']['title']['color']); ?>"
                                               placeholder="<?php esc_html_e("Default", 'checkout-upsell-woocommerce'); ?>"
                                               data-name="color" data-target=".cuw-ppu-offer-title">
                                        <input type="color"
                                               style="top:0; right: 0; height: 36px; width: 48px;"
                                               class="cuw-color-picker color-picker-container border-left-0 rounded-right position-absolute form-control">
                                    </div>
                                </div>
                                <div class="d-flex flex-column">
                                    <label class="form-label"><?php esc_html_e("Background color", 'checkout-upsell-woocommerce'); ?></label>
                                    <div class="cuw-color-inputs input-group position-relative"
                                         style="gap: 8px;">
                                        <input type="text" class="cuw-color-input form-control w-50"
                                               name="offers[<?php echo esc_attr($uuid); ?>][data][styles][title][background-color]"
                                               maxlength="7"
                                               value="<?php echo esc_attr($offer_data['styles']['title']['background-color']); ?>"
                                               placeholder="<?php esc_html_e("Default", 'checkout-upsell-woocommerce'); ?>"
                                               data-name="background-color" data-target=".cuw-ppu-offer-title">
                                        <input type="color" style="top:0; right: 0; height: 36px; width: 48px;"
                                               class="cuw-color-picker color-picker-container border-left-0 rounded-right position-absolute form-control">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="pb-3 d-flex flex-column" style="gap: 12px;">
                            <label class="form-label font-weight-semibold"><?php esc_html_e("Description", 'checkout-upsell-woocommerce'); ?></label>
                            <div class="d-flex flex-column" style="gap: 12px;">
                                <div class="form-group m-0">
                                    <label class="form-label"><?php esc_html_e("Font size", 'checkout-upsell-woocommerce'); ?></label>
                                    <div class="input-group " style="gap: 12px;">
                                        <select class="form-control"
                                                name="offers[<?php echo esc_attr($uuid); ?>][data][styles][description][font-size]"
                                                data-name="font-size" data-target=".cuw-ppu-offer-description">
                                            <option value="" <?php selected('8px', $offer_data['styles']['description']['font-size']); ?>>
                                                <?php esc_html_e("Default", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="8px" <?php selected('8px', $offer_data['styles']['description']['font-size']); ?>>
                                                <?php esc_html_e("8px", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="12px" <?php selected('8px', $offer_data['styles']['description']['font-size']); ?>>
                                                <?php esc_html_e("12px", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="14px" <?php selected('8px', $offer_data['styles']['description']['font-size']); ?>>
                                                <?php esc_html_e("14px", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="16px" <?php selected('8px', $offer_data['styles']['description']['font-size']); ?>>
                                                <?php esc_html_e("16px", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="18px" <?php selected('8px', $offer_data['styles']['description']['font-size']); ?>>
                                                <?php esc_html_e("18px", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="20px" <?php selected('8px', $offer_data['styles']['description']['font-size']); ?>>
                                                <?php esc_html_e("20px", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="24px" <?php selected('8px', $offer_data['styles']['description']['font-size']); ?>>
                                                <?php esc_html_e("24px", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="32px" <?php selected('8px', $offer_data['styles']['description']['font-size']); ?>>
                                                <?php esc_html_e("32px", 'checkout-upsell-woocommerce'); ?></option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex" style="gap: 10px;">
                                <div class="d-flex flex-column">
                                    <label class="form-label"><?php esc_html_e("Font color", 'checkout-upsell-woocommerce'); ?></label>
                                    <div class="cuw-color-inputs input-group position-relative"
                                         style="gap: 8px;">
                                        <input type="text" class="cuw-color-input form-control w-50"
                                               name="offers[<?php echo esc_attr($uuid); ?>][data][styles][description][color]"
                                               maxlength="7"
                                               value="<?php echo esc_attr($offer_data['styles']['description']['color']); ?>"
                                               placeholder="<?php esc_html_e("Default", 'checkout-upsell-woocommerce'); ?>"
                                               data-name="color" data-target=".cuw-ppu-offer-description">
                                        <input type="color"
                                               style="top:0; right: 0; height: 36px; width: 48px;"
                                               class="cuw-color-picker color-picker-container border-left-0 rounded-right position-absolute form-control">
                                    </div>
                                </div>
                                <div class="d-flex flex-column">
                                    <label class="form-label"><?php esc_html_e("Background color", 'checkout-upsell-woocommerce'); ?></label>
                                    <div class="cuw-color-inputs input-group position-relative"
                                         style="gap: 8px;">
                                        <input type="text" class="cuw-color-input form-control w-50"
                                               name="offers[<?php echo esc_attr($uuid); ?>][data][styles][description][background-color]"
                                               maxlength="7"
                                               value="<?php echo esc_attr($offer_data['styles']['description']['background-color']); ?>"
                                               placeholder="<?php esc_html_e("Default", 'checkout-upsell-woocommerce'); ?>"
                                               data-name="background-color" data-target=".cuw-ppu-offer-description">
                                        <input type="color" style="top:0; right: 0; height: 36px; width: 48px;"
                                               class="cuw-color-picker color-picker-container border-left-0 rounded-right position-absolute form-control">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="border border-gray-extra-light rounded pt-3">
                    <div class="cuw-custom-option d-flex justify-content-between px-3 pb-3 cursor-pointer border-gray-light"
                         data-target="#cuw-design-timer">
                        <div class="d-flex" style="gap: 10px;">
                            <i class="cuw-icon-clock mx-1"></i>
                            <h4 class="cuw-option-title">
                                <?php esc_html_e("Timer", 'checkout-upsell-woocommerce'); ?>
                                <span class="offer-index"></span>
                            </h4>
                        </div>
                        <div class="accordion-icon">
                            <i class="cuw-icon-accordion-open"></i>
                        </div>
                    </div>
                    <div id="cuw-design-timer" class="cuw-design-section px-3" style="display: none;">
                        <div class="pb-4 pt-3 d-flex flex-column" style="gap: 12px;">
                            <div class="d-flex flex-column" style="gap: 12px;">
                                <div class="form-group m-0">
                                    <label class="form-label"><?php esc_html_e("Font size", 'checkout-upsell-woocommerce'); ?></label>
                                    <div class="input-group " style="gap: 12px;">
                                        <select class="form-control"
                                                name="offers[<?php echo esc_attr($uuid); ?>][data][styles][timer][font-size]"
                                                data-name="font-size" data-target=".cuw-ppu-offer-timer.cuw-built-in">
                                            <option value="" <?php selected('', $offer_data['styles']['timer']['font-size']); ?>>
                                                <?php esc_html_e("Default", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="8px" <?php selected('8px', $offer_data['styles']['timer']['font-size']); ?>>
                                                <?php esc_html_e("8px", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="12px" <?php selected('12px', $offer_data['styles']['timer']['font-size']); ?>>
                                                <?php esc_html_e("12px", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="14px" <?php selected('14px', $offer_data['styles']['timer']['font-size']); ?>>
                                                <?php esc_html_e("14px", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="16px" <?php selected('16px', $offer_data['styles']['timer']['font-size']); ?>>
                                                <?php esc_html_e("16px", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="18px" <?php selected('18px', $offer_data['styles']['timer']['font-size']); ?>>
                                                <?php esc_html_e("18px", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="20px" <?php selected('20px', $offer_data['styles']['timer']['font-size']); ?>>
                                                <?php esc_html_e("20px", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="24px" <?php selected('24px', $offer_data['styles']['timer']['font-size']); ?>>
                                                <?php esc_html_e("24px", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="32px" <?php selected('32px', $offer_data['styles']['timer']['font-size']); ?>>
                                                <?php esc_html_e("32px", 'checkout-upsell-woocommerce'); ?></option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex" style="gap: 10px;">
                                <div class="d-flex flex-column">
                                    <label class="form-label"><?php esc_html_e("Font color", 'checkout-upsell-woocommerce'); ?></label>
                                    <div class="cuw-color-inputs input-group position-relative"
                                         style="gap: 8px;">
                                        <input type="text" class="cuw-color-input form-control w-50"
                                               name="offers[<?php echo esc_attr($uuid); ?>][data][styles][timer][color]"
                                               maxlength="7"
                                               value="<?php echo esc_attr($offer_data['styles']['timer']['color']); ?>"
                                               placeholder="<?php esc_html_e("Default", 'checkout-upsell-woocommerce'); ?>"
                                               data-name="color" data-target=".cuw-ppu-offer-timer.cuw-built-in">
                                        <input type="color"
                                               style="top:0; right: 0; height: 36px; width: 48px;"
                                               class="cuw-color-picker color-picker-container border-left-0 rounded-right position-absolute form-control">
                                    </div>
                                </div>
                                <div class="d-flex flex-column">
                                    <label class="form-label"><?php esc_html_e("Background color", 'checkout-upsell-woocommerce'); ?></label>
                                    <div class="cuw-color-inputs input-group position-relative"
                                         style="gap: 8px;">
                                        <input type="text" class="cuw-color-input form-control w-50"
                                               name="offers[<?php echo esc_attr($uuid); ?>][data][styles][timer][background-color]"
                                               maxlength="7"
                                               value="<?php echo esc_attr($offer_data['styles']['timer']['background-color']); ?>"
                                               placeholder="<?php esc_html_e("Default", 'checkout-upsell-woocommerce'); ?>"
                                               data-name="background-color"
                                               data-target=".cuw-ppu-offer-timer.cuw-built-in">
                                        <input type="color" style="top:0; right: 0; height: 36px; width: 48px;"
                                               class="cuw-color-picker color-picker-container border-left-0 rounded-right position-absolute form-control">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="border border-gray-extra-light rounded pt-3">
                    <div class="cuw-custom-option d-flex justify-content-between px-3 pb-3 cursor-pointer border-gray-light"
                         data-target="#cuw-design-button">
                        <div class="d-flex" style="gap: 10px;">
                            <i class="cuw-icon-row-vertical mx-1"></i>
                            <h4 class="cuw-option-title">
                                <?php esc_html_e("Buttons", 'checkout-upsell-woocommerce'); ?>
                                <span class="offer-index"></span>
                            </h4>
                        </div>
                        <div class="accordion-icon">
                            <i class="cuw-icon-accordion-open"></i>
                        </div>
                    </div>
                    <div id="cuw-design-button" class="cuw-design-section px-3" style="display: none;">
                        <div class="pb-4 pt-3 d-flex flex-column" style="gap: 12px;">
                            <label class="form-label font-weight-semibold"><?php esc_html_e("Accept button", 'checkout-upsell-woocommerce'); ?></label>
                            <div class="d-flex flex-column" style="gap: 12px;">
                                <div class="form-group m-0">
                                    <label class="form-label"><?php esc_html_e("Font size", 'checkout-upsell-woocommerce'); ?></label>
                                    <div class="input-group " style="gap: 12px;">
                                        <select class="form-control"
                                                name="offers[<?php echo esc_attr($uuid); ?>][data][styles][accept_button][font-size]"
                                                data-name="font-size" data-target=".cuw-ppu-accept-button">
                                            <option value="" <?php selected('', $offer_data['styles']['accept_button']['font-size']); ?>>
                                                <?php esc_html_e("Default", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="8px" <?php selected('8px', $offer_data['styles']['accept_button']['font-size']); ?>>
                                                <?php esc_html_e("8px", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="12px" <?php selected('12px', $offer_data['styles']['accept_button']['font-size']); ?>>
                                                <?php esc_html_e("12px", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="14px" <?php selected('14px', $offer_data['styles']['accept_button']['font-size']); ?>>
                                                <?php esc_html_e("14px", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="16px" <?php selected('16px', $offer_data['styles']['accept_button']['font-size']); ?>>
                                                <?php esc_html_e("16px", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="18px" <?php selected('18px', $offer_data['styles']['accept_button']['font-size']); ?>>
                                                <?php esc_html_e("18px", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="20px" <?php selected('20px', $offer_data['styles']['accept_button']['font-size']); ?>>
                                                <?php esc_html_e("20px", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="24px" <?php selected('24px', $offer_data['styles']['accept_button']['font-size']); ?>>
                                                <?php esc_html_e("24px", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="32px" <?php selected('32px', $offer_data['styles']['accept_button']['font-size']); ?>>
                                                <?php esc_html_e("32px", 'checkout-upsell-woocommerce'); ?></option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex" style="gap: 10px;">
                                <div class="d-flex flex-column">
                                    <label class="form-label"><?php esc_html_e("Font color", 'checkout-upsell-woocommerce'); ?></label>
                                    <div class="cuw-color-inputs input-group position-relative"
                                         style="gap: 8px;">
                                        <input type="text" class="cuw-color-input form-control w-50"
                                               name="offers[<?php echo esc_attr($uuid); ?>][data][styles][accept_button][color]"
                                               maxlength="7"
                                               value="<?php echo esc_attr($offer_data['styles']['accept_button']['color']); ?>"
                                               placeholder="<?php esc_html_e("Default", 'checkout-upsell-woocommerce'); ?>"
                                               data-name="color" data-target=".cuw-ppu-accept-button">
                                        <input type="color"
                                               style="top:0; right: 0; height: 36px; width: 48px;"
                                               class="cuw-color-picker color-picker-container border-left-0 rounded-right position-absolute form-control">
                                    </div>
                                </div>
                                <div class="d-flex flex-column">
                                    <label class="form-label"><?php esc_html_e("Background color", 'checkout-upsell-woocommerce'); ?></label>
                                    <div class="cuw-color-inputs input-group position-relative"
                                         style="gap: 8px;">
                                        <input type="text" class="cuw-color-input form-control w-50"
                                               name="offers[<?php echo esc_attr($uuid); ?>][data][styles][accept_button][background-color]"
                                               maxlength="7"
                                               value="<?php echo esc_attr($offer_data['styles']['accept_button']['background-color']); ?>"
                                               placeholder="<?php esc_html_e("Default", 'checkout-upsell-woocommerce'); ?>"
                                               data-name="background-color" data-target=".cuw-ppu-accept-button">
                                        <input type="color" style="top:0; right: 0; height: 36px; width: 48px;"
                                               class="cuw-color-picker color-picker-container border-left-0 rounded-right position-absolute form-control">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="pb-3 d-flex flex-column" style="gap: 12px;">
                            <label class="form-label font-weight-semibold"><?php esc_html_e("Decline button", 'checkout-upsell-woocommerce'); ?></label>
                            <div class="d-flex flex-column" style="gap: 12px;">
                                <div class="form-group m-0">
                                    <label class="form-label"><?php esc_html_e("Font size", 'checkout-upsell-woocommerce'); ?></label>
                                    <div class="input-group " style="gap: 12px;">
                                        <select class="form-control"
                                                name="offers[<?php echo esc_attr($uuid); ?>][data][styles][decline_button][font-size]"
                                                data-name="font-size" data-target=".cuw-ppu-decline-button">
                                            <option value="" <?php selected('', $offer_data['styles']['decline_button']['font-size']); ?>>
                                                <?php esc_html_e("Default", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="8px" <?php selected('8px', $offer_data['styles']['decline_button']['font-size']); ?>>
                                                <?php esc_html_e("8px", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="12px" <?php selected('12px', $offer_data['styles']['decline_button']['font-size']); ?>>
                                                <?php esc_html_e("12px", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="14px" <?php selected('14px', $offer_data['styles']['decline_button']['font-size']); ?>>
                                                <?php esc_html_e("14px", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="16px" <?php selected('16px', $offer_data['styles']['decline_button']['font-size']); ?>>
                                                <?php esc_html_e("16px", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="18px" <?php selected('18px', $offer_data['styles']['decline_button']['font-size']); ?>>
                                                <?php esc_html_e("18px", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="20px" <?php selected('20px', $offer_data['styles']['decline_button']['font-size']); ?>>
                                                <?php esc_html_e("20px", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="24px" <?php selected('24px', $offer_data['styles']['decline_button']['font-size']); ?>>
                                                <?php esc_html_e("24px", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="32px" <?php selected('32px', $offer_data['styles']['decline_button']['font-size']); ?>>
                                                <?php esc_html_e("32px", 'checkout-upsell-woocommerce'); ?></option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex" style="gap: 10px;">
                                <div class="d-flex flex-column">
                                    <label class="form-label"><?php esc_html_e("Font color", 'checkout-upsell-woocommerce'); ?></label>
                                    <div class="cuw-color-inputs input-group position-relative"
                                         style="gap: 8px;">
                                        <input type="text" class="cuw-color-input form-control w-50"
                                               name="offers[<?php echo esc_attr($uuid); ?>][data][styles][decline_button][color]"
                                               maxlength="7"
                                               value="<?php echo esc_attr($offer_data['styles']['decline_button']['color']); ?>"
                                               placeholder="<?php esc_html_e("Default", 'checkout-upsell-woocommerce'); ?>"
                                               data-name="color" data-target=".cuw-ppu-decline-button">
                                        <input type="color"
                                               style="top:0; right: 0; height: 36px; width: 48px;"
                                               class="cuw-color-picker color-picker-container border-left-0 rounded-right position-absolute form-control">
                                    </div>
                                </div>
                                <div class="d-flex flex-column">
                                    <label class="form-label"><?php esc_html_e("Background color", 'checkout-upsell-woocommerce'); ?></label>
                                    <div class="cuw-color-inputs input-group position-relative"
                                         style="gap: 8px;">
                                        <input type="text" class="cuw-color-input form-control w-50"
                                               name="offers[<?php echo esc_attr($uuid); ?>][data][styles][decline_button][background-color]"
                                               maxlength="7"
                                               value="<?php echo esc_attr($offer_data['styles']['decline_button']['background-color']); ?>"
                                               placeholder="<?php esc_html_e("Default", 'checkout-upsell-woocommerce'); ?>"
                                               data-name="background-color" data-target=".cuw-ppu-decline-button">
                                        <input type="color" style="top:0; right: 0; height: 36px; width: 48px;"
                                               class="cuw-color-picker color-picker-container border-left-0 rounded-right position-absolute form-control">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <a href="<?php echo esc_url($template_edit_url); ?>" target="_blank"
                   class="border border-primary rounded d-flex justify-content-between p-3 text-decoration-none">
                    <div class="d-flex" style="gap: 10px;">
                        <h4 class="cuw-option-title text-primary">
                            <?php esc_html_e("Edit template layout", 'checkout-upsell-woocommerce'); ?>
                            <span class="offer-index"></span>
                        </h4>
                    </div>
                    <div>
                        <i class="cuw-icon-external-link text-primary"></i>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>
