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

namespace CUW\App\Modules\Email;

defined('ABSPATH') || exit;

use CUW\App\Helpers\Functions;
use CUW\App\Helpers\WC;
use CUW\App\Helpers\WP;
use CUW\App\Models\Stats;

class Reports extends \WC_Email
{
    /**
     * Load email properties.
     */
    function __construct()
    {
        $this->id = 'cuw_weekly_report';
        $this->customer_email = false;
        $this->title = __('Upsells summary', 'checkout-upsell-and-order-bumps');
        $this->description = esc_html__('This email is help to send an upsells summary of the week to admin.', 'checkout-upsell-and-order-bumps');
        $this->email_type = 'html';
        $this->template_html = 'email/weekly-report.php';

        parent::__construct();
        $this->recipient = $this->get_option('recipient', get_option('admin_email'));
        $this->template_base = CUW_PLUGIN_PATH . 'templates/';
    }

    /**
     * Get email subject.
     *
     * @return string
     */
    public function get_default_subject()
    {
        return esc_html__('[{site_title}]: Upsells summary', 'checkout-upsell-and-order-bumps');
    }

    /**
     * get the template With the data
     *
     * @return string
     */
    public function get_content_html()
    {
        $previous_week_date = [
            'from' => Functions::getDateByString('last week monday', 'Y-m-d'),
            'to' => Functions::getDateByString('last sunday', 'Y-m-d'),
        ];

        $previous_of_previous_week_date = [
            'from' => Functions::getDateByString('last week monday -7 days', 'Y-m-d'),
            'to' => Functions::getDateByString('last sunday -7 days', 'Y-m-d'),
        ];

        $previous_week_data = Stats::getUpsellInfo('dashboard', WC::getCurrency(), 'all', 'custom', $previous_week_date, false);
        $previous_of_previous_week_data = Stats::getUpsellInfo('dashboard', WC::getCurrency(), 'all', 'custom', $previous_of_previous_week_date, false);

        $total_revenue_percentage = Stats::getDiffPercentage($previous_of_previous_week_data['html']['revenue'], $previous_week_data['html']['revenue']);
        $items_count_percentage = Stats::getDiffPercentage($previous_of_previous_week_data['html']['items'], $previous_week_data['html']['items']);
        $orders_count_percentage = Stats::getDiffPercentage($previous_of_previous_week_data['html']['orders'], $previous_week_data['html']['orders']);
        $conversion_percentage = Stats::getDiffPercentage($previous_of_previous_week_data['html']['conversion_percentage'], $previous_week_data['html']['conversion_percentage']);

        $top_revenue_html = '';
        $top_revenue_campaigns = Stats::getTopRevenueCampaign($previous_week_date);
        if (!empty($top_revenue_campaigns)) {
            foreach ($top_revenue_campaigns as &$top_revenue_campaign) {
                if ($top_revenue_campaign['title'] === null) {
                    $top_revenue_campaign['title'] = __("(Deleted)", 'checkout-upsell-and-order-bumps');
                }
            }
            $top_revenue_html = CUW()->template('email/top-revenue-campaigns', ['top_revenue_campaigns' => $top_revenue_campaigns], false);
        }
        ob_start();
        $this->placeholders = array_merge(
            array(
                '{report_from}' => WP::formatDate($previous_week_date['from']),
                '{report_to}' => WP::formatDate($previous_week_date['to']),

                '{total_revenue}' => WC::formatPrice($previous_week_data['html']['revenue']),
                '{items_count}' => (int)$previous_week_data['html']['items'],
                '{orders_count}' => (int)$previous_week_data['html']['orders'],
                '{conversion}' => (int)$previous_week_data['html']['conversion_percentage'] . '%',

                '{total_revenue_percentage}' => ($previous_week_data['html']['revenue'] > $previous_of_previous_week_data['html']['revenue'] ? '&uarr;' : '&darr;') . str_replace('-', '', $total_revenue_percentage) . '%',
                '{items_count_percentage}' => ($previous_week_data['html']['items'] > $previous_of_previous_week_data['html']['items'] ? '&uarr;' : '&darr;') . str_replace('-', '', $items_count_percentage) . '%',
                '{orders_count_percentage}' => ($previous_week_data['html']['orders'] > $previous_of_previous_week_data['html']['orders'] ? '&uarr;' : '&darr;') . str_replace('-', '', $orders_count_percentage) . '%',
                '{conversion_percentage}' => ($previous_week_data['html']['conversion_percentage'] > $previous_of_previous_week_data['html']['conversion_percentage'] ? '&uarr;' : '&darr;') . str_replace('-', '', $conversion_percentage) . '%',

                '{total_revenue_color}' => $previous_week_data['html']['revenue'] > $previous_of_previous_week_data['html']['revenue'] ? 'green' : 'red',
                '{items_count_color}' => $previous_week_data['html']['items'] > $previous_of_previous_week_data['html']['items'] ? 'green' : 'red',
                '{orders_count_color}' => $previous_week_data['html']['orders'] > $previous_of_previous_week_data['html']['orders'] ? 'green' : 'red',
                '{conversion_color}' => $previous_week_data['html']['conversion_percentage'] > $previous_of_previous_week_data['html']['conversion_percentage'] ? 'green' : 'red',

                '{top_revenue_campaign}' => $top_revenue_html,
            ),
            $this->placeholders
        );
        wc_get_template($this->template_html, [], '', $this->template_base);
        return str_replace(PHP_EOL, '', $this->format_string(ob_get_clean()));
    }


    /**
     * To send mail
     *
     * @return void
     */
    public function sendMail()
    {
        $this->send($this->get_recipient(), $this->get_subject(), $this->get_content_html(), $this->get_headers(), $this->get_attachments());
    }

    /**
     * To set custom fields
     */
    public function init_form_fields()
    {
        // translators: %s placeholders.
        $placeholder_text = sprintf(__('Available placeholders: %s', 'checkout-upsell-and-order-bumps'), '<code>' . implode('</code>, <code>', array_keys($this->placeholders)) . '</code>');
        $this->form_fields = array(
            'enabled' => array(
                'title' => __('Enable/Disable', 'checkout-upsell-and-order-bumps'),
                'type' => 'checkbox',
                'label' => __('Enable this email notification', 'checkout-upsell-and-order-bumps'),
                'default' => 'yes',
            ),
            'recipient' => array(
                'title' => __('Recipient(s)', 'checkout-upsell-and-order-bumps'),
                'type' => 'text',
                // translators: %s email.
                'description' => sprintf(__('Enter recipients (comma separated) for this email. Defaults to %s.', 'checkout-upsell-and-order-bumps'), '<code>' . esc_attr(get_option('admin_email')) . '</code>'),
                'placeholder' => '',
                'default' => '',
                'desc_tip' => true,
            ),
            'subject' => array(
                'title' => __('Subject', 'checkout-upsell-and-order-bumps'),
                'type' => 'text',
                'desc_tip' => true,
                'description' => $placeholder_text,
                'placeholder' => $this->get_default_subject(),
                'default' => '',
            ),
            'email_type' => array(
                'title' => __('Email type', 'checkout-upsell-and-order-bumps'),
                'type' => 'select',
                'description' => __('Choose which format of email to send.', 'checkout-upsell-and-order-bumps'),
                'default' => 'html',
                'class' => 'email_type wc-enhanced-select',
                'options' => array(
                    'html' => __('HTML', 'checkout-upsell-and-order-bumps'),
                ),
                'desc_tip' => true,
            ),
        );
    }
}