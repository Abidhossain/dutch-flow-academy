<?php defined('ABSPATH') || exit ?>

<?php
$key = isset($key) ? (int)$key : '{key}';
$type = isset($type) ? $type : '{type}';
$name = isset($name) ? $name : '{name}';
?>

<div class="cuw-engine-amplifier" data-id="<?php echo esc_attr($key); ?>"
     data-type="<?php echo esc_attr($type); ?>">
    <div class="amplifier-inputs">
        <div class="amplifier-name d-none"><?php echo esc_html($name); ?></div>
        <div class="d-flex flex-column" style="gap: 8px;">
            <div class="amplifier-data w-100 d-flex flex-row" style="gap: 8px;">
                <input type="hidden" name="engine_amplifiers[<?php echo esc_attr($key); ?>][type]"
                       value="<?php echo esc_attr($type); ?>">
                <?php if (is_numeric($key) && !empty($amplifier) && !empty($amplifiers) && !empty($amplifiers[$type]['handler'])) {
                    $amplifiers[$type]['handler']->template(['key' => $key, 'amplifier' => $amplifier], true);
                } else {
                    echo '{data}';
                } ?>
            </div>
        </div>
    </div>
</div>
