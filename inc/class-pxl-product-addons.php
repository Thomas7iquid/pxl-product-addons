<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Class_PXL_Product_Addons {

	public function __construct() {
		define( 'WC_PRODUCT_ADDONS_VERSION', '1.0.0' );
		add_action( 'plugins_loaded', array( $this, 'init_classes' ) );
		// add_action( 'init', array( $this, 'init_post_types' ), 20 );
		// // Product Combos compatibility
		// add_filter( 'woocommerce_combos_compatibility_modules', array( $this, 'init_pb_compatibility_module' ) );
	}

	public function init_classes() {
		// Core (models)
		// include_once( dirname( __FILE__ ) . '/includes/groups/class-lafka-product-addon-group-validator.php' );
		// include_once( dirname( __FILE__ ) . '/includes/groups/class-lafka-product-addon-global-group.php' );
		// include_once( dirname( __FILE__ ) . '/includes/groups/class-lafka-product-addon-product-group.php' );
		// include_once( dirname( __FILE__ ) . '/includes/groups/class-lafka-product-addon-groups.php' );

		// Admin
		if ( is_admin() ) {
			$this->init_admin();
		}
	}

	protected function init_admin() {
		include_once( dirname( __FILE__ ) . '/admin/pxl-product-addon-admin.php' );
		$GLOBALS['PXL_Product_Addon_Admin'] = new PXL_Product_Addon_Admin();
	}
}

new Class_PXL_Product_Addons();