<?php

// show the stats
function px_display_stats() {
	global $wpdb;
	$px_table_name = $wpdb->prefix.'gcm_users';
	
	$color1 = array(    // dark
		1 => '#16a085', // turquoise
		2 => '#d35400', // orange
		3 => '#2980b9', // blue
		4 => '#8e44ad', // purple
		5 => '#f39c12', // yellow
		6 => '#27ae60', // green
		7 => '#2c3e50', // black
		8 => '#c0392b', // red
		9 => '#a4c400', // lime green
		10 => '#d80073', // magenta
		11 => '#81CFE0', // spray blue
		12 => '#EB974E', // jaffa
		13 => '#86E2D5', // riptide green
		14 => '#D64541', // valencia red
		15 => '#95A5A6', // cascade gray
	);
	
	$color2 = array(    // light
		1 => '#1abc9c', // turquoise
		2 => '#e67e22', // orange
		3 => '#3498db', // blue
		4 => '#9b59b6', // purple
		5 => '#f1c40f', // yellow
		6 => '#2ecc71', // green
		7 => '#34495e', // black
		8 => '#e74c3c', // red
		9 => '#BADE02', // lime green
		10 => '#E8057E', // magenta
		11 => '#8FE5F7', // spray blue
		12 => '#FAAA64', // jaffa
		13 => '#9DF5E8', // riptide green
		14 => '#ED504C', // valencia red
		15 => '#A9B9BA', // cascade gray
	);
	
	/*---------------------- Total Num DATA ----------------------------------------*/
	$all = $wpdb->get_var( "SELECT COUNT(*) FROM $px_table_name" );
	if($all != false) {
		$num_rows = $all;
	}else {
		$num_rows = 0;
	}
	
	/*---------------------- OS DATA ----------------------------------------*/
	$sqlO = "SELECT os, COUNT(*) FROM $px_table_name GROUP BY os";
	$resO = $wpdb->get_results($sqlO, ARRAY_A);
	
	/*---------------------- MODEL DATA ----------------------------------------*/
	$sqlM = "SELECT model, COUNT(*) FROM $px_table_name GROUP BY model";
	$resM = $wpdb->get_results($sqlM, ARRAY_A);
	
	?>
	<style>
		.px_txt_big {
			font-size: 77px;
		}
	</style>
	<div class="wrap">
		<h2><?php _e('Stats','px_gcm'); ?></h2>
		<div id="poststuff">		
			<div id="post-body" class="metabox-holder columns-1"> 
			<!-- Content starts here -->
				<div id="post-body-content">
					<div class="postbox">
						<div class="inside">
						
							<p><b><?php _e('Total number of sent messages','px_gcm'); ?>:</b><br>
							<b id="totalChart" class="px_txt_big"> </b></p>
							
							<table width="100%">
								<td width="33%" >
									<b><?php _e('Device Models','px_gcm'); ?>:</b><br>
									<canvas id="modelChart" width="250" height="250"></canvas>
								</td>
								<td width="33%">							
									<b><?php _e('Device OS','px_gcm'); ?>:</b><br>
									<canvas id="osChart" width="250" height="250"></canvas>
								</td>
								<td width="33%">							
									<b><?php _e('Messages success/fail','px_gcm'); ?>:</b><br>
									<canvas id="sfChart" width="250" height="250"></canvas>
								</td>
							</table>
						
						</div> 
					</div>
				</div>
			</div> 
			<br class="clear">				
		</div>
	</div>
	<script type="text/javascript">
		var total = document.getElementById("totalChart");
		var model = document.getElementById("modelChart").getContext("2d");
		var os = document.getElementById("osChart").getContext("2d");
		var sf = document.getElementById("sfChart").getContext("2d");
		
		// Charts
		var options = { animationEasing: "easeOutQuart" };
		
		var oData = [
			<?php
			$i = 0;
			foreach($resO as $row) {
				$i++;
				?>
				{
					value: <?php echo $row['COUNT(*)']; ?>,
					color: "<?php echo $color1[$i]; ?>",
					highlight: "<?php echo $color2[$i]; ?>",
					label: "<?php echo $row['os']; ?>"
				},
			<?php
			}			
		?>];
		
		var mData = [
			<?php
			$i = 0;
			foreach($resM as $row) {
				$i++;
				?>
				{
					value: <?php echo $row['COUNT(*)']; ?>,
					color: "<?php echo $color1[$i]; ?>",
					highlight: "<?php echo $color2[$i]; ?>",
					label: "<?php echo $row['model']; ?>"
				},
			<?php
			}			
		?>];
		
		var sfData = [
			{
				value: <?php echo get_option('px_gcm_suc_msg',0); ?>,
				color: "#27ae60",
				highlight: "#2ecc71",
				label: "<?php _e('sent successfully','px_gcm'); ?>"
			},
			{
				value: <?php echo get_option('px_gcm_fail_msg',0); ?>,
				color: "#c0392b",
				highlight: "#e74c3c",
				label: "<?php _e('sent unsuccessfully','px_gcm'); ?>"
			},
		];
		
		var osChart = new Chart(os).Pie(oData, options);
		var mChart = new Chart(model).Pie(mData, options);
		var sfChart = new Chart(sf).Pie(sfData, options);

		// Count Up
		var numAnim = new countUp(total, 0, <?php echo get_option('px_gcm_total_msg', 0); ?>);
		numAnim.start();
		
	</script>
	<?php
}
?>