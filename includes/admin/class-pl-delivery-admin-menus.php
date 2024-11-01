<?php
/**
 * 배송추적 > 관리자 > 메뉴
 * 
 * @class			PL_Delivery_Admin_Menus
 * @version		1.0.0
 * @package		Core
 * @category		Dashboard
 * @author 		gaegoms (gaegoms@gmail.com)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'PL_Delivery_Admin_Menus' ) ) :

class PL_Delivery_Admin_Menus {
	
	public function __construct() {
		add_filter( 'woocommerce_screen_ids',  array( $this, 'add_woocommerce_screen' ) );
		
		add_action( 'admin_menu', array( $this, 'wooshipping_menu' ), 54 );
	}
	
	/**
	 *   관리자 : 우커머스 스크린 추가 - css&js 사용
	 */
	public function add_woocommerce_screen( $screens ) {
		$wc_screen_id	= sanitize_title( __( 'WooCommerce', 'woocommerce' ) );
	
		$screens[] = $wc_screen_id . '_page_wooshipping-delivery';
	
		return $screens;
	}
	
	/**
	 *  관리자 : 우커머스 > 배송설정 메뉴 추가
	 */
	public function wooshipping_menu() {
		$hook = add_submenu_page(
				'woocommerce',
				__( 'Delivery Settings', PL_DELIVERY_LANG ),
				__( 'Delivery Settings', PL_DELIVERY_LANG ),
				'administrator',
				'wooshipping-delivery',
				array( $this, 'output_page')
		);
		add_action( 'load-' . $hook, array( $this, 'init_page' ) );
	}
	
	/**
	 * 관리자 : 화면 초기화
	 */
	public function init_page() {}
	
	/**
	 * 설정 출력
	 */
	public function output_page() {
		PL_Delivery_Admin_Settings::output();
	}
	
}

endif;

return new PL_Delivery_Admin_Menus();