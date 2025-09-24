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

use Valitron\Validator;

class Input
{
    /**
     * List of available input types.
     *
     * @var array
     */
    protected static $input_types = [
        'params',
        'query',
        'post',
        'cookie',
        'body',
    ];

    /**
     * List of available sanitize callbacks.
     *
     * @var array
     */
    protected static $sanitize_callbacks = [
        'text' => 'sanitize_text_field',
        'title' => 'sanitize_title',
        'email' => 'sanitize_email',
        'url' => 'sanitize_url',
        'key' => 'sanitize_key',
        'meta' => 'sanitize_meta',
        'option' => 'sanitize_option',
        'file' => 'sanitize_file_name',
        'mime' => 'sanitize_mime_type',
        'class' => 'sanitize_html_class',
        'html' => [__CLASS__, 'sanitizeHtml'],
        'content' => [__CLASS__, 'sanitizeContent'],
    ];

    /**
     * To hold allowed tags.
     *
     * @var array
     */
    private static $allowed_html;

    /**
     * Get sanitized input form request.
     *
     * @param string $var
     * @param mixed $default
     * @param string $type
     * @param string|false $sanitize
     * @return mixed
     */
    public static function get($var, $default = '', $type = 'params', $sanitize = 'text')
    {
        if (!in_array($type, self::$input_types)) {
            throw new \UnexpectedValueException('Expected a valid type on get method');
        }

        // phpcs:disable
        if ($type == 'params' && isset($_REQUEST[$var])) {
            return self::sanitize($_REQUEST[$var], $sanitize);
        } elseif ($type == 'query' && isset($_GET[$var])) {
            return self::sanitize($_GET[$var], $sanitize);
        } elseif ($type == 'post' && isset($_POST[$var])) {
            return self::sanitize($_POST[$var], $sanitize);
        } elseif ($type == 'cookie' && isset($_COOKIE[$var])) {
            return self::sanitize($_COOKIE[$var], $sanitize);
        } elseif ($type == 'body') {
            $body = file_get_contents('php://input');
            if (!empty($body) && !empty(trim($body))) {
                $body = trim(self::sanitize($body));
                if (!empty($var)) {
                    $data = (strpos($body, '{') !== false) ? json_decode($body, true) : [];
                    return is_array($data) && isset($data[$var]) ? $data[$var] : $default;
                }
                return $body;
            }
        }
        // phpcs:enable

        return $default;
    }

    /**
     * Sanitize inputs and values.
     *
     * @param string|array $value
     * @param string|false $type
     * @return string|array
     */
    public static function sanitize($value, $type = 'text')
    {
        if ($type === false) {
            return $value;
        }

        if (!array_key_exists($type, self::$sanitize_callbacks)) {
            throw new \UnexpectedValueException('Expected a valid type on sanitize method');
        }

        if (is_array($value)) {
            return self::sanitizeRecursively($value, self::$sanitize_callbacks[$type]);
        }
        return call_user_func(self::$sanitize_callbacks[$type], $value);
    }

    /**
     * Sanitize recursively
     *
     * @param array $array
     * @param string $callback
     * @return array
     */
    public static function sanitizeRecursively(&$array, $callback)
    {
        foreach ($array as &$value) {
            if (is_array($value)) {
                $value = self::sanitizeRecursively($value, $callback);
            } else {
                $value = call_user_func($callback, $value);
            }
        }
        return $array;
    }

    /**
     * Sanitize text and allow some basic HTML tags and attributes.
     *
     * @param string $value
     * @return string
     */
    public static function sanitizeHtml($value)
    {
        return self::filterHtml($value, self::getAllowedHtmlTags());
    }

    /**
     * Sanitize text and allow HTML without input tags and attributes.
     *
     * @param string $value
     * @return string
     */
    public static function sanitizeContent($value)
    {
        return wp_kses_post($value);
    }

    /**
     * Get allowed html tags.
     *
     * @return array
     */
    public static function getAllowedHtmlTags()
    {
        if (!isset(self::$allowed_html)) {
            self::$allowed_html = (array)apply_filters('cuw_allowed_html_elements_and_attributes', [
                'b' => [],
                'br' => [],
                'em' => [],
                'i' => [],
                'strong' => [],
                'u' => [],
            ]);
        }
        return self::$allowed_html;
    }

    /**
     * HTML filter.
     *
     * @param string $value
     * @param array $allowed_html
     * @return string
     */
    public static function filterHtml($value, $allowed_html = [])
    {
        return wp_kses($value, $allowed_html);
    }

    /**
     * Validator (vlucas/valitron)
     *
     * @link https://github.com/vlucas/valitron
     *
     * @param array $data
     * @return Validator
     */
    public static function validator($data)
    {
        return new Validator($data);
    }
}