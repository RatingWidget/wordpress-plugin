<?php
	/**
	 * @package     RatingWidget
	 * @copyright   Copyright (c) 2015, Rating-Widget, Inc.
	 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
	 * @since       2.6.0
	 */

	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	class RW_Account_Manager
	{
		/**
		 * @var number
		 */
		public $site_id;
		/**
		 * @var string
		 */
		public $site_public_key;
		/**
		 * @var string
		 */
		public $site_secret_key;
		/**
		 * @var number
		 */
		public $user_id;
		/**
		 * @var string
		 */
		public $user_email;

		/**
		 * @var RW_Account_Manager
		 */
		private static $INSTANCE;

		#region Singleton ---------------------------------------------

		public static function instance()
		{
			if ( ! isset( self::$INSTANCE ) )
				self::$INSTANCE = new RW_Account_Manager();

			return self::$INSTANCE;
		}

		#endregion ---------------------------------------------

		private function __construct() {
			$this->load_account();
		}

		/**
		 * Load RatingWidget account information.
		 *
		 * 1. First try to load account from DB.
		 * 2. If no account stored, check if key is configured (key.php include account details).
		 * 3. If no account in DB, but found account from external source, store it in DB.
		 *
		 * @author Vova Feldman (@svovaf)
		 * @since  2.5.7
		 */
		private function load_account() {

			$fs_options = rw_fs_options();

			$site_id    = $fs_options->get_option( WP_RW__DB_OPTION_SITE_ID );
			$public_key = $fs_options->get_option( WP_RW__DB_OPTION_SITE_PUBLIC_KEY );
			$secret_key = $fs_options->get_option( WP_RW__DB_OPTION_SITE_SECRET_KEY );
			$user_id    = $fs_options->get_option( WP_RW__DB_OPTION_OWNER_ID );
			$user_email = $fs_options->get_option( WP_RW__DB_OPTION_OWNER_EMAIL );

			$account_updated = false;

			if ( empty( $site_id ) && defined( 'WP_RW__SITE_ID' ) ) {
				$this->site_id = WP_RW__SITE_ID;
				$fs_options->set_option( WP_RW__DB_OPTION_SITE_ID, $this->site_id );
				$account_updated = true;
			} else if ( ! empty( $site_id ) ) {
				define( 'WP_RW__SITE_ID', $site_id );
				$this->site_id = $site_id;
			}

			if ( empty( $public_key ) && defined( 'WP_RW__SITE_PUBLIC_KEY' ) ) {
				$this->site_public_key = WP_RW__SITE_PUBLIC_KEY;
				$fs_options->set_option( WP_RW__DB_OPTION_SITE_PUBLIC_KEY, $this->site_public_key );
				$account_updated = true;
			} else if ( ! empty( $public_key ) ) {
				define( 'WP_RW__SITE_PUBLIC_KEY', $public_key );
				$this->site_public_key = $public_key;
			}

			if ( empty( $secret_key ) && defined( 'WP_RW__SITE_SECRET_KEY' ) ) {
				$this->site_secret_key = WP_RW__SITE_SECRET_KEY;
				$fs_options->set_option( WP_RW__DB_OPTION_SITE_SECRET_KEY, $this->site_secret_key );
				$account_updated = true;
			} else if ( ! empty( $secret_key ) ) {
				define( 'WP_RW__SITE_SECRET_KEY', $secret_key );
				$this->site_secret_key = $secret_key;
			}

			if ( ! empty( $user_id ) ) {
				define( 'WP_RW__OWNER_ID', $user_id );
				$this->user_id = $user_id;
			}

			if ( ! empty( $user_email ) ) {
				define( 'WP_RW__OWNER_EMAIL', $user_email );
				$this->user_email = $user_email;
			}

			if ( $account_updated ) {
				$fs_options->store();
			}
		}

		function reload()
		{
			$this->load_account();
		}

		function set($site_id, $site_public_key, $site_secret_key, $user_id, $user_email)
		{
			$this->update_user_id($user_id, false);
			$this->update_user_email($user_email, false);
			$this->update_site_id($site_id, false);
			$this->update_site_public_key($site_public_key, false);
			$this->update_site_secret_key($site_secret_key, false);
			rw_options()->store();
		}

		function clear()
		{
			$options = rw_options();
			$options->unset_option( WP_RW__DB_OPTION_OWNER_ID );
			$options->unset_option( WP_RW__DB_OPTION_OWNER_EMAIL );
			$options->unset_option( WP_RW__DB_OPTION_SITE_PUBLIC_KEY );
			$options->unset_option( WP_RW__DB_OPTION_SITE_ID );
			$options->unset_option( WP_RW__DB_OPTION_SITE_SECRET_KEY );
			$options->store();
		}

		function is_registered()
		{
			return !empty($this->site_public_key) && is_string($this->site_public_key);
		}

		function has_secret_key()
		{
			return !empty($this->site_secret_key) && is_string($this->site_secret_key);
		}

		function has_site_id()
		{
			return !empty($this->site_id) && is_numeric($this->site_id);
		}

		function has_owner()
		{
			return !empty($this->user_id) && is_numeric($this->user_id);
		}

		function update_site_id($id, $flush = true)
		{
			rw_options()->set_option( WP_RW__DB_OPTION_SITE_ID, $id, $flush );
			$this->site_id = $id;
		}

		function update_site_public_key($public_key, $flush = true)
		{
			rw_options()->set_option( WP_RW__DB_OPTION_SITE_PUBLIC_KEY, $public_key, $flush );
			$this->site_public_key = $public_key;
		}

		function update_site_secret_key($secret_key, $flush = true)
		{
			rw_options()->set_option( WP_RW__DB_OPTION_SITE_SECRET_KEY, $secret_key, $flush );
			$this->site_secret_key = $secret_key;
		}

		function update_user_id($id, $flush = true)
		{
			rw_options()->set_option( WP_RW__DB_OPTION_OWNER_ID, $id, $flush );
			$this->user_id = $id;
		}

		function update_user_email($email, $flush = true)
		{
			rw_options()->set_option( WP_RW__DB_OPTION_OWNER_EMAIL, $email, $flush );
			$this->user_email = $email;
		}
	}

	/**
	 * @return \RW_Account_Manager
	 */
	function rw_account()
	{
		return RW_Account_Manager::instance();
	}
