<?php
/**
 * Admin custom settings
 *
 * @author      WooThemes
 * @package     WC_OD
 * @since       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/** Custom settings *********************************************************/


/**
 * Outputs the content for a custom field within a wrapper.
 *
 * @since 1.0.0
 *
 * @param array $field The field data.
 */
function wc_od_field_wrapper( $field ) {
	// Description handling.
	if ( true === $field['desc_tip'] ) {
		$field['desc_tip'] = $field['desc'];
		$field['desc'] = '';
	}

	$field['desc'] = wp_kses_post( $field['desc'] );

	// Custom attributes handling.
	$custom_attributes = array();
	if ( !empty( $field['custom_attributes'] ) && is_array( $field['custom_attributes'] ) ) {
		foreach ( $field['custom_attributes'] as $attribute => $attribute_value ) {
			$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
		}
	}

	$field['custom_attributes'] = $custom_attributes;

	/**
	 * Filters the function used for output the field content within a wrapper.
	 *
	 * @since 1.0.0
	 * @param callable $callable The callable function.
	 * @param array    $field    The field data.
	 */
	$callback = apply_filters( 'wc_od_field_wrapper_callback', "{$field['type']}_field", $field );
	?>
	<tr valign="top">
		<th scope="row" class="titledesc">
			<label for="<?php echo esc_attr( $field['id'] ); ?>"><?php echo esc_html( $field['title'] ); ?></label>
			<?php echo ( $field['desc_tip'] ? wc_help_tip( $field['desc_tip'], true ) : '' ); ?>
		</th>
		<td class="forminp forminp-<?php echo sanitize_title( $field['type'] ) ?>">
		<?php
			if ( $callback && is_callable( $callback ) ) :
				call_user_func( $callback, $field );
			endif;
		?>
		</td>
	</tr>
	<?php
}

/**
 * Outputs the content for the wc_od_shipping_days field.
 *
 * @since 1.0.0
 * @param array $field The field data.
 */
function wc_od_shipping_days_field( $field ) {
	$week_days     = wc_od_get_week_days();
	$field_id      = $field['id'];
	$shipping_days = WC_OD()->settings()->get_setting( $field_id );
	?>
	<fieldset>
	<?php foreach ( $shipping_days as $key => $data ) : ?>
		<label for="<?php echo esc_attr( "{$field_id}_{$key}" ); ?>" style="display:inline-block;width:125px;">
		<input id="<?php echo esc_attr( "{$field_id}_{$key}" ); ?>" type="checkbox" name="<?php echo esc_attr( $field_id . "[{$key}][enabled]" ); ?>" <?php checked( (bool) $data['enabled'], true ); ?> />
		<?php echo wp_kses_post( $week_days[ $key ] ); ?></label>

		<?php $limit_id = wc_od_maybe_prefix( "shipping_days_time_{$key}" ); ?>
		<label for="<?php echo esc_attr( $limit_id ); ?>">
			<span class="shipping-days-time-label" style="font-size:12px;"><?php _e( 'limit:', 'woocommerce-order-delivery' ); ?></span>
			<input class="timepicker" id="<?php echo esc_attr( $limit_id ); ?>" type="text" name="<?php echo esc_attr( $field_id . "[{$key}][time]" ); ?>" value="<?php echo esc_attr( $data['time'] ); ?>" style="width:80px;" />
		</label>
		<br>
	<?php endforeach; ?>

	<?php if ( $field['desc'] ) : ?>
		<p class="description"><?php echo $field['desc']; ?></p>
	<?php endif; ?>
	</fieldset>
	<?php
}

/**
 * Outputs the content for the wc_od_day_range field.
 *
 * @since 1.0.0
 * @param array $field The field data.
 */
function wc_od_day_range_field( $field ) {
	$field_id = $field['id'];
	$value    = WC_OD()->settings()->get_setting( $field_id );
	?>
	<label for="<?php echo $field_id; ?>">
	<?php
		printf( __( 'Between %1$s and %2$s days.', 'woocommerce-order-delivery' ),
			sprintf(
				'<input id="%1$s" name="%1$s[min]" type="number" value="%2$s" style="%3$s" %4$s />',
				$field_id,
				esc_attr( $value['min'] ),
				esc_attr( $field['css'] ),
				implode( ' ', $field['custom_attributes'] )
			),
			sprintf(
				'<input id="%1$s" name="%1$s[max]" type="number" value="%2$s" style="%3$s" %4$s />',
				$field_id,
				esc_attr( $value['max'] ),
				esc_attr( $field['css'] ),
				implode( ' ', $field['custom_attributes'] )
			)
		);
	?>
	</label>
	<?php if ( $field['desc'] ) : ?>
		<p class="description"><?php echo $field['desc']; ?></p>
	<?php endif; ?>
	<?php
}

/**
 * Outputs the content for the wc_od_delivery_days field.
 *
 * @since 1.0.0
 * @param array $field The field data.
 */
function wc_od_delivery_days_field( $field ) {
	$week_days     = wc_od_get_week_days();
	$field_id      = $field['id'];
	$delivery_days = WC_OD()->settings()->get_setting( $field_id );
	?>
	<fieldset>
	<?php foreach ( $delivery_days as $key => $data ) : ?>
		<label for="<?php echo esc_attr( "{$field_id}_{$key}" ); ?>">
		<input id="<?php echo esc_attr( "{$field_id}_{$key}" ); ?>" type="checkbox" name="<?php echo esc_attr( $field_id . "[{$key}][enabled]" ); ?>" <?php checked( (bool) $data['enabled'], true ); ?> />
		<?php echo wp_kses_post( $week_days[ $key ] ); ?></label>
		<br>
	<?php endforeach; ?>
		<?php if ( $field['desc'] ) : ?>
			<p class="description"><?php echo $field['desc']; ?></p>
		<?php endif; ?>
	</fieldset>
	<?php
}
