<?php
/**
 * Automatic Generate PDF Invoice and attach  with order email for WooCommerce.
 *
 * @package  Woo_Invoice
 * Plugin Name:  Challan - PDF Invoice & Packing Slip for WooCommerce
 * Plugin URI:   https://webappick.com
 * Description:  Automatic Generate PDF Invoice and attach  with order email for WooCommerce.
 * Version:      3.7.71
 * Author:       WebAppick
 * Author URI:   https://webappick.com
 * License:      GPLv2
 * License URI:  https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:  webappick-pdf-invoice-for-woocommerce
 * Domain Path:  /languages
 * Requires Plugins: woocommerce
 * WP Requirement & Test
 * Requires at least: 4.4
 * Tested up to: 6.8
 * Requires PHP: 7.4
 * WC requires at least: 3.2
 * WC tested up to: 10.1
 **/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! function_exists( 'woo_invoice_is_uploads_folder_writable' ) ) {
    // is uploads folder writable
    function woo_invoice_is_uploads_folder_writable() {
        $upload_dir             = wp_upload_dir();
        $base_dir               = $upload_dir['basedir'];

        if ( wp_is_writable( $base_dir ) ) {
            return true;
        }
        return false;
    }
}

if ( ! defined( 'CHALLAN_FREE_ROOT_FILE' ) ) {
    /**
     * Plugin main file
     *
     * @since 3.3.25
     * @var string
     */
    define( 'CHALLAN_FREE_ROOT_FILE', __FILE__ );
}

if ( ! defined( 'CHALLAN_FREE_INVOICE_DIR' ) ) {
    /**
     * Custom Font Directory.
     *
     * @var string.
     * @since 2.3.1
     */
    $upload_dir        = wp_upload_dir();
    $base_dir          = $upload_dir['basedir'];
    $wpifw_invoice_dir = $base_dir."/WOO-INVOICE";
    define( 'CHALLAN_FREE_INVOICE_DIR', $wpifw_invoice_dir . '/' );
    if ( woo_invoice_is_uploads_folder_writable() ) {
        if ( ! file_exists(CHALLAN_FREE_INVOICE_DIR.'.htaccess' ) ) {
            mkdir( CHALLAN_FREE_INVOICE_DIR, 0777, true );
            // Protect files from public access.
            $content = 'deny from all';
            $fp = fopen(CHALLAN_FREE_INVOICE_DIR . '.htaccess', 'wb');
            fwrite($fp, $content);
            fclose($fp);
        }
    }
}

if ( ! defined( 'CHALLAN_FREE_FONT_DIR' ) ) {
	/**
	 * Custom Font Directory.
	 *
	 * @var string
	 * @since 2.3.1
	 */
	$upload_dir             = wp_upload_dir();
	$base_dir               = $upload_dir['basedir'];
	$wpifw_invoice_font_dir = CHALLAN_FREE_INVOICE_DIR."WOO-INVOICE-FONTS";
	define( 'CHALLAN_FREE_FONT_DIR', $wpifw_invoice_font_dir . '/' );
	if ( woo_invoice_is_uploads_folder_writable() ) {
        if ( ! file_exists(CHALLAN_FREE_FONT_DIR ) ) {
            mkdir( $wpifw_invoice_font_dir, 0777, true );
            // Protect files from public access.
            $content = 'deny from all';
            $fp      = fopen( CHALLAN_FREE_FONT_DIR . '.htaccess', 'wb' );
            fwrite( $fp, $content );
            fclose( $fp );
        }
	}
}

if ( ! defined( 'CHALLAN_FREE_ROOT_FILE_PATH' ) ) {
	/**
	 * Plugin Root Path with trailing slash
	 *
	 * @since 3.3.25
	 * @var string dirname( __FILE__ )
	 */
	define( 'CHALLAN_FREE_ROOT_FILE_PATH', plugin_dir_path( __FILE__ ) );
}


    // Load files
    require_once CHALLAN_FREE_ROOT_FILE_PATH . 'includes/constants.php';
    require_once CHALLAN_FREE_ROOT_FILE_PATH . 'includes/config.php';
    require_once CHALLAN_FREE_ROOT_FILE_PATH . 'includes/settings.php';
    require_once CHALLAN_FREE_ROOT_FILE_PATH . 'includes/functions.php';
    require_once CHALLAN_FREE_ROOT_FILE_PATH . 'includes/classes.php';
    require_once CHALLAN_FREE_ROOT_FILE_PATH . 'includes/activator.php';
    require_once CHALLAN_FREE_ROOT_FILE_PATH . 'includes/deactivator.php';
    require_once CHALLAN_FREE_ROOT_FILE_PATH . 'includes/uninstaller.php';
    function challan_free_load_file_upgrader(){
        require_once CHALLAN_FREE_ROOT_FILE_PATH . 'includes/upgrader.php';
    }

    // Hook into `init` to ensure files are only loaded after WordPress initialization.
    add_action('init', 'challan_free_load_file_upgrader');
    require_once CHALLAN_FREE_ROOT_FILE_PATH . 'includes/notices.php';
    require_once CHALLAN_FREE_ROOT_FILE_PATH . 'includes/filters.php';

//Load MPDF library

if ( function_exists('challan_load_mpdf_lib') ) :
challan_load_mpdf_lib();
endif;

require_once CHALLAN_FREE_ROOT_FILE_PATH . 'includes/rest-api/font-downloader-api.php';

/**
 * Webappick Service API
 */
require CHALLAN_FREE_ROOT_FILE_PATH . 'includes/class-woo-invoice-webappick-api.php';
/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-woo-invoice-activator.php
 */
function woo_invoice_activate() {
    woo_invoice_deactivate_pro();
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-woo-invoice-activator.php';
	Woo_Invoice_Activator::activate();
}

function woo_invoice_deactivate_pro() {
    if ( ! function_exists( 'deactivate_plugins' ) ) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }
    $woo_invoice_pro = 'webappick-pdf-invoice-for-woocommerce-pro/woo-invoice-pro.php';
    if ( is_plugin_active( $woo_invoice_pro ) ) {
        deactivate_plugins( $woo_invoice_pro );
    }

}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-woo-invoice-deactivator.php
 */
function woo_invoice_deactivate() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-woo-invoice-deactivator.php';
	Woo_Invoice_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'woo_invoice_activate' );
register_deactivation_hook( __FILE__, 'woo_invoice_deactivate' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require CHALLAN_FREE_ROOT_FILE_PATH . 'includes/class-woo-invoice.php';


/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function woo_invoice_run() {
    $plugin = new Woo_Invoice();
    $plugin->run();

    // Initialize your API instance during 'init' action.
    add_action('init', function() {
        Woo_Invoice_WebAppickAPI::get_instance();
    });

    // Use 'plugins_loaded' to ensure WordPress has loaded plugin functions.
    add_action('plugins_loaded', function() {
        // Check if WooCommerce is active before proceeding.
        if (function_exists('is_plugin_active') && is_plugin_active('woocommerce/woocommerce.php')) {
            // Hook for HPOS compatibility
            add_action('before_woocommerce_init', function () {
                if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
                    \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
                }
            });
        }
    });
}

woo_invoice_run();

// Pages.
if ( ! function_exists( 'woo_invoice_pro_vs_free' ) ) {
	/**
	 * Difference between free and premium plugin
	 */
	function woo_invoice_pro_vs_free() {
		require CHALLAN_FREE_ADMIN_PATH . 'partials/woo-invoice-pro-vs-free.php';
	}
}

/**
 * Load plugin docs from WEBAPPICK API.
 */
function woo_invoice_docs(){

    // Enter the name of your blog here followed by /wp-json/wp/v2/posts and add filters like this one that limits the result to 2 posts.
    $response = wp_remote_get( 'https://webappick.com/wp-json/wp/v2/docs/?parent=3960&_fields=parent,title,link,id' );

    // Exit if error.
    if ( is_wp_error( $response ) ) {
        return;
    }
    // Get the body.
    $posts = json_decode( wp_remote_retrieve_body( $response ) );
    // Exit if nothing is returned.
    if ( empty( $posts ) ) {
        return;
    }
    if ( ! empty( $posts ) ) {
        $new_posts = [ $posts[3], $posts[2], $posts[4], $posts[1], $posts[0] ];
        ?>
        <div class="_winvoice_docs">
            <?php foreach ( $new_posts as $post ) {
                $boxId = ( isset( $post->title->rendered ) ) ? sanitize_title( $post->title->rendered ) : '';
                $current_screen = get_current_screen();
                ?>
                <div id="<?php echo esc_attr( $boxId ); ?>" class="postbox <?php echo esc_attr( postbox_classes( $boxId, $current_screen->id ) ); ?>">
                    <button type="button" class="handlediv" aria-expanded="true">
                        <span class="screen-reader-text">
                            <?php
                            // Translators: %s is the title of the post.
                            printf( esc_html__( 'Toggle panel: %s', 'webappick-pdf-invoice-for-woocommerce' ), esc_html( $post->title->rendered ) );
                            ?>
                        </span>
                        <span class="toggle-indicator" aria-hidden="true"></span>
                    </button>
                    <h2 class="hndle">
                        <span class="dashicons dashicons-sos" aria-hidden="true"></span>
                        <span><?php echo esc_html( $post->title->rendered ); ?></span>
                    </h2>
                    <div class="inside">
                        <div class="main">
                            <?php
                            $response1 = wp_remote_get( 'https://webappick.com/wp-json/wp/v2/docs/?per_page=60&parent='.$post->id.'&_fields=parent,title,link,id,doc_tag' );
                            $posts1 = json_decode( wp_remote_retrieve_body( $response1 ) );
                            ?>
                            <ul>
                                <?php
                                // For each post.
                                foreach ( $posts1 as $post ) {
                                    ?>
                                    <li style="padding-bottom: 20px;">
                                        <span class="dashicons dashicons-media-text" aria-hidden="true"></span>
                                        <a href="<?php echo esc_url( $post->link ); ?>" style="font-size: 14px;line-height: 20px" target="_blank">
                                            <?php
                                            // Add "Pro" tag if feature is only for pro plugin.
                                            if ( in_array("4128", $post->doc_tag ) ) {
                                                echo esc_html( $post->title->rendered ) . '- <strong>Pro</strong>';
                                            }else {
                                                echo esc_html($post->title->rendered);
                                            }?></a>                                    </li>
                                <?php } ?>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div><!-- end ._winvoice_docs-->
        <?php
    }
}

/**
 *  Load Plugin settings page, process and save setting
 */
function woo_invoice_settings() {
	$invoice_allow  = 'wpifw_invoicing';
	$email_allow    = 'wpifw_order_email';
	$download_allow = 'wpifw_download';
	$currency_allow = 'wpifw_currency_code';
	$payment_method = 'wpifw_payment_method_show';
	$order_note     = 'wpifw_show_order_note';

	// Process settings tab form data and update.
	if ( isset( $_POST['wpifw_submit'] ) ) {

        // Security: Nonce verification
        $retrieved_nonce = isset( $_REQUEST['invoice_form_nonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['invoice_form_nonce'] ) ) : '';
        if ( ! wp_verify_nonce( $retrieved_nonce, 'invoice_form_nonce' ) ) {
            wp_die( esc_html__( 'Security check failed!', 'webappick-pdf-invoice-for-woocommerce' ) );
        }

        // Capability check: Only admins can proceed
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You are not allowed to perform this action.', 'webappick-pdf-invoice-for-woocommerce' ) );
        }

		// If checkbox is not checked then put empty value.
		if ( ! isset( $_POST[ $invoice_allow ] ) ||
			 ! isset( $_POST[ $email_allow ] ) ||
			 ! isset( $_POST[ $download_allow ] ) ||
			 ! isset( $_POST[ $currency_allow ] ) ||
			 ! isset( $_POST[ $payment_method ] )
			 || ! isset( $_POST[ $order_note ] ) ) {
			update_option( $invoice_allow, sanitize_textarea_field( '' ) );
			update_option( $email_allow, sanitize_textarea_field( '' ) );
			update_option( $download_allow, sanitize_textarea_field( '' ) );
			update_option( $currency_allow, sanitize_text_field( '' ) );
			update_option( $payment_method, sanitize_text_field( '' ) );
			update_option( $order_note, sanitize_text_field( '' ) );
		}

        // Allow to download invoice from my account base on order status.
        if ( isset( $_POST['wpifw_download'] ) && isset( $_POST['wpifw_invoice_download_check_list'] ) ) {
            $download_check_list = array();
            foreach ( $_POST['wpifw_invoice_download_check_list'] as $key => $value ) { //phpcs:ignore
                $download_check_list[ sanitize_text_field( $key ) ] = sanitize_text_field( $value );
            }
            update_option( 'wpifw_invoice_download_check_list', $download_check_list );
        } else {
            update_option( 'wpifw_invoice_download_check_list', array() );
        }
        // Attach Invoice with email based on order status.
        if ( isset( $_POST['wpifw_order_email'] ) && isset( $_POST['wpifw_email_attach_check_list'] ) ) {
            $email_check_list = array();
            foreach ( $_POST['wpifw_email_attach_check_list'] as $key => $value ) { //phpcs:ignore
                $email_check_list[ sanitize_text_field( $key ) ] = sanitize_text_field( $value );
            }
            update_option( 'wpifw_email_attach_check_list', $email_check_list );
        } else {
            update_option( 'wpifw_email_attach_check_list', array() );
        }

        // if order meta label is empty then related all array are empty.
        if ( ! array_key_exists('_winvoice_order_meta_label', $_POST) ) {
            update_option( '_winvoice_order_meta_label', array() );
            update_option( '_winvoice_order_meta_name', array() );
            update_option( '_winvoice_order_meta_name_position', array() );
        }

        // if order item  meta label is empty then related all array are empty.
        if ( ! array_key_exists('_winvoice_order_item_meta_label', $_POST) ) {
            update_option( '_winvoice_order_item_meta_label','' );
            update_option( '_winvoice_order_item_meta_name', '' );
        }

        // if order product  meta label is empty then related all array are empty.
        if ( ! array_key_exists('_winvoice_post_meta_label', $_POST) ) {
            update_option( '_winvoice_post_meta_label', '' );
            update_option( '_winvoice_post_meta_name', '' );
        }

        foreach ( $_POST as $key => $value ) { // phpcs:ignore

            if ( '_winvoice_order_meta_label' == $key ) {
                $label = array();
                $name = array();
                $place = array();
                $label_arr = isset($_POST['_winvoice_order_meta_label']) ? $_POST['_winvoice_order_meta_label'] : array(); //phpcs:ignore
                $name_arr = isset($_POST['_winvoice_order_meta_name']) ? $_POST['_winvoice_order_meta_name'] : array(); //phpcs:ignore
                $place_arr = isset($_POST['_winvoice_order_meta_name_position']) ? $_POST['_winvoice_order_meta_name_position'] : array(); //phpcs:ignore

                foreach ( $label_arr as $index => $val ) {

                    if ( '' != $val && '' != $name_arr[ $index ] && '' != $place_arr[ $index ] ) {
                        array_push($label, $val);
                        array_push($name, $name_arr[ $index ]);
                        array_push($place, $place_arr[ $index ]);
                    } else {
                        $label_arr[ $index ] = '';
                        $name_arr[ $index ] = '';
                        $place_arr[ $index ] = '';
                    }
                }

                if ( count($label) > 0 ) {
                    update_option($key, $label);
                    update_option('_winvoice_order_meta_name', $name);
                    update_option('_winvoice_order_meta_name_position', $place);
                } else {
                    update_option('_winvoice_order_meta_label', array());
                    update_option('_winvoice_order_meta_name', array());
                    update_option('_winvoice_order_meta_name_position', array());
                }
            }elseif ( '_winvoice_post_meta_name' == $key ) {

                $label = $_POST['_winvoice_post_meta_label']; //phpcs:ignore
                $name = $_POST['_winvoice_post_meta_name']; //phpcs:ignore
                if ( $name ) {
                    update_option('_winvoice_post_meta_label', $label);
                    update_option($key, $name);
                } else {
                    update_option('_winvoice_post_meta_label', '');
                    update_option('_winvoice_post_meta_name','');
                }
            }elseif ( strpos( $key, '_winvoice_order_item_meta_name' ) !== false ) {

                $label = $_POST['_winvoice_order_item_meta_label']; //phpcs:ignore
                $name = $_POST['_winvoice_order_item_meta_name']; //phpcs:ignore
                if ( $name ) {
                    update_option('_winvoice_order_item_meta_label', $label);
                    update_option('_winvoice_order_item_meta_name', $name);
                } else {
                    update_option('_winvoice_order_item_meta_label', '');
                    update_option('_winvoice_order_item_meta_name','');
                }
            }

            if ( 'wpifw_invoice_download_check_list' !== $key
                && 'wpifw_email_attach_check_list' !== $key
                && '_winvoice_order_meta_label' !== $key
                && '_winvoice_order_meta_name' !== $key
                && '_winvoice_order_meta_name_position' !== $key
                && '_winvoice_post_meta_label' !== $key
                && '_winvoice_post_meta_name' !== $key
                && '_winvoice_order_item_meta_label' !== $key
                && '_winvoice_order_item_meta_name' !== $key
            ) {
                update_option($key, sanitize_text_field($value));
            }
        }
    }

	// Process Seller & Buyer tab form data & update.
	if ( isset( $_POST['wpifw_submit_seller&buyer'] ) ) {
        $retrieved_nonce = isset( $_REQUEST['_wpnonce']) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '';
		if ( ! wp_verify_nonce( $retrieved_nonce, 'seller_form_nonce' ) ) {
			die( 'Failed security check' );
		}
		foreach ( $_POST as $key => $value ) {
			if ( 'wpifw_terms_and_condition' === $key || 'wpifw_other_information' === $key ) {
				update_option( $key, sanitize_textarea_field( wp_unslash( $value ) ) );
			} elseif ( 'wpifw_buyer' === $key || 'wpifw_cdetails' === $key ) {
				update_option( $key, sanitize_textarea_field( wp_unslash( $value ) ) );
			} elseif ( 'wpifw_buyer_shipping_address' === $key ) {
				update_option( $key, sanitize_textarea_field( wp_unslash( $value ) ) );
			} elseif ( 'wpifw_logo_attachment_id' === $key ) {
				$full_size_path = get_attached_file( $value );
				update_option( $key, $full_size_path );
				update_option( 'wpifw_logo_attachment_image_id', $value );
			} else {
				update_option( $key, sanitize_text_field( wp_unslash( $value ) ) );
			}
		}
	}

	// Process Localization tab form date and update.
	if ( isset( $_POST['wpifw_submit_localization'] ) ) {
		$retrieved_nonce = isset( $_REQUEST['_wpnonce']) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '';
		if ( ! wp_verify_nonce( $retrieved_nonce, 'localization_form_nonce' ) ) {
			die( 'Failed security check' );
		}
		foreach ( $_POST as $key => $value ) {
			update_option( $key, sanitize_text_field( wp_unslash( $value ) ) );
		}
	}

	// Process Batch Download Form data and update.
	if ( isset( $_POST['wpifw_submit_bulk_download'] ) ) {
        $retrieved_nonce = isset( $_REQUEST['_wpnonce']) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '';
		if ( ! wp_verify_nonce( $retrieved_nonce, 'bulk_download_form_nonce' ) ) {
			die( 'Failed security check' );
		}

		if ( isset( $_POST['wpifw_date_from'], $_POST['wpifw_date_to'] ) ) {
			$date_from = sanitize_text_field( wp_unslash( $_POST['wpifw_date_from'] ) );
			$date_to   = sanitize_text_field( wp_unslash( $_POST['wpifw_date_to'] ) );

			$args = array(
				'date_created' => $date_from . '...' . $date_to,
				'limit'        => - 1,
				'type'         => 'shop_order',
				'return'       => 'ids',
			);

			$order_ids = wc_get_orders( $args );

			if ( empty( $order_ids ) ) {
				$status = esc_html__( 'No order found with your given date range.', 'webappick-pdf-invoice-for-woocommerce' );
				wp_safe_redirect( add_query_arg( array( 'message' => $status ), admin_url( 'admin.php?page=webappick-woo-invoice' ) ) );
				exit();
			}

			$order_ids = implode( ',', $order_ids );

			$url   = wp_nonce_url( admin_url( 'admin-ajax.php' ), 'woo_invoice_ajax_nonce' );
			$param = array( 'order_ids' => $order_ids );

			// Bulk Download type checked and downloads the invoice and slip between the input dates.
			$bulk_type = isset( $_POST['wpifw_bulk_type'] ) ? sanitize_text_field( wp_unslash( $_POST['wpifw_bulk_type'] ) ) : 'WPIFW_INVOICE_DOWNLOAD';
			if ( 'WPIFW_INVOICE_DOWNLOAD' === $bulk_type ) {
				$param['action'] = 'wpifw_generate_invoice';
				wp_safe_redirect( add_query_arg( $param, $url ) );
				exit;
			} elseif ( 'WPIFW_PACKING_SLIP' === $bulk_type ) {
				$param['action'] = 'wpifw_generate_invoice_packing_slip';
				wp_safe_redirect( add_query_arg( $param, $url ) );
				exit;
			}
		}
	}

	// Start shipping label process.
	if ( isset( $_POST['wpifw_submit_delivery_address'] ) ) {
		// Verify Nonce.
		$retrieved_nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '';
		if ( ! wp_verify_nonce( $retrieved_nonce, 'delivery_address_nonce' ) ) {
			die( 'Failed security check' );
		}

		// Sanitize Inputs.
		$delivery_address = array();
		foreach ( $_POST as $key => $value ) {
			if ( 'wpifw_delivery_address_buyer' === $key ) {
				$delivery_address[ $key ] = sanitize_textarea_field( $value );
			} else {
				$delivery_address[ $key ] = sanitize_text_field( $value );
			}
		}
		update_option( 'wpifw_delivery_address_buyer', $delivery_address['wpifw_delivery_address_buyer'] );
	}
	// End Shipping label process

	// Load plugin settings view.
	require plugin_dir_path( __FILE__ ) . 'admin/partials/woo-invoice-settings.php';
}

/**
 * Add extra settings link in plugins page
 *
 * @param array $links Action links.
 *
 * @return array
 */
function woo_invoice_plugin_action_links( $links ) {
	$links[] = sprintf( '<a style="color:#8e44ad;" href="' . admin_url( 'admin.php?page=webappick-woo-invoice' ) . '" target="_blank">%s</a>',  __( 'Settings', 'webappick-pdf-invoice-for-woocommerce' ));
    $links[] = sprintf( '<a style="color:green;" href="https://webappick.com/plugin/woocommerce-pdf-invoice-packing-slips/?utm_source=customer_site&utm_medium=free_vs_pro&utm_campaign=woo_invoice_free" target="_blank"><b>%s</b></a>',  __( 'Get Pro', 'webappick-pdf-invoice-for-woocommerce' ) );
    $links[] = sprintf( '<a style="color:#8e44ad;" href="https://webappick.com/docs/" target="_blank">%s</a>',  __( 'Documentation', 'webappick-pdf-invoice-for-woocommerce' ) );

	return $links;
}

add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'woo_invoice_plugin_action_links' );

/**
 * Add or Get invoice number
 *
 * @param integer $order_id Order Id.
 *
 * @return mixed|string
 */
function woo_invoice_get_invoice_number( $order_id ) {

    // Ensure we are working with a valid order object
    $order = woo_invoice_get_order( $order_id );

    // Get the existing invoice number from the order meta
    $invoice_no = woo_invoice_get_meta( $order, 'wpifw_invoice_no', true );

    if ( empty($invoice_no) || '' == $invoice_no ) {

        $invoice_no = $order_id;

        // Get Prefix.
        $prefix = get_option( 'wpifw_invoice_no_prefix' );
        $prefix = ! empty( $prefix ) ? $prefix : '';

        // Get Suffix.
        $suffix = get_option( 'wpifw_invoice_no_suffix' );
        $suffix = ! empty( $suffix ) ? $suffix : '';

        // Generate Invoice Number.
        $invoice_no = $prefix . $order_id . $suffix;

        $invoice_no = woo_invoice_process_date_macros( $order_id, $invoice_no );

        // Save the generated invoice number to the order meta
        woo_invoice_update_meta( $order, 'wpifw_invoice_no', $invoice_no );
    }

    return $invoice_no;
}

/**
 * Process macros for custom order or invoice number
 *
 * @param int    $order_id Order Unique Id.
 * @param string $order_no Custom Order Number.
 *
 * @return mixed
 */
function woo_invoice_process_date_macros( $order_id, $order_no ) {
	$order_created = get_the_date( 'Y-m-d', $order_id );
	if ( false !== strpos( $order_no, '{{day}}' ) ) {
		$order_no = str_replace( '{{day}}', date( 'd', strtotime( $order_created ) ), $order_no ); //phpcs:ignore
	}
	if ( false !== strpos( $order_no, '{{month}}' ) ) {
		$order_no = str_replace( '{{month}}', date( 'm', strtotime( $order_created ) ), $order_no ); //phpcs:ignore
	}
	if ( false !== strpos( $order_no, '{{year}}' ) ) {
		$order_no = str_replace( '{{year}}', date( 'Y', strtotime( $order_created ) ), $order_no ); //phpcs:ignore
	}

	return $order_no;
}