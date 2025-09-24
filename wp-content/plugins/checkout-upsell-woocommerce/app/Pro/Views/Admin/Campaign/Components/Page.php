<?php
defined('ABSPATH') || exit;
if (!isset($campaign)) {
    return;
}
$campaign_id = $campaign['id'] ?? '';
$display_limit = !empty($campaign['data']['display_limit']) ? $campaign['data']['display_limit'] : '0';
$columns = !empty($campaign['data']['columns']) ? $campaign['data']['columns'] : '';
$display_location = \CUW\App\Helpers\Campaign::getDisplayLocation($campaign);

$page_types = \CUW\App\Pro\Helpers\Page::get();
$page = !empty($campaign['data']['page']) ? $campaign['data']['page'] : '';
$page_type = CUW()->input->get('page_type');
$page_type = !empty($page_type) ? $page_type : $page;
?>

    <div class="p-4">
        <div class="row">
            <div class="page col-md-6">
                <label for="page-type"
                       class="form-label"><?php esc_html_e("Page to display the recommendations", 'checkout-upsell-woocommerce'); ?></label>
                <select class="form-control" id="page-type" name="data[page]"
                        style="<?php echo $campaign_id != 0 ? 'pointer-events: none; opacity: 0.8;' : 'pointer-events: auto; opacity: 1;'; ?>">
                    <option value="" selected disabled>
                        <?php echo esc_html__("Choose a page", 'checkout-upsell-woocommerce') ?>
                    </option>
                    <?php foreach ($page_types as $key => $page) {
                        $url = \CUW\App\Controllers\Admin\Page::getUrl(['page_type' => $key], true);
                        ?>
                        <option value="<?php echo esc_attr($key); ?>"
                                data-url="<?php echo esc_url($url); ?>" <?php if ($page_type == $key) echo "selected"; ?> ><?php echo esc_html($page['title']); ?></option>
                    <?php } ?>
                </select>
            </div>

            <?php if (empty($campaign_id) && empty($page_type)) { ?>
                <div class="col-md-6" style="margin-top: 22px;">
                    <label class="form-label">
                        <?php esc_html_e("Select the page where you'd like product recommendations to appear and customize the appearance. For example: Cart page", 'checkout-upsell-woocommerce'); ?>
                    </label>
                </div>
            <?php } ?>

            <?php if (!empty($display_locations)) { ?>
                <div class="display-location col-md-6">
                    <label for="display-location"
                           class="form-label"><?php echo !empty($display_location_text) ? esc_html($display_location_text) : '' ?></label>
                    <select class="form-control" id="display-location" name="data[display_location]">
                        <?php foreach ($display_locations as $location) { ?>
                            <option value="<?php echo esc_attr($location['hook']); ?>" <?php if ($display_location == $location['hook']) echo "selected"; ?>><?php echo esc_html($location['title']); ?></option>
                        <?php } ?>
                    </select>
                </div>
            <?php } ?>
        </div>

        <?php if (!empty($display_locations)) { ?>
            <div class="row mt-2">
                <div class="pr_columns col-md-6">
                    <label for="pr-columns"
                           class="form-label"><?php esc_html_e("Products columns", 'checkout-upsell-woocommerce'); ?></label>
                    <select class="form-control" id="pr-columns" name="data[columns]">
                        <option value="" <?php if ($columns == '') echo "selected"; ?>>
                            <?php esc_html_e("Automatic", 'checkout-upsell-woocommerce'); ?>
                        </option>
                        <option value="1" <?php if ($columns == '1') echo "selected"; ?>>1</option>
                        <option value="2" <?php if ($columns == '2') echo "selected"; ?>>2</option>
                        <option value="3" <?php if ($columns == '3') echo "selected"; ?>>3</option>
                        <option value="4" <?php if ($columns == '4') echo "selected"; ?>>4</option>
                        <option value="5" <?php if ($columns == '5') echo "selected"; ?>>5</option>
                        <option value="6"<?php if ($columns == '6') echo "selected"; ?>>6</option>
                    </select>
                </div>
                <div class="pr_display_limit col-md-6">
                    <label for="display-limit"
                           class="form-label"><?php esc_html_e("Number of products to show", 'checkout-upsell-woocommerce'); ?></label>
                    <select class="form-control" id="display-limit" name="data[display_limit]">
                        <option value="0" <?php if ($display_limit == '0') echo "selected"; ?>>
                            <?php esc_html_e("No limits", 'checkout-upsell-woocommerce'); ?>
                        </option>
                        <?php CUW()->view('Admin/Components/LimitOptions', ['selected_limit' => $display_limit]); ?>
                    </select>
                </div>
            </div>
        <?php } ?>
    </div>

<?php if (!empty($display_locations) && empty($campaign_id)) { ?>
    <div class="form-separator m-0"></div>

    <div class="input-group flex-row-reverse justify-content-between p-3" style="gap: 8px;">
        <button type="button"
                class="btn btn-outline-primary move-to-filter <?php if (!empty($filters)) echo 'd-none'; ?>">
            <?php esc_html_e("Next", 'checkout-upsell-woocommerce'); ?>
            <i class="cuw-icon-chevron-right inherit-color mx-1"></i>
        </button>
    </div>
<?php } ?>