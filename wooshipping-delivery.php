<?php
/**
 * Plugin Name: WooShipping - Delivery
 * Description: Enter single or multiple tracking numbers in one order, and customers can track their order.
 * Plugin URI: http://www.planet8.co/wooshipping-delivery-pro/
 * Version: 1.0.0
 * Author: Planet8
 * Author URI: http://www.planet8.co
 * Requires at least: 3.8
 * Tested up to: 4.0
 *
 * Text Domain: wooshipping-delivery
 * Domain Path: /languages/
 *
 * @package		WooShipping
 * @category		Delivery
 * @author		gaegoms (gaegoms@gmail.com)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WooShipping_Delivery' ) ) :

final class WooShipping_Delivery {
	
	public $version = '1.0.0';
	
	public $company;
	
	protected static $_instance = null;
	
	public static function instance() {
		
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		
		return self::$_instance;
	}
	
	public function __construct() {
		
		$this->define_constants();
		$this->includes();
		$this->extension_includes();
		
		add_action( 'init', array( $this, 'init' ), 0 );
		
	}
		
	private function define_constants() {
		global $wpdb;
				
		define( 'PL_DELIVERY_FILE', __FILE__ );
		define( 'PL_DELIVERY_LANG', 'wooshipping-delivery' );
		define( 'PL_DELIVERY_TABLE', $wpdb->prefix . 'woocommerce_pl_delivery_tracking' );
		define( 'PL_DELIVERY_TABLE_COMPANY', $wpdb->prefix . 'woocommerce_pl_delivery_tracking_companies' );
		define( 'PL_DELIVERY_VERSION', $this->version );
	}
	
	private function includes() {
		include_once( 'includes/pl-delivery-functions.php' );
		
		include_once( 'includes/class-pl-delivery-install.php' );
		include_once( 'includes/class-pl-delivery-object.php' );
		include_once( 'includes/class-pl-delivery-order-status.php' );
		
		if ( defined( 'DOING_CRON' ) ) {
			include_once( 'includes/class-pl-delivery-cron.php' );
		}
		
		if ( defined( 'DOING_AJAX' ) ) {
			include_once( 'includes/class-pl-delivery-ajax.php' );
		}
		
		if ( is_admin() ) {
			include_once( 'includes/admin/class-pl-delivery-admin.php' );
		}
		
		if ( ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' ) ) {
			//include_once( 'includes/class-pl-delivery-assets.php' );
			include_once( 'includes/class-pl-delivery-frontend.php' );
			include_once( 'includes/class-pl-delivery-form-handler.php' );
		}

	}
	
	public function extension_includes() {
		if ( class_exists( 'DanbiSMS' ) ) {
			include_once( 'includes/danbi-sms/class-pl-delivery-ext-danbi-sms.php' );
		}
	}
	
	public function init() {		
		load_plugin_textdomain( PL_DELIVERY_LANG, false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
		
		$this->company = include_once( 'includes/class-pl-delivery-company.php' );
	}
	
	
	/********************
	 * Helper functions *
	 ********************/
	
	public function plugin_url() {
		return untrailingslashit( plugins_url( '/', __FILE__ ) );
	}
	
	public function plugin_path() {
		return untrailingslashit( plugin_dir_path( __FILE__ ) );
	}
	
	public function template_path() {
		return $this->plugin_path() . '/templates';
	}
	
}

endif;


function wooshipping_delivery() {
	return WooShipping_Delivery::instance();
}

$GLOBALS['wooshipping_delivery'] = wooshipping_delivery();
