<?php
/*
This is the new API endpoint. Eventually I'd like to move all functionality here and retire:
- wc-siftscience-event.php
- wc-siftscience-score.php
 */

include_once( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . '/wp-load.php' );

if ( ! is_super_admin() ) {
	http_response_code( 401 );
	echo json_encode( array( 'error' => 'not allowed' ) );
	die;
}

echo json_encode( array( 'message' => 'hello world' ) );
