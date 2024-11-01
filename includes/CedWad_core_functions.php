<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
* This function includes files.
* @CedWadIncludeFile
* @author CedCommerce <plugins@cedcommerce.com>
* @link  https://www.cedcommerce.com/
*/
if(!function_exists('CedWadIncludeFile')){
	function CedWadIncludeFile($fileNmae){
		if(file_exists($fileNmae)){
			if(include_once $fileNmae) return true;
		}
	}
}

/**
* This function fetch data using get_option wp function.
* @CedWadGetOption
* @author CedCommerce <plugins@cedcommerce.com>
* @link  https://www.cedcommerce.com/
*/
if(!function_exists('CedWadGetOption')){
	function CedWadGetOption($key){
		$result = get_option($key);
		$result = isset($result) ? $result : false;
		return $result;
	}
}

/**
* This function fetch data using get_option wp function.
* @CedWadUpdateOption
* @author CedCommerce <plugins@cedcommerce.com>
* @link  https://www.cedcommerce.com/
*/
if(!function_exists('CedWadUpdateOption')){
	function CedWadUpdateOption($key, $value){
		update_option($key,$value);
		return true;
	}
}

/**
* This function prepares data for ajax response.
* processed for "success" and "fail" $status value
* @prapareAjaxResponse
* @author CedCommerce <plugins@cedcommerce.com>
* @link  https://www.cedcommerce.com/
*/
if(!function_exists('prapareAjaxResponse')){
	function prapareAjaxResponse($status="success",$code="200",$data=""){;
		return json_encode(array('status'=>$status,'code'=>$code,'data' =>$data,));
	}
}

/**
* This function create rules for data fetching.
* @saveRulesData
* @author CedCommerce <plugins@cedcommerce.com>
* @link  https://www.cedcommerce.com/
*/
if(!function_exists('saveRulesData')){
	function saveRulesData($rules){;
		$processRules = _formatRules($rules);
		if(insertIntoDb($processRules)) return true;
		return false;
	}
}
/**
*This function delete Filter 
*@CedWadDeleteFilterFromId
*@author CedCommerce <plugins@cedcommerce.com>
* @link  https://www.cedcommerce.com/
*/
if(!function_exists('CedWadDeleteFilterFromId')){
	function  CedWadDeleteFilterFromId($filterId = ""){
		if( $filterId == "" )
			return false;
		global $wpdb;
		$prefix = $wpdb->prefix . CedWad_PREFIX;
		$tableName = $prefix.'filters';
		if( !$wpdb->delete( $tableName, array( 'id' => $filterId ) ) )
		{
			return false;
		}
		$prefix = $wpdb->prefix . CedWad_PREFIX;
		$tableName = $prefix.'bunches';
		if( !$wpdb->delete( $tableName, array( 'filter-id' => $filterId ) ) )
		{
			return false;
		}
		return true;
	}
}

/**
*This function delete Bunch 
*@CedWadDeleteBunchFromId
*@author CedCommerce <plugins@cedcommerce.com>
* @link  https://www.cedcommerce.com/
*/
if(!function_exists('CedWadDeleteBunchFromId')){
	function CedWadDeleteBunchFromId($bunchId = ""){
		if( $bunchId == "" )
			return false;
		global $wpdb;
		$prefix = $wpdb->prefix . CedWad_PREFIX;
		$tableName = $prefix.'bunches';
		if( !$wpdb->delete( $tableName, array( 'id' => $bunchId ) ) )
		{
			return false;
		}
		return true;

	}
}

/**
* This function update rules for data fetching.
* @updateRulesData
* @author CedCommerce <plugins@cedcommerce.com>
* @link  https://www.cedcommerce.com/
*/
if(!function_exists('updateRulesData')){
	function updateRulesData($filterId, $rules){
		$processRules = _formatRules($rules);
		if(updateIntoDb($filterId,$processRules)) return true;
		return false;
	}
}
/**
* This function get details 
* @CedWadGetFilterData
* @author CedCommerce <plugins@cedcommerce.com>
* @link  https://www.cedcommerce.com/
*/
if(!function_exists('CedWadGetFilterData')){
	function CedWadGetFilterData($editFilterId){
		global $wpdb;
		$prefix = $wpdb->prefix . CedWad_PREFIX;
		$tableName = $prefix.'filters';
		$sql = "SELECT * FROM `".$tableName."` where `id`=$editFilterId";
		$queryData = $wpdb->get_results($sql,'ARRAY_A');
		return $filterData = isset($queryData[0]) ? $queryData[0] : false;
	}
}
/**
* This function create bunch for 
* @CedWadCreateBunch
* @author CedCommerce <plugins@cedcommerce.com>
* @link  https://www.cedcommerce.com/
*/
if(!function_exists('CedWadCreateBunch')){
	function CedWadCreateBunch($filteId, $create_manual_bunch = false, $pageNumber = 1){
		$savedData = CedWadGetOption('CedWad_cofiguration_details');		    
		$appKey = is_array($savedData) ? (isset($savedData['appKey']) ? $savedData['appKey'] : "") : '79509';
		$filterData = CedWadGetFilterData($filteId);
		$filterData = json_decode($filterData['filter_data'],true);

		if(is_array($filterData) && !empty($filterData)){
			$keyword = isset($filterData['keyword']) ? $filterData['keyword'] : false;
			$catId = isset($filterData['cat']) ? $filterData['cat'] : false;
			$searchMinPurchase = isset($filterData['searchMinPurchase']) ? $filterData['searchMinPurchase'] : false;
			$searchMaxPurchase = isset($filterData['searchMaxPurchase']) ? $filterData['searchMaxPurchase'] : false;
		}

		$resp = array(
			'timeout' => 45,
			'redirection' => 5,
			'httpversion' => '1.0',
			'blocking' => true,
			'headers' => array(),
			'body' => array(
				'appKey' => $appKey,
				'keyword' => $keyword,
				'filteId' => $filteId,
				'catId' => $catId,
				'action' => 'bunch_it',
				'searchMaxPurchase' => $searchMaxPurchase,
				'searchMinPurchase' => $searchMinPurchase,
				'pageNumber' => $pageNumber),
			'cookies' => array()
			);	
		$remote_url = "http://demo.cedcommerce.com/woocommerce/aliexpress-dropshipping/Ali-express-Api/api_calls.php";
		$response = CedWadSendRequest( $remote_url,$resp );
		$responded = json_decode($response['body'],true);
		createBunch($responded,$filteId);
	}
}

/**
*This function create Bunch 
*@CedWadDeleteFilterFromId
*@author CedCommerce <plugins@cedcommerce.com>
* @link  https://www.cedcommerce.com/
*/

function createBunch($responded,$filteId){			
	$products = isset($responded['result']['products']) ? $responded['result']['products'] : false;
	if($products && is_array($products) && !empty($products)){
		if(CedWadInsertBunch($products,$filteId)){
			echo prapareAjaxResponse();die;
		}
	}
}
/**
* This function is to foramr rules as to be saved.
* @_formatRules
* @author CedCommerce <plugins@cedcommerce.com>
* @link  https://www.cedcommerce.com/
*/
function _formatRules($rules){
	$count = count($rules['minPrice']);
	for($i=0; $i< $count;$i++ ){
		$markups[] = array('min'=> $rules['minPrice'][$i], 'max' => $rules['maxPrice'][$i], 'amount' => $rules['markupPrice'][$i], 'sign' => $rules['signs'][$i]);
	}
	return $_rules = array('name' =>$rules['name'], 'cat'=> $rules['cat'], 'keyword' => $rules['keyword'], 'searchMinPrice' => $rules['searchMinPrice'], 'searchMaxPrice' => $rules['searchMaxPrice'],'searchMinPurchase' => $rules['searchMinPurchase'], 'searchMaxPurchase' => $rules['searchMaxPurchase'], 'priceMarkup' => $markups, 'catname'=>$rules['catname']);
}

/**
* This function is to insert data in to db.
* @insertIntoDb
* @author CedCommerce <plugins@cedcommerce.com>
* @link  https://www.cedcommerce.com/
*/
function insertIntoDb($processRules){
	global $wpdb;
	$prefix = $wpdb->prefix . CedWad_PREFIX;
	$tableName = $prefix.'filters';
	if($wpdb->insert($tableName, array('name'=>$processRules['name'],'filter_data'=>json_encode($processRules))) !== false)  return true;
	return false;
}

/**
* This function is to update data in to db.
* @updateIntoDb
* @author CedCommerce <plugins@cedcommerce.com>
* @link  https://www.cedcommerce.com/
*/
function updateIntoDb($filterId,$processRules){
	global $wpdb;
	$prefix = $wpdb->prefix . CedWad_PREFIX;
	$tableName = $prefix.'filters';
	if($wpdb->update($tableName, array('name'=>$processRules['name'],'filter_data'=>sanitize_text_field(json_encode($processRules))), array('id'=>$filterId)) !== false) return true;
	return false;
}

/**
* This function is to insert products in bunches.
* @CedWadInsertBunch
* @author CedCommerce <plugins@cedcommerce.com>
* @link  https://www.cedcommerce.com/
*/
function CedWadInsertBunch($products, $filterId, $manually_created = "", $blast = false){
	global $wpdb;
	$prefix = $wpdb->prefix . CedWad_PREFIX;
	$tableName = $prefix.'bunches';
	$toReturn=true;
	if(!$wpdb->insert($tableName, array('filter-id'=>$filterId,'manually_created'=>$manually_created,'blast'=>$blast,'products'=> json_encode($products)))){
		$toReturn = false;
	}
	$lastid = $wpdb->insert_id;
	return $lastid;
}

/**
* This function is to update products in bunches.
* @CedWadUpdateBunch
* @author CedCommerce <plugins@cedcommerce.com>
* @link  https://www.cedcommerce.com/
*/
function CedWadUpdateBunch($products, $filterId, $bunchId = "", $blast = false){
	global $wpdb;
	$prefix = $wpdb->prefix . CedWad_PREFIX;
	$tableName = $prefix.'bunches';
	$toReturn=true;
	if( !$wpdb->update( $tableName, array( 'filter-id'=>$filterId,'products'=> json_encode($products) ), array( 'id' => $bunchId ) ) ){
		$toReturn = false;
	}
	return $toReturn;
}

/**
* This function is to remove products from bunch
* @CedWadRemoveProductFromBunch
* @author CedCommerce <plugins@cedcommerce.com>
* @link  https://www.cedcommerce.com/
*/
function CedWadRemoveProductFromBunch($bunchId, $productId, $filterId){
	global $wpdb;
	$prefix = $wpdb->prefix . CedWad_PREFIX;
	$tableName = $prefix.'bunches';
	$toReturn=true;
	$bunches = $wpdb->get_results( 
		" SELECT `products` FROM `$tableName` WHERE `id` = $bunchId ",
		"ARRAY_A"
		);
	$products = isset( $bunches[0]['products'] ) ? json_decode($bunches[0]['products'], true) : array();
	if( !empty( $products ) )
	{
		foreach ($products as $key => $value) {
			if( $value['productId'] == $productId )
				unset( $products[$key] );
		}
		$products = array_values( $products );
		$res = CedWadUpdateBunch( $products, $filterId, $bunchId );
		if( $res )
			return $toReturn;
		else
			return false;
	}
	return false;
}

/**
*This function get manual bunch Id 
*@CedWadDeleteFilterFromId
*@author CedCommerce <plugins@cedcommerce.com>
* @link  https://www.cedcommerce.com/
*/

function CedWadGetManualBunchId( $filterId ){
	global $wpdb;
	$prefix = $wpdb->prefix . CedWad_PREFIX;
	$tableName = $prefix.'bunches';
	$bunches = $wpdb->get_results( 
		" SELECT `id` FROM `$tableName` WHERE `filter-id` = $filterId AND `manually_created` = 'manual' ",
		"ARRAY_A"
		);
	if( is_array( $bunches ) && !empty( $bunches ) )
	{
		if( isset( $bunches[0]['id'] ) )
			return $bunches[0]['id'];
		else
			return false;
	}
	return false;
}

/**
* This function get details 
* @CedWadGetBunchData
* @author CedCommerce <plugins@cedcommerce.com>
* @link  https://www.cedcommerce.com/
*/
if(!function_exists('CedWadGetBunchData')){
	function CedWadGetBunchData($bunch_id){
		global $wpdb;
		$prefix = $wpdb->prefix . CedWad_PREFIX;
		$tableName = $prefix.'bunches';
		$sql = "SELECT * FROM `".$tableName."` where `id`=$bunch_id";
		$queryData = $wpdb->get_results($sql,'ARRAY_A');
		return $filterData = $queryData[0];
	}
}

/**
* This function fetches a product from a bunch 
* @CedWadFetchProductFromBunch
* @author CedCommerce <plugins@cedcommerce.com>
* @link  https://www.cedcommerce.com/
*/
if(!function_exists('CedWadFetchProductFromBunch')){
	function CedWadFetchProductFromBunch($products, $product_id){
		$flag = 0;
		if( is_array( $products ) && !empty( $products ) )
		{
			foreach ($products as $key => $value) {
				if( $value['productId'] == $product_id ){
					$flag = 1;
					return $value;
				}
			}
		}
		if( $flag == 0 )
		{
			return "No Product Found";
		}
	}
}

/**
* This function checks whetjer the product is blasted or not 
* @CedWad_ProductBlastedOrNot
* @author CedCommerce <plugins@cedcommerce.com>
* @link  https://www.cedcommerce.com/
*/
if(!function_exists('CedWad_ProductBlastedOrNot')){
	function CedWad_ProductBlastedOrNot($product_id){
		$productOnStore = get_posts(
			array(
				'numberposts' => -1,
				'post_type'   => 'product',
				'meta_key' => 'CedWad_product_id',
				'meta_value' => $product_id,
				'meta_compare' => '='
				) 
			);
		if( is_array( $productOnStore ) && !empty( $productOnStore ) )
			return "Blasted";
		else
			return "Not Blasted";
	}
}

/**
* This function return product id for the alreasy blasted product 
* @CedWad_GetProductIdForBlastedProduct
* @author CedCommerce <plugins@cedcommerce.com>
* @link  https://www.cedcommerce.com/
*/
if(!function_exists('CedWad_GetProductIdForBlastedProduct')){
	function CedWad_GetProductIdForBlastedProduct($product_id){
		$productOnStore = get_posts(
			array(
				'numberposts' => -1,
				'post_type'   => 'product',
				'meta_key' => 'CedWad_product_id',
				'meta_value' => $product_id,
				'meta_compare' => '='
				) 
			);
		if( is_array( $productOnStore ) && !empty( $productOnStore ) ){
			return $productOnStore[0]->ID;
		}
		
	}
}


/**
*This function update product data
*@CedWadDeleteFilterFromId
*@author CedCommerce <plugins@cedcommerce.com>
* @link  https://www.cedcommerce.com/
*/

if( !function_exists('CedWad_updateProductData') ){
	function CedWad_updateProductData( $bunchId, $productId, $productDetails ){
		$bunch_data = CedWadGetBunchData( $bunchId );
		$bunch_products = json_decode($bunch_data['products'], true);
		$response = false;
		foreach ($bunch_products as $key => $value) {
			if( $value['productId'] == $productId )
			{
				$bunch_products[$key] = $productDetails;
				break;
			}
		}
		$bunch_products = json_encode($bunch_products);
		$response = CedWad_updateBunch( $bunchId, $bunch_products );
		return $response;
	}
}

/**
*This function update bunch 
*@CedWadDeleteFilterFromId
*@author CedCommerce <plugins@cedcommerce.com>
* @link  https://www.cedcommerce.com/
*/

if( !function_exists('CedWad_updateBunch') ){
	function CedWad_updateBunch( $bunchId, $bunch_products ){
		global $wpdb;
		$prefix = $wpdb->prefix . CedWad_PREFIX;
		$tableName = $prefix.'bunches';

		if( $wpdb->update( $tableName, array( 'products' => $bunch_products ), array( 'id' => $bunchId ) ) )
		{
			return true;
		}
		else
		{
			return false;
		}
	}
}

/**
*This function get product data from url 
*@CedWadDeleteFilterFromId
*@author CedCommerce <plugins@cedcommerce.com>
* @link  https://www.cedcommerce.com/
*/

if( !function_exists('CedWad_getProductDataFromUrl') ){
	function CedWad_getProductDataFromUrl( $productUrl ){
		$url = $_SERVER['HTTP_HOST'];
		$resp = array(
			'timeout' => 45,
			'redirection' => 5,
			'httpversion' => '1.0',
			'blocking' => true,
			'headers' => array(),
			'body' => array(
				'action' => 'blast_it',
				'productUrl' => $productUrl,
				'domain' => $url,
				'cookies' => array()
				)
			);	
		$remote_url = "http://demo.cedcommerce.com/woocommerce/aliexpress-dropshipping/Ali-express-Api/api_calls.php";
		$response = CedWadSendRequest( $remote_url,$resp );
		$data = $response['body'];
		$data=json_decode($data,true);
		if( is_array( $data ) && !empty( $data ) )
			return $data;
		else
			return array();
	}
}

/**
*This function check whether woocommerce is istalled 
*@CedWadDeleteFilterFromId
*@author CedCommerce <plugins@cedcommerce.com>
* @link  https://www.cedcommerce.com/
*/


function ced_wad_check_woocommerce_active(){

	if ( function_exists('is_multisite') && is_multisite() ){

		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ){

			return true;
		}
		return false;
	}else{
			
		if ( in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins'))) ){

			return true;
		}
		return false;
	}
}


/**
*This function deactivate plugin if woo is not installed 
*@CedWadDeleteFilterFromId
*@author CedCommerce <plugins@cedcommerce.com>
* @link  https://www.cedcommerce.com/
*/

function deactivate_ced_wad_woo_missing() {

	deactivate_plugins( plugin_basename( __FILE__ ) );
	add_action('admin_notices', 'ced_wad_woo_missing_notice' );
	if ( isset( $_GET['activate'] ) ) {
		unset( $_GET['activate'] );
	}
}


/**
*This function return missing notice 
*@CedWadDeleteFilterFromId
*@author CedCommerce <plugins@cedcommerce.com>
* @link  https://www.cedcommerce.com/
*/

function ced_wad_woo_missing_notice(){

	echo '<div class="error"><p>' . sprintf(__('Woocommerce aliexpress-dropshipping LITE requires WooCommerce to be installed and active. You can download %s here.', 'CedWad'), '<a href="http://www.woothemes.com/woocommerce/" target="_blank">WooCommerce</a>') . '</p></div>';
}

/**
*This function send remote request 
*@CedWadDeleteFilterFromId
*@author CedCommerce <plugins@cedcommerce.com>
* @link  https://www.cedcommerce.com/
*/

if(!function_exists('CedWadSendRequest')){
function CedWadSendRequest($remote_url = "" , $args = ""){
	$response = wp_remote_post($remote_url,$args);
	return $response;
}
}