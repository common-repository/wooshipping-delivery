/**
 * 관리자 스크립트
 
 * @version		1.0.0
 * @package		WooShipping
 * @category		Delivery
 * @author 		gaegoms (gaegoms@gmail.com)
 */

jQuery( document ).ready( function($) {
	
	var wooshipping_delivery = {

			init: function() {
				$( '.wooshipping-delivery-tracking-trigger' ).on( 'click', this.open_tracking_popup );
				$( '.show_order_items-trigger' ).on( 'click', this.toggle_order_items );
						
				$( '#wooshipping_delivery_tracking_no' ).on( 'keypress', this.prevent_submit );
				$( '#wooshipping_delivery_submit' ).on( 'click', this.unshipped_submit );
				$( '.shipped-items .package-button' ).on( 'click', this.view_package );
				$( '.shipped-items .trigger-cancel' ).on( 'click', this.cancel_delivery );
				$( '.shipped-items .trigger-cancel-all' ).on( 'click', this.cancel_all_delivery );
				
				$( '#wooshipping_delivery_export_period' ).each( this.show_setting_fields ).on( 'change', this.show_setting_fields );
			},
			
			// 배송조회 클릭시 팝업
			open_tracking_popup: function(e) {
				e.preventDefault();
				
				var url = $( this ).data( 'href' );
				var options = 'width=640, height=480, resizable=yes, scrollbars=yes, status=no;';
				window.open( url, 'wooshipping-delivery', options );
			},
			
			// 우커머스 > 주문 > 배송정보 클릭시 구매함 토글
			toggle_order_items: function() {
				$( this ).closest( 'tr' ).find( '.show_order_items' ).trigger( 'click' );
			},

			prevent_submit: function(e) {
				var key;

				if ( window.event ) key = window.event.keyCode;
				else key = e.which;

				if ( key == 13 ) {
					e.preventDefault();
					$( '#wooshipping_delivery_submit' ).trigger( 'click' );
					return false;
				}
			},

			// 주문 > 배송정보 입력
			unshipped_submit: function() {
				var unshipped_items = $( 'input:checkbox[name=\"unshipped_items[]\"]:checked' ),
					company_id = $( '#wooshipping_delivery_company_id' ),
					tracking_no = $( '#wooshipping_delivery_tracking_no' );

				if ( unshipped_items.length < 1 ) {
					alert( wooshipping_delivery_params.no_selected_items );
					return false;
				}

				var unshipped_item_values = [];
				unshipped_items.map( function() {
					unshipped_item_values.push( $( this ).val() );
				} );

				if ( ! company_id.val() ) {
					alert( wooshipping_delivery_params.no_selected_company );
					company_id.trigger( 'chosen:open' );
					return false;
				}
				if ( tracking_no.val().length < 5 ) {
					alert( wooshipping_delivery_params.wrong_invoice_number );
					tracking_no.focus();
					return false;
				}

				var data = {
						action: 'wooshipping_delivery_update_delivery_data',
						security: $( this ).data( 'nonce' ),
						order_id: $( '#post_ID' ).val(),
						item_ids: unshipped_item_values,
						company_id: company_id.val(),
						tracking_no: tracking_no.val(),
				};

				$.post( ajaxurl, data, function( response ) {
						alert( response.message );
						location.reload();
				});
			},
			
			// 주문 > 배송패키지 상세보기
			view_package: function() {
				$( this ).closest( '.item' ).children( '.package' ).slideToggle( 300 );
			},

			// 주문 > 발송 취소
			cancel_delivery: function() {
				var shipped_items = $( 'input:checkbox[name=\"shipped_items[]\"]:checked' );

				if ( shipped_items.length < 1 ) {
					alert( wooshipping_delivery_params.no_selected_cancel_items );
					return false;
				} else if ( ! confirm( wooshipping_delivery_params.confirm_cancel_items ) ) {
					return false;
				}

				var shipped_item_values = [];
				shipped_items.map( function() {
					shipped_item_values.push( $( this ).val() );
				} );

				var data = {
						action: 'wooshipping_delivery_delete_delivery_data',
						security: $( this ).data( 'nonce' ),
						order_id: $( '#post_ID' ).val(),
						item_ids: shipped_item_values,
				};

				$.post( ajaxurl, data, function( response ) {
						alert( response.message );
						location.reload();
				});
			},

			// 주문 발송 일괄 취소
			cancel_all_delivery: function() {
				var package = $( this ).closest( '.item' );
				var package_title = package.find('.package-button .title' ).html()

				if ( ! confirm( wooshipping_delivery_params.confirm_cancel_all_items.replace(/%s/g, package_title ) ) ) {
					return false;
				}

				var shipped_items = package.find( 'input:checkbox[name=\"shipped_items[]\"]' );
				var shipped_item_values = [];
				shipped_items.map( function() {
					shipped_item_values.push( $( this ).val() );
				} );
				
				var data = {
						action: 'wooshipping_delivery_delete_delivery_data',
						security: $( this ).data( 'nonce' ),
						order_id: $( '#post_ID' ).val(),
						item_ids: shipped_item_values,
				};

				$.post( ajaxurl, data, function( response ) {
						alert( response.message );
						location.reload();
				});
			},

			// 설정 > 배송정보 > 내보내기
			show_setting_fields: function() {
				if ( $( this ).val() == 'custom' ) {
					$( '#wooshipping_delivery_export_start_date' ).closest( 'tr' ).show();
					$( '#wooshipping_delivery_export_end_date' ).closest( 'tr' ).show();
				} else {
					$( '#wooshipping_delivery_export_start_date' ).closest( 'tr' ).hide();
					$( '#wooshipping_delivery_export_end_date' ).closest( 'tr' ).hide();
				}
			}

	}.init();
	
} );