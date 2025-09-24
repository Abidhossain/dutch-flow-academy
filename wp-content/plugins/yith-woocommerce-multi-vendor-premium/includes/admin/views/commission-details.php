<?php
/**
 * Commission details template
 *
 * @since   4.0.0
 * @author  YITH
 * @package YITH\MultiVendor
 */
/**
 * This file belongs to the YIT Framework.
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

?>

<script type="text/template" id="tmpl-yith-wcmv-modal-commission-header">
	<?php /* translators: %s: commission ID */ ?>
	<h2><?php echo esc_html( sprintf( _x( 'Commission #%s', '[Admin Commission Detail]Modal title', 'yith-woocommerce-product-vendors' ), '{{ data.id }}' ) ); ?></h2>
	<?php /* translators: %1$s: Commission user  %2$s: Commission order*/ ?>
	<p><?php echo sprintf( __( 'Credited to vendor %1$s from order %2$s', 'yith-woocommerce-product-vendors' ), '{{{ data.user }}}', '{{{ data.order }}}' ); ?></p>
</script>
<script type="text/template" id="tmpl-yith-wcmv-modal-commission-content">
	<div class="commission-details-data">
		<div class="general-data">
			<h3><?php echo esc_html_x( 'General info', '[Admin Commission Detail]Section title', 'yith-woocommerce-product-vendors' ); ?></h3>
			<ul>
				<li>
					<strong><?php echo esc_html_x( 'Date:', '[Admin Commission Detail]Detail label', 'yith-woocommerce-product-vendors' ); ?></strong>
					{{{data.date }}}
				</li>
				<li>
					<strong><?php echo esc_html_x( 'Last update:', '[Admin Commission Detail]Detail label', 'yith-woocommerce-product-vendors' ); ?></strong>
					{{{data.last_edit }}}
				</li>
				<li>
					<strong><?php echo esc_html_x( 'Status:', '[Admin Commission Detail]Detail label', 'yith-woocommerce-product-vendors' ); ?></strong>
					{{{data.status_html }}}
				</li>
			</ul>
		</div>
		<div class="vendor-data">
			<h3><?php echo esc_html_x( 'Vendor info', '[Admin Commission Detail]Section title', 'yith-woocommerce-product-vendors' ); ?></h3>
			<# if ( data.vendor ) { #>
			<ul>
				<li>
					<strong><?php echo esc_html_x( 'Vendor:', '[Admin Commission Detail]Detail label', 'yith-woocommerce-product-vendors' ); ?></strong>
					{{{data.vendor_owner }}}
					(<a href="mailto:{{ data.vendor_owner_email }}">{{ data.vendor_owner_email }}</a>)
				</li>
				<li>
					<strong><?php echo esc_html_x( 'Store:', '[Admin Commission Detail]Detail label', 'yith-woocommerce-product-vendors' ); ?></strong>
					{{{data.vendor }}}
				</li>
				<# if ( data.vendor_paypal ) { #>
				<li>
					<strong><?php echo esc_html_x( 'PayPal:', '[Admin Commission Detail]Detail label', 'yith-woocommerce-product-vendors' ); ?></strong>
					{{ data.vendor_paypal }}
				</li>
				<# } #>
				<# if ( data.vendor_bank ) { #>
				<li>
					<strong><?php echo esc_html_x( 'Bank account:', '[Admin Commission Detail]Detail label', 'yith-woocommerce-product-vendors' ); ?></strong>
					{{ data.vendor_bank }}
				</li>
				<# } #>
			</ul>
			<# } else { #>
			<?php echo esc_html__( 'Vendor deleted.', 'yith-woocommerce-product-vendors' ); ?>
			<# } #>
		</div>
	</div>
	<div class="commission-details-item">
		<table class="commission-item">
			<tbody>
			<tr>
				<td class="thumb">{{{data.item_image}}}</td>
				<td class="name">{{{data.item_name}}}</td>
				<td class="price">{{{data.item_price}}}</td>
			</tr>
			</tbody>
		</table>
		<table class="commission-total">
			<tbody>
			<# if( 'product' == data.type ) { #>
			<tr>
				<td class="label"><?php echo esc_html_x( 'Rate:', '[Admin Commission Detail]Detail label', 'yith-woocommerce-product-vendors' ); ?></td>
				<td class="total">{{{data.rate}}}</td>
			</tr>
			<# } #>
			<tr>
				<td class="label"><?php echo esc_html_x( 'Commission:', '[Admin Commission Detail]Detail label', 'yith-woocommerce-product-vendors' ); ?></td>
				<td class="total">{{{data.amount}}}</td>
			</tr>
			<# if( data.refunded ) { #>
			<tr>
				<td class="label refunded"><?php echo esc_html_x( 'Refunded:', '[Admin Commission Detail]Detail label', 'yith-woocommerce-product-vendors' ); ?></td>
				<td class="total refunded">{{{data.refunded}}}</td>
			</tr>
			<# } #>
			<tr>
				<td class="label"><?php echo esc_html_x( 'Total:', '[Admin Commission Detail]Detail label', 'yith-woocommerce-product-vendors' ); ?></td>
				<td class="total">{{{data.to_pay}}}</td>
			</tr>
			</tbody>
		</table>
	</div>
	<# if( data.notes_html ) { #>
	<div class="commission-details-note">
		<h3><?php echo esc_html_x( 'Commission notes', '[Admin Commission Detail]Section title', 'yith-woocommerce-product-vendors' ); ?></h3>
		<ul class="commission-notes">
			{{{data.notes_html}}}
		</ul>
	</div>
	<# } #>
</script>
<script type="text/template" id="tmpl-yith-wcmv-modal-commission-footer">
	<# if( data.save ) { #>
	<a href="javascript:void(0)" class="yith-plugin-fw__button--primary yith-plugin-fw__button--xl save-commission" data-commission_id="{{data.id}}"><?php echo esc_html_x( 'Save', '[Admin Commission Detail]Modal save button label', 'yith-woocommerce-product-vendors' ); ?></a>
	<# } #>
</script>
