<?php
/**
 * Modules tab content.
 *
 * @var array $available_modules     The available modules data.
 * @var array $non_available_modules The non-available modules data.
 *
 * @package YITH\GiftCards\Views
 */

defined( 'ABSPATH' ) || exit();

$premium_url = yith_plugin_fw_add_utm_data( YITH_YWGC_PREMIUM_LANDING_URL, YITH_YWGC_SLUG, 'button-upgrade', 'wp-extended-dashboard' );

?>
<div class="yith-ywgc-modules">
	<div class="modules">
		<?php foreach ( $available_modules as $module_data ) : ?>
			<?php yith_ywgc_get_view( 'settings-tabs/html-module.php', compact( 'module_data' ) ); ?>
		<?php endforeach; ?>
	</div>
	<?php if ( $non_available_modules ) : ?>
		<div class="premium-modules">
			<div class="premium-modules__header">
				<div class="premium-modules__title">
					<?php echo esc_html_x( 'Need more? Get our premium modules!', 'Messsage in the modules tab', 'yith-woocommerce-gift-cards' ); ?>
				</div>
				<div class="premium-modules__description">
					<?php echo esc_html_x( 'Upgrade to the premium version to get more advanced modules.', 'Messsage in the modules tab', 'yith-woocommerce-gift-cards' ); ?>
				</div>
				<div class="premium-modules__cta">
					<a class="yith-ywgc-get-premium-button" href="<?php echo esc_url( $premium_url ); ?>"><?php echo esc_html_x( 'Get premium', 'Get premium button in the modules tab', 'yith-woocommerce-gift-cards' ); ?></a>
				</div>
			</div>
			<div class="modules">
				<?php foreach ( $non_available_modules as $module_data ) : ?>
					<?php yith_ywgc_get_view( 'settings-tabs/html-module.php', compact( 'module_data' ) ); ?>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endif; ?>
</div>
