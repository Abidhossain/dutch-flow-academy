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

namespace CUW\App\Controllers\Common;

defined('ABSPATH') || exit;

use CUW\App\Controllers\Controller;
use CUW\App\Helpers\Config;
use CUW\App\Models\Stats;
use CUW\App\Modules\Email\Reports;

class Cron extends Controller
{
    /**
     * To get schedules.
     *
     * @return array
     */
    public static function get()
    {
        return apply_filters('cuw_schedules', [
            'send_weekly_report' => [
                'title' => __('Send weekly reports', 'checkout-upsell-woocommerce'),
                'recurrence' => 'weekly',
                'day' => 'next monday',
                'at' => '10:00:00',
                'callback' => [__CLASS__, 'sendWeeklyReport'],
                'active' => true,
            ],
        ]);
    }

    /**
     * To schedule events.
     */
    public static function scheduleEvents()
    {
        foreach (self::get() as $key => $schedule) {
            $hook = 'cuw_' . $key;
            $args = !empty($schedule['args']) ? $schedule['args'] : [];
            if (!wp_next_scheduled($hook, $args) && $schedule['active']) {
                $time = !empty($schedule['day']) || !empty($schedule['at']) ? strtotime(($schedule['day'] ?? 'Y-m-d') . ' ' . ($schedule['at'] ?? '00:00:00')) : time();
                wp_schedule_event($time, $schedule['recurrence'], $hook, $args);
            }
        }
    }

    /**
     * To add hooks.
     */
    public static function handleEvents()
    {
        foreach (self::get() as $key => $schedule) {
            $accepted_args = !empty($schedule['args']) ? count($schedule['args']) : 1;
            add_action('cuw_' . $key, $schedule['callback'], 10, $accepted_args);
        }
    }

    /**
     * To unschedule events.
     */
    public static function unscheduleEvents()
    {
        foreach (self::get() as $key => $schedule) {
            $hook = 'cuw_' . $key;
            $args = !empty($schedule['args']) ? $schedule['args'] : [];
            if ($timestamp = wp_next_scheduled($hook, $args)) {
                wp_unschedule_event($timestamp, $hook, $args);
            }
        }
    }

    /**
     * Send weekly report to admin emails.
     */
    public static function sendWeeklyReport()
    {
        if (Config::getEmailSettings('weekly_report', 'enabled', 'yes') != 'yes') {
            return;
        }

        if (empty(Stats::getOrdersCount())) {
            return;
        }

        if (defined('WC_PLUGIN_FILE') && file_exists(WC_PLUGIN_FILE) . 'includes/emails/class-wc-email.php') {
            require_once plugin_dir_path(WC_PLUGIN_FILE) . 'includes/emails/class-wc-email.php';
            $email = new Reports();
            $email->sendMail();
        }
    }

    /**
     * To load email templates.
     *
     * @hooked woocommerce_email_classes
     */
    public static function loadEmailTemplates($emails)
    {
        if (defined('WC_PLUGIN_FILE') && file_exists(WC_PLUGIN_FILE) . 'includes/emails/class-wc-email.php') {
            include_once plugin_dir_path(WC_PLUGIN_FILE) . 'includes/emails/class-wc-email.php';
            $emails['cuw_reports_email'] = new Reports();
        }
        return $emails;
    }
}