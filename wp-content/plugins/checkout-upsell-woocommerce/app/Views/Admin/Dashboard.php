<?php
defined('ABSPATH') || exit;

$currency_codes = \CUW\App\Models\Stats::getAvailableCurrencies();
$store_currency = \CUW\App\Helpers\WC::getCurrency();
$revenue_tax_display = CUW()->config->get('revenue_tax_display', 'without_tax');
?>

<div id="cuw-dashboard" data-tab="dashboard">
    <div class="d-flex  title-container align-items-center">
        <h5><?php esc_html_e("Dashboard", 'checkout-upsell-woocommerce'); ?></h5>
    </div>

    <div class="d-flex justify-content-between align-items-center p-3" style="padding-bottom: 0 !important; gap: 8px;">
        <?php if (!empty($currency_codes)) { ?>
            <div class="btn-group d-flex" style="gap:8px;" id="range">
                <label class="btn">
                    <input type="radio" name="range" value="last_7_days" class="d-none"> <span
                            class="mx-1"><?php esc_html_e("Last 7 days", 'checkout-upsell-woocommerce'); ?></span>
                </label>
                <label class="btn btn-primary">
                    <input type="radio" name="range" value="last_30_days" class="d-none" checked><span
                            class="mx-1"><?php esc_html_e("Last 30 days", 'checkout-upsell-woocommerce'); ?></span>
                </label>
                <label class="btn">
                    <input type="radio" name="range" value="this_week" class="d-none"><span
                            class="mx-1"> <?php esc_html_e("This week", 'checkout-upsell-woocommerce'); ?></span>
                </label>
                <label class="btn">
                    <input type="radio" name="range" value="last_week" class="d-none" <span
                            class="mx-1"><?php esc_html_e("Last week", 'checkout-upsell-woocommerce'); ?></span>
                </label>
                <label class="btn">
                    <input type="radio" name="range" value="this_month" class="d-none"><span
                            class="mx-1"><?php esc_html_e(" This month", 'checkout-upsell-woocommerce'); ?></span>
                </label>
                <label class="btn">
                    <input type="radio" name="range" value="last_month" class="d-none"><span
                            class="mx-1"><?php esc_html_e("Last month", 'checkout-upsell-woocommerce'); ?></span>
                </label>
            </div>
            <div class="d-flex align-items-center" style="gap: 8px">
                <div class="select-box-with-icon position-relative d-flex">
                    <i class="cuw-icon-fbt pr-1"></i>
                    <select id="currency" class="form-control">
                        <?php foreach ($currency_codes as $currency_code) { ?>
                            <option value="<?php echo esc_attr($currency_code) ?>" <?php if ($store_currency == $currency_code) echo "selected"; ?> ><?php echo esc_html($currency_code) ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="select-box-with-icon position-relative d-flex">
                    <i class="cuw-icon-currency text-light-gray"></i>
                    <select id="revenue-type" class="form-control">
                        <option value="<?php esc_attr_e('without_tax', 'checkout-upsell-woocommerce') ?>"
                                selected <?php if ($revenue_tax_display == 'without_tax') echo 'selected'; ?>><?php esc_html_e('Excluding tax', 'checkout-upsell-woocommerce') ?></option>
                        <option value="<?php esc_attr_e('with_tax', 'checkout-upsell-woocommerce') ?>" <?php if ($revenue_tax_display == 'with_tax') echo 'selected'; ?> ><?php esc_html_e('Including tax', 'checkout-upsell-woocommerce') ?></option>
                    </select>
                </div>
            </div>
        <?php } ?>
    </div>

    <div class="row px-3">
        <div class="col-md-3 col-sm-6">
            <div class="card bg-white cuw-card">
                <div class="header d-flex-between">
                    <p class="font-weight-medium mb-2"><?php esc_html_e("Upsell Revenue", 'checkout-upsell-woocommerce'); ?></p>
                    <div class="cuw-icon-container"><i class="cuw-icon-money text-primary"></i></div>
                </div>
                <div class="card-body p-0">
                    <div class="d-flex flex-column" style="gap:12px;">
                        <h4 id="revenue" class="font-weight-bold d-block m-0">
                            <span class="spinner-border spinner-border-sm text-secondary font-base"></span>
                        </h4>
                        <div id="revenue-diff"
                             class="difference-wrapper font-base font-weight-medium text-secondary align-items-center"
                             style="display: none;">
                            <span class="difference d-inline-flex align-items-center">
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
        <div class="col-md-3 col-sm-6 ">
            <div class="card cuw-card bg-white">
                <div class="header d-flex-between">
                    <p class="font-weight-medium mb-2"><?php esc_html_e("Conversion", 'checkout-upsell-woocommerce'); ?></p>
                    <div class="cuw-icon-container"><i class="cuw-icon-conversion text-primary"></i></div>
                </div>
                <div class="card-body p-0">
                    <div class="d-flex flex-column" style="gap:12px;">
                        <h4 id="conversion_percentage" class="font-weight-bold d-block m-0">
                            <span class="spinner-border spinner-border-sm text-secondary font-base"></span>
                        </h4>
                        <div id="conversion_percentage-diff"
                             class="difference-wrapper font-base font-weight-medium text-secondary align-items-center"
                             style="display: none;">
                            <span class="difference d-inline-flex align-items-center">
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
        <div class="col-md-3 col-sm-6">
            <div class="card bg-white cuw-card">
                <div class="header d-flex-between">
                    <p class="font-weight-medium mb-2"><?php esc_html_e("Upsell Orders", 'checkout-upsell-woocommerce'); ?></p>
                    <div class="cuw-icon-container"><i class="cuw-icon-upsell-revenue text-primary"></i></div>
                </div>
                <div class="card-body p-0">
                    <div class="d-flex flex-column" style="gap:12px;">
                        <h4 id="orders" class="font-weight-bold d-block m-0">
                            <span class="spinner-border spinner-border-sm text-secondary font-base"></span>
                        </h4>
                        <div id="orders-diff"
                             class="difference-wrapper font-base font-weight-medium text-secondary align-items-center"
                             style="display: none;">
                            <span class="difference d-inline-flex align-items-center">
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
        <div class="col-md-3 col-sm-6">
            <div class="card  bg-white cuw-card">
                <div class="header d-flex-between">
                    <p class="font-weight-medium mb-2"><?php esc_html_e("Products Purchased", 'checkout-upsell-woocommerce'); ?></p>
                    <div class="cuw-icon-container">
                        <i class="cuw-icon-purchased text-primary"></i>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="d-flex flex-column" style="gap:12px;">
                        <h4 id="items" class="font-weight-bold d-block m-0">
                            <span class="spinner-border spinner-border-sm text-secondary font-base"></span>
                        </h4>
                        <div id="items-diff"
                             class="difference-wrapper font-base font-weight-medium text-secondary align-items-center"
                             style="display: none;">
                            <span class="difference d-inline-flex align-items-center">
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

    <div class="row px-3">
        <div class="col-md-6 pb-2">
            <div class="p-3 mt-4 mb-2 bg-white chart-section">
                <h5 class=" text-dark" style="font-size: 16px;"><?php esc_html_e("Upsell Revenue", 'checkout-upsell-woocommerce'); ?></h5>
                <canvas id="upsell-revenue-chart" class="mt-2" height="75"></canvas>
                <div id="default-text"
                     style="display: flex; justify-content:center; height:100px; font-size: 1rem; align-items: start">
                    <span class="spinner-border spinner-border-sm text-secondary font-base"></span>
                </div>
            </div>
            <div class="p-3 mt-4 mb-2 bg-white chart-section">
                <h5 class="text-dark" style="font-size: 16px;"><?php esc_html_e("Products Purchased", 'checkout-upsell-woocommerce'); ?></h5>
                <canvas id="products-purchased-chart" class="mt-2" height="75"></canvas>
                <div id="default-text"
                     style="display: flex; justify-content:center; height:100px; font-size: 1rem; align-items: start">
                    <span class="spinner-border spinner-border-sm text-secondary font-base"></span>
                </div>
            </div>
        </div>

        <div class="col-md-6 pb-2">
            <div class="p-3 mt-4 bg-white chart-section">
                <h5 class="text-dark" style="font-size: 16px;"><?php esc_html_e("Campaigns Revenue", 'checkout-upsell-woocommerce'); ?></h5>
                <canvas id="campaign-revenue-chart" class="mt-1" height="150"></canvas>
                <div id="default-text"
                     style="display:flex; justify-content:center; height:150px; font-size: 1rem; align-items: start;">
                    <span class="spinner-border spinner-border-sm text-secondary font-base"></span>
                </div>
            </div>
        </div>
    </div>
</div>
