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
	<h2 class="CedWad_setting_header CedWad_bottom_margin"><?php __('Enter Your Email Address For Generating Validation Key','CedWad') ?></h2>
	<div>
		<form method="post">
			<table class="wp-list-table widefat fixed striped CedWad_config_table">
				<tbody>
					<tr>
						<th class="manage-column"><?php _e('Email Address','CedWad')?>
							<input type="email" value="" id="CedWad_email">
						</th>
						<td>
							<input type="button" value="Get the key!!" id="email_registration" class="button button-CedWad">
						</td>
					</tr>
				</tbody>
			</table>
		</form>
	</div>
<div>