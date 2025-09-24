<?php
defined('ABSPATH') || exit;

$key = isset($key) ? intval($key) + 1 : '{key}';
$active = $key == 1 ? 'active' : '';

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
$data = !empty($offer_id) && isset($offer['data']) ? $offer['data'] : \CUW\App\Helpers\Template::getDefaultData('', $campaign_type);
$data_json = json_encode($data, JSON_UNESCAPED_UNICODE);
?>

<div class="cuw-offer" id="offer-<?php echo esc_attr($key); ?>" data-key="<?php echo esc_attr($key); ?>" data-index="">
    <p class="mt-2 mb-2 offer-text text-uppercase"><?php esc_html_e("Offer", 'checkout-upsell-and-order-bumps'); ?><?php echo ' ' . esc_attr($key); ?></p>
    <div class="offer-item mt-2 mb-3 p-3 d-flex align-items-center <?php if (is_numeric($key) && !$is_valid) echo 'border-warning'; ?>">
        <div class="offer-item-image rounded"
             style="min-width: 48px; height: 48px;"><?php echo isset($image) ? wp_kses_post($image) : ''; ?></div>
        <div class="<?php echo CUW()->wp->isRtl() ? 'mr-2 ml-auto' : 'ml-2 mr-auto'; ?>" style="max-width: 50%">
            <span class="offer-item-name text-dark font-weight-bold d-block"><?php echo esc_html($product_name); ?></span>
            <div class="d-flex" style="gap:8px;">
                <small><?php esc_html_e("Qty", 'checkout-upsell-and-order-bumps'); ?>:
                    <span class="offer-item-qty font-weight-bold"><?php echo !empty($product_qty) ? esc_html($product_qty) : esc_html__("Custom", 'checkout-upsell-and-order-bumps'); ?></span>
                </small>
                <?php if ($discount_type != 'no_discount') { ?>
                    <span>|</span>
                    <small><?php esc_html_e("Discount", 'checkout-upsell-and-order-bumps'); ?>:
                        <span class="offer-item-discount font-weight-bold">
                            <?php echo esc_html(\CUW\App\Helpers\Discount::getText($product_id, ['value' => $discount_value, 'type' => $discount_type])); ?>
                        </span>
                    </small>
                <?php } ?>
            </div>
        </div>
        <div class="offer-stats mx-5 d-flex" style="min-width: 128px; gap: 32px">
            <?php if (!isset($offer)) {
                esc_html_e("Publish campaign to see the stats", 'checkout-upsell-and-order-bumps');
            } else { ?>
                <div><h5 class="offer-used text-dark mb-0"><?php echo esc_html($used); ?></h5>
                    <?php esc_html_e("Offer Used", 'checkout-upsell-and-order-bumps'); ?> </div>
                <div><h5 class="offer-views text-dark mb-0"><?php echo esc_html($views); ?></h5>
                    <?php esc_html_e("Views", 'checkout-upsell-and-order-bumps'); ?></div>
            <?php } ?>
        </div>
        <div class="offer-actions d-flex mx-2" style="gap: 8px; min-width: 104px;">
            <span class="offer-view text-secondary d-flex-center cursor-pointer border rounded-lg "
                  title="<?php echo esc_attr__('Preview', 'checkout-upsell-and-order-bumps'); ?>">
                 <i class="cuw-icon-eye"></i>
            </span>
            <span class="offer-edit text-secondary d-flex-center cursor-pointer border border-gray-light rounded-lg"
                  title="<?php esc_html_e("Edit", 'checkout-upsell-and-order-bumps'); ?>"
                  data-id="<?php echo esc_attr($key); ?>">
                 <i class="cuw-icon-edit-note"></i>
            </span>
            <div class="dropdown d-inline-block">
                <button type="button" class="btn btn-data-toggle px-2" data-toggle="dropdown">
                    <i class="cuw-icon-more"></i>
                </button>
                <div class="dropdown-menu">
                    <?php if ($campaign_type != 'post_purchase') { ?>
                        <a class="dropdown-item duplicate-icon-container offer-duplicate d-flex align-items-center text-secondary cursor-pointer"
                           title="<?php esc_html_e("Duplicate", 'checkout-upsell-and-order-bumps'); ?>"
                           data-key="<?php echo esc_attr($key); ?>">
                            <i class="cuw-icon-copy  px-1"></i>
                            <?php esc_html_e("Duplicate", 'checkout-upsell-and-order-bumps'); ?>
                        </a>
                    <?php } ?>
                    <a class="dropdown-item delete-icon-container offer-remove d-flex align-items-center text-secondary cursor-pointer"
                       title="<?php esc_html_e("Remove", 'checkout-upsell-and-order-bumps'); ?>"
                       data-key="<?php echo esc_attr($key); ?>" data-toggle="modal" data-target="#modal-remove">
                        <i class="cuw-icon-delete  px-1"></i>
                        <?php esc_html_e("Delete", 'checkout-upsell-and-order-bumps'); ?>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="offer-data">
        <input type="hidden" name="offers[<?php echo esc_attr($key); ?>][id]"
               value="<?php echo esc_attr($offer_id); ?>">
        <input type="hidden" name="offers[<?php echo esc_attr($key); ?>][product_id]"
               value="<?php echo esc_attr($product_id); ?>">
        <input type="hidden" name="offers[<?php echo esc_attr($key); ?>][product_name]"
               value="<?php echo esc_attr($product_name); ?>">
        <input type="hidden" name="offers[<?php echo esc_attr($key); ?>][product_qty]"
               value="<?php echo esc_attr($product_qty); ?>">
        <input type="hidden" name="offers[<?php echo esc_attr($key); ?>][discount_type]"
               value="<?php echo esc_attr($discount_type); ?>">
        <input type="hidden" name="offers[<?php echo esc_attr($key); ?>][discount_value]"
               value="<?php echo esc_attr($discount_value); ?>">
        <input type="hidden" name="offers[<?php echo esc_attr($key); ?>][limit]"
               value="<?php echo esc_attr($limit); ?>">
        <input type="hidden" name="offers[<?php echo esc_attr($key); ?>][limit_per_user]"
               value="<?php echo esc_attr($limit_per_user); ?>">
        <input type="hidden" name="offers[<?php echo esc_attr($key); ?>][used]" value="<?php echo esc_attr($used); ?>">
        <input type="hidden" name="offers[<?php echo esc_attr($key); ?>][views]"
               value="<?php echo esc_attr($views); ?>">
        <input type="hidden" name="offers[<?php echo esc_attr($key); ?>][data]"
               value='<?php echo esc_attr($data_json); ?>'>
    </div>
</div>
