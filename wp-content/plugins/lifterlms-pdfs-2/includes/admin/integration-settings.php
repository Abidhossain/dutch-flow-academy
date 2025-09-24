<?php
/**
 * LifterLMS PDFs Integration Settings.
 *
 * @package LifterLMS_PDFS/Admin
 *
 * @since 2.0.0
 * @version 2.1.0
 */

defined( 'ABSPATH' ) || exit;

$settings = array();

$settings[] = array(
	'id'    => 'pdfs-general-opts',
	'title' => __( 'General Settings', 'lifterlms-pdfs' ),
	'type'  => 'subtitle',
);

$settings[] = array(
	'id'      => $this->get_option_name( 'header_title' ),
	'title'   => __( 'Export Header Title', 'lifterlms-pdfs' ),
	'desc'    => '<br>' . __( 'Text displayed as the title in the header of most exports. Leave blank to disable.', 'lifterlms-pdfs' ),
	'type'    => 'text',
	'default' => get_bloginfo( 'name' ),
);

$settings[] = array(
	'id'    => $this->get_option_name( 'header_description' ),
	'title' => __( 'Export Header Description', 'lifterlms-pdfs' ),
	'desc'  => '<br>' . __( 'Additional text displayed in the header of most exports. Leave blank to disable.', 'lifterlms-pdfs' ),
	'type'  => 'textarea',
);

$settings[] = array(
	'desc'    => '<br>' . __( 'Determines the paper size used for most export types.', 'lifterlms-pdfs' ),
	'id'      => $this->get_option_name( 'paper_size' ),
	'title'   => __( 'Paper size', 'lifterlms-pdfs' ),
	'type'    => 'select',
	'default' => 'letter',
	'options' => array(
		'letter' => __( 'Letter (8.5" x 11")', 'lifterlms' ),
		'legal'  => __( 'Legal (8.5" x 14")', 'lifterlms' ),
		'ledger' => __( 'Ledger (11" x 17")', 'lifterlms' ),
		'a5'     => __( 'A5 (148mm x 210mm)', 'lifterlms' ),
		'a4'     => __( 'A4 (210mm x 297mm)', 'lifterlms' ),
		'a3'     => __( 'A3 (297mm x 420mm)', 'lifterlms' ),
	),
);

$settings[] = array(
	'id'    => 'pdfs-export-types-opts',
	'title' => __( 'Export Types', 'lifterlms-pdfs' ),
	'desc'  => __( 'Choose what types of PDF downloads are made available to your students.', 'lifterlms-pdfs' ),
	'type'  => 'subtitle',
);

$content_type_option_name = $this->get_option_name( 'content_types' );
foreach ( $this->get_exportable_content_types() as $content_type ) {

	$type_id = $content_type->get_id();

	$settings[] = array(
		'default'            => 'yes',
		'desc'               => $content_type->get_description(),
		'id'                 => $content_type_option_name . "[{$type_id}]",
		'title'              => $content_type->get_title(),
		'type'               => 'checkbox',
		'_x_content_type_id' => $type_id,
	);

}

$settings[] = array(
	'id'    => 'pdfs-proxy-opts',
	'title' => __( 'Image Proxy Settings', 'lifterlms-pdfs' ),
	'desc'  => sprintf(
		// Translators: %1$s = opening anchor tag linking to documentation; %2$s = closing anchor tag.
		__( 'If the site utilizes a CDN, an image proxy is necessary to enable client-side PDF generators to include images served from the CDN. %1$sRead more%2$s.', 'lifterlms-pdfs' ),
		'<a href="https://lifterlms.com/docs/pdf-image-proxy/">',
		'</a>'
	),
	'type'  => 'subtitle',
);

$settings[] = array(
	'id'      => $this->get_option_name( 'proxy_enabled' ),
	'title'   => __( 'Enable Image Proxy', 'lifterlms-pdfs' ),
	'desc'    => __( 'Use the image proxy for client-side PDF generation.', 'lifterlms-pdfs' ),
	'type'    => 'checkbox',
	'default' => 'no',
);

$settings[] = array(
	'id'      => $this->get_option_name( 'proxy_allowed_origins' ),
	'title'   => __( 'Allowed Origins', 'lifterlms-pdfs' ),
	'desc'    => '<br>' . __( 'Only images with URLs matching a domain (or domain part) in this list will be passed through the proxy. Enter one domain (or partial domain) per line. To allow any domain, use the "*" wildcard character.', 'lifterlms-pdfs' ),
	'type'    => 'textarea',
	'default' => '*',
);

return $settings;
