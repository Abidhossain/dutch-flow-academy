<?php
defined('ABSPATH') || exit;

$key = isset($key) ? (int)$key : '{key}';
$type = $type ?? '{type}';
$name = $name ?? '{name}';
$relation = $relation ?? 'and';
$count = $count ?? '';
?>

<div class="cuw-condition mt-3" data-id="<?php echo esc_attr($key); ?>">
    <div class="condition-inputs" style="display: none;">
        <div class="condition-name text-secondary font-weight-medium mb-1"><?php echo esc_html($name); ?></div>
        <div class="d-flex flex-column" style="gap: 8px;">
            <div class="condition-data w-100 d-flex flex-column" style="gap: 12px;">
                <input type="hidden" name="conditions[<?php echo esc_attr($key); ?>][type]"
                       value="<?php echo esc_attr($type); ?>">
                <?php if (is_numeric($key) && !empty($condition) && !empty($conditions) && !empty($conditions[$type]['handler'])) {
                    $conditions[$type]['handler']->template(['key' => $key, 'condition' => $condition], true);
                } else {
                    echo '{data}';
                } ?>
            </div>
        </div>
    </div>

    <span class="condition-relation-wrapper" style="display:flex; gap:4px; align-items: center">
        <span class="cuw-relation condition-relation relation-<?php echo esc_attr($relation); ?>"><?php echo !empty($show_relation) ? esc_html($relation) : '' ?></span>
        <div class="condition-count text-uppercase"><?php echo esc_html__('Condition', 'checkout-upsell-and-order-bumps') . ' ' . esc_html(!empty($count) ? $count : ''); ?></div>
    </span>

    <div class="condition-row">
        <div class="d-flex align-items-center justify-content-between" style="gap:8px;">
            <div class="condition-text-wrapper">
                <i class="cuw-icon-box cuw-condition-icon text-dark"></i>
                <div class="condition-text"><span class="spinner-border spinner-border-sm"></span></div>
            </div>

            <div class="d-flex" style="gap:8px;">
                <div class="condition-edit">
                    <i class="cuw-icon-edit-note" title="Edit"></i>
                </div>
                <div class="condition-remove">
                    <i class="cuw-icon-delete inherit-color" title="Remove"></i>
                </div>
            </div>
        </div>
    </div>
</div>