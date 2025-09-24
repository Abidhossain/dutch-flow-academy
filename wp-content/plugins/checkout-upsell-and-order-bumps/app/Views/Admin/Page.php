<?php
defined('ABSPATH') || exit;
if (!isset($page)) {
    return;
}
$rtl = CUW()->wp->isRtl();

$tabs = $page->getTabs();
$current_tab = $page->getCurrentTab();
$page_url = $page->getUrl();

$is_campaign_tab = $current_tab == 'campaigns' && (isset($_GET['create']) || isset($_GET['edit'])); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$is_engine_tab = $current_tab == 'engines' && (isset($_GET['create']) || isset($_GET['edit'])); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

$help_options = [
    'documentation' => [
        'title' => esc_html__("Documentation", 'checkout-upsell-and-order-bumps'),
        'url' => 'https://docs.upsellwp.com',
    ],
    'video_tutorials' => [
        'title' => esc_html__("Video tutorials", 'checkout-upsell-and-order-bumps'),
        'url' => 'https://www.youtube.com/@UpsellWP/videos',
    ],
    'feature_request' => [
        'title' => esc_html__("Request a feature", 'checkout-upsell-and-order-bumps'),
        'url' => 'https://features.upsellwp.net',
    ],
    'support' => [
        'title' => esc_html__("Support", 'checkout-upsell-and-order-bumps'),
        'url' => CUW()->plugin->getSupportUrl(),
    ],
];
if (!CUW()->plugin->has_pro) {
    $help_options['upgrade'] = [
        'title' => esc_html__("Upgrade to PRO", 'checkout-upsell-and-order-bumps'),
        'url' => CUW()->plugin->getUrl('upgrade'),
    ];
}
?>

<div id="cuw-page" class="mt-3 p-5 <?php echo $rtl ? 'cuw-rtl' : ''; ?> cuw-bs4">

    <?php do_action('cuw_before_page', $page, $current_tab); ?>

    <div id="notify"></div>
    <div id="overlay" style="display: none;"></div>

    <nav class="navbar navbar-expand-lg navbar-light bg-white px-3 py-0"
         style="<?php echo ($is_campaign_tab || $is_engine_tab) ? 'display: none;' : ''; ?>">
        <div class="d-flex align-items-center" style="gap:6px">
            <h2 class="navbar-brand plugin-title font-weight-bold"
                href="#"> <?php esc_html_e('UpsellWP', 'checkout-upsell-and-order-bumps'); ?></h2>
            <span style="font-size: 14px; line-height: 1.2;"
                  class="<?php echo CUW()->plugin->has_pro ? 'badge-pill-green-primary' : 'badge-pill-blue-primary'; ?> font-weight-medium px-2 py-1">
                <?php echo CUW()->plugin->has_pro
                    ? esc_html__('PRO', 'checkout-upsell-and-order-bumps')
                    : esc_html__('Lite', 'checkout-upsell-and-order-bumps');
                ?>
            </span>
        </div>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavDropdown"
                aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse cuw-top-navbar-container px-3 navbar-collapse" id="navbarNavDropdown">
            <ul class="nav navbar-nav">
                <?php foreach ($tabs as $tab => $title) {
                    if ($tab == 'buy-pro' && CUW()->plugin->has_pro) {
                        continue;
                    }
                    $is_active = ($tab == $current_tab);
                    $nav_item_class = "p-4 cuw-nav-item-text text-decoration-none d-flex align-items-center" . ($is_active ? ' active' : '');
                    ?>
                    <li class="nav-item">
                        <a class="<?php echo esc_attr($nav_item_class); ?>"
                           href="<?php echo esc_url($page_url . '&tab=' . $tab); ?>">
                            <i class="mx-1 cuw-icon-<?php echo esc_attr($tab); ?>" style="font-size: 18px;"></i>
                            <?php echo esc_html($title); ?>

                            <?php if ($tab == 'engines' && !CUW()->plugin->has_pro) { ?>
                                <small class="badge badge-success font-weight-medium"
                                       style="line-height:1; padding: 3px 4px; margin:2px 2px 0 2px; background: #fff;">
                                    <?php esc_html_e('PRO', 'checkout-upsell-and-order-bumps'); ?>
                                </small>
                            <?php } ?>
                        </a>
                    </li>
                <?php } ?>
            </ul>
            <ul class="navbar-nav plugin-version-container flex-row align-items-center ml-auto">
                <li class="nav-item p-2" id="help-panel-toggle">
                    <i class="cuw-icon-help"></i>
                </li>
                <li style="background-color: #F2F4F7; max-width: 80px;"
                    class="nav-item rounded p-2 text-custom-gray-extra-light cuw-version">
                    <?php echo 'v' . esc_html(CUW()->plugin->version); ?>
                </li>
            </ul>
        </div>
        <ul class="navbar-nav plugin-version-outer-container align-items-center" style="gap: 8px;">
            <li class="nav-item p-2" id="help-panel-toggle">
                <i class="cuw-icon-help"></i>
            </li>
            <li style="background-color: #F2F4F7; max-width: 80px;"
                class="nav-item rounded p-2 text-custom-gray-extra-light cuw-version">
                <?php echo 'v' . esc_html(CUW()->plugin->version); ?>
            </li>
        </ul>
    </nav>

    <div class="help-panel border border-gray-extra-light rounded-lg" id="help-panel">
        <div class="d-flex justify-content-between align-items-center p-3 border-bottom ">
            <h6 class="mb-0"><?php esc_html_e("Help", 'checkout-upsell-and-order-bumps'); ?></h6>
            <button id="help-panel-close"><i class="cuw-icon-close-circle"></i></button>
        </div>
        <div class="help-actions p-3 d-flex flex-column">
            <?php foreach ($help_options as $key => $link) { ?>
                <div style="display:flex; align-items: center;" class="help-menu">
                    <a class="help-link d-flex align-items-center" target="_blank" style="gap: 4px;"
                       href="<?php echo esc_url($link['url']); ?>"
                       title="<?php esc_html_e($link['title'], 'checkout-upsell-and-order-bumps'); //phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText ?>">
                        <?php esc_html_e($link['title'], 'checkout-upsell-and-order-bumps'); //phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText ?>
                        <i class="cuw-icon-external-link inherit-color" style="font-size: 16px;"></i>
                    </a>
                </div>
            <?php } ?>
        </div>
    </div>

    <div id="content" class="card m-3 p-0 mw-100 border-0">
        <div class="container-fluid  p-0">
            <?php
            if ($current_tab == 'dashboard') {
                CUW()->view('Admin/Dashboard');
            } elseif ($current_tab == 'campaigns' && !$is_campaign_tab) {
                CUW()->view('Admin/Campaigns/Main');
            } elseif ($current_tab == 'campaigns' && $is_campaign_tab) {
                CUW()->view('Admin/Campaign/Main');
            } elseif ($current_tab == 'engines' && !$is_engine_tab) {
                CUW()->view('Pro/Admin/Engines/Main', ['page' => $page]);
            } elseif ($current_tab == 'engines' && $is_engine_tab) {
                CUW()->view('Pro/Admin/Engine/Main', ['page' => $page]);
            } elseif ($current_tab == 'reports') {
                CUW()->view('Admin/Reports');
            } elseif ($current_tab == 'settings') {
                CUW()->view('Admin/Settings');
            } elseif ($current_tab == 'addons') {
                CUW()->view('Admin/AddOns');
            } elseif ($current_tab == 'recommendations') {
                CUW()->view('Admin/Recommendations');
            } elseif ($current_tab == 'buy-pro' && !CUW()->plugin->has_pro) {
                CUW()->view('Admin/BuyPRO');
            }

            if ($current_tab == 'engines' && !CUW()->plugin->has_pro) {
                ?>
                <div class="d-flex flex-wrap title-container align-items-center justify-content-between">
                    <h5><?php esc_html_e("Recommendation Engines", 'checkout-upsell-and-order-bumps'); ?></h5>
                    <div></div>
                </div>
                <div style="padding: 64px 16px;">
                    <?php CUW()->view('Admin/Components/Upgrade'); ?>
                </div>
            <?php } ?>
        </div>
    </div>
</div>
