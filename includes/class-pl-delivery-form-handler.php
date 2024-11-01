<?php
/**
 * 배송추적 폼 핸들러
 * 
 * @class			PL_Delivery_Form_Handler
 * @version		1.0.0
 * @package		WooShipping
 * @category		Delivery
 * @author 		gaegoms (gaegoms@gmail.com)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class PL_Delivery_Form_Handler {
	
	public static function init() {	
		add_action( 'wp', array( __CLASS__, 'process_receive_confirmation' ) );
	}
	
	// 관심상품 등록
	public static function process_receive_confirmation() {
		
		if ( ! empty( $_REQUEST['set-receive-confirmation'] ) ) {
			global $wp, $wpdb;
			
			try {
				$order_id	= get_query_var( 'view-order' );
				$delivery		= pl_get_delivery( $order_id );
				$order		= $delivery->get_order();
				
				$item_id		= $_REQUEST['set-receive-confirmation'];
				
				if ( ! is_numeric( $item_id ) ) {
					throw new Exception( __( 'The delivery tracking ID is not valid.', PL_DELIVERY_LANG ) );
				}
				
				if ( $order->user_id != get_current_user_id() ) {
					throw new Exception( __( 'This order is not your order.', PL_DELIVERY_LANG ) );
				}
				
				if ( $delivery->is_unshipped_item( $item_id ) ) {
					throw new Exception( __( 'This delivery is not shipped yet. Can not send recieve confirmation.', PL_DELIVERY_LANG ) );
				}
				
				$items = $order->get_items();
				$item_name = $items[ $item_id ]['name'];
				
				wc_update_order_item_meta( $item_id, 'wooshipping_delivery_receipt_date', current_time( 'mysql' ) );
				
				if ( ! empty( $item_name ) ) {
					$message = sprintf( __( '&quot;%s&quot; is set receive confirmation by customer.', PL_DELIVERY_LANG ), $item_name );
					$order->add_order_note( $message );
					wc_add_notice( $message, 'success' );
				}
				
				$complate = true;
				foreach( $items as $key => $val ) {
					$date = wc_get_order_item_meta( $key, 'wooshipping_delivery_receipt_date', true );
					if ( empty( $date ) ) {
						$complate = false;
						continue;
					}
				}
				
				if ( $complate ) {
					delete_post_meta( $order_id, 'wooshipping_delivery_need_receipt_check' );
					$order->update_status( 'wc-completed' );
				}
				
			} catch ( Exception $e ) {	
				wc_add_notice( apply_filters( 'wooshipping_delivery_set_receive_confirmation_errors', $e->getMessage() ), 'error' );	
			}
			
			wp_redirect( site_url( $wp->request ) );
			exit;
		}
	}
			
}

PL_Delivery_Form_Handler::init();
