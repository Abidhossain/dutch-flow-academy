<?php

namespace FluentSupportPro\App\Services;

use FluentSupport\App\Services\Helper;
use FluentSupport\Framework\Support\Arr;
use FluentSupport\App\Models\Product;

class CustomFieldsService
{
    private static $optionKey = '_ticket_custom_fields';

    public static function getCustomFields()
    {
        return Helper::getOption(self::$optionKey, []);
    }

    public static function updateCustomFields($fields)
    {
        return Helper::updateOption(self::$optionKey, $fields);
    }

    public static function getFieldTypes()
    {
        return apply_filters('fluent_support/custom_field_types', [
            'text'          => [
                'type'       => 'text',
                'label'      => 'Single Line Text',
                'value_type' => 'string',
                'required'   => 'no'
            ],
            'textarea'      => [
                'type'       => 'textarea',
                'label'      => 'Multi Line Text',
                'value_type' => 'string',
                'required'   => 'no'
            ],
            'number'        => [
                'type'       => 'number',
                'label'      => 'Numeric Field',
                'value_type' => 'numeric',
                'required'   => 'no'
            ],
            'select-one' => [
                'type'       => 'select-one',
                'label'      => 'Select choice',
                'value_type' => 'string',
                'required'   => 'no'
            ],
            'radio'         => [
                'type'       => 'radio',
                'label'      => 'Radio Choice',
                'value_type' => 'string',
                'required'   => 'no'
            ],
            'checkbox'      => [
                'type'       => 'checkbox',
                'label'      => 'Checkboxes',
                'value_type' => 'array',
                'required'   => 'no'
            ]
        ]);
    }

    public static function getFieldSlugs()
    {
        $fields = self::getCustomFields();

        $slugs = [];
        foreach ($fields as $field) {
            $slugs[] = $field['slug'];
        }

        return $slugs;
    }

    public static function getFieldLabels($scope = 'public')
    {
        $fields = self::getCustomFields();

        if (!$fields) {
            return [];
        }

        $formattedData = [];

        foreach ($fields as $field) {

            if ($scope == 'public' && Arr::get($field, 'admin_only') == 'yes') {
                continue;
            }

            $label = $field['label'];
            if ($scope == 'admin' && !empty($label['admin_label'])) {
                $label = $label['admin_label'];
            }

            $field['label'] = $label;
            unset($field['admin_label']);

            $formattedData[$field['slug']] = $field;
        }

        return $formattedData;
    }


    public static function getRenderedPublicFields($customer = false, $scope = 'public')
    {
        if (!$customer) {
            $customer = Helper::getCurrentCustomer();
        }

        if (!$customer) {
            return [];
        }

        $publicFields = self::getFieldLabels($scope);
        $fieldTypes = self::getFieldTypes();

        $validFields = [];

        foreach ($publicFields as $fieldIndex => $publicField) {
            $fieldType = Arr::get($fieldTypes, $publicField['type']);
            if (!$fieldType) {
                continue;
            }

            if (!empty($fieldType['is_remote'])) {
                $publicField = apply_filters('fluent_support/render_custom_field_options_' . $fieldType['type'], $publicField, $customer);
                if (!$publicField || empty($publicField['rendered'])) {
                    continue;
                }
            }

            $validFields[$fieldIndex] = $publicField;
        }

        return $validFields;

    }

    public static function requiredFieldsForCustomer($requiredFields)
    {
        $customer = Helper::getCurrentCustomer();

        if (!$customer) {
            return $requiredFields;
        }

        $publicFields = self::getFieldLabels();

        if(!$publicFields){
            return $requiredFields;
        }

        $fieldTypes = self::getFieldTypes();

        foreach ($publicFields as $fieldIndex => $publicField) {
            $fieldType = Arr::get($fieldTypes, $publicField['type']);

            if (!$fieldType) {
                continue;
            }

            if(isset($publicField['required']) && Arr::get($publicField, 'required') == 'yes') {
                if (empty($fieldType['is_remote'])) {
                    $requiredFields['required_fields']['custom_data.' . $publicField['slug']] = 'required';
                    $requiredFields['error_messages']['custom_data.' . $publicField['slug'] . '.required'] = sprintf(__('%s is required', 'fluent-support-pro'), $publicField['label']);
                } else{
                    $publicField = apply_filters('fluent_support/render_custom_field_options_' . $fieldType['type'], $publicField, $customer);
                    if (isset($publicField['rendered']) && $publicField['rendered'] &&  Arr::get($publicField, 'required') == 'yes') {
                        $requiredFields['required_fields']['custom_data.' . $publicField['slug']] = 'required';
                        $requiredFields['error_messages']['custom_data.' . $publicField['slug'] . '.required'] = sprintf(__('%s is required', 'fluent-support-pro'), $publicField['label']);
                    }
                }
            }
        }

        return $requiredFields;
    }

    /**
     * @param $data
     * @return array
     * This method will remove required fields from validation if condition is not passed
     */
    public static function requiredFieldsForCustomerByConditions($data)
    {
        $publicFields = self::getFieldLabels();
        $userData = array_merge($data['custom_data'], $data['default_data']);
        foreach ($publicFields as $publicField){
            if(self::IsThisFieldIsRequiredAndDependsOnCondition($publicField)){
                if(!self::IsConditionPassed($publicField, $userData)){
                    unset($data['required_fields']['custom_data.' . $publicField['slug']]);
                    unset($data['error_messages']['custom_data.' . $publicField['slug'] . '.required']);
                    unset($data['custom_data'][$publicField['slug']]);
                }
            }
        }
        return $data;
    }

    /**
     * @param $field
     * @param $userData
     * @return bool
     * This method check all conditions for a field and return true if all conditions are passed
     */
    private static function IsConditionPassed($field, $userData){
        $conditions = Arr::get($field, 'conditions');
        $conditionMatchType = Arr::get($field, 'match_type');
        if( !$conditions ){
            return true;
        }
        $singleMatch = false;
        $allMatch = true;
        foreach ($conditions as $condition){
            $field = Arr::get($condition, 'item_key');
            $matchValue = Arr::get($condition, 'value');

            if ($field === 'ticket_product_id' && isset($userData['ticket_product_id'])) {
                $productID = $userData['ticket_product_id'];
                $product = Product::where('id', $productID)->value('title');
                $matchValue = ($product === $matchValue) ? $productID : $matchValue;
            }

            $operator = Arr::get($condition, 'operator');
            $submittedValue = Arr::get($userData, $field);
            if(self::compare($matchValue, $operator, $submittedValue)){
                $singleMatch = true;
            }else{
                $allMatch = false;
            }
        }

        if($conditionMatchType == 'all'){
            return $singleMatch && $allMatch;
        }else{
            return $singleMatch;
        }
    }

    /**
     * @param $field
     * @return bool
     * This method check if a field is required and depends on condition
     */
    private static function IsThisFieldIsRequiredAndDependsOnCondition($field){
        return (isset($field['required']) && Arr::get($field, 'required') == 'yes') &&
            (isset($field['has_logics']) && Arr::get($field, 'has_logics') == 'yes');
    }

    /**
     * @param $matchValue
     * @param $operator
     * @param $submittedValue
     * @return bool
     * This method compare two values based on operator
     */
    private static function compare($matchValue, $operator, $submittedValue)
    {
        switch ($operator) {
            case '=':
                return $submittedValue == $matchValue;
            case '!=':
                return $submittedValue != $matchValue;
            case 'gt':
                return $submittedValue > $matchValue;
            case 'lt':
                return $submittedValue < $matchValue;
            case 'contains':
                return strpos($submittedValue, $matchValue) !== false;
            case 'not_contains':
                return strpos($submittedValue, $matchValue) === false;
            case 'in':
                return in_array($submittedValue, explode(',', $matchValue));
            case 'not_in':
                return !in_array($submittedValue, explode(',', $matchValue));
            default:
                return false;
        }
    }

    public static function getCustomerRenderers()
    {
		$fieldTypes = [
			'woo_orders',
			'woo_products',
			'edd_orders',
			'edd_products',
			'learndash_courses',
			'learndash_user_courses',
			'llms_user_courses',
			'llms_courses',
			'pmpro_levels',
			'pmpro_user_levels',
			'rcpro_levels',
			'rcpro_user_levels',
			'tutorlms_courses',
			'tutorlms_user_courses',
			'wlm_levels',
			'wlm_user_levels',
			'bb_groups',
			'bb_user_groups',
            'learnpress_courses',
            'learnpress_user_courses',
            'fcom_courses',
            'fcom_user_courses'
		];

        return apply_filters('fluent_support/custom_field_renders_type', $fieldTypes);
    }

    public function addCustomFieldToWorkflowTrigger($conditions)
    {
        $customFields = static::getCustomFields();

        if(!$customFields) {
            return $conditions;
        }

        foreach ($customFields as $field) {
            $conditions[] = 'custom_fields.' . $field['slug'];
        }

        return $conditions;
    }

    public function addCustomFieldToWorkflowCondition($conditions)
    {
        $customFields = static::getCustomFields();

        if(!$customFields) {
            return $conditions;
        }

        foreach ($customFields as $customField) {
            if(in_array($customField['type'], CustomFieldsService::getCustomerRenderers())) {
                $customFieldsServicesForIntegrations = new CustomFieldsServicesForIntegrations();
                $conditions['custom_fields.'.$customField['slug']] = $customFieldsServicesForIntegrations->addCustomFieldToWorkflowCondition($customField['type'] , $customField);
            } else {
                $conditions['custom_fields.'.$customField['slug']] = [
                    'title'     => $customField['label'],
                    'data_type' => $this->convertDataType($customField['type']),
                    'group'     => 'Custom Fields'
                ];

                if(!in_array($customField['type'], ['text', 'textarea', 'number'])){
                    $conditions['custom_fields.'.$customField['slug']]['options'] = array_combine($customField['options'], $customField['options']);
                }
            }

        }

        return $conditions;
    }

    private function convertDataType($type)
    {
        $types = [
            'text'       => 'string',
            'textarea'   => 'string',
            'number'     => 'string',
            'select-one' => 'single_dropdown',
            'radio'      => 'single_dropdown',
            'checkbox'   => 'multiple_select'
        ];

        return Arr::get($types, $type, 'string');
    }
}
