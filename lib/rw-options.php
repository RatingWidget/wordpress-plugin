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

	/**
	 * This class responsible for accessing all RatingWidget database options.
	 *
	 * It's a simple wrapper to FS_Option_Manager instance with extract methods for data migration.
	 */
	class RW_Options {
		/**
		 * @var \FS_Option_Manager
		 */
		private $_options;

		/**
		 * @var \RW_Options
		 */
		private static $_instance;

		/**
		 * @return \RW_Options
		 */
		static function instance() {
			if ( ! isset( self::$_instance ) ) {
				self::$_instance = new RW_Options();
			}

			return self::$_instance;
		}

		private function __construct( $load = false ) {
			$this->optional_migration();

			$this->_options = rw_fs()->get_options_manager( WP_RW__OPTIONS, true, false );
		}

		#region Data Migration ------------------------------------------------------------------

		/**
		 * Optional data migration based on the database stored options.
		 *
		 * @author Vova Feldman (@svovaf)
		 * @since  2.6.0
		 */
		private function optional_migration() {
			$rw_options = get_option( WP_RW__OPTIONS );

			if ( false === $rw_options ) {
				$public_key = get_option( WP_RW__DB_OPTION_SITE_PUBLIC_KEY );

				if ( false !== $public_key ) {
					// Very old plugin versions, when each account property was stored in separated option.
					$this->migrate_from_separated_to_json();
					$this->migrate_from_json_to_serialized();
				} else {
					// No options are stored, probably new plugin install.
				}
			} else if ( is_string( $rw_options ) ) {
				$rw_options = json_decode( $rw_options );

				if ( is_string( $rw_options ) ) {
					// Don't remember why, but sometimes double decoding works.
					$rw_options = json_decode( $rw_options );
				}

				if ( is_null( $rw_options ) ) {
					// Ignore account option record.
				} else {
					// Old plugin versions, when account details serialized into one JSON option record.
					$this->migrate_from_json_to_serialized();
				}
			} else // Object or Array
			{
				// New versions, where account details are PHP serialized in one option record.
			}
		}

		/**
		 * Migration from separated option records into one option with all the settings in JSON format.
		 *
		 * @author Vova Feldman (@svovaf)
		 * @since  2.6.0
		 */
		private function migrate_from_separated_to_json() {
			$rw_options = new stdClass();

			$option_names = array(
				// Account
				WP_RW__DB_OPTION_SITE_ID,
				WP_RW__DB_OPTION_SITE_PUBLIC_KEY,
				WP_RW__DB_OPTION_SITE_SECRET_KEY,
				WP_RW__DB_OPTION_SITE_PLAN,
				WP_RW__DB_OPTION_SITE_PLAN_UPDATE,
				WP_RW__DB_OPTION_OWNER_ID,
				WP_RW__DB_OPTION_OWNER_EMAIL,
				WP_RW__DB_OPTION_TRACKING,
				WP_RW__DB_OPTION_WP_RATE_NOTICE_MIN_VOTES_TRIGGER,
				WP_RW__DB_OPTION_STATS_UPDATED,
				WP_RW__DB_OPTION_RICH_SNIPPETS_SETTINGS,
				WP_RW__SHOW_ON_EXCERPT,
				WP_RW__SHOW_ON_ARCHIVE,
				WP_RW__SHOW_ON_CATEGORY,
				WP_RW__SHOW_ON_SEARCH,
				WP_RW__VISIBILITY_SETTINGS,
				WP_RW__READONLY_SETTINGS,
				WP_RW__AVAILABILITY_SETTINGS,
				WP_RW__CATEGORIES_AVAILABILITY_SETTINGS,
				WP_RW__CUSTOM_SETTINGS_ENABLED,
				WP_RW__CUSTOM_SETTINGS,
				WP_RW__MULTIRATING_SETTINGS,
				WP_RW__IS_ACCUMULATED_USER_RATING,
				// Posts
				WP_RW__BLOG_POSTS_ALIGN,
				WP_RW__BLOG_POSTS_OPTIONS,
				// Comments
				WP_RW__COMMENTS_ALIGN,
				WP_RW__COMMENTS_OPTIONS,
				// Pages
				WP_RW__PAGES_ALIGN,
				WP_RW__PAGES_OPTIONS,
				// Front page posts
				WP_RW__FRONT_POSTS_ALIGN,
				WP_RW__FRONT_POSTS_OPTIONS,
				// BuddyPress
				WP_RW__ACTIVITY_BLOG_POSTS_ALIGN,
				WP_RW__ACTIVITY_BLOG_POSTS_OPTIONS,
				WP_RW__ACTIVITY_BLOG_COMMENTS_ALIGN,
				WP_RW__ACTIVITY_BLOG_COMMENTS_OPTIONS,
				WP_RW__ACTIVITY_UPDATES_ALIGN,
				WP_RW__ACTIVITY_UPDATES_OPTIONS,
				WP_RW__ACTIVITY_COMMENTS_ALIGN,
				WP_RW__ACTIVITY_COMMENTS_OPTIONS,
				// bbPress
				WP_RW__FORUM_POSTS_ALIGN,
				WP_RW__FORUM_POSTS_OPTIONS,
				WP_RW__ACTIVITY_FORUM_POSTS_ALIGN,
				WP_RW__ACTIVITY_FORUM_POSTS_OPTIONS,
				// User
				WP_RW__USERS_ALIGN,
				WP_RW__USERS_OPTIONS,
				// User accumulated ratings
				WP_RW__USERS_POSTS_ALIGN,
				WP_RW__USERS_POSTS_OPTIONS,
				WP_RW__USERS_PAGES_ALIGN,
				WP_RW__USERS_PAGES_OPTIONS,
				WP_RW__USERS_COMMENTS_ALIGN,
				WP_RW__USERS_COMMENTS_OPTIONS,
				WP_RW__USERS_ACTIVITY_UPDATES_ALIGN,
				WP_RW__USERS_ACTIVITY_UPDATES_OPTIONS,
				WP_RW__USERS_ACTIVITY_COMMENTS_ALIGN,
				WP_RW__USERS_ACTIVITY_COMMENTS_OPTIONS,
				WP_RW__USERS_FORUM_POSTS_ALIGN,
				WP_RW__USERS_FORUM_POSTS_OPTIONS,
			);

			foreach ( $option_names as $option ) {
				$value = get_option( $option, null );
				if ( ! is_null( $value ) ) {
					$rw_options->{$option} = $value;
				}
			}

			update_option( WP_RW__OPTIONS, json_encode( $rw_options ) );

			foreach ( $option_names as $option ) {
				// Clear old options from DB.
				delete_option( $option );
			}
		}

		/**
		 * Migration from one option with all settings in JSON format, into PHP serialized format.
		 *
		 * @author Vova Feldman (@svovaf)
		 * @since  2.6.0
		 */
		private function migrate_from_json_to_serialized() {
			$rw_options = get_option( WP_RW__OPTIONS );

			$rw_options = json_decode( $rw_options );

			if ( is_string( $rw_options ) ) {
				// Don't remember why, but sometimes double decoding works.
				$rw_options = json_decode( $rw_options );
			}

			if ( ! is_null( $rw_options ) ) {
				if ( ! is_array( $rw_options ) ) {
					$rw_options = (array) $rw_options;
				}

				update_option( WP_RW__OPTIONS, $rw_options );
			} else {
				// Invalid JSON object.
			}
		}

		#endregion Data Migration ------------------------------------------------------------------

		function is_loaded() {
			return $this->_options->is_loaded();
		}

		function is_empty() {
			return $this->_options->is_empty();
		}

		function clear( $flush = false ) {
			$this->_options->clear( $flush );
		}

		function delete()
		{
			$this->_options->delete();
		}

		function has_option( $option ) {
			return $this->_options->has_option( $option );
		}

		function get_option( $option, $default = null ) {
			return $this->_options->get_option( $option, $default );
		}

		function set_option( $option, $value, $flush = false ) {
			$this->_options->set_option( $option, $value, $flush );
		}

		function unset_option( $option, $flush = false ) {
			$this->_options->unset_option( $option, $flush );
		}

		function store() {
			$this->_options->store();
		}
	}

	/**
	 * @return RW_Options
	 */
	function rw_fs_options()
	{
		return RW_Options::instance();
	}