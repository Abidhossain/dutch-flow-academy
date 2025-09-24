<?php
defined('ABSPATH') || exit;
if (!isset($campaign)) {
    return;
}

$campaign_type = $campaign['type'] ?? '';
$conditions = $campaign['conditions'] ?? [];
$condition_relation = $campaign['conditions']['relation'] ?? ''
?>
<div>
    <div id="conditions-match" class="align-items-center text-dark font-weight-medium mb-2" style="display: <?php echo empty($conditions) ? 'none' : 'flex'; ?>; justify-content: space-between; ">
        <div style="display: flex; align-items: center; gap:4px;">
            <?php $conditions_relation = isset($conditions['relation']) && $conditions['relation'] == 'or' ? "or" : "and"; ?>
                <?php echo sprintf("%s of the following conditions",
                    '<span class="filter-match-radio d-inline-block"><span class="custom-control custom-radio custom-control-inline">
                            <input type="radio" class="custom-control-input" id="conditions-match-all" name="conditions[relation]" value="and"' . ($conditions_relation == "and" ? ' checked' : '') . (empty($conditions) ? ' disabled' : '') . '>
                            <label class="custom-control-label" for="conditions-match-all">' . esc_html__("Match All", "checkout-upsell-and-order-bumps") . '</label>
                        </span>
                        <span class="custom-control custom-radio custom-control-inline">
                            <input type="radio" class="custom-control-input" id="conditions-match-any" name="conditions[relation]" value="or"' . ($conditions_relation == "or" ? ' checked' : '') . (empty($conditions) ? ' disabled' : '') . '>
                            <label class="custom-control-label" for="conditions-match-any">' . esc_html__("Match Any", "checkout-upsell-and-order-bumps") . '</label>
                        </span></span>'); ?>
        </div>
        <div id="delete-all-conditions" class="d-flex justify-content-end align-items-center" style="gap:6px;">
            <i class="cuw-icon-delete text-red-primary"></i>
            <p class="text-red-primary font-weight-medium"><?php esc_html_e('Delete all conditions', 'checkout-upsell-and-order-bumps'); ?></p>
        </div>
    </div>

    <div id="no-conditions" class="mt-2 text-secondary font-weight-medium" <?php if (!empty($conditions)) echo 'style="display: none;"' ?>>
        <?php esc_html_e("Add conditions if you would like to personalize based on the items in the cart, order total etc.", 'checkout-upsell-and-order-bumps'); ?>
        <?php esc_html_e("(optional)", 'checkout-upsell-and-order-bumps'); ?>
    </div>
    <div id="cuw-conditions">
       <?php  $show_relation = false; $count = 0?>
        <?php foreach ($conditions as $key => $condition) {
            if (empty($condition['type'])) {
                continue;
            }
            $count ++;
            $type = $condition['type'];
            $name = isset($available_conditions[$type]['name']) ? $available_conditions[$type]['name'] : '';
            if (empty($name) || !isset($available_conditions[$type]['handler'])) {
                continue;
            }
            CUW()->view('Admin/Campaign/Conditions/Wrapper', [
                'key' => $key,
                'count' => $count,
                'name' => $name,
                'type' => $type,
                'condition' => $condition,
                'conditions' => $available_conditions,
                'relation' => $condition_relation,
                'show_relation' => $show_relation
            ]);
            $show_relation = true;
        } ?>
    </div>

    <div class="input-group mt-3 d-flex">
        <button type="button" id="add-condition" class="btn btn-outline-primary px-2">
            <i class="cuw-icon-add-circle inherit-color px-1"></i>
            <?php esc_html_e("Add condition", 'checkout-upsell-and-order-bumps'); ?>
        </button>
    </div>
</div>
