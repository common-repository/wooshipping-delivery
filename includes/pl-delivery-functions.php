<?php 
/**
 * 배송추적 관련함수
 * 
 * @version		1.0.0
 * @package		WooShipping
 * @category		Delivery
 * @author 		gaegoms (gaegoms@gmail.com)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! function_exists( 'pl_print' ) ) {
	function pl_print( $var, $end = true ) {
		echo '<pre>'; print_r( $var ); echo '</pre>';
		if ( $end ) exit;
	}
}

if ( ! function_exists( 'pl_dropdown' ) ) {
	function pl_dropdown( $array, $selected = '' ) {
		if ( ! is_array( $array ) ) return;
	
		$ret = '';
		foreach ( $array as $key => $val ) {
			if ( is_array( $val ) && isset( $val['label'] ) ) {
				$label = sanitize_text_field( $val['label'] );
			} else {
				$label = sanitize_text_field( $val );
			}
			$ret .= '<option value="' . $key . '" ' . selected( $key, $selected, false ) . '>' . $label . '</option>';
		}
	
		return $ret;
	}
}

function pl_get_delivery( $order_id = '' ) {
	return new PL_Delivery_Object( $order_id );
}

// 템플릿 출력
function pl_get_delivery_template( $template_name, $args = array(), $wrapping = true ) {
	$name = 'template-' . preg_replace('/\.php$/', '', basename( $template_name ) );

	if ( function_exists( 'wc_notice_count' ) && wc_notice_count() > 0 ) {
		wc_print_notices();
	}

	do_action( 'wooshipping_delivery_before_' . $name );

	if ( $wrapping ) echo '<div class="' . $name . '">';

	wc_get_template( $template_name, $args, 'planet8/wooshipping-delivery-pro', trailingslashit( wooshipping_delivery()->template_path() ) );

	if ( $wrapping ) echo '</div>';

	do_action( 'wooshipping_delivery_after_' . $name );
}

function pl_get_delivery_available_order_statuses() {
	$available	= array();
	$restrict		= array( 'wc-pending', 'wc-on-hold', 'wc-cancelled', 'wc-refunded', 'wc-failed' );
	
	foreach( wc_get_order_statuses() as $key => $label ) {
		if ( ! in_array( $key, $restrict ) ) {
			$available[] = $key;
		}
	}
	return apply_filters( 'wooshipping_delivery_available_order_statuses', $available );
}

function pl_get_delivery_company_name( $company_id ) {
	return wooshipping_delivery()->company->get_name( $company_id );
}

function pl_get_delivery_tracking_url( $company_id, $tracking_no ) {
	return wooshipping_delivery()->company->get_tracking_url( $company_id, $tracking_no );
}