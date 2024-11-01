<?php 
/**
 * 배송조회 > 주문상태 추가
 * 
 * @class			PL_Delivery_Order_Status
 * @version		1.0.0
 * @package		WooShipping
 * @category		Delivery
 * @author 		gaegoms (gaegoms@gmail.com)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class PL_Delivery_Order_Status {
	
	public  function __construct() {
		
		add_action( 'init', array( $this, 'register_order_status' ), 5 );
		add_filter( 'wc_order_statuses', array( $this, 'edit_order_statuses' ), 99 );
		
	}
	
	// For Planet8 Order Status
	public function register_order_status() {
		register_post_status( 'wc-on-delivery', array(
				'label'									=> __( 'On Delivery', PL_DELIVERY_LANG ),
				'public'								=> true,
				'exclude_from_search'			=> false,
				'show_in_admin_all_list	'		=> true,
				'show_in_admin_status_list'	=> true,
				'label_count'						=> _n_noop( 'On delivery <span class="count">(%s)</span>', 'On delivery <span class="count">(%s)</span>' )
		) );
	}
	
	public function edit_order_statuses( $statuses ) {
		$new_statuses = array();
	
		foreach ( $statuses as $key => $status ) {
			$new_statuses[ $key ] = $status;
	
			if ( 'wc-processing' === $key ) {
				if ( 'yes' == get_option( 'wooshipping_delivery_wc-processing_label', 'yes' ) ) {
					$new_statuses[$key] = __( 'Preparing Delivery', PL_DELIVERY_LANG );
				}
				$new_statuses['wc-on-delivery']			= __( 'On Delivery', PL_DELIVERY_LANG );
			}
		}
		return $new_statuses;
	}
	
}

return new PL_Delivery_Order_Status();