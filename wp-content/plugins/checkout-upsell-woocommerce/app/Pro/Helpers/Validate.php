<?php
/**
 * UpsellWP
 *
 * @package   checkout-upsell-woocommerce
 * @author    Anantharaj B <anantharaj@flycart.org>
 * @copyright 2024 UpsellWP
 * @license   GPL-3.0-or-later
 * @link      https://upsellwp.com
 */

namespace CUW\App\Pro\Helpers;

use CUW\App\Helpers\Input;
use CUW\App\Helpers\Validate as baseValidate;

defined('ABSPATH') || exit;

class Validate extends baseValidate
{
    /**
     * Validate recommendation engine data before save
     *
     * @param array $data
     * @return array
     */
    public static function engine($data)
    {
        $errors = [];
        $messages = self::messages();

        // validate title
        $title = isset($data['title']) ? $data['title'] : "Untitled";
        $validator = Input::validator(compact('title'));
        $validator->rule('lengthMax', 'title', 255)->message($messages['lengthMax']);

        if (!$validator->validate()) {
            foreach ($validator->errors() as $field => $error) {
                $errors['fields'][$field] = $error[0];
            }
        }

        // validate filters
        if (!empty($data['engine_filters'])) {
            foreach ($data['engine_filters'] as $key => $filter) {
                $validator = Input::validator($filter);
                $validator->rule('requiredWith', 'values', 'method')->message($messages['requiredWith']);
                $validator->rule('requiredWith', 'value', 'operator')->message($messages['requiredWith']);
                $validator->rule('numeric', 'value')->message($messages['numeric']);

                if (!$validator->validate()) {
                    foreach ($validator->errors() as $field => $error) {
                        $errors['fields']['engine_filters[' . $key . '][' . $field . ']'] = $error[0];
                    }
                }
            }
        }

        return apply_filters('cuw_validate_engine', $errors, $data);
    }

    /**
     * Validate product recommendations campaign
     *
     * @param array $errors
     * @param array $data
     * @return array
     */
    public static function validateCampaign($errors, $data)
    {
        $messages = self::messages();
        if (!empty($data['type']) && $data['type'] == 'product_recommendations') {
            $validator = Input::validator($data['data'] ?? []);
            $validator->rule('required', 'page')->message($messages['required']);
            if (!$validator->validate()) {
                foreach ($validator->errors() as $key => $error) {
                    $errors['fields']['data[' . $key . ']'] = $error[0];
                }
            }
        }
        return $errors;
    }
}