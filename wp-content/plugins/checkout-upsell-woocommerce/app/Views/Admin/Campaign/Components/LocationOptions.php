<?php
defined('ABSPATH') || exit;
if (!isset($locations) || !isset($selected_location)) {
    return;
}
?>

<?php
$has_pro = CUW()->plugin->hasPro();
$pro_locations = ['woocommerce_cart_contents'];
foreach ($locations as $location => $name) {
    $is_pro_location = !$has_pro && in_array($location, $pro_locations); ?>
    <option value="<?php echo esc_attr($location); ?>" <?php if ($selected_location == $location) echo "selected"; ?> <?php if ($is_pro_location) echo 'disabled'; ?>>
        <?php echo esc_html($name); ?><?php if ($is_pro_location) echo esc_html(' – ' . __("PRO", 'checkout-upsell-woocommerce')); ?>
    </option>
<?php }
