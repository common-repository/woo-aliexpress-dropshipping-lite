<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       cedcommerce.com
 * @since      1.0.0
 *
 * @package    woocommerce-aliexpress-dropshipping
 * @subpackage CedWad/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    woocommerce-aliexpress-dropshipping
 * @subpackage CedWad/admin
 * @author     CedCommerce <plugins@cedcommerce.com>
 */
class CedWad_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$page = isset($_GET['page']) ? $_GET['page'] : "";
		if($page == 'CedWad-config' || $page == 'CedWad-bunch' || $page == 'CedWad-blast' || $page == 'CedWad-filters')
		{
			add_action('all_admin_notices',array($this,'CedWadBannerImage'));
		}
		add_action('admin_menu',array($this,'loadAdminPages'));
		add_action('plugins_loaded',array($this,'load_plugin_textdomain'));
		/**
		* action to handle recived details through ajax 
		*/
		add_action('CedWad_front_page',array($this,'CedWadFrontPage'));
		add_action( 'CedWad_license_panel', array( $this, 'CedWadLicensePanel' ) );
		add_action('wp_ajax_CedWad_Pushingdeatils', array($this, 'CedWadProcessdetails'));
		add_action('wp_ajax_CedWad_PushingRules', array($this, 'CedWadPushingRules'));
		add_action('wp_ajax_CedWad_createBunch', array($this, 'CedWad_createBunch'));
		add_action('wp_ajax_CedWad_blastProduct', array($this, 'CedWad_createProducts'));
		add_action('wp_ajax_CedWad_blastBunch', array($this, 'CedWad_createProducts'));
		add_action('wp_ajax_CedWad_addProductToBunch', array($this, 'CedWad_addProductToBunch'));
		add_action('wp_ajax_CedWad_removeProductFromBunch', array($this, 'CedWad_removeProductFromBunch'));
		add_action( 'wp_ajax_nopriv_CedWad_validate_licensce', array( $this, 'CedWad_validate_license_callback') );
		add_filter( 'CedWad_license_check', array ( $this, 'CedWad_license_check_function' ), 10, 1);
		add_action('wp_ajax_chromeRequst', array($this, 'CedWadGetOrderDetailsFromAliexpress'));
		add_action('wp_ajax_CedWad_regenerate_license',array($this, 'CedWad_regenerate_license'));
		add_action('wp_ajax_CedWadGetEmail',array($this,'CedWadGetUserEmail'));
		add_action('wp_ajax_CedWadGetlicense',array($this,'CedWadLicenseKey'));
		add_action( 'cedWad_auto_import_cron_job', array( $this, 'cedWad_auto_import_cron_job' ) );
	}

	/**
		* function for checking license 
		*/

		public function CedWad_license_check_function($check)
		{
			$CedWad_license = get_option('CedWad_lincense',false);
			$CedWad_license_key = get_option('CedWad_lincense_key',false);
			$CedWad_license_module = get_option('CedWad_lincense_module',false);

			if(!empty($CedWad_license))
			{
				$response = json_decode($CedWad_license, true);
				$ced_hash = '';

				if(isset($response['hash']) && isset($response['level']))
				{
					$ced_hash = $response['hash'];
					$ced_level = $response['level'];
					{
						$i=1;
						for($i=1;$i<=$ced_level;$i++)
						{
							$ced_hash = base64_decode($ced_hash);
						}
					}
				}

				$CedWad_license = json_decode($ced_hash, true);

				if(isset($CedWad_license['license']) && isset($CedWad_license['module_name']))
				{
					if($CedWad_license['license'] == $CedWad_license_key && $CedWad_license['module_name'] == $CedWad_license_module && $CedWad_license['domain'] == $_SERVER['HTTP_HOST'])
					{
						$check = true;
					}
				}
			}
			return $check;
		}


	/**
		* function for listing Advertisement
		*/


		public function CedWadBannerImage()
		{
			$product_limit = get_option('CedWad_product_limit');
			if($product_limit == 'limit_exceed')
			{ 

				?>
				<div class="CedWad_maximum_limit_reached notice notice-warning">
					<p>
						<?php _e( "Maximum Import Limit Reached! Upgrade to premium version to import more products ", 'CedWad' ); ?><a target="blank" href="https://dropshipping.cedcommerce.com/solutions/aliexpress/woocommerce/"><?php _e( 'Click Here', 'CedWad' ); ?></a><?php _e( ' to Upgrade', 'CedWad' );  ?>
					</p>
				</div>
				<?php
			}
			?>
			<div class="CedWad_promotion_images">
				<a target="blank" href="https://dropshipping.cedcommerce.com/solutions/aliexpress/woocommerce/" class="CedWad_upgrade_banner">
					<img src="<?php echo CedWad_URL.'admin/images/Aliexpress-banner.jpg' ?>">
				</a>
			</div>	
			<?php	
		}


	/**
		* function to jump over frontpage 
		*/

		public function CedWadFrontPage(){

			$fileNmae = CedWad_PATH."admin/partials/CedWad-registration-html.php";
			CedWadIncludeFile($fileNmae);

		}


	/**
		* function for License panel
		*/

		public function CedWadLicensePanel(){
			$fileNmae = CedWad_PATH."admin/partials/CedWad-license-html.php";
			CedWadIncludeFile($fileNmae);
		}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		$page = isset($_GET['page']) ? $_GET['page'] : false;
		if(strpos($page, 'CedWad') !== false){
			wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/CedWad-admin.css', array(), $this->version, 'all' );
			wp_enqueue_style( 'CedWad-bootstrap-css', plugin_dir_url( __FILE__ ) . 'css/bootstrap.min.css', array(), $this->version, 'all' );
			$action = isset($_GET['action']) ? $_GET['action'] : false;
			$page = isset($_GET['page']) ? $_GET['page'] : false;
			wp_enqueue_style( 'CedWad-admin-addnew', plugin_dir_url( __FILE__ ) . 'css/CedWad-admin-addnew.css', array(), $this->version, 'all' );
			if($page == 'CedWad-blast' && $action == 'edit' ){
				wp_enqueue_style( 'CedWad-admin-productedit', plugin_dir_url( __FILE__ ) . 'css/CedWad-admin-productedit.css', array(), $this->version, 'all' );
			}
		}
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		$action = isset($_GET['action']) ? $_GET['action'] : false;
		$page = isset($_GET['page']) ? $_GET['page'] : false;
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/CedWad-admin.js', array( 'jquery' ), $this->version, false );
		if(strpos($page, 'CedWad') !== false){
			wp_enqueue_script( 'CedWad-bootstrap-js', plugin_dir_url( __FILE__ ) . 'js/bootstrap.min.js', array( 'jquery' ), $this->version, false );
			wp_enqueue_script( 'CedWad-license', plugin_dir_url( __FILE__ ) . 'js/CedWad-license.js', array( 'jquery' ), $this->version, false );
			if($page == 'CedWad-filters'){
				wp_enqueue_script( 'CedWad-bunch-js', plugin_dir_url( __FILE__ ) . 'js/CedWad-bunch.js', array( 'jquery' ), $this->version, false );
				
			}

			if($page == 'CedWad-blast' || $page == 'CedWad-bunch'){
				wp_enqueue_script( 'CedWad-blast-js', plugin_dir_url( __FILE__ ) . 'js/CedWad-blast.js', array( 'jquery' ), $this->version, false );
				
			}
		}
		wp_localize_script( $this->plugin_name, 'CedWad_action_handler', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
	}

	/**
	 * Creating admin area settings 
	 *
	 * @since    1.0.0
	 */
	public function loadAdminPages() {
		add_menu_page( __( 'Aliexpress Dropshipping','CedWad'),__('CedCommerce Dropshipping','CedWad'), 'manage_woocommerce', 'CedWad-config', array( $this, 'CedWadConfigDisplay' ),'dashicons-thumbs-up', 59.56 );
		add_submenu_page('CedWad-config', __('Get Started','CedWad'), __('Get Started','CedWad'), 'manage_woocommerce', 'CedWad-config', array( $this, 'CedWadConfigDisplay' ) );
		add_submenu_page('CedWad-config', __('Filters','CedWad'), __('Filters','CedWad'), 'manage_woocommerce', 'CedWad-filters', array( $this, 'CedWadFiltersDisplay' ) );
		add_submenu_page('CedWad-config', __('Bunches','CedWad'), __('Bunches','CedWad'), 'manage_woocommerce', 'CedWad-bunch', array( $this, 'CedWadBunchsDisplay' ) );
		add_submenu_page('CedWad-config', __('Blast Now','CedWad'), __('Blast Now :)','CedWad'), 'manage_woocommerce', 'CedWad-blast', array( $this, 'CedWadBlastDisplay' ) );
	}

	
	/**
	 * callback to create admin area html
	 *
	 * @since    1.0.0
	 */
	public function CedWadConfigDisplay()
	{
		$CedWad_license_response = get_option('license_key_response');
		if($CedWad_license_response == 'verified')
		{
			$fileNmae=CedWad_PATH."admin/partials/CedWad-admin-config.php";
			CedWadIncludeFile($fileNmae);
		}
		else
		{	
			$CedWad_license = get_option('license_key_Aliexpress',"");
			if(isset($CedWad_license) && !empty($CedWad_license)){
				$CedWad_license = json_decode($CedWad_license,true);
			}
			if(is_array($CedWad_license))
			{
				foreach ($CedWad_license as $key => $value) 
				{
					if($value['status'] == '200ok' && $key == $_SERVER['HTTP_HOST'])
					{	
						$fileNmae = CedWad_PATH."admin/partials/CedWad-license-html.php";
						CedWadIncludeFile($fileNmae);
					}

				}
			}
			else
			{
				do_action("CedWad_front_page");
			}	
		}

	}

	/**
	 * callback to create admin area html
	 *
	 * @since    1.0.0
	 */
	public function CedWadBunchsDisplay(){
		$CedWad_license_response = get_option('license_key_response');
		if($CedWad_license_response == 'verified')
		{
			$fileNmae=CedWad_PATH."admin/partials/CedWad-admin-bunch.php";
			CedWadIncludeFile($fileNmae);
		}
		else
		{	
			$CedWad_license = get_option('license_key_Aliexpress',"");
			if(isset($CedWad_license) && !empty($CedWad_license)){
				$CedWad_license = json_decode($CedWad_license,true);
			}
			if(is_array($CedWad_license))
			{
				foreach ($CedWad_license as $key => $value) 
				{
					if($value['status'] == '200ok' && $key == $_SERVER['HTTP_HOST'])
					{	
						$fileNmae = CedWad_PATH."admin/partials/CedWad-license-html.php";
						CedWadIncludeFile($fileNmae);
					}
				}
			}
			else
			{
				do_action("CedWad_front_page");
			}	
		}
	}

	/**
	 * callback to create admin area html
	 *
	 * @since    1.0.0
	 */
	public function CedWadFiltersDisplay(){
		$CedWad_license_response = get_option('license_key_response');
		if($CedWad_license_response == 'verified')
		{
			$fileNmae=CedWad_PATH."admin/partials/CedWad-admin-filter.php";
			CedWadIncludeFile($fileNmae);
		}
		else
		{	
			$CedWad_license = get_option('license_key_Aliexpress',"");
			if(isset($CedWad_license) && !empty($CedWad_license)){
				$CedWad_license = json_decode($CedWad_license,true);
			}
			if(is_array($CedWad_license))
			{
				foreach ($CedWad_license as $key => $value) 
				{
					if($value['status'] == '200ok' && $key == $_SERVER['HTTP_HOST'])
					{	
						$fileNmae = CedWad_PATH."admin/partials/CedWad-license-html.php";
						CedWadIncludeFile($fileNmae);
					}

				}
			}
			else
			{
				do_action("CedWad_front_page");
			}	
		}
	}

	/**
	 * callback to create admin area html
	 *
	 * @since    1.0.0
	 */
	public function CedWadBlastDisplay(){
		$CedWad_license_response = get_option('license_key_response');
		if($CedWad_license_response == 'verified')
		{
			$fileNmae=CedWad_PATH."admin/partials/CedWad-admin-blast.php";
			CedWadIncludeFile($fileNmae);
		}
		else
		{	
			$CedWad_license = get_option('license_key_Aliexpress',"");
			if(isset($CedWad_license) && !empty($CedWad_license)){
				$CedWad_license = json_decode($CedWad_license,true);
			}
			if(is_array($CedWad_license))
			{
				foreach ($CedWad_license as $key => $value) 
				{
					if($value['status'] == '200ok' && $key == $_SERVER['HTTP_HOST'])
					{	
						$fileNmae = CedWad_PATH."admin/partials/CedWad-license-html.php";
						CedWadIncludeFile($fileNmae);
					}

				}
			}
			else
			{
				do_action("CedWad_front_page");
			}	
		}
	}

	/**
	  *callback to handle and process request.
	  *
	  * @since 1.0.0
	 **/
	public function CedWadProcessdetails(){
		$nonce = isset($_POST['_nonce']) ? sanitize_text_field($_POST['_nonce']) : false;
		$action = isset($_POST['action']) ? sanitize_text_field($_POST['action']) : false;
		if($action == 'CedWad_Pushingdeatils' && $nonce == 'CedWad_Pushingdeatils'){
			$details = isset($_POST['details']) ? $_POST['details'] :false;
			if($details && is_array($details)){
				$saveddetails = CedWadUpdateOption('CedWad_cofiguration_details', $details);
				if($saveddetails){
					echo prapareAjaxResponse('success');die;
				}
			}
		}
	}

	/**
	  *callback to handle and process rules.
	  *
	  * @since 1.0.0
	 **/
	public function CedWadPushingRules(){
		$nonce = isset($_POST['_nonce']) ? sanitize_text_field($_POST['_nonce']) : false;
		$action = isset($_POST['action']) ? sanitize_text_field($_POST['action']) : false;
		if($action == 'CedWad_PushingRules'  && $nonce == 'CedWad_PushingRules'){
			$rules = isset($_POST['details']) ? $_POST['details'] :false;
			$toDo = isset($rules['todo']) ? $rules['todo'] : false;
			if($rules && is_array($rules)){
				$rules = isset($rules['rules']) ? $rules['rules'] :false;
				if(is_numeric($toDo)){
					$updateRulesData = updateRulesData($toDo, $rules);
					if($updateRulesData){

						echo prapareAjaxResponse('success');die;
					}
					else{
						echo prapareAjaxResponse('failure');die;
					}
				}else{
					$saveRulesData = saveRulesData($rules);
					if($saveRulesData){
						echo prapareAjaxResponse('success');die;
					}
					else{
						echo prapareAjaxResponse('failure');die;
					}
				}
				
			}
		}
	}

	/**
	  * Create Bunch of products from filter.
	  *
	  * @since 1.0.0
	 **/
	public function CedWad_createBunch( $create_manual_bunch = false ){
		$nonce = isset($_POST['_nonce']) ? sanitize_text_field($_POST['_nonce']) : false;
		$action = isset($_POST['action']) ? sanitize_text_field($_POST['action']) : false;
		$bunchData = array();
		if($action == 'CedWad_createBunch' && $nonce == 'CedWad_createBunch'){
			$filterId = isset($_POST['filetrId']) ? sanitize_text_field($_POST['filetrId']) : false;
			if( !$create_manual_bunch )
				$bunchId = CedWadCreateBunch($filterId);
			else
				$bunchData = CedWadCreateBunch( $filterId, true );
		}
	}

	/**
	  * Manually filter products to create a Bunch.
	  *
	  * @since 1.0.0
	 **/
	public function CedWad_addProductToBunch(){
		$products = array();
		$filterId = isset( $_POST['filterId'] ) ? sanitize_text_field($_POST['filterId']) : "";
		$bunchId = isset( $_POST['bunchId'] ) ? sanitize_text_field($_POST['bunchId']) : "";
		$productId = isset( $_POST['productId'] ) ? sanitize_text_field($_POST['productId']) : "";
		$productData = isset( $_POST['productData'] ) ? unserialize(stripslashes($_POST['productData'])) : array();
		if( $bunchId == "" )
		{
			if( !empty( $productData ) )
				$products[] = $productData;
			$bunchId = CedWadInsertBunch( $products, $filterId, 'manual' );
		}
		else
		{
			$bunchData = CedWadGetBunchData( $bunchId );
			$products  = isset( $bunchData['products'] ) ? json_decode($bunchData['products'], true) : array();
			if( !empty( $products ) && !empty( $productData ) )
			{
				$products[] = $productData;
			}
			$response = CedWadUpdateBunch( $products, $filterId, $bunchId );
		}
		echo $bunchId;
		wp_die();
	}

	/**
	  * Remove products from a Bunch.
	  *
	  * @since 1.0.0
	 **/
	public function CedWad_removeProductFromBunch(){
		$filterId = isset( $_POST['filterId'] ) ? sanitize_text_field($_POST['filterId']) : "";
		$bunchId = isset( $_POST['bunchId'] ) ? sanitize_text_field($_POST['bunchId']) : "";
		$productId = isset( $_POST['productId'] ) ? sanitize_text_field($_POST['productId']) : "";

		$res = CedWadRemoveProductFromBunch( $bunchId, $productId, $filterId );
		echo "Deleted";wp_die();
	}

	/**
	  * Create Products from the bunch.
	  *
	  * @since 1.0.0
	 **/
	public function CedWad_createProducts(){
		$nonce = isset($_POST['_nonce']) ? sanitize_text_field($_POST['_nonce']) : false;
		$action = isset($_POST['action']) ? sanitize_text_field($_POST['action']) : false;
		if($action == 'CedWad_blastProduct' && $nonce == 'CedWad_blastProduct'){
			$bunchId = isset($_POST['bunchId']) ? sanitize_text_field($_POST['bunchId']) : false;
			$product_id = isset($_POST['product_id']) ? sanitize_text_field($_POST['product_id']) : false;
			$bunchId = $this->CedWad_BlastProduct($bunchId, $product_id);
		}
		else if( $action == 'CedWad_blastBunch' && $nonce == 'CedWad_blastBunch' ){
			$bunchId = isset($_POST['bunchId']) ? sanitize_text_field($_POST['bunchId']) : false;
			$bunchId = $this->CedWad_BlastBunch($bunchId);
		}
		echo " CREATED ";wp_die();
	}

	/**
	  * Create Single Product from the bunch.
	  *
	  * @since 1.0.0
	 **/
	public function CedWad_BlastProduct( $bunchId = "" , $product_id = "" ){
		if( $product_id == "" || $bunchId == "" )
			return __( 'Missing Product Id or Bunch Id', 'CedWad' );
		$bunch_data = CedWadGetBunchData( $bunchId );
		$filter_id = isset( $bunch_data['filter-id'] ) ? sanitize_text_field($bunch_data['filter-id']) : "";

		/* GET FILTER DATA FOR BUNCH */
		$filterData = CedWadGetFilterData($filter_id);
		$filterMarkupData = isset($filterData['filter_data']) ? json_decode($filterData['filter_data'], true) : array();

		$products = isset( $bunch_data['products'] ) ? json_decode($bunch_data['products'], true) : array();
		
		if( !empty( $products ) ){
			$product = CedWadFetchProductFromBunch( $products, $product_id );
			if( is_array( $product ) && !empty( $product ) ){
				$response = $this->CedWad_CreateProductToStore( $product, $filterMarkupData );
			}

			return $response;
		}
	}

	/**
	  * Insert Single Product from the bunch to WOO store.
	  *
	  * @since 1.0.0
	 **/
	public function CedWad_CreateProductToStore( $product, $filterMarkupData ){
		if( !is_array( $product ) || empty( $product ) )
			return "Invalid Product Data";

		if( file_exists(CedWad_PATH.'admin/partials/CedWad-admin-create-product.php') )
		{
			$already_blasted = CedWad_ProductBlastedOrNot( $product['productId'] );

			if( $already_blasted == "Blasted" )
				$already_blasted = true;
			else
				$already_blasted = false;

			require(CedWad_PATH.'admin/partials/CedWad-admin-create-product.php');
			$CedWad_create_product = CedWad_Admin_Create_Product :: get_instance($product, $filterMarkupData, $already_blasted);
			$response = $CedWad_create_product->CedWadCreateProduct();
			return $response;
		}
	}

	/**
	  * Traverse bunch to create products .
	  *
	  * @since 1.0.0
	 **/
	public function CedWad_BlastBunch( $bunchId = "" ){
		if( $bunchId == "" )
			return __( 'Missing Bunch Id', 'CedWad' );
		$bunch_data = CedWadGetBunchData( $bunchId );
		$products = isset( $bunch_data['products'] ) ? json_decode($bunch_data['products'], true) : array();
		if( is_array( $products ) && !empty( $products ) )
		{
			foreach ($products as $key => $value) {
				$response = $this->CedWad_BlastProduct( $bunchId, $value['productId'] );
			}
			return $response;
		}
	}

	public function CedWadGetUserEmail(){
		$email=sanitize_text_field($_POST['email']);
		$url=$_SERVER['HTTP_HOST'];
		$resp = array(
			'method' => 'POST',
			'timeout' => 300,
			'redirection' => 5,
			'httpversion' => '1.0',
			'blocking' => true,
			'headers' => array(),
			'body' => array( 'email' => $email, 'domain' => $url, 'action' => 'license_generation' ),
			'cookies' => array()
			);    
		$remote_url = "http://demo.cedcommerce.com/woocommerce/aliexpress-dropshipping/license/Cedemailverification.php";
		$response = $this->CedWadSendRequest( $remote_url,$resp );
		update_option('license_key_Aliexpress',$response['body']);
		wp_die();
	}

	public function CedWadLicenseKey()
	{
		$license = sanitize_text_field($_POST['license_key']);
		$url=$_SERVER['HTTP_HOST'];
		$resp = array(
			'method' => 'POST',
			'timeout' => 45,
			'redirection' => 5,
			'httpversion' => '1.0',
			'blocking' => true,
			'headers' => array(),
			'body' => array( 'license' => $license, 'domain' => $url, 'action' => 'user_license_key' ),
			'cookies' => array()
			);	
		$remote_url = "http://demo.cedcommerce.com/woocommerce/aliexpress-dropshipping/license/Cedemailverification.php";
		$response = $this->CedWadSendRequest( $remote_url,$resp );
		update_option('license_key_response',$response['body']);
		wp_die();
	}

	/**
		* function for regenerating License key
		*/

	public function CedWad_regenerate_license()
	{
		delete_option('license_key_Aliexpress');
	}

	/**
		* function for auto import cron
		*/


	public function cedWad_auto_import_cron_job()
	{
		ini_set( 'max_execution_time', '-1' );
		$autoImportFilters = get_option( 'cedWad_autoImportFilters', array() );
		foreach ($autoImportFilters as $filterId => $value) {
			if( isset( $value['numberOfProductsToImport'] ) )
			{
				$pageNumber = isset( $value['pageImported'] ) ? $value['pageImported'] : 1;
				if( $productsImported < $value['numberOfProductsToImport'] )
				{
					$filterDetails = CedWadGetFilterData( $filterId );
					$productList = CedWadCreateBunch( $filterId, true, $pageNumber );
					if( !empty( $productList ) )
					{
						foreach ($productList as $key1 => $product) {

							$response = $this->cedWadBlastProductFromFilter( $filterId, $product['productId'], $product );
						}
						$pageNumber = intval($pageNumber) + 1;
						$autoImportFilters[$filterId]['pageImported'] = $pageNumber;
						update_option( 'cedWad_autoImportFilters', $autoImportFilters );
					}
					break;
				}
			}
		}
		wp_die();
	}


	/**
		* function for creating products from filter
		*/

	public function cedWadBlastProductFromFilter( $filterId = "", $product_id = "", $product = array() ){

		if( $product_id == "" || $filterId == "" )
			return __( 'Missing Product Id or Bunch Id', 'CedWad' );


		/* GET FILTER DATA FOR BUNCH */
		$filterData = CedWadGetFilterData($filterId);
		$filterMarkupData = isset($filterData['filter_data']) ? json_decode($filterData['filter_data'], true) : array();

		if( is_array( $product ) && !empty( $product ) ){
			$response = $this->CedWad_CreateProductToStore( $product, $filterMarkupData );
		}

		return $response;
	}


	/**
		* function for sending request
		*/

	public function CedWadSendRequest($remote_url = "", $args = "")
	{
		$response = wp_remote_post($remote_url,$args);
		return $response;
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'CedWad',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/language/'
		);
	}

}
