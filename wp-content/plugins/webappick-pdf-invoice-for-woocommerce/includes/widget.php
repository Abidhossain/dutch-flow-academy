<?php

/**
 * This file contain available Webappick blog pdf-invoices
 *
 * @package Woo_Invoice_Pro
 * Created by PhpStorm.
 * User: wahid
 * Date: 5/22/20
 * Time: 9:02 PM
 */

if (!function_exists('woo_invoice_add_dashboard_widgets')) {
	/**
	 * Add a widget to the dashboard.
	 *
	 * This function is hooked into the 'wp_dashboard_setup' action below.
	 */
	function woo_invoice_add_dashboard_widgets()
	{
		global $wp_meta_boxes;

		add_meta_box('aaaa_webappick_latest_news_dashboard_widget', __('Latest News from WebAppick Blog', 'webappick-pdf-invoice-for-woocommerce'), 'woo_invoice_dashboard_widget_render', 'dashboard', 'side', 'high');
	}
	add_action('wp_dashboard_setup', 'woo_invoice_add_dashboard_widgets', 1);
}

if (!function_exists('woo_invoice_dashboard_widget_render')) {
	/**
	 * Function to get dashboard widget data.
	 */
	function woo_invoice_dashboard_widget_render()
	{

		// Initialize variable.
		$allposts = '';

		// Enter the name of your blog here followed by /wp-json/wp/v2/posts and add filters like this one that limits the result to 2 posts.
		$response = wp_remote_get('https://webappick.com/wp-json/wp/v2/posts?per_page=5&_fields=parent,title,link,id,modified,excerpt');

		// Exit if error.
		if (is_wp_error($response)) {
			return;
		}

		// Get the body.
		$posts = json_decode(wp_remote_retrieve_body($response));

		// Exit if nothing is returned.
		if (empty($posts)) {
			return;
		}
		?>
        <p> <a style="text-decoration: none;font-weight: bold;" href="<?php echo esc_url('https://webappick.com'); ?>" target=_balnk><?php echo esc_html__('WEBAPPICK.COM', 'webappick-pdf-invoice-for-woocommerce'); ?></a></p>
        <hr>
		<?php

		$woo_invoice_pro_image = plugin_dir_url(dirname(__FILE__)) . 'admin/images/challan-pro-logo-banner.png';

		$column_one = [
			esc_html__('Paid Stamp, QR & BAR Code ', 'webappick-pdf-invoice-for-woocommerce'),
			esc_html__('Image,Signature & Watermark', 'webappick-pdf-invoice-for-woocommerce'),
			esc_html__('30-Day Money Back Guarantee', 'webappick-pdf-invoice-for-woocommerce'),

		];
		$column_two = [
			esc_html__('Multiple Tax Classes Support', 'webappick-pdf-invoice-for-woocommerce'),
			esc_html__('Multilingual Invoice', 'webappick-pdf-invoice-for-woocommerce'),
			esc_html__('ZATCA-compliant(Saudi Arabia)', 'webappick-pdf-invoice-for-woocommerce'),
		];
		?>
        <div class="woo-pdf-invoice-widget-banner">
            <div class="woo-pdf-invoice-widget-banner-image">
                <img src='<?php echo esc_url($woo_invoice_pro_image); // phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage
                ?>' alt="">

            </div>
            <div class="woo-pdf-invoice-widget-banner-heading"><?php echo esc_html__('Unlock Exclusive Features for PDF Invoice & Packing Slip!', 'webappick-pdf-invoice-for-woocommerce') ?></div>
            <div class="woo-pdf-invoice-widget-banner-list">
                <div class="woo-pdf-invoice-widget-list-item first">
                    <ul>
						<?php foreach ($column_one as $value) : ?>
                            <li class="woo-pdf-invoice-widget-item">
                                <div>
                                    <svg stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 20 20" height="13px" width="13px" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <span><?php echo esc_html( $value ); ?></span>
                            </li>
						<?php endforeach; ?>
                    </ul>
                </div>
                <div class="woo-pdf-invoice-widget-list-item">
                    <ul>
						<?php foreach ($column_two as $value) : ?>
                            <li class="woo-pdf-invoice-widget-item">
                                <div>
                                    <svg stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 20 20" height="13px" width="13px" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <span><?php echo esc_html( $value ); ?></span>
                            </li>
						<?php endforeach; ?>
                    </ul>
                </div>

            </div>
            <div class="woo-pdf-invoice-widget-footer">
                <div class="woo-pdf-invoice-widget-button">
                    <a href="<?php echo esc_url('https://webappick.com/plugin/woocommerce-pdf-invoice-packing-slips/?utm_source=customer_site&utm_medium=das_banner&utm_campaign=woo_invoice_free') ?>" target="_blank"><?php echo esc_html__('Get Your Challan Pro', 'webappick-pdf-invoice-for-woocommerce'); ?> <svg stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 16 16" height="1em" width="1em" xmlns="http://www.w3.org/2000/svg">
                            <path d="m12.14 8.753-5.482 4.796c-.646.566-1.658.106-1.658-.753V3.204a1 1 0 0 1 1.659-.753l5.48 4.796a1 1 0 0 1 0 1.506z"></path>
                        </svg></a>
                </div>
            </div>
        </div>

		<?php
		// If there are posts.
		if (!empty($posts)) {
			// For each post.
			foreach ($posts as $post) {
				$fordate = gmdate('M j, Y', strtotime($post->modified));
				?>
                <p class="webappick-pdf-invoices"> <a style="text-decoration: none;" href="<?php echo esc_url($post->link); ?>" target=_balnk><?php echo esc_html($post->title->rendered); ?></a> - <?php echo esc_html($fordate); ?></p>
                <span><?php echo esc_html(wp_trim_words($post->excerpt->rendered, 100, '...')); ?></span>
				<?php
			}
			?>
            <hr>
            <p> <a style="text-decoration: none;" href="<?php echo esc_url('https://webappick.com/blog/'); ?>" target=_balnk><?php echo esc_html__('Get more woocommerce tips & news on our blog...', 'webappick-pdf-invoice-for-woocommerce'); ?></a></p>
			<?php
		}
	}
}
