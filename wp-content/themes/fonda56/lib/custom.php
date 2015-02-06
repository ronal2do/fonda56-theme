<?php
/**
 * Custom functions
 */

 add_filter( 'json_serve_request', function( ) {

	//if(strpos($_SERVER['REQUEST_URI'], 'meta') !== false) {
		//header('Access-Control-Allow-Origin: http://localhost:8100');
	//} else {
		//header('Access-Control-Allow-Origin: http://localhost:8100');
	//}
	header('Access-Control-Allow-Origin: *');
	//header('Access-Control-Allow-Methods: GET, OPTIONS');
	//header('Access-Control-Allow-Credentials: true');
	//header('Access-Control-Allow-Methods": "GET, POST, PUT, DELETE, OPTIONS');

});


//ajax to clear the meta
add_action( 'wp_ajax_retrieve_meta', 'retrieve_meta');
add_action( 'wp_ajax_nopriv_retrieve_meta', 'retrieve_meta');


//retrieve the meta for a post
function retrieve_meta() {

	$post_id   = intval( $_GET['id'] );

	try {

		$meta_values = get_post_meta($post_id);
		//var_dump($meta_values);
		wp_send_json($meta_values);
	}

	catch (Exception $e) {
		echo false;
	}

	// chiudo l'esecuzione della chiamata AJAX, evitando di far ritornare errori da parte di WordPress
	die();
}