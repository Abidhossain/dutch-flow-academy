<?php
defined('ABSPATH') || exit;

if (!isset($linked_ids) || !isset($page)) {
    return;
}

$page_url = $page->getUrl();
?>

<?php if (count($linked_ids) == 0) { ?>
    <div class="text-center d-flex justify-content-center align-items-center">
        <div class="p-3">
            <p class="text-secondary pb-2"><?php esc_html_e("No campaigns linked", 'checkout-upsell-woocommerce'); ?></p>
            <a href="<?php echo esc_url($page_url . "&tab=campaigns"); ?>" target="_blank"
               class="text-decoration-none btn btn-outline-primary mt-2 justify-content-center" style="gap: 4px;">
                <?php esc_html_e('Link campaigns', 'checkout-upsell-woocommerce'); ?>
                <i class="cuw-icon-external-link inherit-color mx-1" style="font-size: 18px"></i>
            </a>
        </div>
    </div>
<?php } else { ?>
    <div id="linked-campaigns-list" class="d-flex flex-column p-3" style="height: 30vh; gap: 10px; overflow-y: scroll; margin: 4px;">
        <?php foreach ($linked_ids as $campaign_id) {
            $campaign = \CUW\App\Models\Campaign::get($campaign_id);
            ?>
            <div class="d-flex justify-content-between align-items-center text-decoration-none p-0 bg-white border border-gray-light mt-0" style="border-radius: 6px;">
                <div class="p-3">
                    <h4 class="mb-1" style="font-size: 0.8rem;">
                        <?php echo esc_html($campaign['title']); ?>
                    </h4>
                    <?php $type = \CUW\App\Helpers\Campaign::getType($campaign, true);
                    if (!empty($type) && is_array($type)) { ?>
                        <span style="font-size: 12px;"
                              class="badge-pill-blue-primary w-max px-2 py-1 mt-2 campaign-type-badge"><?php echo esc_html($type['text']); ?></span>
                    <?php } ?>
                </div>
                <div class="p-3">
                    <div class="text-center">
                        <a href="<?php echo esc_url($page_url . "&tab=campaigns" . "&edit=" . $campaign['id']); ?>"
                           title="Edit" target="_blank" class="text-decoration-none inherit-color">
                            <i class="cuw-icon-external-link inherit-color mx-1" style="font-size: 18px"></i>
                        </a>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
<?php } ?>
