<?php
/**
 * 배송송장 > 관리자 > 설정
 * 
 * @class			PL_Delivery_Admin_Settings
 * @version		1.0.0
 * @package		WooShipping
 * @category		Delivery
 * @author 		gaegoms (gaegoms@gmail.com)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class PL_Delivery_Admin_Settings {
	
	public static function init() {
		add_action( 'wooshipping_delivery_bulk_page', array( __CLASS__, 'output_bulk_manager' ) );
		add_action( 'wooshipping_delivery_company_page', array( __CLASS__, 'output_company' ) );		
		add_action( 'wooshipping_delivery_settings_page', array( __CLASS__, 'output_settings' ) );
		//add_action( 'wooshipping_delivery_export_page', array( __CLASS__, 'output_export' ) );
		
		add_action( 'current_screen', array( __CLASS__, 'save_company' ), 20  );
		add_action( 'current_screen', array( __CLASS__, 'save_settings' ), 20  );
		//add_action( 'current_screen', array( __CLASS__, 'export_data' ), 20 );
		//add_action( 'current_screen', array( __CLASS__, 'import_data' ), 20 );
	}
	
	public static function output() {
		$page = ! empty( $_REQUEST['page'] ) ? $_REQUEST['page'] : '';
		$current_tab = ! empty( $_REQUEST['tab'] ) ? $_REQUEST['tab'] : 'bulk';
		$tabs = array(
				'bulk'					=> __( 'Bulk Delivery', PL_DELIVERY_LANG ),
				'company'			=> __( 'Active Courier Company', PL_DELIVERY_LANG ),
				'settings'				=> __( 'General Settings', PL_DELIVERY_LANG ),
				//'import-export'		=> __( 'Import/Export', PL_DELIVERY_LANG ),
		);
		
		?><div class="wrap woocommerce">
			<?php do_action( 'wooshipping_delivery_before_' . $current_tab . '_page' ); ?>
			
			<h2 class="nav-tab-wrapper woo-nav-tab-wrapper">
				<?php foreach( $tabs as $tab => $label ) : ?>
				<a href="<?php echo admin_url( 'admin.php?page=' . $page . '&tab=' . $tab ); ?>" class="nav-tab <?php echo ( $tab == $current_tab ) ? 'nav-tab-active' : ''; ?>"><?php echo $label; ?></a>
				<?php endforeach; ?>
			</h2>
			
			<?php do_action( 'wooshipping_delivery_' . $current_tab . '_page' ); ?>
			
		</div><?php
		
	}
	
	public static function output_bulk_manager() {
		$list = include_once( 'class-pl-delivery-admin-bulk-manager.php' );
		$list->prepare_items();
		
		echo '<div id="wooshipping-delivery-bulk-manager">';
		//$list->search_box('search', 'search_id');
		wp_nonce_field( 'save', 'pl-delivery-tracking' );
		$list->display();
		echo '</div>';
		
		$scripts = "
				var bulk_manager = {
					init: function() {
						$( 'table.wooshipping-delivery' ).on( 'click', 'td.tracking_no .submit', this.submit );
					},
					
					submit: function() {
						var tr	= $( this ).closest( 'tr' );
				
						var item_id			= tr.find( 'th input[type=checkbox]')
						var company_id	= tr.find( '.column-company_id select' );
						var tracking_no	= tr.find( '.column-tracking_no input[type=text]' );
				
						if ( ! company_id.val() ) {
							alert( '" . __( 'Please select a courier company.', PL_DELIVERY_LANG ) . "' );
							company_id.trigger( 'chosen:open' );
							return false;
						}
				
						if ( ! tracking_no.val() ) {
							alert( '" . __( 'Please enter the invoice number.', PL_DELIVERY_LANG ) . "' );
							tracking_no.focus();
							return false;
						}
				
						if ( company_id.val() == company_id.data( 'old' ) && tracking_no.val() == tracking_no.data( 'old' ) ) {
							alert( '" . __( 'No information has changed.', PL_DELIVERY_LANG ) . "' );
							return false;
						}
				
						if ( 
							( company_id.data( 'old' ).toString().length > 0 && tracking_no.data( 'old' ).toString().length > 0 )
							&& ( company_id.val() != company_id.data( 'old' ) || tracking_no.val() != tracking_no.data( 'old' ) )
							&& ! confirm( '" . __( 'Existing information delivery. Would you like to change?', PL_DELIVERY_LANG ) . "' )
						) {
							return false;
						}
									
						var data = {
								action: 'wooshipping_delivery_update_delivery_data',
								security: '" . wp_create_nonce( 'update-delivery-data' ) . "',
								order_id: item_id.data( 'order_id' ),
								item_ids: item_id.val(),
								company_id: company_id.val(),
								tracking_no: tracking_no.val(),
						};
			
						$.post( ajaxurl, data, function( response ) {
								alert( response.message );
								location.reload();
						});
						
					}
				}.init();
				";
		wc_enqueue_js( $scripts );
	}
	
	public static function output_company() {
		$current_tab = $_REQUEST['tab'];
		?>
		<form method="post" id="wooshipping-delivery-<?php echo $current_tab; ?>" action="" enctype="multipart/form-data">
			<?php wp_nonce_field( 'wooshipping-delivery-' . $current_tab ); ?>
			<table class="wc_input_table sortable widefat">
				<thead>
					<th scope="col" id="cb" class="column-cb"><?php _e( 'Active', PL_DELIVERY_LANG ); ?></th>
					<th scope="col" id="name" class="column-name"><?php _e( 'Name', PL_DELIVERY_LANG ); ?></th>
					<th scope="col" id="priority" class="column-priority"><?php _e( 'Priority', PL_DELIVERY_LANG ); ?></th>
					<th scope="col" id="tracking_url" class="column-url"><?php _e( 'Tracking Url', PL_DELIVERY_LANG ); ?></th>
				</thead>
				<tbody>
					<?php
					foreach(  wooshipping_delivery()->company->get_all() as $id => $company ) {
						include 'views/html-settings-delivery-company.php';
					}
					?>
				</tbody>
				<tfoot>
					<th scope="col" id="cb" class="column-cb"><?php _e( 'Active', PL_DELIVERY_LANG ); ?></th>
					<th scope="col" id="name" class="column-name"><?php _e( 'Name', PL_DELIVERY_LANG ); ?></th>
					<th scope="col" id="priority" class="column-priority"><?php _e( 'Priority', PL_DELIVERY_LANG ); ?></th>
					<th scope="col" id="tracking_url" class="column-url"><?php _e( 'Tracking Url', PL_DELIVERY_LANG ); ?></th>
				</tfoot>
			</table>
			<p class="submit">
				<input name="save" class="button-primary" type="submit" value="<?php _e( 'Save Changes', PL_DELIVERY_LANG );?>">
				<input type="hidden" name="page_tab" id="page_tab" value="<?php echo $current_tab; ?>">
			</p>
		</form><?php
	}
	
	public static function save_company( ) {
	
		if ( ! empty( $_REQUEST['save'] ) && ! empty( $_REQUEST['_wpnonce'] ) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'wooshipping-delivery-company' ) ) {
				
			$actived	= ! empty ( $_REQUEST['actived'] ) ? $_REQUEST['actived'] : '';
			$label		= ! empty( $_REQUEST['label'] ) ? $_REQUEST['label'] : '';
			$priority	= ! empty( $_REQUEST['priority'] ) ? $_REQUEST['priority'] : '';
			$url		= ! empty( $_REQUEST['url'] ) ? $_REQUEST['url'] : '';
				
			if ( is_array( $label ) && is_array( $priority ) && is_array( $url ) ) {
	
				$update_data = array();
				foreach ( $label as $id => $label ) {
					$update_data[ $id ] = array(
							'label'		=> $label,
							'url'		=> $url[ $id ],
							'actived'	=> isset( $actived[ $id ] ) ? 'yes' : 'no',
							'priority'	=> ! empty( $priority[ $id ] ) ? $priority[ $id ] : 10,
					);
				}
	
				update_option( 'wooshipping_delivery_companies', $update_data );
				self::success_message( __( 'Your Settings have been saved.', PL_DELIVERY_LANG ) );
			}
		}
	}
	
	public static function output_settings() {
		$current_tab = $_REQUEST['tab'];
		$id = 'wooshipping_delivery_wc-processing_label';
		$value = get_option( $id, 'yes' );
		?>
		<form method="post" id="wooshipping-delivery-<?php echo $current_tab; ?>" action="" enctype="multipart/form-data">
			<?php wp_nonce_field( 'wooshipping-delivery-' . $current_tab ); ?>
			<h3><?php _e( 'General Options', PL_DELIVERY_LANG ); ?></h3>
			<p></p>
			
			<table class="form-table">
				<tbody>
					<tr valign="top">
						<th scope="row" class="titledesc">
							<label for="<?php echo $id; ?>"><?php _e( 'Change wc-processing Label', PL_DELIVERY_LANG ); ?></label>
							<span class="woocommerce-help-tip" data-tip="<?php esc_attr_e( 'If you enable this setting, change the order status to "Processing" to "Preparing Delivery"', PL_DELIVERY_LANG ); ?>"></span>
						</th>
						<td class="forminp">
							<input type="checkbox" id="<?php echo $id; ?>" name="<?php echo $id; ?>" value="1" <?php checked( 'yes', $value ); ?> >
							<?php _e( 'Enable', PL_DELIVERY_LANG ); ?>
						</td>
					</tr>
			</table>
			<p class="submit">
				<input name="save" class="button-primary" type="submit" value="<?php _e( 'Save Changes', PL_DELIVERY_LANG );?>">
				<input type="hidden" name="page_tab" id="page_tab" value="<?php echo $current_tab; ?>">
			</p>
		</form>
		<?php
	}
	
	public static function save_settings() {	
		if ( ! empty( $_REQUEST['save'] ) && ! empty( $_REQUEST['_wpnonce'] ) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'wooshipping-delivery-settings' ) ) {
			$value = isset( $_REQUEST['wooshipping_delivery_wc-processing_label'] ) ? 'yes' : 'no';
			update_option( 'wooshipping_delivery_wc-processing_label', $value );
			self::success_message( __( 'Your Settings have been saved.', PL_DELIVERY_LANG ) );
		}
	}
	
	public static function output_export() {
		/*
				array(
						'title'		=> __( 'Export to CSV', PL_DELIVERY_LANG ),
						'type'		=> 'title',
						'desc'		=> __( 'The export goods order item information in the XML format of Excel documents.', PL_DELIVERY_LANG ),
						'id'			=> $this->prefix . 'export',
				),
				array(
						'id'				=> $this->prefix . 'export_content',
						'type'			=> 'select',
						'title'			=> __( 'Export Content', PL_DELIVERY_LANG ),
						'options'		=> array(
								'all'		=> __( 'Entire Items', PL_DELIVERY_LANG ),
								'shipped'	=> __( 'Shipped Item Only', PL_DELIVERY_LANG ),
								'unshipped'	=> __( 'Unshipped Items Only', PL_DELIVERY_LANG ),
						),
						'default'		=> 'all',
						'class'			=> 'wc-enhanced-select',
				),
				array(
						'id'				=> $this->prefix . 'export_order_statuses',
						'type'			=> 'multiselect',
						'title'			=> __( 'Order Statuses', PL_DELIVERY_LANG ),
						'placeholder'	=> __( 'Select Order Statuses&hellip;', PL_DELIVERY_LANG ),
						'options'		=> wc_get_order_statuses(),
						//'default'		=> array( 'wc-processing' ),
						'class'			=> 'wc-enhanced-select',
						'desc'			=> __( 'To export all orders, regardless of the status of your order please leave blank.', PL_DELIVERY_LANG ),
						'desc_tip'	=> true,
				),
				array(
						'id'				=> $this->prefix . 'export_period',
						'title'			=> __( 'Query Period', PL_DELIVERY_LANG ),
						'type'			=> 'select',
						'options'		=> array(
								'all'		=> __( 'Entire Period', PL_DELIVERY_LANG ),
								'day'		=> __( 'Today', PL_DELIVERY_LANG ),
								'week'	=> __( 'Past 1 Week', PL_DELIVERY_LANG ),
								'month'	=> __( 'Past 1 Month', PL_DELIVERY_LANG ),
								'custom'	=> __( 'Custom', PL_DELIVERY_LANG ),
						),
						'default'		=> 'all',
						'class'			=> 'wc-enhanced-select',
				),
				array(
						'id'			=> $this->prefix . 'export_start_date',
						'title'		=> __( 'Start DateTime', PL_DELIVERY_LANG ),
						'type'		=> 'datetime',
						'default'	=> date( 'Y-m-d 00:00:00', strtotime( '-1 week' ) ),
				),
				array(
						'id'			=> $this->prefix . 'export_end_date',
						'title'		=> __( 'End DateTime', PL_DELIVERY_LANG ),
						'type'		=> 'datetime',
						'default'	=> date_i18n( 'Y-m-d 23:59:59', time() ),
				),
				array( 'type' => 'sectionend', 'id' => $this->prefix . 'export' ),
		*/
		
		/*
		$company_codes = array();		
		foreach( wooshipping_delivery()->company->get_availables() as $key => $val ) {
			$company_codes[] = sprintf( '<span class="tips" data-tip="%s" style="color:#2da0f4">%s</span>', $val, $key );
		}
		
		$args = array( 
				'_wpnonce'		=> wp_create_nonce( $_REQUEST['page'] . '-settings' ),
				'subtab'			=> $_REQUEST['tab'],
				'wooshipping_delivery_export_content'	=> 'all',
				'wooshipping_delivery_export_period'	=> 'all',
				'wooshipping_delivery_export_content'	=> 'unshipped',
		);
		
		$settings = array(
	
				array(
						'title'		=> __( 'Import from CSV', PL_DELIVERY_LANG ),
						'type'		=> 'title',
						'desc'		=> sprintf( __( 'Using a CSV file, you can enter your shipping information ordering information in a batch. To download the entry form, press <a href="%s">here</a>.', PL_DELIVERY_LANG ), add_query_arg( $args ) ),
						'id'			=> $this->prefix . 'import',
				),
				array(
						'id'			=> $this->prefix . 'import_filename',
						'title'		=> __( 'Import CSV File', PL_DELIVERY_LANG ),
						'type'		=> 'file',
						'custom_attributes'	=> array(
								'accept'	=> '.csv',
						),
				),
				array(
						'type'		=> 'guide',
						'title'		=> __( 'Import Guide', PL_DELIVERY_LANG ),
						'guide'	=> array(
								__( 'You can only upload files encoded in UTF-8. Please enter customized possible to download the entry form.', PL_DELIVERY_LANG ),
								__( 'If you need a certain period of time and under certain conditions by entry form, please use the downloaded file in the Export tab.', PL_DELIVERY_LANG ),
								__( 'The CSV file order_id, order_item_id, company_code, a tracking_no fields and field titles are required.', PL_DELIVERY_LANG ),
								sprintf( __( 'You must enter the field company_code company code ( %s ) - Not company name.', PL_DELIVERY_LANG ), implode( ', ', $company_codes ) ),
								__( 'Enter the delivery information in MS Excel, then please upload After saving as comma-separated CSV file.', PL_DELIVERY_LANG ),
						),
				),
				array( 'type' => 'sectionend', 'id' => $this->prefix . 'import' ),
	
		);
		*/
	}
	
	public static function export_data() {
		global $wpdb;
		
		if ( ! isset( $_REQUEST['wooshipping_delivery_export_content'] ) ) { return; }
		
		try {
			$posts_table			= $wpdb->prefix . 'posts';
			$postmeta_table		= $wpdb->prefix . 'postmeta';
			$items_table			= $wpdb->prefix . 'woocommerce_order_items';
			$itemmeta_table		= $wpdb->prefix . 'woocommerce_order_itemmeta';
				
			$content_type = ! empty( $_REQUEST['wooshipping_delivery_export_content'] ) ? $_REQUEST['wooshipping_delivery_export_content'] : 'all';
				
			$where = '';
		
			if ( ! empty( $_REQUEST['wooshipping_delivery_export_order_statuses'] ) ) {
				$where .= sprintf( " AND posts.post_status IN ( '%s' )", implode( "','", $_REQUEST['wooshipping_delivery_export_order_statuses'] ) );
			}
				
			switch( $_REQUEST['wooshipping_delivery_export_period'] ) {
				case 'day' :
					$start_date 	= date_i18n( 'Y-m-d 00:00:00' );
					break;
						
				case 'week' :
					$start_date		= date_i18n( 'Y-m-d 00:00:00', strtotime( '-1 week' ) );
					break;
						
				case 'month' :
					$start_date		= date_i18n( 'Y-m-d 00:00:00', strtotime( '-1 month' ) );
					break;
						
				case 'custom' :
					$start_date		= ! empty( $_REQUEST[ 'wooshipping_delivery_export_start_date'] ) ? $_REQUEST[ 'wooshipping_delivery_export_start_date'] : '';
					$end_date		= ! empty( $_REQUEST[ 'wooshipping_delivery_export_end_date'] ) ? $_REQUEST[ 'wooshipping_delivery_export_end_date'] : '';
					break;
						
				case 'all' :
				default :
			}
				
			if ( ! empty( $start_date ) ) {
				$where .= sprintf( " AND posts.post_date>= '%s'", $start_date );
			}
			if ( ! empty( $end_date ) ) {
				$where .= sprintf( " AND posts.post_date<='%s'", $end_date );
			}
		
			$query ="
			SELECT
			posts.ID,
			items.order_item_id,
			items.order_item_name,
			MAX(CASE WHEN itemmeta.meta_key='_qty' THEN  itemmeta.meta_value ELSE NULL END) quantity,
			MAX(CASE WHEN itemmeta.meta_key='_product_id' THEN  itemmeta.meta_value ELSE NULL END) product_id,
			MAX(CASE WHEN itemmeta.meta_key='_variation_id' THEN  itemmeta.meta_value ELSE NULL END) variation_id,
			MAX(CASE WHEN itemmeta.meta_key='wooshipping_delivery_company_id' THEN  itemmeta.meta_value ELSE NULL END) company_code,
			MAX(CASE WHEN itemmeta.meta_key='wooshipping_delivery_tracking_no' THEN  itemmeta.meta_value ELSE NULL END) tracking_no,
			MAX(CASE WHEN itemmeta.meta_key='_line_total' THEN  itemmeta.meta_value ELSE NULL END) total_price,
			MAX(CASE WHEN postmeta.meta_key='_shipping_first_name' THEN  postmeta.meta_value ELSE NULL END) shipping_name,
			MAX(CASE WHEN postmeta.meta_key='_shipping_postcode' THEN  postmeta.meta_value ELSE NULL END) shipping_postcode,
			MAX(CASE WHEN postmeta.meta_key='_shipping_address_1' THEN  postmeta.meta_value ELSE NULL END) shipping_address_1,
			MAX(CASE WHEN postmeta.meta_key='_shipping_address_2' THEN  postmeta.meta_value ELSE NULL END) shipping_address_2,
			MAX(CASE WHEN postmeta.meta_key='_shipping_phone' THEN  postmeta.meta_value ELSE NULL END) shipping_phone,
			MAX(CASE WHEN postmeta.meta_key='_billing_phone' THEN  REPLACE( postmeta.meta_value, '-', '' ) ELSE NULL END) billing_phone,
			MAX(CASE WHEN postmeta.meta_key='_billing_email' THEN  postmeta.meta_value ELSE NULL END) billing_email
			FROM
			$posts_table AS posts
			LEFT JOIN	$postmeta_table AS postmeta ON posts.ID=postmeta.post_id
			JOIN $items_table AS items ON posts.ID=items.order_id
			JOIN $itemmeta_table AS itemmeta ON items.order_item_id=itemmeta.order_item_id
			WHERE
			posts.post_type='shop_order' {$where}
			GROUP BY posts.ID";
		
		
				
			$results = $wpdb->get_results( $query );
		
			if ( sizeof( $results ) > 0 ) {
					
				$filename = sanitize_text_field( get_bloginfo( 'name' ) ) . '_' . __( 'Delivery', PL_DELIVERY_LANG ) . '_' . date( 'Ymd_His' );
		
				@ob_clean();
				header( 'Content-type: application/vnd.ms-excel' );
				header( 'Content-type: application/vnd.ms-excel; charset=utf-8' );
				header( 'Content-Disposition: attachment; filename = ' . $filename . '.xls' );
				header( 'Expires: 0' );
				header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
				header( 'Cache-Control: private', false);
				header( 'Content-Description:' );
		
				echo '<?xml version="1.0" encoding="UTF-8" ?>' . "\n";
				echo '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet" xmlns:html="http://www.w3.org/TR/REC-html40">' . "\n";
				echo '<Worksheet ss:Name="' . sanitize_text_field( get_bloginfo( 'name' ) ) . __( 'Delivery Information', PL_DELIVERY_LANG ) . '">' . "\n";
				echo '<Table>' . "\n";
		
				$columns = array(
						'order_id',
						'order_item_id',
						'order_item_name',
						'item_options',
						'quantity',
						'total_price',
						'company_code',
						'tracking_no',
						'shipping_name',
						'shipping_postcode',
						'shipping_address_1',
						'shipping_address_2',
						'shipping_phone',
						'billing_email',
				);
		
				echo '<Row>' . "\n";
				foreach ( $columns as $col ) {
					echo sprintf( '<Cell><Data ss:Type="String">%s</Data></Cell>', $col ) . "\n";
				}
				echo '</Row>' . "\n";
		
				foreach( $results as $key => $row ) {
					if ( $content_type == 'shipped' && empty( $row->company_code ) ) { continue; }
					else if ( $content_type == 'unshipped' && ! empty( $row->company_code ) ) { continue; }
		
					echo '<Row>' . "\n";
					foreach( $columns as $col ) {
						switch( $col ) {
							case 'order_id' :
								$val = $row->ID;
								break;
							case 'item_options' :
								$val = ! empty( $row->variation_id ) ? implode( ' | ', self::get_attributes( $row->variation_id ) ) : '';
								break;
							case 'shipping_phone' :
								$val = ! empty( $row->shipping_phone ) ? $row->shipping_phone : $row->billing_phone;
								break;
							default :
								$val = $row->{$col};
						}
						echo sprintf( '<Cell><Data ss:Type="String">%s</Data></Cell>', $val ) . "\n";
					}
					echo '</Row>' . "\n";
		
				}
		
				echo "</Table>\n</Worksheet>\n</Workbook>";
				exit;
					
			} else {
				throw new Exception( __( 'No delivery information found.', PL_DELIVERY_LANG ) );
			}
				
		} catch( Exception $e ) {
			printf( '<script language="javascript">alert("%s");</script>',  $e->getMessage() ) ;
		}
	}
	
	protected static function get_attributes( $variation_id ) {
		global $wpdb;
	
		$results = $wpdb->get_results( $wpdb->prepare( "SELECT REPLACE( meta_key, 'attribute_', '') AS attribute, meta_value AS value  FROM {$wpdb->prefix}postmeta WHERE post_id=%d AND meta_key LIKE 'attribute_%%'", $variation_id ) );
	
		$attr = array();
		foreach ( $results as $val ) {
			$attr[] = urldecode( $val->attribute ) . '(' . $val->value . ')';
		}
	
		return $attr;
	}
	
	public static function import_data() {
		global $wpdb;
		
		if ( ! isset( $_FILES['wooshipping_delivery_import_filename'] ) ) { return; }
		
		if ( ! function_exists( 'wp_handle_upload' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/file.php' );
		}
		
		try {
				
			$items_table			= $wpdb->prefix . 'woocommerce_order_items';
			$itemmeta_table		= $wpdb->prefix . 'woocommerce_order_itemmeta';
				
			$csv = file_get_contents( $_FILES['wooshipping_delivery_import_filename']['tmp_name'] );
			$csv_rows = explode( "\n", $csv );
				
			$column_row = array_shift( $csv_rows );
			$columns = explode( ',', $column_row );
			$companies = wooshipping_delivery()->company->get_availables();
				
				
			if ( ! in_array( 'order_id', $columns ) ) {
				throw new Exception( __( 'Order ID field(order_id) is not defined.', PL_DELIVERY_LANG ) );
			}
			$order_id_key = array_search( 'order_id', $columns );
				
			if ( ! in_array( 'order_item_id', $columns ) ) {
				throw new Exception( __( 'Order item ID field(order_item_id) is not defined.', PL_DELIVERY_LANG ) );
			}
			$order_item_id_key	= array_search( 'order_item_id', $columns );
				
			if ( ! in_array( 'company_code', $columns ) ) {
				throw new Exception( __( 'Courier company field(company_code) is not defined.', PL_DELIVERY_LANG ) );
			}
			$company_code_key	= array_search( 'company_code', $columns );
		
			if ( ! in_array( 'tracking_no', $columns ) ) {
				throw new Exception( __( 'Invoice number field(tracking_no) is not defined.', PL_DELIVERY_LANG ) );
			}
			$tracking_no_key	= array_search( 'tracking_no', $columns );
				
			$values = array();
			foreach( $csv_rows as $cnt => $current_row ) {
				$row = explode( ',', $current_row );
				$item_id = $row[ $order_item_id_key ];
				$company_id = $row[ $company_code_key ];
				$tracking_no = $row[ $tracking_no_key ];
		
				if ( empty( $item_id ) || empty( $company_id ) || empty( $tracking_no ) ) { continue; }
		
				if ( ! array_key_exists( $company_id, $companies ) ) {
					throw new Exception( sprintf( __( '%d second column company code "%s" is a code that does not exist or does not use.', PL_DELIVERY_LANG ), ($cnt + 2), $company_id ) );
				}
		
				$values[ $item_id ] = array(
						'wooshipping_delivery_company_id'	=> $company_id,
						'wooshipping_delivery_tracking_no'	=> $tracking_no,
				);
		
			}
				
			$count = array(
					'total'			=> sizeof( $csv_rows ),
					'source'		=> sizeof( $values ),
					'updated'	=> 0,
			);
				
				
			if ( $count['source'] < 1 ) {
				throw new Exception( __( 'No invoice information has been entered in the import file.', PL_DELIVERY_LANG ) );
			}
				
			$shipping_date = current_time( 'mysql' );
				
			foreach( $values as $item_id => $delivery ) {
				foreach( $delivery as $meta_key => $meta_value ) {
					wc_update_order_item_meta( $item_id, $meta_key, $meta_value );
				}
				wc_update_order_item_meta( $item_id, 'wooshipping_delivery_shipping_date', $shipping_date );
				$count['updated']++;
			}
				
			$message = sprintf( __( '[Success] A total of %d columns, the information is entered %d items, %d of the updated shipping information.', PL_DELIVERY_LANG ), $count['total'], $count['source'], $count['updated'] );
			throw new Exception( $message );
				
		} catch( Exception $e ) {
				
			printf( '<script language="javascript">alert("%s");</script>',  $e->getMessage() ) ;
			//wp_redirect( $_POST['_wp_http_referer'] );
			//exit;
				
		}
	}
	
	private static function success_message( $text = '' ) {
		echo '<div id="message" class="updated"><p><strong>' . $text . '</strong></p></div>';
	}
	
}

PL_Delivery_Admin_Settings::init();