<?php
/**
 * 배송추적 > AJAX
 * 
 * @class			PL_Delivery_AJAX
 * @version		1.0.0
 * @package		WooMember
 * @category		Account
 * @author 		gaegoms (gaegoms@gmail.com)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class PL_Delivery_AJAX {
	
	public static function init() {
		$ajax_events = array(
			'update_delivery_data'		=> true,	
			'delete_delivery_data'		=> true,
		);
	
		foreach ( $ajax_events as $ajax_event => $nopriv ) {
			add_action( 'wp_ajax_wooshipping_delivery_' . $ajax_event, array( __CLASS__, $ajax_event ) );
			
			if ( $nopriv ) {
				add_action( 'wp_ajax_nopriv_wooshipping_delivery_' . $ajax_event, array( __CLASS__, $ajax_event ) );
			}
		}
	}
	
	public static function update_delivery_data() {
		
		check_ajax_referer( 'update-delivery-data', 'security' );
		
		$order_id		= ! empty( $_POST['order_id'] ) ? $_POST['order_id'] : '';
		$item_ids		= ! empty( $_POST['item_ids'] ) ? $_POST['item_ids'] : array();
		$company_id	= ! empty( $_POST['company_id'] ) ? $_POST['company_id'] : '';
		$tracking_no	= ! empty( $_POST['tracking_no'] ) ?  preg_replace( "/[^0-9]*/s", "", $_POST['tracking_no'] ) : '';
		
		if ( ! is_array( $item_ids) ) {
			$item_ids = array( $item_ids );
		}
		
		if ( empty( $order_id ) || sizeof( $item_ids ) < 1 || empty( $company_id ) || empty( $tracking_no) ) {
			
			$response = array(
					'status'		=> false,
					'message'	=> __( '[Error] it does not require input.', PL_DELIVERY_LANG ),
			);
			
		} else {

			$order = wc_get_order( $order_id );
			$order_items = $order->get_items();
			$company_name = pl_get_delivery_company_name( $company_id );
			$new_item_names = $update_item_names = array();
			
			foreach ( $item_ids as $item_id ) {
				
				$exist = wc_get_order_item_meta( $item_id, 'wooshipping_delivery_company_id', true );
				
				if ( empty( $exist ) ) {
					$new_item_names[ $item_id ] =  $order_items[ $item_id ]['name'];
				} else {
					$update_item_names[ $item_id ] = $order_items[ $item_id ]['name'];
				}
				
				wc_update_order_item_meta( $item_id, 'wooshipping_delivery_company_id', $company_id );
				wc_update_order_item_meta( $item_id, 'wooshipping_delivery_tracking_no', $tracking_no );
				wc_update_order_item_meta( $item_id, 'wooshipping_delivery_shipping_date', current_time( 'mysql' ) );
			}
			
			$order->update_status( 'wc-on-delivery' );
			add_post_meta( $order_id, 'wooshipping_delivery_need_receipt_check', 'yes', true );
			
			if ( sizeof( $new_item_names ) > 0 ) {
				$order->add_order_note( sprintf( __( '&quot;%1$s&quot; has been shipped %2$s (<a href="%3$s" target="_blank">%4$s</a>).', PL_DELIVERY_LANG ),
					implode( '&quot;, &quot;', $new_item_names ), $company_name, pl_get_delivery_tracking_url($company_id, $tracking_no), $tracking_no ) );

				do_action( 'wooshipping_delivery_order_items_sended', $order, $new_item_names, $company_id, $tracking_no );
			}
			
			if ( sizeof( $update_item_names ) > 0 ) {
				$order->add_order_note( sprintf( __( 'Delivery of &quot;%1$s&quot; has been changed to %2$s (<a href="%3$s" target="_blank">%4$s</a>).', PL_DELIVERY_LANG ),
					implode( '&quot;, &quot;', $update_item_names ), $company_name, pl_get_delivery_tracking_url($company_id, $tracking_no), $tracking_no ) );
			}
			
			$response = array(
					'status'		=> true,
					'message'	=> sprintf( __( 'Shipment Information of the %s has been updated.', PL_DELIVERY_LANG ), implode( ', ', array_merge( $new_item_names, $update_item_names ) ) ),
			);

		}
		
		header( "Content-Type: application/json" );
		die( json_encode( $response ) );
	}
	
	public static function delete_delivery_data() {
		
		check_ajax_referer( 'delete-delivery-data', 'security' );
		
		$order_id		= ! empty( $_POST['order_id'] ) ? $_POST['order_id'] : '';
		$item_ids		= ! empty( $_POST['item_ids'] ) ? $_POST['item_ids'] : array();
		
		if ( empty( $order_id ) || sizeof( $item_ids ) < 1 ) {
			
			$response = array(
					'status'		=> false,
					'message'	=> __( '[Error] it does not require input.', PL_DELIVERY_LANG ),
			);
			
		} else {
			
			$order = wc_get_order( $order_id );
			$order_items = $order->get_items();
			$item_names = array();
			$receipted = false;
			
			foreach( $item_ids as $item_id ) {
				if ( $order->get_item_meta( $item_id, 'wooshipping_delivery_receipt_date', true ) ) {
					$receipted = true;
				}
			}
			
			if ( $receipted ) {
				
				$response = array(
						'status'		=> false,
						'message'	=> __( '[Error] This product has already been received by the purchaser.', PL_DELIVERY_LANG ),
				);
				
			} else {
				
				foreach ( $item_ids as $item_id ) {
					$item_names[ $item_id ] = $order_items[ $item_id ]['name'];

					wc_delete_order_item_meta( $item_id, 'wooshipping_delivery_company_id' );
					wc_delete_order_item_meta( $item_id, 'wooshipping_delivery_tracking_no' );
					wc_delete_order_item_meta( $item_id, 'wooshipping_delivery_shipping_date' );
				}
				
				$order->add_order_note( sprintf( __( 'Product &quot;%1$s&quot; is the delivery has been canceled.', PL_DELIVERY_LANG ), implode( '&quot;, &quot;', $item_names ) ) );
				
				$shipped_count = 0;
				foreach( $order_items as $item_id => $item ) {
					$company_id	= $order->get_item_meta( $item_id, 'wooshipping_delivery_company_id', true );
					$tracking_no	= $order->get_item_meta( $item_id, 'wooshipping_delivery_tracking_no', true );
					
					if ( ! empty( $company_id ) && ! empty( $tracking_no ) ) {
						$shipped_count++;
					}
				}
				
				if ( $shipped_count == 0 ) {
					$order->update_status( 'wc-processing' );
					delete_post_meta( $order_id, 'wooshipping_delivery_need_receipt_check' );
				}
				
				$response = array(
						'status'		=> true,
						'message'	=> sprintf( __( '%s products have been shipped cancel treatment.', PL_DELIVERY_LANG ), implode( ', ', $item_names ) ),
				);
				
			}
		}
		
		header( "Content-Type: application/json" );
		die( json_encode( $response ) );
		
	}	
}

PL_Delivery_AJAX::init();