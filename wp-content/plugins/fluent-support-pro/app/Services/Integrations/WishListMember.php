<?php

namespace FluentSupportPro\App\Services\Integrations;

class WishListMember
{

    public function boot()
    {
        add_filter('fluent_support/customer_extra_widgets', array($this, 'getWLMembershipInfo'), 110, 2);

        // Custom Fields Support For WishListMember
        $this->renderCustomFields();
    }

    public function renderCustomFields()
    {
	    add_filter('fluent_support/custom_field_types', function ($fieldTypes) {
		    $fieldTypes['wlm_levels'] = [
			    'is_custom'   => true,
			    'is_remote'   => true,
			    'custom_text' => __('WishListMember levels will be shown at the ticket form', 'fluent-support-pro'),
			    'type'        => 'wlm_levels',
			    'label'       => __('WishListMember Levels', 'fluent-support-pro'),
			    'value_type'  => 'number'
		    ];
		    $fieldTypes['wlm_user_levels'] = [
			    'is_custom'   => true,
			    'is_remote'   => true,
			    'custom_text' => __('WishListMember user levels will be shown at the ticket form', 'fluent-support-pro'),
			    'type'        => 'wlm_user_levels',
			    'label'       => __('WishListMember User Levels', 'fluent-support-pro'),
			    'value_type'  => 'number'
		    ];

		    return $fieldTypes;
	    }, 10, 1);

	    add_filter('fluent_support/render_custom_field_options_wlm_levels', function ($field, $customer) {
		    $levelBlocks = wlmapi_get_levels();

		    if (!$levelBlocks) return $field;

		    $levels = $levelBlocks['levels']['level'];

            if(!$levels) return $field;

		    $options = [];

		    foreach ($levels as $level) {
			    $options[] = [
				    'id'    => strval($level['id']),
				    'title' => $level['name']
			    ];
		    }
		    if(!$options) return $field;

		    $field['type'] = 'select';
		    $field['filterable'] = true;
		    $field['rendered'] = true;
		    $field['options'] = $options;

		    return $field;

	    }, 10, 2);

	    add_filter('fluent_support/render_custom_field_options_wlm_user_levels', function ($field, $customer) {
		    $levels = wlmapi_get_member_levels($customer->user_id);

		    if (!$levels) return $field;

		    $options = [];

		    foreach ($levels as $level) {
			    $options[] = [
				    'id'    => strval($level->Level_ID),
				    'title' => $level->Name
			    ];
		    }
		    if(!$options) return $field;

		    $field['type'] = 'select';
		    $field['filterable'] = true;
		    $field['rendered'] = true;
		    $field['options'] = $options;

		    return $field;

	    }, 10, 2);

	    $hooks = ['wlm_levels', 'wlm_user_levels'];

	    foreach ($hooks as $hook) {
		    add_filter('fluent_support/custom_field_render_'. $hook, function ($value){

			    if (!is_numeric($value)) {
				    return $value;
			    }

			    $levelId = absint($value);

			    if (!$levelId) return $value;
                $level = wlmapi_get_level($levelId);

                if (!$level) return;
			    return $level['level']['name'];
		    }, 10,1);
	    }
    }

    public function getWLMembershipInfo($widgets, $customer)
    {
        if (!$customer->user_id) return;

        $levels = wlmapi_get_member_levels($customer->user_id);

        if (empty($levels)) return;
        $membershipInfo = [];
        foreach ($levels as $level) {
            $membershipInfo[] = [
                'level'  => esc_html($level->Name),
                'status' => esc_html($level->Status[0])
            ];
        }

        if (empty($membershipInfo)) return;

        ob_start();
        ?>

        <ul>
            <?php foreach ($membershipInfo as $info): ?>
                <li title="<?php $info['level'] ?>" class="fs_widget_li">
                    <?php
                    echo '<code>'.__('Level:', 'fluent-support-pro').'</code> '. $info['level'] . '<br>';
                    echo '<code>'. __('Status:', 'fluent-support-pro') .'</code> '. $info['status'] . '<br>';
                    ?>
                </li>
            <?php endforeach; ?>
        </ul>
        <?php
        $content = ob_get_clean();

        $widgets['wlm'] = [
            'header'    => __('WishList Member', 'fluent-support-pro'),
            'body_html' => $content
        ];
        return $widgets;
    }

    public function addToWorkflow($customField, $key)
    {
        $options = [];

        $levelBlocks = wlmapi_get_levels();

        $levels = $levelBlocks['levels']['level'];

        foreach ($levels as $level) {
            $options[$level['id']] = $level['name'];
        }

        return [
            'title'     => $customField['label'],
            'data_type' => 'single_dropdown',
            'group'     => 'Custom Fields',
            'options'   => $options
        ];
    }
}
