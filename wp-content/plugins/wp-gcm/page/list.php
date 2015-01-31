<?php

if(!class_exists('WP_List_Table')){
   require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Device_List_Table extends WP_List_Table {
	
	function __construct() {
		parent::__construct( array(
			'singular'=> 'px_gcm_device',
			'plural' => 'px_gcm_devices',
			'ajax'   => false) 
		);
	}
  
	public function prepare_items() {
		global $wpdb;
        $px_table_name = $wpdb->prefix.'gcm_users';
		$query = "SELECT * FROM $px_table_name";
		
		// Delete Action
	    if(isset($_GET['action']) && $_GET['action']  == 'delete'){
			$device = $_GET['device'];
			$result = $wpdb->query($wpdb->prepare("DELETE FROM $px_table_name WHERE id = %s",$device));
        }
		
		// View Action
		if(isset($_GET['action']) && $_GET['action'] == 'view'){
		   $start = new px_gcm_device_view();
		   $start->startup($_GET['device']);
		}
		
        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();
		
		// Ordering parameters 
		$orderby;
		$order;
		if(!empty($_GET['orderby'])) { 
			$orderby = $_GET['orderby']; 
		}
		
        if(!empty($_GET['order'])) { 
			$order = $_GET['order']; 
		}
		
        if(!empty($orderby) & !empty($order)){ 
			$query.=' ORDER BY '.$orderby.' '.$order; 
		}
		
		// Pagination parameters
        $totalitems = $wpdb->query($query);
        $perpage = 10;
        $paged = !empty($_GET["paged"]) ? mysql_real_escape_string($_GET["paged"]) : '';
		
        if(empty($paged) || !is_numeric($paged) || $paged<=0 ){ 
			$paged=1; 
		}
		
        $totalpages = ceil($totalitems/$perpage);
		if(!empty($paged) && !empty($perpage)){
			$offset=($paged-1)*$perpage;
			$query.=' LIMIT '.(int)$offset.','.(int)$perpage;
		}

		// Register the pagination
		$this->set_pagination_args( array(
			"total_items" => $totalitems,
			"total_pages" => $totalpages,
			"per_page" => $perpage) 
		);
		
        $this->_column_headers = array($columns, $hidden, $sortable);
		
		//Filter for search
		if(empty($_GET['s'])){
			$this->items = $wpdb->get_results($query); 
		}else {
			$se = $_GET['s'];
			$query = "SELECT * FROM $px_table_name WHERE 
				gcm_regid LIKE '%{$se}%' OR
				os LIKE '%{$se}%' OR
				created_at LIKE '%{$se}%' OR
				id LIKE '%{$se}%' OR
				model LIKE '%{$se}%'";
			$this->items = $wpdb->get_results($query);
		}
    }
	
	function extra_tablenav( $which ) {
		if($which == "top") {
			if(isset($_GET['action']) && $_GET['action'] == 'delete' ) { ?>
				<div id="message" class="updated">
					<p><strong><?php _e('Device with ID','px_gcm'); ?><i><?php echo "&nbsp;";echo $_GET['device'];echo "&nbsp;"; ?></i><?php _e('deleted','px_gcm'); ?></strong></p>
				</div> <?php
			} 
		}
		
		if($which == "bottom") {
			echo "Created by Pixelart Web and App Development";
		}
	}
   
	public function get_columns(){
        $columns = array(
			'gcm_regid'	=> __('GCM ID','px_gcm'),
			'id'		=> __('Database ID','px_gcm'),
            'os'		=> __('Device OS','px_gcm'),
            'model'		=> __('Device Model','px_gcm'),
            'created_at'=> __('Registered At','px_gcm')			
        );

        return $columns;
    }
	
	public function get_hidden_columns(){
        return array();
    }
	
	private function get_seached($item) {
	
	}
	
	public function get_sortable_columns(){
        return array('os' => array('os', true),
		             'model' => array('model', true),
					 'created_at' => array('created_at', true),
					 'id' => array('id', true),
					 'gcm_regid' => array('gcm_regid', true));
    }
	
	public function column_default($item, $column_name) {
        switch($column_name) {
			case 'gcm_regid':
            case 'id':
            case 'os':
            case 'model':
            case 'created_at':
			    if($item->$column_name != null){
                 return $item->$column_name;
			    }else {
				   return "";
			    }
        }
    }
	
	public function column_gcm_regid($item) {
		$actions = array( 
			'view'    => sprintf('<a href="?page=%s&action=%s&device=%s">%s</a>',$_REQUEST['page'],'view',$item->id, __('View','px_gcm')),
			'delete'  => sprintf('<a href="?page=%s&action=%s&device=%s">%s</a>',$_REQUEST['page'],'delete',$item->id, __('Delete','px_gcm')) );
			
		$set = sprintf('<a class="row-title" href="?page=%s&action=%s&device=%s">%s</a>',$_REQUEST['page'],'view',$item->id,$item->gcm_regid);

		return sprintf('%1$s %2$s', $set, $this->row_actions($actions) );
    }
	
	public function column_created_at($item) {
		$date = $item->created_at;
		$set_date = get_option('date_format');
		$set_time = get_option('time_format');
		$set = $set_date.' '.$set_time;
		
		$old_date_timestamp = strtotime($date);
		$new_date = date($set, $old_date_timestamp);   
		
		$txt = sprintf('%s', $new_date);

		return sprintf('%1$s', $txt);
    }
	
	public function no_items() {
		_e('No registered Devices','px_gcm');
	}
   
}

function px_display_devices() {
	$wp_list_table = new Device_List_Table();
	$wp_list_table->prepare_items();
	if(isset($_GET['action']) && $_GET['action'] == 'view') {
	}else {
		?>
		<div class="wrap">
			<h2><?php _e('All Devices','px_gcm'); ?></h2>
				<form method="get">
					<input type="hidden" name="page" value="<?php echo $_REQUEST['page']; ?>" />
					<?php $wp_list_table->search_box('search', 'search_id'); ?>
				</form>
			<?php $wp_list_table->display(); ?>
		</div>
	<?php
	}
}

?>