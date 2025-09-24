<?php
defined('ABSPATH') || exit;
if (!isset($campaign)) {
    return;
}

$has_pro = CUW()->plugin->has_pro;
$campaign_type = $campaign['type'] ?? '';
$template = !empty($campaign['data']['template']) ? $campaign['data']['template'] : [];
$discount = !empty($campaign['data']['discount']) ? $campaign['data']['discount'] : [];

$template_name = \CUW\App\Helpers\Campaign::getTemplateName($campaign);
$default_template = \CUW\App\Helpers\Template::getDefaultData($template_name, $campaign_type);
$template = array_merge($default_template, $template);
$template['name'] = $template_name;

$display_location = \CUW\App\Helpers\Campaign::getDisplayLocation($campaign);
?>

<div class="row p-3">
    <div class="col-md-12">
        <?php if ($campaign['type'] == 'product_recommendations') { ?>
            <div class="row">
                <div class="col-md-6 d-flex flex-column">
                    <label for="template-name" class="form-label"><?php esc_html_e("Template name", 'checkout-upsell-woocommerce'); ?></label>
                    <input type="text" class="form-control" id="template-name" name="data[template][name]"
                           value="<?php echo (isset($template['name'])) ? esc_attr($template['name']) : esc_attr($template['template']); ?>"
                           readonly>
                </div>
                <div class="col-md-6 d-flex flex-column">
                    <label for="template-title" class="form-label"><?php esc_html_e("Template title", 'checkout-upsell-woocommerce'); ?></label>
                    <input type="text" class="form-control" id="template-title" name="data[template][title]"
                           value="<?php echo (isset($template['title'])) ? esc_attr($template['title']) : ''; ?>">
                </div>
            </div>
        <?php } else { ?>
            <label for="template-name"
                   class="form-label"><?php esc_html_e("Template", 'checkout-upsell-woocommerce'); ?></label>
            <div class="input-group d-flex align-items-center justify-content-between">
<!--                <p id="template-name" class="font-weight-semibold text-dark" name="data[template][name]">-->
<!--                --><?php //echo (isset($template['name'])) ? esc_attr($template['name']) : esc_attr($template['template']); ?><!--</p>-->
                <input type="text" class="form-control" id="template-name" name="data[template][name]"
                       value="<?php echo (isset($template['name'])) ? esc_attr($template['name']) : esc_attr($template['template']); ?>"
                       readonly>
                <div class="input-group-append d-flex flex-wrap align-items-center" style="gap:8px;">
                    <div class="d-flex-center border rounded-lg view-template" id="view-template" style="padding: 6px;"
                         title="<?php echo esc_attr__('Preview', 'checkout-upsell-woocommerce'); ?>">
                        <i class="cuw-icon-eye"></i>
                    </div>
                    <button type="button" class="btn rounded border border-primary text-primary choose-template "
                            id="choose-template">
                        <i class="cuw-icon-campaigns text-primary mx-1"></i><?php esc_html_e("Change template", 'checkout-upsell-woocommerce'); ?>
                    </button>
                    <button type="button" class="btn btn-primary rounded edit-template" id="edit-template">
                        <i class="cuw-icon-edit-simple text-white  mx-1"></i>
                        <?php esc_html_e("Edit Content/Style", 'checkout-upsell-woocommerce'); ?>
                    </button>
                </div>
            </div>
        <?php } ?>
    </div>
</div>

<?php if (!empty($display_locations)) { ?>
    <div class="form-separator m-0"></div>

    <div class="row p-3">
        <div class="display-location col-md-6">
            <label for="display-location"
                   class="form-label"><?php echo !empty($display_location_text) ? esc_html($display_location_text) : '' ?></label>
            <select class="form-control" id="display-location" name="data[display_location]">
                <?php if ($campaign_type == 'noc') { ?>
                    <?php CUW()->view('Admin/Campaign/Components/LocationOptions', ['locations' => $display_locations, 'selected_location' => $display_location]); ?>
                <?php } else { ?>
                    <?php foreach ($display_locations as $action => $name) { ?>
                        <option value="<?php echo esc_attr($action); ?>" <?php if ($display_location == $action) echo "selected"; ?>><?php echo esc_html($name); ?></option>
                    <?php }
                } ?>
            </select>
        </div>
        <?php if ($campaign_type == 'noc') {
            $display_locations_on_email = \CUW\App\Modules\Campaigns\NOC::getDisplayLocationsOnEmail();
            $display_locations_on_myaccount_page = \CUW\App\Modules\Campaigns\NOC::getDisplayLocationsOnMyAccountPage();

            $display_location_on_email = \CUW\App\Helpers\Campaign::getDisplayLocation($campaign, 'display_location_on_email');
            $display_location_on_myaccount_page = \CUW\App\Helpers\Campaign::getDisplayLocation($campaign, 'display_location_on_myaccount_page');
            if (!$has_pro) {
                $display_location_on_email = 'do_not_display';
            }
            ?>
            <div class="col-md-6 email-display-location">
                <label for="email-display-location" class="form-label d-block">
                    <?php esc_html_e("Display location on Emails", 'checkout-upsell-woocommerce'); ?>
                    <?php if (!$has_pro) { ?>
                        <span class="float-right"><?php esc_html_e("Unlock this feature by", 'checkout-upsell-woocommerce'); ?>
                            <a class="text-decoration-none"
                               href="<?php echo esc_url(CUW()->plugin->getUrl($campaign_type)); ?>" target="_blank">
                                <?php esc_html_e("Upgrading to PRO", 'checkout-upsell-woocommerce'); ?>
                            </a>
                        </span>
                    <?php } ?>
                </label>
                <select class="form-control" id="email-display-location"
                        name="data[display_location_on_email]" <?php if (!$has_pro) echo 'disabled'; ?>>
                    <?php CUW()->view('Admin/Campaign/Components/LocationOptions', ['locations' => $display_locations_on_email, 'selected_location' => $display_location_on_email]); ?>
                </select>
            </div>
            <div class="col-md-6 display-location-on-myacccount-page mt-2">
                <label for="display-location"
                       class="form-label"><?php esc_html_e("Display location on the My account page", 'checkout-upsell-woocommerce'); ?></label>
                <select class="form-control" id="display-location" name="data[display_location_on_myaccount_page]">
                    <?php CUW()->view('Admin/Campaign/Components/LocationOptions', ['locations' => $display_locations_on_myaccount_page, 'selected_location' => $display_location_on_myaccount_page]); ?>
                </select>
            </div>
        <?php } ?>
    </div>
<?php } ?>

<?php if ($campaign['type'] == 'product_recommendations' && empty($campaign['id'])) { ?>
    <div class="form-separator m-0"></div>

    <div class="input-group flex-row flex-row-reverse p-3" style="gap: 8px;">
        <button type="button" id="campaign-save"
                class="btn btn-outline-primary px-2">
            <i class="cuw-icon-tick-circle inherit-color mx-1"></i>
            <?php esc_html_e("Save", 'checkout-upsell-woocommerce'); ?>
        </button>
    </div>
<?php } ?>
