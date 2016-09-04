<?php
/*
This is the new API endpoint. Eventually I'd like to move all functionality here and retire:
- wc-siftscience-event.php
- wc-siftscience-score.php
 */

include_once( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . '/wp-load.php' );

require_once( dirname( __FILE__ ) . '/includes/class-wc-siftscience-options.php' );
require_once( dirname( __FILE__ ) . '/includes/class-wc-siftscience-comm.php' );
require_once( dirname( __FILE__ ) . '/includes/class-wc-siftscience-backfill.php' );
require_once( dirname( __FILE__ ) . '/includes/class-wc-siftscience-api.php' );

$id = filter_input( INPUT_GET, 'id' );
$action = filter_input( INPUT_GET, 'action' );

$options = new WC_SiftScience_Options();
$comm = new WC_SiftScience_Comm( $options );
$backfill = new WC_SiftScience_Backfill( $options, $comm );

$api = new WC_SiftScience_Api( $comm, $backfill, $options );
$result = $api->handleRequest( $action, $id );

if ( isset( $result[ 'status' ] ) ) {
	http_response_code( $result[ 'status' ] );
}

echo json_encode( $result );
