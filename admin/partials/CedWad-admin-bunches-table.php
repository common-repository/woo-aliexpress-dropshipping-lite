<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class CedWadBunches extends WP_List_Table {

	/** Class constructor */
	public function __construct() {	
		parent::__construct( [
			'singular' => __( 'Bunch', 'CedWad' ), //singular name of the listed records
			'plural'   => __( 'Bunches', 'CedWad' ), //plural name of the listed records
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
	public function getBunches( $per_page = 5, $page_number = 1 ) {

		global $wpdb;
		$prefix = $wpdb->prefix . CedWad_PREFIX;
		$tableName = $prefix.'bunches';
		$customresult = array();
		$sql = "SELECT `id`,`filter-id`,`blast`, `products` FROM `$tableName` ORDER BY `id` DESC";
		$sql .= " LIMIT $per_page";
		$sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;

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
				$customresult[] = array('bunch_id'=>$value['id'] ,'created_from'=>$filter_name, 'bunch_size'=>$bunch_size);
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
		$tableName = $prefix.'bunches';
		$sql = "SELECT * FROM `$tableName`";
		$result = $wpdb->get_results($sql,'ARRAY_A');
		return count($result);
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
	function column_created_from( $item ) {
		$title = '<strong>' . $item['created_from'] . '</strong>';
		$actions = [
		'view' => sprintf( '<a href="?page=CedWad-blast&bunch_id=%s">View</a>', $item['bunch_id'] ),
		'delete' => sprintf( '<a href="?page=%s&action=%s&bunchId=%s">Delete</a>', esc_attr( $_REQUEST['page'] ), 'delete', $item['bunch_id'] )
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
	function column_bunch_size( $item ) {
		$title = '<strong>' . $item['bunch_size'] . '</strong>';
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
		$html ='<button type="button" id="CedWad_blast_'.$item['bunch_id'].'" class="primary button CedWad_blast" data-bunchId = "'.$item['bunch_id'].'" data-blast="bunch" >'.__('Blast Now!', 'CedWad').'</button><div class="CedWad_loader_blast_table" style="display: none"><img width="50" src="'.CedWad_URL.'admin/images/loader.gif"></div>';
		return $html;
	}
	/**
	 *  Associative array of columns
	 *
	 * @return array
	 */
	function get_columns() {
		$columns = [
		'created_from'    => __( 'Created From', 'CedWad' ),
		'bunch_size' => __( 'Bunch Size', 'CedWad' ),
		'blast' => __( 'Blast Bunch', 'CedWad' ),
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

		$per_page = apply_filters( 'CedWad_list_bunches_per_page', 10 );
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
		
		$this->items = self::getBunches( $per_page, $current_page );

		$count = self::get_count( );


		// Set the pagination
		$this->set_pagination_args( array(
			'total_items' => $count,
			'per_page'    => $per_page,
			'total_pages' => ceil( $count / $per_page )
			) );

		if(!$this->current_action()) {
			$this->items = self::getBunches( $per_page, $current_page );
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
		<div class="CedWad_wrap CedWad_wrap_extn CedWad_plugin_wrapper CedWad_padding_main CedWad_bunches_table">
			<div class="CedWad_heading CedWad_heading_table">
				<h3 class="CedWad_setting_header"><?php _e('Bunches','CedWad');?></h3>
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
$CedWadBunches = new CedWadBunches();
$CedWadBunches->prepare_items();