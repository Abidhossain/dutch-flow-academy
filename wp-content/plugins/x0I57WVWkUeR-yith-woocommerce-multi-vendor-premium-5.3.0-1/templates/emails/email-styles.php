<?php
/**
 * Email Styles
 *
 * @package YITH\MultiVendor
 * @version 5.0.0
 * @auhtor YITH
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

// Load colors.
$base = get_option( 'woocommerce_email_base_color' );

?>
.yith-vendor-quote {
	background-color: #f5f5f5;
	padding: 30px 20px 30px 45px;
	border-radius: 10px;
	position: relative;
}
.yith-vendor-quote .quotation-mark {
	position: absolute;
	top: 10px;
	left: 10px;
}
.yith-vendor-button-cta {
	padding: 20px;
	text-transform: uppercase;
	color: #ffffff;
	font-size: 13px;
	font-weight: 400;
	letter-spacing: .5px;
	border-radius: 10px;
	display: block;
	box-shadow: none;
	background-color: <?php echo esc_attr( $base ); ?>;
	cursor: pointer;
	text-align: center;
	text-decoration: none;
	margin: 0 0 16px;
}
<?php
