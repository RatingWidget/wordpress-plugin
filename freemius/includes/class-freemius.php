<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	final class Freemius {
		/**
		 * @var string
		 */
		public $version = '1.0.1';

		private $_id;
		private $_public_key;
		private $_slug;
		private $_logger;
		private $_plugin_basename;
		private $_plugin_data;

		private static $_instances = array();
		/**
		 * @var FS_User
		 */
		private static $_user;
		/**
		 * @var FS_Site
		 */
		private static $_site;
		/**
		 * @var FS_Logger
		 */
		private static $_static_logger;

		/**
		 * @var FS_Option_Manager
		 */
		private static $_static_options;

		// Verification is done by slug + public.

		private function __construct( $slug ) {
			$this->_slug   = $slug;
			$this->_logger = FS_Logger::get_logger( WP_FS__SLUG . '_' . $slug, WP_FS__DEBUG_SDK, WP_FS__ECHO_DEBUG_SDK );
		}

		static function instance( $slug ) {
			$slug = strtolower( $slug );

			if ( ! isset( self::$_instances[ $slug ] ) ) {
				if ( 0 === count( self::$_instances ) ) {
					self::load_static();
				}

				self::$_instances[ $slug ] = new Freemius( $slug );
			}

			return self::$_instances[ $slug ];
		}

		private static function load_static() {
			self::$_static_logger = FS_Logger::get_logger( WP_FS__SLUG, WP_FS__DEBUG_SDK, WP_FS__ECHO_DEBUG_SDK );

			self::$_static_logger->entrance();

			self::$_static_options = FS_Option_Manager::get_manager( WP_FS__ACCOUNT_OPTION_NAME, true );

			self::load_account();
		}

		private static $_account_loaded = false;

		/***
		 * Load account information (user + site).
		 */
		private static function load_account() {
			self::$_static_logger->entrance();

			// Make sure account is loaded only once.
			if ( self::$_account_loaded ) {
				self::$_static_logger->log( 'Account already loaded' );

				return;
			}

			self::$_account_loaded = true;

			/*<--{obfuscate}*/
			self::$_site = self::$_static_options->get_option( 'site' );
			self::$_user = self::$_static_options->get_option( 'user' );

			if ( ! is_object( self::$_user ) && function_exists( 'fs_load_external_account' ) ) {
				self::$_static_logger->info( 'Trying to load account from external source with fs_load_external_account' );

				$account     = fs_load_external_account();
				self::$_site = $account['site'];
				self::$_user = $account['user'];

				if ( is_object( self::$_user ) ) {
					self::$_static_logger->info( 'Account loaded: user_id = ' . self::$_user->id . '; site_id = ' . self::$_site->id . ';' );

					// Store new account information after loading from external source.
//					self::$_static_options->store();
				}
			}
			/*{obfuscate}-->*/
		}

		function init( $id, $public_key, $options ) {
			$this->_logger->entrance();

			if ( ! is_plugin_active( 'rating-widget/rating-widget.php' ) && file_exists(WP_FS__DIR_INCLUDES . '/class-dummy-rw-plugin.php') ) {
				require_once WP_FS__DIR_INCLUDES . '/class-dummy-rw-plugin.php';
			}

			$bt                     = debug_backtrace();
			$this->_plugin_basename = plugin_basename( $bt[1]['file'] );
			$this->_public_key      = $public_key;
			$this->_id              = $id;
			$this->_plugin_data     = get_plugin_data( $bt[1]['file'] );

			$this->_logger->info( 'plugin_basename = ' . $this->_plugin_basename );

			if (!$this->is_registered())
				return;

			if ( is_admin() ) {
				if ( isset( $options['menu'] ) ) // Plugin has menu.
				{
					$this->set_has_menu();
				}

				add_action( 'admin_init', array( &$this, '_add_upgrade_action_link' ) );
				add_action( 'admin_menu', array( &$this, '_add_dashboard_menu' ), WP_FS__LOWEST_PRIORITY );
				add_action( 'init', array( &$this, '_redirect_on_clicked_menu_link' ), WP_FS__LOWEST_PRIORITY );
				add_action( 'fs_after_license_loaded', array( $this, 'add_default_submenu_items' ) );
			}
		}

		/* Account
		------------------------------------------------------------------------------------------------------------------*/
		function is_registered() {
			return is_object( self::$_user );
		}

		/**
		 * @return FS_User
		 */
		function get_user() {
			return self::$_user;
		}

		/**
		 * @return FS_Site
		 */
		function get_site() {
			return self::$_site;
		}

		function get_plan() {

		}

		/* Licensing
		------------------------------------------------------------------------------------------------------------------*/
		function is_trial()/*{obfuscate-method}*/ {
			$this->_logger->entrance();

			/*<--{obfuscate}*/

			return ratingwidget()->RW_IsTrial();
			/*{obfuscate}-->*/
		}

		function is_paying()/*{obfuscate-method}*/ {
			$this->_logger->entrance();

			/*<--{obfuscate}*/

			return ratingwidget()->RW_IsPaying();
			/*{obfuscate}-->*/
		}

		function is_plan( $plan, $exact = false )/*{obfuscate-method}*/ {
			$this->_logger->entrance();

			/*<--{obfuscate}*/

			return false;
			/*{obfuscate}-->*/
		}

		function is_ssl()/*{obfuscate-method}*/ {
			/*<--{obfuscate}*/
			return
				// Checks if CloudFlare's HTTPS (Flexible SSL support)
				( isset( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && 'https' === strtolower( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) ) ||
				// Check if HTTPS request.
				( isset( $_SERVER['HTTPS'] ) && 'on' == $_SERVER['HTTPS'] ) ||
				( isset( $_SERVER['SERVER_PORT'] ) && 443 == $_SERVER['SERVER_PORT'] );
			/*{obfuscate}-->*/
		}

		function is_ssl_and_plan( $plan, $exact = false )/*{obfuscate-method}*/ {
			return ( $this->is_ssl() && $this->is_plan( $plan, $exact ) );
		}

		function get_upgrade_url( $plan = WP_FS__PLAN_DEFAULT_PAID, $period = WP_FS__PERIOD_ANNUALLY ) {
			$this->_logger->entrance();

			return ratingwidget()->GetUpgradeUrl( false, $period, $plan );
		}

		function get_pricing_url( $period = WP_FS__PERIOD_ANNUALLY ) {
			$this->_logger->entrance();

			return '';
		}

		function get_account_url() {
			return add_query_arg( array( 'page' => $this->_slug . '-account' ), admin_url( 'admin.php', 'admin' ) );
		}

		/* Logger
		------------------------------------------------------------------------------------------------------------------*/
		/**
		 * @param string $id
		 * @param bool $prefix_slug
		 *
		 * @return FS_Logger
		 */
		function get_logger( $id = '', $prefix_slug = true ) {
			return FS_Logger::get_logger( ( $prefix_slug ? $this->_slug : '' ) . ( ( ! $prefix_slug || empty( $id ) ) ? '' : '_' ) . $id );
		}

		/**
		 * @param $id
		 * @param bool $load_options
		 * @param bool $prefix_slug
		 *
		 * @return FS_Option_Manager
		 */
		function get_options_manager( $id, $load_options = false, $prefix_slug = true ) {
			return FS_Option_Manager::get_manager( ( $prefix_slug ? $this->_slug : '' ) . ( ( ! $prefix_slug || empty( $id ) ) ? '' : '_' ) . $id, $load_options );
		}

		/* Management Dashboard Menu
		------------------------------------------------------------------------------------------------------------------*/
		private $_has_menu = false;
		private $_menu_items = array();
		private $_menu_link_items = array();

		function _redirect_on_clicked_menu_link() {
			$this->_logger->entrance();

			$page = strtolower( isset( $_REQUEST['page'] ) ? $_REQUEST['page'] : '' );

			$this->_logger->log( 'page = ' . $page );


			foreach ( $this->_menu_link_items as $priority => $items) {
				foreach ( $items as $item ) {
					if ( $page === $item['menu_slug'] ) {
						$this->_logger->log( 'Redirecting to ' . $item['url'] );

						fs_redirect( $item['url'] );
					}
				}
			}
		}

		function _add_dashboard_menu() {
			$this->_logger->entrance();

			// Add user account page.
			$this->add_submenu_item(
				__( 'Account', $this->_slug ),
				array( &$this, '_account_page_render' ),
				$this->_plugin_data['Name'] . ' &ndash; ' . __( 'Account', $this->_slug ),
				'manage_options',
				'account',
				array( &$this, '_account_page_load' )
			);

			foreach ( $this->_menu_items as $item ) {
				$hook = add_submenu_page(
					$this->_slug,
					$item['page_title'],
					$item['menu_title'],
					$item['capability'],
					$item['menu_slug'],
					$item['render_function']
				);

				if ( false !== $item['before_render_function'] ) {
					add_action( "load-$hook", $item['before_render_function'] );
				}
			}

			ksort($this->_menu_link_items);

			foreach ( $this->_menu_link_items as $priority => $items) {
				foreach ( $items as $item ) {
					add_submenu_page(
						$this->_slug,
						$item['page_title'],
						$item['menu_title'],
						$item['capability'],
						$item['menu_slug'],
						array( $this, '' )
					);
				}
			}
		}

		function add_default_submenu_items() {
			if (!$this->_has_menu)
				return;

			$this->add_submenu_link_item( __( 'Support Forum', $this->_slug ), 'https://wordpress.org/support/plugin/' . $this->_slug, 'wp-support-forum', 'read', 50 );

			if ( ! $this->is_paying() ) {
				$this->add_submenu_link_item( '&#9733; ' . __( 'Upgrade', $this->_slug ) . ' &#9733;', $this->get_upgrade_url(), 'upgrade', 'read', 100 );
			}
		}

		function set_has_menu() {
			$this->_logger->entrance();

			$this->_has_menu = true;
		}

		private function _get_menu_slug( $slug = '' ) {
			return $this->_slug . ( empty( $slug ) ? '' : ( '-' . $slug ) );
		}

		function add_submenu_item( $menu_title, $render_function, $page_title = false, $capability = 'manage_options', $menu_slug = false, $before_render_function = false ) {
			$this->_logger->entrance();

			$this->_menu_items[] = array(
				'page_title'             => is_string( $page_title ) ? $page_title : $menu_title,
				'menu_title'             => $menu_title,
				'capability'             => $capability,
				'menu_slug'              => $this->_get_menu_slug( is_string( $menu_slug ) ? $menu_slug : strtolower( $menu_title ) ),
				'render_function'        => $render_function,
				'before_render_function' => $before_render_function,
			);

			$this->_has_menu = true;
		}

		function add_submenu_link_item( $menu_title, $url, $menu_slug = false, $capability = 'read', $priority = 10 ) {
			$this->_logger->entrance('Title = ' . $menu_title . '; Url = ' . $url);

			if (!isset($this->_menu_link_items[$priority]))
				$this->_menu_link_items[$priority] = array();

			$this->_menu_link_items[$priority][] = array(
				'menu_title'             => $menu_title,
				'capability'             => $capability,
				'menu_slug'              => $this->_get_menu_slug( is_string( $menu_slug ) ? $menu_slug : strtolower( $menu_title ) ),
				'url'                    => $url,
				'page_title'             => $menu_title,
				'render_function'        => 'fs_dummy',
				'before_render_function' => '',
			);

			$this->_has_menu = true;
		}

		/* Actions / Hooks / Filters
		------------------------------------------------------------------------------------------------------------------*/
		function do_action( $tag ) {
			$this->_logger->entrance( $tag );

			do_action( $tag . '_' . $this->_slug );
		}

		function add_action( $tag, $function_to_add, $priority = 10, $accepted_args = 1 ) {
			$this->_logger->entrance( $tag );

			add_action( $tag . '_' . $this->_slug, $function_to_add, $priority, $accepted_args );
		}

		/* Account Page
		------------------------------------------------------------------------------------------------------------------*/
		static function _secret_key_updated_message() {
			$vars = array(
				"message" => "You have successfully updated your Secret Key.",
				"type"    => "update-nag success"
			);

			fs_require_once_template( "admin-notice.php", $vars );
		}

		function _account_page_load() {
			$this->_logger->entrance();

			$this->_logger->info( var_export( $_REQUEST, true ) );

			fs_enqueue_local_style( 'fs_account', 'account.css' );

			if ( fs_request_is_action( 'update_secret' ) ) {
				check_admin_referer( 'update_secret' );

				$this->_logger->log( 'update_secret' );

				ratingwidget()->UpdateSecret( fs_request_get( 'fs_site_secret_' . $this->_slug, '' ) );

				// Anonymous functions are only available since PHP 5.3
				add_action( 'all_admin_notices', array('Freemius', '_secret_key_updated_message') );
			}

			$this->do_action( 'fs_account_page_load_before_departure' );
		}

		function _account_page_render() {
			$this->_logger->entrance();

			$vars = array( 'slug' => $this->_slug );
			fs_require_once_template( 'user-account.php', $vars );
		}

		/* Action Links
		------------------------------------------------------------------------------------------------------------------*/
		private $_action_links_hooked = false;
		private $_action_links = array();

		private function is_plugin_action_links_hooked() {
			$this->_logger->entrance( json_encode( $this->_action_links_hooked ) );

			return $this->_action_links_hooked;
		}

		private function hook_plugin_action_links() {
			$this->_logger->entrance();

			$this->_action_links_hooked = true;

			$this->_logger->log( 'Adding action links hooks.' );

			// Add action link to settings page.
			add_filter( 'plugin_action_links_' . $this->_plugin_basename, array(
					&$this,
					'_modify_plugin_action_links'
				), 10, 2 );
			add_filter( 'network_admin_plugin_action_links_' . $this->_plugin_basename, array(
					&$this,
					'_modify_plugin_action_links'
				), 10, 2 );
		}

		function add_plugin_action_link( $label, $url, $external = false, $priority = 10, $key = false ) {
			$this->_logger->entrance();

			if ( ! isset( $this->_action_links[ $priority ] ) ) {
				$this->_action_links[ $priority ] = array();
			}

			if ( false === $key ) {
				$key = preg_replace( "/[^A-Za-z0-9 ]/", '', strtolower( $label ) );
			}

			$this->_action_links[ $priority ][] = array(
				'label'    => $label,
				'href'     => $url,
				'key'      => $key,
				'external' => $external
			);

			if ( ! $this->is_plugin_action_links_hooked() ) {
				$this->hook_plugin_action_links();
			}
		}

		function _add_upgrade_action_link() {
			$this->_logger->entrance();

			if ( ! $this->is_paying() ) {
				$this->add_plugin_action_link( __( 'Upgrade', $this->_slug ), $this->get_upgrade_url(), true, 20, 'upgrade' );
			}
		}

		function _modify_plugin_action_links( $links, $file ) {
			$this->_logger->entrance();

			ksort( $this->_action_links );

			foreach ( $this->_action_links as $new_links ) {
				foreach ( $new_links as $link ) {
					$links[ $link['key'] ] = '<a href="' . $link['href'] . '"' . ( $link['external'] ? ' target="_blank"' : '' ) . '>' . $link['label'] . '</a>';
				}
			}

			return $links;
		}
	}
