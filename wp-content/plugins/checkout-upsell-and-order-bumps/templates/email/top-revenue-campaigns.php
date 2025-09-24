<?php
/**
 * Top revenue campaigns template
 *
 * This template can be overridden by copying it to yourtheme/checkout-upsell-and-order-bumps/email/top-revenue-campaign.php.
 *
 * HOWEVER, on occasion we will need to update template files and you (the theme developer) will need to copy the new files
 * to your theme to maintain compatibility. We try to do this as little as possible, but it does happen.
 */

defined('ABSPATH') || exit;

if (!isset($top_revenue_campaigns)) {
    return;
}
?>

<div style="width: 100%; max-width: 600px; margin: 0 auto; font-family: 'Helvetica Neue',Helvetica,Roboto,Arial,sans-serif;" >
    <div style=" padding: 20px 0; border-radius: 6px;  background-color: #ffffff; border: 1px solid #e2e2e2;">
    <div style="text-align: center; font-size: 22px; padding-bottom: 12px; font-weight: 600; color: #0f172a;"><?php esc_html_e('Top 3 Campaigns by Revenue', 'checkout-upsell-and-order-bumps'); ?></div>
    <div style="margin: 8px auto ;width: 80%; border:1px solid #e2e2e2; border-bottom-width: 0; overflow: hidden; border-radius: 6px; ">
        <table style="border-collapse: collapse; width: 100%; border: 0; min-width: 280px; font-family: 'Helvetica Neue',Helvetica,Roboto,Arial,sans-serif;">
            <tr style="margin: 0 8px; font-size: 16px;">
                <th style="text-align: left;color: #4b5563; padding: 8px ; border-bottom: 1px solid #e2e2e2;">
                    <?php esc_html_e('Campaigns', 'checkout-upsell-and-order-bumps'); ?>
                </th>
                <th style="text-align: right; color: #4b5563;padding: 8px; border-bottom: 1px solid #e2e2e2;">
                    <?php esc_html_e('Revenue', 'checkout-upsell-and-order-bumps'); ?>
                </th>
            </tr>
            <?php foreach ($top_revenue_campaigns as $top_revenue_campaign) { ?>
                <tr style="margin: 0 8px; font-size: 16px;">
                    <td style="text-align: left; padding: 8px; border-bottom: thin solid #e2e2e2; color: #020617;">
                        <?php echo esc_html($top_revenue_campaign['title']); ?>
                    </td>
                    <td style="text-align: right; padding: 8px; border-bottom: thin solid #e2e2e2; color: #020617;">
                        <?php echo wp_kses_post(wc_price($top_revenue_campaign['revenue'])); ?>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </div>
    </div>
</div>
