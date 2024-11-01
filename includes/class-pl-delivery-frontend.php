<?php 
/**
 * 프론트
 * 
 * @class			PL_Delivery_Frontend
 * @version		1.0.0
 * @package		WooShipping
 * @category		Delivery
 * @author 		gaegoms (gaegoms@gmail.com)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class PL_Delivery_Frontend {
		
	public function __construct() {
		add_action( 'woocommerce_my_account_my_orders_column_order-status', array( $this, 'change_order_status' ) );
		
		add_action( 'woocommerce_view_order', array( $this, 'output_delivery_tablebox' ), 5 );
		add_filter( 'woocommerce_order_items_meta_get_formatted', array( $this, 'hide_order_meta' ), 10, 2 );
	}
	
	/**
	 * 마이페이지 > 주문목록 : 주문상태에 배송정보 추가
	 * 
	 * @param unknown $order
	 */
	public function change_order_status( $order ) {
		echo wc_get_order_status_name( $order->get_status() );
		
		if ( $order->get_status() == 'on-delivery' ) { 
			$delivery = pl_get_delivery( $order->id );
			
			if ( $delivery->is_available() ) {
				echo '<div class="wooshipping-delivery-status-' . $delivery->get_status() . '">';
				switch( $delivery->get_status() ) {
					case 1 :
						echo __( '[All Shipped]', PL_DELIVERY_LANG );
						break;
							
					case 0 :
						echo __( '[Only Part]', PL_DELIVERY_LANG );
						break;
							
					case -1 :
						echo __( '[Preparing]', PL_DELIVERY_LANG );
				}
				echo '</div>';
			}
		}
	}
	
	/**
	 * 마이 페이지 > 주문상세 : 배송추적 테이블 추가
	 */ 
	public function output_delivery_tablebox( $order_id ) {
		
		$delivery = pl_get_delivery( $order_id );
		
		if ( ! $delivery->is_available() ) return;
		
		pl_get_delivery_template( 'view-delivery.php', array( 'delivery' => $delivery ) );
 		
	}
	
	/**
	 * 마이페이지 > 주문상세 > 주문상세 테이블의 메타제거
	 * 
	 * @param array $meta
	 * @param object $obj
	 * @return array []
	 */
	public function hide_order_meta( $meta, $obj ) {
		
		$delivery_meta = array( 
				'wooshipping_delivery_company_id',
				'wooshipping_delivery_tracking_no',
				'wooshipping_delivery_shipping_date',
				'wooshipping_delivery_receipt_date'
		);
		$new = array();
		
		foreach ( $meta as $key => $val ) {
			if ( ! in_array( $val[ 'key' ], $delivery_meta ) ) {
				$new[ $key ] = $val;
			}
		}
		return $new;
	}
	
}

return new PL_Delivery_Frontend();