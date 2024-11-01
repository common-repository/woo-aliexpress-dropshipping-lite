<?php
$savedData = CedWadGetOption('CedWad_cofiguration_details');
$appKey = is_array($savedData) ? (isset($savedData['appKey']) ? $savedData['appKey'] : "") : "";
$transactionId = is_array($savedData) ? (isset($savedData['transactionId']) ? $savedData['transactionId'] : "") : "";
?>
<div class="CedWad_loader" style="display: none">
	<img src="<?php echo CedWad_URL.'admin/images/loader.gif'; ?>">
</div>
<div class="container">
	<div class="row">
		<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
			<div class="CedWad_plugin_wrapper CedWad_main_wrapper">
				<div class="CedWad_input_field_wrapper CedWad_padding_main">
				<h2><?php _e('How To Get Started','CedWad') ?></h2>
				<div class="cedWad-filter-sec"> <?php _e('STEP 1 :- Create a filter by clicking on add-filter button and set keywords,categories and more Filtering methods like pricing rules.','CedWad') ?></div>
				<p class="cedWad-filter-row"><?php echo '<a href="'. get_admin_url() .'admin.php?page=CedWad-filters&action=addNew">click here</a>' ?><?php _e(' to create a filter','CedWad') ?></p>
				<p><?php _e('STEP 2 :- Create Bunch of the Filtered products','CedWad') ?><p>
				<p><?php _e('STEP 3 :- Blast it individually or in bulk according to your need','CedWad') ?><p>
				</div>
			</div>
		</div>
	</div>
</div>
