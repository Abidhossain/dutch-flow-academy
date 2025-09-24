<?php
defined('ABSPATH') || exit;

use CUW\App\Helpers\Campaign;
use CUW\App\Models\Campaign as CampaignModel;

$rtl = CUW()->wp->isRtl();

$id = 0;
if (CUW()->input->get('create', '', 'query') == 'new') {
    $form = 'create';
    $type = CUW()->input->get('type', '', 'query');
    $campaign = [
        'id' => $id,
        'type' => $type,
        'title' => '',
        'enabled' => 1,
        'priority' => '',
        'offers' => [],
        'filters' => [floor(microtime(true) * 1000) => ['type' => 'all_products']],
        'conditions' => [],
        'data' => ['display_method' => 'random'],
    ];
} elseif (is_numeric($id = CUW()->input->get('edit', '', 'query'))) {
    $form = 'edit';
    $campaign = CampaignModel::get($id, null, true);
} else {
    echo '<p class="text-center mt-5">Unable to perform this action!</p>';
    return;
}

$campaign_type = isset($campaign['type']) ? $campaign['type'] : '';
$campaign_text = Campaign::getTypes($campaign_type);

if (!Campaign::isAvailable($campaign_type)) {
    echo '<p class="text-center mt-5">Unable to load this campaign!</p>';
    return;
}

$is_single = Campaign::isSingle($campaign_type);
if ($is_single && CampaignModel::getCount($campaign_type) > 1) {
    echo '<p class="text-center mt-5">Campaign already created!</p>';
    return;
}

$notices = Campaign::getNotices($campaign_type, $id);
$status = Campaign::getStatus($campaign, true);
$badge = Campaign::getBadge($campaign_type, true);
$available_filters = \CUW\App\Helpers\Filter::get($campaign_type);
$available_conditions = \CUW\App\Helpers\Condition::get($campaign_type);
$filters = isset($campaign['filters']) ? $campaign['filters'] : [];
$conditions = isset($campaign['conditions']) ? $campaign['conditions'] : [];
$campaign_data = isset($campaign['data']) ? $campaign['data'] : [];

$advanced_options = [];
if ($campaign_type == 'noc') {
    $advanced_options['redirect_options'] = ['home', 'shop', 'cart', 'checkout', 'custom'];
} elseif (in_array($campaign_type, ['fbt', 'thankyou_upsells'])) {
    $advanced_options['redirect_options'] = ['default', 'cart', 'checkout', 'custom'];
}

if (!empty($campaign['offers'])) {
    $invalid_products = [];
    foreach ($campaign['offers'] as $key => $offer) {
        if (!empty($offer['product']['id']) && isset($offer['product']['qty'])) {
            $product = CUW()->wc->getProduct($offer['product']['id']);
            $product_title = is_object($product)
                ? CUW()->wc->getProductTitle($product->get_id(), true)
                : __('(Deleted)', 'checkout-upsell-and-order-bumps');
            $is_valid_product = CUW()->wc->isPurchasableProduct($product, $offer['product']['qty']);
            $campaign['offers'][$key]['product_title'] = $product_title;
            $campaign['offers'][$key]['is_valid'] = $is_valid_product;
            if (!$is_valid_product && $product) {
                $invalid_products[] = '<a href="' . esc_url(get_edit_post_link($product->get_id())) . '" target="_blank">' . esc_html($product_title) . '</a>';
            }
        }
    }
    if ($invalid_products) {
        $invalid_products = array_unique($invalid_products);
        $notices[] = [
            'status' => 'warning',
            'message' => __("The following offer products are not purchasable or not has enough stock.", 'checkout-upsell-and-order-bumps')
                . '<br>' . implode("<br>", $invalid_products),
        ];
    }
}
?>

<div id="cuw-campaign" data-type="<?php echo esc_attr($campaign_type); ?>" data-action="<?php echo esc_attr($form); ?>">
    <form id="campaign-form" action="" method="POST" enctype="multipart/form-data"
          style="<?php if (apply_filters('cuw_hide_campaign_form', false, $campaign_type, $campaign)) echo 'display: none;'; ?>">
        <?php if ($form !== 'create') { ?>
            <div id="edit-campaign-name-block" class="title-container" style="display: none">
                <div class="d-flex flex-row" style="gap: 8px;">
                    <input type="text" class="form-control" id="title" name="title"
                           value="<?php echo esc_attr($campaign['title']); ?>"
                           placeholder="<?php esc_attr_e("Campaign name", 'checkout-upsell-and-order-bumps'); ?>"
                           maxlength="255"
                           style="font-size: 16px;"/>
                    <button type="button" id="campaign-name-save" class="btn btn-primary px-3">
                        <i class="cuw-icon-tick-circle text-white mx-1"></i>
                        <?php esc_html_e("Save", 'checkout-upsell-and-order-bumps'); ?>
                    </button>
                    <button type="button" id="campaign-name-close" class="btn btn-outline-secondary px-3">
                        <i class="cuw-icon-close-circle inherit-color mx-1"></i>
                    </button>
                </div>
            </div>
        <?php } ?>
        <div id="header">
            <div class="row title-container m-0">
                <div id="campaign-header" class="col-md-12 p-0 d-flex  align-items-center justify-content-between">
                    <div class="cuw-title-container">
                        <button type="button" id="campaign-close" class="btn border border-gray-extra-light  px-2">
                            <i class="cuw-icon-close"></i>
                        </button>
                        <?php if ($form !== 'create') { ?>
                            <div id="campaign-name-settings" style="gap:6px" class="d-flex align-items-center">
                                <div style="gap:6px" class="d-flex align-items-center"><h5 class="" id="campaign-name">
                                        <?php echo $form == 'create'
                                            ? esc_html__("New Campaign", 'checkout-upsell-and-order-bumps')
                                            : esc_html($campaign['title']);
                                        ?>
                                    </h5>
                                    <i class="cuw-icon-edit-simple  mx-1" id="edit-campaign-name"></i>
                                </div>
                                <span class="badge-pill-blue-primary px-2 py-1 rounded campaign-badge"><?php echo esc_html($campaign_text); ?></span>
                                <?php if ($badge) { ?>
                                    <span class="cuw-badge badge-pill-<?php echo esc_attr($badge['class']); ?> rounded px-2 py-1">
                                        <?php echo esc_html($badge['text']); ?>
                                    </span>
                                <?php } ?>
                            </div>
                        <?php } else { ?>
                            <div class="d-flex align-items-center flex-fill">
                                <input type="text" class="form-control" id="title" name="title"
                                       value="<?php echo !empty($campaign['title']) ? esc_attr($campaign['title']) : ''; ?>"
                                       placeholder="<?php esc_attr_e("Campaign name", 'checkout-upsell-and-order-bumps'); ?>"
                                       maxlength="255"
                                       style="font-size: 16px;">
                            </div>
                            <span class=" mx-2 badge-pill-blue-primary px-2 py-1 rounded campaign-badge"><?php echo esc_html($campaign_text); ?></span>
                            <?php if ($badge) { ?>
                                <span class="cuw-badge ml-1 badge-pill-<?php echo esc_attr($badge['class']); ?> rounded px-2 py-1"
                                      style="margin-left:2px;">
                                    <?php echo esc_html($badge['text']); ?>
                                </span>
                            <?php } ?>
                        <?php } ?>
                    </div>

                    <div id="campaign-actions" class="d-flex align-items-center" style="gap:8px;">
                        <div class="d-flex align-items-center px-1">
                            <div class="custom-control custom-switch custom-switch-md mb-1">
                                <input type="checkbox" name="enabled" value="1"
                                       class="campaign-enable custom-control-input" data-id="campaign-enable"
                                       id="switch-campaign-enable" <?php if ($campaign['enabled'] == 1) echo "checked"; ?>>
                                <label class="custom-control-label pl-2" for="switch-campaign-enable"></label>
                            </div>
                            <?php if (isset($status['code'])) { ?>
                                <div class="px-2">
                                    <span class="text-dark status-<?php echo esc_attr($status['code']); ?>"><?php echo esc_html($status['text']); ?></span>
                                </div>
                            <?php } ?>
                        </div>
                        <button type="button" id="campaign-save"
                                class="btn btn-outline-primary px-2">
                            <i class="cuw-icon-tick-circle inherit-color mx-1"></i>
                            <?php esc_html_e("Save", 'checkout-upsell-and-order-bumps'); ?>
                        </button>
                        <button type="button" id="campaign-save-close"
                                class="btn btn-primary px-2">
                            <i class="cuw-icon-save text-white mx-1"></i>
                            <?php esc_html_e("Save & Close", 'checkout-upsell-and-order-bumps'); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div id="campaign-notices" style="margin: 16px 16px -8px 16px;">
            <?php CUW()->view('Admin/Campaign/Components/Notices', ['notices' => $notices]); ?>
        </div>

        <input type="hidden" id="campaign-id" name="id" value="<?php echo esc_attr($id); ?>">
        <input type="hidden" id="campaign-type" name="type" value="<?php echo esc_attr($campaign_type); ?>">
        <div class="row p-3 " style="">
            <div class="col-md-8 position-relative">
                <?php if (!empty($available_filters)): ?>
                    <?php
                    CUW()->view('Admin/Components/Accordion', [
                        'id' => 'filter',
                        'title' => __('Filters / Apply to', 'checkout-upsell-and-order-bumps'),
                        'icon' => 'filter',
                        'view' => 'Admin/Campaign/Components/Filters',
                        'data' => ['campaign' => $campaign],
                    ]);
                    ?>
                <?php endif; ?>

                <?php do_action('cuw_before_campaign_contents', $campaign_type, $campaign); ?>

                <?php if (in_array($campaign_type, ['checkout_upsells', 'cart_upsells', 'post_purchase'])): ?>
                    <?php
                    CUW()->view('Admin/Components/Accordion', [
                        'id' => 'offers',
                        'title' => __('Offers', 'checkout-upsell-and-order-bumps'),
                        'icon' => 'offers',
                        'view' => 'Admin/Campaign/Components/Offers',
                        'data' => ['campaign' => $campaign, 'available_conditions' => $available_conditions],
                    ]);
                    ?>
                <?php endif; ?>

                <?php do_action('cuw_campaign_contents', $campaign_type, $campaign); ?>

                <?php if (!empty($available_conditions)): ?>
                    <?php
                    CUW()->view('Admin/Components/Accordion', [
                        'id' => 'condition',
                        'title' => __('Conditions', 'checkout-upsell-and-order-bumps'),
                        'icon' => "rules",
                        'view' => 'Admin/Campaign/Components/Conditions',
                        'data' => ['campaign' => $campaign, 'available_conditions' => $available_conditions],
                    ]);
                    ?>

                <?php endif; ?>

                <?php do_action('cuw_after_campaign_contents', $campaign_type, $campaign); ?>
            </div>

            <div class="col-md-4">
                <?php
                CUW()->view('Admin/Components/Accordion', [
                    'id' => 'optional_settings',
                    'title' => __('Optional Settings', 'checkout-upsell-and-order-bumps'),
                    'icon' => 'settings',
                    'view' => 'Admin/Campaign/Components/OptionalSettings',
                    'data' => ['campaign' => $campaign, 'is_single' => $is_single],
                ]);
                do_action('cuw_additional_contents', $campaign_type, $campaign);
                ?>
                <?php if ($campaign_type == 'noc') { ?>
                    <?php
                    CUW()->view('Admin/Components/Accordion', [
                        'id' => 'usage_limits',
                        'title' => __('Usage Limits', 'checkout-upsell-and-order-bumps'),
                        'icon' => 'usage-limit',
                        'view' => 'Admin/Campaign/Components/UsageLimit',
                        'data' => ['campaign' => $campaign],
                    ]);
                    ?>
                <?php } ?>
                <?php if (!empty($advanced_options)) { ?>
                    <?php
                    CUW()->view('Admin/Components/Accordion', [
                        'id' => 'advanced_options',
                        'title' => __('Advanced Options', 'checkout-upsell-and-order-bumps'),
                        'icon' => 'options',
                        'view' => 'Admin/Campaign/Components/AdvancedOptions',
                        'data' => array_merge(['campaign' => $campaign], $advanced_options),
                    ]);
                    ?>
                <?php } ?>
                <?php
                CUW()->view('Admin/Components/Accordion', [
                    'id' => 'tutorials',
                    'title' => __('Tutorials', 'checkout-upsell-and-order-bumps'),
                    'icon' => 'books',
                    'view' => 'Admin/Campaign/Components/Tutorials',
                    'data' => ['campaign' => $campaign],
                    'expand' => false,
                ]);
                ?>
                <?php if ($form == 'edit') { ?>
                    <?php
                    CUW()->view('Admin/Components/Accordion', [
                        'id' => 'info',
                        'title' => __('Information', 'checkout-upsell-and-order-bumps'),
                        'icon' => 'info-circle',
                        'view' => 'Admin/Campaign/Components/Information',
                        'data' => ['campaign' => $campaign],
                        'expand' => false,
                    ]);
                    ?>
                <?php } ?>
            </div>
        </div>
        <?php
        if (in_array($campaign_type, ['fbt', 'noc', 'upsell_popups', 'double_order', 'thankyou_upsells', 'product_addons', 'cart_addons', 'product_recommendations'])) {
            CUW()->view('Admin/Components/Slider', [
                'id' => 'edit-template',
                'width' => '80%',
                'view' => 'Admin/Campaign/Sliders/EditCampaignTemplate',
                'data' => ['campaign' => $campaign],
            ]);
        }
        ?>
    </form>
    <?php
    CUW()->view('Admin/Components/Slider', [
        'id' => 'condition',
        'width' => '25%',
        'view' => 'Admin/Campaign/Sliders/AddCondition',
        'data' => ['campaign' => $campaign],
    ]);

    if ($campaign_type == 'product_recommendations') {
        CUW()->view('Admin/Components/Slider', [
            'id' => 'engine-filter',
            'width' => '50%',
            'view' => 'Pro/Admin/Engine/Slider/AddFilter',
            'data' => ['campaign' => $campaign],
        ]);
    }

    if (in_array($campaign_type, ['fbt', 'product_addons', 'cart_addons'])) {
        CUW()->view('Admin/Components/Slider', [
            'id' => 'filter',
            'width' => '25%',
            'view' => 'Admin/Campaign/Sliders/AddFilter',
            'data' => ['campaign' => $campaign],
        ]);
    }

    if (in_array($campaign_type, ['checkout_upsells', 'cart_upsells', 'post_purchase', 'post_purchase_upsells'])) {
        CUW()->view('Admin/Components/Slider', [
            'id' => 'offer',
            'width' => '80%',
            'view' => 'Admin/Campaign/Sliders/EditOffer',
            'data' => ['form' => $form, 'campaign' => $campaign, 'campaign_type' => $campaign_type, 'campaign_text' => $campaign_text],
        ]);
        CUW()->view('Admin/Components/Slider', [
            'id' => 'view-offer',
            'width' => '60%',
            'view' => 'Admin/Campaign/Sliders/ViewOffer',
            'data' => ['form' => $form, 'campaign' => $campaign, 'campaign_type' => $campaign_type, 'campaign_text' => $campaign_text],
        ]);
        CUW()->view('Admin/Components/Slider', [
            'id' => 'choose-offer-template',
            'width' => '50%',
            'view' => 'Admin/Campaign/Sliders/ChangeOfferTemplate',
            'data' => ['campaign' => $campaign],
        ]);
        CUW()->view('Admin/Campaign/Modals/RemoveOffer');
    } else {
        CUW()->view('Admin/Components/Slider', [
            'id' => 'template',
            'width' => '50%',
            'view' => 'Admin/Campaign/Sliders/ChangeCampaignTemplate',
            'data' => ['campaign' => $campaign],
        ]);
        CUW()->view('Admin/Components/Slider', [
            'id' => 'view-template',
            'width' => '60%',
            'view' => 'Admin/Campaign/Sliders/ViewTemplate',
            'data' => ['campaign' => $campaign],
        ]);
    }
    do_action('cuw_after_campaign_page', $campaign_type, $campaign);
    ?>
</div>
