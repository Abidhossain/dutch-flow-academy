<?php
/**
 * Class Chalan_Review_Reminder
 */
class Challan_Notifications {
	/**
	 * Challan_Review_Reminder constructor.
	 */
	public function __construct() {
		$this->notifications_load_hooks();
	}

	/**
	 * Load all Notifications hooks.
	 */
	public function notifications_load_hooks() {

        if ( isset( $_GET['page'] ) && $_GET['page'] === 'webappick-woo-invoice' ) {//phpcs:ignore
          //  add_action( 'admin_notices', [ $this, 'woo_invoice_free_promotion_notice_christmas' ] );
        }

//		add_action( 'admin_notices', [ $this, 'woo_invoice_review_notice' ] );
		add_action( 'admin_notices', [ $this, 'woo_invoice_translation_request' ] );
		$countries = [ 'ar', 'ary' ];
		global $locale;
		if ( in_array($locale, $countries) ) {
			add_action( 'admin_notices', [ $this, 'woo_invoice_free_saudi_zatca_notice' ] );
		}

        // Retrieve the WooCommerce store country
        $store_base_location = get_option('woocommerce_default_country');
        // Split the country and state (if set)
        $split_location = explode(':', $store_base_location);
        $store_country = $split_location[0];

        // Get the site's current language
        $site_language = get_locale();

        // Define target countries and languages
        $target_countries = ['IN']; // India,
        $target_languages = ['hi_IN']; // languages

        // Check if the store's country is in the target countries OR the site's language is in the target languages
        if (in_array($store_country, $target_countries) || in_array($site_language, $target_languages)) {
            add_action('admin_notices', [$this, 'woo_invoice_free_gst_notice']);
        }

		add_action('wp_ajax_woo_invoice_save_review_notice', [ $this, 'woo_invoice_save_review_notice' ] );
		add_action('wp_ajax_woo_invoice_hide_notice', [ $this, 'woo_invoice_hide_notice' ] );

	}

    /**
	 * Translation notice action.
	 */
	public function woo_invoice_translation_request() {

//	    delete_option('woo_invoice_translation_notice_next_show_time');
//	    delete_user_meta('1', 'woo_invoice_translation_notice_dismissed');
//      update_option('woo_invoice_translation_notice_next_show_time', 12);

		$pluginName    = sprintf( '<b>%s</b>', esc_html__( 'Challan', 'webappick-pdf-invoice-for-woocommerce' ) );
		$has_notice    = false;
		$user_id       = get_current_user_id();
		$next_timestamp = get_option( 'woo_invoice_translation_notice_next_show_time' );
		$review_notice_dismissed = get_user_meta($user_id, 'woo_invoice_translation_notice_dismissed', true);
		$nonce         = wp_create_nonce( 'woo_invoice_notice_nonce' );
		if ( ! empty($next_timestamp) ) {
			if ( ( time() > $next_timestamp ) ) {
				$show_notice = true;
			}else {
				$show_notice = false;
			}
		} else {
			if ( isset($review_notice_dismissed) && ! empty($review_notice_dismissed) ) {
				$show_notice = false;
			}else {
				$show_notice = true;
			}
		}
		// translation Notice.
		if ( $show_notice ) {
			$has_notice = true;
			$languages = woo_invoice_get_default_languages();
			global $locale;
			$language = isset ( $languages[ $locale ] ) ? $languages[ $locale ] : "";
			$language_string = $language ? ' in <b>'. $language .'</b>.' : '.';
			$contact_link = '<a href="https://webappick.com/contact/" target="_blank" style="color:blue">here</a>'
			?>
            <div class="woo-invoice-notice notice notice-info is-dismissible" dir="<?php echo woo_invoice_is_rtl() ? 'ltr' : 'auto'?>" data-which="translate" data-nonce="<?php echo esc_attr( $nonce ); ?>">
                <p><?php
					printf(
                    // Translators: %1$s is the plugin name, %2$s is an image/logo, %3$s is a line break, %4$s is a language string, %5$s is a contact link.
                    esc_html__( ' %2$s  We are looking for people to translate this plugin%4$s If you can help we would love for you to jump in and contact with us %5$s, we will guide you. %3$s Thanks for using %1$s.', 'webappick-pdf-invoice-for-woocommerce' ),
						$pluginName, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						'<div class="woo-invoice-review-notice-logo"></div>',
						'<br/>',
						$language_string, //phpcs:ignore
						$contact_link //phpcs:ignore
					);
					?></p>
                <p>
                    <a class="button button-primary" data-response="translate" href="#" target="_blank"><?php esc_html_e( 'Translate Here', 'webappick-pdf-invoice-for-woocommerce' ); ?></a>
                </p>
                <style>

                </style>
            </div>

			<?php
		}

		if ( true === $has_notice ) {
			add_action( 'admin_print_footer_scripts', function() use ( $nonce ) {
				?>
                <script>
                    (function($){
                        "use strict";
                        $(document)
                            .on('click', '.woo-invoice-notice a.button', function (e) {
                                e.preventDefault();
                                // noinspection ES6ConvertVarToLetConst
                                let self = $(this);
                                self.closest(".woo-invoice-notice").slideUp( 200, 'linear' );

                                let  invoice_notice = self.closest('.woo-invoice-notice'), which = invoice_notice.attr('data-which');
                                wp.ajax.post( 'woo_invoice_hide_notice', { _wpnonce: '<?php echo esc_attr( $nonce ); ?>', which: which } );

                                let notice = self.attr('data-response');
                                if ( 'translate' === notice ) {
                                    window.open('https://translate.wordpress.org/projects/wp-plugins/webappick-pdf-invoice-for-woocommerce/', '_blank');
                                }
                            })

                            .on('click', '.woo-invoice-notice .notice-dismiss', function (e) {
                                e.preventDefault();
                                // noinspection ES6ConvertVarToLetConst
                                var self = $(this), invoice_notice = self.closest('.woo-invoice-notice'), which = invoice_notice.attr('data-which');
                                wp.ajax.post( 'woo_invoice_hide_notice', { _wpnonce: '<?php echo esc_attr( $nonce ); ?>', which: which } );
                            });

						<?php if ( woo_invoice_is_rtl() ) { ?>
                        setTimeout(function () {
                            $('.notice-dismiss').css('left', '97%');
                        },100)
						<?php } ?>
                    })(jQuery)
                </script><?php
			}, 99 );
		}
	}

	/**
	 * Review notice action.
	 */
	public function woo_invoice_review_notice() {

//	    delete_option('woo_invoice_review_notice_next_show_time');
//	    delete_user_meta('1', 'woo_invoice_review_notice_dismissed');
//      update_option('woo_invoice_review_notice_next_show_time', 12);

		$pluginName    = sprintf( '<b>%s</b>', esc_html__( 'Challan', 'webappick-pdf-invoice-for-woocommerce' ) );
		$has_notice    = false;
		$user_id       = get_current_user_id();
		$next_timestamp = get_option('woo_invoice_review_notice_next_show_time');
		$review_notice_dismissed = get_user_meta( $user_id, 'woo_invoice_review_notice_dismissed', true );
		$nonce         = wp_create_nonce( 'woo_invoice_notice_nonce' );

		if ( ! empty($next_timestamp) ) {
			if ( ( time() > $next_timestamp ) ) {
				$show_notice = true;
			}else {
				$show_notice = false;
			}
		} else {
			if ( isset($review_notice_dismissed) && ! empty($review_notice_dismissed) ) {
				$show_notice = false;
			}else {
				$show_notice = true;
			}
		}
		// Review Notice.
		if ( $show_notice ) {
			$has_notice = true;
			?>
            <div class="woo-invoice-notice notice notice-info is-dismissible" style="line-height:1.5;" data-which="rating" data-nonce="<?php echo esc_attr( $nonce ); ?>">
                <p><?php
					printf(
					/* translators: 1: plugin name,2: Slightly Smiling Face (Emoji), 3: line break 'br' tag */
						esc_html__( '%3$s %2$s We have spent countless hours developing this free plugin for you, and we would really appreciate it if you drop us a quick rating. Your opinion matters a lot to us.%4$s It helps us to get better. Thanks for using %1$s.', 'webappick-pdf-invoice-for-woocommerce' ),
						$pluginName, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						'<span style="font-size: 16px;">&#128516</span>',
						'<div class="woo-invoice-review-notice-logo"></div>',
						'<br>'
					);
					?></p>
                <p>
                    <a class="button button-primary" data-response="given" href="#" target="_blank"><?php esc_html_e( 'Review Now', 'webappick-pdf-invoice-for-woocommerce' ); ?></a>
                    <a class="button button-secondary" data-response="later" href="#"><?php esc_html_e( 'Remind Me Later', 'webappick-pdf-invoice-for-woocommerce' ); ?></a>
                    <a class="button button-secondary" data-response="done" href="#" target="_blank"><?php esc_html_e( 'Already Done!', 'webappick-pdf-invoice-for-woocommerce' ); ?></a>
                    <a class="button button-secondary" data-response="never" href="#"><?php esc_html_e( 'Never Ask Again', 'webappick-pdf-invoice-for-woocommerce' ); ?></a>
                </p>
            </div>
			<?php
		}

		if ( true === $has_notice ) {
			add_action( 'admin_print_footer_scripts', function() use ( $nonce ) {
				?>
                <script>
                    (function($){
                        "use strict";
                        $(document)
                            .on('click', '.woo-invoice-notice a.button', function (e) {
                                e.preventDefault();
                                // noinspection ES6ConvertVarToLetConst
                                var self = $(this), notice = self.attr('data-response');
                                if ( 'given' === notice ) {
                                    window.open('https://wordpress.org/support/plugin/webappick-pdf-invoice-for-woocommerce/reviews/?rate=5#new-post', '_blank');
                                }
                                console.log(self)
                                self.closest(".woo-invoice-notice").slideUp( 200, 'linear' );
                                wp.ajax.post( 'woo_invoice_save_review_notice', { _ajax_nonce: '<?php echo esc_attr( $nonce ); ?>', notice: notice } );
                            })



                            .on('click', '.woo-invoice-notice .notice-dismiss', function (e) {
                                e.preventDefault();
                                // noinspection ES6ConvertVarToLetConst
                                var self = $(this), invoice_notice = self.closest('.woo-invoice-notice'), which = invoice_notice.attr('data-which');
                                wp.ajax.post( 'woo_invoice_hide_notice', { _wpnonce: '<?php echo esc_attr( $nonce ); ?>', which: which } );
                            });
                    })(jQuery)
                </script><?php
			}, 99 );
		}

	}


	/**
	 * Zatca implementation.
	 */
	public function woo_invoice_free_saudi_zatca_notice() {
//      delete_option( 'woo_invoice_free_saudi_zatca_notice');
		$has_notice = false;
		if ( get_option('woo_invoice_free_saudi_zatca_notice') != 'showen_zatca_banner' ) {
			$has_notice = true;

			$nonce         = wp_create_nonce( 'woo_invoice_notice_nonce' );
			$pluginName    = sprintf( '<b>%s</b>', esc_html( 'Challan' ) );

			$image_url = CHALLAN_FREE_PLUGIN_URL . 'admin/images/zatca-challan-banner.png'
			?>
            <div class="woo-invoice-notice notice notice-info is-dismissible price_update" style="line-height:1.5;" data-which="zatca_close" data-nonce="<?php echo esc_attr( $nonce ); ?>">
                <p><?php
					printf(
					/* translators: 1: plugin name,2: Slightly Smiling Face (Emoji), 3: line break 'br' tag */
						'<a class="woo_invoice_promotion_notice" href="https://webappick.com/plugin/woocommerce-pdf-invoice-packing-slips/?utm_source=customer_site&utm_medium=free_vs_pro&utm_campaign=woo_invoice_free" target="_blank"><img  src="'.$image_url.'" alt="Challan_Free_Price"></a>', //phpcs:ignore;
						$pluginName, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						'<span style="font-size: 16px;">&#128516</span>',
						'<div class="woo-invoice-review-notice-logo"></div>',
						'<br>'
					);
					?></p>
            </div>
			<?php

			if ( true === $has_notice ) {
				add_action( 'admin_print_footer_scripts', function() use ( $nonce ) {
					?>
                    <script>
                        (function($){
                            "use strict";
                            $(document)
                                .on('click', '.woo-invoice-notice .notice-dismiss', function (e) {
                                    e.preventDefault();
                                    // noinspection ES6ConvertVarToLetConst
                                    var self = $(this), invoice_notice = self.closest('.woo-invoice-notice'), which = invoice_notice.attr('data-which');
                                    console.log(invoice_notice.attr('data-which'))
                                    wp.ajax.post( 'woo_invoice_hide_notice', { _wpnonce: '<?php echo esc_attr( $nonce ); ?>', which: which } );
                                });
                        })(jQuery)
                    </script><?php
				}, 99 );
			}
		}

	}

    /**
     * GST Invoice implementation.
     */
    public function woo_invoice_free_gst_notice() {
        $has_notice = false;
        if (get_option('woo_invoice_free_gst_notice') != 'shown_gst_banner') {
            $has_notice = true;

            $nonce = wp_create_nonce('woo_invoice_notice_nonce');
            $pluginName = sprintf('<b>%s</b>', esc_html('Challan'));

            $image_url = CHALLAN_FREE_PLUGIN_URL . 'admin/images/gst-challan-banner.png';
            ?>
            <div class="woo-invoice-notice notice notice-info is-dismissible price_update" style="line-height:1.5;" data-which="gst_close" data-nonce="<?php echo esc_attr($nonce); ?>">
                <p><?php
                    printf(
                        '<a class="woo_invoice_promotion_notice" href="https://webappick.com/plugin/woocommerce-pdf-invoice-packing-slips/?utm_source=customer_site&utm_medium=free_vs_pro&utm_campaign=woo_invoice_free" target="_blank"><img  src="' . $image_url . '" alt="Challan_Free_Price"></a>', //phpcs:ignore;
                        $pluginName, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                        '<span style="font-size: 16px;">&#128516</span>',
                        '<div class="woo-invoice-review-notice-logo"></div>',
                        '<br>'
                    );
                    ?></p>
            </div>
            <?php

            if (true === $has_notice) {
                add_action('admin_print_footer_scripts', function() use ($nonce) {
                    ?>
                    <script>
                        (function($){
                            "use strict";
                            $(document)
                                .on('click', '.woo-invoice-notice .notice-dismiss', function (e) {
                                    e.preventDefault();
                                    var self = $(this), invoice_notice = self.closest('.woo-invoice-notice'), which = invoice_notice.attr('data-which');
                                    console.log(invoice_notice.attr('data-which'))
                                    wp.ajax.post('woo_invoice_hide_notice', { _wpnonce: '<?php echo esc_attr($nonce); ?>', which: which });
                                });
                        })(jQuery)
                    </script><?php
                }, 99);
            }
        }
    }



    /**
	 * Black friday implementation.
	 */
	public function woo_invoice_free_promotion_notice_christmas() {

//        delete_user_meta( 1, 'woo_invoice_promotion_notice_dismissed');

		$image_url = CHALLAN_FREE_PLUGIN_URL . 'admin/images/christmas-banner-24.png';
		$pluginName    = sprintf( '<b>%s</b>', esc_html( 'Challan' ) );
		$user_id       = get_current_user_id();
		$review_notice_dismissed = get_user_meta($user_id, 'woo_invoice_promotion_notice_dismissed_christmas', true);
		$nonce         = wp_create_nonce( 'woo_invoice_notice_nonce' );

        if ( isset($review_notice_dismissed) && ! empty($review_notice_dismissed) ) {
            $show_notice = false;
        }else {
            $show_notice = true;
        }

		if ( $show_notice ) {
			?>
            <div class="woo-invoice-notice notice notice-info is-dismissible price_update" style="line-height:1.5;" data-which="promotion_2024_close" data-nonce="<?php echo esc_attr( $nonce ); ?>">
                <p><?php
					printf(
					/* translators: 1: plugin name,2: Slightly Smiling Face (Emoji), 3: line break 'br' tag */
						'<a class="woo_invoice_promotion_notice" href="https://webappick.com/plugin/woocommerce-pdf-invoice-packing-slips/?utm_source=challan+Plugin&utm_medium=HH_banner&utm_campaign=HH_24&utm_id=2024" target="_blank"><img  src="'.$image_url.'" alt="Challan_Free_Price"></a>', //phpcs:ignore
						$pluginName, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						'<span style="font-size: 16px;">&#128516</span>',
						'<div class="woo-invoice-review-notice-logo"></div>',
						'<br>'
					);
					?></p>
            </div>
            <style>
                /* Custom CSS to change the dismiss button color */
                .woo-invoice-notice .notice-dismiss:before {
                    color: #fff !important;
                }
            </style>
			<?php

			if ( $show_notice ) {
				add_action( 'admin_print_footer_scripts', function() use ( $nonce ) {
					?>
                    <script>
                        (function($){
                            "use strict";
                            $(document)
                                .on('click', '.woo-invoice-notice .notice-dismiss', function (e) {
                                    e.preventDefault();
                                    // noinspection ES6ConvertVarToLetConst
                                    var self = $(this), invoice_notice = self.closest('.woo-invoice-notice'), which = invoice_notice.attr('data-which');
                                    console.log(invoice_notice.attr('data-which'))
                                    wp.ajax.post( 'woo_invoice_hide_notice', { _wpnonce: '<?php echo esc_attr( $nonce ); ?>', which: which } );
                                });
                        })(jQuery)
                    </script><?php
				}, 99 );
			}
		}

	}




	/**
	 * Show Review request admin notice
	 * @return string
	 */
	public function woo_invoice_save_review_notice() {
		check_ajax_referer( 'woo_invoice_notice_nonce' );
		$user_id = get_current_user_id();
		update_option('review_test', 'review');
		$review_actions = [ 'later', 'never', 'done', 'given' ];
		if ( isset( $_POST['notice'] ) && ! empty( $_POST['notice'] ) && in_array( $_POST['notice'], $review_actions ) ) {
			$value  = [
				'review_notice' => sanitize_text_field( $_POST['notice'] ), //phpcs:ignore
				'updated_at'    => time(),
			];
			if ( 'never' === $_POST['notice'] || 'done' === $_POST['notice'] || 'given' === $_POST['notice'] ) {

				add_user_meta( $user_id, 'woo_invoice_review_notice_dismissed', true, true );

				update_option( 'woo_invoice_review_notice_next_show_time', 0 );

			}elseif ( 'later' == $_POST['notice'] ) {

				add_user_meta( $user_id, 'woo_invoice_review_notice_dismissed', true, true );

				update_option( 'woo_invoice_review_notice_next_show_time', time() + ( DAY_IN_SECONDS * 30 ) );
			}
			update_option( 'woo_invoice_review_notice', $value );
			wp_send_json_success( $value );
			wp_die();
		}
		wp_send_json_error( esc_html__( 'Invalid Request.', 'webappick-pdf-invoice-for-woocommerce' ) );
		wp_die();
	}
	/**
	 * Ajax Action For Hiding Compatibility Notices
	 */
	public function woo_invoice_hide_notice() {
		check_ajax_referer( 'woo_invoice_notice_nonce' );
		$notices = [ 'rp-wcdpd', 'wpml', 'rating', 'product_limit', 'promotion_2024_close', 'zatca_close','gst_close', 'translate' ];
		if ( isset( $_REQUEST['which'] ) && ! empty( $_REQUEST['which'] ) && in_array( $_REQUEST['which'], $notices ) ) {
			$user_id = get_current_user_id();

			if ( 'rating' == $_REQUEST['which'] ) {
				$updated_user_meta = add_user_meta( $user_id, 'woo_invoice_review_notice_dismissed', true, true );
				update_option( 'woo_invoice_review_notice_next_show_time', time() + ( DAY_IN_SECONDS * 30 ) );
			}elseif ( 'zatca_close' == $_REQUEST['which'] ) {
                update_option( 'woo_invoice_free_saudi_zatca_notice', 'showen_zatca_banner' );
            }elseif ( 'gst_close' == $_REQUEST['which'] ) {
                update_option( 'woo_invoice_free_gst_notice', 'shown_gst_banner' );
            }elseif ( 'promotion_2024_close' == $_REQUEST['which'] ) {
				add_user_meta($user_id, 'woo_invoice_promotion_notice_dismissed_christmas', true, true);
			}elseif ( 'translate' == $_REQUEST['which'] ) {
				update_option( 'woo_invoice_translation_notice_next_show_time', time() + ( DAY_IN_SECONDS * 30 )  );
				add_user_meta($user_id, 'woo_invoice_translation_notice_dismissed', true, true);
			}

			if ( isset($updated_user_meta ) && $updated_user_meta ) {
				wp_send_json_success( esc_html__( 'Request Successful.', 'webappick-pdf-invoice-for-woocommerce' ) );
			}else {
				wp_send_json_error( esc_html__( 'Something is wrong.', 'webappick-pdf-invoice-for-woocommerce' ) );
			}
			wp_die();
		}
		wp_send_json_error( esc_html__( 'Invalid Request.', 'webappick-pdf-invoice-for-woocommerce' ) );
		wp_die();
	}

}

new Challan_Notifications();
