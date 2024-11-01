<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class CedWadFilters extends WP_List_Table {


	/** Class constructor */
	public function __construct() {
		parent::__construct( [
			'singular' => __( 'Filters', 'CedWad' ), //singular name of the listed records
			'plural'   => __( 'Filters', 'CedWad' ), //plural name of the listed records
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
	public function getFilters( $per_page = 5, $page_number = 1 ) {

		global $wpdb;
		$prefix = $wpdb->prefix . CedWad_PREFIX;
		$tableName = $prefix.'filters';
		$customresult = array();
		$sql = "SELECT `id`,`name`,`filter_data` FROM `$tableName` ORDER BY `id` DESC";
		$sql .= " LIMIT $per_page";
		$sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;

		$result = $wpdb->get_results($sql,'ARRAY_A');
		if(is_array($result) && !empty($result)){
			foreach ($result as $key => $value) {
				$filterData = json_decode($value['filter_data'], true);
				if(isset($filterData['keyword']) && isset($filterData['cat']) && isset($filterData['catname']))
				$customresult[] = array('id'=>$value['id'] ,'name'=>$value['name'], 'Keyword'=>$filterData['keyword'], 'Category'=>$filterData['cat'],'Categoryname'=>$filterData['catname']);
			}
		}
		return $customresult;
	}

	/**
	* Function to count number of responses in result
	*/
	public function get_count( ) {
		global $wpdb;
		$prefix = $wpdb->prefix . CedWad_PREFIX;
		$tableName = $prefix.'filters';
		$sql = "SELECT * FROM `$tableName`";
		$result = $wpdb->get_results($sql,'ARRAY_A');
		return count($result);
	}
	
	/** Text displayed when no customer data is available */
	public function no_items() {
		_e( 'No Filters avaliable.', 'CedWad' );
	}

	/**
	 * Method for name column
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	function column_name( $item ) {
		$title = '<strong>' . $item['name'] . '</strong>';
		$actions = [
		'edit' => sprintf( '<a href="?page=%s&action=%s&filterId=%s">Edit</a>', esc_attr( $_REQUEST['page'] ), 'edit', $item['id'] ),
		'delete' => sprintf( '<a href="?page=%s&action=%s&filterId=%s">Delete</a>', esc_attr( $_REQUEST['page'] ), 'delete', $item['id'] )
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
	function column_Keyword( $item ) {
		$keyword = '<strong>' . $item['Keyword'] . '</strong>';
		return $keyword;
	}

	/**
	 * Method for name column
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	function column_Category( $item ) {
		$category = '<strong>' . $item['Categoryname'] . '</strong>';
		return $category;
	}

	/**
	 * Method for name column
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	function column_bunch( $item ) {
		$html = '<button type="button" name="CedWad_createBunchButton" class="CedWad_bunch primary button" id="CedWad_bunch_'.$item['id'].'" data-filterId = "'.$item['id'].'">'.__('Bunch it', 'CedWad').'</button><div class="CedWad_loader_filter_table" style="display: none"><img width="50" src="'.CedWad_URL.'admin/images/loader.gif"></div>';
		return $html;
	}
	
	/**
	 *  Associative array of columns
	 *
	 * @return array
	 */
	function get_columns() {
		$columns = [
		'name'    => __( 'Name', 'CedWad' ),
		'keyword' => __( 'Keyword', 'CedWad' ),
		'category' => __( 'Category', 'CedWad' ),
		'bunch' => __( 'Create Bunch', 'CedWad' ),
		
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

		$per_page = apply_filters( 'CedWad_list_profiles_per_page', 10 );
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
		
		$this->items = self::getFilters( $per_page, $current_page );

		$count = self::get_count( );


		// Set the pagination
		$this->set_pagination_args( array(
			'total_items' => $count,
			'per_page'    => $per_page,
			'total_pages' => ceil( $count / $per_page )
			) );

		if(!$this->current_action()) {
			$this->items = self::getFilters( $per_page, $current_page );
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
		<div class="CedWad_wrap CedWad_wrap_extn CedWad_plugin_wrapper CedWad_padding_main CedWad_filters_table_wrap">
			<div class="CedWad_heading CedWad_heading_table">
				<h3 class="CedWad_setting_header"><?php _e('Filters','CedWad');?></h3>
				<div class="CedWad_button_filter_table">
					<?php echo '<a href="'. get_admin_url() .'admin.php?page=CedWad-filters&action=addNew" class="button button-CedWad page-title-action ">' . __('Add Filters','CedWad') . '</a>';?>
				</div>
			</div>
			<div>
				<?php
				
				if(isset($_SESSION['CedWad_validation_notice'])) {
					$value = $_SESSION['CedWad_validation_notice'];
					$cedumbhelper->umb_print_notices($value);
					unset($_SESSION['CedWad_validation_notice']);
				}
				?>
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
}
$CedWadFilters = new CedWadFilters();
$CedWadFilters->prepare_items();