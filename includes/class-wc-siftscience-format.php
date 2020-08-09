<?php

/*
 * Author: Nabeel Sulieman
 * Description: This class is used to format various data for the Sift API.
 * License: GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WC_SiftScience_Format' ) ) :

	require_once( 'class-wc-siftscience-options.php' );
	require_once( 'class-wc-siftscience-logger.php' );
	require_once( 'class-wc-siftscience-format-account.php' );
	require_once( 'class-wc-siftscience-format-cart.php' );
	require_once( 'class-wc-siftscience-format-items.php' );
	require_once( 'class-wc-siftscience-format-login.php' );
	require_once( 'class-wc-siftscience-format-order.php' );
	require_once( 'class-wc-siftscience-format-transaction.php' );

	class WC_SiftScience_Format {
		public $_items;
		public $_login;
		public $_account;
		public $_order;
		public $_cart;
		public $_transactions;

		public function __construct( WC_SiftScience_Options $options, WC_SiftScience_Logger $logger ) {
			$this->_transactions = new WC_SiftScience_Format_Transaction( $options );
			$this->_items = new WC_SiftScience_Format_Items( $options );
			$this->_login = new WC_SiftScience_Format_Login( $options );
			$this->_account = new WC_SiftScience_Format_Account( $options );
			$this->_order = new WC_SiftScience_Format_Order( $this->_items, $this->_transactions, $options, $logger );
			$this->_cart = new WC_SiftScience_Format_Cart( $options );
		}
	}

endif;