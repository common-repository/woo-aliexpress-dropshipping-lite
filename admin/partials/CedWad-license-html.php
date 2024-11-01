<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
?>
<div class="CedWad_loader" style="display: none">
  <img src="<?php echo CedWad_URL.'admin/images/loader.gif'; ?>">
</div>
<div class="CedWad_wrap">
	<h2 class="CedWad_setting_header CedWad_bottom_margin"><?php __('Woocommerce AliExpress DropShipping License Configuration','CedWad') ?></h2>
	<div>
		<form method="post">
			<table class="wp-list-table widefat fixed striped CedWad_config_table">
				<tbody>
					<tr>
						<th class="manage-column"><?php _e('Lisence Key','CedWad')?>
							<input type="text" value="" id="CedWad_license_key">
						</th>
						<td>
							<input type="button" value="Validate" id="CedWad_save_license" class="button button-CedWad">
							<input type="button" value="Regenerate" id="CedWad_regenerate_license" class="button button-CedWad">
						</td>
					</tr>
				</tbody>
			</table>
		</form>
	</div>
<div>