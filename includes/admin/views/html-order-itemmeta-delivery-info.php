<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<div class="delivery-info">
	<label><i class="fa fa-lg fa-truck"></i> <?php _e( 'Delivery Information : ', PL_DELIVERY_LANG ); ?></label>
	<ul>
	
	<?php if ( $delivery->is_shipped_item( $item_id ) ) : $item = $delivery->get_the_item( $item_id ); ?>
	
		<li><span class="tips" data-tip="<?php _e( 'Delivery Company', PL_DELIVERY_LANG ); ?>"><?php echo pl_get_delivery_company_name( $item['company_id'] ); ?></span></li>
		<li>
			<a class="wooshipping-delivery-tracking-trigger tips" data-href="<?php echo pl_get_delivery_tracking_url( $item['company_id'], $item['tracking_no'] ); ?>" data-tip= "<?php _e( 'Tracking Number', PL_DELIVERY_LANG ); ?>"><?php echo $item['tracking_no']; ?></a>
		</li>
		<li><span class="tips" data-tip="<?php _e( 'Shipping Date', PL_DELIVERY_LANG ); ?>"><?php echo $item['shipping_date']; ?></span></li>
		<li><span class="tips" data-tip="<?php _e( 'Receipt Date', PL_DELIVERY_LANG ); ?>"><?php echo ! empty( $item['receipt_date'] ) ? $item['receipt_date'] : __( 'None yet received.', PL_DELIVERY_LANG ); ?></span></li>
		
	<?php else : ?>
	
		<li><?php _e( 'Not Exist Delivery Infomations.', PL_DELIVERY_LANG ); ?></li>
		
	<?php endif; ?>
	
	</ul>
</div>