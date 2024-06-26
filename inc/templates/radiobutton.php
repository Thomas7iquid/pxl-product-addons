<?php
/** @var array $addon */
foreach ( $addon['options'] as $i => $option ) :
	/**
	 * @var WC_Product $product
	 * @var Lafka_Product_Addon_Display $Product_Addon_Display
	 */

	global $product;
	global $Product_Addon_Display;

	$option_price             = lafka_get_option_price_on_default_attribute( $product, $option['price'] );
	$option_price_for_display = '';
	if ( is_numeric( $option_price ) ) {
		$option_price_for_display = '(' . wc_price( WC_Product_Addons_Helper::get_product_addon_price_for_display( $option_price ) ) . ')';
	}

	$price = apply_filters( 'lafka_product_addons_option_price', $option_price_for_display, $option, $i, 'radiobutton' );

	$current_value = 0;

	if ( isset( $_POST[ 'addon-' . sanitize_title( $addon['field-name'] ) ] ) ) {
		$current_value = (
				isset( $_POST[ 'addon-' . sanitize_title( $addon['field-name'] ) ] ) &&
				in_array( sanitize_title( $option['label'] ), $_POST[ 'addon-' . sanitize_title( $addon['field-name'] ) ] )
				) ? 1 : 0;
	} elseif ( ! empty( $option['default'] ) ) {
		$current_value = $option['default'];
	}

	$attribute_raw_prices = $option['price'];
	$attribute_prices = lafka_convert_attribute_raw_prices_to_prices( $attribute_raw_prices );
	?>

	<p class="form-row form-row-wide addon-wrap-<?php echo sanitize_title( $addon['field-name'] ) . '-' . $i; ?>">
		<label><input type="radio" class="addon addon-radio" name="addon-<?php echo sanitize_title( $addon['field-name'] ); ?>[]"
                    data-attribute-raw-prices="<?php echo esc_attr( json_encode( $attribute_raw_prices ) ); ?>"
                    data-attribute-prices="<?php echo esc_attr( json_encode( $attribute_prices ) ); ?>"
                    <?php $addon_attribute = isset( $addon['attribute'] ) ? wc_get_attribute( $addon['attribute'] ) : null; ?>
                    <?php if ( ! is_null( $addon_attribute ) && isset( $attribute_prices[ $addon_attribute->slug ] ) && is_array( $attribute_prices[ $addon_attribute->slug ] ) ): ?>
                        <?php foreach ( $attribute_prices[ $addon_attribute->slug ] as $attribute => $attr_price ): ?>
                            data-<?php echo esc_html( $attribute ) ?>-formatted-price="<?php echo esc_html( wc_price( $attr_price ) ) ?>"
                        <?php endforeach; ?>
                    <?php endif; ?>
                    data-raw-price="<?php echo esc_attr( $option_price ); ?>"
                    data-price="<?php echo WC_Product_Addons_Helper::get_product_addon_price_for_display( $option_price ); ?>"
                    value="<?php echo sanitize_title( $option['label'] ); ?>" <?php checked( $current_value, 1 ); ?> /><?php echo ' '; ?>
			<?php echo wptexturize( $option['label'] . ' ' . $price ); ?></label>
	</p>

<?php endforeach; ?>
