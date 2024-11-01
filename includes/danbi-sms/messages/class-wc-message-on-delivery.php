<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'WC_Message_On_Delivery' ) ) :

class WC_Message_On_Delivery extends WC_Message_Manager {

	/**
	 * Constructor
	 */
	function __construct() {

		$this->id 				= 'on_delivery';
		$this->title 				= __( 'Order Delivery', PL_DELIVERY_LANG );
		$this->description	= __( 'Once the goods are shipped company name and invoice number will be sent to the buyer by SMS.', PL_DELIVERY_LANG );
		$this->enabled			= 'no';

		$this->subject 		= __( 'The items in your order has been shipped.', PL_DELIVERY_LANG );
		$this->content      	= __( '[{blogname}] {item_names} goods have been sent to {delivery_company} {tracking_no}.', PL_DELIVERY_LANG );

		// Triggers for this email
		add_action( 'wooshipping_delivery_order_items_sended', array( $this, 'add_notification_action' ) );
		add_action( 'wooshipping_delivery_order_items_sended_notification', array( $this, 'trigger' ) );

		// Call parent constructor
		parent::__construct();
	}
	
	function add_notification_action( $order, $item_names, $company_id, $tracking_no ) {
		$this->find[]		= '{item_names}';
		$this->replace[]	= '"' . implode( '","', $item_names ) . '"';
		
		$this->find[]		= '{delivery_company}';
		$this->replace[]	= pl_get_delivery_company_name( $company_id );
		
		$this->find[]		= '{tracking_no}';
		$this->replace[]	= $tracking_no;
		
		do_action( 'wooshipping_delivery_order_items_sended_notification', $order );
	}

    /**
     * Initialise Settings Form Fields
     *
     * @access public
     * @return void
     */
    function init_form_fields() {
    	$this->form_admin_noti();
    	$this->form_subject_content();
    }
}

endif;

return new WC_Message_On_Delivery;