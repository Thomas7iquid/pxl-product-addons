<?php
/**
 * Plugin Name: PXL Product Addons
 * Plugin URI: https://7iquid.tech/
 * Description: PXL Product Addons
 * Version: 1.0.0
 * Author: PXL Team
 * Author URI:  https://themeforest.net/user/7iquid/portfolio
 * Update URI:  https://7iquid.tech/
 * Text Domain: pxl-product-addons
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

define('PHB_TEXT_DOMAIN', 'pxl-product-addons');
define('PHB_VERSION', '1.0.1');
define('PHB_NAME', 'PXL Product Addons');
define('PHB_PATH', plugin_dir_path(__FILE__));
define('PHB_URL', plugin_dir_url(__FILE__));
define('PHB_PLUGIN_FILE', __FILE__ );

#[AllowDynamicProperties]
class Pxl_Product_Addons {

    private static $instance = null;

    public function __construct(){
        $this->file = __FILE__;
        $this->plugin_path = plugin_dir_path(__FILE__);
        $this->plugin_url = plugin_dir_url(__FILE__);
        $this->includes();
        add_action( 'init', array( $this, 'init' ), 0 );  
    }

    public static function instance(){
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function includes() {
        require_once( plugin_dir_path( __FILE__ ) . '/inc/class-pxl-product-addons.php' );
    }

    public function init() {
        $this->loaded_text_domain();
        do_action( 'ppa_init' );
    }

    public function loaded_text_domain(){
        load_plugin_textdomain(PHB_TEXT_DOMAIN, false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
}

function pxl_pa(){
    return Pxl_Product_Addons::instance();
}

// Install
pxl_pa();