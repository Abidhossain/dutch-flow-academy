<?php
defined('ABSPATH') || exit;
if (!isset($id)) {
    return;
}
?>

<div class="cuw-slider" style="padding-top:0;" id="<?php echo esc_attr($id) ?>-slider">
    <div class="cuw-slider-content cuw-animate-fade" style="width:<?php echo esc_attr($width ?? ''); ?>">
        <div class="cuw-slider-body px-3" style="overflow-x: auto; overflow-y: auto;height:100%;">
            <?php
            if (isset($view)) {
                $data = $data ?? [];
                CUW()->view($view, $data);
            } else if (isset($body)) {
                echo $body; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            }
            ?>
        </div>
    </div>
</div>

