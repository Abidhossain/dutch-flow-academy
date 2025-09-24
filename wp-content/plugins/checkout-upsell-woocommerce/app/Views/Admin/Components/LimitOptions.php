<?php
defined('ABSPATH') || exit;
if (!isset($selected_limit)) {
    return;
}
?>

<?php for ($limit = ($from ?? 1); $limit <= ($to ?? 12); $limit++) { ?>
    <option value="<?php echo esc_attr($limit); ?>" <?php if ($selected_limit == $limit) echo 'selected'; ?>>
        <?php echo esc_html($limit); ?>
    </option>
<?php } ?>