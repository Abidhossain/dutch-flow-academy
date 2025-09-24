<?php
defined('ABSPATH') || exit;
if (!isset($notices)) {
    return;
}
?>

<?php foreach ($notices as $notice) {
    $status = !empty($notice['status']) ? $notice['status'] : 'info';
    if ($status == 'error') {
        $status = 'danger';
    }
    $icon = 'dashicons dashicons-info';
    if ($status == 'success') {
        $icon = 'dashicons dashicons-yes-alt';
    } elseif ($status == 'warning') {
        $icon = 'dashicons dashicons-warning';
    } elseif ($status == 'danger') {
        $icon = 'dashicons dashicons-dismiss';
    }
    ?>
    <div class="alert alert-<?php echo esc_attr($status); ?> d-flex align-items-center">
        <span class="<?php echo esc_attr($icon); ?> mr-2"></span>
        <div>
            <?php if (!empty($notice['heading'])) {
                echo '<strong>' . esc_html($notice['heading']) . '</strong><br>';
            } ?>
            <?php echo wp_kses_post($notice['message']); ?>
        </div>
    </div>
<?php } ?>
