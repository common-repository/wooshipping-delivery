<?php
/**
 * 
 * 배송송장 > 관리자 스타일
 * 
 * @class			PL_Delivery_Admin_Assets
 * @version		1.0.0
 * @package		WooShipping
 * @category		Delivery
 * @author 		gaegoms (gaegoms@gmail.com)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class PL_Delivery_Admin_Assets {
	
	public $assets_path = null;
	
	public function __construct() {
		$this->set_path();
		 
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
	}
	
	public function set_path() {
		$this->assets_path = str_replace( array( 'http:', 'https:' ), '', wooshipping_delivery()->plugin_url() ) . '/assets/';
	}
	
	public function admin_styles() {
		wp_enqueue_style( 'wooshipping-delivery-admin', $this->assets_path . 'css/wooshipping-delivery-admin.css', array(), PL_DELIVERY_VERSION );
	}
	
	public function admin_scripts() {
		wp_enqueue_script( 'wooshipping-delivery-admin', $this->assets_path . 'js/wooshipping-delivery-admin.js', array( 'jquery' ), PL_DELIVERY_VERSION );
		
		$args = array( 'ajax_url' => admin_url( 'admin-ajax.php' ) );
		wp_localize_script( 'wooshipping-delivery-admin', 'wooshipping_delivery_params', apply_filters( 'wooshipping_delivery_localize_script', $args ) );
	}

}

return new PL_Delivery_Admin_Assets();