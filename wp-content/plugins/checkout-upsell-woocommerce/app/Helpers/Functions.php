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

class Functions
{
    /**
     * Check version
     *
     * @param string $current
     * @param string $required
     * @param string $operator
     * @return bool
     */
    public static function checkVersion($current, $required, $operator = null)
    {
        if ($required == "*") {
            return true;
        }
        return (bool)version_compare($current, $required, ($operator ?? '>='));
    }

    /**
     * Render template file
     *
     * @param string $file
     * @param array $data
     * @return false|string
     */
    public static function renderTemplate($file, $data = [])
    {
        if (file_exists($file)) {
            ob_start();
            extract($data);
            include $file;
            return ob_get_clean();
        }
        return false;
    }

    /**
     * Generate UUID
     *
     * @param int $length
     * @return string
     */
    public static function generateUuid($length = 8)
    {
        return substr(md5(uniqid()), -$length);
    }

    /**
     * Generate hash
     *
     * @param mixed $data
     * @return string
     */
    public static function generateHash($data)
    {
        return md5(serialize($data));
    }

    /**
     * Crop text
     *
     * @param string $text
     * @param int $length
     * @param string $append
     * @return string
     */
    public static function cropText($text, $length = 125, $append = '')
    {
        return strlen($text) > $length ? substr($text, 0, $length) . $append : $text;
    }

    /**
     * Get date by a date or time string.
     *
     * @param string $modifier
     * @param string $format
     * @return string|false
     */
    public static function getDateByString($modifier, $format = 'Y-m-d H:i:s')
    {
        try {
            $datetime = new \DateTime('now', wp_timezone());
            $datetime->modify($modifier);
            return $datetime->format($format);
        } catch (\Exception $e) {
            return false;
        }
    }
}