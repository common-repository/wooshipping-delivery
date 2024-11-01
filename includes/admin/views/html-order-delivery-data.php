<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<div class="wc-metabox">

	<p><stront><?php echo $title; ?></stront></p>
	
	<div class="unshipped-items">
				
		<?php foreach ( $delivery->get_unshipped_items() as $item_id => $item ) : ?>
			<div id="unshipped-item-<?php echo $item_id; ?>" class="item tips" data-tip="<?php echo '#' . $item['product_id'] . ' ' . esc_attr( $item['name'] ); ?>">
				<input type="checkbox" name="unshipped_items[]" value="<?php echo $item_id; ?>" />
				<a href="<?php echo admin_url( 'post.php?post=' . $item['product_id'] . '&action=edit' ); ?>" target="_blank">
					<?php echo wp_trim_words( $item['name'], 20 ); ?>
				</a>
				<span class="item-quantity align-right"><?php printf( __( '%d EA', PL_DELIVERY_LANG ), $item['qty'] ); ?></span>
			</div>	
		<?php endforeach; ?>
				
	</div>
			
	<div class="delivery-tool <?php echo ( $delivery->get_count( 'unshipped' ) > 0 ? '' : 'p8-hidden' ); ?>">
		<p class="form-row validate-required" id="wooshipping_delivery_company_id_field">
			<label for="wooshipping_delivery_company_id" class=""><?php _e( 'Delivery Company', PL_DELIVERY_LANG ); ?> <abbr class="required" title="<?php _e( 'Required', PL_DELIVERY_LANG ); ?>">*</abbr></label>
			<select name="wooshipping_delivery_company_id" id="wooshipping_delivery_company_id" class="select wc-enhanced-select" data-allow_clear="true" placeholder="<?php _e( 'Select Delivery Company&hellip;', PL_DELIVERY_LANG ); ?>">
				<option value="" ></option><?php echo pl_dropdown( wooshipping_delivery()->company->get_availables(), '' );?>
			</select>
		</p>
		<p class="form-row validate-required" id="wooshipping_delivery_tracking_no_field">
			<label for="wooshipping_delivery_tracking_no" class=""><?php _e( 'Tracking Number', PL_DELIVERY_LANG ); ?> <abbr class="required" title="<?php _e( 'Required', PL_DELIVERY_LANG ); ?>">*</abbr></label>
			<input type="text" class="input-text " name="wooshipping_delivery_tracking_no" id="wooshipping_delivery_tracking_no" placeholder="<?php _e( 'Enter Traking Number&hellip;', PL_DELIVERY_LANG ); ?>"  value=""  />
			<a id="wooshipping_delivery_submit" data-nonce="<?php echo wp_create_nonce( 'update-delivery-data' ); ?>" class="button"><?php _e( 'Shipping', PL_DELIVERY_LANG ); ?></a>
		</p>
	</div>
	
	<div class="shipped-items">
	
		<?php foreach ( $delivery->get_packages() as $id => $package ) : $info = $delivery->get_package_info( $id ); ?>
			<div id="shipped-item-<?php echo $id; ?>" class="item">
				<p class="button package-button tips" data-tip="<?php printf( __( 'The delivery package includes %d product items.', PL_DELIVERY_LANG ), sizeof( $package ) ) ?>">
					<span class="title"><?php echo pl_get_delivery_company_name( $info->company_id ); ?> - <?php echo $info->tracking_no; ?></span>
					<span class="toggle-icon"><span class="dashicons dashicons-arrow-down"></span></span>
				</p>
				
				<div class="package p8-hidden">
					<p class="shipping_date">
						<label><?php _e( 'Shipping : ', PL_DELIVERY_LANG ); ?></label>
						<input type="text" value="<?php echo $info->shipping_date; ?>" readonly="readonly" />
					</p>
					
					<p class="receipt_date">
						<label><?php _e( 'Receipt : ', PL_DELIVERY_LANG );?></label>
						<input type="text" value="<?php echo ! empty( $info->receipt_date ) ? $info->receipt_date : __( 'None yet received.', PL_DELIVERY_LANG ); ?>" readonly="readonly" />
					</p>
					
					<hr />
					
					<ul>
						<?php foreach ( $package as $item_id => $item ) : ?>
		 					<li class="tips" data-tip="<?php printf( '#%s %s - %s<br/>%s', $item['product_id'], esc_attr( $item['name'] ), sprintf( __( '%d EA', PL_DELIVERY_LANG ), $item['qty'] ), $item['shipping_date'] ); ?>">
		 						<input type="checkbox" name="shipped_items[]" value="<?php echo $item_id; ?>" />
								<a href="<?php echo admin_url( 'post.php?post=' . $item['product_id'] . '&action=edit' ); ?>" target="_blank">
									<?php echo wp_trim_words( $item['name'], 20 ); ?>
								</a>
								<span class="item-quantity align-right"><?php printf( __( '%d EA', PL_DELIVERY_LANG ), $item['qty'] ); ?></span>
							</li>
						<?php endforeach; ?>
					</ul>
					
					<hr />

					<p class="align-center">
						<a class="button trigger-cancel" data-nonce="<?php echo wp_create_nonce( 'delete-delivery-data' ); ?>"><?php _e( 'Select Cancel', PL_DELIVERY_LANG ); ?></a>
						<a class="button trigger-cancel-all" data-nonce="<?php echo wp_create_nonce( 'delete-delivery-data' ); ?>"><?php _e( 'Entire Cancel', PL_DELIVERY_LANG ); ?></a>
						<a class="button button-primary wooshipping-delivery-tracking-trigger" data-href="<?php echo pl_get_delivery_tracking_url( $info->company_id, $info->tracking_no ); ?>" target="_blank"><?php _e( 'Tracking', PL_DELIVERY_LANG ); ?></a>
					</p>
				</div>
				
			</div>
		<?php endforeach; ?> 
		
	</div>
	
</div>