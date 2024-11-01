<?php 
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
$action = isset( $_GET['action'] ) ? $_GET['action'] : "";
if( $action == 'delete' ){
	$bunchId = isset( $_GET['bunchId'] ) ? $_GET['bunchId'] : "";
	if( $bunchId != "" ){
		$deleted = CedWadDeleteBunchFromId( $bunchId );
	}
}
$redirect_url = admin_url('admin.php?page=CedWad-bunch');
header( "Location: $redirect_url" );
?>