<?php 
/**
 * 배송송장 > 확장모듈 > 단비SMS
 * 
 * @class			PL_Delivery_Ext_Danbi_SMS
 * @version		1.0.0
 * @package		WooShipping
 * @category		Delivery
 * @author 		gaegoms (gaegoms@gmail.com)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class PL_Delivery_Ext_Danbi_SMS {
	
	public function __construct() {
		
		add_filter( 'woocommerce_message_classes', array( $this, 'woocommerce_message_classes' ) );

	}
	
	public function woocommerce_message_classes( $settings ) {
		$new_settings = array();
		
		foreach( $settings as $key => $val ) {
			$new_settings[ $key ] = $val;
					
			if ( $key == 'customer-processing-order' ) {
				$new_settings['on-delivery'] = include( 'messages/class-wc-message-on-delivery.php' );
			}
		}
		 
		return $new_settings;
	}
	
}

return new PL_Delivery_Ext_Danbi_SMS();