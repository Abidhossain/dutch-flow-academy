<?php
defined('ABSPATH') || exit;

$campaigns = \CUW\App\Models\Campaign::all([
    'columns' => ['id', 'title'],
    'order_by' => 'title',
    'sort' => 'asc'
]);
$campaign_id = CUW()->input->get('campaign_id', '', 'query');
$campaign_types = \CUW\App\Helpers\Campaign::getTypes();
$currency_types = \CUW\App\Models\Stats::getAvailableCurrencies();
$revenue_tax_display = CUW()->config->get('revenue_tax_display', 'without_tax');
?>

<div id="cuw-reports" data-tab="reports">
    <div class="d-flex justify-content-between title-container align-items-center">
        <h5><?php esc_html_e("Reports", 'checkout-upsell-woocommerce'); ?></h5>
        <?php if (!empty($currency_types)) { ?>
            <div class="d-flex" style="gap: 8px;">
                <div class="select-box-with-icon position-relative d-flex">
                    <i class="cuw-icon-campaigns px-1"></i>
                    <select id="campaign" class="form-control">
                        <option value="all" <?php if (empty($campaign_id)) echo 'selected'; ?>>
                            <?php esc_html_e("All campaigns", 'checkout-upsell-woocommerce'); ?>
                        </option>
                        <optgroup label="<?php esc_attr_e("Campaign type", 'checkout-upsell-woocommerce'); ?>">
                            <?php foreach ($campaign_types as $key => $title) {
                                echo '<option value="' . esc_attr($key) . '">' . esc_html($title) . '</option>';
                            } ?>
                        </optgroup>
                        <optgroup label="<?php esc_attr_e("Campaign", 'checkout-upsell-woocommerce'); ?>">
                            <?php foreach ($campaigns as $campaign) {
                                $selected = !empty($campaign_id) && $campaign_id == $campaign['id'] ? 'selected' : '';
                                echo '<option value="' . esc_attr($campaign['id']) . '" ' . esc_attr($selected) . '>' . esc_html($campaign['title']) . '</option>';
                            } ?>
                        </optgroup>
                    </select>
                </div>
                <div class="d-flex" style="gap:8px;">
                    <div class="select-box-with-icon position-relative d-flex">
                        <i class="cuw-icon-calendar px-1"></i>
                        <select id="range" class="form-control">
                            <option value="this_week"><?php esc_html_e("This week", 'checkout-upsell-woocommerce'); ?></option>
                            <option value="last_week"><?php esc_html_e("Last week", 'checkout-upsell-woocommerce'); ?></option>
                            <option value="this_month"
                                    selected><?php esc_html_e("This month", 'checkout-upsell-woocommerce'); ?></option>
                            <option value="last_month"><?php esc_html_e("Last month", 'checkout-upsell-woocommerce'); ?></option>
                            <option value="custom"><?php esc_html_e("Custom", 'checkout-upsell-woocommerce'); ?></option>
                        </select>
                    </div>
                    <div id="custom-range" style="display: none;">
                        <div class="d-flex" style="gap: 6px;">
                            <input class="form-control" type="date" id="date-from" min="2022-01-01"
                                   max="<?php echo esc_attr(current_time('Y-m-d')); ?>"
                                   title="<?php esc_html_e("From", 'checkout-upsell-woocommerce'); ?>">
                            <input class="form-control" type="date" id="date-to" min="2022-01-01"
                                   max="<?php echo esc_attr(current_time('Y-m-d')); ?>"
                                   title="<?php esc_html_e("To", 'checkout-upsell-woocommerce'); ?>">
                        </div>
                    </div>
                </div>
                <div>
                    <div class="select-box-with-icon position-relative d-flex">
                        <i class="cuw-icon-fbt px-1"></i>
                        <select id="currency" class="form-control">
                            <?php foreach ($currency_types as $currency_type) {
                                echo '<option value="' . esc_attr($currency_type) . '">' . esc_html($currency_type) . '</option>';
                            } ?>
                        </select>
                    </div>
                </div>
                <div class="select-box-with-icon position-relative d-flex">
                    <i class="cuw-icon-currency text-light-gray"></i>
                    <select id="revenue-type" class="form-control">
                        <option value="<?php esc_attr_e('without_tax', 'checkout-upsell-woocommerce') ?>" <?php if ($revenue_tax_display == 'without_tax') echo 'selected'; ?>><?php esc_html_e('Excluding tax', 'checkout-upsell-woocommerce') ?></option>
                        <option value="<?php esc_attr_e('with_tax', 'checkout-upsell-woocommerce') ?>" <?php if ($revenue_tax_display == 'with_tax') echo 'selected'; ?> ><?php esc_html_e('Including tax', 'checkout-upsell-woocommerce') ?></option>
                    </select>
                </div>
            </div>
        <?php } ?>
    </div>
    <div class="row px-3 mx-1">
        <div class="col-md-6">
            <div class="row">
                <div class="col-md-6">
                    <div class="card cuw-card ">
                        <div class="d-flex-between">
                            <p class="font-weight-medium"><?php esc_html_e("Upsell Revenue", 'checkout-upsell-woocommerce'); ?></p>
                            <div class="img-icon cuw-icon-container"
                                 style="background: #D8E5FE; padding: 8px; border-radius: 50%;">
                                <i class="cuw-icon-total-revenue"></i>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <h2 id="revenue" class="font-weight-bold d-block" style="margin-bottom: 12px;">
                                <span class="spinner-border spinner-border-sm text-secondary font-base"></span>
                            </h2>
                            <div id="revenue-diff"
                                 class="difference-wrapper font-base font-weight-medium text-secondary align-items-center"
                                 style="display: none;">
                                <span class="difference d-inline-flex align-items-center mx-1">
                                    <i class="arrow-up cuw-icon-arrow-up text-success" style="display: none;"></i>
                                    <i class="arrow-down cuw-icon-arrow-down text-danger" style="display: none;"></i>
                                    <span class="percentage"></span>
                                </span>
                                <span class="since-text"></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card cuw-card ">
                        <div class="d-flex-between">
                            <p class="font-weight-medium mb-2"><?php esc_html_e("Products Purchased", 'checkout-upsell-woocommerce'); ?></p>
                            <div class="img-icon cuw-icon-container"
                                 style="background: #D8E5FE; padding: 8px; border-radius: 50%;">
                                <i class="cuw-icon-purchased text-primary"></i>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <h2 id="items" class="font-weight-bold d-block" style="margin-bottom: 12px;">
                                <span class="spinner-border spinner-border-sm text-secondary font-base"></span>
                            </h2>
                            <div id="items-diff"
                                 class="difference-wrapper font-base font-weight-medium text-secondary align-items-center"
                                 style="display: none;">
                                <span class="difference d-inline-flex align-items-center mx-1">
                                    <i class="arrow-up cuw-icon-arrow-up text-success" style="display: none;"></i>
                                    <i class="arrow-down cuw-icon-arrow-down text-danger" style="display: none;"></i>
                                    <span class="percentage"></span>
                                </span>
                                <span class="since-text"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="card cuw-card ">
                        <div class="d-flex-between">
                            <p class="font-weight-medium mb-2"><?php esc_html_e("Campaigns Created", 'checkout-upsell-woocommerce'); ?></p>
                            <div class="img-icon cuw-icon-container"
                                 style="background: #D8E5FE; padding: 8px; border-radius: 50%;">
                                <i class="cuw-icon-campaigns-created"></i>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <h2 id="campaigns" class="font-weight-bold d-block" style="margin-bottom: 12px;">
                                <span class="spinner-border spinner-border-sm text-secondary font-base"></span>
                            </h2>
                            <div id="campaigns-diff"
                                 class="difference-wrapper font-base font-weight-medium text-secondary align-items-center"
                                 style="display: none;">
                                <span class="difference d-inline-flex align-items-center mx-1">
                                    <i class="arrow-up cuw-icon-arrow-up text-success" style="display: none;"></i>
                                    <i class="arrow-down cuw-icon-arrow-down text-danger" style="display: none;"></i>
                                    <span class="percentage"></span>
                                </span>
                                <span class="since-text"></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card cuw-card ">
                        <div class="d-flex-between">
                            <p class="font-weight-medium mb-2"><?php esc_html_e("Offers Created", 'checkout-upsell-woocommerce'); ?></p>
                            <div class="img-icon cuw-icon-container"
                                 style="background: #D8E5FE; padding: 8px; border-radius: 50%;">
                                <i class="cuw-icon-offers-created"></i>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <h2 id="offers" class="font-weight-bold d-block" style="margin-bottom: 12px;">
                                <span class="spinner-border spinner-border-sm text-secondary font-base"></span>
                            </h2>
                            <div id="offers-diff"
                                 class="difference-wrapper font-base font-weight-medium text-secondary align-items-center"
                                 style="display: none;">
                                <span class="difference d-inline-flex align-items-center mx-1">
                                    <i class="arrow-up cuw-icon-arrow-up text-success" style="display: none;"></i>
                                    <i class="arrow-down cuw-icon-arrow-down text-danger" style="display: none;"></i>
                                    <span class="percentage"></span>
                                </span>
                                <span class="since-text"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 chart-section" style="margin-top: 20px;">
            <div id="default-text"
                 style="display:flex; justify-content:center; height:180px; font-size: 1rem; align-items: center;">
                <span class="spinner-border spinner-border-sm text-secondary font-base"></span>
            </div>
            <canvas id="reports-chart" class="w-100 mt-2"></canvas>
        </div>
    </div>
</div>
