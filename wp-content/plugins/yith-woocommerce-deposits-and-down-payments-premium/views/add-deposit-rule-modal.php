<?php
/**
 * Add deposit rule modal
 *
 * @author YITH <plugins@yithemes.com>
 * @package YITH\Deposits\Views
 * @version 1.0.0
 */

/**
 * Template variables:
 *
 * @var $product_categories array
 */

if ( ! defined( 'YITH_WCDP' ) ) {
	exit;
} // Exit if accessed directly
?>

<script type="text/template" id="tmpl-yith-wcdp-add-deposit-rule-modal">
	<div id="add_affiliate_modal">
		<form method="post" action="<?php echo esc_url( YITH_WCDP_Admin_Panel::get_action_url( 'create_rule' ) ); ?>">
			<div class="form-row form-row-wide required">
				<label for="type">
					<?php echo esc_html_x( 'Rule type', '[ADMIN] Add deposit rule modal', 'yith-woocommerce-deposits-and-down-payments' ); ?>
				</label>
				<select class="wc-enhanced-select" name="type" id="type" style="width: 100%" data-value="{{data.type}}">
					<?php foreach ( YITH_WCDP_Deposit_Rule::get_supported_types() as $field_type => $type_label ) : ?>
						<option value="<?php echo esc_attr( $field_type ); ?>"><?php echo esc_html( $type_label ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="form-row form-row-wide required">
				<label for="product_ids">
					<?php echo esc_html_x( 'Search products', '[ADMIN] Add Affiliate modal', 'yith-woocommerce-deposits-and-down-payments' ); ?>
				</label>
				<select
					class="wc-product-search"
					name="product_ids[]"
					id="product_ids"
					style="width: 100%"
					multiple="multiple"
					data-value="{{data.products}}"
					data-placeholder="<?php echo esc_attr_x( 'Search products', '[ADMIN] Add deposit rule modal', 'yith-woocommerce-deposits-and-down-payments' ); ?>"
					data-dependencies='{"type":"product_ids"}'
				></select>
			</div>
			<div class="form-row form-row-wide required">
				<label for="product_categories">
					<?php echo esc_html_x( 'Search product categories', '[ADMIN] Add deposit rule modal', 'yith-woocommerce-deposits-and-down-payments' ); ?>
				</label>
				<select
					class="wc-enhanced-select"
					name="product_categories[]"
					id="product_categories"
					style="width: 100%"
					multiple="multiple"
					data-value="{{data.product_categories}}"
					data-placeholder="<?php echo esc_attr_x( 'Search categories', '[ADMIN] Add deposit rule modal', 'yith-woocommerce-deposits-and-down-payments' ); ?>"
					data-dependencies='{"type":"product_categories"}'
				>
					<?php foreach ( $product_categories as $category_id => $category_name ) {
						$current_category = get_term( $category_id, 'product_cat' );
						if ( $current_category && $current_category->parent != 0) {
							$parent_category = get_term( $current_category->parent, 'product_cat');
							if ( $parent_category ) {
								$category_name = $parent_category->name . ' - ' . $category_name;
							}
						} ?>
						<option value="<?php echo esc_attr( $category_id ); ?>"><?php echo esc_html( $category_name ); ?></option>
					<?php } ?>
				</select>
			</div>
			<div class="form-row form-row-wide required">
				<label for="user_roles">
					<?php echo esc_html_x( 'Search user roles', '[ADMIN] Add deposit rule modal', 'yith-woocommerce-deposits-and-down-payments' ); ?>
				</label>
				<select
					class="wc-enhanced-select"
					name="user_roles[]"
					id="user_roles"
					style="width: 100%"
					multiple="multiple"
					data-value="{{data.user_roles}}"
					data-placeholder="<?php echo esc_attr_x( 'Search roles', '[ADMIN] Add deposit rule modal', 'yith-woocommerce-deposits-and-down-payments' ); ?>"
					data-dependencies='{"type":"user_roles"}'
				>
					<?php wp_dropdown_roles(); ?>
				</select>
			</div>
			<div class="form-row form-row-wide required deposit-type-field">
				<label for="rate">
					<?php echo esc_html_x( 'Deposit type', '[ADMIN] Add deposit rule modal', 'yith-woocommerce-deposits-and-down-payments' ); ?>
				</label>
				<span class="form-row required">
					<input type="number" min="0" max="<?php echo esc_attr( apply_filters( 'yith_wcaf_max_rate_value', 100 ) ); ?>" step="0.01" name="rate" id="rate" value="{{data.rate}}" data-dependencies='{"fixed":"0"}'/>
				</span>
				<span class="form-row required">
					<input type="number" min="0" step="0.01" name="amount" id="amount" value="{{data.amount}}" data-dependencies='{"fixed":"1"}'/>
				</span>
				<select
					class="wc-enhanced-select"
					name="fixed"
					id="fixed"
					style="width: 100%"
					data-value="{{data.fixed}}"
				>
					<option value="0">
						<?php esc_html_e( '% of product price', 'yith-woocommerce-deposits-and-down-payments' ); ?>
					</option>
					<option value="1">
						<?php
						// translators: 1. Currency symbol.
						echo esc_html( sprintf( __( '%s - Fixed amount', 'yith-woocommerce-deposits-and-down-payments' ), get_woocommerce_currency_symbol() ) );
						?>
					</option>
				</select>
			</div>
			<div class="form-row form-row-wide submit">
				<button class="submit button-primary">
					<# if ( data.id ) { #>
					<?php echo esc_html_x( 'Save rule', '[ADMIN] Add deposit rule modal', 'yith-woocommerce-deposits-and-down-payments' ); ?>
					<# } else { #>
					<?php echo esc_html_x( 'Add rule', '[ADMIN] Add deposit rule modal', 'yith-woocommerce-deposits-and-down-payments' ); ?>
					<# } #>
				</button>
				<input type="hidden" name="id" value="{{data.id}}"/>
				<input type="hidden" name="enabled" value="{{data.enabled}}" data-value="{{data.enabled}}"/>
			</div>
		</form>
	</div>
</script>
