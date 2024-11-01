<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

$action = isset($_GET['action']) ? $_GET['action'] : false;
$page = isset($_GET['page']) ? $_GET['page'] : false;
if($page == 'CedWad-filters'  ){
	switch ($action) {
		case 'addNew':
		$fileName = CedWad_PATH.'admin/partials/CedWad-admin-filters-addNew.php';
		CedWadIncludeFile($fileName);
		break;
		case 'edit':
		$fileName = CedWad_PATH.'admin/partials/CedWad-admin-filters-addNew.php';
		CedWadIncludeFile($fileName);
		break;
		case 'create_manual_bunch':
		$fileName = CedWad_PATH.'admin/partials/CedWad-admin-product-filter.php';
		CedWadIncludeFile($fileName);
		break;
		case 'delete':
		$fileName = CedWad_PATH.'admin/partials/CedWad-admin-filter-delete.php';
		CedWadIncludeFile($fileName);
		break;
		default:
		$fileName = CedWad_PATH.'admin/partials/CedWad-admin-filters-table.php';
		CedWadIncludeFile($fileName);
		break;
	}
}