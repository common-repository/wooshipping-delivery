<?php
/**
 * 배송송장 : 관리자 메타박스
 * 
 * @class			PL_Delivery_Admin_Meta_Boxes
 * @version		1.0.0
 * @package		WooShipping
 * @category		Delivery
 * @author 		gaegoms (gaegoms@gmail.com)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class PL_Delivery_Admin_Meta_Boxes {
	
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_metabox' ) );
		add_filter( 'wooshipping_delivery_localize_script', array( __CLASS__, 'localize_script' ) );
	}
	
	public function add_metabox() {
		global $post, $wpdb;
		
		add_meta_box(
			'wooshipping-delivery-data',
			__( 'WooShipping - Delivery', PL_DELIVERY_LANG ) . ' <span class="tips" data-tip="' . __( 'Note: Permissions for order items will automatically be granted when the order status changes to processing/completed.', PL_DELIVERY_LANG ) . '">[?]</span>',
			array( __CLASS__, 'output' ),
			'shop_order',
			'side',
			'default'
		);
	}
	
	public static function output() {
		global $post;
	
		$delivery = pl_get_delivery( $post->ID );
	
		if ( ! $delivery->is_available() ) {
				
			$status = array();
			foreach ( $delivery->get_available_order_statuses() as $key ) {
				$status[ $key ] = wc_get_order_status_name( $key );
			}
				
			echo '<p><strong>' . sprintf( __( 'Permissions for order items will automatically be granted when the order status changes to %s.', PL_DELIVERY_LANG ), implode( '/', $status ) ) . '</strong></p>';
			return;
		}
	
		if ( $delivery->get_count() < 1 ) {
			echo '<p><strong>' . __( 'This Order does not have shipping items.', PL_DELIVERY_LANG ) . '</strong></p>';
			return;
		}
			
		$title = $delivery->get_count( 'unshipped' ) > 0 ? sprintf( __( '%d unshipped product remains.', PL_DELIVERY_LANG ), $delivery->get_count( 'unshipped' ) ) : __( 'All items are shipped complete.', PL_DELIVERY_LANG );
	
		include( 'views/html-order-delivery-data.php' );
	}
	
	public static function localize_script( $params ) {
		$messages = array(
				'no_selected_items'				=> __( 'Please select an item to be shipped processing.', PL_DELIVERY_LANG ),
				'no_selected_company'			=> __( 'Please select a courier company.', PL_DELIVERY_LANG ),
				'wrong_invoice_number'			=> __( 'The invoice number is too short. Please try again.', PL_DELIVERY_LANG ),
				
				'no_selected_cancel_items'	=> __( 'Please select the items you want to cancel sending.', PL_DELIVERY_LANG ),
				'confirm_cancel_items'			=> __( 'Are you sure you want to delete the selected shipping information really?', PL_DELIVERY_LANG ),
				'confirm_cancel_all_items'		=> __( 'Are you sure you want to delete all delivery information in the "%s"?', PL_DELIVERY_LANG ),
		);
		return array_merge( $params, $messages );
	}

}

return new PL_Delivery_Admin_Meta_Boxes();