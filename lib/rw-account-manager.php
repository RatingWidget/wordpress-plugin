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
		 * @var RW_Options
		 */
		private $_options;

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

		#endregion Singleton ---------------------------------------------

		private function __construct() {
			$this->_options = rw_fs_options();

			$this->optional_migration();

			$this->load_account();
		}

		#region Data Migration ------------------------------------------------------------------

		/**
		 * Optional data migration based on the database stored options.
		 *
		 * @author Vova Feldman (@svovaf)
		 * @since  2.6.3
		 */
		private function optional_migration() {
			$site_id = $this->_options->get_option( WP_RW__DB_OPTION_SITE_ID );

			if ( empty( $site_id ) ) {
				$fs_options = rw_fs()->get_options_manager( WP_FS__ACCOUNTS_OPTION_NAME, true, false );
				if ( false === $fs_options ) {
					// No FS options are stored, probably new plugin install.
				} else {
					// Check if RW account stored in FS accounts object (was set in one of the first FS semi-integrated versions.
					$sites = $fs_options->get_option( 'sites' );

					if ( ! empty( $sites ) && is_array( $sites ) && 0 < count( $sites ) ) {
						foreach ( $sites as $basename => $site ) {
							if ( '/rating-widget.php' !== substr( $basename, - strlen( '/rating-widget.php' ) ) ) {
								continue;
							}

							if ( is_object( $site ) &&
							     ! empty( $site->secret_key ) &&
							     'sk_' !== substr( $site->secret_key, 0, 3 ) &&
							     ! empty( $site->public_key ) &&
							     'pk_' !== substr( $site->public_key, 0, 3 )
							) {
								$this->migrate_from_fs_options( $fs_options, $basename );
							}
						}
					}
				}
			}
		}

		/**
		 * Migration from account options stored in FS accounts object back to RW options object.
		 *
		 * @author Vova Feldman (@svovaf)
		 * @since  2.6.3
		 *
		 * @param FS_Option_Manager $fs_options
		 * @param string            $basename
		 */
		private function migrate_from_fs_options($fs_options, $basename){
			// Load site information.
			$sites = $fs_options->get_option('sites');
			$site = $sites[$basename];

			// Load user information.
			$users = $fs_options->get_option('users');
			$user = $users[$site->user_id];

			// Update account information.
			$this->set(
				$site->id,
				$site->public_key,
				$site->secret_key,
				$user->id,
				$user->email
			);

			// Remove RW account from FS object.
			unset($sites[$basename]);
			unset($users[$site->user_id]);

			$fs_options->set_option('sites', $sites);
			$fs_options->set_option('users', $users);
			$fs_options->store();
		}

		#endregion Data Migration ------------------------------------------------------------------

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

			$site_id    = $this->_options->get_option( WP_RW__DB_OPTION_SITE_ID );
			$public_key = $this->_options->get_option( WP_RW__DB_OPTION_SITE_PUBLIC_KEY );
			$secret_key = $this->_options->get_option( WP_RW__DB_OPTION_SITE_SECRET_KEY );
			$user_id    = $this->_options->get_option( WP_RW__DB_OPTION_OWNER_ID );
			$user_email = $this->_options->get_option( WP_RW__DB_OPTION_OWNER_EMAIL );

			$account_updated = false;

			if ( empty( $site_id ) && defined( 'WP_RW__SITE_ID' ) ) {
				$this->site_id = WP_RW__SITE_ID;
				$this->_options->set_option( WP_RW__DB_OPTION_SITE_ID, $this->site_id );
				$account_updated = true;
			} else if ( ! empty( $site_id ) ) {
				define( 'WP_RW__SITE_ID', $site_id );
				$this->site_id = $site_id;
			}

			if ( empty( $public_key ) && defined( 'WP_RW__SITE_PUBLIC_KEY' ) ) {
				$this->site_public_key = WP_RW__SITE_PUBLIC_KEY;
				$this->_options->set_option( WP_RW__DB_OPTION_SITE_PUBLIC_KEY, $this->site_public_key );
				$account_updated = true;
			} else if ( ! empty( $public_key ) ) {
				define( 'WP_RW__SITE_PUBLIC_KEY', $public_key );
				$this->site_public_key = $public_key;
			}

			if ( empty( $secret_key ) && defined( 'WP_RW__SITE_SECRET_KEY' ) ) {
				$this->site_secret_key = WP_RW__SITE_SECRET_KEY;
				$this->_options->set_option( WP_RW__DB_OPTION_SITE_SECRET_KEY, $this->site_secret_key );
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
				$this->_options->store();
			}
		}

		function reload()
		{
			$this->load_account();
		}

		function set($site_id, $site_public_key, $site_secret_key, $user_id, $user_email) {
			$this->set_site( $site_id, $site_public_key, $site_secret_key, false );
			$this->set_user( $user_id, $user_email );

			$this->_options->store();
		}

		function set_site($site_id, $site_public_key, $site_secret_key, $flush = true) {
			$this->update_site_id( $site_id, false );
			$this->update_site_public_key( $site_public_key, false );
			$this->update_site_secret_key( $site_secret_key, false );

			if ( $flush ) {
				$this->_options->store();
			}
		}

		function set_user($user_id, $user_email, $flush = true)
		{
			$this->update_user_id($user_id, false);
			$this->update_user_email($user_email, false);

			if ( $flush ) {
				$this->_options->store();
			}
		}

		function clear()
		{
			$this->_options->unset_option( WP_RW__DB_OPTION_OWNER_ID );
			$this->_options->unset_option( WP_RW__DB_OPTION_OWNER_EMAIL );
			$this->_options->unset_option( WP_RW__DB_OPTION_SITE_PUBLIC_KEY );
			$this->_options->unset_option( WP_RW__DB_OPTION_SITE_ID );
			$this->_options->unset_option( WP_RW__DB_OPTION_SITE_SECRET_KEY );
			$this->_options->store();
		}

		function is_registered()
		{
			return !empty($this->site_public_key) && is_string($this->site_public_key);
		}

		function has_secret_key()
		{
			return !empty($this->site_secret_key) && is_string($this->site_secret_key);
		}

		function has_public_key()
		{
			return !empty($this->site_public_key) && is_string($this->site_public_key);
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
			$this->_options->set_option( WP_RW__DB_OPTION_SITE_ID, $id, $flush );
			$this->site_id = $id;
		}

		function update_site_public_key($public_key, $flush = true)
		{
			$this->_options->set_option( WP_RW__DB_OPTION_SITE_PUBLIC_KEY, $public_key, $flush );
			$this->site_public_key = $public_key;
		}

		function update_site_secret_key($secret_key, $flush = true)
		{
			$this->_options->set_option( WP_RW__DB_OPTION_SITE_SECRET_KEY, $secret_key, $flush );
			$this->site_secret_key = $secret_key;
		}

		function update_user_id($id, $flush = true)
		{
			$this->_options->set_option( WP_RW__DB_OPTION_OWNER_ID, $id, $flush );
			$this->user_id = $id;
		}

		function update_user_email($email, $flush = true)
		{
			$this->_options->set_option( WP_RW__DB_OPTION_OWNER_EMAIL, $email, $flush );
			$this->user_email = $email;
		}

		function save()
		{
			$this->_options->store();
		}
	}

	/**
	 * @return \RW_Account_Manager
	 */
	function rw_account()
	{
		return RW_Account_Manager::instance();
	}
