<?php
/**
 * This class is used to format various data for the Sift API.
 *
 * @author Nabeel Sulieman, Rami Jamleh
 * @license GPL2
 * @package sift-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WC_SiftScience_Format' ) ) :

	require_once 'class-wc-siftscience-options.php';
	require_once 'class-wc-siftscience-logger.php';
	require_once 'class-wc-siftscience-format-account.php';
	require_once 'class-wc-siftscience-format-cart.php';
	require_once 'class-wc-siftscience-format-items.php';
	require_once 'class-wc-siftscience-format-login.php';
	require_once 'class-wc-siftscience-format-order.php';
	require_once 'class-wc-siftscience-format-transaction.php';

	/**
	 * Class WC_SiftScience_Format
	 */
	class WC_SiftScience_Format {
		/**
		 * Items formatter
		 *
		 * @var WC_SiftScience_Format_Items
		 */
		public $items;

		/**
		 * Login formatter
		 *
		 * @var WC_SiftScience_Format_Login
		 */
		public $login;

		/**
		 * Account data formatter
		 *
		 * @var WC_SiftScience_Format_Account
		 */
		public $account;

		/**
		 * Order data formatter
		 *
		 * @var WC_SiftScience_Format_Order
		 */
		public $order;

		/**
		 * Cart data formatter
		 *
		 * @var WC_SiftScience_Format_Cart
		 */
		public $cart;

		/**
		 * Transaction data formatter
		 *
		 * @var WC_SiftScience_Format_Transaction
		 */
		public $transactions;

		/**
		 * WC_SiftScience_Format constructor.
		 *
		 * @param WC_SiftScience_Format_Transaction $transactions Transactions formatter.
		 * @param WC_SiftScience_Format_Items       $items Items object formatter.
		 * @param WC_SiftScience_Format_Login       $login Login event formatter.
		 * @param WC_SiftScience_Format_Account     $account Account data formatter.
		 * @param WC_SiftScience_Format_Order       $order Order formatter.
		 * @param WC_SiftScience_Format_Cart        $cart Cart data formatter.
		 */
		public function __construct(
				WC_SiftScience_Format_Transaction $transactions,
				WC_SiftScience_Format_Items $items,
				WC_SiftScience_Format_Login $login,
				WC_SiftScience_Format_Account $account,
				WC_SiftScience_Format_Order $order,
				WC_SiftScience_Format_Cart $cart ) {
			$this->transactions = $transactions;
			$this->items        = $items;
			$this->login        = $login;
			$this->account      = $account;
			$this->order        = $order;
			$this->cart         = $cart;
		}
	}

endif;
