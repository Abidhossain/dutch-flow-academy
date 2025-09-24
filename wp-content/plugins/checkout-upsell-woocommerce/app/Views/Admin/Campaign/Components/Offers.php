<?php
defined('ABSPATH') || exit;
if (!isset($campaign)) {
    return;
}
$campaign_type = $campaign['type'];
$campaign_data = $campaign['data'];
$has_pro = CUW()->plugin->has_pro;

$offer_display_locations = \CUW\App\Helpers\Offer::getDisplayLocations($campaign_type);
$offers_max_limit = \CUW\App\Helpers\Offer::getMaxLimit($campaign_type);
$offer_display_method = $campaign_data['display_method'] ?? 'random';
$offer_display_location = \CUW\App\Helpers\Campaign::getDisplayLocation($campaign);

$display_locations_on_mini_cart = \CUW\App\Helpers\Offer::getDisplayLocationsOnMiniCart($campaign_type);
$display_location_on_mini_cart = \CUW\App\Helpers\Campaign::getDisplayLocation($campaign, 'display_location_on_mini_cart');
?>

<?php if (in_array($campaign_type, ['checkout_upsells', 'cart_upsells'])) { ?>
    <?php
    $offer_a_percentage = $campaign_data['a']['percentage'] ?? '50';
    $offer_b_percentage = $campaign_data['b']['percentage'] ?? '50';
    ?>
    <div id="offer-data" class="row">
        <div class="offer-location col-md-5">
            <label for="offer-display-location" class="form-label">
                <?php if ($campaign_type == 'cart_upsells') {
                    esc_html_e("Display location at Cart page", 'checkout-upsell-woocommerce');
                } else {
                    esc_html_e("Display location at Checkout page", 'checkout-upsell-woocommerce');
                } ?>
            </label>
            <select class="form-control" id="offer-display-location" name="data[display_location]">
                <?php CUW()->view('Admin/Campaign/Components/LocationOptions', ['locations' => $offer_display_locations, 'selected_location' => $offer_display_location]); ?>
            </select>
        </div>
        <div class="offer-select col-md-3">
            <label for="offer-display-method"
                   class="form-label"><?php esc_html_e("Display Method", 'checkout-upsell-woocommerce'); ?></label>
            <select class="form-control" id="offer-display-method" name="data[display_method]">
                <option value="all" <?php if ($offer_display_method == 'all') echo "selected"; elseif (!$has_pro) echo "disabled"; ?>>
                    <?php esc_html_e("All offers", 'checkout-upsell-woocommerce'); ?>
                    <?php if (!CUW()->plugin->has_pro) echo esc_html(" – " . __("PRO", 'checkout-upsell-woocommerce')); ?>
                </option>
                <option value="random" <?php if ($offer_display_method == 'random') echo "selected"; ?>>
                    <?php esc_html_e("Random offer", 'checkout-upsell-woocommerce'); ?>
                </option>
                <option value="ab_testing" <?php if ($offer_display_method == 'ab_testing') echo "selected"; elseif (!$has_pro) echo "disabled"; ?>>
                    <?php esc_html_e("A/B Testing", 'checkout-upsell-woocommerce'); ?>
                    <?php if (!$has_pro) echo esc_html(' – ' . __("PRO", 'checkout-upsell-woocommerce')); ?>
                </option>
            </select>
        </div>

        <div id="ab-testing-section" class="col-md-4"
             style="display: <?php echo $offer_display_method == 'ab_testing' ? 'flex' : 'none'; ?>; gap: 8px;">
            <div class="offer-a w-50">
                <label for="offer-a"
                       class="form-label"><?php esc_html_e("Offer A", 'checkout-upsell-woocommerce'); ?></label>
                <div class="input-group">
                    <input class="form-control" type="number" id="offer-a" name="data[a][percentage]" min="0" max="100"
                           value="<?php echo esc_attr($offer_a_percentage); ?>" <?php if ($offer_display_method != 'ab_testing') echo "disabled"; ?>>
                    <div class="input-group-append"><span class="input-group-text px-2">%</span></div>
                </div>
            </div>
            <div class="offer-b w-50">
                <label for="offer-b"
                       class="form-label"><?php esc_html_e("Offer B", 'checkout-upsell-woocommerce'); ?></label>
                <div class="input-group">
                    <input class="form-control" type="number" id="offer-b" name="data[b][percentage]" min="0" max="100"
                           value="<?php echo esc_attr($offer_b_percentage); ?>" <?php if ($offer_display_method != 'ab_testing') echo "disabled"; ?>>
                    <div class="input-group-append"><span class="input-group-text px-2">%</span></div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <?php if ($campaign_type == 'cart_upsells' && !empty($display_locations_on_mini_cart)) { ?>
            <div class="mini-cart-offer-location col-md-5 mt-2">
                <label for="mini-cart-offer-display-location" class="form-label">
                    <?php esc_html_e('Display location on Mini-cart', 'checkout-upsell-woocommerce'); ?>
                </label>
                <select class="form-control" id="mini-cart-offer-display-location"
                        name="data[display_location_on_mini_cart]">
                    <option value="do_not_display" <?php if ($display_location_on_mini_cart == 'do_not_display') echo 'selected'; ?>>
                        <?php esc_html_e('Do not display', 'checkout-upsell-woocommerce'); ?>
                    </option>
                    <?php CUW()->view('Admin/Campaign/Components/LocationOptions', ['locations' => $display_locations_on_mini_cart, 'selected_location' => $display_location_on_mini_cart]); ?>
                </select>
            </div>
        <?php } ?>
    </div>
<?php } ?>

<div id="cuw-offers" class="mt-3">
    <div class="cuw-offer-message text-center mt-3 text-secondary" <?php if (!empty($campaign['offers'])) echo 'style="display: none;"' ?>>
        <?php esc_html_e("Add an offer to start using this campaign", 'checkout-upsell-woocommerce'); ?>
    </div>
    <?php if (!empty($campaign['offers'])) {
        foreach ($campaign['offers'] as $key => $offer) {
            CUW()->view('Admin/Campaign/Offer/Content', ['key' => $key, 'campaign_id' => $campaign['id'], 'campaign_type' => $campaign_type, 'offer' => $offer]);
        }
    } ?>
</div>

<div class="d-flex mt-3 align-items-center" style="gap:12px;">
    <div id="offer-add" class="d-flex align-items-center">
        <button type="button" class="btn btn-outline-primary px-2" style="gap:4px;">
            <i class="cuw-icon-add-circle" style="color: inherit"></i>
            <?php esc_html_e("Add offer", 'checkout-upsell-woocommerce'); ?>
        </button>
    </div>
    <div>
        <?php $offers_max_limit = ($offer_display_method == 'ab_testing') ? 2 : $offers_max_limit; ?>
        <span>
            <small class="font-weight-medium text-secondary offers-max-limit"><?php echo esc_html(sprintf(__("Maximum: %s", 'checkout-upsell-woocommerce'), $offers_max_limit)); ?></small>
        </span>
        <?php if (!$has_pro) { ?>
            <span class="d-block mt-1 small">
            <?php esc_html_e("To add more offers", 'checkout-upsell-woocommerce'); ?> –
            <a class="text-decoration-none" href="<?php echo esc_url(CUW()->plugin->getUrl($campaign_type)); ?>"
               target="_blank">
                <?php esc_html_e("Upgrade to PRO", 'checkout-upsell-woocommerce'); ?>
            </a>
        </span>
        <?php } ?>
    </div>
</div>