<?php
defined('ABSPATH') || exit;
if (!isset($campaign)) {
    return;
}

$engine = [];
$page = !empty($campaign['data']['page']) ? $campaign['data']['page'] : '';
$page_type = CUW()->input->get('page_type');
$page = \CUW\App\Pro\Helpers\Page::get($page_type);
$engine_type = !empty($page['engine_type']) ? $page['engine_type'] : '';
if (isset($campaign['id']) && $campaign['id'] == 0) {
    $engine = [
        'id' => 0,
        'type' => $engine_type,
        'title' => '',
        'enabled' => 1,
        'filters' => [],
        'amplifiers' => [floor(microtime(true) * 1000) => ['type' => 'default']],
    ];
} else {
    $engine_id = $campaign['data']['engine_id'] ?? '';
    $page_type = $campaign['data']['page'] ?? '';
    if (!empty($engine_id)) {
        $engine = \CUW\App\Pro\Models\Engine::get($engine_id);
    } else {
        echo '<p class="text-center mt-5">Engine not found for this campaign!</p>';
        return;
    }
    $engine_type = $engine['type'] ?? '';
}
$display_locations = \CUW\App\Pro\Helpers\Page::getLocations($page_type);
?>

    <input type="hidden" name="engine_id" value="<?php echo esc_attr($engine['id']); ?>">
    <div id="page-accordion">
        <?php
        CUW()->view('Admin/Components/Accordion', [
            'id' => 'product_recommendations_page',
            'title' => __('Display Settings', 'checkout-upsell-woocommerce'),
            'icon' => 'desktop',
            'view' => 'Pro/Admin/Campaign/Components/Page',
            'data' => [
                'campaign' => $campaign,
                'display_locations' => !empty($display_locations) && !empty($page_type) ? $display_locations : '',
                'display_location_text' => __('Display location', 'checkout-upsell-woocommerce'),
            ],
        ]);
        ?>
    </div>

<?php if (!empty($engine_type)) { ?>
    <div id="filter-accordion">
        <?php
        CUW()->view('Admin/Components/Accordion', [
            'id' => 'product_recommendations_filter',
            'title' => __('Filters & Conditions', 'checkout-upsell-woocommerce'),
            'icon' => 'filter',
            'body' => CUW()->view('Pro/Admin/Engine/Components/Filters', ['engine' => $engine], false),
            'expand' => $campaign['id'] != 0,
        ]);
        ?>
    </div>
    <div id="amplifier-accordion">
        <?php
        CUW()->view('Admin/Components/Accordion', [
            'id' => 'product_recommendations_amplifiers',
            'title' => __('Sorting Options', 'checkout-upsell-woocommerce'),
            'icon' => 'sorting',
            'body' => CUW()->view('Pro/Admin/Engine/Components/Amplifiers', ['engine' => $engine], false),
            'expand' => $campaign['id'] != 0,
        ]);
        ?>
    </div>
    <div id="template-accordion">
        <?php
        CUW()->view('Admin/Components/Accordion', [
            'id' => 'template',
            'title' => __('Template', 'checkout-upsell-woocommerce'),
            'icon' => 'campaigns',
            'body' => CUW()->view('Admin/Campaign/Components/Template', ['campaign' => $campaign], false),
            'expand' => $campaign['id'] != 0,
        ]);
        ?>
    </div>
<?php } ?>