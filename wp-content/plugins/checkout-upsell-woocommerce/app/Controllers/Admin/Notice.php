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

namespace CUW\App\Controllers\Admin;

defined('ABSPATH') || exit;

use CUW\App\Controllers\Controller;
use CUW\App\Helpers\Config;
use CUW\App\Helpers\Functions;
use CUW\App\Helpers\WC;
use CUW\App\Helpers\WP;
use CUW\App\Models\Stats;

class Notice extends Controller
{
    /**
     * Days after show review notice.
     *
     * @var int
     */
    const DAYS = 30;

    /**
     * Revenue threshold.
     *
     * @var int
     */
    const REVENUE = 200;

    /**
     * To show the review notice.
     */
    public static function showReviewNotice()
    {
        $data = Config::get('review_notice_data');
        if (empty($data)) {
            $data = ['status' => '', 'timestamp' => 0];
        }
        if (!empty($data['status']) && $data['status'] == 'done') {
            return;
        }
        $revenue = self::getRevenue();
        if (!empty($revenue) && $revenue >= self::REVENUE) {
            $nonce = WP::createNonce('cuw_review_notice');
            $view_data = [
                'days' => self::DAYS,
                'revenue' => WC::formatPrice($revenue),
                'review_url' => 'https://wordpress.org/support/plugin/checkout-upsell-and-order-bumps/reviews/?filter=5',
                'later_url' => add_query_arg(['cuw_review_notice' => 1, 'cuw_action' => 'later', 'cuw_nonce' => $nonce]),
                'done_url' => add_query_arg(['cuw_review_notice' => 1, 'cuw_action' => 'done', 'cuw_nonce' => $nonce]),
            ];
            if (!empty($data['status']) && $data['status'] == 'later') {
                if (($data['timestamp'] + (7 * 24 * 60 * 60)) < (current_time('timestamp', true))) {
                    self::app()->view('Admin/Notices/Review', ['data' => $view_data]);
                }
            } else {
                self::app()->view('Admin/Notices/Review', ['data' => $view_data]);
            }
        }
    }

    /**
     * To handle review notice actions.
     */
    public static function handleReviewNoticeActions()
    {
        if (isset($_GET['cuw_review_notice'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $action = self::app()->input->get('cuw_action', '', 'query');
            $nonce = self::app()->input->get('cuw_nonce', '', 'query');
            if (!empty($action) && !empty($nonce) && WP::verifyNonce($nonce, 'cuw_review_notice')) {
                Config::set('review_notice_data', [
                    'status' => $action,
                    'timestamp' => current_time('timestamp', true),
                ]);
            }
            wp_safe_redirect(remove_query_arg('cuw_review_notice', remove_query_arg('cuw_action', remove_query_arg('cuw_nonce'))));
            exit;
        }
    }

    /**
     * To get revenue.
     */
    private static function getRevenue()
    {
        $revenue = get_transient('cuw_review_notice_revenue');
        if ($revenue === false) {
            $revenue = 0;
            if (Stats::getRecordsDateIntervalInSeconds() > (self::DAYS * 24 * 60 * 60)) {
                $currency = WC::getCurrency();
                $date_from = Functions::getDateByString('-' . self::DAYS . ' days', 'Y-m-d');
                $date_to = Functions::getDateByString('now', 'Y-m-d');
                $revenue = Stats::getRevenue('all', $date_from, $date_to, $currency);
            }
            set_transient('cuw_review_notice_revenue', $revenue, (24 * 60 * 60));
        }
        return $revenue;
    }
}