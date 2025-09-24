<?php
/**
 * Expiration fallback field
 *
 * @author YITH <plugins@yithemes.com>
 * @package YITH\Deposits\Views
 * @version 1.0.0
 */

/**
 * Template variables:
 *
 * @var $field array
 */

if ( ! defined( 'YITH_WCDP' ) ) {
	exit;
} // Exit if accessed directly
?>

<div id="<?php echo esc_attr( $field['id'] ); ?>">
	<?php
	yith_plugin_fw_get_field(
		array(
			'id'      => "{$field['id']}_initial_fallback",
			'type'    => 'select',
			'class'   => 'wc-enhanced-select',
			'name'    => "{$field['id']}[initial_fallback]",
			'options' => $field['fallbacks'],
			'default' => 'none',
			'value'   => isset( $field['value']['initial_fallback'] ) ? $field['value']['initial_fallback'] : false,
		),
		true
	);
	?>

	<?php for ( $i = 1; $i < $field['max_attempts']; $i++ ) : ?>
		<?php
		$is_first = 1 === $i;
		$is_last  = 1 === ( $field['max_attempts'] - $i );

		if ( $is_first ) {
			$dependencies = array(
				"{$field['id']}_initial_fallback" => 'retry',
			);
		} else {
			$prev         = $i - 1;
			$dependencies = array(
				"{$field['id']}_attempts_{$prev}_fallback" => 'retry',
			);
		}

		if ( $is_last ) {
			unset( $field['fallbacks']['retry'] );
		}
		?>
		<div class="next-attempt option-element" id="attempt_<?php echo esc_attr( $i ); ?>" data-dependencies="<?php echo esc_attr( wp_json_encode( $dependencies ) ); ?>">
			<?php echo esc_html_x( 'and', '[ADMIN] Expiration fallback field', 'yith-woocommerce-deposits-and-down-payments' ); ?>
			<?php
			yith_plugin_fw_get_field(
				array(
					'id'                => "{$field['id']}_attempts_{$i}_days_after",
					'type'              => 'number',
					'name'              => "{$field['id']}[attempts][$i][days_after]",
					'default'           => 1,
					'value'             => isset( $field['value']['attempts'][ $i ]['days_after'] ) ? $field['value']['attempts'][ $i ]['days_after'] : false,
					'custom_attributes' => array(
						'min' => 1,
					),
				),
				true
			);
			?>
			<?php
			// translators: 1. Condition that could lead to another attempt (EG: if the balance is still not paid).
			echo esc_html( sprintf( _x( 'day(s) after, %s, then', '[ADMIN] Expiration fallback field', 'yith-woocommerce-deposits-and-down-payments' ), $field['fail_description'] ) );
			?>
			<?php
			yith_plugin_fw_get_field(
				array(
					'id'      => "{$field['id']}_attempts_{$i}_fallback",
					'type'    => 'select',
					'class'   => 'wc-enhanced-select',
					'name'    => "{$field['id']}[attempts][$i][fallback]",
					'options' => $field['fallbacks'],
					'default' => 'none',
					'value'   => isset( $field['value']['attempts'][ $i ]['fallback'] ) ? $field['value']['attempts'][ $i ]['fallback'] : false,
				),
				true
			);
			?>
		</div>
	<?php endfor; ?>
</div>
