<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PXL_Product_Addon_Admin {

	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'styles' ), 100 );
		add_action( 'admin_menu', array( $this, 'admin_menu' ), 9 );
		//add_filter( 'woocommerce_screen_ids', array( $this, 'add_screen_id' ) );
		add_action( 'woocommerce_product_write_panel_tabs', array( $this, 'tab' ) );
		add_action( 'woocommerce_product_data_panels', array( $this, 'panel' ) );
		//add_action( 'woocommerce_process_product_meta', array( $this, 'process_meta_box' ), 1 );
	}

	public function styles() {
		//wp_enqueue_script( 'wc-enhanced-select' );
		wp_enqueue_style( 'pxl_product_addons_css', plugins_url( '../../assets/css/admin.css', __FILE__ ), array('woocommerce_admin_styles') );
	}

	public function admin_menu() {
		$page = add_submenu_page( 'edit.php?post_type=product', esc_html__( 'PXL Global Add-ons', 'pxl-product-addons' ), esc_html__( 'PXL Global Add-ons', 'pxl-product-addons' ), 'manage_woocommerce', 'pxl_global_addons', array( $this, 'global_addons_admin' ) );
	}

	public function tab() {
		?><li class="addons_tab product_addons"><a href="#product_addons_data"><span><?php esc_html_e( 'PXL Add-ons', 'lafka-plugin' ); ?></span></a></li><?php
	}

	public function panel() {
		global $post;

		$product        = wc_get_product( $post );
		$exists         = (bool) $product->get_id();
		$product_addons = array_filter( (array) $product->get_meta( '_product_addons' ) );
		$exclude_global = $product->get_meta( '_product_addons_exclude_global' );
		$attribute_taxonomies = wc_get_attribute_taxonomies();

		include( dirname( __FILE__ ) . '/views/addon-panel-html.php' );
	}

	public function global_addons_admin() {
		if ( ! empty( $_GET['add'] ) || ! empty( $_GET['edit'] ) ) {

			if ( $_POST ) {

				if ( $edit_id = $this->save_global_addons() ) {
					echo '<div class="updated"><p>' . esc_html__( 'Add-on saved successfully', 'lafka-plugin' ) . '</p></div>';
				}

				$reference      = wc_clean( $_POST['addon-reference'] );
				$priority       = absint( $_POST['addon-priority'] );
				$objects        = ! empty( $_POST['addon-objects'] ) ? array_map( 'absint', $_POST['addon-objects'] ) : array();
				$product_addons = array_filter( (array) $this->get_posted_product_addons() );
			}

			if ( ! empty( $_GET['edit'] ) ) {

				$edit_id      = absint( $_GET['edit'] );
				$global_addon = get_post( $edit_id );

				if ( ! $global_addon ) {
					echo '<div class="error">' . esc_html__( 'Error: Global Add-on not found', 'lafka-plugin' ) . '</div>';
					return;
				}

				$reference      = $global_addon->post_title;
				$priority       = get_post_meta( $global_addon->ID, '_priority', true );
				$objects        = (array) wp_get_post_terms( $global_addon->ID, apply_filters( 'Pxl_Product_Addons_global_post_terms', array( 'product_cat' ) ), array( 'fields' => 'ids' ) );
				$product_addons = array_filter( (array) get_post_meta( $global_addon->ID, '_product_addons', true ) );

				if ( get_post_meta( $global_addon->ID, '_all_products', true ) == 1 ) {
					$objects[] = 0;
				}

			} elseif ( ! empty( $edit_id ) ) {

				$global_addon   = get_post( $edit_id );
				$reference      = $global_addon->post_title;
				$priority       = get_post_meta( $global_addon->ID, '_priority', true );
				$objects        = (array) wp_get_post_terms( $global_addon->ID, apply_filters( 'Pxl_Product_Addons_global_post_terms', array( 'product_cat' ) ), array( 'fields' => 'ids' ) );
				$product_addons = array_filter( (array) get_post_meta( $global_addon->ID, '_product_addons', true ) );

				if ( get_post_meta( $global_addon->ID, '_all_products', true ) == 1 ) {
					$objects[] = 0;
				}

			} else {

				$global_addons_count = wp_count_posts( 'pxl_global_addon' );
				$reference           = __( 'Global Add-on Group', 'lafka-plugin' ) . ' #' . ( $global_addons_count->publish + 1 );
				$priority            = 10;
				$objects             = array( 0 );
				$product_addons      = array();

			}

			include( dirname( __FILE__ ) . '/views/admin-global-html-add.php' );
		} else {

			if ( ! empty( $_GET['delete'] ) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'delete_addon' ) ) {
				wp_delete_post( absint( $_GET['delete'] ), true );
				echo '<div class="updated"><p>' . esc_html__( 'Add-on deleted successfully', 'lafka-plugin' ) . '</p></div>';
			}

			include( dirname( __FILE__ ) . '/views/admin-global-html.php' );
		}
	}

	public function save_global_addons() {
		$edit_id        = ! empty( $_POST['edit_id'] ) ? absint( $_POST['edit_id'] ) : '';
		$reference      = wc_clean( $_POST['addon-reference'] );
		$priority       = absint( $_POST['addon-priority'] );
		$objects        = ! empty( $_POST['addon-objects'] ) ? array_map( 'absint', $_POST['addon-objects'] ) : array();
		$product_addons = $this->get_posted_product_addons();

		if ( ! $reference ) {
			$global_addons_count = wp_count_posts( 'pxl_global_addon' );
			$reference           = __( 'Global Add-on Group', 'lafka-plugin' ) . ' #' . ( $global_addons_count->publish + 1 );
		}

		if ( ! $priority && $priority !== 0 ) {
			$priority = 10;
		}

		if ( $edit_id ) {

			$edit_post               = array();
			$edit_post['ID']         = $edit_id;
			$edit_post['post_title'] = $reference;

			wp_update_post( $edit_post );
			wp_set_post_terms( $edit_id, $objects, 'product_cat', false );
			do_action( 'Pxl_Product_Addons_global_edit_addons', $edit_post, $objects );

		} else {

			$edit_id = wp_insert_post( apply_filters( 'Pxl_Product_Addons_global_insert_post_args', array(
				'post_title'    => $reference,
				'post_status'   => 'publish',
				'post_type'		=> 'pxl_global_addon',
				'tax_input'     => array(
					'product_cat' => $objects
				)
			), $reference, $objects ) );

		}

		if ( in_array( 0, $objects ) ) {
			update_post_meta( $edit_id, '_all_products', 1 );
		} else {
			update_post_meta( $edit_id, '_all_products', 0 );
		}

		update_post_meta( $edit_id, '_priority', $priority );
		update_post_meta( $edit_id, '_product_addons', $product_addons );

		return $edit_id;
	}


	protected function get_posted_product_addons() {
		$product_addons = array();

		if ( isset( $_POST[ 'product_addon_name' ] ) ) {
			$addon_name         = $_POST['product_addon_name'];
			$addon_limit         = $_POST['product_addon_limit'];
			$addon_description  = $_POST['product_addon_description'];
			$addon_type         = $_POST['product_addon_type'];
			$addon_position     = $_POST['product_addon_position'];
			$addon_required     = isset( $_POST['product_addon_required'] ) ? $_POST['product_addon_required'] : array();

			$addon_option_label = $_POST['product_addon_option_label'];
			$addon_option_price = $_POST['product_addon_option_price'];

			$addon_option_min   = $_POST['product_addon_option_min'];
			$addon_option_max   = $_POST['product_addon_option_max'];

			$addon_option_default = $_POST['product_addon_option_default'];

			for ( $i = 0; $i < sizeof( $addon_name ); $i++ ) {

				if ( ! isset( $addon_name[ $i ] ) || ( '' == $addon_name[ $i ] ) ) {
					continue;
				}

				$addon_options 	= array();
				$option_label  	= $addon_option_label[ $i ];
				$option_price  	= $addon_option_price[ $i ];
				$option_min		= $addon_option_min[ $i ];
				$option_max		= $addon_option_max[ $i ];
				$option_default  	= $addon_option_default[ $i ];

				for ( $ii = 0; $ii < sizeof( $option_label ); $ii++ ) {
					$label 	= sanitize_text_field( stripslashes( $option_label[ $ii ] ) );
					if ( isset( $option_price[ $ii ] ) ) {
						$price = wc_format_decimal( sanitize_text_field( stripslashes( $option_price[ $ii ] ) ) );
					} else {
					    $price = array();
						foreach ( $option_price as $attribute_name => $attribute_value ) {
                            foreach ($attribute_value as $attribute_slug => $attribute_price){
	                            $price[$attribute_name][ $attribute_slug ] = wc_format_decimal( sanitize_text_field( stripslashes( $attribute_price[ $ii ] ) ) );
                            }
						}
					}

					$min	= sanitize_text_field( stripslashes( $option_min[ $ii ] ) );
					$max	= sanitize_text_field( stripslashes( $option_max[ $ii ] ) );
					$default 	= sanitize_text_field( stripslashes( $option_default[ $ii ] ) );

					$addon_options[] = array(
						'label'   => $label,
						'price'   => $price,
						'min'     => $min,
						'max'     => $max,
						'default' => $default,
					);
				}

				if ( sizeof( $addon_options ) == 0 ) {
					continue; // Needs options.
				}

				$data                = array();
				$data['name']        = sanitize_text_field( stripslashes( $addon_name[ $i ] ) );
				$data['limit']        = sanitize_text_field( stripslashes( $addon_limit[ $i ] ) );
				$data['description'] = wp_kses_post( stripslashes( $addon_description[ $i ] ) );
				$data['type']        = sanitize_text_field( stripslashes( $addon_type[ $i ] ) );
				$data['position']    = absint( $addon_position[ $i ] );
				$data['attribute'] = absint( empty( $addon_variation_attribute[ $i ] ) ? 0 : $addon_variation_attribute[ $i ] );
				$data['options']     = $addon_options;
				$data['required']    = isset( $addon_required[ $i ] ) ? 1 : 0;

				// Add to array.
				$product_addons[] = apply_filters( 'Pxl_Product_Addons_save_data', $data, $i );
			}
		}

		uasort( $product_addons, array( $this, 'addons_cmp' ) );

		return $product_addons;
	}

	public function process_meta_box( $post_id ) {
		// Save addons as serialised array.
		$product_addons                = $this->get_posted_product_addons();

		$product = wc_get_product( $post_id );
		$product->update_meta_data( '_product_addons', $product_addons );
		$product->save();
	}

	public static function get_new_addon_option() {
		$new_addon_option = array(
			'label' => '',
			'price' => '',
			'default' => '',
			'min' => '',
			'max' => ''
		);

		return apply_filters( 'Pxl_Product_Addons_new_addon_option', $new_addon_option );
	}
}