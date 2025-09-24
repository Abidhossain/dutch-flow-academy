<?php
defined('ABSPATH') || exit;
if (!isset($campaign)) {
    return;
}
$campaign_type = $campaign['type'];
$campaign_data = $campaign['data'];
$offers = [];
foreach ($campaign['offers'] as $offer) {
    $offers[$offer['uuid']] = $offer;
}
$main_order = \CUW\App\Pro\Modules\PostPurchase\Offer::getMainOffer($campaign, $offers);

$background_image_url = \CUW\App\Helpers\Assets::getUrl('img-pro/offer-flow-background.svg');
?>

<div class="offer-flow d-flex w-100 p-1"
     style="background-image: url('<?php echo esc_attr($background_image_url); ?>')">
    <div class="offer-flow-section overflow-auto p-4 w-100">
        <div class="cuw-offer-message text-center mt-3 text-secondary" <?php if (!empty($campaign['offers'])) echo 'style="display: none;"' ?>></div>
        <div id="no-offers" class="<?php echo (!empty($campaign['offers'])) ? 'd-none' : 'd-flex' ?>"
             style="padding: 48px 0;">
            <div class="mx-auto bg-light">
                <button type="button" class="btn btn-outline-primary add-offer">
                    <?php esc_html_e("Add an offer to start using this campaign", 'checkout-upsell-woocommerce'); ?>
                </button>
            </div>
        </div>
        <ul class="w-100">
            <li class="d-flex">
                <?php if (!empty($campaign['offers'])) {
                    if (!empty($campaign_data['offers_map']) && $campaign_type == 'post_purchase_upsells') {
                        CUW()->view('Pro/Admin/Campaign/Offer/PostPurchaseOffer', [
                            'campaign_id' => $campaign['id'],
                            'campaign_type' => $campaign_type,
                            'offer' => $main_order,
                            'offers' => $offers,
                            'offer_type' => 'parent',
                            'offers_map' => $campaign_data['offers_map'],
                            'campaign' => $campaign,
                        ]);
                    }
                } ?>
            </li>
        </ul>
    </div>
</div>
