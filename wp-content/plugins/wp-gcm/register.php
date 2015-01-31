<?php

function px_gcm_register() {
 
	if (isset($_GET["regId"])) {
		global $wpdb;   
		$gcm_regid = $_GET["regId"];
		$os = os();
		$model = model();
		$time = date("Y-m-d H:i:s");   
		$px_table_name = $wpdb->prefix.'gcm_users';
		$sql = "SELECT gcm_regid FROM $px_table_name WHERE gcm_regid='$gcm_regid'";
		$result = $wpdb->get_results($sql);

		if(!$result) {
			$sql = "INSERT INTO $px_table_name (gcm_regid, os, model, created_at) VALUES ('$gcm_regid', '$os', '$model', '$time')";
			$q = $wpdb->query($sql);
			echo __('You are now registered','px_gcm');
		}else {
		  echo __('You are already registered','px_gcm');
		}
	}
}

function os() {
	if(isset($_GET["os"])) {
		return $os = $_GET["os"];
	}else {
		return $os = 'not set';
	}
}

function model() {
	if(isset($_GET["model"])) {
		return $model = $_GET["model"];
	}else {
		return $model = 'not set';
	}
}
?>