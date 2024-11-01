<?php
/**
 * 플러그인 인스톨시 작업
 * 
 * @class			PL_Delivery_Install
 * @version		1.0.0
 * @package		WooShipping
 * @category		Delivery
 * @author 		gaegoms (gaegoms@gmail.com)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class PL_Delivery_Install {

	public static function init() {
		
		register_activation_hook( PL_DELIVERY_FILE, array( __CLASS__, 'install' ) );
		register_deactivation_hook( PL_DELIVERY_FILE, array( __CLASS__, 'deactivate' ) );
		
		add_filter( 'cron_schedules', array( __CLASS__, 'cron_schedules' ) );
	}
	
	public static function cron_schedules( $schedules ) {
		$schedules['minutes_10'] = array(
				'interval'		=> 10,
				'display'		=> __( 'Once 10 minutes', PL_DELIVERY_LANG ),
		);
		
		return $schedules;
	}
	
	// 활성화시 작업
	public static function install() {
		$installed_version = get_option( 'wooshipping_delivery_version' );
		
		self::create_cron_jobs();
		
		if ( ! $installed_version ) {			
			update_option( 'wooshipping_delivery_version', PL_DELIVERY_VERSION );
		}
	}
	
	private static function create_cron_jobs() {
		wp_clear_scheduled_hook( 'wooshipping_delivery_cron' );	
		wp_schedule_event( time(), 'minutes_10', 'wooshipping_delivery_cron' );
	}
	
	// 비활성화시 작업
	public static function deactivate() {
		wp_clear_scheduled_hook( 'wooshipping_delivery_cron' );
		delete_option( 'wooshipping_delivery_version' );
	}
	
}

PL_Delivery_Install::init();
