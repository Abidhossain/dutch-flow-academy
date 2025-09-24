<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase
/**
 * Functions file
 *
 * @package YITH\GiftCards\Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/** Define constant values */
defined( 'YWGC_CUSTOM_POST_TYPE_NAME' ) || define( 'YWGC_CUSTOM_POST_TYPE_NAME', 'gift_card' );
defined( 'YWGC_GIFT_CARD_PRODUCT_TYPE' ) || define( 'YWGC_GIFT_CARD_PRODUCT_TYPE', 'gift-card' );
defined( 'YWGC_PRODUCT_PLACEHOLDER' ) || define( 'YWGC_PRODUCT_PLACEHOLDER', '_ywgc_placeholder' );
defined( 'YWGC_CATEGORY_TAXONOMY' ) || define( 'YWGC_CATEGORY_TAXONOMY', 'giftcard-category' );

/** Race conditions - Gift cards duplicates */
defined( 'YWGC_RACE_CONDITION_BLOCKED' ) || define( 'YWGC_RACE_CONDITION_BLOCKED', '_ywgc_race_condition_blocked' );
defined( 'YWGC_RACE_CONDITION_UNIQUID' ) || define( 'YWGC_RACE_CONDITION_UNIQUID', '_ywgc_race_condition_uniqid' );

/*  plugin actions */
defined( 'YWGC_ACTION_RETRY_SENDING' ) || define( 'YWGC_ACTION_RETRY_SENDING', 'retry-sending' );
defined( 'YWGC_ACTION_DOWNLOAD_PDF' ) || define( 'YWGC_ACTION_DOWNLOAD_PDF', 'download-gift-pdf' );
defined( 'YWGC_ACTION_ENABLE_CARD' ) || define( 'YWGC_ACTION_ENABLE_CARD', 'enable-gift-card' );
defined( 'YWGC_ACTION_DISABLE_CARD' ) || define( 'YWGC_ACTION_DISABLE_CARD', 'disable-gift-card' );
defined( 'YWGC_ACTION_ADD_DISCOUNT_TO_CART' ) || define( 'YWGC_ACTION_ADD_DISCOUNT_TO_CART', 'ywcgc-add-discount' );
defined( 'YWGC_ACTION_VERIFY_CODE' ) || define( 'YWGC_ACTION_VERIFY_CODE', 'ywcgc-verify-code' );
defined( 'YWGC_ACTION_PRODUCT_ID' ) || define( 'YWGC_ACTION_PRODUCT_ID', 'ywcgc-product-id' );
defined( 'YWGC_ACTION_GIFT_THIS_PRODUCT' ) || define( 'YWGC_ACTION_GIFT_THIS_PRODUCT', 'ywcgc-gift-this-product' );

/*  gift card post_metas */
defined( 'YWGC_META_GIFT_CARD_ORDERS' ) || define( 'YWGC_META_GIFT_CARD_ORDERS', '_ywgc_orders' );
defined( 'YWGC_META_GIFT_CARD_CUSTOMER_USER' ) || define( 'YWGC_META_GIFT_CARD_CUSTOMER_USER', '_ywgc_customer_user' ); // Refer to user that use the gift card.
defined( 'YWGC_ORDER_ITEM_DATA' ) || define( 'YWGC_ORDER_ITEM_DATA', '_ywgc_order_item_data' );

/*  order item metas    */
defined( 'YWGC_META_GIFT_CARD_POST_ID' ) || define( 'YWGC_META_GIFT_CARD_POST_ID', '_ywgc_gift_card_post_id' );
defined( 'YWGC_META_GIFT_CARD_CODE' ) || define( 'YWGC_META_GIFT_CARD_CODE', '_ywgc_gift_card_code' );
defined( 'YWGC_META_GIFT_CARD_STATUS' ) || define( 'YWGC_META_GIFT_CARD_STATUS', '_ywgc_gift_card_status' );

if ( ! function_exists( 'ywgc_get_status_label' ) ) {
	/**
	 * Retrieve the status label for every gift card status
	 *
	 * @param YITH_YWGC_Gift_Card $gift_card Gift card object.
	 *
	 * @return string
	 */
	function ywgc_get_status_label( $gift_card ) {
		return $gift_card->get_status_label();
	}
}

if ( ! function_exists( 'ywgc_get_order_item_giftcards' ) ) {
	/**
	 * Retrieve the gift card ids associated to an order item
	 *
	 * @param int $order_item_id Order item ID.
	 *
	 * @return string|void
	 * @author YITH <plugins@yithemes.com>
	 * @since  1.0.0
	 */
	function ywgc_get_order_item_giftcards( $order_item_id ) {
		/*
		 * Let third party plugin to change the $order_item_id
		 */
		$order_item_id = apply_filters( 'yith_get_order_item_gift_cards', $order_item_id );
		$gift_ids      = wc_get_order_item_meta( $order_item_id, YWGC_META_GIFT_CARD_POST_ID );

		if ( is_numeric( $gift_ids ) ) {
			$gift_ids = array( $gift_ids );
		}

		if ( ! is_array( $gift_ids ) ) {
			$gift_ids = array();
		}

		return $gift_ids;
	}
}

if ( ! function_exists( 'ywgc_set_order_item_giftcards' ) ) {
	/**
	 * Retrieve the gift card ids associated to an order item
	 *
	 * @param int   $order_item_id the order item.
	 * @param array $ids           the array of gift card ids associated to the order item.
	 *
	 * @return string|void
	 * @since  1.0.0
	 */
	function ywgc_set_order_item_giftcards( $order_item_id, $ids ) {
		$ids = apply_filters( 'yith_ywgc_set_order_item_meta_gift_card_ids', $ids, $order_item_id );

		wc_update_order_item_meta( $order_item_id, YWGC_META_GIFT_CARD_POST_ID, $ids );

		$gift_card_codes = array();

		foreach ( $ids as $gc_id ) {
			$gc                = new YWGC_Gift_Card_Premium( array( 'ID' => $gc_id ) );
			$gc_code           = $gc->get_code();
			$gift_card_codes[] = $gc_code;
		}

		wc_update_order_item_meta( $order_item_id, YWGC_META_GIFT_CARD_CODE, $gift_card_codes );

		do_action( 'yith_ywgc_set_order_item_meta_gift_card_ids_updated', $order_item_id, $ids );
	}
}

if ( ! function_exists( 'yith_get_attachment_image_url' ) ) {
	/**
	 * Get the attachment URL
	 *
	 * @param int    $attachment_id Attachment ID.
	 * @param string $size          Image size.
	 *
	 * @return string
	 */
	function yith_get_attachment_image_url( $attachment_id, $size = 'thumbnail' ) {
		if ( function_exists( 'wp_get_attachment_image_url' ) ) {
			$header_image_url = wp_get_attachment_image_url( $attachment_id, $size );
		} else {
			$header_image     = wp_get_attachment_image_src( $attachment_id, $size );
			$header_image_url = $header_image['url'];
		}

		return apply_filters( 'yith_ywcgc_attachment_image_url', $header_image_url );
	}
}

add_filter( 'yit_fw_metaboxes_type_args', 'ywgc_filter_balance_display' );

if ( ! function_exists( 'ywgc_filter_balance_display' ) ) {
	/**
	 * Fix the current balance display to match WooCommerce settings
	 *
	 * @param mixed $args args.
	 *
	 * @return mixed
	 */
	function ywgc_filter_balance_display( $args ) {
		if ( '_ywgc_balance_total' === $args['args']['args']['id'] ) {
			$args['args']['args']['value'] = round( $args['args']['args']['value'], wc_get_price_decimals() );
		}

		return $args;
	}
}

add_action( 'init', 'yith_wcgc_convert_date_to_timestamp' );

/**
 * Convert gift card delivery date to timestamp
 */
function yith_wcgc_convert_date_to_timestamp() {
	$delivery_date_converted_in_timestamp = get_option( 'yith_wcgc_delivery_date_converted_in_timestamp' );

	if ( ! $delivery_date_converted_in_timestamp ) { // Remove NOT logic condition to execute again the process (if the option is already saved).
		$gift_cards = get_posts(
			array(
				'post_type'   => YWGC_CUSTOM_POST_TYPE_NAME,
				'post_status' => 'any',
				'numberposts' => -1,
			)
		);

		foreach ( $gift_cards as $gift_card ) {
			$delivery_date      = get_post_meta( $gift_card->ID, '_ywgc_delivery_date', true );
			$delivery_send_date = get_post_meta( $gift_card->ID, '_ywgc_delivery_send_date', true );
			$expiration_date    = get_post_meta( $gift_card->ID, '_ywgc_expiration', true );

			if ( '' !== $delivery_date ) {
				$timestamp = strtotime( $delivery_date );
				$timestamp && update_post_meta( $gift_card->ID, '_ywgc_delivery_date', $timestamp );
			}

			if ( '' !== $delivery_send_date ) {
				$timestamp = strtotime( $delivery_send_date );
				$timestamp && update_post_meta( $gift_card->ID, '_ywgc_delivery_send_date', $timestamp );
			}

			if ( '' !== $expiration_date ) {
				$timestamp = strtotime( $expiration_date );
				$timestamp && update_post_meta( $gift_card->ID, '_ywgc_expiration', $timestamp );
			}
		}

		update_option( 'yith_wcgc_delivery_date_converted_in_timestamp', true );
	}
}

add_action( 'init', 'yith_wcgc_convert_date_picker_dates' );

/**
 * Convert gift card delivery date to timestamp
 */
function yith_wcgc_convert_date_picker_dates() {
	$checker = get_option( 'yith_wcgc_date_pickers_convert_v5' );

	if ( ! $checker ) { // Remove NOT logic condition to execute again the process (if the option is already saved).
		$gift_cards = get_posts(
			array(
				'post_type'   => YWGC_CUSTOM_POST_TYPE_NAME,
				'post_status' => 'any',
				'numberposts' => -1,
			)
		);

		$date_format = apply_filters( 'yith_wcgc_date_format', 'Y-m-d' );

		foreach ( $gift_cards as $gift_card ) {
			$gift_card = YITH_YWGC()->get_gift_card_by_code( $gift_card->post_title );

			$delivery_date   = $gift_card->delivery_date;
			$expiration_date = $gift_card->expiration;

			if ( '' !== $delivery_date ) {
				$delivery_date_format = date_i18n( $date_format, $delivery_date );
				update_post_meta( $gift_card->ID, '_ywgc_delivery_date_formatted', $delivery_date_format );
			}

			if ( '' !== $expiration_date ) {
				$expiration_date_format = '0' !== $expiration_date ? date_i18n( $date_format, $expiration_date ) : '';
				update_post_meta( $gift_card->ID, '_ywgc_expiration_date_formatted', $expiration_date_format );
			}
		}

		update_option( 'yith_wcgc_date_pickers_convert_v5', true );
	}
}

/**
 * Make a backup of the database
 */
function yith_wcgc_make_backup_db() {
	global $wpdb;

	// Get a list of the tables.
	$tables = $wpdb->get_results( 'SHOW TABLES' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

	$upload_dir = wp_upload_dir();
	$backup_dir = $upload_dir['basedir'] . '/backups';

	if ( ! is_dir( $backup_dir ) ) {
		mkdir( $backup_dir, 0700 ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_mkdir
	}

	$file_path = $backup_dir . '/database-' . time() . '.sql';
	$file      = fopen( $file_path, 'w' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen

	foreach ( $tables as $table ) {
		foreach ( $table as $index => $table_name ) {
			$schema = $wpdb->get_row( 'SHOW CREATE TABLE ' . $table_name, ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.NotPrepared

			fwrite( $file, $schema['Create Table'] . ';' . PHP_EOL ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite

			$rows = $wpdb->get_results( 'SELECT * FROM ' . $table_name, ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared

			if ( $rows ) {
				fwrite( $file, 'INSERT INTO ' . $table_name . ' VALUES ' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite

				$total_rows = count( $rows );
				$counter    = 1;

				foreach ( $rows as $row => $fields ) {
					$line = '';

					foreach ( $fields as $key => $value ) {
						$value = addslashes( $value );
						$line .= '"' . $value . '",';
					}

					$line = '(' . rtrim( $line, ',' ) . ')';

					if ( $counter !== $total_rows ) {
						$line .= ',' . PHP_EOL;
					}

					fwrite( $file, $line ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite

					++$counter;
				}

				fwrite( $file, '; ' . PHP_EOL ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite
			}
		}
	}

	fclose( $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
}

/**
 * Ywgc_get_attachment_id_from_url
 *
 * @param  mixed $attachment_url attachment_url.
 * @return int
 */
function ywgc_get_attachment_id_from_url( $attachment_url = '' ) {
	global $wpdb;

	$attachment_id = false;

	if ( '' === $attachment_url ) {
		return;
	}

	$upload_dir_paths = wp_upload_dir();

	if ( false !== strpos( $attachment_url, $upload_dir_paths['baseurl'] ) ) {
		$attachment_url = preg_replace( '/-\d+x\d+(?=\.(jpg|jpeg|png|gif)$)/i', '', $attachment_url );
		$attachment_url = str_replace( $upload_dir_paths['baseurl'] . '/', '', $attachment_url );

		$attachment_id = $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				"SELECT wposts.ID FROM $wpdb->posts wposts, $wpdb->postmeta wpostmeta
                WHERE wposts.ID = wpostmeta.post_id
            	AND wpostmeta.meta_key = '_wp_attached_file'
                AND wpostmeta.meta_value = %s
            	AND wposts.post_type = 'attachment'",
				$attachment_url
			)
		);
	}

	return $attachment_id;
}

if ( ! function_exists( 'yith_ywgc_get_view' ) ) {
	/**
	 * Get the view
	 *
	 * @param string $file_name Name of the file to get in views.
	 * @param array  $args      Arguments.
	 */
	function yith_ywgc_get_view( $file_name, $args = array() ) {
		$file_path = trailingslashit( YITH_YWGC_VIEWS_PATH ) . $file_name;

		extract( $args ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract

		if ( file_exists( $file_path ) ) {
			include $file_path;
		}
	}
}

if ( ! function_exists( 'yith_ywgc_get_view_html' ) ) {
	/**
	 * Get a view HTML
	 *
	 * @param string $view The view.
	 * @param array  $args Arguments.
	 *
	 * @return string
	 * @since 2.0.0
	 */
	function yith_ywgc_get_view_html( $view, $args = array() ): string {
		ob_start();

		yith_ywgc_get_view( $view, $args );

		return ob_get_clean();
	}
}

/**
 * Convert string to float
 *
 * @param string $num Number.
 *
 * @return float
 */
function ywgc_string_to_float( $num ) {
	$dot_pos   = strrpos( $num, '.' );
	$comma_pos = strrpos( $num, ',' );
	$sep       = ( ( $dot_pos > $comma_pos ) && $dot_pos ) ? $dot_pos : ( ( ( $comma_pos > $dot_pos ) && $comma_pos ) ? $comma_pos : false );

	if ( ! $sep ) {
		return floatval( preg_replace( '/[^0-9]/', '', $num ) );
	}

	return floatval( preg_replace( '/[^0-9]/', '', substr( $num, 0, $sep ) ) . '.' . preg_replace( '/[^0-9]/', '', substr( $num, $sep + 1, strlen( $num ) ) ) );
}

/**
 * Set noindex meta tag for gift card default products
 */
function yith_wcgc_add_tagseo_metarob() {
	if ( 'product' === get_post_type( get_the_ID() ) ) {
		$post_id = get_the_ID();
		$product = new WC_Product( $post_id );

		if ( $product->get_id() === YITH_WooCommerce_Gift_Cards_Premium::get_instance()->default_gift_card_id ) {
			?>
			<meta name="robots" content="noindex">
			<?php
		}
	}
}

add_action( 'wp_head', 'yith_wcgc_add_tagseo_metarob' );

if ( ! function_exists( 'yith_ywgc_current_screen_is' ) ) {
	/**
	 * Return true if current screen is one of the $ids.
	 *
	 * @param string|string[] $ids The screen ID(s).
	 *
	 * @return bool
	 * @since 3.0.0
	 */
	function yith_ywgc_current_screen_is( $ids ) {
		$ids       = (array) $ids;
		$screen_id = yith_ywgc_get_current_screen_id();

		return $screen_id && in_array( $screen_id, $ids, true );
	}
}

if ( ! function_exists( 'yith_ywgc_get_current_screen_id' ) ) {
	/**
	 * Retrieve the current screen ID.
	 *
	 * @return string|false
	 * @since 3.0.0
	 */
	function yith_ywgc_get_current_screen_id() {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : false;

		return ! ! $screen && is_a( $screen, 'WP_Screen' ) ? $screen->id : false;
	}
}

if ( ! function_exists( 'yith_ywgc_admin_screen_ids' ) ) {
	/**
	 * Return gift card admin screen ids.
	 * Useful to enqueue correct styles/scripts in Booking's pages.
	 *
	 * @return array
	 */
	function yith_ywgc_admin_screen_ids(): array {
		$screen_ids = array(
			'product',
			'edit-product',
		);

		return $screen_ids;
	}
}

if ( ! function_exists( 'yith_ywgc_array_sort' ) ) {
	/**
	 * Sort array of arrays.
	 *
	 * @param array      $array            The array.
	 * @param string|int $field            The field used for sorting.
	 * @param string|int $default_priority Default priority.
	 *
	 * @since 4.0.0
	 */
	function yith_ywgc_array_sort( array &$array, $field = 'priority', $default_priority = '' ) { // phpcs:ignore Universal.NamingConventions.NoReservedKeywordParameterNames.arrayFound
		uasort(
			$array,
			function ( $a, $b ) use ( $field, $default_priority ) {
				$a_field = $a[ $field ] ?? $default_priority;
				$b_field = $b[ $field ] ?? $default_priority;

				return $a_field <=> $b_field;
			}
		);
	}
}

if ( ! function_exists( 'yith_ywgc_filter_options' ) ) {
	/**
	 * Filter options based on active modules and versions.
	 *
	 * @param array $options The options to filter.
	 * @param array $args    Arguments.
	 *
	 * @return array
	 * @since 4.0.0
	 */
	function yith_ywgc_filter_options( array $options, array $args = array() ): array {
		$defaults = array(
			'sort'             => false,
			'default_priority' => '',
		);
		$args     = wp_parse_args( $args, $defaults );

		$sort             = $args['sort'];
		$default_priority = $args['default_priority'];
		$should_sort      = ! ! $sort;

		$module_key      = 'yith-ywgc-module';
		$version_key     = 'yith-ywgc-version';
		$min_version_key = 'yith-ywgc-min-version';
		$priority_key    = is_string( $sort ) ? $sort : 'yith-ywgc-priority';

		if ( $should_sort ) {
			yith_ywgc_array_sort( $options, $priority_key, $default_priority );
		}

		foreach ( $options as $key => $option ) {
			$required_module      = $option[ $module_key ] ?? false;
			$required_version     = $option[ $version_key ] ?? false;
			$required_min_version = $option[ $min_version_key ] ?? false;
			$available            = true;

			if ( $required_module && ! yith_ywgc_is_module_active( $required_module ) ) {
				$available = false;
			}

			if ( $available && $required_version ) {
				if ( 'premium' === $required_version && ! defined( 'YITH_YWGC_PREMIUM' ) ) {
					$available = false;
				}

				if ( 'extended' === $required_version && ! defined( 'YITH_YWGC_EXTENDED' ) ) {
					$available = false;
				}
			}

			if ( $available && $required_min_version ) {
				if ( 'premium' === $required_version && ! defined( 'YITH_YWGC_PREMIUM' ) ) {
					$available = false;
				}

				if ( 'extended' === $required_version && ! ( defined( 'YITH_YWGC_EXTENDED' ) || defined( 'YITH_YWGC_PREMIUM' ) ) ) {
					$available = false;
				}
			}

			if ( ! $available ) {
				unset( $options[ $key ] );
			} else {
				unset( $options[ $key ][ $module_key ] );
				unset( $options[ $key ][ $version_key ] );
				unset( $options[ $key ][ $min_version_key ] );

				if ( $should_sort ) {
					unset( $options[ $key ][ $priority_key ] );
				}
			}
		}

		return $options;
	}
}

if ( ! function_exists( 'yith_ywgc_is_module_active' ) ) {
	/**
	 * Is this module active?
	 *
	 * @param string $module_key The module key.
	 *
	 * @return bool
	 */
	function yith_ywgc_is_module_active( string $module_key ): bool {
		$modules_class = YITH_YWGC_Modules::get_instance();

		return $modules_class->is_module_active( $module_key );
	}
}

/**
 * Compatibility with https://wordpress.org/plugins/woo-ajax-add-to-cart/
 */
add_filter( 'qlwcajax_product_supported_types', function( $types ) {
	$types[] = 'gift-card';
	return $types;
} );

