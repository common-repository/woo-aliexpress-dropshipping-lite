<?php 
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
$action = isset( $_GET['action'] ) ? $_GET['action'] : "";
if( $action == 'delete' ){
	$filterId = isset( $_GET['filterId'] ) ? $_GET['filterId'] : "";
	if( $filterId != "" ){
		$deleted = CedWadDeleteFilterFromId( $filterId );
	}
}
$redirect_url = admin_url('admin.php?page=CedWad-filters');
header( "Location: $redirect_url" );
?>