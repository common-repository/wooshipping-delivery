<?php 
/**
 * 배송조회 > 배송정보
 * 
 * @class			PL_Delivery_Object
 * @version		1.0.0
 * @package		WooShipping
 * @category		Delivery
 * @author 		gaegoms (gaegoms@gmail.com)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class PL_Delivery_Object {
	
	public $prefix;
	
	protected $order;
	
	protected $unshipped_items  = array();
	
	protected $shipped_items = array();
	
	public function __construct( $order_id = '' ) {
		
		global $post;
		
		if ( empty( $order_id ) ) {
			if ( ! empty( $post->ID ) ) {
				$order_id = $post->ID;
			} else {
				return false;
			}
		}
	
		$this->prefix	= 'wooshipping_delivery_';
		$this->order	= wc_get_order( $order_id );
		
		foreach ( $this->order->get_items() as $item_id => $item ) {
			$company_id	= $this->order->get_item_meta( $item_id, $this->prefix . 'company_id', true );
			$tracking_no	= $this->order->get_item_meta( $item_id, $this->prefix . 'tracking_no', true );
		
			if ( empty( $company_id ) || empty( $tracking_no ) ) {
		
				$this->unshipped_items[$item_id] = $item;
		
			} else {
				$item['company_id']		= $company_id;
				$item['tracking_no']		= $tracking_no;
				$item['shipping_date']	= $this->order->get_item_meta( $item_id, $this->prefix . 'shipping_date', true );
				$item['receipt_date']		= $this->order->get_item_meta( $item_id, $this->prefix . 'receipt_date', true );
		
				$this->shipped_items[$item_id] = $item;
			}
			
		}
		
	}
	
	public function get_order() {
		return $this->order;
	}
	
	public function get_items() {
		$r = array();
		
		foreach ( $this->unshipped_items as $key => $val ) {
			$r[ $key ] = $val;
		}
		foreach ( $this->shipped_items as $key => $val ) {
			$r[ $key ] = $val;
		}
		
		return $r;
	}
	
	public function get_unshipped_items() {
		return $this->unshipped_items;
	}
	
	public function get_shipped_items() {
		return $this->shipped_items;
	}
	
	public function get_packages() {
		$packages = array();

		foreach ( $this->get_shipped_items() as $item_id => $item ) {
			$id = sprintf( '%s_____%s', $item['company_id'], $item['tracking_no'] );
			$packages[ $id ][ $item_id ] = $item;
		}
		
		return $packages; 
	}
	
	public function get_package_info( $package_id ) {
		$packages	= $this->get_packages();
		$sample		= reset( $packages[ $package_id ] );
		
		$info						= new stdClass();
		$info->company_id	= $sample['company_id'];
		$info->tracking_no	= $sample['tracking_no'];
		$info->shipping_date	= $sample['shipping_date'];
		$info->receipt_date	= $sample['receipt_date'];		
		
		return $info;
	}
		
	public function is_available() {
		return in_array( $this->order->post_status, $this->get_available_order_statuses() ) ? true : false;
	}
	
	public function is_shipped_item( $item_id ) {
		return array_key_exists( $item_id, $this->shipped_items );
	}
	
	public function is_unshipped_item( $item_id ) {
		return array_key_exists( $item_id, $this->unshipped_items );
	}
	
	public function get_the_item( $item_id ) {
		if ( $this->is_shipped_item( $item_id ) ) {
			return $this->shipped_items[ $item_id ];
		} else if ( $this->is_unshipped_item( $item_id ) ) {
			return $this->unshipped_items[ $item_id ];
		}
		
		return false;
	}
	
	public function get_status() {
		$total			= $this->get_count();
		$shipped		= $this->get_count( 'shipped' );
		$unshipped	= $this->get_count( 'unshipped' );
		
		if ( $total == $unshipped ) {
			return -1;
		} else if ( $total == $shipped ) {
			return 1;
		} else {
			return 0;
		}
	}
	
	public static function get_available_order_statuses() {
		$available	= array();
		$restrict		= array( 'wc-pending', 'wc-on-hold', 'wc-cancelled', 'wc-refunded', 'wc-failed', 'wc-awaiting' );
	
		foreach( wc_get_order_statuses() as $key => $label ) {
			if ( ! in_array( $key, $restrict ) ) {
				$available[] = $key;
			}
		}
	
		return apply_filters( 'wooshipping_delivery_available_order_statuses', $available );
	}
		
	public function get_count( $type = '' ) {
		switch( $type ) {
			case 'shipped' :
				$count = sizeof( $this->shipped_items );
				break;
				
			case 'unshipped' :
				$count = sizeof( $this->unshipped_items );
				break;
				
			default :
				$count = sizeof( array_merge( $this->unshipped_items, $this->shipped_items ) );
				break;
		}
		
		return $count;
	}
	
}