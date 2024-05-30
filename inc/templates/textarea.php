<?php
/** @var array $addon */
foreach ( $addon['options'] as $key => $option ) :
	/**
	 * @var WC_Product $product
	 * @var Lafka_Product_Addon_Display $Product_Addon_Display
	 */

	global $product;
	global $Product_Addon_Display;

	$option_price = lafka_get_option_price_on_default_attribute($product, $option['price']);
	$option_price_for_display = '';
	if ( is_numeric( $option_price ) ) {
		$option_price_for_display = '(' . wc_price( WC_Product_Addons_Helper::get_product_addon_price_for_display( $option_price ) ) . ')';
	}

	$addon_key     = 'addon-' . sanitize_title( $addon['field-name'] );
	$option_key    = empty( $option['label'] ) ? $key : sanitize_title( $option['label'] );
	$current_value = isset( $_POST[ $addon_key ] ) && isset( $_POST[ $addon_key ][ $option_key ] ) ? wc_clean( $_POST[ $addon_key ][ $option_key ] ) : '';
	$price = apply_filters( 'lafka_product_addons_option_price',
		$option_price_for_display,
		$option,
		$key,
		'textarea'
	);

	$attribute_raw_prices = $option['price'];
	$attribute_prices = lafka_convert_attribute_raw_prices_to_prices( $attribute_raw_prices );
	?>

	<p class="form-row form-row-wide addon-wrap-<?php echo sanitize_title( $addon['field-name'] ); ?>">
		<?php if ( ! empty( $option['label'] ) ) : ?>
            <label>
				<?php echo wptexturize( $option['label'] ) . ' ' . $price; ?>
            </label>
		<?php endif; ?>
		<textarea type="text" class="input-text addon addon-custom-textarea"
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
                  name="<?php echo $addon_key ?>[<?php echo $option_key; ?>]" rows="4" cols="20" <?php if ( ! empty( $option['max'] ) ) echo 'maxlength="' . $option['max'] .'"'; ?>><?php echo esc_textarea( $current_value ); ?></textarea>
	</p>

<?php endforeach; ?>
