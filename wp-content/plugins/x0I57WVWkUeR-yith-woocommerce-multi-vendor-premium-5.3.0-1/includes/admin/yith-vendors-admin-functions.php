<?php
/**
 * Collection of YITH Vendor admin actions
 *
 * @author  YITH
 * @package YITH\MultiVendor
 * @version 4.0.0
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

if ( ! function_exists( 'YITH_Vendors_Privacy' ) ) {
	/**
	 * Wrapper to get new YITH_Vendors_Privacy class instance
	 *
	 * @return YITH_Vendors_Privacy
	 */
	function YITH_Vendors_Privacy() { // phpcs:ignore
		return new YITH_Vendors_Privacy();
	}
}

if ( ! function_exists( 'yith_wcmv_include_admin_template' ) ) {
	/**
	 * Include admin template
	 *
	 * @since  4.0.0
	 * @param string $template The template to load.
	 * @param array  $args     An array of template arguments.
	 */
	function yith_wcmv_include_admin_template( $template, $args = array() ) {
		// Make sure template has extension.
		if ( false === strpos( $template, '.php' ) ) {
			$template .= '.php';
		}

		if ( file_exists( YITH_WPV_PATH . 'includes/admin/views/' . $template ) ) {
			extract( $args ); // phpcs:ignore

			include YITH_WPV_PATH . 'includes/admin/views/' . $template;
		}
	}
}

if ( ! function_exists( 'yith_wcmv_is_plugin_panel' ) ) {
	/**
	 * Check if current section is the plugin panel
	 *
	 * @since  4.0.0
	 * @param string|array $tab (Optional) A single tab to check ar an array of tabs to check. Default is empty string.
	 * @return boolean
	 */
	function yith_wcmv_is_plugin_panel( $tab = '' ) {
		$is_plugin_panel = isset( $_GET['page'] ) && YITH_Vendors_Admin::PANEL_PAGE === sanitize_text_field( wp_unslash( $_GET['page'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( $is_plugin_panel && ! empty( $tab ) ) {
			// If tab is given, check if the query string match.
			$current_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : '';  // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( empty( $current_tab ) ) { // If tab is empty check if is the first one.
				$plugin_tabs = array_keys( YITH_Vendors()->admin->get_plugin_tabs() );
				/**
				 * Let's filter plugin panel tabs to check. Useful for hidden tabs or vendor dashboard.
				 *
				 * @param array $plugin_tabs Default admin plugin tabs.
				 * @return array
				 */
				$plugin_tabs = apply_filters( 'yith_wcmv_is_admin_plugin_panel_tabs', $plugin_tabs );
				$current_tab = array_shift( $plugin_tabs );
			}

			$is_plugin_panel = is_array( $tab ) ? in_array( $current_tab, $tab, true ) : $current_tab === $tab;
		}

		return apply_filters( 'yith_wcmv_is_admin_plugin_panel', $is_plugin_panel, $tab );
	}
}

if ( ! function_exists( 'yith_wcmv_is_vendor_dashboard' ) ) {
	/**
	 * Check if plugin panel is a vendor dashboard
	 *
	 * @since  4.0.0
	 * @return boolean
	 */
	function yith_wcmv_is_vendor_dashboard() {
		if ( ! yith_wcmv_is_plugin_panel() ) {
			return false;
		}

		$vendor = yith_wcmv_get_vendor( 'current', 'user' );
		return $vendor && $vendor->is_valid() && $vendor->has_limited_access();
	}
}

if ( ! function_exists( 'yith_wcmv_get_panel_item_icon' ) ) {
	/**
	 * Return panel item icon
	 *
	 * @since 5.0.0
	 * @param string $item The panel menu item.
	 * @return string
	 */
	function yith_wcmv_get_panel_item_icon( $item ) {
		switch ( $item ) {
			case 'dashboard':
				return '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" /></svg>';
			case 'commissions':
				return '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6"><path stroke-linecap="round" stroke-linejoin="round" d="m9 14.25 6-6m4.5-3.493V21.75l-3.75-1.5-3.75 1.5-3.75-1.5-3.75 1.5V4.757c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0 1 11.186 0c1.1.128 1.907 1.077 1.907 2.185ZM9.75 9h.008v.008H9.75V9Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm4.125 4.5h.008v.008h-.008V13.5Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /></svg>';
			case 'vendors':
				return '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016c.896 0 1.7-.393 2.25-1.015a3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72l1.189-1.19A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72M6.75 18h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .414.336.75.75.75Z" /></svg>';
			case 'frontend-pages':
				return '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z"></path></svg>';
			case 'payments':
				return '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" /></svg>';
			case 'other':
				return '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6"><path stroke-linecap="round" stroke-linejoin="round" d="M6 13.5V3.75m0 9.75a1.5 1.5 0 0 1 0 3m0-3a1.5 1.5 0 0 0 0 3m0 3.75V16.5m12-3V3.75m0 9.75a1.5 1.5 0 0 1 0 3m0-3a1.5 1.5 0 0 0 0 3m0 3.75V16.5m-6-9V3.75m0 3.75a1.5 1.5 0 0 1 0 3m0-3a1.5 1.5 0 0 0 0 3m0 9.75V10.5" /></svg>';
			case 'modules':
				return '<svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" d="M14.25 6.087c0-.355.186-.676.401-.959.221-.29.349-.634.349-1.003 0-1.036-1.007-1.875-2.25-1.875s-2.25.84-2.25 1.875c0 .369.128.713.349 1.003.215.283.401.604.401.959v0a.64.64 0 01-.657.643 48.39 48.39 0 01-4.163-.3c.186 1.613.293 3.25.315 4.907a.656.656 0 01-.658.663v0c-.355 0-.676-.186-.959-.401a1.647 1.647 0 00-1.003-.349c-1.036 0-1.875 1.007-1.875 2.25s.84 2.25 1.875 2.25c.369 0 .713-.128 1.003-.349.283-.215.604-.401.959-.401v0c.31 0 .555.26.532.57a48.039 48.039 0 01-.642 5.056c1.518.19 3.058.309 4.616.354a.64.64 0 00.657-.643v0c0-.355-.186-.676-.401-.959a1.647 1.647 0 01-.349-1.003c0-1.035 1.008-1.875 2.25-1.875 1.243 0 2.25.84 2.25 1.875 0 .369-.128.713-.349 1.003-.215.283-.4.604-.4.959v0c0 .333.277.599.61.58a48.1 48.1 0 005.427-.63 48.05 48.05 0 00.582-4.717.532.532 0 00-.533-.57v0c-.355 0-.676.186-.959.401-.29.221-.634.349-1.003.349-1.035 0-1.875-1.007-1.875-2.25s.84-2.25 1.875-2.25c.37 0 .713.128 1.003.349.283.215.604.401.96.401v0a.656.656 0 00.658-.663 48.422 48.422 0 00-.37-5.36c-1.886.342-3.81.574-5.766.689a.578.578 0 01-.61-.58v0z"></path></svg>';
			case 'emails':
				return '<svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"></path></svg>';
			default:
				return '';
		}
	}
}

if ( ! function_exists( 'yith_wcmv_format_vendor_admins_for_select2' ) ) {
	/**
	 * Get vendor admin array for select2
	 *
	 * @since  1.0.0
	 * @param YITH_Vendor|null $vendor The vendor object.
	 * @return array
	 */
	function yith_wcmv_format_vendor_admins_for_select2( $vendor = null ) {
		if ( empty( $vendor ) ) {
			$vendor = yith_wcmv_get_vendor( 'current', 'user' );
		}

		$admins = array();
		if ( $vendor && $vendor->is_valid() ) {
			foreach ( $vendor->get_admins() as $user_id ) {
				if ( absint( $vendor->get_owner() ) !== absint( $user_id ) ) {
					$user               = get_userdata( $user_id );
					$user_display       = is_object( $user ) ? $user->display_name . '(#' . $user_id . ' - ' . $user->user_email . ')' : '';
					$admins[ $user_id ] = $user_display;
				}
			}
		}

		return $admins;
	}
}

if ( ! function_exists( 'yith_wcmv_get_admin_panel_url' ) ) {
	/**
	 * Get plugin admin panel url
	 *
	 * @since  4.0.0
	 * @param array $args An array of query arguments.
	 * @return string
	 */
	function yith_wcmv_get_admin_panel_url( $args = array() ) {
		$url = admin_url( 'admin.php?page=' . YITH_Vendors_Admin::PANEL_PAGE );
		if ( ! empty( $args ) ) {
			$url = add_query_arg( $args, $url );
		}

		return apply_filters( 'yith_wcmv_get_admin_panel_url', $url, $args );
	}
}

if ( ! function_exists( 'yith_wcmv_get_formatted_date_html' ) ) {
	/**
	 * Format given date with appropriate html
	 *
	 * @since  4.0.0
	 * @param string $date The date to format.
	 * @return boolean
	 */
	function yith_wcmv_get_formatted_date_html( $date ) {

		$date_timestamp = strtotime( $date );
		if ( empty( $date_timestamp ) ) {
			$return = '-';
		} else {
			$date_format = apply_filters( 'yith_wcmv_admin_date_format', get_option( 'date_format' ) );
			$time_format = get_option( 'time_format' );
			// Check if the date is within the last 24 hours, and not in the future.
			if ( $date_timestamp > strtotime( '-1 day', time() ) && $date_timestamp <= time() ) {
				$show_date = sprintf(
				/* translators: %s: human-readable time difference */
					_x( '%s ago', '%s = human-readable time difference', 'yith-woocommerce-product-vendors' ),
					human_time_diff( $date_timestamp, time() )
				);
			} else {
				$show_date = date_i18n( $date_format, $date_timestamp );
			}

			$return = sprintf(
				'<time datetime="%1$s" title="%2$s">%3$s</time>',
				esc_attr( date( 'c', $date_timestamp ) ),
				esc_html( date_i18n( $date_format . ' ' . $time_format, $date_timestamp ) ),
				esc_html( $show_date )
			);
		}

		return apply_filters( 'yith_wcmv_get_formatted_date_html', $return, $date );
	}
}

if ( ! function_exists( 'yith_wcmv_get_formatted_user_html' ) ) {
	/**
	 * Format given user with appropriate html
	 *
	 * @since  4.0.0
	 * @param WP_User|integer $user The user to format.
	 * @param boolean         $edit (Optional) True to include edit link, false otherwise.
	 * @return boolean
	 */
	function yith_wcmv_get_formatted_user_html( $user, $edit = true ) {

		if ( $user && ! $user instanceof WP_User ) {
			$user = get_user_by( 'id', absint( $user ) );
		}

		if ( ! $user || ! $user->exists() ) {
			return '';
		}

		$return = ( $user->first_name || $user->last_name ) ? ( ucfirst( $user->first_name ) . ' ' . ucfirst( $user->last_name ) ) : ucfirst( $user->display_name );
		if ( apply_filters( 'yith_wcmv_current_user_can_edit_users', current_user_can( 'edit_users' ) ) && $edit ) {
			$user_url = get_edit_user_link( $user->ID );
			$return   = '<a href="' . $user_url . '">' . $return . '</a>';
		}

		return apply_filters( 'yith_wcmv_get_formatted_user_html', $return, $user, $edit );
	}
}

if ( ! function_exists( 'yith_wcmv_get_formatted_order_user_html' ) ) {
	/**
	 * Format given order user with appropriate html
	 *
	 * @since  4.0.0
	 * @param WC_Order|integer $order The order where retrieve user data.
	 * @param boolean          $edit  (Optional) True to include edit link, false otherwise.
	 * @return boolean
	 */
	function yith_wcmv_get_formatted_order_user_html( $order, $edit = true ) {

		// Make sure order is valid.
		if ( $order && ! $order instanceof WC_Order ) {
			$order = wc_get_order( absint( $order ) );
		}

		$return = esc_html__( 'Guest', 'yith-woocommerce-product-vendors' );
		if ( $order ) {
			$user_html = yith_wcmv_get_formatted_user_html( $order->get_user(), $edit );

			if ( ! $user_html ) {
				$billing_first_name = $order->get_billing_first_name();
				$billing_last_name  = $order->get_billing_last_name();

				if ( $billing_first_name || $billing_last_name ) {
					$return = trim( $billing_first_name . ' ' . $billing_last_name );
				}
			} else {
				$return = $user_html;
			}
		}

		return apply_filters( 'yith_wcmv_get_formatted_order_user_html', $return, $order, $edit );
	}
}

if ( ! function_exists( 'yith_wcmv_print_vendor_admin_fields' ) ) {
	/**
	 * Print vendor admin field. Used on vendor dashboard and on vendor modal
	 *
	 * @since  4.0.0
	 * @param string $id    The field ID.
	 * @param array  $field Field options.
	 * @return void
	 */
	function yith_wcmv_print_vendor_admin_fields( $id, $field ) {

		$field = wp_parse_args(
			$field,
			array(
				'id'                => $id,
				'name'              => "vendor[{$id}]",
				'type'              => 'text',
				'label'             => '',
				'description'       => '',
				'placeholder'       => '',
				'required'          => false,
				'class'             => array(),
				'options'           => array(),
				'default'           => '',
				'custom_attributes' => array(),
			)
		);

		if ( $field['required'] ) {
			$field['label']  .= ' <abbr class="required">*</abbr>';
			$field['class'][] = 'field-required';
		}

		// Backward type compatibility.
		if ( 'tel' === $field['type'] ) {
			$field['type'] = 'text';
		} elseif ( 'multiselect' === $field['type'] ) {
			$field['type']     = 'select';
			$field['multiple'] = true;
		}

		$field = yith_wcmv_set_panel_field_value( $field );

		$wide_class = '';
		if ( ! empty( $field['wide'] ) ) {
			$wide_class = 'wide';
		}

		// Handle field custom attributes.
		if ( ! empty( $field['placeholder'] ) ) {
			$field['custom_attributes']['placeholder'] = $field['placeholder'];
		}

		// Let's build custom attributes as string.
		$custom_attributes = array();
		foreach ( $field['custom_attributes'] as $attribute => $attribute_value ) {
			$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
		}

		$field['custom_attributes'] = implode( ' ', $custom_attributes );
		// Stringify field classes.
		$field['class'] = implode( ' ', $field['class'] );

		switch ( $field['type'] ) {
			// Title.
			case 'title':
				?>
				<h4><?php echo esc_html( $field['label'] ); ?></h4>
				<?php
				break;
			// Fields separator.
			case 'separator':
				?>
				<div class="vendor-field-separator"></div>
				<?php
				break;
			// Select owner special field.
			case 'select_owner':
				?>
				<div class="vendor-owner-wrapper">
					<div class="owner-navigation">
						<?php
						// translators: %1$s and %3$s are placeholder for a html opening anchor, %2$s is a placeholder for closing anchor.
						echo wp_kses_post( sprintf( __( '%1$sSelect user%2$s or %3$sCreate a new user profile%2$s', 'yith-woocommerce-product-vendors' ), '<a href="#select-owner" class="current">', '</a>', '<a href="#create-owner">' ) );
						?>
					</div>
					<div id="select-owner">
						<select
							id="<?php echo esc_attr( $field['id'] ); ?>"
							name="<?php echo esc_attr( $field['name'] ); ?>"
							class="yith-wcmv-owner-select yith-wcmv-ajax-search"
							data-placeholder="<?php echo esc_attr__( 'Search for a user...', 'yith-woocommerce-product-vendors' ); ?>"
							data-action="search_for_owner"
							style="width: 100%"
							<?php echo $field['custom_attributes']; // phpcs:ignore
							?>
						>
						</select>
					</div>
					<div id="create-owner" class="vendor-fields-container" style="display: none;">
						<?php
						foreach ( yith_wcmv_create_owner_form_fields() as $owner_field ) {
							yith_wcmv_print_vendor_admin_fields( '', $owner_field );
						}
						?>
					</div>
				</div>
				<?php
				break;
			// Image special field.
			case 'avatar_image':
			case 'header_image':
				$src   = ! empty( $field['value'] ) ? wp_get_attachment_image_url( absint( $field['value'] ), 'full' ) : false;
				$value = ! empty( $src ) ? absint( $field['value'] ) : '';
				$style = ! empty( $src ) ? "background-image: url('{$src}');" : '';

				?>
				<div class="vendor-image-upload <?php echo esc_attr( str_replace( '_', '-', $field['type'] ) ); ?>" style="<?php echo esc_attr( $style ); ?>">
					<input type="hidden" id="<?php echo esc_attr( $field['id'] ); ?>" name="<?php echo esc_attr( $field['name'] ); ?>" class="vendor-image-upload-input" value="<?php echo esc_attr( $value ); ?>" <?php echo $field['custom_attributes']; // phpcs:ignore
					?>
					>
					<a href="javascript:void(0)" class="upload_vendor_image_button" data-input="<?php echo esc_attr( $field['id'] ); ?>" <?php echo ! empty( $src ) ? 'style="display: none;"' : ''; ?>>
						<span>
							<?php if ( isset( $field['icon'] ) ) : ?>
								<img src="<?php echo esc_url( YITH_WPV_ASSETS_URL ); ?>icons/<?php echo esc_attr( $field['icon'] ); ?>.svg" width="40"/>
								<br>
							<?php endif; ?>
							+ <?php echo esc_attr( $field['label'] ); ?>
						</span>
					</a>
					<a href="javascript:void(0)" class="remove_vendor_image_button" data-input="<?php echo esc_attr( $field['id'] ); ?>" <?php echo empty( $src ) ? 'style="display: none;"' : ''; ?>>
						<i class="yith-icon yith-icon-close"></i>
					</a>
				</div>
				<?php
				break;
			// Single country selects.
			case 'country':
			case 'select_country':
				$countries         = WC()->countries->get_countries();
				$field_description = ! empty( $field['description'] ) ? '<span class="description">' . esc_html( $field['description'] ) . '</span>' : '';
				?>

				<div class="vendor-field">
					<label for="<?php echo esc_attr( $field['id'] ); ?>"><?php echo wp_kses_post( $field['label'] ); ?></label>
					<select name="<?php echo esc_attr( $field['name'] ); ?>" id="<?php echo esc_attr( $field['id'] ); ?>" class="country-field"
						data-placeholder="<?php echo esc_attr__( 'Select a country / region&hellip;', 'yith-woocommerce-product-vendors' ); ?>" style="width: 100%;" <?php echo $field['custom_attributes']; // phpcs:ignore
						?>
					>
						<option value=""><?php echo esc_html__( 'Select a country / region&hellip;', 'yith-woocommerce-product-vendors' ); ?></option>
						<?php foreach ( $countries as $ckey => $cvalue ) : ?>
							<option value="<?php echo esc_attr( $ckey ); ?>" <?php selected( $field['value'], $ckey, true ); ?>><?php echo esc_html( $cvalue ); ?></option>
						<?php endforeach; ?>
					</select>
					<?php echo wp_kses_post( $field_description ); ?>
				</div>
				<?php
				break;
			// Special Generate password field.
			case 'generate_password':
				?>
				<div class="vendor-field">
					<button class="set-password yith-plugin-fw__button--secondary"><?php esc_html_e( 'Generate Password', 'yith-woocommerce-product-vendors' ); ?></button>
					<div class="password-field-wrap" style="display: none;">
						<label for="<?php echo esc_attr( $field['id'] ); ?>"><?php echo wp_kses_post( $field['label'] ); ?></label>
						<input type="text" name="<?php echo esc_attr( $field['name'] ); ?>" id="<?php echo esc_attr( $field['id'] ); ?>" value="<?php echo esc_attr( wp_generate_password() ); ?>"/>
					</div>
				</div>
				<?php
				break;
			// Special socials fields.
			case 'socials':
				?>
				<h4><?php echo esc_html( $field['label'] ); ?></h4>
				<?php

				foreach ( $field['socials'] as $social_key => $social_data ) :
					$id    = 'social_' . $social_key;
					$name  = $field['name'] . "[{$social_key}]";
					$value = ( is_array( $field['value'] ) && ! empty( $field['value'][ $social_key ] ) ) ? $field['value'][ $social_key ] : '';
					// Customize data-value if present.
					// TODO find a better solution
					$custom_attributes = '';
					if ( ! empty( $field['custom_attributes'] ) && false !== strpos( $field['custom_attributes'], 'data-value' ) ) {
						$custom_attributes = "data-value={{data.socials?.{$social_key}}}";
					}
					?>
					<div class="vendor-field">
						<label for="<?php echo esc_attr( $id ); ?>"><?php echo wp_kses_post( $social_data['label'] ); ?></label>
						<input type="text" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>" <?php echo esc_attr( $custom_attributes ); ?>>
					</div>
					<?php
				endforeach;
				break;
			default:
				if ( has_action( 'yith_wcmv_vendor_admin_field_' . $field['type'] ) ) { // Handle custom action.
					do_action( 'yith_wcmv_vendor_admin_field_' . $field['type'], $field );
				} else {
					// Description handling.
					$field_description = ! empty( $field['description'] ) ? '<span class="description">' . esc_html( $field['description'] ) . '</span>' : '';
					$field_html        = yith_plugin_fw_get_field( $field, false, false );

					?>
					<div class="vendor-field yith-plugin-fw-panel-wc-row <?php echo esc_attr( $wide_class ); ?>" <?php echo yith_field_deps_data( $field ); // phpcs:ignore ?>>
						<label for="<?php echo esc_attr( $field['id'] ); ?>"><?php echo wp_kses_post( $field['label'] ); ?></label>
						<div class="yith-plugin-fw-field-wrapper"><?php echo $field_html . $field_description; // phpcs:ignore ?></div>
					</div>
					<?php
				}
				break;
		}
	}
}

if ( ! function_exists( 'yith_wcmv_create_owner_form_fields' ) ) {
	/**
	 * Return an array of fields for create owner form
	 *
	 * @since  4.0.0
	 * @return array
	 */
	function yith_wcmv_create_owner_form_fields() {
		return apply_filters(
			'yith_wcmv_create_owner_form_fields',
			array(
				array(
					'id'    => 'new_owner_first_name',
					'name'  => 'new_owner_first_name',
					'label' => _x( 'First name', '[Admin]Create owner field label', 'yith-woocommerce-product-vendors' ),
				),
				array(
					'id'    => 'new_owner_last_name',
					'name'  => 'new_owner_last_name',
					'label' => _x( 'Last name', '[Admin]Create owner field label', 'yith-woocommerce-product-vendors' ),
				),
				array(
					'id'       => 'new_owner_username',
					'name'     => 'new_owner_username',
					'label'    => _x( 'Username', '[Admin]Create owner field label', 'yith-woocommerce-product-vendors' ),
					'required' => 'yes' !== get_option( 'woocommerce_registration_generate_username', 'yes' ),
				),
				array(
					'id'       => 'new_owner_email',
					'name'     => 'new_owner_email',
					'label'    => _x( 'Email', '[Admin]Create owner field label', 'yith-woocommerce-product-vendors' ),
					'required' => true,
				),
				array(
					'id'       => 'new_owner_password',
					'name'     => 'new_owner_password',
					'type'     => 'generate_password',
					'label'    => _x( 'Password', '[Admin]Create owner field label', 'yith-woocommerce-product-vendors' ),
					'required' => 'yes' !== get_option( 'woocommerce_registration_generate_password' ),
				),
			)
		);
	}
}

if ( ! function_exists( 'yith_wcmv_print_panel_field' ) ) {
	/**
	 * Print a panel admin field.
	 * This function is a short circuit for add_yith_field ( /plugin-fw/includes/class-yit-plugin-panel-woocommerce.php:620 )
	 *
	 * @since  4.0.0
	 * @param array $field The field to output.
	 * @return void
	 */
	function yith_wcmv_print_panel_field( $field ) {

		if ( ! defined( 'YIT_CORE_PLUGIN_TEMPLATE_PATH' ) || ! file_exists( YIT_CORE_PLUGIN_TEMPLATE_PATH . '/panel/woocommerce/woocommerce-option-row.php' ) ) {
			return;
		}

		$field = wp_parse_args(
			$field,
			array(
				'type'              => 'text',
				'label'             => '',
				'description'       => '',
				'placeholder'       => '',
				'required'          => false,
				'class'             => array(),
				'options'           => array(),
				'default'           => '',
				'custom_attributes' => array(),
			)
		);

		$field['id'] = isset( $field['id'] ) ? $field['id'] : '';
		if ( empty( $field['name'] ) ) {
			$field['name'] = $field['id'];
		}
		if ( empty( $field['type'] ) ) {
			$field['type'] = 'text';
		}

		$field = yith_wcmv_set_panel_field_value( $field );
		if ( is_array( $field['class'] ) ) {
			// Stringify field classes.
			$field['class'] = implode( ' ', $field['class'] );
		}

		if ( ! empty( $field['required'] ) ) {
			$field['class'] .= ' field-required';
		}

		require YIT_CORE_PLUGIN_TEMPLATE_PATH . '/panel/woocommerce/woocommerce-option-row.php';
	}
}

if ( ! function_exists( 'yith_wcmv_set_panel_field_value' ) ) {
	/**
	 * Set the value of given panel field
	 *
	 * @since  4.0.0
	 * @param array $field The field to process.
	 * @return array
	 */
	function yith_wcmv_set_panel_field_value( $field ) {

		if ( ! isset( $field['value'] ) ) {
			// Handle special select and radio fields.
			if ( ! empty( $field['custom_attributes']['data-value'] ) && in_array( $field['type'], array( 'select', 'radio' ), true ) ) {
				$field['value'] = ! empty( $field['multiple'] ) ? array( $field['custom_attributes']['data-value'] ) : $field['custom_attributes']['data-value'];
				unset( $field['custom_attributes']['data-value'] );
			} elseif ( isset( $field['default'] ) ) {
				$field['value'] = $field['default'];
			}
		}

		return $field;
	}
}

if ( ! function_exists( 'yith_wcmv_get_posted_data' ) ) {
	/**
	 * Get vendor form posted data
	 *
	 * @since  4.0.0
	 * @param array  $fieldset The fieldset to process.
	 * @param string $key      (Optional) The key to use of global $_POST variable. Default is empty string.
	 * @param mixed  $_post    (Optional) Current value for $_POST variable. Useful for recursive call. Default is null.
	 * @return array
	 */
	function yith_wcmv_get_posted_data( $fieldset, $key = '', $_post = null ) {

		$posted = array();

		// Get $_POST value.
		if ( is_null( $_post ) ) {
			if ( ! empty( $key ) ) {
				// If is not set in post, return empty.
				if ( ! isset( $_POST[ $key ] ) || ! is_array( $_POST[ $key ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
					return $posted;
				}
				$_post = $_POST[ $key ]; // phpcs:ignore
			} else {
				$_post = $_POST; // phpcs:ignore
			}
		}

		foreach ( $fieldset as $field_key => $field ) {
			// Set text as default type.
			$type = isset( $field['type'] ) ? $field['type'] : 'text';
			// Exclude special field type.
			if ( in_array( $type, array( apply_filters( 'yith_wcmv_get_posted_data_excluded_type', array( 'title', 'html', 'separator' ) ) ), true ) ) {
				continue;
			}

			$value = apply_filters( 'yith_wcmv_get_posted_data_' . $type, null, $field_key, $key, $field );

			if ( is_null( $value ) ) {

				if ( 'checkbox' === $type || 'onoff' === $type ) {
					$value = isset( $_post[ $field_key ] ) ? 'yes' : 'no';
				} elseif ( isset( $_post[ $field_key ] ) ) {
					switch ( $type ) {

						case 'textarea-editor':
						case 'textarea':
							$value = wp_kses_post( wp_unslash( $_post[ $field_key ] ) );
							break;

						case 'email':
							$value = sanitize_email( wp_unslash( $_post[ $field_key ] ) );
							break;

						case 'inline-fields':
							$value = yith_wcmv_get_posted_data( $field['fields'], $key, $_post[ $field_key ] );
							break;

						case 'price':
							$value = wc_format_decimal( $_post[ $field_key ] );
							break;

						case 'ajax-vendors':
							$value = isset( $_post[ $field_key ] ) ? array_map( 'absint', $_post[ $field_key ] ) : '';
							break;

						default:
							$value = is_array( $_post[ $field_key ] ) ? wc_clean( wp_unslash( $_post[ $field_key ] ) ) : sanitize_text_field( wp_unslash( $_post[ $field_key ] ) );
							break;
					}
				}
			}

			if ( ! is_null( $value ) ) {
				$posted[ $field_key ] = $value;
			}
		}

		return $posted;
	}
}

if ( ! function_exists( 'yith_wcmv_print_admin_notice' ) ) {
	/**
	 * Print an admin notice
	 *
	 * @since  4.0.0
	 * @param string  $message The notice message.
	 * @param string  $type    (Optional) The notice type.
	 * @param boolean $return  (Optional) True to return the notice, false to print.
	 * @return string|void
	 */
	function yith_wcmv_print_admin_notice( $message, $type = 'notice', $return = false ) {
		$notice = '<div id="message" class="updated ' . $type . ' is-dismissible yith-plugin-fw-animate__appear-from-top inline"><p>' . $message . '</p></div>';
		if ( $return ) {
			return $notice;
		}
		echo $notice; // phpcs:ignore
	}
}
