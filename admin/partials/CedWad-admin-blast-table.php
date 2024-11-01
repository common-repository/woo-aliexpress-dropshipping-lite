<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class CedWadBlast extends WP_List_Table {

	/** Class constructor */
	public function __construct() {
		parent::__construct( [
			'singular' => __( 'Blast Product', 'CedWad' ), //singular name of the listed records
			'plural'   => __( 'Blast Products', 'CedWad' ), //plural name of the listed records
			'ajax'     => false //does this table support ajax?
			] );
	}

	/**
	 * Retrieve filters details 
	 *
	 * @param int $per_page
	 * @param int $page_number
	 *
	 * @return mixed
	 */
	public function getBunchDetails( $per_page = 5, $page_number = 1 ) {

		$bunch_id = isset( $_GET['bunch_id'] ) ? $_GET['bunch_id'] : false;
		$products = array();
		if( $bunch_id )
		{
			$bunch_data = CedWadGetBunchData( $bunch_id );
			$filter_id = isset( $bunch_data['filter-id'] ) ? $bunch_data['filter-id'] : "";
			
			/* GET FILTER DATA FOR BUNCH */
			$this->filterData = CedWadGetFilterData($filter_id);
			$this->filterName = isset($filterData['name']) ? $filterData['name'] : "";

			$products = isset( $bunch_data['products'] ) ? json_decode($bunch_data['products'], true) : array();
		}
		return $products;
	}

	/**
	* Function to count number of responses in result
	*/
	public function get_count( ) {
		return count($this->items);
	}
	
	/** Text displayed when no customer data is available */
	public function no_items() {
		_e( 'No Bunches avaliable.', 'CedWad' );
	}

	/**
	 * Method for name column
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	function column_imageUrl( $item ) {
		$title = '<img src="'.$item['imageUrl'].'" width="100px" height="100px">';
		return $title;
	}

	/**
	 * Method for name column
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	function column_productTitle( $item ) {
		$title = '<strong>' . $item['productTitle'] . '</strong>';
		$actions = [
		'view' => '<a href="'.$item["productUrl"].'">View</a>',
		];
		return $title . $this->row_actions( $actions );
	}

	/**
	 * Method for name column
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	function column_originalPrice( $item ) {
		$title = '<strong>' . $item['originalPrice'] . '</strong>';
		return $title;
	}

	/**
	 * Method for name column
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	function column_commissionRate( $item ) {
		$title = '<strong>' . $item['commissionRate'] . '</strong>';
		return $title;
	}

	/**
	 * Method for name column
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	function column_30daysCommission( $item ) {
		$title = '<strong>' . $item['30daysCommission'] . '</strong>';
		return $title;
	}

	/**
	 * Method for name column
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	function column_blast( $item ) {
		$html ='<button id="CedWad_blast_'.$item['productId'].'" type="button" class="primary button CedWad_blast" data-bunchId = "'.$_GET["bunch_id"].'" data-productId="'.$item["productId"].'" data-blast="product" >'.__('Blast Now!', 'CedWad').'</button><div class="CedWad_loader_blast_table" style="display: none"><img width="50" src="'.CedWad_URL.'admin/images/loader.gif"></div>';
		return $html;
	}

	/**
	 *  Associative array of columns
	 *
	 * @return array
	 */
	function get_columns() {
		$columns = [
		'imageUrl'    => __( 'Image', 'CedWad' ),
		'productTitle' => __( 'Title', 'CedWad' ),
		'originalPrice' => __( 'Price', 'CedWad' ),
		'30daysCommission' => __( '30 Days Commission', 'CedWad' ),
		'blast' => __( 'Blast Now!', 'CedWad' ),
		];
		$columns = apply_filters( 'CedWad_alter_feed_table_columns', $columns );
		return $columns;
	}
	/**
	 * Columns to make sortable.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		return $sortable_columns = array();
	}
	/**
	 * Handles data query and filter, sorting, and pagination.
	 */
	public function prepare_items() {

		global $wpdb;

		$per_page = apply_filters( 'CedWad_list_bunch_products_per_page', 20 );
		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = $this->get_sortable_columns();

		// Column headers
		$this->_column_headers = array( $columns, $hidden, $sortable );

		
		$current_page = $this->get_pagenum();
		if ( 1 < $current_page ) {
			$offset = $per_page * ( $current_page - 1 );
		} else {
			$offset = 0;
		}
		
		$this->items = self::getBunchDetails( $per_page, $current_page );
		$count = self::get_count( );

		// Set the pagination
		$this->set_pagination_args( array(
			'total_items' => $count,
			'per_page'    => $per_page,
			'total_pages' => ceil( $count / $per_page )
			) );
		if(!$this->current_action()) {
			$this->items = self::getBunchDetails( $per_page, $current_page );
			$this->renderHTML();
		}
		else {
			$this->process_bulk_action();
		}
	}
	
	/**
	* Function to get changes in html
	*/
	public function renderHTML() {
		?>
		<div class="CedWad_wrap CedWad_wrap_extn CedWad_plugin_wrapper CedWad_padding_main CedWad_blasts_table_wrap">
			<div class="CedWad_heading CedWad_heading_table">
				<h3 class="CedWad_setting_header"><?php _e('Bunch Details','CedWad');?></h3>
				<?php 
				$bunches = $this->CedWad_GetBunchesList();
				?>
				<div class="CedWad_button_filter_table">
					<form method="get" action="">
						<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
						<select name="bunch_id">
							<option value=""><?php _e( 'Select Bunch', 'CedWad' ); ?></option>
							<?php 
							foreach ($bunches as $key => $value) {
								$selected = "";
								if( $key == $_GET['bunch_id'] )
									$selected = "selected";
								?>
								<option value="<?php echo $key; ?>" <?php echo $selected; ?>><?php echo $value; ?></option>
								<?php
							}
							?>
						</select>
						<?php submit_button( __( 'Select', 'CedWad' ), 'action', '', false, array() ); ?>
					</form>
				</div>
			</div>
			<div>
				<div id="post-body" class="metabox-holder columns-2">
					<div id="post-body-content">
						<div class="meta-box-sortables ui-sortable">
							<form method="post">
								<?php
								$this->display();
								?>
							</form>
						</div>
					</div>
					<div class="clear"></div>
				</div>
				<br class="clear">
			</div>
		</div>
		<?php
	}

	/**
*This function to get bunch list 
*@CedWadDeleteFilterFromId
*@author CedCommerce <plugins@cedcommerce.com>
* @link  https://www.cedcommerce.com/
*/

public function CedWad_GetBunchesList()
{
	global $wpdb;
	$prefix = $wpdb->prefix . CedWad_PREFIX;
	$tableName = $prefix.'bunches';
	$bunches = array();
	$sql = "SELECT `id`,`filter-id`,`blast`, `products` FROM `$tableName` ORDER BY `id` DESC";

	$result = $wpdb->get_results($sql,'ARRAY_A');
	if(is_array($result) && !empty($result))
	{
		foreach ($result as $key => $value) 
		{
			$products = json_decode($value['products'], true);
			$bunch_size = count( $products );
			$filter_id = $value['filter-id'];
			$tableName = $prefix.'filters';
			$sql = "SELECT `name` FROM $tableName WHERE id=$filter_id";
			$result = $wpdb->get_results($sql,'ARRAY_A');
			$filter_name = isset( $result[0]['name'] ) ? $result[0]['name'] : "";

			$bunches[$value['id']] = $filter_name." ( ".$bunch_size." Products )";
		}
	}
	return $bunches;
}
}
$CedWadBlast = new CedWadBlast();
$CedWadBlast->prepare_items();