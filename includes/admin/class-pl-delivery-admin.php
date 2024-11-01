<?php
/**
 * 배송송장 관리자
 * 
 * @class			PL_Delivery_Tracking_Admin
 * @version		1.0.0
 * @package		WooShipping
 * @category		Delivery
 * @author 		gaegoms (gaegoms@gmail.com)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class PL_Delivery_Admin {
	
	public function __construct() {
		add_action( 'init', array( $this, 'includes' ) );
		add_action( 'current_screen', array( $this, 'conditional_includes' ) );
	}
	
	public function includes() {
		include_once( 'class-pl-delivery-admin-meta-boxes.php' );
		
		if ( ! defined( 'DOING_AJAX' ) ) {
			include_once( 'class-pl-delivery-admin-assets.php' );
			include_once( 'class-pl-delivery-admin-menus.php' );
		}
		
	}
	
	public function conditional_includes() {
		$wc_screen_id	= sanitize_title( __( 'WooCommerce', 'woocommerce' ) );
		
		$screen	= get_current_screen();

		switch ( $screen->id ) {
			case 'shop_order' :
			case 'edit-shop_order' :
				include_once( 'class-pl-delivery-admin-order.php' );
				break;
				
			case $wc_screen_id . '_page_wooshipping-delivery' :
				include_once( 'class-pl-delivery-admin-settings.php' );
				break;
		}
	}

}

return new PL_Delivery_Admin();