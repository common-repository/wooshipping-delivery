<?php
/**
 * 배송추적 > 설정 > 벌크 관리자
 * 
 * @class			PL_Delivery_Admin_Bulk_Manager
 * @version		1.0.0
 * @package		WooShipping
 * @category		Delivery
 * @author 		gaegoms (gaegoms@gmail.com)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WP_List_Table' ) ) require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );

class PL_Delivery_Admin_Bulk_Manager extends WP_List_Table {
	
	private $index;
		
	function __construct() {
		
		$this->index = 0;
		
		parent::__construct( array(
				'singular'		=> 'wooshipping-delivery',
				'plural'		=> 'wooshipping-delivery',
				'ajax'			=> true,
		) );
		
	}
	
	function no_items() {
		echo __( 'No Order Item found.', PL_DELIVERY_LANG );
	}
	
	function get_columns(){
		$columns = array(
				'cb'        				=> '<input type="checkbox" />',
				'order_id' 	    		=> __( 'Order', PL_DELIVERY_LANG ),
				'order_item_name'		=> __( 'Product Name', PL_DELIVERY_LANG ),
				'shipping_address'		=> __( 'Shipping Address', PL_DELIVERY_LANG ),
				'company_id'			=> __( 'Delivery Company', PL_DELIVERY_LANG ),
				'tracking_no'			=> __( 'Tracking Number', PL_DELIVERY_LANG ),
				'shipping_date'			=> __( 'Shipping Date', PL_DELIVERY_LANG ),
				'tracking'					=> __( 'Delivery Tracking', PL_DELIVERY_LANG ),
		);
		return $columns;
	}

	function get_sortable_columns() {
		$sortable_columns = array(
				'order_id'    				=> array( 'order_id', false ),
				'order_item_name'		=> array( 'order_item_name', false ),
				'company_id'			=> array( 'company_id', false ),
				'tracking_no'			=> array( 'tracking_no', false ),
				'shipping_date'			=> array( 'shipping_date', false ),
		);
		return $sortable_columns;
	}
	
	function column_cb( $row ){

		return sprintf(	'<input type="checkbox" name="item_id[]" value="%s" data-order_id="%s" />', $row->order_item_id, $row->order_id );
	}
	
	function column_default( $row, $column_id ) {
			
		switch( $column_id ) {
				
			case 'order_id' :
				$output = sprintf( '<a href="%s">#%s</a>', admin_url( 'post.php?post=' . $row->order_id . '&action=edit' ), $row->order_id );
				break;
	
			case 'order_item_name'	:				
				$output = sprintf( 
					'<a href="%s" class="tips" data-tip="%s">%s</a>',
					admin_url( 'post.php?post=' . $row->product_id . '&action=edit' ),
					sprintf( __( 'Quantity : %dEA', PL_DELIVERY_LANG ), $row->quantity ),
					$row->order_item_name );
				break;
	
			case 'shipping_address' :			
				$order			= wc_get_order( $row->order_id );
				$formatted		= explode( '<br/>', $order->get_formatted_billing_address() );
				$user_name		= $formatted[0];
				$address			= sprintf( '[%s] %s %s', $order->shipping_postcode, $formatted[2], $formatted[3] );
				$user_id			= $order->get_user_id();
	
				if ( empty( $user_id ) ) {
					$output = '<span class="tips" data-tip="' . esc_attr( __( 'Guest User', PL_DELIVERY_LANG ) ) . '">' . $username . '</span> ';
				} else {
					$output = '<a href="' . admin_url( 'user-edit.php?user_id=' . $user_id ) . '" class="tips" data-tip="' . esc_attr( __( 'Registered User', PL_DELIVERY_LANG ) ) . '">' . $user_name . '</a> ';
				}
				
				if ( ! empty( $order->billing_phone ) ) {
					$phone = preg_replace("/(0(?:2|[0-9]{2}))([0-9]+)([0-9]{4}$)/", "\\1-\\2-\\3", str_replace( '-', '', $order->billing_phone ) );
					$output .= '<span class="tips" data-tip="' . esc_attr( $phone ) . '">(' . $phone . ')</span> ';
				}
				$output .= '<span class="tips" data-tip="' . esc_attr( $address ) . '"><i class="fa fa-pencil-square"></i></span> ';
	
				if ( ! empty( $order->customer_message ) ) {
					$output .= '<span class="tips" data-tip="' . esc_attr( $order->get_cucustomer_message ) . '"><i class="fa fa-info-circle"></i></span> ';
				}
				break;
	
			case 'company_id' :	
				$output = '<select name="company_id[' . $row->order_item_id . ']" class="wc-enhanced-select" placeholder="' . __( 'Delivery Company', PL_DELIVERY_LANG ) . '" data-old="' . $row->company_id . '">' .
						'<option value="">&mdash;</option>' .
						pl_dropdown( wooshipping_delivery()->company->get_availables(),  $row->company_id ) .
						'</select>';
	
				break;
					
			case 'tracking_no' :	
				$output = sprintf( '<input type="text" name="tracking_no[%1$s]" value="%2$s" data-old="%2$s" placeholder="%3$s" />', $row->order_item_id, $row->tracking_no,  __( 'Enter Tracking Number&hellips;', PL_DELIVERY_LANG ) ) .
				'<a class="button submit">' . __( 'Delivery', PL_DELIVERY_LANG ) . '</a>';
				break;
					
			case 'shipping_date' :
				if ( ! empty( $row->shipping_date ) ) {
					$short_date = date_i18n( 'm-d H:i', strtotime( $row->shipping_date ) );
					$output = sprintf( '<span class="tips" data-tip="%s">%s</span>', $row->shipping_date, $short_date );
				} else {
					$output = '<span>&mdash;</span>';
				}
				break;
	
			case 'tracking' :	
				if ( ! empty( $row->company_id ) && ! empty( $row->tracking_no ) ) {
					$output = '<a class="button-primary wooshipping-delivery-tracking-trigger" data-href="' . pl_get_delivery_tracking_url( $row->company_id, $row->tracking_no ) . '" target="_blank">' . __( 'Tracking', PL_DELIVERY_LANG ) . '</a>';
				} else {
					$output = '<span>&mdash;</span>';
				}
				break;
		}
	
		return $output;
	}

	function get_bulk_actions() {
		$actions = array(
				''    => __( '------', PL_DELIVERY_LANG )
		);
		return $actions;
	}

	function process_bulk_action() {
		global $wpdb;
		
		echo '<div class="updated"><p>' . __( 'This feature is coming soon.', PL_DELIVERY_LANG ) . '</p></div>';

	}

	function prepare_items() {
		global $wpdb;

		$current_page 	= $this->get_pagenum();
		$per_page			= $this->get_items_per_page( 'pl_dt_per_page', 10 );
		$item_db			= $wpdb->prefix . 'woocommerce_order_items';
		$itemmeta_db		= $wpdb->prefix . 'woocommerce_order_itemmeta';
		$hidden				= array();
		
		$this->_column_headers = array( $this->get_columns(), $hidden, $this->get_sortable_columns() );
		
		$orderby = 'items.order_item_id';
		if ( ! empty( $_REQUEST['orderby'] ) ) {
			
			$orderby = $_REQUEST['orderby'];
			$item_db_columns =  array( 'order_id', 'order_item_name' );
			
			$orderby = ( in_array( $orderby, $item_db_columns ) ? 'items.' : '' ) . $orderby;
		}
		
		$order = ( empty( $_REQUEST['order'] ) || $_REQUEST['order'] == 'desc' ) ? 'DESC' : 'ASC';
		
		$where = ! empty( $_REQUEST['s'] ) ? " AND itemmeta.meta_value LIKE '%%" . esc_attr( $_REQUEST['s'] ) . "%%' " : "";
		
		$query = "
			SELECT %s
			FROM $item_db items
			INNER JOIN $itemmeta_db itemmeta 
				ON items.order_item_id = itemmeta.order_item_id
			LEFT OUTER JOIN {$wpdb->prefix}posts posts
				ON items.order_id = posts.ID
			WHERE
				items.order_item_type='line_item'
			AND
				posts.post_status != 'trash'
			AND
				posts.post_status IN ( '" . implode( "','", PL_Delivery_Object::get_available_order_statuses() ) . "' )
			GROUP BY items.order_item_id %s";

		$max = sizeof( $wpdb->get_col( sprintf( $query, "items.order_item_id", ' ' ) ) );
		
		$this->set_pagination_args( array(
				'total_items' => $max,
				'per_page'    => $per_page,
				'total_pages' => ceil( $max / $per_page )
		) );

		$columns = "items.order_item_id, items.order_id, items.order_item_name,
				MAX(CASE WHEN itemmeta.meta_key='_qty' THEN  itemmeta.meta_value ELSE NULL END) quantity,
				MAX(CASE WHEN itemmeta.meta_key='_product_id' THEN  itemmeta.meta_value ELSE NULL END) product_id,
				MAX(CASE WHEN itemmeta.meta_key='_vaiation_id' THEN  itemmeta.meta_value ELSE NULL END) variation_id,
				MAX(CASE WHEN itemmeta.meta_key='wooshipping_delivery_company_id' THEN  itemmeta.meta_value ELSE NULL END) company_id,
				MAX(CASE WHEN itemmeta.meta_key='wooshipping_delivery_tracking_no' THEN  itemmeta.meta_value ELSE NULL END) tracking_no,
				MAX(CASE WHEN itemmeta.meta_key='wooshipping_delivery_shipping_date' THEN  itemmeta.meta_value ELSE NULL END) shipping_date,
				MAX(CASE WHEN itemmeta.meta_key='wooshipping_delivery_reciept_date' THEN  itemmeta.meta_value ELSE NULL END) receipt_date 
				";
		
		$this->items = $wpdb->get_results( $wpdb->prepare( 
			sprintf( $query, $columns, "ORDER BY {$orderby} {$order}" ) . " LIMIT %d, %d",
			( $current_page - 1 ) * $per_page,
			$per_page
		) );
	}

}

return new PL_Delivery_Admin_Bulk_Manager();