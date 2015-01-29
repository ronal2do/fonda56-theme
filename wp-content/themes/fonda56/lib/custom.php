<?php
/**
 * Custom functions
 */

 add_filter( 'json_serve_request', function( ) {
	header( "Access-Control-Allow-Origin: *" );
});