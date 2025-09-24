<?php

namespace FluentSupportPro\App\Services\Integrations;

class BuddyBoss
{
    public function boot()
    {
        add_filter('fluent_support/customer_extra_widgets', array($this, 'getBbInfo'), 50, 2);

	    // Custom Fields Support For WishListMember
	    $this->renderCustomFields();
    }

    public function renderCustomFields()
    {
	    add_filter('fluent_support/custom_field_types', function ($fieldTypes) {
		    $fieldTypes['bb_groups'] = [
			    'is_custom'   => true,
			    'is_remote'   => true,
			    'custom_text' => __('BuddyBoss groups will be shown at the ticket form', 'fluent-support-pro'),
			    'type'        => 'bb_groups',
			    'label'       => __('BuddyBoss Groups', 'fluent-support-pro'),
			    'value_type'  => 'number'
		    ];
		    $fieldTypes['bb_user_groups'] = [
			    'is_custom'   => true,
			    'is_remote'   => true,
			    'custom_text' => __('BuddyBoss user groups will be shown at the ticket form', 'fluent-support-pro'),
			    'type'        => 'bb_user_groups',
			    'label'       => __('BuddyBoss User Groups', 'fluent-support-pro'),
			    'value_type'  => 'number'
		    ];

		    return $fieldTypes;
	    }, 10, 1);

	    add_filter('fluent_support/render_custom_field_options_bb_groups', function ($field, $customer) {
		    if (function_exists('groups_get_groups')){
                $groups = groups_get_groups();
            }

		    if (!$groups) return $field;

		    $groups = $groups['groups'];

		    if(!$groups) return $field;

		    $options = [];

		    foreach ($groups as $group) {

			    $options[] = [
				    'id'    => strval($group->id),
				    'title' => $group->name
			    ];
		    }
		    if(!$options) return $field;

		    $field['type'] = 'select';
		    $field['filterable'] = true;
		    $field['rendered'] = true;
		    $field['options'] = $options;

		    return $field;

	    }, 10, 2);

	    add_filter('fluent_support/render_custom_field_options_bb_user_groups', function ($field, $customer) {
		    if(function_exists('groups_get_user_groups')){
			    $groupIds = groups_get_user_groups($customer->user_id);
		    }

		    if(!$groupIds) return $field;

		    $groupIds = $groupIds['groups'];

		    if(!$groupIds) return $field;

		    foreach ($groupIds as $groupId) {
                if (function_exists('groups_get_group')) {
	                $group = groups_get_group( array( 'group_id' => $groupId) );
                }

			    $options[] = [
				    'id'    => strval($group->id),
				    'title' => $group->name
			    ];
		    }
		    if(!$options) return $field;

		    $field['type'] = 'select';
		    $field['filterable'] = true;
		    $field['rendered'] = true;
		    $field['options'] = $options;

		    return $field;

	    }, 10, 2);

	    $hooks = ['bb_groups', 'bb_user_groups'];

	    foreach ($hooks as $hook) {
		    add_filter('fluent_support/custom_field_render_'. $hook, function ($value){

			    if (!is_numeric($value)) {
				    return $value;
			    }

			    $groupId = absint($value);

			    if (!$groupId) return $value;

			    if (function_exists('groups_get_group')) {
				    $group = groups_get_group( array( 'group_id' => $groupId) );
			    }

			    if (!$group) return;

                return '<a target="_blank" rel="nofollow" href="'. bp_get_group_permalink($group) .'">' .$group->name. '</a>';

		    }, 10,1);
	    }
    }
    public function getBbInfo($widgets, $customer)
    {

	    if(function_exists('groups_get_user_groups')){
		    $groupIds = groups_get_user_groups($customer->user_id);
	    }

        if(empty($groupIds)) return;

        $groupInfos = [];

        foreach( $groupIds["groups"] as $id ) {
            $group = groups_get_group( array( 'group_id' => $id) );
            $groupInfos[] = [
                'name'          => esc_html($group->name),
                'member_from'   => esc_html(date_i18n(get_option('date_format'), strtotime($group->date_created)))
            ];
        }
        if (empty($groupInfos)) return;

        ob_start();
        ?>

        <ul>
            <?php foreach ($groupInfos as $group):?>
                <li title="<?php $group['name']?>" class="fs_widget_li">
                    <?php
                    echo '<code>'.__('Name:', 'fluent-support-pro').'</code> ' . $group['name'] . '<br>';
                    echo '<code>'.__('Member From:', 'fluent-support-pro').'</code> '. $group['member_from'];
                    ?>
                </li>
            <?php endforeach; ?>
        </ul>
        <?php
        $content = ob_get_clean();

        $widgets['bb_groups'] = [
            'header'    => __('BuddyBoss Groups', 'fluent-support-pro'),
            'body_html' => $content
        ];
        return $widgets;
    }

    public function addToWorkflow($customField, $key)
    {
        $options = [];

        if (function_exists('groups_get_groups')){
            $groups = groups_get_groups();
        }

        foreach ($groups['groups'] as $group) {
            $options[$group->id] = $group->name;
        }

        return [
            'title'     => $customField['label'],
            'data_type' => 'single_dropdown',
            'group'     => 'Custom Fields',
            'options'   => $options
        ];
    }
}
