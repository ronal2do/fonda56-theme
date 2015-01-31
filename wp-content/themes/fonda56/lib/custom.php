<?php
/**
 * Custom functions
 */

 add_filter( 'json_serve_request', function( ) {
	header( "Access-Control-Allow-Origin: *" );
});


/* send custom GCM message */
function GCM_send_custom_message () {

  $messageData = "Ciao Gegge, questa è una notifica";
  $messageType = "AggiornamentoOBF";

  px_sendGCM($messageData, $messageType);
}