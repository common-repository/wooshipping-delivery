<?php 
/**
 * 배송조회 > 배송사 정보
 * 
 * @class			PL_Delivery_Company
 * @version		1.0.0
 * @package		WooShipping
 * @category		Delivery
 * @author 		gaegoms (gaegoms@gmail.com)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class PL_Delivery_Company {
	
	private $company;
	
	private $available_company;
	
	public function __construct() {
		$this->company = $this->get_all();
	}
	
	private function sort( $company_array ) {
		uasort( $company_array, function($a, $b) {
			return $a['priority'] - $b['priority'];
		});
			return $company_array;
	}
	
	private  function get_default() {
	
		$company = array(
				'doortodoor'	=> array(
						'label'		=> __( 'Korea Express', PL_DELIVERY_LANG ),
						'url'		=> 'https://www.doortodoor.co.kr/parcel/doortodoor.do?fsp_action=PARC_ACT_002&fsp_cmd=retrieveInvNoACT&invc_no=',
				),
				'dongbuexpress' => array(
						'label'		=> __( 'Dongbu Express', PL_DELIVERY_LANG ),
						'url'		=> 'http://www.dongbuexpress.co.kr/Html/Delivery/DeliveryCheckView.jsp?item_no=',
				),
				'hanips' => array(
						'label'		=> __( 'Haneuisarang Express', PL_DELIVERY_LANG ),
						'url'		=> 'http://www.hanips.com/html/sub03_03_1.html?logicnum=',
				),
				'hanjin' => array(
						'label'		=> __( 'Hanjin Express', PL_DELIVERY_LANG ),
						'url'		=> 'http://www.hanjin.co.kr/Delivery_html/inquiry/result_waybill.jsp?wbl_num=',
				),
				'hlc' => array(
						'label'		=> __( 'Hyundai Express', PL_DELIVERY_LANG ),
						'url'		=> 'http://www.hlc.co.kr/hydex/jsp/tracking/trackingViewCus.jsp?InvNo=',
				),
				'kdexp' => array(
						'label'		=> __( 'Kyeongdong Express', PL_DELIVERY_LANG ),
						'url'		=> 'http://www.kdexp.com/rerere.asp?stype=11&p_item=',
				),
				'kglogis'	=> array(
						'label'		=> __( 'KG Logis', PL_DELIVERY_LANG ),
						'url'		=> 'http://www.kglogis.co.kr/delivery/delivery_result.jsp?item_no=',
				),
				'koreanair' => array(
						'label'		=> __( 'Korean Air Express', PL_DELIVERY_LANG ),
						'url'		=> 'http://cargo.koreanair.com/ecus/trc/servlet/TrackingServlet?pid=5&version=kor&menu1=m1&menu2=m01-1&awb_no=',
				),
				'ilogen' => array(
						'label'		=> __( 'Logen Express', PL_DELIVERY_LANG ),
						'url'		=> 'http://www.ilogen.com/iLOGEN.Web.New/TRACE/TraceView.aspx?gubun=slipno&slipno=',
				),
				'epantos' => array(
						'label'		=> __( 'Bumhanpantos Express', PL_DELIVERY_LANG ),
						'url'		=> 'http://www.epantos.com/jsp/gx/tracking/tracking/trackingInquery.jsp?refNo=',
				),
				'epost' => array(
						'label'		=> __( 'Korea ePost Express', PL_DELIVERY_LANG ),
						'url'		=> 'https://service.epost.go.kr/trace.RetrieveDomRigiTraceList.comm?displayHeader=N&sid1=',
				),
				'innogis' => array(
						'label'		=> __( 'Innogis Express', PL_DELIVERY_LANG ),
						'url'		=> 'http://www.innogis.net/trace02.asp?invoice=',
				),
				'ilyanglogis' => array(
						'label'		=> __( 'Ilyanglogis Express', PL_DELIVERY_LANG ),
						'url'		=> 'http://www.ilyanglogis.com/functionality/tracking_result.asp?hawb_no=',
				),
				'cvsnet' => array(
						'label'		=> __( 'CVSnet Convenience Stores Courier', PL_DELIVERY_LANG ),
						'url'		=> 'http://www.cvsnet.co.kr/postbox/m_local/member/status_result.jsp?invoice_no=',
				),
				'epost-ems' => array(
						'label'		=> __( 'Korea ePost EMS', PL_DELIVERY_LANG ),
						'url'		=> 'http://service.epost.go.kr/trace.RetrieveEmsRigiTraceList.comm?POST_CODE=',
				),
				'fedex' => array(
						'label'		=> __( 'Fedex', PL_DELIVERY_LANG ),
						'url'		=> 'http://www.fedex.com/Tracking?ascend_header=1&clienttype=dotcomreg&cntry_code=kr&language=korean&tracknumbers=',
				),
				'kgbls' => array(
						'label'		=> __( 'KGB Express', PL_DELIVERY_LANG ),
						'url'		=> 'http://www.kgbls.co.kr/sub5/trace.asp?f_slipno=',
				),
				'ocskorea' => array(
						'label'		=> __( 'OCS Korea Express', PL_DELIVERY_LANG ),
						'url'		=> 'http://www.ocskorea.com/online_bl_multi.asp?mode=search&search_no=',
				),
				'tnt' => array(
						'label'		=> __( 'TNT Express', PL_DELIVERY_LANG ),
						'url'		=> 'http://www.tnt.com/webtracker/tracking.do?respCountry=kr&respLang=ko&searchType=CON&cons=',
				),
				'ups' => array(
						'label'		=> 'UPS',
						'url'		=> 'http://www.ups.com/WebTracking/track?loc=ko_KR&InquiryNumber1=',
				),
				'dhl' => array(
						'label'		=> 'DHL',
						'url'		=> 'http://www.dhl.com/en/express/tracking.html?&brand=DHL&AWB='
				)
		);
	
		foreach( $company as $key => $val ) {
			$company[ $key ]['actived'] = 'yes';
			$company[ $key ]['priority'] = 10;
		}
	
		return apply_filters( 'wooshipping_delivery_default_companies', $company );
	}
	
	public function get_all() {
		$custom = get_option( 'wooshipping_delivery_companies', array() );
		$companies = array_merge( $this->get_default(), $custom );
		return $this->sort( $companies );
	}
	
	public function get_availables() {
		if ( sizeof( $this->available_company ) < 1 ) {		
			$this->available_company = array();
			foreach ( $this->company as $key => $val ) {
				if ( $val['actived'] == 'yes' ) {
					$this->available_company[ $key ] = $val['label'];
				}
			}
		}
	
		return $this->available_company;
	}
	
	public function get_name( $company_id ) {
		if ( ! key_exists( $company_id, $this->company ) ) {
			return __( 'Unknown', PL_DELIVERY_LANG );
		}
		return $this->company[ $company_id ]['label'];
		
	}
	
	public function get_tracking_url( $company_id, $tracking_no ) {
		if ( ! key_exists( $company_id, $this->company ) ) {
			return false;
		}
		return $this->company[$company_id]['url'] . str_replace( '-', '', $tracking_no );
	}
	
	
	
}

return new PL_Delivery_Company();