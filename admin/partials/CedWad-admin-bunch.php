<?php
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
$action = isset($_GET['action']) ? $_GET['action'] : false;
$page = isset($_GET['page']) ? $_GET['page'] : false;
if($page == 'CedWad-bunch'  ){
	switch ($action) {
		case 'delete':
		$fileName = CedWad_PATH.'admin/partials/CedWad-admin-bunches-delete.php';
		CedWadIncludeFile($fileName);
		break;
		default:
		$fileName = CedWad_PATH.'admin/partials/CedWad-admin-bunches-table.php';
		CedWadIncludeFile($fileName);
		break;
	}
}