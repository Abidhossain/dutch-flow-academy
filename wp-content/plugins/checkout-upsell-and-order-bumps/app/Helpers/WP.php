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

class WP
{
    /**
     * Add admin notice
     *
     * @param string $message
     * @param string $status
     * @return void
     */
    public static function adminNotice($message, $status = "success")
    {
        add_action('admin_notices', function () use ($message, $status) {
            ?>
            <div class="notice notice-<?php echo esc_attr($status); ?>">
                <p><?php echo wp_kses_post($message); ?></p>
            </div>
            <?php
        }, 1);
    }

    /**
     * Create nonce
     *
     * @param string $action
     * @return false|string
     */
    public static function createNonce($action = '')
    {
        if (empty($action)) {
            $action = 'cuw_nonce';
        }
        return wp_create_nonce($action);
    }

    /**
     * Verify nonce
     *
     * @param string $nonce
     * @param string $action
     * @return false
     */
    public static function verifyNonce($nonce, $action = '')
    {
        if (empty($action)) {
            $action = 'cuw_nonce';
        }
        return (bool)wp_verify_nonce($nonce, $action);
    }

    /**
     * Get format (datetime, date, time)
     *
     * @param string $type
     * @return string|false
     */
    public static function getFormat($type)
    {
        if ($type == 'datetime') {
            return get_option('date_format', 'Y-m-d') . ' ' . get_option('time_format', 'h:i:s');
        } elseif ($type == 'date') {
            return get_option('date_format', 'Y-m-d');
        } elseif ($type == 'time') {
            return get_option('time_format', 'h:i:s');
        }
        return false;
    }

    /**
     * Format date
     *
     * @param string|int $date
     * @param string $format
     * @param bool $is_gmt
     * @return string
     */
    public static function formatDate($date, $format = 'date', $is_gmt = false)
    {
        if (is_numeric($date)) {
            $date = gmdate('Y-m-d H:i:s', $date);
        }
        if (in_array($format, ['datetime', 'date', 'time'])) {
            $format = self::getFormat($format);
        }
        return $is_gmt ? get_date_from_gmt($date, $format) : gmdate($format, strtotime($date));
    }

    /**
     * Get the post id
     *
     * @return int
     */
    public static function getId()
    {
        return function_exists('get_the_ID') ? get_the_ID() : 0;
    }

    /**
     * Get the post title
     *
     * @param int $post_id
     * @return string
     */
    public static function getTitle($post_id)
    {
        return function_exists('get_the_title') ? get_the_title($post_id) : '';
    }

    /**
     * Get image
     *
     * @param int $image_id
     * @param int[]|string $size
     * @param bool $icon
     * @param array|string $attr
     * @return string
     */
    public static function getImage($image_id, $size = 'medium', $icon = false, $attr = [])
    {
        return wp_get_attachment_image($image_id, $size, $icon, $attr);
    }

    /**
     * Get available user roles
     *
     * @return array
     */
    public static function getUserRoles()
    {
        global $wp_roles;
        if (isset($wp_roles->roles)) {
            return $wp_roles->roles;
        }
        return [];
    }

    /**
     * Get user role
     *
     * @param object $user
     * @return array
     */
    public static function getRole($user)
    {
        if (!empty($user) && isset($user->user_login) && isset($user->roles)) {
            return $user->roles;
        }
        return [];
    }

    /**
     * To get Attachment image url.
     *
     * @param $attachment_id
     * @param $size
     * @param $icon
     * @return false|string
     */
    public static function getAttachmentImageUrl($attachment_id, $size = 'thumbnail', $icon = false)
    {
        if (function_exists('wp_get_attachment_image_url')) {
            return wp_get_attachment_image_url($attachment_id, $size, $icon);
        }
        return false;
    }

    /**
     * Get user
     *
     * @param int $id
     * @return object|null
     */
    public static function getUser($id)
    {
        return get_user_by('ID', $id);
    }

    /**
     * Get user's name
     *
     * @param int $id
     * @param string $name
     * @return string
     */
    public static function getUserName($id, $name = 'display')
    {
        $user = self::getUser($id);
        if (is_object($user)) {
            if ($name == 'display') {
                return isset($user->display_name) ? $user->display_name : '';
            } elseif ($name == 'nick') {
                return isset($user->nickname) ? $user->nickname : '';
            }
        }
        return false;
    }

    /**
     * Get current user
     *
     * @return object|null
     */
    public static function getCurrentUser()
    {
        return is_user_logged_in() ? self::getUser(self::getCurrentUserId()) : null;
    }

    /**
     * Get current user id
     *
     * @return int
     */
    public static function getCurrentUserId()
    {
        return function_exists('get_current_user_id') ? get_current_user_id() : 0;
    }

    /**
     * Get current page url
     *
     * @param bool $with_query
     * @param array $additional_params
     * @return string
     */
    public static function getCurrentPageUrl($with_query = true, $additional_params = [])
    {
        global $wp;
        // phpcs:disable
        return sanitize_url(home_url($wp->request) . ($with_query ? '?' . http_build_query(array_merge($_GET, $additional_params)) : ''));
        // phpcs:enable
    }

    /**
     * Returns the current theme slug.
     *
     * @return string
     */
    public static function getCurrentThemeSlug()
    {
        return function_exists('wp_get_theme') ? wp_get_theme()->get_template() : '';
    }

    /**
     * To get site information.
     *
     * @param string $key
     * @return string
     */
    public static function getSiteInfo($key)
    {
        return function_exists('get_bloginfo') ? get_bloginfo($key) : '';
    }

    /**
     * Check is admin or not
     *
     * @return bool
     */
    public static function isAdmin()
    {
        return function_exists('is_admin') && is_admin();
    }

    /**
     * Check is right-to-left (RTL) enabled or not
     *
     * @return bool
     */
    public static function isRtl()
    {
        return function_exists('is_rtl') && is_rtl();
    }

    /**
     * Check if the current request is AJAX
     *
     * @retun bool
     */
    public static function isAjax()
    {
        return function_exists('is_ajax') ? is_ajax() : defined('DOING_AJAX') && DOING_AJAX;
    }

    /**
     * Check if the current request is XML HTTP request
     *
     * @retun bool
     */
    public static function isXhr()
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower(sanitize_text_field(wp_unslash($_SERVER['HTTP_X_REQUESTED_WITH']))) == 'xmlhttprequest';
    }
}
