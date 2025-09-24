<?php
defined('ABSPATH') || exit;

$offers = $offers ?? [];
$campaign_id = $campaign_id ?? 0;
$offers_map = $offers_map ?? [];
$offers_map_uuid = array_column($offers_map, 'uuid');
$campaign = $campaign ?? [];
$campaign_data = $campaign['data'] ?? [];
$page_builder = $campaign_data['page']['builder'] ?? '';
$template_id = $campaign_data['page']['template'] ?? '';
$template = \CUW\App\Pro\Modules\PostPurchase\Templates::getTemplate($page_builder, $template_id);

$offer_id = isset($offer['id']) ? $offer['id'] : 0;
$product_id = isset($offer['product']['id']) ? $offer['product']['id'] : '0';
$product_qty = isset($offer['product']['qty']) && !empty($offer['product']['qty']) ? floatval($offer['product']['qty']) : '';
$discount_type = isset($offer['discount']['type']) ? $offer['discount']['type'] : '';
$discount_value = isset($offer['discount']['value']) ? floatval($offer['discount']['value']) : '';
$limit = !empty($offer['usage_limit']) ? $offer['usage_limit'] : '';
$limit_per_user = !empty($offer['usage_limit_per_user']) ? $offer['usage_limit_per_user'] : '';
$views = isset($offer['display_count']) ? $offer['display_count'] : '0';
$used = isset($offer['usage_count']) ? $offer['usage_count'] : '0';

$product_name = isset($offer['product_title']) ? $offer['product_title'] : '';
$is_valid = isset($offer['is_valid']) ? $offer['is_valid'] : false;

$image_id = isset($offer['data']['image_id']) ? (int)$offer['data']['image_id'] : 0;
if ($image_id == 0) {
    $image = CUW()->wc->getProductImage($product_id);
} else {
    $image = CUW()->wp->getImage($image_id);
}
$campaign_type = isset($campaign_type) ? $campaign_type : '';
$uuid = $offer['uuid'] ?? '{uuid}';
$parent_uuid = $offers_map[$uuid]['parent_uuid'] ?? '{parent_uuid}';
$accept_uuid = $offers_map[$uuid]['accept_uuid'] ?? '';
$decline_uuid = $offers_map[$uuid]['decline_uuid'] ?? '';
$depth = $offers_map[$uuid]['depth'] ?? '{depth}';
$position = $offers_map[$uuid]['position'] ?? '{position}';
$offer_type = $offer_type ?? '{offer_type}';
?>

<div id="offer-<?php echo esc_attr($uuid); ?>"
     class="cuw-offer mx-auto d-flex flex-column align-items-center w-100 mt-4"
     data-key="<?php echo esc_attr($uuid); ?>" data-index="">
    <div class="offer-item-<?php echo esc_attr($uuid); ?> offer-item d-flex flex-column mx-auto border border-gray-light rounded <?php if (is_numeric($uuid) && !$is_valid) echo 'border-warning'; ?>"
         style="width: 288px; background-color: #ffffff">
        <div class="d-flex flex-column p-3" style="gap: 10px;">
            <div class="d-flex" style="gap: 12px;">
                <div class="offer-item-image rounded mb-2"
                     style="min-width: 42px; height: 42px;"><?php echo isset($image) ? wp_kses_post($image) : ''; ?></div>
                <span class="offer-item-name text-dark font-weight-bold d-block"
                      style="text-align: start;"><?php echo esc_html($product_name); ?></span>
            </div>
            <div class="d-flex flex-column" style="gap: 6px;">
                <div class="d-flex" style="gap:8px;">
                    <small style="font-size: 16px;"><?php esc_html_e("Qty", 'checkout-upsell-woocommerce'); ?>:
                        <span class="offer-item-qty"><?php echo !empty($product_qty) ? esc_html($product_qty) : esc_html__("Custom", 'checkout-upsell-woocommerce'); ?></span>
                    </small>
                    <span class="discount-separator"
                          style="font-size: 16px; <?php if ($discount_type == 'no_discount') echo 'display: none;' ?>">|</span>
                    <small class="discount-block"
                           style="font-size: 16px; <?php if ($discount_type == 'no_discount') echo 'display: none;' ?>"><?php esc_html_e("Discount", 'checkout-upsell-woocommerce'); ?>
                        :
                        <span class="offer-item-discount font-weight-bold">
                            <?php echo esc_html(\CUW\App\Helpers\Discount::getText($product_id, ['value' => $discount_value, 'type' => $discount_type])); ?>
                        </span>
                    </small>
                </div>
            </div>
            <div class="offer-stats d-flex" style="gap: 8px;">
                <?php if (!isset($offer)) {
                    esc_html_e("Publish campaign to see the stats", 'checkout-upsell-woocommerce');
                } else { ?>
                    <small style="font-size: 16px;"><?php esc_html_e("Offer Used", 'checkout-upsell-woocommerce'); ?>:
                        <span class="offer-used text-dark"><?php echo esc_html($used); ?></span>
                    </small>
                    <span style="font-size: 16px;">|</span>
                    <small style="font-size: 16px;"><?php esc_html_e("Views", 'checkout-upsell-woocommerce'); ?>:
                        <span class="offer-views text-dark"><?php echo esc_html($views); ?></span>
                    </small>
                <?php } ?>
            </div>
        </div>
        <div class="offer-actions d-flex mt-auto border-top border-gray-light">
            <span class="offer-edit d-flex w-100 text-secondary d-flex-center cursor-pointer font-small"
                  data-uuid="<?php echo esc_attr($uuid); ?>" style="padding: 14px 0; gap: 4px;">
                 <i class="cuw-icon-edit-note inherit-color"></i><?php esc_html_e("Edit", 'checkout-upsell-woocommerce'); ?>
            </span>
            <a href="<?php echo !empty($template['url']) ? esc_url(add_query_arg(['cuw_ppu_preview' => '1', 'cuw_ppu_preview_offer' => $offer_id], $template['url'])) : '' ?>"
               target="_blank"
               class="offer-view-link w-100 text-decoration-none border-left border-right border-gray-light ">
                <span class="offer-view w-100 text-secondary d-flex-center cursor-pointer font-small"
                      data-key="<?php echo esc_attr($uuid); ?>" style="padding: 14px 0; gap: 4px;">
                     <i class="cuw-icon-eye inherit-color"></i><?php esc_html_e('View', 'checkout-upsell-woocommerce'); ?>
                </span>
            </a>
            <span class="offer-remove w-100 d-flex-center text-secondary cursor-pointer font-small"
                  data-uuid="<?php echo esc_attr($uuid); ?>" data-offer_type="<?php echo esc_attr($offer_type); ?>"
                  data-toggle="modal" data-target="#modal-remove" style="padding: 14px 0; gap: 4px;">
                <i class="cuw-icon-delete inherit-color"></i><?php esc_html_e("Remove", 'checkout-upsell-woocommerce'); ?>
            </span>
        </div>
    </div>

    <div id="offer-<?php echo esc_attr($uuid); ?>-data" class="offer-data d-none">
        <?php
        CUW()->view('Pro/Admin/Campaign/Offer/OfferOptionTabs', [
            'uuid' => $uuid,
            'campaign' => $campaign,
            'offers' => $offers,
            'offers_map' => $offers_map,
        ]) ?>
    </div>
    <ul class="<?php echo ($depth == 3) ? 'd-none' : 'd-flex'; ?> justify-content-between w-100">
        <li class="accept-<?php echo esc_attr($uuid); ?> child-offer w-100 mt-2">
            <div class="d-flex justify-content-center mt-2 offer-flow-badge position-relative">
                <small class="border rounded-pill border-dark-green py-1 px-3 text-success bg-white"><?php esc_html_e("Accept", 'checkout-upsell-woocommerce'); ?></small>
            </div>
            <?php $accept_offer = $offers[$accept_uuid] ?? []; ?>
            <div class="add-accept-<?php echo esc_attr($uuid); ?>-offer-section mt-4 mx-auto p-3 border border-gray-light rounded <?php if (!empty($accept_uuid) && !empty($accept_offer)) echo 'd-none'; ?>"
                 style="width: 264px; background-color: #ffffff;">
                <label class="w-100 text-dark font-weight-semibold"
                       style="text-align: start;">
                    <?php esc_html_e("What's next", 'checkout-upsell-woocommerce'); ?>?
                </label>
                <button type="button" id="offer-accept-<?php echo esc_attr($uuid); ?>" class="btn btn-outline-primary
                add-offer mx-auto justify-content-center w-100" data-offer_type="accept"
                        data-key="<?php echo esc_attr($uuid); ?>"
                        data-parent_offer_uuid="<?php echo esc_attr($uuid); ?>">
                    <i class="cuw-icon-add-circle inherit-color mx-1"></i>
                    <?php esc_html_e("Add offer", 'checkout-upsell-woocommerce'); ?>
                </button>
                <div class="mt-2 d-flex badge-pill-danger rounded p-1" style="text-align: initial;">
                    <i class="cuw-icon-info-circle m-1" style="color: #f13536"></i>
                    <span class="text-danger small">
                        <?php esc_html_e("Customer will exit if no offer is added", 'checkout-upsell-woocommerce'); ?>
                    </span>
                </div>
            </div>
            <?php
            if (!empty($accept_uuid) && !empty($accept_offer)) {
                CUW()->view('Pro/Admin/Campaign/Offer/PostPurchaseOffer', ['key' => $accept_offer,
                    'campaign_id' => $campaign_id,
                    'campaign_type' => $campaign_type,
                    'offer' => $accept_offer,
                    'offers' => $offers,
                    'campaign' => $campaign,
                    'offer_type' => 'accept',
                    'offers_map' => $offers_map,
                    'campaign_data' => $campaign_data,
                ]);
            }
            ?>
        </li>
        <li class="decline-<?php echo esc_attr($uuid); ?> child-offer w-100 mt-2">
            <div class="d-flex justify-content-center mt-2 offer-flow-badge position-relative">
                <small class="border rounded-pill border-warning py-1 px-3 text-warning bg-white">
                    <?php esc_html_e("Decline", 'checkout-upsell-woocommerce'); ?>
                </small>
            </div>
            <?php $decline_offer = $offers[$decline_uuid] ?? []; ?>
            <div class="add-decline-<?php echo esc_attr($uuid); ?>-offer-section mt-4 mx-auto p-3 border border-gray-light rounded <?php if (!empty($decline_uuid) && !empty($decline_offer)) echo 'd-none'; ?>"
                 style="width: 264px; background-color: #ffffff;">
                <label class="w-100 text-dark font-weight-semibold"
                       style="text-align: start;">
                    <?php esc_html_e("What's next", 'checkout-upsell-woocommerce'); ?>?
                </label>
                <button type="button" id="offer-decline-<?php echo esc_attr($uuid); ?>" class="btn btn-outline-primary
                add-offer mx-auto justify-content-center w-100" data-offer_type="decline"
                        data-key="<?php echo esc_attr($uuid); ?>"
                        data-parent_offer_uuid="<?php echo esc_attr($uuid); ?>">
                    <i class="cuw-icon-add-circle inherit-color mx-1"></i>
                    <?php esc_html_e("Add offer", 'checkout-upsell-woocommerce'); ?>
                </button>
                <div class="mt-2 d-flex badge-pill-danger rounded p-1" style="text-align: initial;">
                    <i class="cuw-icon-info-circle m-1" style="color: #f13536"></i>
                    <span class="text-danger small">
                        <?php esc_html_e("Customer will exit if no offer is added", 'checkout-upsell-woocommerce'); ?>
                    </span>
                </div>
            </div>
            <?php
            if (!empty($decline_uuid) && !empty($decline_offer)) {
                CUW()->view('Pro/Admin/Campaign/Offer/PostPurchaseOffer', [
                    'campaign_id' => $campaign_id,
                    'campaign_type' => $campaign_type,
                    'offer' => $decline_offer,
                    'offers' => $offers,
                    'campaign' => $campaign,
                    'offer_type' => 'decline',
                    'offers_map' => $offers_map,
                    'campaign_data' => $campaign_data,
                ]);
            }
            ?>
        </li>
    </ul>
    <div id="offer-<?php echo esc_attr($uuid); ?>-exit-badge"
         class="exit-badge <?php echo ($depth == 3) ? 'd-flex' : 'd-none'; ?>">
        <div class="border rounded-pill border-danger py-1 px-3 text-danger mt-2 bg-white">
            <?php esc_html_e("Exit", 'checkout-upsell-woocommerce'); ?>
        </div>
    </div>
</div>
