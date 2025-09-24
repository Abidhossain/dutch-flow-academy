<?php
defined('ABSPATH') || exit;
if (!isset($action)) {
    return;
}
?>

<?php if ($action == 'cuw_campaign_contents' && isset($campaign)): ?>

    <?php
    CUW()->view('Admin/Components/Accordion', [
        'id' => 'timer',
        'title' => __('Timer', 'checkout-upsell-woocommerce'),
        'icon' => 'clock',
        'view' => 'Pro/Admin/Campaign/Components/Timer',
        'data' => ['campaign' => $campaign],
    ]);
    ?>

<?php elseif ($action == 'cuw_after_offer_tabs'): ?>
    <li class="nav-item">
        <a class="nav-link" data-toggle="tab" href="#template-contents">
            2.<?php esc_html_e("Content", 'checkout-upsell-woocommerce'); ?>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-toggle="tab" href="#template-styling">
            3.<?php esc_html_e("Page", 'checkout-upsell-woocommerce'); ?>
        </a>
    </li>
<?php elseif ($action == 'cuw_after_offer_tab_contents'): ?>
    <div class="tab-pane fade" id="template-styling">
        <div class="legend mb-3"><?php esc_html_e("Page", 'checkout-upsell-woocommerce'); ?></div>
        <div class="row">
            <div class="col-12">
                <label for="offer-page-id"
                       class="form-label"><?php esc_html_e("Offer page", 'checkout-upsell-woocommerce'); ?></label>
                <div class="text-dark" id="offer-page-id"></div>
            </div>
            <div class="col-12 mt-2">
                <?php $offer_pages = \CUW\App\Pro\Helpers\OfferPage::availablePages(); ?>
                <select class="form-control select2-local" id="page-id">
                    <?php foreach ($offer_pages as $page_id => $data) {
                        $page_title = '#' . $page_id . ' ' . $data['title'] . ($data['default'] ? ' ' . esc_html__("(default)", 'checkout-upsell-woocommerce') : '');
                        echo '<option value="' . esc_attr($page_id) . '" data-slug="' . esc_attr($data['slug']) . '" data-url="' . esc_attr($data['url']) . '">' . esc_html($page_title) . '</option>';
                    } ?>
                </select>
            </div>
        </div>
    </div>
<?php endif; ?>
