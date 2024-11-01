<?php 
/**
 * 배송조회 > 크론 작업
 * 
 * @class			PL_Delivery_Cron
 * @version		1.0.0
 * @package		WooShipping
 * @category		Delivery
 * @author 		gaegoms (gaegoms@gmail.com)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

Class PL_Delivery_Cron {
	
	public static function init() {
		add_action( 'wooshipping_delivery_cron', array( __CLASS__, 'receive_confirmation' ) ) ;
	}
	
	public static function receive_confirmation() {
		exit;
		global $wpdb;
		
		$delivery_orders = $wpdb->get_results( "
				SELECT
					items.order_id,
					items.order_item_id AS item_id,
					items.order_item_name AS item_name,
					MAX(CASE WHEN itemmeta.meta_key='wooshipping_delivery_company_id' THEN  itemmeta.meta_value ELSE NULL END) company_id,
					MAX(CASE WHEN itemmeta.meta_key='wooshipping_delivery_tracking_no' THEN  itemmeta.meta_value ELSE NULL END) tracking_no,
					MAX(CASE WHEN itemmeta.meta_key='wooshipping_delivery_shipping_date' THEN  itemmeta.meta_value ELSE NULL END) shipping_date,
					MAX(CASE WHEN itemmeta.meta_key='wooshipping_delivery_receipt_date' THEN  itemmeta.meta_value ELSE NULL END) receipt_date
				FROM
					{$wpdb->prefix}woocommerce_order_items AS items
					JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS itemmeta ON items.order_item_id = itemmeta.order_item_id
				WHERE
					items.order_item_type = 'line_item'
					AND items.order_id IN (SELECT post_id FROM {$wpdb->prefix}postmeta WHERE meta_key='wooshipping_delivery_need_receipt_check' 	AND meta_value='yes' )
				GROUP BY items.order_id
		" );
					
		require_once( 'lib/class-simple-html-parser.php' );
		
		foreach( $delivery_orders as $delivery ) {
			
			$method = str_replace( '-', '_', $delivery->company_id );
			if ( method_exists( __CLASS__, $method ) ) {
				$is_receipt = self::{$method}( $delivery->tracking_no );
				
				if ( $is_receipt ) {
					$order = wc_get_order( $delivery->order_id );					
					wc_update_order_item_meta( $delivery->item_id, 'wooshipping_delivery_receipt_date', current_time( 'mysql' ) );
					$order->add_order_note( sprintf( __( '&quot;%s&quot; is set receive confirmation by cron.', PL_DELIVERY_LANG ), $delivery->item_name ) );
					
					$complate = true;
					foreach( $order->get_items() as $key => $val ) {
						$date = wc_get_order_item_meta( $key, 'wooshipping_delivery_receipt_date', true );
						if ( empty( $date ) ) {
							$complate = false;
						}
					}
					
					if ( $complate ) {
						delete_post_meta( $order->id, 'wooshipping_delivery_need_receipt_check' );
						$order->update_status( 'wc-completed' );
					}
				
				}
				
			}
		}		
		
	}
	
	// 대한통운
	protected static function doortodoor( $code ) {
		$url = pl_get_delivery_tracking_url( 'doortodoor', $code ) ;
	
		if ( ! empty( $url ) ) {
			$response = wp_remote_get( $url );
				
			if ( ! is_wp_error( $response ) ) {
				$dom = new simple_html_dom( $response['body'] );
				$trs = $dom->find( "table.mb15" , 0 )->find( "tr" );
				$last_tr = $trs[ count( $trs ) - 1 ];
				$tds = $last_tr->find("td");
				$now_stage = trim( $tds[ 0 ]->plaintext );
	
				if( trim( $now_stage ) !== '조회된 데이터가 없습니다.'){
					return true;
				}
			} else {
				echo '<!-- Server Failed : ' . $url . ' -->';
			}
				
		}
		return false;
	}
	
	// 한진택배
	protected static function hanjin( $code ) {
		$url = pl_get_delivery_tracking_url( 'hanjin', $code );
		
		if ( ! empty( $url ) ) {
			$response = wp_remote_get( $url );
				
			if ( ! is_wp_error( $response ) ) {
				$dom = new simple_html_dom( $response['body'] );
				$table = $dom->find( "table[summary] > tbody" , 1 );
			
				if ( empty( $table ) ) return false;
				
				$trs = $table->find("tr");
				$last_tr = $trs[ count( $trs ) - 1 ];
				$tds = $last_tr->find("td");
				
				return ( count( $tds ) < 3 );				
				
			} else {
				echo '<!-- Server Failed : ' . $url . ' -->';
			}
		}
		return false;
	}
	
	// 현대택배 : 작동 안됨
	protected static function hlc( $code ) {
		$url = 'http://global.e-hlc.com/servlet/Tracking_View_DLV_ALL';
		
		if ( ! empty( $url ) ) {
			$response = wp_remote_post( $url, array(
					'headers'		=> array(
							'Referer'	=> 'https://global.e-hlc.com/htdocs/HOM/BTOC/hdcm_tracing.jsp',
					),
					'body'		=> array( 'DvlInvNo' => $code ),
			) );

			if ( ! is_wp_error( $response ) ) {
				$dom = new simple_html_dom( $response['body'] );
				$table = $dom->find( "table.table_02", 1 );
				
				$trs		= $table->find( "tr" );
				$last_tr	= $trs[ count( $trs ) - 1 ];
				$tds		= $last_tr->find( "td" );
				
				$tmp_now = $tds[2];
				if ( trim( $tmp_now->plaintext ) === "고객" ) {
					$last_tr	= $trs[ count( $trs ) - 2 ];
					$tds		= $last_tr->find( "td" );
				}
				
				$now_stage = trim( $tds[3]->plaintext );
				return ( strpos( $now_stage, "완료" ) !== false );
	
			} else {
				echo '<!-- Server Failed : ' . $url . ' -->';
			}
		}
		return false;
	}
	
	// 우체국택배
	protected static function epost( $code ) {
		$url = pl_get_delivery_tracking_url( 'epost', $code ) ;
		if ( ! empty( $url ) ) {
			$response = wp_remote_get( $url );
			if ( ! is_wp_error( $response ) ) {
				$dom = new simple_html_dom( $response['body'] );
				$table = $dom->find( "table.detail_off > tbody" , 0 );
				$trs = $table->find("tr");
				if ( count( $trs ) > 1 ) {
					$last_tr = $trs[ count( $trs ) - 1 ];
					$tds = $last_tr->find("td");
				
					return ( strpos( $tds[3]->plaintext , "완료" ) !== false );
				}
			} else {
				echo '<!-- Server Failed : ' . $url . ' -->';
			}
		}
		return false;
	}
	
	// 편의점택배
	protected static function cvsnet( $code ) {
		return self::doortodoor($code);
	}
	
	// 로젠택배
	protected static function ilogen( $code ) {
		$url = pl_get_delivery_tracking_url( 'ilogen', $code ) ;
	
		if ( ! empty( $url ) ) {
			$response = wp_remote_get( $url );
	
			if ( ! is_wp_error( $response ) ) {
				$dom = new simple_html_dom( $response['body'] );
				$table = $dom->find( "#form1 > table > tbody > tr > td > table" , 1 );
				$trs = $table->find("tr");
				if( count( $trs ) > 1 ){
					$last_tr = $trs[ count( $trs ) - 2 ];
					$tds = $last_tr->find("td");
					return ( isset( $tds[2] ) && strpos( $tds[2]->plaintext , "완료" ) !== false );
				}
	
			} else {
				echo '<!-- Server Failed : ' . $url . ' -->';
			}
		}
		return false;
	}
	
	// KGB택배
	protected static function kgbls( $code ) {
		$url = pl_get_delivery_tracking_url( 'kgbls', $code ) ;
	
		if ( ! empty( $url ) ) {
			$response = wp_remote_get( $url );
	
			if ( ! is_wp_error( $response ) ) {
				$dom = new simple_html_dom( $response['body'] );
				$table = $dom->find( "#cont table");
				$trs = $table[count($table) - 2]->find("tr");
				if ( count( $trs ) > 3 ) {
					$tr = $trs[ count( $trs ) - 4 ];
					$td = $tr->find( "td" )[2];
					return ( strpos( $td->plaintext , "완료" ) !== false );
				}
			} else {
				echo '<!-- Server Failed : ' . $url . ' -->';
			}
		}
		return false;
	}
	
	// 경동택배
	protected static function kdexp( $code ) {
		$url = pl_get_delivery_tracking_url( 'kdexp', $code ) ;
	
		if ( ! empty( $url ) ) {
			$response = wp_remote_get( $url );
	
			if ( ! is_wp_error( $response ) ) {
				$dom = new simple_html_dom( $response['body'] );
				$table = $dom->find("#printme table" , 0 );
				return ( strpos( iconv("euc-kr","utf-8",$table->plaintext) , "인수자정보" ) !== false );
			} else {
				echo '<!-- Server Failed : ' . $url . ' -->';
			}
		}
		return false;
	}
	
	// 일양로지스
	protected static function ilyanglogis( $code ) {
		$url = pl_get_delivery_tracking_url( 'ilyanglogis', $code ) ;
	
		if ( ! empty( $url ) ) {
			$response = wp_remote_get( $url );
	
			if ( ! is_wp_error( $response ) ) {
				$dom = new simple_html_dom( $response['body'] );
				$table = $dom->find("#popContainer table",0);
				$tr = $table->find("tr",-1);
				$td = $tr->find("td",2);
				return (strpos( $td , "완료" ) !== false);
			} else {
				echo '<!-- Server Failed : ' . $url . ' -->';
			}
		}
		return false;
	}
	
	// 우체국EMS
	protected static function epost_ems( $code ) {
		$url = pl_get_delivery_tracking_url( 'epost-ems', $code ) ;
	
		if ( ! empty( $url ) ) {
			$response = wp_remote_get( $url );

			if ( ! is_wp_error( $response ) ) {
				$dom = new simple_html_dom( $response['body'] );
				$table = $dom->find("#print table" , 0);
				$tr = $table->find("tr",1);
				$td = $tr->find("td",3);
				return (strpos( $td , "완료" ) !== false);
			} else {
				echo '<!-- Server Failed : ' . $url . ' -->';
			}
		}
		return false;
	}
	
	// FEDEX
	protected static function fedex( $code ) {
		$url = 'https://www.fedex.com/trackingCal/track';
	
		if ( ! empty( $url ) ) {
			$response = wp_remote_post( $url, array( 'body' => array(
					'action'	=> 'trackpackages',
					'locale'	=> 'ko_KR',
					'format'	=> 'json',
					'data'		=> '{"TrackPackagesRequest":{"appType":"WTRK","uniqueKey":"","processingParameters":{},"trackingInfoList":[{"trackNumberInfo":{"trackingNumber":"' . $code . '","trackingQualifier":"","trackingCarrier":""}}]}}',
			) ) );
	
			if ( ! is_wp_error( $response ) ) {
				$arr = json_decode( $response['body'] );
				return (strpos( $arr->TrackPackagesResponse->packageList[0]->keyStatus , "완료" ) !== false);
			} else {
				echo '<!-- Server Failed : ' . $url . ' -->';
			}
		}
		return false;
	}
	
	// KG로지스
	protected static function kglogis( $code ) {
		$url = pl_get_delivery_tracking_url( 'kglogis', $code ) ;
	
		if ( ! empty( $url ) ) {
			$response = wp_remote_get( $url );
			
			if ( ! is_wp_error( $response ) ) {
				$dom = new simple_html_dom( $response['body'] );
				$now = $dom->find( "li.actived dd", 0 );
				return ( ! empty( $now ) && strpos( $now->plaintext , "완료" ) !== false);
			} else {
				echo '<!-- Server Failed : ' . $url . ' -->';
			}
		}
		return false;
	}
	
	// 기본
	protected static function sample( $code ) {
		$url = pl_get_delivery_tracking_url( 'hanjin', $code ) ;
	
		if ( ! empty( $url ) ) {
			$response = wp_remote_get( $url );
	
			if ( ! is_wp_error( $response ) ) {
				$dom = new simple_html_dom( $response['body'] );
	
			} else {
				echo '<!-- Server Failed : ' . $url . ' -->';
			}
		}
		return false;
	}
	
	
	
}

PL_Delivery_Cron::init();