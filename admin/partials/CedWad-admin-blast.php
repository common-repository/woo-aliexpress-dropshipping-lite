<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
$action = isset($_GET['action']) ? $_GET['action'] : false;
$page = isset($_GET['page']) ? $_GET['page'] : false;
if($page == 'CedWad-blast'  ){
	switch ($action) {
		default:
		$fileName = CedWad_PATH.'admin/partials/CedWad-admin-blast-table.php';
		CedWadIncludeFile($fileName);
		break;
	}
}