<?php
namespace FluentSupportPro\App\Services;

use FluentSupport\App\Services\Helper;
use FluentSupport\Framework\Support\Arr;
use FluentSupportPro\App\Services\CustomFieldsService;

class CustomFieldsServicesForIntegrations
{
    public function mapClass($key, $customField)
    {
        $namespace = 'FluentSupportPro\App\Services\Integrations\\';

        $methodArray = [
            'woo_products' => 'WooCommerce',
            'woo_orders' => 'WooCommerce',
            'edd_orders' => 'Edd',
            'edd_products' => 'Edd',
            'learndash_courses' => 'LearnDash',
            'learndash_user_courses' => 'LearnDash',
            'llms_user_courses' => 'LifterLMS',
            'llms_courses' => 'LifterLMS',
            'pmpro_levels' => 'PMPro',
            'pmpro_user_levels' => 'PMPro',
            'rcpro_levels' => 'RCPro',
            'rcpro_user_levels' => 'RCPro',
            'tutorlms_courses' => 'TutorLMS',
            'tutorlms_user_courses' => 'TutorLMS',
            'wlm_levels' => 'WishListMember',
            'wlm_user_levels' => 'WishListMember',
            'bb_groups' => 'BuddyBoss',
            'bb_user_groups' => 'BuddyBoss',
            'learnpress_courses' => 'LearnPress',
            'learnpress_user_courses' => 'LearnPress',
            'fcom_user_courses' => 'FluentCommunity',
            'fcom_courses' => 'FluentCommunity'
        ];

        $namespaceWithClass = $namespace . $methodArray[$key];
        $class = new $namespaceWithClass();
        return $class->addToWorkflow($customField, $key);
    }

    public function addCustomFieldToWorkflowCondition($type, $customField)
    {
        return $this->mapClass($type, $customField);
    }
}
