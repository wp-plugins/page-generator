<?php
/**
* Keywords WP_List_Table
* 
* @package WP Cube
* @subpackage Page Generator Pro
* @author Tim Carr
* @version 1.0
* @copyright WP Cube 
*/ 
class PGKeywords_List_Table extends WP_List_Table {
	/**
	* Constructor, we override the parent to pass our own arguments
	*
	* We usually focus on three parameters: singular and plural labels, as well as whether the class supports AJAX.
	*/
	function __construct() {
		parent::__construct( array(
			'singular'=> 'keyword', // Singular label
			'plural' => 'keywords', // plural label, also this well be one of the table css class
			'ajax'	=> false // We won't support Ajax for this table
		));
	}
	
	/**
	* Defines the message to display when no items exist in the table
	*/
	function no_items() {
		_e('Keywords are used to product unique content for each Page that is generated.', 'page-generator');
		echo ('<br /><a href="admin.php?page=page-generator&cmd=add" class="button">'.__('Create first keyword.', 'page-generator').'</a>');
	}
	 
	/**
 	* Define the columns that are going to be used in the table
 	*
 	* @return array $columns, the array of columns to use with the table
 	*/
	function get_columns() {
		return array(
			'cb' => '<input type="checkbox" class="toggle" />',
			'col_field_keyword' => __('Keyword'),
		);
	}
	
	/**
 	* Decide which columns to activate the sorting functionality on
 	*
 	* @return array $sortable, the array of columns that can be sorted by the user
 	*/
	public function get_sortable_columns() {
		return $sortable = array(
			'col_field_keyword' => array('keyword', true)
		);
	}
	
	/**
	* Overrides the list of bulk actions in the select dropdowns above and below the table
	*/
	public function get_bulk_actions() {
		return array(
			'delete' => __('Delete'),
		);
	}
	
	/**
 	* Prepare the table with different parameters, pagination, columns and table elements
 	*/
	function prepare_items() {
		global $pgKeywords, $_wp_column_headers;
		
		$screen = get_current_screen();
		
		// Get params
		$search = (isset($_REQUEST['s']) ? $_REQUEST['s'] : '');
		$orderBy = (isset($_GET['orderby']) ? $_GET['orderby'] : '');
  		$order = (isset($_GET['order']) ? $_GET['order'] : '');
		
		// Adjust as necessary to display the required number of rows per screen
		$rowsPerPage = 10;

		// Get all records
		$total = $pgKeywords->GetTotal($search);
		
		// Define pagination if required
		$paged = ((isset($_GET['paged']) AND !empty($_GET['paged'])) ? mysql_real_escape_string($_GET['paged']) : '');
        if(empty($paged) OR !is_numeric($paged) OR $paged<=0 ) $paged = 1;
        $totalPages = ceil($total / $rowsPerPage);
		$this->set_pagination_args( array(
			'total_items' => $total,
			'total_pages' => $totalPages,
			'per_page' => $rowsPerPage,
		));
		
		// Set table columns and rows
		$columns = $this->get_columns();
  		$hidden  = array();
  		$sortable = $this->get_sortable_columns();
  		$this->_column_headers = array( $columns, $hidden, $sortable );
  		$this->items = $pgKeywords->getAll($orderBy, $order, $paged, $rowsPerPage, $search);
	}

	/**
	* Display the rows of records in the table
	* @return string, echo the markup of the rows
	*/
	function display_rows() {
		global $pageGenerator, $pgKeywords;

		// Get rows and columns
		$records = $this->items;
		list($columns, $hidden) = $this->get_column_info();
		
		// Go through each row
		if (!empty($records)) {
			foreach ($records as $key=>$rec) {
				echo ('<tr id="record_'.$rec->keywordID.'"'.(($key % 2 == 0) ? ' class="alternate"' : '').'>');
				
				// Go through each column
				foreach ($columns as $columnName=>$columnDisplayName) {
					switch ($columnName) {
						case 'cb': 
							echo ('<th scope="row" class="check-column"><input type="checkbox" name="keywordIDs['.stripslashes($rec->keywordID).']" value="'.stripslashes($rec->keywordID).'" /></th>'); 
							break;
						case 'col_field_keyword': 
							echo ('	<td class="'.$columnName.' column-'.$columnName.'">
										<strong><a href="admin.php?page='.$pageGenerator->plugin->name.'&cmd=edit&pKey='.stripslashes($rec->keywordID).'" title="'.__('Edit this item').'">'.stripslashes($rec->keyword).'</a></strong>
										<div class="row-actions">
											<span class="edit"><a href="admin.php?page='.$pageGenerator->plugin->name.'&cmd=edit&pKey='.stripslashes($rec->keywordID).'" title="'.__('Edit this item').'">'.__('Edit').'</a> | </span>
											<span class="trash"><a href="admin.php?page='.$pageGenerator->plugin->name.'&cmd=delete&pKey='.stripslashes($rec->keywordID).'" title="'.__('Delete this item').'" class="delete">'.__('Delete').'</a></span>
										</div>
									</td>'); 
							break;
					}
				}	
				
				echo ('</tr>');
			}
		}
	}
}
?>