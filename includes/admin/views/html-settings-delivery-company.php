<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<tr class="company-<?php echo $id; ?>">
	<td class="column-cb"><input type="checkbox" name="actived[<?php echo $id; ?>]" value="yes" <?php checked( 'yes', $company['actived'] ); ?> /></td>
	<td class="column-label"><input type="text" name="label[<?php echo $id; ?>]" value="<?php echo esc_attr( $company['label'] ); ?>" /></td>
	<td class="column-priority">
		<select name="priority[<?php echo $id; ?>]">
		<?php echo pl_dropdown( range(0, 99), $company['priority'] ); ?>
		</select>
	</td>
	<td class="column-url"><input type="text" name="url[<?php echo $id; ?>]" value="<?php echo esc_attr( $company['url'] ); ?>" style="width:100%;" /></td>
</tr>	