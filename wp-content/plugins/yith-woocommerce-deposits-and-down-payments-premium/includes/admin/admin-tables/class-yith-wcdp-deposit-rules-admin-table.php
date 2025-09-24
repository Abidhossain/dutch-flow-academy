<?php
/**
 * Deposit Rules Table class
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\Deposits\Classes\Tables
 * @version 1.0.0
 */

if ( ! defined( 'YITH_WCDP' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

if ( ! class_exists( 'YITH_WCDP_Deposit_Rules_Admin_Table' ) ) {
	/**
	 * Deposit Rules Table
	 *
	 * @since 2.0.0
	 */
	class YITH_WCDP_Deposit_Rules_Admin_Table extends WP_List_Table {
		/**
		 * Class constructor method
		 */
		public function __construct() {
			// Set parent defaults.
			parent::__construct(
				array(
					'singular' => 'deposit',
					'plural'   => 'deposits',
					'ajax'     => false,
				)
			);

			add_action( 'yit_framework_after_print_wc_panel_content', array( $this, 'add_create_rule_button' ) );
		}

		/* === COLUMNS METHODS === */

		/**
		 * Print default column content
		 *
		 * @param array  $item        Item of the row.
		 * @param string $column_name Column name.
		 *
		 * @return string Column content
		 * @since 1.0.0
		 */
		public function column_default( $item, $column_name ) {
			if ( isset( $item[ $column_name ] ) ) {
				return esc_html( $item[ $column_name ] );
			} else {
				// Show the whole array for troubleshooting purposes.
				return print_r( $item, true ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
			}
		}

		/**
		 * Returns content of column showing Rule Type
		 *
		 * @param YITH_WCDP_Deposit_Rule $item Current item.
		 * @return string
		 */
		public function column_type( $item ) {
			return $item->get_formatted_type();
		}

		/**
		 * Returns content of column showing Rule Meta
		 *
		 * @param YITH_WCDP_Deposit_Rule $item Current item.
		 * @return string
		 */
		public function column_rules( $item ) {
			$type   = $item->get_type();
			$column = '';

			switch ( $type ) {
				case 'product_ids':
					$product_ids = $item->get_product_ids();

					foreach ( $product_ids as $product_id ) {
						$product = wc_get_product( $product_id );

						if ( ! $product ) {
							continue;
						}

						$product_image = $product->get_image( array( 60, 60 ) );
						$product_name  = $product->get_name();
						$product_url   = get_edit_post_link( $product->get_id() );

						$column .= <<<EOL
<div class="product_item">
	$product_image
	<a href="$product_url">
		$product_name
	</a>
</div>
EOL;
					}
					break;
				case 'product_categories':
					$categories       = $item->get_product_categories();
					$categories_names = array();

					foreach ( $categories as $category_id ) {
						$category = get_term( $category_id, 'product_cat' );

						if ( ! $category || is_wp_error( $category ) ) {
							continue;
						}

						$term_link = get_edit_term_link( $category );
						$term_name = $category->name;

						$categories_names[] = "<a href='$term_link'>$term_name</a>";
					}

					$column .= implode( ', ', $categories_names );
					break;
				case 'user_roles':
					$existing_roles = get_editable_roles();
					$roles          = $item->get_user_roles();
					$role_names     = array();

					foreach ( $roles as $role ) {
						if ( ! array_key_exists( $role, $existing_roles ) ) {
							continue;
						}

						$role_names[] = $existing_roles[ $role ]['name'];
					}

					$column .= implode( ', ', $role_names );
					break;
			}

			return $column;
		}

		/**
		 * Returns content of column showing Rule amount
		 *
		 * @param YITH_WCDP_Deposit_Rule $item Current item.
		 * @return string
		 */
		public function column_amount( $item ) {
			$fixed = $item->is_fixed();

			if ( $fixed ) {
				// translators: 1. HTML formatted price.
				return sprintf( __( 'Fixed amount of %1$s', 'yith-woocommerce-deposits-and-down-payments' ), wc_price( $item->get_amount() ) );
			} else {
				// translators: 1. Formatted percentage rage.
				return sprintf( __( '%1$s%% of product price', 'yith-woocommerce-deposits-and-down-payments' ), $item->get_formatted_rate() );
			}
		}

		/**
		 * Returns content of column showing Rule Actions
		 *
		 * @param YITH_WCDP_Deposit_Rule $item Current item.
		 * @return string
		 */
		public function column_actions( $item ) {
			$column  = '';
			$actions = array(
				'edit'   => array(
					'action' => _x( 'Edit', '[ADMIN] Deposit rule table', 'yith-woocommerce-deposits-and-down-payments' ),
					'icon'   => 'edit',
					'url'    => '#',
					'class'  => 'edit-deposit-rule',
				),
				'delete' => array(
					'action'       => _x( 'Delete', '[ADMIN] Deposit rule table', 'yith-woocommerce-deposits-and-down-payments' ),
					'icon'         => 'trash',
					'url'          => YITH_WCDP_Admin_Panel::get_action_url( 'delete_rule', array( 'id' => $item->get_id() ) ),
					'class'        => 'delete delete-deposit-rule',
					'confirm_data' => array(
						'title'               => _x( 'Confirm delete', 'yith-woocommerce-affiliates' ),
						'message'             => _x( 'Are you sure you want to delete this item?', 'yith-woocommerce-affiliates' ),
						'confirm-button'      => _x( 'Delete', 'yith-woocommerce-affiliates' ),
						'confirm-button-type' => 'delete',
					),
				),
			);

			foreach ( $actions as $action ) {
				$column .= yith_plugin_fw_get_component(
					array_merge(
						array(
							'type' => 'action-button',
						),
						$action
					),
					false
				);
			}

			return $column;
		}

		/**
		 * Returns columns available in table
		 *
		 * @return array Array of columns of the table
		 * @since 1.0.0
		 */
		public function get_columns() {
			$columns = array(
				'type'    => _x( 'Rule type', '[ADMIN] Deposit rule table', 'yith-woocommerce-deposits-and-down-payments' ),
				'rules'   => _x( 'Applied on', '[ADMIN] Deposit rule table', 'yith-woocommerce-deposits-and-down-payments' ),
				'amount'  => _x( 'Deposit type', '[ADMIN] Deposit rule table', 'yith-woocommerce-deposits-and-down-payments' ),
				'actions' => '',
			);

			return $columns;
		}

		/**
		 * Returns column to be sortable in table
		 *
		 * @return array Array of sortable columns
		 * @since 1.0.0
		 */
		public function get_sortable_columns() {
			$sortable_columns = array(
				'type' => array( 'type', false ),
			);

			return $sortable_columns;
		}

		/* === ROWS === */

		/**
		 * Generates content for a single row of the table.
		 *
		 * @param YITH_WCDP_Deposit_Rule $item Current item.
		 */
		public function single_row( $item ) {
			$serialize_array = $item->get_data();

			foreach ( $serialize_array as $key => $value ) {
				$serialize_array[ $key ] = is_scalar( $value ) ? $value : wp_json_encode( $value );
			}

			echo '<tr data-item="' . esc_attr( wc_esc_json( wp_json_encode( $serialize_array ) ) ) . '">';
			$this->single_row_columns( $item );
			echo '</tr>';
		}

		/* === DISPLAY METHODS === */

		/**
		 * Display table content
		 */
		public function display() {
			if ( ! $this->has_items() ) {
				?>
				<div class="empty-state">
					<p class="no-item-message">
						<?php echo esc_html_x( 'You have no deposit rule yet.', '[ADMIN] Deposit rule empty state', 'yith-woocommerce-deposits-and-down-payments' ); ?>
					</p>
					<a href="#" role="button" class="button yith-plugin-fw__button--primary yith-plugin-fw__button--xl yith-wcdp-add-rule-button">
						<?php echo esc_html_x( 'Create rule', '[ADMIN] Deposit rule empty state', 'yith-woocommerce-deposits-and-down-payments' ); ?>
					</a>
				</div>
				<?php
			} else {
				parent::display();
			}
		}

		/* === QUERY METHODS === */

		/**
		 * Prepare items for table
		 */
		public function prepare_items() {
			try {
				/**
				 * Retrieve rules Data_Store
				 *
				 * @var YITH_WCDP_Deposit_Rule_Data_Store $data_store
				 */
				$data_store = WC_Data_Store::load( 'deposit_rule' );
			} catch ( Exception $e ) {
				$this->items = array();
				return;
			}

			// sets pagination arguments.
			$per_page     = 20;
			$current_page = $this->get_pagenum();
			$total_items  = $data_store->count();

			$this->items = $data_store->query(
				array(
					'limit'   => $per_page,
					'offset'  => ( $current_page - 1 ) * $per_page,
					'orderby' => isset( $_GET['orderby'] ) ? sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) : 'ID', // phpcs:ignore
					'order'   => isset( $_GET['order'] ) ? sanitize_text_field( wp_unslash( $_GET['order'] ) ) : 'desc', // phpcs:ignore
				)
			);

			// sets columns headers.
			$columns  = $this->get_columns();
			$hidden   = array();
			$sortable = $this->get_sortable_columns();

			$this->_column_headers = array( $columns, $hidden, $sortable );

			// sets pagination args.
			$this->set_pagination_args(
				array(
					'total_items' => $total_items,
					'per_page'    => $per_page,
					'total_pages' => ceil( $total_items / $per_page ),
				)
			);
		}

		/**
		 * Adds "Create rule" button in the table
		 */
		public function add_create_rule_button() {
			global $current_screen;

			if ( ! isset( $_GET['tab'] ) || 'settings' !== $_GET['tab'] || 'yith-plugins_page_yith_wcdp_panel' !== $current_screen->id ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				return;
			}

			if ( $this->has_items() ) {
				?>
				<script type="text/javascript">
					jQuery(function () {
						jQuery('#yith_wcdp_panel_settings-rules h1.yith-plugin-fw__panel__content__page__title').after("<a class='button yith-plugin-fw__button--primary yith-wcdp-add-rule-button' href='#'><?php echo esc_html_x( 'Add rule', '[ADMIN] Add new deposit rule button, in Deposits Rules tab', 'yith-woocommerce-deposits-and-down-payments' ); ?></a>");
					});
				</script>
				<?php
			}
		}
	}
}
