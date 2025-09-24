<?php
/**
 * Admin View: Settings
 *
 * @author YITH <plugins@yithemes.com>
 * @package YITH\Deposits\Templates
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>
<div class="yith-plugin-fw yit-admin-panel-container" id="yith-wcdp-emails-wrapper">
	<div id="yith-wcdp-table-emails">
		<div class="heading-table yith-wcdp-row">
			<span class="yith-wcdp-column email"><?php esc_html_e( 'Email', 'yith-woocommerce-deposits-and-down-payments' ); ?></span>
			<span class="yith-wcdp-column status"><?php esc_html_x( 'Active', '[ADMIN] Column name table emails', 'yith-woocommerce-deposits-and-down-payments' ); ?></span>
			<span class="yith-wcdp-column action"></span>
		</div>
		<div class="content-table">
			<?php foreach ( $emails_table as $email_key => $email ) : ?>
				<?php $url = YITH_WCDP_Admin()->build_single_email_settings_url( $email_key ); ?>
				<div class="yith-wcdp-row">
					<span class="yith-wcdp-column email">
						<?php echo esc_html( $email['title'] ); ?>
					</span>
					<span class="yith-wcdp-column status">
						<?php
							$email_status = array(
								'id'      => 'yith-wcdp-email-status',
								'type'    => 'onoff',
								'default' => 'yes',
								'value'   => $email['enable'],
								'data'    => array(
									'email_key' => $email_key,
								),
							);

							yith_plugin_fw_get_field( $email_status, true );
							?>
					</span>
					<span class="yith-wcdp-column action">
						<?php
						yith_plugin_fw_get_component(
							array(
								'title'  => __( 'Edit', 'yith-woocommerce-deposits-and-down-payments' ),
								'type'   => 'action-button',
								'action' => 'edit',
								'icon'   => 'edit',
								'url'    => esc_url( $url ),
								'data'   => array(
									'target' => $email_key,
								),
								'class'  => 'toggle-settings',
							)
						);
						?>
					</span>
					<div class="email-settings" id="<?php echo esc_attr( $email_key ); ?>">
						<?php do_action( 'yith_wcdp_print_email_settings', $email_key ); ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</div>
