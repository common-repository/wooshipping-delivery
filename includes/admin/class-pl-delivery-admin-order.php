<?php 
/**
 * 배송추적 > 우커머스 > 주문
 * 
 * @class			PL_Delivery_Admin_Order
 * @version		1.0.0
 * @package		WooShipping
 * @category		Delivery
 * @author 		gaegoms (gaegoms@gmail.com)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class PL_Delivery_Admin_Order {
	
	public function __construct() {
		
		// edit-shor_order hooks
		//add_filter( 'views_edit-shop_order', array( $this, 'edit_order_subsubsub' ) );
		add_filter( 'manage_edit-shop_order_columns', array( $this, 'edit_order_column' ) );
		add_action( 'manage_shop_order_posts_custom_column', array( $this, 'edit_order_column_render' ), 10, 2 );
		add_filter( 'woocommerce_order_items_meta_display', array( $this, 'edit_list_order_itemmeta' ), 10, 2 );
		
		// shop_order hooks
		add_action( 'woocommerce_hidden_order_itemmeta', array( $this, 'hidden_order_itemmeta' ) );		
		add_action( 'woocommerce_after_order_itemmeta', array( $this, 'add_wc_order_itemmeta' ), 10, 3 );		
	}
	
	/**************
	 *  주문 목록 페이지 */
	
	public function edit_order_subsubsub( $views ) {
		// 번역때문에 사용하는 임시용입니다.
		if ( array_key_exists( 'wc-pending', $views ) ) {
			preg_match( '/\(.*?\)/', $views[ 'wc-pending' ], $count );
			$views[ 'wc-pending' ] = sprintf( '<a href="edit.php?post_status=wc-pending&post_type=shop_order">%s<span class="count">%s</span></a>', wc_get_order_status_name( 'wc-pending' ), $count[0] );
		}
		
		if ( 'yes' == get_option( 'wooshipping_delivery_wc-processing_label', 'yes' ) && array_key_exists( 'wc-processing', $views ) ) {
			preg_match( '/\(.*?\)/', $views[ 'wc-processing' ], $count );
			$views[ 'wc-processing' ] = sprintf( '<a href="edit.php?post_status=wc-processing&post_type=shop_order">%s<span class="count">%s</span></a>', wc_get_order_status_name( 'wc-processing' ), $count[0] );
		}
		
		return $views;
	}
	
	public function edit_order_column( $column ) {
		$column[ 'wooshipping_delivery' ] = __( 'Delivery', PL_DELIVERY_LANG );
		return $column;
	}
	
	public function edit_order_column_render( $column_id, $order_id ) {
		if ( $column_id != 'wooshipping_delivery' ) return;
		
		$delivery = pl_get_delivery( $order_id );
		
		if ( ! $delivery->is_available() ) {
			printf( '<p class="tips" data-tip="%s" ><span class="dashicons dashicons-nametag"></span></p>', __( 'This order is a state that can not enter shipping information.', PL_DELIVERY_LANG ) );
			return;
		}

		switch( $delivery->get_status() ) {
			case 1 :
				$class	= 'complate';
				$tip		= __( 'All items are shipped complete.', PL_DELIVERY_LANG );
				break;
	
			case 0 :
				$class	= 'progress';
				$tip		= __( 'Shipping information has been entered, only part of it. There has not been shipped items.', PL_DELIVERY_LANG );
				break;
	
			case -1 :
			default :
				$class	= 'prepare';
				$tip		= __( 'Not Exist Delivery Infomations. Administrator must enter the shipping information.', PL_DELIVERY_LANG );
				break;
		}
		
		printf( '<a class="show_order_items-trigger tips %s" data-tip="%s"><span class="dashicons dashicons-nametag"></span></a>', $class, $tip );
		
	}
	
	public function edit_list_order_itemmeta( $output, $order_item_meta ) {
		$output = __( 'On Prepare', PL_DELIVERY_LANG );
		return $output;
	}
	
	
	/**************
	 *  주문 상세 페이지 */
	
	public function hidden_order_itemmeta( $hidden_meta_keys ) {
		$hidden = array(
				'wooshipping_delivery_company_id',
				'wooshipping_delivery_tracking_no',
				'wooshipping_delivery_shipping_date',
				'wooshipping_delivery_receipt_date',
		);
	
		return array_merge( $hidden_meta_keys, $hidden );
	}
	
	public function add_wc_order_itemmeta( $item_id, $item, $product ) {
		global $post;
		
		$delivery = pl_get_delivery( $post->ID );
		
		if ( ! $delivery->is_available() ) {
			return;
		}

		include( 'views/html-order-itemmeta-delivery-info.php' );
	}
}

return new PL_Delivery_Admin_Order();