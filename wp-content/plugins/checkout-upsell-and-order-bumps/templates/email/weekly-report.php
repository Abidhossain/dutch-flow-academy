<?php
/**
 * Weekly Reports template
 *
 * This template can be overridden by copying it to yourtheme/checkout-upsell-and-order-bumps/email/reports.php.
 *
 * HOWEVER, on occasion we will need to update template files and you (the theme developer) will need to copy the new files
 * to your theme to maintain compatibility. We try to do this as little as possible, but it does happen.
 */

defined('ABSPATH') || exit;

?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php esc_html_e('Upsells summary', 'checkout-upsell-and-order-bumps') ?></title>
</head>
<body style="background: #f7f7f7; padding: 20px 0; font-family: 'Helvetica Neue',Helvetica,Roboto,Arial,sans-serif;">
<table style="max-width: 600px; width: 100%; margin: 20px auto;">
    <tr><td>
        <table style="width: 100%;">
            <tr>
                <td style="font-size: 24px;font-weight: 600; color:#2563eb;">
                    <?php esc_html_e('UpsellWP', 'checkout-upsell-and-order-bumps'); ?>
                </td>
                <td style="float: right;">
                    <table>
                        <tr>
                            <td style="padding: 5px 6px; font-size: 14px; color:#4b5563;">
                                <?php esc_html_e('Weekly dashboard', 'checkout-upsell-and-order-bumps'); ?>
                            </td>
                            <td style="padding: 4px 6px; font-size: 14px; color:#4b5563;border-radius: 24px;background:white; border: 1px solid #e2e2e2;">
                                {report_from} - {report_to}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        </td>
    </tr>
    <tr>
        <td>
        <table style="border-spacing:0;  background-color: #ffffff; border-radius: 6px; border: 1px solid #e2e2e2;overflow: hidden;">
            <tr>
                <td style="padding: 16px 24px; border-bottom: 0.5px solid #e6e6e6;">
                    <table style="margin: 0;">
                        <tr>
                            <td style="padding: 0; background-color: #ffffff; width: 50%; ">
                                <div style="font-size: 20px; color: #0f172a; padding: 6px;">
                                    <span>&#128075;</span> <?php esc_html_e('Hey There!', 'checkout-upsell-and-order-bumps') ?>
                                </div>
                                <div style="font-size: 24px; font-weight: bold; padding: 6px; color: #0f172a; text-align: left;">
                                    <?php esc_html_e("Your site's upsells summary", 'checkout-upsell-and-order-bumps') ?>
                                </div>
                            </td>
                            <td style="padding: 0 24px; background-color: #ffffff; vertical-align: middle; color: #0f172a;">
                                <div style="padding: 4px; font-size: 16px; color: #9ca3af;">
                                    <?php esc_html_e('Below is a look at how your store earned via upsells in last week', 'checkout-upsell-and-order-bumps') ?>
                                </div>
                                <div style="padding: 4px;">
                                    <a style="color:#2563eb; font-size: 16px;" href="{site_url}">{site_url}</a>
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td style="padding: 24px; background-color: #ffffff; margin: 0 auto;">
                    <table style="color: #000000; margin: 0 auto; background-color: #ffffff; border-spacing: 0; text-align: center;">
                        <tr>
                            <td style=" background-color: #f1f5f9;  padding: 8px; vertical-align: middle; text-align: center; border-radius: 8px">
                                <table style="margin: 0; width: 100%;">
                                    <tr style="">
                                        <td style="font-size: 24px;width: 40%; text-align: center; color: #020617;">
                                            {total_revenue}
                                        </td>
                                        <td style="padding: 0; background-color: #f1f5f9; font-size: 18px; color: #0f172a;">
                                            <?php esc_html_e('Total Upsell Revenue', 'checkout-upsell-and-order-bumps') ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 4px; text-align: right; font-size: 14px; color: {total_revenue_color}; background-color: #f1f5f9; float: right">
                                            {total_revenue_percentage}
                                        </td>

                                        <td style="padding: 4px; font-size: 14px; background-color: #f1f5f9; color: #1e293b;">
                                             <?php esc_html_e('vs previous week', 'checkout-upsell-and-order-bumps') ?>
                                        </td>
                                    </tr>
                                </table>

                            </td>
                            <td style="padding: 6px; background-color: #ffffff;"></td>
                            <td style=" background-color: #f1f5f9;  padding: 8px; vertical-align: middle; text-align: center; border-radius: 8px">
                                <table style="margin: 0; width: 100%;">
                                    <tr style="">
                                        <td style="font-size: 24px;width: 40%; text-align: center; color: #020617;">
                                            {items_count}
                                        </td>
                                        <td style="padding: 0; font-size: 18px; background-color: #f1f5f9; color: #0f172a;">
                                            <?php esc_html_e('Total Products Purchased', 'checkout-upsell-and-order-bumps') ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 4px;text-align: right; font-size: 14px; color: {items_count_color}; background-color: #f1f5f9; float: right">
                                            {items_count_percentage}
                                        </td>
                                        <td style="padding: 4px;  font-size: 14px; background-color: #f1f5f9; color: #1e293b;">
                                            <?php esc_html_e('vs previous week', 'checkout-upsell-and-order-bumps') ?>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr style="">
                            <td style="padding: 6px;"></td>
                        </tr>
                        <tr>
                            <td style=" background-color: #f1f5f9;  padding: 8px; vertical-align: middle; text-align: center; border-radius: 8px">
                                <table style="margin: 0; width: 100%;">
                                    <tr style="">
                                        <td style="font-size: 24px; width: 40%; text-align: center; color: #020617;">
                                            {orders_count}
                                        </td>
                                        <td style="padding: 0; font-size: 18px; background-color: #f1f5f9; color: #0f172a;">
                                            <?php esc_html_e('Upsell Orders made', 'checkout-upsell-and-order-bumps') ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 4px;text-align: right; font-size: 14px; color: {orders_count_color}; background-color: #f1f5f9; float: right">
                                            {orders_count_percentage}
                                        </td>
                                        <td style="padding: 4px;font-size: 14px; background-color: #f1f5f9; color: #1e293b;">
                                            <?php esc_html_e('vs previous week', 'checkout-upsell-and-order-bumps') ?>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td style="padding: 6px; background-color: #ffffff;"></td>

                            <td style=" background-color: #f1f5f9;  padding: 8px; vertical-align: middle; text-align: center; border-radius: 8px">
                                <table style="margin: 0;width: 100%; ">
                                    <tr style="">
                                        <td style="font-size: 24px; width: 40%; text-align: center; color: #020617;">
                                            {conversion}
                                        </td>
                                        <td style="padding: 0; font-size: 18px; background-color: #f1f5f9; color: #0f172a;">
                                            <?php esc_html_e('Conversion rate', 'checkout-upsell-and-order-bumps') ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 4px; text-align: right; font-size: 14px; color: {conversion_color}; background-color: #f1f5f9; float: right">
                                            {conversion_percentage}
                                        </td>
                                        <td style="padding: 4px; font-size: 14px; background-color: #f1f5f9; color: #1e293b;">
                                            <?php esc_html_e('vs previous week', 'checkout-upsell-and-order-bumps') ?>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        </td>
    </tr>

</table>
{top_revenue_campaign}
</body>
</html>
