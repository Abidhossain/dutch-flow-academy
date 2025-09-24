<?php
defined('ABSPATH') || exit;
if (!isset($id) || !isset($title) || !isset($icon)) {
    return;
}

$expand = $expand ?? true;
?>

<div id="accordion" class="accordion p-0 border mb-3 rounded-lg">
    <div class="w-100">
        <div class="accordion-head d-flex align-items-center justify-content-between border-bottom"
             data-toggle="collapse" href="<?php echo '#cuw_' . esc_attr($id); ?>">
            <div class="d-flex align-items-center" style="gap:6px;">
                <i class="cuw-icon-<?php echo esc_attr($icon); ?> text-primary"></i>
                <h6 style="line-height: 0"><?php echo esc_html($title); ?></h6>
            </div>
            <div class="navigator">
                <i class="cuw-icon-accordion-<?php echo !empty($expand) ? 'open' : 'close'; ?> text-dark"
                   style="font-size: 24px;"></i>
            </div>
        </div>
        <div id="<?php echo 'cuw_' . esc_attr($id); ?>" class="collapse <?php echo !empty($expand) ? 'show' : ''; ?>">
            <div class="card-body">
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
</div>