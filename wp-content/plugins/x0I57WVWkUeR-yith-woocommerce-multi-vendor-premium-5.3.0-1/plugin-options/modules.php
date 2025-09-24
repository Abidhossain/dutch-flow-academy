<?php
/**
 * A list of available modules
 *
 * @since 5.0.0
 * @author YITH
 * @package YITH\MultiVendor
 */

return apply_filters(
	'yith_wcmv_modules',
	array(

		'seller-vacation'  => array(
			'title'       => __( 'Vendors vacations', 'yith-woocommerce-product-vendors' ),
			'description' => __( 'If you enable this option, vendors will be able to close their shops for vacation.', 'yith-woocommerce-product-vendors' ),
			'autoload'    => array(
				'yith-vendors-vacation-admin' => 'modules/vacation/class-yith-vendors-vacation-admin.php',
			),
			'includes'    => array(
				'common' => 'vacation/class-yith-vendors-vacation.php',
			),
		),

		'shipping'         => array(
			'title'       => __( 'Vendors shipping', 'yith-woocommerce-product-vendors' ),
			'description' => __( 'If you enable this option, vendors will be able to set their own costs for their shipping methods.', 'yith-woocommerce-product-vendors' ),
			'autoload'    => array(
				'yith-vendors-shipping-admin'    => 'modules/shipping/class-yith-vendors-shipping-admin.php',
				'yith-vendors-shipping-frontend' => 'modules/shipping/class-yith-vendors-shipping-frontend.php',
			),
			'includes'    => array(
				'common' => 'shipping/class-yith-vendors-shipping.php',
			),
		),

		'announcements'    => array(
			'title'       => __( 'Vendors announcements', 'yith-woocommerce-product-vendors' ),
			'description' => __( 'If you enable this option, you\'ll be able to create announcements to be shown on the vendors\' dashboards.', 'yith-woocommerce-product-vendors' ),
			'admin_tabs'  => array(
				'title'       => _x( 'Announcements', '[Admin]Sub-tab title.', 'yith-woocommerce-product-vendors' ),
				'icon'        => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6"><path stroke-linecap="round" stroke-linejoin="round" d="M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 1 1 0-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.657.38c-.551.318-1.26.117-1.527-.461a20.845 20.845 0 0 1-1.44-4.282m3.102.069a18.03 18.03 0 0 1-.59-4.59c0-1.586.205-3.124.59-4.59m0 9.18a23.848 23.848 0 0 1 8.835 2.535M10.34 6.66a23.847 23.847 0 0 0 8.835-2.535m0 0A23.74 23.74 0 0 0 18.795 3m.38 1.125a23.91 23.91 0 0 1 1.014 5.395m-1.014 8.855c-.118.38-.245.754-.38 1.125m.38-1.125a23.91 23.91 0 0 0 1.014-5.395m0-3.46c.495.413.811 1.035.811 1.73 0 .695-.316 1.317-.811 1.73m0-3.46a24.347 24.347 0 0 1 0 3.46" /></svg>',
				'description' => _x( 'Create dynamic notifications to display on vendor dashboards.', '[Admin]Panel tab description', 'yith-woocommerce-product-vendors' ),
			),
			'autoload'    => array(
				'yith-vendors-announcement'        => 'modules/announcements/class-yith-vendors-announcement.php',
				'yith-vendors-announcements-admin' => 'modules/announcements/class-yith-vendors-announcements-admin.php',
			),
			'includes'    => array(
				'common' => 'announcements/class-yith-vendors-announcements.php',
			),
		),

		'report-abuse'     => array(
			'title'       => __( 'Vendors abuse report', 'yith-woocommerce-product-vendors' ),
			'description' => __( 'If you enable this option, a "Report abuse" link will be shown on all of the product pages.', 'yith-woocommerce-product-vendors' ),
			'admin_tabs'  => array(
				'title' => _x( 'Abuse report', '[Admin]Sub-tab title.', 'yith-woocommerce-product-vendors' ),
				'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" /></svg>',
			),
			'includes'    => array(
				'common' => 'report-abuse/class-yith-vendors-report-abuse.php',
			),
		),

		'staff'            => array(
			'title'       => __( 'Vendor staff', 'yith-woocommerce-product-vendors' ),
			'description' => __( 'If you enable this option, vendors will be able to add staff members to their stores.', 'yith-woocommerce-product-vendors' ),
			'includes'    => array(
				'admin'  => 'staff/class-yith-vendors-staff-admin.php',
				'common' => 'staff/class-yith-vendors-staff.php',
			),
		),

		'registration-fee' => array(
			'title'       => __( 'Vendor registration fee', 'yith-woocommerce-product-vendors' ),
			'description' => __( 'If you enable this option, vendors will have to pay a fee in order to complete the registration process.', 'yith-woocommerce-product-vendors' ),
			'admin_tabs'  => array(
				'title'       => _x( 'Registration fee', '[Admin]Sub-tab title.', 'yith-woocommerce-product-vendors' ),
				'icon'        => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" /></svg>',
				'description' => _x( 'Set the fee options for vendors who want to join your marketplace.', '[Admin]Panel tab description', 'yith-woocommerce-product-vendors' ),
			),
			'includes'    => array(
				'admin'  => 'registration-fee/class-yith-vendors-registration-fee-admin.php',
				'common' => 'registration-fee/class-yith-vendors-registration-fee.php',
			),
		),
	)
);
