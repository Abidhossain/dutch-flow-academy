<?php
/**
 * Product deposit Quick/Bulk edit template
 *
 * @author YITH <plugins@yithemes.com>
 * @package YITH\Deposits\Views
 * @version 1.0.0
 */

/**
 * Template variables:
 *
 * @var $enable_deposit string
 * @var $deposit_default string
 * @var $force_deposit string
 * @var $create_balance_orders string
 * @var $product_note string
 */

if ( ! defined( 'YITH_WCDP' ) ) {
	exit;
} // Exit if accessed directly
?>

<fieldset class="inline-edit-col-right" id="yith_wcdp_deposit_bulk">
	<label for="_enable_deposit">
		<span class="title" ><?php esc_html_e( 'Deposit?', 'yith-woocommerce-deposits-and-down-payments' ); ?></span>
		<span class="input-text-wrap">
			<select name="_enable_deposit" id="enable_deposit" class="enable_deposit">
				<option value="default" <?php selected( empty( $enable_deposit ) || 'default' === $enable_deposit ); ?> >
					<?php esc_html_e( 'Default', 'yith-woocommerce-deposits-and-down-payments' ); ?>
				</option>
				<option value="yes" <?php selected( $enable_deposit, 'yes' ); ?> >
					<?php esc_html_e( 'Yes', 'yith-woocommerce-deposits-and-down-payments' ); ?>

				</option>
				<option value="no" <?php selected( $enable_deposit, 'no' ); ?> >
					<?php esc_html_e( 'No', 'yith-woocommerce-deposits-and-down-payments' ); ?>
				</option>
			</select>
		</span>
	</label>
	<label for="_deposit_default">
		<span class="title" ><?php esc_html_e( 'Default?', 'yith-woocommerce-deposits-and-down-payments' ); ?></span>
		<span class="input-text-wrap">
			<select name="_deposit_default" id="deposit_default" class="deposit_default">
				<option value="default" <?php selected( 'default' === $deposit_default || empty( $deposit_default ) ); ?> >
					<?php esc_html_e( 'Default', 'yith-woocommerce-deposits-and-down-payments' ); ?>
				</option>
				<option value="yes" <?php selected( $deposit_default, 'yes' ); ?> >
					<?php esc_html_e( 'Yes', 'yith-woocommerce-deposits-and-down-payments' ); ?>

				</option>
				<option value="no" <?php selected( $deposit_default, 'no' ); ?> >
					<?php esc_html_e( 'No', 'yith-woocommerce-deposits-and-down-payments' ); ?>
				</option>
			</select>
		</span>
	</label>
	<label for="_enable_deposit">
		<span class="title"><?php esc_html_e( 'Force?', 'yith-woocommerce-deposits-and-down-payments' ); ?></span>
		<span class="input-text-wrap">
			<select name="_force_deposit" id="force_deposit" class="force_deposit">
				<option value="default" <?php selected( 'default' === $force_deposit || empty( $force_deposit ) ); ?> >
					<?php esc_html_e( 'Default', 'yith-woocommerce-deposits-and-down-payments' ); ?>
				</option>
				<option value="yes" <?php selected( $force_deposit, 'yes' ); ?> >
					<?php esc_html_e( 'Force deposit', 'yith-woocommerce-deposits-and-down-payments' ); ?>

				</option>
				<option value="no" <?php selected( $force_deposit, 'no' ); ?> >
					<?php esc_html_e( 'Allow deposit', 'yith-woocommerce-deposits-and-down-payments' ); ?>
				</option>
			</select>
		</span>
	</label>
	<label for="_create_balance_orders" style="margin-bottom: 15px;">
		<?php /* @since 1.2.0 */ ?>
		<span class="title"><?php esc_html_e( 'How to pay balance orders?', 'yith-woocommerce-deposits-and-down-payments' ); ?></span>
		<br class="clear">
		<select name="_create_balance_orders" id="create_balance_orders" class="create_balance_orders">
			<option value="default" <?php selected( 'default' === $create_balance_orders || empty( $force_deposit ) ); ?> >
				<?php esc_html_e( 'Default', 'yith-woocommerce-deposits-and-down-payments' ); ?>
			</option>
			<option value="pending" <?php selected( $create_balance_orders, 'pending' ); ?> >
				<?php esc_html_e( 'Create balance orders with "Pending payment" status, and users will pay the balance online', 'yith-woocommerce-deposits-and-down-payments' ); ?>
			</option>
			<option value="on-hold" <?php selected( $create_balance_orders, 'on-hold' ); ?> >
				<?php esc_html_e( 'Create balance orders with "On hold" status, and manage payments manually (e.g.: users pay cash in your shop)', 'yith-woocommerce-deposits-and-down-payments' ); ?>
			</option>
			<option value="none" <?php selected( $create_balance_orders, 'none' ); ?> >
				<?php esc_html_e( 'Do not create balance orders', 'yith-woocommerce-deposits-and-down-payments' ); ?>
			</option>
		</select>
	</label>
	<label for="_product_note">
		<span class="title"><?php esc_html_e( 'Notes', 'yith-woocommerce-deposits-and-down-payments' ); ?></span>
		<span class="input-text-wrap">
			<textarea name="_product_note" id="_product_note" cols="30" rows="10"><?php echo esc_html( $product_note ); ?></textarea>
		</span>
	</label>
</fieldset>
