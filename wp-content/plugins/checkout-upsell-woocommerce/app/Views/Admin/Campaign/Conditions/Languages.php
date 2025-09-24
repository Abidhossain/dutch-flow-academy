<?php
defined('ABSPATH') || exit;

$languages = \CUW\App\Helpers\WPML::getActiveLanguages();
$key = isset($key) ? (int)$key : '{key}';
$value = isset($condition['value']) && !empty($condition['value']) ? $condition['value'] : '';
?>

<div class="condition-value">
    <select class="select2-local optional trigger-change" name="conditions[<?php echo esc_attr($key); ?>][value]"
            data-placeholder=" <?php esc_html_e("Choose language", 'checkout-upsell-woocommerce'); ?>">
        <?php foreach ($languages as $slug => $language) { ?>
            <option value="<?php echo esc_attr($slug); ?>" <?php if ($slug == $value) echo "selected"; ?>><?php echo esc_html($language['translated_name']); ?></option>
        <?php } ?>
    </select>
</div>
