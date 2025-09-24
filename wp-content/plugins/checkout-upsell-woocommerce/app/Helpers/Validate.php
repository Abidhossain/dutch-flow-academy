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

namespace CUW\App\Helpers;

defined('ABSPATH') || exit;

class Validate
{
    /**
     * Messages
     *
     * @return array
     */
    public static function messages()
    {
        return [
            // validator messages
            'required' => esc_html__("This field is required", 'checkout-upsell-woocommerce'),
            'numeric' => esc_html__("It must be numeric", 'checkout-upsell-woocommerce'),
            'integer' => esc_html__("It must be an integer", 'checkout-upsell-woocommerce'),
            'min' => esc_html__("It must be at least %s", 'checkout-upsell-woocommerce'),
            'max' => esc_html__("It must be no more than %s", 'checkout-upsell-woocommerce'),
            'url' => esc_html__("It is not a valid URL", 'checkout-upsell-woocommerce'),
            'regex' => esc_html__("It contains invalid characters", 'checkout-upsell-woocommerce'),
            'dateAfter' => esc_html__("It must be date after %s", 'checkout-upsell-woocommerce'),
            'lengthMax' => esc_html__("It must not exceed %d characters", 'checkout-upsell-woocommerce'),
            'requiredWith' => esc_html__("This field is required", 'checkout-upsell-woocommerce'),

            // custom messages
            'coupon_exists' => esc_html__('This coupon already exists in WooCommerce', 'checkout-upsell-woocommerce'),

            // other messages
            // 'equals'         => esc_html__("{field} must be the same as '%s'", 'checkout-upsell-woocommerce'),
            // 'different'      => esc_html__("{field} must be different than '%s'", 'checkout-upsell-woocommerce'),
            // 'accepted'       => esc_html__("{field} must be accepted", 'checkout-upsell-woocommerce'),
            // 'length'         => esc_html__("{field} must be %d characters long", 'checkout-upsell-woocommerce'),
            // 'listContains'   => esc_html__("{field} contains invalid value", 'checkout-upsell-woocommerce'),
            // 'in'             => esc_html__("{field} contains invalid value", 'checkout-upsell-woocommerce'),
            // 'notIn'          => esc_html__("{field} contains invalid value", 'checkout-upsell-woocommerce'),
            // 'ip'             => esc_html__("{field} is not a valid IP address", 'checkout-upsell-woocommerce'),
            // 'ipv4'           => esc_html__("{field} is not a valid IPv4 address", 'checkout-upsell-woocommerce'),
            // 'ipv6'           => esc_html__("{field} is not a valid IPv6 address", 'checkout-upsell-woocommerce'),
            // 'email'          => esc_html__("{field} is not a valid email address", 'checkout-upsell-woocommerce'),
            // 'urlActive'      => esc_html__("{field} must be an active domain", 'checkout-upsell-woocommerce'),
            // 'alpha'          => esc_html__("{field} must contain only letters a-z", 'checkout-upsell-woocommerce'),
            // 'alphaNum'       => esc_html__("{field} must contain only letters a-z and/or numbers 0-9", 'checkout-upsell-woocommerce'),
            // 'slug'           => esc_html__("{field} must contain only letters a-z, numbers 0-9, dashes and underscores", 'checkout-upsell-woocommerce'),
            // 'date'           => esc_html__("{field} is not a valid date", 'checkout-upsell-woocommerce'),
            // 'dateFormat'     => esc_html__("{field} must be date with format '%s'", 'checkout-upsell-woocommerce'),
            // 'dateBefore'     => esc_html__("{field} must be date before %s", 'checkout-upsell-woocommerce'),
            // 'contains'       => esc_html__("{field} must contain %s", 'checkout-upsell-woocommerce'),
            // 'boolean'        => esc_html__("{field} must be a boolean", 'checkout-upsell-woocommerce'),
            // 'lengthBetween'  => esc_html__("{field} must be between %d and %d characters", 'checkout-upsell-woocommerce'),
            // 'creditCard'     => esc_html__("{field} must be a valid credit card number", 'checkout-upsell-woocommerce'),
            // 'lengthMin'      => esc_html__("{field} must be at least %d characters long", 'checkout-upsell-woocommerce'),
            // 'instanceOf'     => esc_html__("{field} must be an instance of '%s'", 'checkout-upsell-woocommerce'),
            // 'containsUnique' => esc_html__("{field} must contain unique elements only", 'checkout-upsell-woocommerce'),
            // 'requiredWithout'=> esc_html__("{field} is required", 'checkout-upsell-woocommerce'),
            // 'subset'         => esc_html__("{field} contains an item that is not in the list", 'checkout-upsell-woocommerce'),
            // 'arrayHasKeys'   => esc_html__("{field} does not contain all required keys", 'checkout-upsell-woocommerce'),
        ];
    }

    /**
     * Validate campaign data before save
     *
     * @param array $data
     * @return array
     */
    public static function campaign($data)
    {
        $errors = [];
        $messages = self::messages();

        // validate title, priority and limits
        $title = isset($data['title']) ? $data['title'] : "Untitled";
        $priority = isset($data['priority']) ? $data['priority'] : 10;
        $limit = isset($data['limit']) ? $data['limit'] : '';
        $limit_per_user = isset($data['limit_per_user']) ? $data['limit_per_user'] : '';
        $validator = Input::validator(compact('title', 'priority', 'limit', 'limit_per_user'));
        $validator->rule('lengthMax', 'title', 255)->message($messages['lengthMax']);
        $validator->rule('integer', 'priority')->message($messages['integer']);
        $validator->rule('min', 'priority', 1)->message($messages['min']);
        $validator->rule('max', 'priority', 9999)->message($messages['max']);
        if (!empty($data['limit'])) {
            $validator->rule('integer', 'limit')->message($messages['integer']);
            $validator->rule('min', 'limit', 1)->message($messages['min']);
            $validator->rule('max', 'limit', 99999999)->message($messages['max']);
        }
        if (!empty($data['limit_per_user'])) {
            $max_limit = !empty($data['limit']) ? $data['limit'] : 99999999;
            $validator->rule('integer', 'limit_per_user')->message($messages['integer']);
            $validator->rule('min', 'limit_per_user', 1)->message($messages['min']);
            $validator->rule('max', 'limit_per_user', $max_limit)->message($messages['max']);
        }
        if (!$validator->validate()) {
            foreach ($validator->errors() as $field => $error) {
                $errors['fields'][$field] = $error[0];
            }
        }

        // validate offers
        if (!empty($data['offers'])) {
            foreach ($data['offers'] as $key => $offer) {
                if (isset($offer['deleted'])) {
                    continue;
                }

                $validator = self::offerValidator($offer);
                if (!$validator->validate()) {
                    $errors['divs'][] = "#cuw-offers #offer-$key .offer-item";
                }
            }
        }

        // validate checkout upsells campaign offer section
        if (!empty($data['type']) && $data['type'] == 'checkout_upsells' && !empty($data['data'])) {
            $validator = Input::validator($data['data']);
            $validator->rule('required', ['display_method', 'display_location'])->message($messages['required']);
            if (isset($data['data']['display_method']) && $data['data']['display_method'] == 'ab_testing') {
                $validator->rule('required', ['a.percentage', 'b.percentage'])->message($messages['required']);
                $validator->rule('integer', ['a.percentage', 'b.percentage'])->message($messages['integer']);
                $validator->rule('min', ['a.percentage', 'b.percentage'], 1)->message($messages['min']);
                $validator->rule('max', ['a.percentage', 'b.percentage'], 99)->message($messages['max']);
            }
            if (!$validator->validate()) {
                foreach ($validator->errors() as $key => $error) {
                    $key = str_replace('.', '][', $key);
                    $errors['fields']['data[' . $key . ']'] = $error[0];
                }
            }
        }

        // validate filters
        if (!empty($data['filters'])) {
            foreach ($data['filters'] as $key => $filter) {
                $validator = Input::validator($filter);
                $validator->rule('requiredWith', 'values', 'method')->message($messages['requiredWith']);
                $validator->rule('requiredWith', 'value', 'operator')->message($messages['requiredWith']);

                if (!$validator->validate()) {
                    foreach ($validator->errors() as $field => $error) {
                        $errors['fields']['filters[' . $key . '][' . $field . ']'] = $error[0];
                    }
                }
            }
        }

        // validate conditions
        if (!empty($data['conditions'])) {
            foreach ($data['conditions'] as $key => $condition) {
                $validator = Input::validator($condition);
                if (!empty($condition['method']) && !in_array($condition['method'], ['empty', 'not_empty'])) {
                    $validator->rule('requiredWith', 'values', 'method')->message($messages['requiredWith']);
                }
                $validator->rule('requiredWith', 'value', 'operator')->message($messages['requiredWith']);
                if (isset($condition['operator']) && isset($condition['value'])) {
                    if ($condition['operator'] == 'range') {
                        $validator->rule('regex', 'value', '/^[0-9.]+-[0-9.]+$/')->message($messages['regex']);
                    } else {
                        if ($condition['type'] == 'items_count') {
                            $validator->rule('integer', 'value')->message($messages['integer']);
                            $validator->rule('min', 'value', 1)->message($messages['min']);
                            $validator->rule('max', 'value', 99999999)->message($messages['max']);
                        } else {
                            $validator->rule('numeric', 'value')->message($messages['numeric']);
                            $validator->rule('min', 'value', 0)->message($messages['min']);
                            $validator->rule('max', 'value', 99999999999.999)->message($messages['max']);
                        }
                    }
                }

                // validate purchase history based conditions
                if (isset($condition['type']) && in_array($condition['type'], ['orders_made', 'orders_made_with_products', 'total_spent'])) {
                    $validator->rule('required', 'order_statuses')->message($messages['required']);
                    if ($condition['type'] == 'orders_made_with_products') {
                        $validator->rule('required', 'order_product_ids')->message($messages['required']);
                    }
                }

                if (!$validator->validate()) {
                    foreach ($validator->errors() as $field => $error) {
                        $errors['fields']['conditions[' . $key . '][' . $field . ']'] = $error[0];
                    }
                }

                // validate time condition
                if (isset($condition['type']) && isset($condition['values']) && $condition['type'] == 'time') {
                    $validator = Input::validator($condition['values']);
                    $validator->rule('required', ['from', 'to'])->message($messages['required']);
                    if (!$validator->validate()) {
                        foreach ($validator->errors() as $field => $error) {
                            $errors['fields']['conditions[' . $key . '][values][' . $field . ']'] = $error[0];
                        }
                    }
                }
            }
        }

        // validate products
        if (!empty($data['data']['products'])) {
            $validator = Input::validator($data['data']['products']);
            if (!empty($data['data']['products']['use'])) {
                if ($data['data']['products']['use'] == 'specific') {
                    $validator->rule('required', 'ids')->message($messages['required']);
                } elseif ($data['data']['products']['use'] == 'engine') {
                    $validator->rule('required', 'engine_id')->message($messages['required']);
                }
            }

            if (!empty($data['data']['products']['quantity_field']) && $data['data']['products']['quantity_field'] == 'fixed') {
                $validator->rule('required', 'quantity_value')->message($messages['required']);
                $validator->rule('numeric', 'quantity_value')->message($messages['numeric']);
                $validator->rule('min', 'quantity_value', 0.001)->message($messages['min']);
                $validator->rule('max', 'quantity_value', 99999999.999)->message($messages['max']);
            }

            if (!$validator->validate()) {
                foreach ($validator->errors() as $field => $error) {
                    $errors['fields']['data[products][' . $field . ']'] = $error[0];
                }
            }
        }

        // validate discount
        if (!empty($data['data']['discount'])) {
            if (!isset($data['data']['discount']['apply_to']) || $data['data']['discount']['apply_to'] != 'no_products') {
                $discount = $data['data']['discount'];
                $validator = Input::validator($discount);
                $validator->rule('required', ['type', 'value'])->message($messages['required']);
                $validator->rule('numeric', 'value')->message($messages['numeric']);
                if (!in_array($discount['type'], ['free', 'no_discount'])) {
                    $validator->rule('min', 'value', 0.001)->message($messages['min']);
                }
                if ($discount['type'] == "percentage") {
                    $validator->rule('max', 'value', 100)->message($messages['max']);
                } elseif ($discount['type'] == "fixed_price") {
                    $validator->rule('max', 'value', 99999999999.999)->message($messages['max']);
                }

                if (isset($data['data']['discount']['apply_as']) && $data['data']['discount']['apply_as'] == 'dynamic_coupon') {
                    $validator->rule('required', ['label'])->message($messages['required']);
                    if (WC::isCouponExists($data['data']['discount']['label'])) {
                        $errors['fields']['data[discount][label]'] = $messages['coupon_exists'];
                    }
                }

                if (!$validator->validate()) {
                    foreach ($validator->errors() as $field => $error) {
                        $errors['fields']['data[discount][' . $field . ']'] = $error[0];
                    }
                }
            }
        }

        // validate next order coupon
        if (!empty($data['type']) && $data['type'] == 'noc' && !empty($data['data'])) {
            $validator = Input::validator($data['data']);
            $validator->rule('required', 'order_statuses')->message($messages['required']);
            if (!$validator->validate()) {
                foreach ($validator->errors() as $key => $error) {
                    $errors['fields']['data[' . $key . ']'] = $error[0];
                }
            }
        }

        // validate coupon
        if (!empty($data['data']['coupon'])) {
            $coupon = $data['data']['coupon'];
            $validator = Input::validator($coupon);
            $validator->rule('lengthMax', 'prefix', 16)->message($messages['lengthMax']);
            $validator->rule('required', 'length')->message($messages['required']);
            $validator->rule('numeric', 'length')->message($messages['numeric']);
            $validator->rule('min', 'length', 6)->message($messages['min']);
            $validator->rule('max', 'length', 16)->message($messages['max']);
            $validator->rule('integer', 'expire_after_x_days')->message($messages['numeric']);
            $validator->rule('min', 'expire_after_x_days', 1)->message($messages['min']);
            $validator->rule('max', 'expire_after_x_days', 366)->message($messages['max']);
            $min_amount = !empty($coupon['minimum_amount']) ? $coupon['minimum_amount'] : 1;
            $validator->rule('min', ['minimum_amount', 'maximum_amount'], max($min_amount, 1))->message($messages['min']);
            $validator->rule('max', ['minimum_amount', 'maximum_amount'], 99999999)->message($messages['max']);
            if (!$validator->validate()) {
                foreach ($validator->errors() as $field => $error) {
                    $errors['fields']['data[coupon][' . $field . ']'] = $error[0];
                }
            }
        }

        // validate options
        if (!empty($data['data']['options'])) {
            if (!empty($data['data']['options']['redirect_url']) && $data['data']['options']['redirect_url'] == 'custom') {
                $validator = Input::validator($data['data']['options']);
                $validator->rule('required', ['custom_redirect_url'])->message($messages['required']);
                $validator->rule('url', ['custom_redirect_url'])->message($messages['url']);
                if (!$validator->validate()) {
                    foreach ($validator->errors() as $field => $error) {
                        $errors['fields']['data[options][' . $field . ']'] = $error[0];
                    }
                }
            }
        }

        // validate timer
        if (!empty($data['data']['timer']) && !empty($data['data']['timer']['enabled'])) {
            $validator = Input::validator($data['data']['timer']);
            $validator->rule('required', ['minutes', 'seconds', 'message'])->message($messages['required']);
            $validator->rule('numeric', ['minutes', 'seconds'])->message($messages['numeric']);
            if (!$validator->validate()) {
                foreach ($validator->errors() as $field => $error) {
                    $errors['fields']['data[timer][' . $field . ']'] = $error[0];
                }
            }
        }

        // validate schedule from and to date
        if (!empty($data['date_from']) && !empty($data['date_to']) && $data['date_from'] != $data['date_to']) {
            $validator = Input::validator(['date_from' => $data['date_from'], 'date_to' => $data['date_to']]);
            $validator->rule('dateAfter', 'date_to', $data['date_from'])->message($messages['dateAfter']);
            if (!$validator->validate()) {
                foreach ($validator->errors() as $field => $error) {
                    $errors['fields'][$field] = $error[0];
                }
            }
        }
        return apply_filters('cuw_validate_campaign', $errors, $data);
    }

    /**
     * Validate offer data before save (alone)
     *
     * @param array $data
     * @return array
     */
    public static function offer($data)
    {
        $errors = [];
        $validator = self::offerValidator($data, true);
        if (!$validator->validate()) {
            foreach ($validator->errors() as $key => $error) {
                $errors['fields']['offer[' . $key . ']'] = $error[0];
            }
        }
        return apply_filters('cuw_validate_offer', $errors, $data);
    }

    /**
     * Validator for offer
     *
     * @param array $data
     * @param bool $check_product
     * @return \Valitron\Validator
     */
    private static function offerValidator($data, $check_product = false)
    {
        $product = false;
        $messages = self::messages();
        $validator = Input::validator($data);

        $check_product = apply_filters('cuw_validate_offer_product_on_save', $check_product, $data);
        if ($check_product && isset($data['product_id'])) {
            \Valitron\Validator::addRule('isPurchasableProduct', function ($field, $value, $params) {
                return WC::isPurchasableProduct($value, isset($params[0]) ? $params[0] : 0);
            });
            $product = WC::getProduct($data['product_id']);
        }
        $validator->rule('required', ['product_id', 'discount_type', 'discount_value'])->message($messages['required']);
        if ($check_product && $product) {
            $validator->rule('isPurchasableProduct', ['product_id'], $data['product_qty'])
                ->message(esc_html__("This product is not purchasable or not has enough stock", 'checkout-upsell-woocommerce'));
        }

        if (!empty($data['product_qty'])) {
            $validator->rule('numeric', 'product_qty')->message($messages['numeric']);
            $validator->rule('min', 'product_qty', 0.001)->message($messages['min']);
            $validator->rule('max', 'product_qty', 99999999999.999)->message($messages['max']);
        }

        $validator->rule('numeric', 'discount_value')->message($messages['numeric']);
        if (!in_array($data['discount_type'], ['free', 'no_discount'])) {
            $validator->rule('min', 'discount_value', 0.001)->message($messages['min']);
        }
        if ($data['discount_type'] == "percentage") {
            $validator->rule('max', 'discount_value', 100)->message($messages['max']);
        } elseif ($data['discount_type'] == "fixed_price" && $check_product && $product) {
            $price = Offer::getProductPrice($product);
            $validator->rule('max', 'discount_value', round($price, 3))->message($messages['max']);
        } else {
            $validator->rule('max', 'discount_value', 99999999999.999)->message($messages['max']);
        }

        if (!empty($data['limit'])) {
            $validator->rule('integer', 'limit')->message($messages['integer']);
            $validator->rule('min', 'limit', 1)->message($messages['min']);
            $validator->rule('max', 'limit', 99999999)->message($messages['max']);
        }
        if (!empty($data['limit_per_user'])) {
            $max_limit = !empty($data['limit']) ? $data['limit'] : 99999999;
            $validator->rule('integer', 'limit_per_user')->message($messages['integer']);
            $validator->rule('min', 'limit_per_user', 1)->message($messages['min']);
            $validator->rule('max', 'limit_per_user', $max_limit)->message($messages['max']);
        }
        return $validator;
    }
}