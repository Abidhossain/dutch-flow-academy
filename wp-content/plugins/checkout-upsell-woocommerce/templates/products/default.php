<?php
/**
 * Product loop default template
 *
 * This template can be overridden by copying it to yourtheme/checkout-upsell-woocommerce/products/default.php.
 *
 * HOWEVER, on occasion we will need to update template files and you (the theme developer) will need to copy the new files
 * to your theme to maintain compatibility. We try to do this as little as possible, but it does happen.
 */

defined('ABSPATH') || exit;
if (!isset($data) || !isset($product_ids) || !isset($campaign) || !function_exists('wc_get_loop_prop') || !function_exists('wc_set_loop_prop')) {
    return;
}

$columns = $data['columns'] ?? '';
$campaign_type = !empty($campaign['type']) ? $campaign['type'] : '';
$campaign_id = !empty($campaign['id']) ? $campaign['id'] : '';
$heading = $data['template']['title'] ?? '';
$heading = apply_filters('cuw_products_template_heading', $heading, $campaign);
$class = $data['template']['class'] ?? '';
$class = apply_filters('cuw_products_template_class', $class, $campaign);
?>

<div class="cuw-products <?php echo esc_attr($class); ?> cuw-<?php echo esc_attr(str_replace('_', '-', $campaign_type)); ?>-template"
     data-campaign_id="<?php echo esc_attr($campaign['id']); ?>"
     style="margin: 16px 0;">
    <?php
    if (!empty($product_ids)) {
        if (!empty($heading)) { ?>
            <h2 class="cuw-heading cuw-template-title" style="margin-bottom: 16px;">
                <?php echo wp_kses_post($heading); ?>
            </h2>
        <?php }

        $default_loop_columns = wc_get_loop_prop('columns');
        wc_set_loop_prop('columns', !empty($columns) ? $columns : $default_loop_columns);

        woocommerce_product_loop_start();
        do_action('cuw_before_products_loop', $campaign_id, $campaign_type);

        foreach ($product_ids as $product_id) {
            $product_data = get_post($product_id);
            if ($product_data) {
                setup_postdata($GLOBALS['post'] = &$product_data);
                wc_get_template_part('content', 'product');
                wp_reset_postdata();
            }
        }

        do_action('cuw_after_products_loop', $campaign_id, $campaign_type);
        woocommerce_product_loop_end();

        wc_set_loop_prop('columns', $default_loop_columns);
    }
    ?>
</div>