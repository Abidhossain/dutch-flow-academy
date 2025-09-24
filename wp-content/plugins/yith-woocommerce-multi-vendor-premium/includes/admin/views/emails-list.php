<?php
/**
 * Emails tab content.
 *
 * @var WC_Email[] $emails A list of plugin emails.
 * @since 5.0.0
 * @auhtor YITH
 * @package YITH\MultiVendor
 */

defined( 'YITH_WPV_INIT' ) || exit();

$columns = array(
	'name'      => _x( 'Email', 'Email list header', 'yith-woocommerce-product-vendors' ),
	'recipient' => _x( 'Recipient(s)', 'Email list header', 'yith-woocommerce-product-vendors' ),
	'status'    => _x( 'Active', 'Email list header', 'yith-woocommerce-product-vendors' ),
	'actions'   => '',
);

?>
<div id="emails-container">
	<div class="emails-headings">
		<?php foreach ( $columns as $key => $column ) : ?>
			<div class="emails-heading-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $column ); ?></div>
		<?php endforeach; ?>
	</div>
	<div class="emails-list">
		<?php foreach ( $emails as $email_key => $email ) : ?>
			<div class="single-email" data-key="<?php echo esc_attr( $email_key ); ?>">
				<div class="single-email-headings">
					<?php foreach ( $columns as $key => $column ) : ?>
						<div class="single-email-heading-<?php echo esc_attr( $key ); ?>">
							<?php
							switch ( $key ) {
								case 'name':
									echo '<strong>' . esc_html( $email->get_title() ) . '</strong>';
									echo '<div class="description">' . esc_html( $email->get_description() ) . '</div>';
									break;
								case 'recipient':
									echo esc_html( $email->is_customer_email() ? __( 'Customer', 'yith-woocommerce-product-vendors' ) : ( empty( $email->get_recipient() ) ? __( 'Vendor', 'yith-woocommerce-product-vendors' ) : $email->get_recipient() ) );
									break;
								case 'status':
									if ( $email->is_manual() ) {
										echo esc_html__( 'Manually sent', 'yith-woocommerce-product-vendors' );
									} else {
										yith_plugin_fw_get_field(
											array(
												'type'  => 'onoff',
												'value' => $email->is_enabled(),
												'class' => 'single-email-toggle-active',
											),
											true
										);
									}
									break;
								case 'actions':
									yith_plugin_fw_get_component(
										array(
											'class'  => 'single-email-toggle-editing',
											'type'   => 'action-button',
											'action' => 'edit',
											'icon'   => 'edit',
											'title'  => __( 'Edit', 'yith-woocommerce-product-vendors' ),
											'url'    => '#',
										)
									);
									break;
								default:
									break;
							}
							?>
						</div>
					<?php endforeach; ?>
				</div>
				<div class="single-email-options">
					<form class="single-email-settings-form" method="POST">
						<table class="form-table">
							<?php $email->generate_settings_html(); ?>
						</table>
						<div class="single-email-actions">
							<input type="hidden" name="request" value="email_save_settings"/>
							<input type="hidden" name="email" value="<?php echo esc_attr( $email_key ); ?>"/>
							<input type="submit" class="single-email-save yith-plugin-fw__button yith-plugin-fw__button--primary yith-plugin-fw__button--xl"
								data-saved-message="<?php esc_attr_e( 'Saved!', 'yith-woocommerce-product-vendors' ); ?>" value="<?php esc_html_e( 'Save', 'yith-woocommerce-product-vendors' ); ?>"/>
						</div>
					</form>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</div>