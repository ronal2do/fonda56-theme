<?php

class px_gcm_device_view {

	// Init of the class
	public function startup($id) {
		global $wpdb;
		$deviceId = $_GET['device'];
		$total_msg = get_option('px_gcm_total_msg',0);
		$px_table_name = $wpdb->prefix.'gcm_users';
		$query = "SELECT * FROM $px_table_name WHERE id = $id";
		$device = $wpdb->get_row($query);
		$num = $this->num($device->send_msg);
		$this->px_gcm_display_view(
			$device->id,
			$device->gcm_regid,
			$device->os,
			$device->model,
			$device->created_at,
			$num,
			$total_msg);
	}
	
	function num($num) {
		if(empty($num)) {
			return $num = 0;
		}else {
			return $num;
		}
	}

	// render the view with the data
	public function px_gcm_display_view($id, $regId, $os, $model, $date, $num, $total) {
		$set_date = get_option('date_format');
		$set_time = get_option('time_format');
		$set = $set_date.' '.$set_time;
		
		$old_date_timestamp = strtotime($date);
		$new_date = date($set, $old_date_timestamp);
		
		?>
		<style type="text/css">
		.table-content {
			font-style: italic;
			background-color: #CCC;
		}
		</style>

		<div class="wrap">
			<h2 class=""><?php _e('Single Device','px_gcm'); ?></h2>
			<div id="poststuff">		
				<div id="post-body" class="metabox-holder columns-1"> 
				<!-- Content starts here -->
					<div id="post-body-content">
						<div class="postbox">
							<h3><?php _e('Information','px_gcm'); ?></h3>
							<div class="inside">
								<table width="100%" border="0">
									<td>
										<table width="77%" border="0">
											<tr>
												<td><?php _e('Database ID','px_gcm'); ?></td>
												<td><input type="text" name="db_id" value="<?php echo $id; ?>" readonly /></td>
											</tr>
											<tr>
												<td><?php _e('GCM ID','px_gcm'); ?></td>
												<td><textarea type="text" name="gcm_id" cols="77" rows="5" readonly ><?php echo $regId; ?></textarea></td>
											</tr>
											<tr>
												<td><?php _e('Device OS','px_gcm'); ?></td>
												<td><input type="text" name="os" value="<?php echo $os; ?>" readonly /></td>
											</tr>
											<tr>
												<td><?php _e('Device Model','px_gcm'); ?></td>
												<td><input type="text" name="model" value="<?php echo $model; ?>" readonly /></td>
											</tr>
											<tr>
												<td><?php _e('Registration Date','px_gcm'); ?></td>
												<td><input type="text" name="date" value="<?php echo $new_date; ?>" readonly /></td>
											</tr>
										</table>
									</td>
									<td>
										<canvas id="msgChart" width="250" height="250"></canvas>
									</td>
								</table>
							</div> 
						</div>
					</div>
					
					<div id="postbox-container-1" class="postbox-container">
						<div class="postbox">
								<h3><?php _e('Write a Message to this Device','px_gcm'); ?></h3>
								<div class="inside">
									<form method="post" action="#">
										<p><?php _e('Enter here your message','px_gcm'); ?></p>
										<textarea id="msg" name="msg" type="text" cols="50" rows="5" ></textarea>
										<p><?php _e('*Please don\'t use HTML','px_gcm'); ?></p>
										<?php submit_button(__('Send','px_gcm')); ?>
									</form>
								</div> 
						</div>
					</div>
				
					
				</div> 
				<br class="clear">				
			</div>
		</div>
		<script type="text/javascript">
			var ctx = document.getElementById("msgChart").getContext("2d");
			var options = { animationEasing: "easeOutQuart" };
			var data = [
				{
					value: <?php echo $num ?>,
					color:"#2980b9",
					highlight: "#3498db",
					label: "<?php _e('Messages sent to this device','px_gcm'); ?>"
				},
				{
					value: <?php echo $total ?>,
					color: "#bdc3c7",
					highlight: "#ecf0f1",
					label: "<?php _e('Total Messages sent','px_gcm'); ?>"
				}];
			var msgChart = new Chart(ctx).Doughnut(data, options);			
		</script>
		<?php
		
		if(isset($_POST['msg'])) {
			$message = $_POST["msg"];
			$arr = array();
			array_push($arr, $regId);
			print_r(px_sendGCM($message, "message", $arr));
		}
	}
}
?>