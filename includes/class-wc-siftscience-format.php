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
		public $items;
		public $login;
		public $account;
		public $order;
		public $cart;
		public $transactions;

		public function __construct( WC_SiftScience_Options $options, WC_SiftScience_Logger $logger ) {
			$this->transactions = new WC_SiftScience_Format_Transaction( $options );
			$this->items = new WC_SiftScience_Format_Items( $options );
			$this->login = new WC_SiftScience_Format_Login( $options );
			$this->account = new WC_SiftScience_Format_Account( $options );
			$this->order = new WC_SiftScience_Format_Order( $this->items, $this->transactions, $options, $logger );
			$this->cart = new WC_SiftScience_Format_Cart( $options );
		}
	}

endif;