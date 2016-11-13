<?php
/*
This is the new API endpoint. Eventually I'd like to move all functionality here and retire:
- wc-siftscience-event.php
- wc-siftscience-score.php
 */

include_once( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . '/wp-load.php' );

require_once( dirname( __FILE__ ) . '/includes/class-wc-siftscience-logger.php' );
require_once( dirname( __FILE__ ) . '/includes/class-wc-siftscience-options.php' );
require_once( dirname( __FILE__ ) . '/includes/class-wc-siftscience-comm.php' );
require_once( dirname( __FILE__ ) . '/includes/class-wc-siftscience-events.php' );
require_once( dirname( __FILE__ ) . '/includes/class-wc-siftscience-api.php' );

$id = filter_input( INPUT_GET, 'id' );
$action = filter_input( INPUT_GET, 'action' );

$logger = new WC_SiftScience_Logger();
$options = new WC_SiftScience_Options();
$comm = new WC_SiftScience_Comm( $options, $logger );
$events = new WC_SiftScience_events( $comm, $options);
$api = new WC_SiftScience_Api( $comm, $events, $options );

try {

	$result = $api->handleRequest( $action, $id );

	if ( isset( $result[ 'status' ] ) ) {
		http_response_code( $result[ 'status' ] );
	}

	echo json_encode( $result, JSON_PRETTY_PRINT );

} catch ( Exception $error ) {
	http_response_code( 500 );
	echo json_encode( array(
		'error' => true,
		'code' => $error->getCode(),
		'message' => $error->getMessage(),
		'file' => $error->getFile(),
		'line' => $error->getLine(),
	) );

}
