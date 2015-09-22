<?php
	/**
	 * Plugin Name: Rating-Widget: Star Review System
	 * Plugin URI:  http://rating-widget.com/wordpress-plugin/
	 * Description: Create and manage Rating-Widget ratings in WordPress.
	 * Version:     2.6.6
	 * Author:      Rating-Widget
	 * Author URI:  http://rating-widget.com/wordpress-plugin/
	 * License:     GPLv2
	 * Text Domain: ratingwidget
	 * Domain Path: /langs
	 *
	 * @package     RatingWidget
	 * @copyright   Copyright (c) 2015, Rating-Widget, Inc.
	 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
	 * @since       1.0.0
	 *
	 * @fs_premium_only dir, file.php
	 */

	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	if (!class_exists('RatingWidgetPlugin')) :
		// Load common config.
		require_once( dirname( __FILE__ ) . "/workflow/start.php" );
		require_once( dirname( __FILE__ ) . "/lib/rw-core-functions.php" );
		require_once( dirname( __FILE__ ) . "/lib/config.common.php" );
		require_once( WP_RW__PLUGIN_LIB_DIR . "rw-core-rw-functions.php" );
		require_once( WP_RW__PLUGIN_LIB_DIR . "rw-options.php" );
		require_once( WP_RW__PLUGIN_LIB_DIR . "rw-account-manager.php" );
		require_once( WP_RW__PLUGIN_LIB_DIR . "rw-api.php" );
		require_once( WP_RW__PLUGIN_LIB_DIR . "rw-settings.php" );
		require_once( WP_RW__PLUGIN_LIB_DIR . "rw-shortcodes.php" );
		require_once( WP_RW__PLUGIN_DIR . "/lib/logger.php" );

		/**
		 * Rating-Widget Plugin Class
		 *
		 * @package Wordpress
		 * @subpackage RatingWidget Plugin
		 * @author Vova Feldman
		 * @copyright Rating-Widget, Inc.
		 */
		class RatingWidgetPlugin
		{
			/**
			 * @var RatingWidgetPlugin_Admin
			 */
			public $admin;
			/**
			 * @var RatingWidgetPlugin_Settings
			 */
			public $settings;

			/**
			 * @var RW_Account_Manager
			 */
			public $account;

			private $errors;
			private $success;
			/**
			 * @var Freemius
			 */
			public $fs;
			static $ratings = array();

			var $is_admin;
			var $languages;
			var $languages_short;
			var $_visibilityList;
			var $categories_list;
			var $availability_list;
			var $show_on_excerpts_list;
			var $custom_settings_enabled_list;
			var $custom_settings_list;
			var $multirating_settings_list;
			var $_inDashboard = false;
			var $_isRegistered = false;
			var $_inBuddyPress;
			var $_inBBPress;
			/**
			 * @var FS_Option_Manager
			 */
			var $_options;

			/**
			 * @var stdClass
			 */
			private $_readonly_list;

			static $VERSION;

			public static $WP_RW__HIDE_RATINGS = false;

			#region Singleton Pattern ------------------------------------------------------------------

			private static $INSTANCE;
			public static function Instance()
			{
				if ( ! isset( self::$INSTANCE ) )
					self::$INSTANCE = new RatingWidgetPlugin();

				return self::$INSTANCE;
			}

			#endregion Singleton Pattern ------------------------------------------------------------------

			#region Plugin Setup ------------------------------------------------------------------

			private function __construct() {
				$this->account          = rw_account();
				$this->fs               = rw_fs();
				$this->wf               = rw_wf();
				$this->_options = rw_fs_options();

				if ( WP_RW__DEBUG ) {
					$this->InitLogger();
				}

				// Load plugin options.
				$this->LoadDefaultOptions();
				$this->LoadOptions();

				// Give 2nd chance to logger after options are loaded.
				if ( ! RWLogger::IsOn() && $this->GetOption( WP_RW__LOGGER ) ) {
					$this->InitLogger();
				}

				// If not in admin dashboard and account don't exist, don't continue with plugin init.
				if ( ! $this->fs->is_registered() && ! is_admin() ) {
					return;
				}

				// Load config after keys are loaded.
				require_once( WP_RW__PLUGIN_DIR . "/lib/config.php" );

				// Load top-rated
				require_once(WP_RW__PLUGIN_LIB_DIR . "rw-top-rated-widget.php");
			}

			function Init()
			{
				$this->init_fs_hooks();


				if ( ! $this->fs->is_registered() && ! is_admin() ) {
					return;
				}

				if ($this->fs->is_registered() && !$this->account->is_registered()) {
					// Connected only with FS, therefore try to connect to RW.
					$this->connect_account(
						$this->fs->get_user(),
						$this->fs->get_site()
					);
				}

				// Load all extensions.
				require_once(WP_RW__PLUGIN_LIB_DIR . "rw-extension-abstract.php");
				require_once(WP_RW__PLUGIN_LIB_DIR_EXT . 'rw-woocommerce.php');

				$this->LoadExtensionsDefaultOptions();

				$this->LogInitialData();

				// Run plugin setup.
				$continue = is_admin() ?
					$this->setup_on_dashboard() :
					$this->setup_on_site();

				if ( ! $continue )
				{
					return;
				}

				if ( $this->fs->is_registered() )
				{
					$this->fs->add_submenu_link_item(__('FAQ', WP_RW__ID), rw_get_site_url( 'support/wordpress/#platform' ), false, 'read', 25);

//					add_action( 'init', array( &$this, 'LoadPlan' ) );
					// Clear cache has to be executed after LoadPlan, because clear
					// cache is has a condition that checks the plan.
					add_action( 'init', array( &$this, 'ClearCache' ) );
					add_action( 'init', array( &$this, 'SetupBuddyPress' ) );
					add_action( 'init', array( &$this, 'SetupBBPress' ) );
				}

				$this->errors  = new WP_Error();
				$this->success = new WP_Error();

				/**
				 * IMPORTANT:
				 *   All scripts/styles must be enqueued from these actions,
				 *   otherwise it will mass-up the layout of the admin's dashboard
				 *   on RTL WP versions.
				 */
				add_action( 'admin_enqueue_scripts', array( &$this, 'InitScriptsAndStyles' ) );

				// Enqueue site's styles
				add_action('wp_enqueue_scripts', array(&$this, 'init_site_styles'));

				require_once( WP_RW__PLUGIN_DIR . "/languages/dir.php" );
				$this->languages       = $rw_languages;
				$this->languages_short = array_keys( $this->languages );

				add_action( 'plugins_loaded', array( &$this, 'rw_load_textdomain' ) );
			}


			/**
			 * Init Freemius related action & filter hooks.
			 *
			 * @author Vova Feldman (@svovaf)
			 * @since 2.5.7
			 */
			function init_fs_hooks()
			{
				$this->fs->add_filter('connect_message', array(&$this, 'fs_connect_message'));
				$this->fs->add_action('after_account_connection', array(&$this, 'connect_account'), 10, 2);
				$this->fs->add_action('after_premium_version_activation', array(&$this, 'after_premium_version_activation_hook'), 10, 2);
				$this->fs->add_action('after_account_delete', array(&$this, 'delete_account_and_settings'));
//				$this->fs->add_action('account_email_verified', array(&$this, 'verify_email'));

				$this->fs->add_action('after_account_details', array(&$this, 'AccountPageRender'));

				$this->fs->add_action('account_page_load_before_departure', array(&$this, 'AccountPageLoad'), 10, 3);
			}

			private function LoadExtensionsDefaultOptions()
			{
				RWLogger::LogEnterence("LoadExtensionsDefaultOptions");

				foreach ($this->_extensions as $ext)
				{
					$def_options = $ext->GetDefaultOptions();
					foreach ($def_options as $k => $v)
						$this->_OPTIONS_DEFAULTS[$k] = $v;

					$def_align = $ext->GetDefaultAlign();
					foreach ($def_align as $k => $v)
						$this->_OPTIONS_DEFAULTS[$k] = $v;
				}

				RWLogger::LogDeparture("LoadExtensionsDefaultOptions");
			}

			function rw_load_textdomain()
			{
				load_plugin_textdomain('ratingwidget', false, dirname(plugin_basename( __FILE__ )) . '/langs');
			}

			private function InitLogger()
			{
				// Start logger.
				RWLogger::PowerOn();

//				if (is_admin())
//					add_action('admin_footer', array(&$this, "DumpLog"));
//				else
//					add_action('wp_footer', array(&$this, "DumpLog"), 100);
			}

			protected function LogInitialData()
			{
				if (!RWLogger::IsOn())
					return;

				RWLogger::Log("WP_RW__VERSION", WP_RW__VERSION);
				RWLogger::Log("Site Public Key", $this->account->site_public_key);
				RWLogger::Log('Site ID', $this->account->site_id);
				RWLogger::Log("WP_RW__DOMAIN", WP_RW__DOMAIN);
				RWLogger::Log("WP_RW__CLIENT_ADDR", WP_RW__CLIENT_ADDR);
				RWLogger::Log("WP_RW__PLUGIN_DIR", WP_RW__PLUGIN_DIR);
				RWLogger::Log("WP_RW__PLUGIN_URL", WP_RW__PLUGIN_URL);
				RWLogger::Log("WP_RW__SHOW_PHP_ERRORS", json_encode(WP_RW__SHOW_PHP_ERRORS));
				RWLogger::Log("WP_RW__LOCALHOST_SCRIPTS", json_encode(WP_RW__LOCALHOST_SCRIPTS));
				RWLogger::Log("WP_RW__CACHING_ON", json_encode(WP_RW__CACHING_ON));
				RWLogger::Log("WP_RW__STAGING", json_encode(WP_RW__STAGING));
				RWLogger::Log('WP_RW__HTTPS', json_encode(WP_RW__HTTPS));
				RWLogger::Log('WP_RW__PROTOCOL', json_encode(WP_RW__PROTOCOL));
				RWLogger::Log('WP_RW__ADDRESS', json_encode(WP_RW__ADDRESS));
				RWLogger::Log('WP_RW__SECURE_ADDRESS', json_encode(WP_RW__SECURE_ADDRESS));

				// Don't log secure data.
				if (is_admin()) {
					RWLogger::Log( "WP_RW__SERVER_ADDR", WP_RW__SERVER_ADDR );
					RWLogger::Log( "WP_RW__DEBUG", json_encode( WP_RW__DEBUG ) );
				}
			}

			private function setup_on_dashboard()
			{
				RWLogger::LogEnterence("setup_on_dashboard");

				// Init settings.
				$this->settings = new RatingWidgetPlugin_Settings();

				$this->_inDashboard = (isset($_GET['page']) && rw_starts_with($_GET['page'], $this->GetMenuSlug()));

				if (!$this->fs->is_registered() &&
				    $this->_inDashboard && strtolower($_GET['page']) !== $this->GetMenuSlug())
					rw_admin_redirect();

				$this->setup_dashboard_actions();

				return true;
			}

			private function setup_on_site()
			{
				RWLogger::LogEnterence("setup_on_site");

				if ($this->IsHideOnMobile() && $this->IsMobileDevice())
				{
					// Don't show any ratings.
					self::$WP_RW__HIDE_RATINGS = true;

					return false;
				}

				$this->setup_site_actions();

				return true;
			}

			private function setup_dashboard_actions() {
				RWLogger::LogEnterence( "setup_dashboard_actions" );

				$this->fs->add_plugin_action_link( __( 'Settings', WP_RW__ADMIN_MENU_SLUG ), rw_get_admin_url() );
				$this->fs->add_plugin_action_link( __( 'Blog', WP_RW__ADMIN_MENU_SLUG ), rw_get_site_url( '/blog/' ), true );

				if ($this->account->is_registered()) {
					add_action( 'wp_ajax_rw-toprated-popup-html', array( &$this, 'generate_toprated_popup_html' ) );
					add_action( 'wp_ajax_rw-affiliate-apply', array( &$this, 'send_affiliate_application' ) );
					add_action( 'wp_ajax_rw-addon-request', array( &$this, 'send_addon_request' ) );
					add_action( 'admin_init', array( &$this, 'register_admin_page_hooks' ) );
					add_action( 'admin_menu', array( &$this, 'AddPostMetaBox' ) ); // Metabox for posts/pages
					add_action( 'admin_menu', array( &$this, 'add_comment_rating_metabox' ) ); // Metabox for comment edit page.
					add_action( 'save_post', array( &$this, 'SavePostData' ) );
					add_action( 'edit_comment', array( &$this, 'save_comment_data' ) );

					if ( $this->is_api_supported() ) {
						// Since some old users might not having a secret key set,
						// the API won't be able to work for them - therefore, all API related
						// hooks must be executed within this scope.
						add_action( 'trashed_post', array( &$this, 'DeletePostData' ) );
						add_action('wp_ajax_rw-five-star-wp-rate', array(&$this, 'five_star_wp_rate_action'));

						$min_votes_trigger = $this->GetOption(WP_RW__DB_OPTION_WP_RATE_NOTICE_MIN_VOTES_TRIGGER);
						if (-1 !== $min_votes_trigger) {
							add_action('admin_notices', array(&$this, 'five_star_wp_rate_notice'));
						}
					}
				}

				add_action( 'admin_head', array( &$this, "rw_admin_menu_icon_css" ) );

//				add_action( 'admin_menu', array( &$this, "admin_menu" ) );
				$this->fs->add_action('before_admin_menu_init', array( &$this, 'admin_menu' ) );

				add_action( 'updated_post_meta', array( &$this, 'PurgePostFeaturedImageTransient' ), 10, 4 );

				{
					// wp_footer call validation.
					// add_action('init', array(&$this, 'test_footer_init'));
				}
			}

			#endregion Plugin Setup ------------------------------------------------------------------

			function _update_account() {
				if ( strtoupper( $_SERVER['REQUEST_METHOD'] ) !== 'POST' ) {
					return;
				}

				$properties = array( 'user_id', 'site_secret_key', 'site_id', 'site_public_key' );

				foreach ( $properties as $p ) {
					if ( rw_request_is_action( 'update_' . $p ) ) {
						check_admin_referer( 'update_' . $p );

						$value = fs_request_get( 'rw_' . $p );

						switch ( $p ) {
							case 'site_id':
								$this->account->update_site_id( $value );
								break;
							case 'site_public_key':
								$this->account->update_site_public_key( $value );
								break;
							case 'site_secret_key':
								$this->account->update_site_secret_key( $value );
								break;
							case 'user_id':
								$this->account->update_site_secret_key( $value );
								break;
							default:
								return;
						}

						break;
					}
				}
			}

			/**
			 * Validates the current site able to connect the API.
			 *
			 * @author Vova Feldman (@svovaf)
			 * @since 2.4.8
			 *
			 * @return bool
			 */
			private function is_api_supported()
			{
				return rwapi()->is_supported();
			}

			/**
			 * Sends an affiliate application to affiliate@rating-widget.com
			 *
			 * @author Leo Fajardo (@leorw)
			 * @since 2.4.4
			 *
			 */
			function send_affiliate_application() {
				// Continue only if the nonce is correct
				check_admin_referer('rw_send_affiliate_application_nonce', '_n');

				$admin_email = get_option('admin_email');
				$user = $this->fs->get_user();

				$posts_count = wp_count_posts('post');
				$pages_count = wp_count_posts('page');
				$total_posts = $posts_count->publish + $pages_count->publish;

				$blog_address = site_url();
				$domain = $_SERVER['HTTP_HOST'];

				$comments_count = wp_count_comments();
				$total_approved_comments = $comments_count->approved;

				$subject = "$domain wants to be an affiliate";

				$email_details = array(
					'aff_admin_email' => $admin_email,
					'aff_user_id' => $user->id,
					'aff_site_id' => $this->account->site_id,
					'aff_site_address' => $blog_address,
					'aff_total_posts' => $total_posts,
					'aff_total_comments' => $total_approved_comments
				);

				// Retrieve the HTML email content
				ob_start();
				rw_require_view('emails/affiliation_email.php', $email_details);
				$message = ob_get_contents();
				ob_end_clean();

				$header = 'Content-type: text/html';
				wp_mail('affiliate@rating-widget.com', $subject, $message, $header);

				echo 1;
				exit;
			}

			/**
			 * Sends an email to addons@rating-widget.com containing
			 * information about the add-on with which the user is interacting.
			 *
			 * @author Leo Fajardo (@leorw)
			 * @since 2.5.1
			 *
			 */
			function send_addon_request() {
				// Continue only if the nonce is correct
				check_admin_referer('rw_send_addon_request', '_n');

				$addons = $this->get_addons();
				$addon = $addons[$_REQUEST['addon_key']];
				$pricing = $addon['pricing'][0];
				$price = $pricing['annual_price'];
				$is_free = (NULL === $price);

				$addon_title = '';
				$total_addons = count($addons);
				for ( $i = 0; $i < $total_addons; $i++ ) {
					if ( !empty($addon_title) ) {
						if ( $i % 3 != 0 ) {
							$addon_title .= ', ';
						} else {
							$addon_title .= '<br />';
						}
					}

					$addon_title .= $addons[$i]['title'];
				}

				$site_address = site_url();

				$email_details = array(
					'addon_title' => $addon['title'],
					'addon_price' => $is_free ? 'Free' : $price,
					'addon_site_address' => $site_address,
					'addon_action' => $_REQUEST['addon_action'],
					'addon_order' => $addon_title
				);

				if ( isset($_REQUEST['add_user']) ) {
					$user_email = get_option('admin_email');

					$email_details['addon_user_email'] = $user_email;
				}

				// Retrieve the HTML email content
				ob_start();
				rw_require_view('emails/addon_email.php', $email_details);
				$message = ob_get_contents();
				ob_end_clean();

				$subject = "Add-on Request: {$addon['title']} / " . ($is_free ? 'Free' : $price);
				$header = 'Content-type: text/html';
				wp_mail('addons@rating-widget.com', $subject, $message, $header);

				echo 1;
				exit;
			}

			/**
			 * Returns an array of available add-ons.
			 *
			 * @author Leo Fajardo (@leorw)
			 * @since 2.5.1
			 *
			 * @return array
			 */
			function get_addons() {
				$addons = array(
					array(
						'id' => 1,
						'title' => 'Reviews',
						'description' => 'Open a comment form after visitor vote to get textual feedback from your users.',
						'thumbnail_url' => rw_get_plugin_img_path('add-ons/reviews.jpg'),
						'avg_rate' => 5.0,
						'pricing' => array(
							array(
								'id' => '',
								'annual_price' => 19.99
							)
						),
						'version' => '',
						'licenses' => ''
					),
					array(
						'id' => 2,
						'title' => 'Product Reviews',
						'description' => 'Open a comment form after visitor vote to get textual feedback from your customers.',
						'thumbnail_url' => rw_get_plugin_img_path('add-ons/product_reviews.jpg'),
						'avg_rate' => 5.0,
						'pricing' => array(
							array(
								'id' => 1,
								'annual_price' => 19.99
							)
						),
						'version' => '',
						'licenses' => ''
					),
					array(
						'id' => 3,
						'title' => 'Subscribers',
						'description' => 'Ask your visitors to subscribe after after a 5-star rating.',
						'thumbnail_url' => rw_get_plugin_img_path('add-ons/subscribers.jpg'),
						'avg_rate' => 5.0,
						'pricing' => array(
							array(
								'id' => 1,
								'annual_price' => 19.99
							)
						),
						'version' => '',
						'licenses' => ''
					),
					array(
						'id' => 4,
						'title' => 'Twitter Followers',
						'description' => 'Ask your visitors to follow your Twitter account after a 5-star rating.',
						'thumbnail_url' => rw_get_plugin_img_path('add-ons/twitter_followers.jpg'),
						'avg_rate' => 5.0,
						'pricing' => array(
							array(
								'id' => 1,
								'annual_price' => 19.99
							)
						),
						'version' => '',
						'licenses' => ''
					),
					array(
						'id' => 5,
						'title' => 'Facebook Fans',
						'description' => 'Ask your visitors to like your Facebook Fans page after a 5-star rating.',
						'thumbnail_url' => rw_get_plugin_img_path('add-ons/facebook_fans.jpg'),
						'avg_rate' => 5.0,
						'pricing' => array(
							array(
								'id' => 1,
								'annual_price' => 19.99
							)
						),
						'version' => '',
						'licenses' => ''
					),
					array(
						'id' => 6,
						'title' => 'Mobile Alerts',
						'description' => 'Get push notification about every ratings on your site in real-time!',
						'thumbnail_url' => rw_get_plugin_img_path('add-ons/mobile_alerts.jpg'),
						'avg_rate' => 5.0,
						'pricing' => array(
							array(
								'id' => 1,
								'annual_price' => 19.99
							)
						),
						'version' => '',
						'licenses' => ''
					),
					array(
						'id' => 7,
						'title' => 'Tweets',
						'description' => 'Ask your visitors to follow your Twitter account after a 5-star rating.',
						'thumbnail_url' => rw_get_plugin_img_path('add-ons/tweets.jpg'),
						'avg_rate' => 5.0,
						'pricing' => array(
							array(
								'id' => 1,
								'annual_price' => 19.99
							)
						),
						'version' => '',
						'licenses' => ''
					),
					array(
						'id' => 8,
						'title' => 'Facebook Likes',
						'description' => 'Ask your visitors to like your Facebook Fans page after a 5-star rating.',
						'thumbnail_url' => rw_get_plugin_img_path('add-ons/facebook_likes.png'),
						'avg_rate' => 5.0,
						'pricing' => array(
							array(
								'id' => 1,
								'annual_price' => 19.99
							)
						),
						'version' => '',
						'licenses' => ''
					)
				);

				// Reorder the add-ons using the Fisher-Yates algorithm.
				// Generate a seed value based on the site URL.
				$seed = crc32(site_url());
				mt_srand($seed);

				// Fisher-Yates shuffle algorithm
				$total = count($addons);
				for ( $i = $total - 1; $i > 0; $i-- ) {
					$j = mt_rand(0, $i);
					$tmp = $addons[$i];
					$addons[$i] = $addons[$j];
					$addons[$j] = $tmp;
				}

				return $addons;
			}

			/**
			 * This function updates the minimum votes required in order to
			 * display the admin notice at the top of the current page.
			 *
			 * @author Leo Fajardo (@leorw)
			 * @since 2.4.9
			 */
			function five_star_wp_rate_action() {
				// Continue only if the nonce is correct
				check_admin_referer('rw_five_star_wp_rate_action_nonce', '_n');

				$min_votes_trigger = $this->GetOption(WP_RW__DB_OPTION_WP_RATE_NOTICE_MIN_VOTES_TRIGGER);
				if (-1 === $min_votes_trigger) {
					exit;
				}

				$rate_action = $_POST['rate_action'];
				if ('do-rate' === $rate_action) {
					$min_votes_trigger = -1;
				} else if (10 === $min_votes_trigger) {
					$min_votes_trigger = 100;
				} else if (100 === $min_votes_trigger) {
					$min_votes_trigger = 1000;
				} else {
					$min_votes_trigger = -1;
				}

				$this->SetOption(WP_RW__DB_OPTION_WP_RATE_NOTICE_MIN_VOTES_TRIGGER, $min_votes_trigger);
				$this->_options->store();

				echo 1;
				exit;
			}

			/**
			 * Determines if rich editing is available
			 *
			 * @author Leo Fajardo (@leorw)
			 * @since 2.4.1
			 *
			 * @return boolean
			 */
			function admin_page_has_editor() {
				global $pagenow, $typenow;

				if (in_array($pagenow, array('post.php', 'post-new.php'))) {
					if (empty($typenow)) {
						if (!empty($_GET['post'])) {
							$post = get_post($_GET['post']);
							$typenow = $post->post_type;
						} else if ('post-new.php' == $pagenow && !isset($_GET['post_type'])) {
							$typenow = 'post';
						}
					}

					if (current_user_can('publish_posts') && get_user_option('rich_editing')) {
						if (function_exists('post_type_supports')) {
							return post_type_supports($typenow, 'editor');
						}

						return true;
					}
				}

				return false;
			}


			/**
			 * Registers the necessary hooks
			 *
			 * @author Leo Fajardo (@leorw)
			 * @since 2.4.1
			 */
			function register_admin_page_hooks() {
				if ($this->admin_page_has_editor()) {
					add_action('admin_footer', array(&$this, "init_toprated_shortcode_settings"));
					add_filter('mce_external_plugins', array(&$this, 'register_tinymce_plugin'));
					add_filter('mce_buttons', array(&$this, 'add_tinymce_button'));
				}

				// If API is supported and the current user is an administrator, add the statistics dashboard widget.
				if ( $this->is_api_supported() && current_user_can('administrator') ) {
					add_action( 'wp_dashboard_setup', array(&$this, 'add_dashboard_widgets') );
				}
			}

			/**
			 * For TinyMCE 3 and below. Generates the HTML content for the TinyMCE dialog box.
			 *
			 * @author Leo Fajardo (@leorw)
			 * @since 2.4.1
			 * @return string
			 */
			function generate_toprated_popup_html() {
				rw_require_view('pages/admin/toprated-tinymce.php');
				exit();
			}

			/**
			 * Initializes the options to be used by the top-rated TinyMCE popup dialog
			 *
			 * @author Leo Fajardo (@leorw)
			 * @since 2.4.1
			 */
			function init_toprated_shortcode_settings() {
				$extensions = ratingwidget()->GetExtensions();

				$bbpress_installed = function_exists('is_bbpress');
				$buddypress_installed = function_exists('is_buddypress');
				$woocommerce_installed = isset($extensions['woocommerce']);

				// Initialize the maximum items allowed.
				if ($this->fs->is_plan_or_trial__premium_only('professional'))
				{
					$max_item_count = 50;
				}
				else
				{
					$max_item_count = 11;
				}

				$max_items = array();

				for ($count = 1; $count <= $max_item_count; $count++) {
					if ($count === $max_item_count) {
						if (!$this->fs->is_plan_or_trial__premium_only('professional')) {
							$max_items['upgrade'] = __( 'Upgrade to Professional for 50 Items', WP_RW__ID );
							break;
						}
					}

					$max_items[$count] = (string) $count;
				}

				// Initialize the available types
				$types = array(
					'pages' => __('Pages', WP_RW__ID),
					'posts' => __('Posts', WP_RW__ID)
				);

				if ($woocommerce_installed) {
					$types['products'] = __('Products', WP_RW__ID);
				}

				if ($bbpress_installed) {
					$types['forum_posts'] = __('Topics', WP_RW__ID);
				}

				if ($bbpress_installed || $buddypress_installed) {
					$types['users'] = __('Users', WP_RW__ID);
				}

				$rw_toprated_options = array(
					'fields' => array('max_items' => $max_items, 'types' => $types),
					'upgrade_url' => $this->fs->get_upgrade_url(),
					'bbpress_installed' => $bbpress_installed,
					'buddypress_installed' => $buddypress_installed,
					'woocommerce_installed' => $woocommerce_installed
				);
				?>
				<script>
					RW_TOPRATED_OPTIONS = <?php echo json_encode($rw_toprated_options); ?>;
				</script>
			<?php
			}

			/**
			 * Registers the top-rated shortcode TinyMCE plugin
			 *
			 * @author Leo Fajardo (@leorw)
			 * @since 2.4.1
			 * @param array $plugin_array
			 * @return array
			 */
			function register_tinymce_plugin($plugin_array) {
				$plugin_array['rw_toprated_shortcode_button'] = WP_RW__PLUGIN_URL . '/resources/js/top-rated/toprated-shortcode-plugin.js';
				return $plugin_array;
			}

			/**
			 * Inserts the top-rated shortcode TinyMCE button
			 *
			 * @author Leo Fajardo (@leorw)
			 * @since 2.4.1
			 * @param array $buttons
			 * @return array
			 */
			function add_tinymce_button($buttons) {
				$buttons[] = 'rw_toprated_shortcode_button';
				return $buttons;
			}

			function RegisterExtensionsHooks() {
				RWLogger::LogEnterence( 'RegisterExtensionsHooks' );

				foreach ( $this->_extensions as $ext ) {
					RWLogger::Log( 'RegisterExtensionsHooks', 'Processing extension ' . $ext->GetSlug() );
					if ( $ext->IsExtensionPage() ) {
						$class = $ext->GetCurrentPageClass();

						RWLogger::Log( 'RegisterExtensionsHooks', 'Extension page for class ' . $class );

						if ( false !== $this->GetRatingAlignByType( $ext->GetAlignOptionNameByClass( $class ) ) && ! $this->IsHiddenRatingByType( $class ) ) {
							RWLogger::Log( 'RegisterExtensionsHooks', 'Hooking ' . $class );
							$ext->Hook($class);
						}
					}
				}

				RWLogger::LogDeparture( 'RegisterExtensionsHooks' );
			}

			private function setup_site_actions()
			{
				RWLogger::LogEnterence("setup_site_actions");

				// If not registered, don't add any actions to site.
				if (!$this->account->is_registered())
					return;

				// Posts / Pages / Comments.
				add_action("loop_start", array(&$this, "rw_before_loop_start"));

				// Register extensions hooks.
				add_action('loop_start', array(&$this, 'RegisterExtensionsHooks'));

				// Register shortcode.
				add_action('init', array(&$this, 'RegisterShortcodes'));

				// Register hooks for comment review mode.
				add_action('init', array(&$this, 'register_comment_review_hooks'));

				// wp_footer call validation.
				// add_action('init', array(&$this, 'test_footer_init'));

				// Rating-Widget main javascript load.
				add_action('wp_footer', array(&$this, "rw_attach_rating_js"), 5);
			}

			/**
			 * Registers the necessary hooks for implementing the comment "Reviews" mode (when the selected mode in the "Comments" options tab is "Review").
			 *
			 * @author Leo Fajardo (@leorw)
			 * @since 2.5.9
			 *
			 */
			function register_comment_review_hooks() {
				if ( !$this->is_comment_review_mode() ) {
					return;
				}

				// If comment rating is disabled, do not proceed.
				if ( false === $this->GetRatingAlignByType( WP_RW__COMMENTS_ALIGN ) ) {
					return;
				}

				// If comment rating is hidden (e.g.: hidden for users who are not logged in), do not proceed.
				if ( $this->IsHiddenRatingByType('comment') ) {
					return;
				}

				global $wp_version;
				if ($wp_version < 3 ) {
					// Since the comment_form_defaults filter is not available for version 2.9 and below, we use a different way of adding the rating to the form.
					add_action('comment_form', array(&$this, 'add_rating_after_comment_form_submit_button'));
				} else {
					add_filter('comment_form_defaults', array(&$this, 'add_rating_before_comment_form_submit_button'));
				}

				// Listen to insert comment event so that we can set the vote of the newly inserted comment's rating when the comment rating mode is "Review".
				add_action('wp_insert_comment', array(&$this, 'create_comment_review_rating'));
			}

			#region Comment Review Mode ------------------------------------------------------------------

			/**
			 * Adds a rating before the comment form's submit button when the
			 * comment's rating mode is set to "Review".
			 *
			 * @author Leo Fajardo (@leorw)
			 * @since  2.5.9
			 *
			 * @param array $comment_form_defaults
			 *
			 * @return array
			 */
			function add_rating_before_comment_form_submit_button($comment_form_defaults) {
				RWLogger::LogEnterence('add_rating_before_comment_form_submit_button');

				if ( RWLogger::IsOn() ) {
					RWLogger::Log('comment_form_defaults', json_encode($comment_form_defaults));
				}

				if ( isset($comment_form_defaults['submit_field']) ) {
					if ( RWLogger::IsOn() ) {
						RWLogger::Log('Add rating above the submit field');
					}

					$comment_form_defaults['submit_field'] = $this->get_comment_form_rating_html() . $comment_form_defaults['submit_field'];
				} else if ( isset($comment_form_defaults['comment_notes_after']) ) {
					if ( RWLogger::IsOn() ) {
						RWLogger::Log('Add rating below the notes');
					}

					$comment_form_defaults['comment_notes_after'] .= $this->get_comment_form_rating_html();
				} else {
					// HTML code for both the submit field and notes are not available. Probably the theme is incompatible.
					if ( RWLogger::IsOn() ) {
						RWLogger::Log('Incompatible comment form');
					}
				}

				RWLogger::LogDeparture('add_rating_before_comment_form_submit_button');
				return $comment_form_defaults;
			}

			/**
			 * Adds a rating after the comment form's submit button when the comment's rating mode is set to "Review". This is for WordPress version 2.9 and below.
			 *
			 * @author Leo Fajardo (@leorw)
			 * @since 2.5.9
			 */
			function add_rating_after_comment_form_submit_button() {
				RWLogger::LogEnterence('add_rating_after_comment_form_submit_button');

				echo $this->get_comment_form_rating_html();
			}

			/**
			 * Generates the HTML for the comment form's rating when in review mode.
			 *
			 * @author Leo Fajardo (@leorw)
			 * @since 2.5.9
			 *
			 */
			function get_comment_form_rating_html() {
				$urid = 'dummy-comment-rating';

				// Enqueue the rating data so that the dummy rating will have the correct styles set in the "Comments" options tab.
				$this->QueueRatingData($urid, '', '', 'comment');

				// Create a dummy rating and disable the report.
				return $html = '<p>' . $this->GetRatingHtml($urid, 'comment', false, '', '', array('show-report' => 'false', 'is-dummy' => 'true')) . '</p>';
			}

			/**
			 * Creates a new rating for the newly inserted comment.
			 *
			 * @author Leo Fajardo (@leorw)
			 * @since 2.5.9
			 *
			 * @param int $comment_id The newly inserted comment's ID.
			 */
			function create_comment_review_rating($comment_id) {
				if ( !isset($_POST['rw-vote-data']) || empty($_POST['rw-vote-data']) ) {
					return;
				}

				// Remove the slashes added by WordPress
				$vote_data = stripslashes($_POST['rw-vote-data']);

				// Enclose numbers with double quotes to prevent json_decode from converting them into exponential forms.
				$vote_data = preg_replace('/:(\d+)/', ':"${1}"', $vote_data);

				$request_params = (array) json_decode($vote_data);
				if ( isset($request_params['url']) ) {
					$request_params['url'] = urlencode($request_params['url']);
				}

				if ( isset($request_params['like']) ) {
					// Convert boolean value to string in order to preserve the value when passed in the HTTP request.
					$request_params['like'] = $request_params['like'] ? 'true' : 'false';
				}

				$this->set_comment_review_vote($comment_id, $request_params);
			}

			/**
			 * Manually votes for the newly inserted comment.
			 *
			 * @author Leo Fajardo (@leorw)
			 * @since 2.5.9
			 *
			 * @param int $comment_id The newly inserted comment's ID.
			 * @param array $request_params The API request parameters.
			 */
			function set_comment_review_vote($comment_id, $request_params) {
				RWLogger::LogEnterence("set_comment_review_vote");

				$comment_urid = $this->_getCommentRatingGuid($comment_id);
				if ( RWLogger::IsOn() ) {
					RWLogger::Log('comment_id', $comment_id);
					RWLogger::Log('comment_urid', $comment_urid);
				}

				$request_params['urid'] = $comment_urid;

				// The "referer" header is important, otherwise we may get an invalid referer error.
				$remote_request_param = array(
					'timeout' => 3,
					'headers' => array(
						'referer' => site_url()
					)
				);

				$comment_review_mode_settings = $this->get_comment_review_mode_settings();
				$failed_requests = $comment_review_mode_settings->failed_requests;
				if ( RWLogger::IsOn() ) {
					RWLogger::Log('failed_requests', json_encode($failed_requests));
				}

				$rating_submitted = false;

				if ( isset($failed_requests[$comment_id]) ) {
					$request = $failed_requests[$comment_id];
					$rating_submitted = isset($request['api_endpoint']) && (false !== strpos($request['api_endpoint'], 'rate.php'));
				}

				if ( $rating_submitted ) {
					$request_errors = array();
				} else {
					// Step 1: Submit the rating's ID to the server so that it will be created.
					$submit_rating_params = array_merge(
						array(
							'ids' => "[$comment_urid]"
						),
						$request_params
					);

					// Remove unneeded params
					unset($submit_rating_params['rate']);
					unset($submit_rating_params['like']);
					unset($submit_rating_params['urid']);
					unset($submit_rating_params['vid']);
					unset($submit_rating_params['voteID']);

					$rating_endpoint = rw_get_js_url('api/rating/get.php');
					$rating_endpoint = add_query_arg($submit_rating_params, $rating_endpoint);
					$submit_rating_response = wp_remote_get($rating_endpoint, $remote_request_param);
					$request_errors = $this->comment_review_submission_errors(array(
						'comment_id' => $comment_id,
						'request_params' => $request_params,
						'api_endpoint' => $rating_endpoint,
						'api_response' => $submit_rating_response
					));
				}

				$update_db_option = true;

				if ( empty($request_errors) ) {
					// Step 2: Submit the rating's vote data to the server.
					$vote_endpoint = rw_get_js_url('api/rating/rate.php');
					$vote_endpoint = add_query_arg($request_params, $vote_endpoint);
					$submit_vote_response = wp_remote_get($vote_endpoint, $remote_request_param);

					$request_errors = $this->comment_review_submission_errors(array(
						'comment_id' => $comment_id,
						'request_params' => $request_params,
						'api_endpoint' => $vote_endpoint,
						'api_response' => $submit_vote_response
					));

					// If there is no issue with the vote submission, remove the failed request information related to this comment from the database.
					if ( empty($request_errors) ) {
						if ( isset($failed_requests[$comment_id]) ) {
							unset($failed_requests[$comment_id]);
						} else {
							$update_db_option = false;
						}
					} else {
						$failed_requests[$comment_id] = $request_errors;
					}
				} else {
					$failed_requests[$comment_id] = $request_errors;
				}

				if ( $update_db_option ) {
					$comment_review_mode_settings->failed_requests = $failed_requests;
					$this->SetOption(WP_RW__DB_OPTION_COMMENT_REVIEW_MODE_SETTINGS, $comment_review_mode_settings);
					$this->_options->store();
				}

				RWLogger::LogDeparture("set_comment_review_vote");
			}

			/**
			 * Validates the API request's response.
			 *
			 * @author Leo Fajardo (leorw)
			 * @since 2.5.9
			 *
			 * @param array $api_request_info The API request parameters.
			 *
			 * @return array Failed requests information. Empty if there are no errors.
			 */
			function comment_review_submission_errors($api_request_info) {
				$request_params = $api_request_info['request_params'];
				$api_endpoint = $api_request_info['api_endpoint'];
				$api_response = $api_request_info['api_response'];

				$errors = array();

				if ( is_wp_error($api_response) ) {
					$errors = $api_response->errors;
				} else if ( 200 !== wp_remote_retrieve_response_code($api_response) ) {
					// Follow the way the WP_Error object saves the error messages to make the code simpler.
					$errors['invalid_response_code'] = array(
						'Invalid response code'
					);
				} else {
					$response_body = wp_remote_retrieve_body($api_response);

					$json_obj = false;

					// Retrieve the JSON string that is passed as a parameter to a callback function, e.g.: RW._rateCallback(JSON_STRING, ...);
					if ( preg_match("/(?<={)(.*)(?=})/", $response_body, $matches) ) {
						if ( !empty($matches) ) {
							$json_str = '{' . $matches[0] . '}';
							$json_obj = json_decode($json_str);
						}
					}

					if ( !is_object($json_obj) ) {
						$errors['invalid_response_string'] = array(
							'Invalid response string'
						);
					} else if ( !$json_obj->success ) {
						$errors['api_request_failed'] = array(
							$json_obj->msg
						);
					}
				}

				if ( empty($errors) ) {
					return array();
				}

				return array(
					'request_params' => $request_params,
					'api_request_errors' => $errors,
					'api_endpoint' => $api_endpoint
				);
			}

			/**
			 * Retrieves the current comment review mode settings.
			 *
			 * @author Leo Fajardo (@leorw)
			 * @since 2.5.9
			 *
			 * @return object
			 */
			function get_comment_review_mode_settings() {
				$comment_review_mode_settings = $this->GetOption( WP_RW__DB_OPTION_COMMENT_REVIEW_MODE_SETTINGS );
				if ( ! is_object( $comment_review_mode_settings ) ) {
					$comment_review_mode_settings = $this->_OPTIONS_DEFAULTS[ WP_RW__DB_OPTION_COMMENT_REVIEW_MODE_SETTINGS ];
				}

				return $comment_review_mode_settings;
			}

			/**
			 * Checks whether the selected rating mode in the "Comments" options tab is "Review".
			 *
			 * @author Leo Fajardo (@leorw)
			 * @since 2.5.9
			 *
			 * @return boolean
			 */
			function is_comment_review_mode() {
				$comment_ratings_mode_settings = $this->get_comment_ratings_mode_settings();

				return ( 'true' === $comment_ratings_mode_settings->comment_ratings_mode );
			}

			#endregion Comment Review Mode ------------------------------------------------------------------

			#region Admin-Only Comment Ratings Mode ------------------------------------------------------------------

			/**
			 * Retrieves the current comment ratings mode settings. If the mode is not set, set to comment reviews.
			 *
			 * @author Leo Fajardo (@leorw)
			 * @since 2.6.0
			 *
			 * @return object
			 */
			function get_comment_ratings_mode_settings() {
				$comment_ratings_mode_settings = $this->GetOption( WP_RW__DB_OPTION_IS_ADMIN_COMMENT_RATINGS_SETTINGS );
				if ( ! is_object( $comment_ratings_mode_settings ) ) {
					$comment_ratings_mode_settings = $this->_OPTIONS_DEFAULTS[ WP_RW__DB_OPTION_IS_ADMIN_COMMENT_RATINGS_SETTINGS ];

					$comment_review_mode_settings = get_comment_review_mode_settings();
					$comment_ratings_mode_settings->comment_ratings_mode = $comment_review_mode_settings->is_comment_review_mode ? 'true' : 'false';
				}

				return $comment_ratings_mode_settings;
			}

			/**
			 * Retrieves the current comment ratings mode.
			 *
			 * @author Leo Fajardo (@leorw)
			 * @since 2.6.0
			 *
			 * @return string
			 */
			function get_comment_ratings_mode() {
				$comment_ratings_mode_settings = $this->get_comment_ratings_mode_settings();

				return $comment_ratings_mode_settings->comment_ratings_mode;
			}

			/**
			 * Checks whether the selected rating mode in the "Comments" options tab is "Admin ratings only".
			 *
			 * @author Leo Fajardo (@leorw)
			 * @since 2.6.0
			 *
			 * @return boolean
			 */
			function is_comment_admin_ratings_mode() {
				$comment_ratings_mode_settings = $this->get_comment_ratings_mode_settings();

				return ( 'admin_ratings' === $comment_ratings_mode_settings->comment_ratings_mode );
			}

			#region Comments Editor Metabox

			/**
			 * Adds a rating metabox on the comment edit page.
			 *
			 * @author Leo Fajardo (@leorw)
			 * @since 2.6.0
			 */
			function add_comment_rating_metabox() {
				/**
				 * Check if the current user has admin privileges.
				 * Also check if the comment ratings mode is not "Reviews Ratings" which means that it is either "Comment Ratings" or "Admin ratings only".
				 */
				if ( current_user_can( 'manage_options' ) && ( ! $this->is_comment_review_mode() ) ) {
					add_meta_box( 'rw-comment-meta-box', WP_RW__NAME, array( &$this, 'show_comment_rating_metabox' ), 'comment', 'normal' );
				}
			}

			/**
			 * Loads the content of the comment rating metabox.
			 *
			 * @author Leo Fajardo (@leorw)
			 * @since 2.6.0
			 */
			function show_comment_rating_metabox() {
				rw_require_view('pages/admin/comment-metabox.php');
			}

			/**
			 * Saves the comment rating's read-only and active states.
			 *
			 * @author Leo Fajardo (@leorw)
			 * @since 2.6.0
			 *
			 * @param int $comment_id
			 */
			function save_comment_data( $comment_id ) {
				if ( RWLogger::IsOn() ) {
					$params = func_get_args();
					RWLogger::LogEnterence( 'save_comment_data', $params, true );
				}

				// Verify nonce.
				if ( ! isset( $_POST['rw_comment_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['rw_comment_meta_box_nonce'], basename( WP_RW__PLUGIN_FILE_FULL ) ) ) {
					return;
				}

				// Check whether this comment's rating is to be included.
				$include_rating = ( isset( $_POST['rw_include_comment_rating'] ) && '1' == $_POST['rw_include_comment_rating'] );

				// Checks whether this comment's rating is to be set to read-only.
				$readonly_rating = ( ! isset( $_POST['rw_readonly_comment_rating'] ) || '1' !== $_POST['rw_readonly_comment_rating'] );

				$this->add_to_visibility_list( $comment_id, array( 'comment' ), $include_rating );
				$this->SetOption( WP_RW__VISIBILITY_SETTINGS, $this->_visibilityList );

				// Add to or remove from the read-only list of comment IDs based on the state of the read-only checkbox.
				$this->add_to_readonly( $comment_id, array( 'comment' ), $readonly_rating );
				$this->SetOption(WP_RW__READONLY_SETTINGS, $this->_readonly_list);

				$this->_options->store();

				if ( RWLogger::IsOn() ) {
					RWLogger::LogDeparture( 'save_comment_data' );
				}
			}

			#endregion Comments Editor Metabox

			#endregion Admin-Only Comment Ratings Mode ------------------------------------------------------------------

			private function IsHideOnMobile()
			{
				RWLogger::LogEnterence("IsHideOnMobile");

				$rw_show_on_mobile = $this->GetOption(WP_RW__SHOW_ON_MOBILE);

				if (RWLogger::IsOn())
					RWLogger::Log("WP_RW__SHOW_ON_MOBILE", $rw_show_on_mobile);

				return (!$rw_show_on_mobile);
			}

			private function IsMobileDevice()
			{
				RWLogger::LogEnterence("IsMobileDevice");

				require_once(WP_RW__PLUGIN_DIR . "/vendors/class.mobile.detect.php");
				$detect = new Mobile_Detect();

				$is_mobile = $detect->isMobile();

				if (RWLogger::IsOn()){ RWLogger::Log("WP_RW__IS_MOBILE_CLIENT", ($is_mobile ? "true" : "false")); }

				return $is_mobile;
			}

			#region Notifications ------------------------------------------------------------------

			function SecretKeyUpdateConfirmNotice()
			{
				$this->Notice('You have successfully updated your Secret Key.', 'update-nag success');
			}

			function ClearCacheConfirmNotice()
			{
				$this->Notice('All cache was successfully purged.', 'update-nag success');
			}

			function RestoreSettingsConfirmNotice()
			{
				$this->Notice('Your settings has been successfully restored.', 'update-nag success');
			}

			function ClearRatingsConfirmNotice()
			{
				$this->Notice('All your ratings has been successfully deleted.', 'update-nag success');
			}

			function StartFreshConfirmNotice() {
				$this->Notice( 'You are fresh like a mentos! All your ratings has been successfully deleted and your settings are back to factory defaults.', 'update-nag success' );
			}

			function ConfirmationNotice()
			{
				if (!$this->is_admin)
					return;

				$url = $this->GetEmailConfirmationUrl();

				$this->Notice('New <i>Weekly Rating Reports</i> feature! <a href="' . $url . '" onclick="_rwwp.openConfirm(\'' . $url . '\'); return false;" target="_blank">Please confirm your email address now</a> to receive your upcoming Monday report.');
			}

			function InvalidEmailConfirmNotice()
			{
				$url = $this->GetEmailConfirmationUrl();

				$this->Notice('Email confirmation has failed. One of the confirmation parameters is invalid. <a href="' . $url . '" onclick="_rwwp.openConfirm(\'' . $url . '\'); return false;" target="_blank">Please try to confirm your email again</a>.');
			}

			function SuccessfulEmailConfirmNotice()
			{
				$this->Notice('W00t! You have successfully confirmed your email address.', 'update-nag success');
			}

			function ApiAccessBlockedNotice()
			{
				$this->Notice('Oops... your server (IP ' . WP_RW__SERVER_ADDR . ') is blocking the access to our API, therefore your license can NOT be synced. <br>Please contact your host to enable remote access to: <ul><li><code><a href="' . RW_API__ADDRESS . '" target="_blank">' . RW_API__ADDRESS . '</a></code></li><li><code><a href="' . WP_RW__ADDRESS . '" target="_blank">' . WP_RW__ADDRESS . '</a></code></li><li><code><a href="' . WP_RW__SECURE_ADDRESS . '" target="_blank">' . WP_RW__SECURE_ADDRESS . '</a></code></li></ul>');
			}

			function ApiUnauthorizedAccessNotice()
			{
				$this->Notice('Oops... seems like one of the authentication parameters is wrong. Update your Public Key, Secret Key & User ID, and try again.');
			}

			function LicenseSyncNotice()
			{
				$this->Notice('Ye-ha! Your license has been successfully synced.', 'update-nag success');
			}

			function LicenseSyncSameNotice()
			{
				$this->Notice('Hmm... it looks like your license remained the same. If you did upgrade, it\'s probably an issue on our side (sorry). Please contact us <a href="' . rw_get_site_url('/contact/?' . http_build_query(array('topic' => 'Report an Issue', 'email' => $this->account->user_email, 'site_id' => $this->account->site_id, 'user_id' => $this->account->user_id, 'website' => get_site_url(), 'platform' => 'wordpress', 'message' => 'I\'ve upgraded my account but when I try to Sync the License in my WordPress Dashboard -> Ratings -> Account, the license remains the same.' . "\n" . 'Your Upgraded Plan: [REPLACE WITH PLAN NAME]' . "\n" . 'Your PayPal Email: [REPLACE WITH PAYPAL ADDRESS]'))) . '" target="_blank">here</a>.');
			}

			function Notice($pNotice, $pType = 'update-nag')
			{
				?>
				<div class="<?php echo $pType ?> rw-notice"><span class="rw-slug"><b>rating</b><i>widget</i></span> <b class="rw-sep">&#9733;</b> <?php echo $pNotice ?></div>
			<?php
			}

			#endregion Notifications ------------------------------------------------------------------

			function UpdateSecret($new_secret) {
				RWLogger::LogEnterence( 'UpdateSecret' );

				$this->SetOption( WP_RW__DB_OPTION_SITE_SECRET_KEY, $new_secret );
				$this->_options->store();

				RWLogger::LogDeparture( 'UpdateSecret' );
			}

			function AccountPageLoad() {
				RWLogger::LogEnterence( 'AccountPageLoad' );

				if ( rw_request_is_action( 'delete_account' ) ) {
					check_admin_referer( 'delete_account' );

					RWLogger::Log( "AccountPageLoad", 'delete_account' );

					$this->fs->delete_account_event();

					$this->_options->clear(true);

					$this->ClearTransients();

					rw_redirect( '#' );
				}

				if ( rw_request_is_action( 'clear_cache' ) ) {
					check_admin_referer( 'clear_cache' );

					RWLogger::Log( "AccountPageLoad", 'clear_cache' );

					$this->ClearTransients();

					add_action( 'all_admin_notices', array( &$this, 'ClearCacheConfirmNotice' ) );
				}

				if ( rw_request_is_action( 'default_settings' ) ) {
					check_admin_referer( 'default_settings' );

					RWLogger::Log( "AccountPageLoad", 'default_settings' );

					$this->RestoreDefaultSettings();

					add_action( 'all_admin_notices', array( &$this, 'RestoreSettingsConfirmNotice' ) );
				}

				if ( rw_request_is_action( 'clear_ratings' ) ) {
					check_admin_referer( 'clear_ratings' );

					RWLogger::Log( "AccountPageLoad", 'clear_ratings' );

					rwapi()->call( '/ratings.json', 'DELETE' );

					$this->ClearTransients();

					add_action( 'all_admin_notices', array( &$this, 'ClearRatingsConfirmNotice' ) );
				}

				if ( rw_request_is_action( 'go_factory' ) ) {
					check_admin_referer( 'go_factory' );

					RWLogger::Log( "AccountPageLoad", 'go_factory' );

					rwapi()->call( '/ratings.json', 'DELETE' );

					$this->ClearTransients();

					$this->RestoreDefaultSettings();

					add_action( 'all_admin_notices', array( &$this, 'StartFreshConfirmNotice' ) );
				}

				$this->_update_account();
			}

			/**
			 * In a case of caching plugin installed, and if user's plan supports Rich-Snippets,
			 * clear cache every 24 hours.
			 */
			function ClearCache() {
				RWLogger::LogEnterence( "ClearCache" );

				if ( $this->fs->is_plan_or_trial__premium_only( 'professional' ) ) {
					$site_plan_update = $this->GetOption( WP_RW__DB_OPTION_SITE_PLAN_UPDATE, false, 0 );
					if ( $site_plan_update < ( time() - WP_RW__TIME_24_HOURS_IN_SEC ) ) {
						/*if ( function_exists( 'prune_super_cache' ) ) {
							prune_super_cache();
						} else */
						if ( function_exists( 'wp_cache_clear_cache' ) ) {
							wp_cache_clear_cache();
						}
					}
				}
			}

			/**
			 * Update rich snippets settings after upgrade to Professional.
			 *
			 * @author Vova Feldman (@svovaf)
			 * @since 2.5.7
			 */
			function after_premium_version_activation_hook() {
				if ( $this->fs->is_plan_or_trial__premium_only( 'professional' ) ) {
					// Only update the rich snippet settings if the query string
					// doesn't contain the 'schema_test' parameter.
					if ( ! isset( $_REQUEST['schema_test'] ) ) {
						$this->update_rich_snippet_settings__premium_only();
					}
				}
			}

			/**
			 * Retrieves the latest post's HTML content and checks the availability of the rich snippet properties.
			 *
			 * @author Leo Fajardo (@leorw)
			 * @since 2.5.2
			 */
			function update_rich_snippet_settings__premium_only() {
				if ( ! $this->fs->is_plan_or_trial( 'professional' ) ) {
					return;
				}

				RWLogger::LogEnterence( 'update_rich_snippet_settings__premium_only' );

				$update_rich_snippet_settings = false;

				$rich_snippet_settings = $this->get_rich_snippet_settings();
				if ( ! $rich_snippet_settings->timestamp ) {
					$update_rich_snippet_settings = true;
				} else {
					// Update the rich snippet settings if one week has already passed since the last update.
					if ( time() - $rich_snippet_settings->timestamp > WP_RW__TIME_WEEK_IN_SEC ) {
						$update_rich_snippet_settings = true;
					}
				}

				if ( ! $update_rich_snippet_settings ) {
					RWLogger::LogDeparture( 'update_rich_snippet_settings__premium_only' );

					return;
				}

				$reset_settings = true;

				if ( ! class_exists( 'DOMDocument' ) ) {
					if ( RWLogger::IsOn() ) {
						RWLogger::Log( 'DOMDocument', 'The DOM extension is not loaded.' );
					}
				} else {
					$recent_posts = wp_get_recent_posts( array( 'numberposts' => 1, 'post_type' => 'post' ) );
					if ( is_array( $recent_posts ) && 0 < count( $recent_posts ) ) {
						$recent_post = $recent_posts[0];

						$post_id = $recent_post['ID'];

						$permalink = get_permalink( $post_id );
						$permalink = esc_url( add_query_arg( array( 'schema_test' => true ), $permalink ) );

						if ( RWLogger::IsOn() ) {
							RWLogger::Log( 'permalink', $permalink );
						}

						$response = wp_remote_get( $permalink, array( 'timeout' => 5 ) );
						if ( RWLogger::IsOn() ) {
							RWLogger::Log( "wp_remote_get", 'Response: ' . var_export( $response, true ) );
						}

						$html = wp_remote_retrieve_body( $response );
						if ( RWLogger::IsOn() ) {
							RWLogger::Log( 'wp_remote_retrieve_body', 'HTML content length: ' . strlen( $html ) );
						}

						if ( ! empty( $html ) ) {
							$multirating_options = $this->get_multirating_options_by_class( 'blog-post' );
							if ( ! $multirating_options->show_summary_rating ) {
								$rating_container_class = 'rw-class-blog-post-criteria-1';
							} else {
								$urid                   = $this->get_rating_id_by_element( $post_id, 'blog-post', false );
								$rating_container_class = 'rw-urid-' . $urid;
							}

							try {
								$dom = new DOMDocument();
								@$dom->loadHTML( $html );

								$xpath = new DomXPath( $dom );

								// Find elements with "itemscope" attribute and whose "itemtype" attribute's value is "http://schema.org/Article"
								// and have div descendants that have $rating_container_class class.
								$xpath_query = "//div[contains(concat(' ', normalize-space(@class), ' '), ' {$rating_container_class} ')]/ancestor::*[@itemscope and @itemtype = 'http://schema.org/Article']";

								$article_wrapper_elements = $xpath->query( $xpath_query );
								if ( RWLogger::IsOn() ) {
									RWLogger::Log( 'total_article_elements: ' . $article_wrapper_elements->length );
								}

								if ( $article_wrapper_elements->length ) {
									$rich_snippet_settings->type_wrapper_available = true;

									$property_names = array_keys( $rich_snippet_settings->properties_availability );
									foreach ( $article_wrapper_elements as $article_wrapper_element ) {
										// Stop if all properties are already marked as available.
										if ( empty( $property_names ) ) {
											break;
										}

										foreach ( $property_names as $property_idx => $property_name ) {
											// Check if a rich snippet property exists within the current article wrapper element.
											$available                                                        = $this->rich_snippet_property_exists( $xpath, $article_wrapper_element, $property_name );
											$rich_snippet_settings->properties_availability[ $property_name ] = $available;

											if ( $available ) {
												unset( $property_names[ $property_idx ] );
											}
										}
									}

									$reset_settings = false;
								}
							} catch ( Exception $e ) {
								if ( RWLogger::IsOn() ) {
									RWLogger::Log( 'parse_html', 'Error: ' . $e->getMessage() );
								}
							}
						}
					}
				}

				if ( $reset_settings ) {
					$rich_snippet_settings->type_wrapper_available = false;
					foreach ( $rich_snippet_settings->properties_availability as $property_idx => $property_name ) {
						$rich_snippet_settings->properties_availability[ $property_idx ] = false;
					}
				}

				$rich_snippet_settings->timestamp = time();

				if ( RWLogger::IsOn() ) {
					RWLogger::Log( 'rich_snippet_settings', json_encode( $rich_snippet_settings ) );
				}

				$this->SetOption( WP_RW__DB_OPTION_RICH_SNIPPETS_SETTINGS, $rich_snippet_settings );
				$this->_options->store();

				RWLogger::LogDeparture( 'update_rich_snippet_settings__premium_only' );
			}

			/**
			 * Checks if the rich snippet property element whose name is specified
			 * by $prop_name exists within the wrapper article element.
			 *
			 * @author Leo Fajardo (@leorw)
			 * @since  2.5.2
			 *
			 * @param DomXPath $xpath
			 * @param DOMNode  $article_wrapper_element
			 * @param string   $property_name
			 *
			 * @return boolean
			 */
			function rich_snippet_property_exists($xpath, $article_wrapper_element, $property_name) {
				RWLogger::LogEnterence('rich_snippet_property_exists');
				try {
					// Find the ancestors with "itemscope" and "itemtype" attributes of the elements in the specified wrapper element
					// whose itemprop attribute's value is specified by $property_name.
					$ancestors = $xpath->query(".//*[@itemprop = '$property_name']/ancestor::*[@itemscope and @itemtype]", $article_wrapper_element);

					if ( $ancestors->length ) {
						$ancestor = $ancestors->item(0);

						if ( 'http://schema.org/Article' === $ancestor->getAttribute('itemtype') ) {
							return true;
						}
					}
				} catch (Exception $e) {
					if ( RWLogger::IsOn() ) {
						RWLogger::Log('rich_snippet_property_exists', 'Error: ' . $e->getMessage());
					}
				}

				RWLogger::LogDeparture('rich_snippet_property_exists');
				return false;
			}

			/**
			 * This function displays a message at the top of the current page
			 * when the site has reached 10, 100, or 1000 votes.
			 *
			 * @author Leo Fajardo (@leorw)
			 * @since 2.4.9
			 */
			function five_star_wp_rate_notice() {
				$min_votes_trigger = $this->GetOption(WP_RW__DB_OPTION_WP_RATE_NOTICE_MIN_VOTES_TRIGGER);
				$response = rwapi()->get("/votes/count.json", false, WP_RW__CACHE_TIMEOUT_DASHBOARD_STATS);
				if ( empty($response) ) {
					return;
				}

				if (!isset($response->error)) {
					$votes = $response->count;
					if ($votes >= $min_votes_trigger) {
						global $wp_version;
						$classes = 'rw-five-star-wp-rate-action update-nag';

						// Use additional class for the different versions of WordPress
						// in order to have the correct message styles.
						if ($wp_version < 3 ) {
							$classes .= ' updated';
						} else if ($wp_version >= 3.8 ) {
							$classes .= ' success';
						}

						// Retrieve the admin notice content
						$params = array('min_votes_trigger' => $min_votes_trigger);

						ob_start();
						rw_require_view('pages/admin/five-star-wp-rate-notice.php', $params);
						$message = ob_get_contents();
						ob_end_clean();

						// Display the message
						ratingwidget()->Notice($message, $classes);
					}
				}
			}

			public function ClearTransients()
			{
				global $wpdb;

				// Clear all rw transients.
				$wpdb->query(
					"DELETE FROM
                $wpdb->options
             WHERE
                option_name LIKE '_transient_rw%'"
				);
			}


			#region IDs Transformations ------------------------------------------------------------------

			/* Private
    -------------------------------------------------*/
			private static function Urid2Id($pUrid, $pSubLength = 1, $pSubValue = 1)
			{
				// Casting the value to integer is important to prevent a warning that is usually thrown by WordPress' caching code.
				return (int) round((double)substr($pUrid, 0, strlen($pUrid) - $pSubLength) - $pSubValue);
			}

			function _getPostRatingGuid($id = false, $criteria_id = false)
			{
				if (false === $id){ $id = get_the_ID(); }

				$urid = ($id + 1) . "0" . (false !== $criteria_id ? '-' . $criteria_id : '');

				if (RWLogger::IsOn()){
					RWLogger::Log("post-id", $id);
					RWLogger::Log("post-urid", $urid);
				}

				return $urid;
			}
			public static function Urid2PostId($pUrid)
			{
				return self::Urid2Id($pUrid);
			}

			private function _getCommentRatingGuid($id = false, $criteria_id = false)
			{
				if (false === $id){ $id = get_comment_ID(); }
				$urid = ($id + 1) . "1" . (false !== $criteria_id ? '-' . $criteria_id : '');

				if (RWLogger::IsOn()){
					RWLogger::Log("comment-id", $id);
					RWLogger::Log("comment-urid", $urid);
				}

				return $urid;
			}
			public static function Urid2CommentId($pUrid)
			{
				return self::Urid2Id($pUrid);
			}

			private function _getActivityRatingGuid($id = false)
			{
				if (false === $id){
					$id = bp_get_activity_id();
				}

				$urid = ($id + 1) . "2";

				if (RWLogger::IsOn()){
					RWLogger::Log("activity-id", $id);
					RWLogger::Log("activity-urid", $urid);
				}

				return $urid;
			}

			public static function Urid2ActivityId($pUrid)
			{
				return self::Urid2Id($pUrid);
			}

			private function _getForumPostRatingGuid($id = false, $criteria_id = false)
			{
				if (false === $id){
					$id = bp_get_the_topic_post_id();
				}

				$urid = ($id + 1) . "3" . (false !== $criteria_id ? '-' . $criteria_id : '');

				if (RWLogger::IsOn()){
					RWLogger::Log("forum-post-id", $id);
					RWLogger::Log("forum-post-urid", $urid);
				}

				return $urid;
			}

			public static function Urid2ForumPostId($pUrid)
			{
				return self::Urid2Id($pUrid);
			}

			function _getUserRatingGuid($id = false, $secondery_id = WP_RW__USER_SECONDERY_ID)
			{
				if (false === $id)
					$id = bp_displayed_user_id();

				$len = strlen($secondery_id);
				$secondery_id = ($len == 0) ? WP_RW__USER_SECONDERY_ID : (($len == 1) ? "0" . $secondery_id : substr($secondery_id, 0, 2));
				$urid = ($id + 1) . $secondery_id . "4";

				if (RWLogger::IsOn()){
					RWLogger::Log("user-id", $id);
					RWLogger::Log("user-secondery-id", $secondery_id);
					RWLogger::Log("user-urid", $urid);
				}

				return $urid;
			}

			public static function Urid2UserId($pUrid)
			{
				return self::Urid2Id($pUrid, 3);
			}

			#endregion IDs Transformations ------------------------------------------------------------------

			#region Plugin Options ------------------------------------------------------------------

			protected $_OPTIONS_DEFAULTS;
			protected function LoadDefaultOptions()
			{
				RWLogger::LogEnterence("LoadDefaultOptions");

				if (isset($this->_OPTIONS_DEFAULTS))
					return;

				$star = (object)array(
					'type' => 'star',
					'size' => 'medium',
					'theme' => 'star_flat_yellow'
				);

				$star_small = (object)array(
					'type' => 'star',
					'size' => 'small',
					'theme' => 'star_flat_yellow'
				);

				$readonly_star = clone $star;
				$readonly_star->readOnly = true;

				$thumbs = (object)array(
					'type' => 'nero',
					'theme' => 'thumbs_1',
				);

				$bp_thumbs = (object)array(
					'type' => 'nero',
					'theme' => 'thumbs_bp1',
				);

				$bp_like = (object)array(
					'type' => 'nero',
					'theme' => 'thumbs_bp1',
					'advanced' => (object)array(
						'nero' => (object)array(
							'showDislike' => false,
						)
					)
				);

				$small_bp_like = clone $bp_like;
				$small_bp_like->size = 'small';

				$readonly_bp_thumbs = clone $bp_thumbs;
				$readonly_bp_thumbs->readOnly = true;

				$top_left = (object)array('ver' => 'top', 'hor' => 'left');
				$bottom_left = (object)array('ver' => 'bottom', 'hor' => 'left');

				$default_multirating_options = (object) array(
					'criteria' => array(time() => array()),
					'summary_label' => __('Summary', WP_RW__ID),
					'show_summary_rating' => true,
				);

				$this->_OPTIONS_DEFAULTS = array(
					WP_RW__DB_OPTION_SITE_PUBLIC_KEY => false,
					WP_RW__DB_OPTION_SITE_ID => false,
					WP_RW__DB_OPTION_SITE_SECRET_KEY => false,

					WP_RW__LOGGER => false,
					WP_RW__DB_OPTION_TRACKING => false,
					WP_RW__DB_OPTION_WP_RATE_NOTICE_MIN_VOTES_TRIGGER => 10,
					WP_RW__DB_OPTION_STATS_UPDATED => false,
					WP_RW__DB_OPTION_RICH_SNIPPETS_SETTINGS => (object) array(
						'timestamp' => false,
						'type_wrapper_available' => false,
						'properties_availability' => array(
							'headline' => false,
							'image' => false,
							'datePublished' => false,
							'name' => false,
							'url' => false,
							'description' => false
						)
					),
					WP_RW__DB_OPTION_COMMENT_REVIEW_MODE_SETTINGS => (object) array(
						'is_comment_review_mode' => false,
						'failed_requests' => array()
					),
					WP_RW__DB_OPTION_IS_ADMIN_COMMENT_RATINGS_SETTINGS => (object) array(
						'comment_ratings_mode' => 'false' // "false" = comment ratings, "true" = reviews ratings, "admin_ratings" = admin-only ratings
					),
					WP_RW__IS_ACCUMULATED_USER_RATING => true,

					WP_RW__IDENTIFY_BY => 'laccount',
					WP_RW__FLASH_DEPENDENCY => true,
					WP_RW__SHOW_ON_MOBILE => true,

					WP_RW__SHOW_ON_ARCHIVE => true,
					WP_RW__SHOW_ON_CATEGORY => true,
					WP_RW__SHOW_ON_SEARCH => true,
					WP_RW__SHOW_ON_EXCERPT => true,


					WP_RW__FRONT_POSTS_ALIGN => $top_left,
					WP_RW__FRONT_POSTS_OPTIONS => $star,

					WP_RW__BLOG_POSTS_ALIGN => $bottom_left,
					WP_RW__BLOG_POSTS_OPTIONS => $star,

					WP_RW__COMMENTS_ALIGN => $bottom_left,
					WP_RW__COMMENTS_OPTIONS => $thumbs,

					WP_RW__PAGES_ALIGN => $bottom_left,
					WP_RW__PAGES_OPTIONS => $star,

					// BuddyPress
					WP_RW__ACTIVITY_BLOG_POSTS_ALIGN => $bottom_left,
					WP_RW__ACTIVITY_BLOG_POSTS_OPTIONS => $star_small,

					WP_RW__ACTIVITY_BLOG_COMMENTS_ALIGN => $bottom_left,
					WP_RW__ACTIVITY_BLOG_COMMENTS_OPTIONS => $bp_thumbs,

					WP_RW__ACTIVITY_UPDATES_ALIGN => $bottom_left,
					WP_RW__ACTIVITY_UPDATES_OPTIONS => $small_bp_like,

					WP_RW__ACTIVITY_COMMENTS_ALIGN => $bottom_left,
					WP_RW__ACTIVITY_COMMENTS_OPTIONS => $small_bp_like,

					// bbPress
					/*WP_RW__FORUM_TOPICS_ALIGN => $bottom_left,
                WP_RW__FORUM_TOPICS_OPTIONS => '{"type": "nero", "theme": "thumbs_bp1", "advanced": {"css": {"container": "background: #F4F4F4; padding: 4px 8px 1px 8px; margin-bottom: 2px; border-right: 1px solid #DDD; border-bottom: 1px solid #DDD; border-radius: 4px; -moz-border-radius: 4px; -webkit-border-radius: 4px;"}}}',*/
					WP_RW__FORUM_POSTS_ALIGN => $bottom_left,
					WP_RW__FORUM_POSTS_OPTIONS => $bp_thumbs,

					/*WP_RW__ACTIVITY_FORUM_TOPICS_ALIGN => $bottom_left,
                WP_RW__ACTIVITY_FORUM_TOPICS_OPTIONS => '{"type": "nero", "theme": "thumbs_bp1", "advanced": {"css": {"container": "background: #F4F4F4; padding: 4px 8px 1px 8px; margin-bottom: 2px; border-right: 1px solid #DDD; border-bottom: 1px solid #DDD; border-radius: 4px; -moz-border-radius: 4px; -webkit-border-radius: 4px;"}}}',*/

					WP_RW__ACTIVITY_FORUM_POSTS_ALIGN => $bottom_left,
					WP_RW__ACTIVITY_FORUM_POSTS_OPTIONS => $bp_thumbs,
					// User
					WP_RW__USERS_ALIGN => $bottom_left,
					WP_RW__USERS_OPTIONS => $star_small,
					// Posts
					WP_RW__USERS_POSTS_ALIGN => $bottom_left,
					WP_RW__USERS_POSTS_OPTIONS => $readonly_star,
					// Pages
					WP_RW__USERS_PAGES_ALIGN => $bottom_left,
					WP_RW__USERS_PAGES_OPTIONS => $readonly_star,
					// Comments
					WP_RW__USERS_COMMENTS_ALIGN => $bottom_left,
					WP_RW__USERS_COMMENTS_OPTIONS => $readonly_star,
					// Activity-Updates
					WP_RW__USERS_ACTIVITY_UPDATES_ALIGN => $bottom_left,
					WP_RW__USERS_ACTIVITY_UPDATES_OPTIONS => $readonly_star,
					// Avtivity-Comments
					WP_RW__USERS_ACTIVITY_COMMENTS_ALIGN => $bottom_left,
					WP_RW__USERS_ACTIVITY_COMMENTS_OPTIONS => $readonly_bp_thumbs,
					// Forum-Posts
					WP_RW__USERS_FORUM_POSTS_ALIGN => $bottom_left,
					WP_RW__USERS_FORUM_POSTS_OPTIONS => $readonly_bp_thumbs,

					WP_RW__VISIBILITY_SETTINGS => new stdClass(),
					WP_RW__READONLY_SETTINGS => new stdClass(),
					// By default, disable all activity ratings for un-logged users.
					WP_RW__AVAILABILITY_SETTINGS => (object)array(
						"activity-update" => 1,
						"activity-comment" => 1,
						"forum-post" => 1,
						"forum-reply" => 1,
						"new-forum-post" => 1,
						"user" => 1,
						"user-post" => 1,
						"user-comment" => 1,
						"user-page" => 1,
						"user-activity-update" => 1,
						"user-activity-comment" => 1,
						"user-forum-post" => 1
					),
					WP_RW__CATEGORIES_AVAILABILITY_SETTINGS => new stdClass(),

					WP_RW__CUSTOM_SETTINGS_ENABLED => new stdClass(),
					WP_RW__CUSTOM_SETTINGS => new stdClass(),
					WP_RW__MULTIRATING_SETTINGS => (object) array(
						'blog-post' => clone $default_multirating_options,
						'forum-post' => clone $default_multirating_options,
						'front-post' => clone $default_multirating_options,
						'comment' => clone $default_multirating_options,
						'page' => clone $default_multirating_options,
						'product' => clone $default_multirating_options
					)
				);

				RWLogger::LogDeparture("LoadDefaultOptions");
			}

			function MigrateOptions()
			{
				RWLogger::LogEnterence("MigrateOptions");

				$this->_options->clear();

				$site_public_key = get_option(WP_RW__DB_OPTION_SITE_PUBLIC_KEY);

				// Only migrate if there's a public key,
				// otherwise new user without options.
				if (!is_string($site_public_key))
					return;

				$options = array_keys($this->_OPTIONS_DEFAULTS);
				foreach ($options as $o)
				{
					$v = get_option($o);

					if (false !== $v)
					{
						if (0 === strpos($v, '{'))
							$this->_options->set_option($o, json_decode($v));
						else if ('true' == $v)
							$this->_options->set_option($o, true);
						else if ('false' == $v)
							$this->_options->set_option($o, false);
						else
							$this->_options->set_option($o, $v);
					}
				}

				// Save to new unified options record.
				$this->_options->store();

				RWLogger::LogDeparture("MigrateOptions");
			}

			function LoadOptions()
			{
				RWLogger::LogEnterence("LoadOptions");

				if ($this->_options->is_empty()) {
					$this->MigrateOptions();
				}

				RWLogger::LogDeparture("LoadOptions");
			}

			function GetOption($pOption, $pFlush = false, $pDefault = null)
			{
				if (null === $pDefault)
					$pDefault = isset($this->_OPTIONS_DEFAULTS[$pOption]) ? $this->_OPTIONS_DEFAULTS[$pOption] : false;

				return $this->_options->get_option($pOption, $pDefault);
			}

			function UnsetOption($pOption)
			{
				$this->_options->unset_option($pOption);
			}

			function SetOption($pOption, $pValue)
			{
				$this->_options->set_option($pOption, $pValue);
			}

			function store_options()
			{
				$this->_options->store();
			}

			#endregion Plugin Options ------------------------------------------------------------------

			#region API ------------------------------------------------------------------

			function GenerateToken($pTimestamp, $pServerCall = false)
			{
				if (RWLogger::IsOn()){ $params = func_get_args(); RWLogger::LogEnterence("GenerateToken", $params, true); }

				$ip = (!$pServerCall) ? WP_RW__CLIENT_ADDR : WP_RW__SERVER_ADDR;

				if ($pServerCall)
				{
					if (RWLogger::IsOn()){
						RWLogger::Log("ServerToken", "ServerToken");
						RWLogger::Log("ServerIP", $ip);
					}
				}
				else
				{
					if (RWLogger::IsOn()){
						RWLogger::Log("ClientToken", "ClientToken");
						RWLogger::Log("ClientIP", $ip);
					}
				}

				$token = md5($pTimestamp . $this->account->site_secret_key);

				if (RWLogger::IsOn()){ RWLogger::Log("TOKEN", $token); }

				if (RWLogger::IsOn()){ $params = func_get_args(); RWLogger::LogDeparture("GenerateToken", $token); }

				return $token;
			}

			function AddToken(&$pData, $pServerCall = false)
			{
				RWLogger::LogEnterence("AddToken");

				$timestamp = time();
				$token = $this->GenerateToken($timestamp, $pServerCall);
				$pData["timestamp"] = $timestamp;
				$pData["token"] = $token;

				return $pData;
			}

			function RemoteCall($pPage, &$pData, $pExpiration = false)
			{
				if (RWLogger::IsOn())
				{
					$params = func_get_args(); RWLogger::LogEnterence("RemoteCall", $params, true);
					RWLogger::Log("RemoteCall", 'Address: ' . WP_RW__ADDRESS . "/{$pPage}");
				}

				if (false === WP_RW__CACHING_ON)
					// No caching on debug mode.
					$pExpiration = false;

				$cacheKey = '';
				if (false !== $pExpiration)
				{
					// Calc cache index key.
					$cacheKey = 'rw_' . md5(var_export($pData, true));

					// Try to get cached item.
					$value = get_transient($cacheKey);

					if (RWLogger::IsOn())
					{
						RWLogger::Log("RemoteCall", "TRANSIENT_KEY - " . $cacheKey);
						RWLogger::Log("RemoteCall", "TRANSIENT_VAL - " . $value);
					}

					// If found returned cached value.
					if (false !== $value)
					{
						if (RWLogger::IsOn())
							RWLogger::Log('RemoteCall', 'IS_CACHED: TRUE');

						return $value;
					}
				}

				if ($this->fs->is_paying_or_trial__premium_only()) {
					if ( RWLogger::IsOn() ) {
						RWLogger::Log( "RemoteCall", "SECURE" );
					}

					$this->AddToken( $pData, true );
				}

				if (RWLogger::IsOn())
				{
					RWLogger::Log('REMOTE_CALL_DATA', 'IS_CACHED: FALSE');
					RWLogger::Log("RemoteCall", 'REMOTE_CALL_DATA: ' . var_export($pData, true));
					RWLogger::Log("RemoteCall", 'Query: "' . WP_RW__ADDRESS . "/{$pPage}?" . http_build_query($pData) . '"');
				}

				if (RWLogger::IsOn())
					RWLogger::Log("wp_remote_post", "exist");

				if (isset($_REQUEST['XDEBUG_SESSION']))
				{
					$pPage = add_query_arg('XDEBUG_SESSION', $_REQUEST['XDEBUG_SESSION'], $pPage);
				}

				$rw_ret_obj = wp_remote_post(WP_RW__ADDRESS . "/{$pPage}", array('body' => $pData));

				if (is_wp_error($rw_ret_obj))
				{
					$this->errors = $rw_ret_obj;

					if (RWLogger::IsOn()){ RWLogger::Log("ret_object", var_export($rw_ret_obj, true)); }

					return false;
				}

				$rw_ret_obj = wp_remote_retrieve_body($rw_ret_obj);

				if (RWLogger::IsOn())
					RWLogger::Log("ret_object", var_export($rw_ret_obj, true));

				if (false !== $pExpiration && !empty($cacheKey))
					set_transient($cacheKey, $rw_ret_obj, $pExpiration);

				return $rw_ret_obj;
			}

			function QueueRatingData($urid, $title, $permalink, $rclass)
			{
				RWLogger::LogEnterence('QueueRatingData');

				if (isset(self::$ratings[$urid])) {
					RWLogger::Log('QueueRatingData', 'Rating ' . $urid .' already queued');
					return self::$ratings[$urid];
				}

				RWLogger::Log('QueueRatingData', 'Queue: urid=' . $urid .'; title=' . $title . '; rclass=' . $rclass . ';');

				$permalink = (mb_strlen($permalink) > 512) ? trim(mb_substr($permalink, 0, 512)) . '...' : $permalink;
				self::$ratings[$urid] = array("title" => $title, "permalink" => $permalink, "rclass" => $rclass);

				return self::$ratings[$urid];
			}

			#endregion API ------------------------------------------------------------------

			/* Public Static
    -------------------------------------------------*/
			var $_TOP_RATED_WIDGET_LOADED = false;
			function TopRatedWidgetLoaded()
			{
				$this->_TOP_RATED_WIDGET_LOADED = true;
			}

			#region Admin Page Settings ------------------------------------------------------------------

			function rw_admin_menu_icon_css()
			{
				rw_require_view('/pages/admin/menu-item.php');
			}

			function InitScriptsAndStyles()
			{
				global $pagenow;

				// wp_enqueue_script( 'rw-test', "/wp-admin/js/rw-test.js", array( 'jquery-ui-sortable', 'jquery-ui-draggable', 'jquery-ui-droppable' ), false, 1 );
				rw_enqueue_style('rw_wp_admin', 'wordpress/admin.css');
				rw_enqueue_script('rw_wp_admin', 'wordpress/admin.js');

				// Enqueue the stylesheets for the metabox rating
				if ($this->admin_page_has_rating_metabox()) {
					rw_enqueue_style('rw-admin-rating', WP_RW__PLUGIN_URL . 'resources/css/admin-rating.css');
				}

				// Enqueue the top-rated shortcode and dashboard stats widget stylesheets
				if ($this->account->is_registered()) {
					if ($this->admin_page_has_editor()) {
						rw_enqueue_style('rw-toprated-shortcode-style', WP_RW__PLUGIN_URL . 'resources/css/toprated-shortcode.css');
					}

					if ('index.php' === $pagenow) {
						rw_enqueue_style('rw-dashboard-stats', WP_RW__PLUGIN_URL . 'resources/css/dashboard-stats.css');
					}

					$min_votes_trigger = $this->GetOption(WP_RW__DB_OPTION_WP_RATE_NOTICE_MIN_VOTES_TRIGGER);
					if (-1 !== $min_votes_trigger) {
						// Enqueue the script that handles the updating of the minimum votes required for
						// displaying the "5-star WP rate" message box in the top of every page.
						rw_enqueue_script('rw-five-star-wp-rate-notice-js', WP_RW__PLUGIN_URL . 'resources/js/five-star-wp-rate-notice.js');

						// "5-star WP rate" message styles
						rw_enqueue_style('rw-five-star-wp-rate-notice-style', WP_RW__PLUGIN_URL . 'resources/css/five-star-wp-rate-notice.css');
					}
				}

				if (!$this->_inDashboard)
					return;

				// Enqueue JS.
				wp_enqueue_script('jquery');
				wp_enqueue_script('json2');

				// Enqueue CSS stylesheets.
				rw_enqueue_style('rw_wp_style', 'wordpress/style.css');

				// rw_enqueue_style('rw', 'settings.php');
				rw_enqueue_style('rw_fonts', add_query_arg(array('family' => 'Noto+Sans:400,700,400italic,700italic'), WP_RW__PROTOCOL . '://fonts.googleapis.com/css'));

				rw_register_script('rw', 'index.php');

				if (!$this->account->is_registered())
				{
					// Account activation page includes.
					rw_enqueue_script('rw_wp_validation', 'rw/validation.js');
					rw_enqueue_script('rw');
					// rw_enqueue_script('rw_wp_signup', 'wordpress/signup.php');
					wp_enqueue_script('jquery-postmessage', plugins_url('resources/js/jquery.ba-postmessage.min.js' ,__FILE__ ));
				}
				else
				{
					if ('rating-widget-addons' === $_GET['page']) {
						rw_enqueue_script('jquery-ui-dialog');
						rw_enqueue_style('wp-jquery-ui-dialog');

						// Enqueue the add-ons page CSS
						rw_enqueue_style('rw-addons-style', WP_RW__PLUGIN_URL . 'resources/css/addons.css');
					} else if ('rating-widget-affiliation' === $_GET['page']) {
						// Enqueue the affiliation page CSS
						rw_enqueue_style('rw-affiliation-style', WP_RW__PLUGIN_URL . 'resources/css/affiliation.css');
					} else {
						// Settings page includes.
						rw_enqueue_script('rw_cp', 'vendors/colorpicker.js');
						rw_enqueue_script('rw_cp_eye', 'vendors/eye.js');
						rw_enqueue_script('rw_cp_utils', 'vendors/utils.js');
						rw_enqueue_script('rw');
						rw_enqueue_script('rw_wp', 'wordpress/settings.js');

						// Include Chosen files.
						rw_enqueue_script('rw_chosen', 'https://cdnjs.cloudflare.com/ajax/libs/chosen/1.1.0/chosen.jquery.min.js');
						rw_enqueue_style('rw_chosen', 'https://cdnjs.cloudflare.com/ajax/libs/chosen/1.1.0/chosen.min.css');

						// Reports includes.
						rw_enqueue_style('rw_cp', 'colorpicker.php');
						rw_enqueue_script('jquery-ui-datepicker', 'vendors/jquery-ui-1.8.9.custom.min.js');
						rw_enqueue_style('jquery-theme-smoothness', 'vendors/jquery/smoothness/jquery.smoothness.css');
						rw_enqueue_style('rw_external', 'style.css?all=t');
						rw_enqueue_style('rw_wp_reports', 'wordpress/reports.php');

						// Load the live preview styles
						$class = isset($_GET['rating']) ? rtrim($_GET['rating'], 's') : '';
						if (empty($class) && 'rating-widget' == $_GET['page']) {
							$class = 'blog-post';
						} else if (empty($class) && 'rating-widget-woocommerce' == $_GET['page']) {
							$class = 'product';
						} else if (empty($class) && 'rating-widget-bbpress' == $_GET['page']) {
							$class = 'forum-post';
						}

						if ($this->has_multirating_options($class)) {
							// Enqueue live preview JS and CSS
							rw_enqueue_script('rw-js-live-preview', WP_RW__PLUGIN_URL . '/resources/js/live-preview.js');
							rw_enqueue_style('rw-live-preview', WP_RW__PLUGIN_URL . 'resources/css/live-preview.css');
						}
					}
				}
			}

			#endregion Admin Page Settings ------------------------------------------------------------------

			/**
			 * Adds the necessary stylesheet
			 */
			function init_site_styles() {
				if (!wp_script_is('jquery')) {
					wp_enqueue_script('jquery');
				}

				rw_enqueue_style('rw-site-rating', WP_RW__PLUGIN_URL . 'resources/css/site-rating.css');
			}

			/**
			 * Checks if the post edit page has rating metabox
			 * for loading the necessary scripts and styles
			 * @return boolean
			 */
			function admin_page_has_rating_metabox() {
				global $pagenow;

				$post_type = get_post_type();

				// Check if the user is viewing the edit or the create post page
				if ('post.php' == $pagenow || 'post-new.php' == $pagenow) {

					// Check if the post type is supported
					if (in_array($post_type, array('post', 'page', 'product'))) {
						return true;
					}
				}

				// Return the default: no rating metabox
				return false;
			}

			/**
			 * Retrieves the options of this type
			 * @param string $class
			 * @return object
			 */
			function get_options_by_class($class) {
				switch ($class) {
					case 'blog-post':
						$options = $this->GetOption(WP_RW__BLOG_POSTS_OPTIONS);
						break;
					case 'forum-post':
					case 'forum-reply':
						$options = $this->GetOption(WP_RW__FORUM_POSTS_OPTIONS);
						break;
					case 'front-post':
						$options = $this->GetOption(WP_RW__FRONT_POSTS_OPTIONS);
						break;
					case 'comment':
						$options = $this->GetOption(WP_RW__COMMENTS_OPTIONS);
						break;
					case 'page':
						$options = $this->GetOption(WP_RW__PAGES_OPTIONS);
						break;
					case 'product':
						$options = $this->GetOption(WP_RW__WOOCOMMERCE_PRODUCTS_OPTIONS);
						break;
					default:
						$options = array();
				}

				return $options;
			}

			/**
			 * Retrieves the multi-rating options of this type
			 *
			 * @author Leo Fajardo (@leorw)
			 * @param string $rclass option type
			 * @return object
			 */
			function get_multirating_options_by_class($rclass) {
				if ('forum-reply' === $rclass) {
					$rclass = 'forum-post';
				}

				$multirating_settings_list = $this->GetOption(WP_RW__MULTIRATING_SETTINGS);

				// If this class has no options set,
				// load the default options to avoid issues in the
				// site, live preview, and post edit meta boxes.
				if (!isset($multirating_settings_list->{$rclass})) {
					$default_multirating_settings = $this->_OPTIONS_DEFAULTS[WP_RW__MULTIRATING_SETTINGS];
					if (isset($default_multirating_settings->{$rclass})) {
						$multirating_settings_list->{$rclass} = $default_multirating_settings->{$rclass};
					}
				}

				return $multirating_settings_list->{$rclass};
			}

			/**
			 * Retrieves the current rich snippet settings
			 *
			 * @author Leo Fajardo (@leorw)
			 * @since 2.5.2
			 *
			 * @return object
			 */
			function get_rich_snippet_settings() {
				$rich_snippet_settings = $this->GetOption(WP_RW__DB_OPTION_RICH_SNIPPETS_SETTINGS);
				if (!$rich_snippet_settings) {
					$rich_snippet_settings = new stdClass();
				}

				$default_rich_snippet_settings = $this->_OPTIONS_DEFAULTS[WP_RW__DB_OPTION_RICH_SNIPPETS_SETTINGS];

				if ( !isset($rich_snippet_settings->properties_availability) ) {
					$rich_snippet_settings->properties_availability = $default_rich_snippet_settings->properties_availability;
				} else {
					foreach ($default_rich_snippet_settings->properties_availability as $name => $value ) {
						if ( !isset($rich_snippet_settings->properties_availability[$name]) ) {
							$rich_snippet_settings->properties_availability[$name] = $value;
						}
					}
				}

				return $rich_snippet_settings;
			}

			/**
			 * Checks if this option type supports multi-rating.
			 *
			 * @param string $class
			 *
			 * @return boolean
			 */
			function has_multirating_options($class) {
				return (in_array($class, array('blog-post', 'front-post', 'comment', 'page', 'product', 'forum-post', 'forum-reply')));
			}

			function ActivationNotice()
			{
				$this->Notice('<a href="edit.php?page=' . WP_RW__ADMIN_MENU_SLUG . '">Activate your account now</a> to start seeing the ratings.');
			}

			function admin_menu() {
				$this->is_admin = (bool) current_user_can( 'manage_options' );

				if ( ! $this->is_admin ) {
					return;
				}

				$pageLoaderFunction = 'SettingsPage';
//				if ( ! $this->fs->is_registered() ) {
//					$pageLoaderFunction = 'rw_user_key_page';

//					if ( empty( $_GET['page'] ) || WP_RW__ADMIN_MENU_SLUG != $_GET['page'] ) {
//						add_actÅiÅoÅnÅ( 'all_admin_notices', array( &$this, 'ActivationNotice' ) );
//					}
//				}

				if ( $this->account->is_registered() && ! $this->account->has_owner() ) {
					if ( ! $this->_inDashboard || ! $this->TryToConfirmEmail() ) {
						add_action( 'all_admin_notices', array( &$this, 'ConfirmationNotice' ) );
					}
				}

				$title = WP_RW__NAME . ' ' . __( 'Settings', WP_RW__ID );

				add_options_page( $title, WP_RW__NAME, 'edit_posts', WP_RW__ADMIN_MENU_SLUG, array(
					&$this,
					$pageLoaderFunction
				) );

				if ( function_exists( 'add_object_page' ) ) // WP 2.7+
				{
					add_object_page( $title, WP_RW__NAME, 'edit_posts', WP_RW__ADMIN_MENU_SLUG, array(
						&$this,
						$pageLoaderFunction
					), WP_RW__PLUGIN_URL . "icon.png" );
				} else {
					add_management_page( $title, WP_RW__NAME, 'edit_posts', WP_RW__ADMIN_MENU_SLUG, array(
						&$this,
						$pageLoaderFunction
					) );
				}

				$this->SetupMenuItems();
			}

			/**
			 * @var RW_AbstractExtension[]
			 */
			private $_extensions = array();

			/**
			 * @return RW_AbstractExtension[]
			 */
			function GetExtensions()
			{
				return $this->_extensions;
			}

			function RegisterExtension(RW_AbstractExtension $extension)
			{
				$slug = $extension->GetSlug();

				$this->_extensions[$slug] = $extension;
			}

			function SetupMenuItems()
			{
				RWLogger::LogEnterence("SetupMenuItems");

				$submenu = array();

				// Basic settings.
				$submenu[] = array(
					'menu_title' => __('Settings', WP_RW__ID),
					'function' => 'SettingsPage',
					'slug' => '',
				);

				// Append registered setting menu items.
				foreach ($this->_extensions as $extension)
					if ($extension->HasSettingsMenu())
						$submenu[] = $extension->GetSettingsMenuItem();

				if ($this->IsBuddyPressInstalled())
					// BuddyPress settings.
					$submenu[] = array(
						'menu_title' => 'BuddyPress',
						'function' => 'SettingsPage',
					);

				if ($this->fs->is__premium_only()) {
					if ( $this->IsBBPressInstalled() ) // bbPress settings.
					{
						$submenu[] = array(
							'menu_title' => 'bbPress',
							'function'   => 'SettingsPage',
						);
					}
				}

				if (false === is_active_widget(false, false, strtolower('RatingWidgetPlugin_TopRatedWidget'), true))
					// Top-Rated Promotion Page.
					$submenu[] = array(
						'menu_title' => __('Top-Rated Widget', WP_RW__ID),
						'function' => 'TopRatedSettingsPageRender',
						'load_function' => 'TopRatedSettingsPageLoad',
						'slug' => 'toprated',
					);

				// Reports.
				$submenu[] = array(
					'menu_title' => __('Reports', WP_RW__ID),
					'function' => 'ReportsPageRender',
				);

				// Advanced settings.
				$submenu[] = array(
					'menu_title' => __('Advanced', WP_RW__ID),
					'function' => 'AdvancedSettingsPageRender',
				);

				// Affiliation application page.
				$submenu[] = array(
					'menu_title' => __('Affiliation', WP_RW__ID),
					'function' => 'affiliation_settings_page_render',
				);
				
				/*
				// Add Ons page
				$submenu[] = array(
					'menu_title' => __('Add Ons', WP_RW__ID),
					'function' => 'addons_settings_page_render',
					'slug' => 'addons'
				);
				*/

				$submenu = apply_filters( 'ratingwidget_dashboard_submenus', $submenu );
				
				foreach ($submenu as $item)
				{
					$this->fs->add_submenu_item(
						$item['menu_title'],
						is_array( $item['function'] ) ? $item['function'] : array(&$this, $item['function']),
						__('Ratings', WP_RW__ID) . '&ndash;' . $item['menu_title'],
						'edit_posts',
						isset($item['slug']) ? $item['slug'] : false,
						(isset($item['load_function']) && !empty($item['load_function'])) ? ( is_array( $item['load_function'] ) ? $item['load_function'] : array( &$this, $item['load_function'])) : false
					);
				}
			}

			/**
			 * @deprecated Old sign-up page callback.
			 */
			function SignUpPageLoad()
			{
				if ($this->fs->is_registered())
					return;

				if ('post' === strtolower($_SERVER['REQUEST_METHOD']) && isset($_POST['action']) && 'account' === $_POST['action'])
				{
					$this->SetOption(WP_RW__DB_OPTION_OWNER_ID, $_POST['user_id']);
					$this->SetOption(WP_RW__DB_OPTION_OWNER_EMAIL, $_POST['user_email']);
					$this->SetOption(WP_RW__DB_OPTION_SITE_ID, $_POST['site_id']);
					$this->SetOption(WP_RW__DB_OPTION_SITE_PUBLIC_KEY, $_POST['public_key']);
					$this->SetOption(WP_RW__DB_OPTION_SITE_SECRET_KEY, $_POST['secret_key']);

					$this->SetOption(WP_RW__DB_OPTION_TRACKING, (isset($_POST['tracking']) && '1' == $_POST['tracking']));

					$this->_options->store();

					// Reload the page with the keys.
					rw_admin_redirect();
				}
			}

			#region Reports ------------------------------------------------------------------

			private static function _getAddFilterQueryString($pQuery, $pName, $pValue)
			{
				$pos = strpos($pQuery, "{$pName}=");
				if (false !== $pos)
				{
					$end = $pos + strlen("{$pName}=");
					$cur = $end;
					$max = strlen($pQuery);
					while ($cur < $max && $pQuery[$cur] !== "&"){
						$cur++;
					}

					$pQuery = substr($pQuery, 0, $end) . urlencode($pValue) . substr($pQuery, $cur);
				}
				else
				{
					$pQuery .= (($pQuery === "") ? "" : "&") . "{$pName}=" . urlencode($pValue);
				}

				return $pQuery;
			}

			private static function _getRemoveFilterFromQueryString($pQuery, $pName)
			{
				$pos = strpos($pQuery, "{$pName}=");

				if (false === $pos){ return $pQuery; }

				$end = $pos + strlen("{$pName}=");
				$cur = $end;
				$max = strlen($pQuery);
				while ($cur < $max && $pQuery[$cur] !== "&"){
					$cur++;
				}

				if ($pos > 0 && $pQuery[$pos - 1] === "&"){ $pos--; }

				return substr($pQuery, 0, $pos) . substr($pQuery, $cur);
			}

			function rw_general_report_page__premium_only() {
				if ( ! $this->fs->is_plan_or_trial( 'professional' ) ) {
					return;
				}
				if ( RWLogger::IsOn() ) {
					$params = func_get_args();
					RWLogger::LogEnterence( "rw_general_report_page__premium_only", $params );
				}

				$elements  = isset( $_REQUEST["elements"] ) ? $_REQUEST["elements"] : "posts";
				$orderby   = isset( $_REQUEST["orderby"] ) ? $_REQUEST["orderby"] : "created";
				$order     = isset( $_REQUEST["order"] ) ? $_REQUEST["order"] : "DESC";
				$date_from = isset( $_REQUEST["from"] ) ? $_REQUEST["from"] : date( WP_RW__DEFAULT_DATE_FORMAT, time() - WP_RW__PERIOD_MONTH );
				$date_to   = isset( $_REQUEST["to"] ) ? $_REQUEST["to"] : date( WP_RW__DEFAULT_DATE_FORMAT );
				$rw_limit  = isset( $_REQUEST["limit"] ) ? max( WP_RW__REPORT_RECORDS_MIN, min( WP_RW__REPORT_RECORDS_MAX, $_REQUEST["limit"] ) ) : WP_RW__REPORT_RECORDS_MIN;
				$rw_offset = isset( $_REQUEST["offset"] ) ? max( 0, (int) $_REQUEST["offset"] ) : 0;

				switch ( $elements ) {
					case "activity-updates":
						$rating_options = WP_RW__ACTIVITY_UPDATES_OPTIONS;
						$rclass         = "activity-update";
						break;
					case "activity-comments":
						$rating_options = WP_RW__ACTIVITY_COMMENTS_OPTIONS;
						$rclass         = "activity-comment";
						break;
					case "forum-posts":
						$rating_options = WP_RW__FORUM_POSTS_OPTIONS;
						$rclass         = "forum-post,new-forum-post";
						break;
					case "forum-replies":
						$rating_options = WP_RW__FORUM_POSTS_OPTIONS;
						$rclass         = "forum-reply";
						break;
					case "users":
						$rating_options = WP_RW__USERS_OPTIONS;
						$rclass         = "user";
						break;
					case "comments":
						$rating_options = WP_RW__COMMENTS_OPTIONS;
						$rclass         = "comment,new-blog-comment";
						break;
					case "pages":
						$rating_options = WP_RW__PAGES_OPTIONS;
						$rclass         = "page";
						break;
					case "posts":
					default:
						$rating_options = WP_RW__BLOG_POSTS_OPTIONS;
						$rclass         = "front-post,blog-post,new-blog-post";
						break;
				}

				$rating_options = $this->GetOption( $rating_options );
				$rating_type    = isset( $rating_options->type ) ? $rating_options->type : 'star';
				$rating_stars   = ( $rating_type === "star" ) ?
					( ( isset( $rating_options->advanced ) && isset( $rating_options->advanced->star ) && isset( $rating_options->advanced->star->stars ) ) ? $rating_options->advanced->star->stars : WP_RW__DEF_STARS ) :
					false;

				$details = array(
					"uid"           => $this->account->site_public_key,
					"rclasses"      => $rclass,
					"orderby"       => $orderby,
					"order"         => $order,
					"since_updated" => "{$date_from} 00:00:00",
					"due_updated"   => "{$date_to} 23:59:59",
					"limit"         => $rw_limit + 1,
					"offset"        => $rw_offset,
				);

				$rw_ret_obj = $this->RemoteCall( "action/report/general.php", $details, WP_RW__CACHE_TIMEOUT_REPORT );

				if ( false === $rw_ret_obj ) {
					return false;
				}

				// Decode RW ret object.
				$rw_ret_obj = json_decode( $rw_ret_obj );

				if ( RWLogger::IsOn() ) {
					RWLogger::Log( "ret_object", var_export( $rw_ret_obj, true ) );
				}

				if ( false == $rw_ret_obj->success ) {
					$this->rw_report_example_page();

					return false;
				}

				// Override token to client's call token for iframes.
				$this->AddToken( $details, false );

				$empty_result = ( ! isset( $rw_ret_obj->data ) || ! is_array( $rw_ret_obj->data ) || 0 == count( $rw_ret_obj->data ) );
				?>
				<div class="wrap rw-dir-ltr rw-report">
				<?php $this->Notice( '<strong style="color: red;">Note: data may be delayed 30 minutes.</strong>' ); ?>
				<form method="post" action="">
				<div class="tablenav">
					<div>
						<span><?php _e( 'Date Range', WP_RW__ID ) ?>:</span>
						<input type="text" value="<?php echo $date_from; ?>" id="rw_date_from" name="rw_date_from"
						       style="width: 90px; text-align: center;"/>
						-
						<input type="text" value="<?php echo $date_to; ?>" id="rw_date_to" name="rw_date_to"
						       style="width: 90px; text-align: center;"/>
						<script type="text/javascript">
							jQuery.datepicker.setDefaults({
								dateFormat: "yy-mm-dd"
							});

							jQuery("#rw_date_from").datepicker({
								maxDate : 0,
								onSelect: function (dateText, inst) {
									jQuery("#rw_date_to").datepicker("option", "minDate", dateText);
								}
							});
							jQuery("#rw_date_from").datepicker("setDate", "<?php echo $date_from;?>");

							jQuery("#rw_date_to").datepicker({
								minDate : "<?php echo $date_from;?>",
								maxDate : 0,
								onSelect: function (dateText, inst) {
									jQuery("#rw_date_from").datepicker("option", "maxDate", dateText);
								}
							});
							jQuery("#rw_date_to").datepicker("setDate", "<?php echo $date_to;?>");
						</script>
						<span><?php _e( 'Element', WP_RW__ID ) ?>:</span>
						<select id="rw_elements">
							<?php
								$select = array(
									__( 'Posts', WP_RW__ID )    => "posts",
									__( 'Pages', WP_RW__ID )    => "pages",
									__( 'Comments', WP_RW__ID ) => "comments"
								);

								if ( $this->IsBuddyPressInstalled() ) {
									$select[ __( 'Activity-Updates', WP_RW__ID ) ]  = "activity-updates";
									$select[ __( 'Activity-Comments', WP_RW__ID ) ] = "activity-comments";
									$select[ __( 'Users-Profiles', WP_RW__ID ) ]    = "users";

									if ( $this->IsBBPressInstalled() ) {
										$select[ __( 'Forum-Posts', WP_RW__ID ) ] = "forum-posts";
									}
								}

								foreach ( $select as $option => $value ) {
									$selected = '';
									if ( $value === $elements ) {
										$selected = ' selected="selected"';
									}
									?>
									<option
										value="<?php echo $value; ?>"<?php echo $selected; ?>><?php echo $option; ?></option>
								<?php
								}
							?>
						</select>
						<span><?php _e( 'Order By', WP_RW__ID ) ?>:</span>
						<select id="rw_orderby">
							<?php
								$select = array(
									"title"   => __( 'Title', WP_RW__ID ),
									"urid"    => __( 'Id', WP_RW__ID ),
									"created" => __( 'Start Date', WP_RW__ID ),
									"updated" => __( 'Last Update', WP_RW__ID ),
									"votes"   => __( 'Votes', WP_RW__ID ),
									"avgrate" => __( 'Average Rate', WP_RW__ID ),
								);
								foreach ( $select as $value => $option ) {
									$selected = '';
									if ( $value == $orderby ) {
										$selected = ' selected="selected"';
									}
									?>
									<option
										value="<?php echo $value; ?>" <?php echo $selected; ?>><?php echo $option; ?></option>
								<?php
								}
							?>
						</select>
						<input class="button-secondary action" type="button"
						       value="<?php _e( "Show", WP_RW__ID ); ?>"
						       onclick="top.location = RWM.enrichQueryString(top.location.href, ['from', 'to', 'orderby', 'elements'], [jQuery('#rw_date_from').val(), jQuery('#rw_date_to').val(), jQuery('#rw_orderby').val(), jQuery('#rw_elements').val()]);"/>
					</div>
				</div>
				<br/>
				<table class="widefat rw-chart-title">
					<thead>
					<tr>
						<th scope="col" class="manage-column"><?php _e( 'Votes Timeline', WP_RW__ID ) ?></th>
					</tr>
					</thead>
				</table>
				<iframe class="rw-chart" src="<?php
					$details["since"] = $details["since_updated"];
					$details["due"]   = $details["due_updated"];
					$details["date"]  = "updated";
					unset( $details["since_updated"], $details["due_updated"] );

					$details["width"]  = 950;
					$details["height"] = 200;

					$query = "";
					foreach ( $details as $key => $value ) {
						$query .= ( $query == "" ) ? "?" : "&";
						$query .= "{$key}=" . urlencode( $value );
					}
					echo WP_RW__ADDRESS . "/action/chart/column.php{$query}";
				?>" width="<?php echo $details["width"]; ?>" height="<?php echo( $details["height"] + 4 ); ?>"
				        frameborder="0"></iframe>
				<br/><br/>
				<table class="widefat"><?php
						$records_num = $shown_records_num = 0;
						if ( $empty_result ) {
							?>
							<tbody>
						<tr>
							<td colspan="6"><?php printf( __( 'No ratings here.', WP_RW__ID ), $elements ); ?></td>
						</tr>
							</tbody><?php
						} else {
							?>
							<thead>
							<tr>
								<th scope="col" class="manage-column"></th>
								<th scope="col" class="manage-column"><?php _e( 'Title', WP_RW__ID ) ?></th>
								<th scope="col" class="manage-column"><?php _e( 'Id', WP_RW__ID ) ?></th>
								<th scope="col" class="manage-column"><?php _e( 'Start Date', WP_RW__ID ) ?></th>
								<th scope="col" class="manage-column"><?php _e( 'Last Update', WP_RW__ID ) ?></th>
								<th scope="col" class="manage-column"><?php _e( 'Votes', WP_RW__ID ) ?></th>
								<th scope="col" class="manage-column"><?php _e( 'Average Rate', WP_RW__ID ) ?></th>
							</tr>
							</thead>
							<tbody>
							<?php
								$alternate = true;

								$records_num       = count( $rw_ret_obj->data );
								$shown_records_num = min( $records_num, $rw_limit );
								for ( $i = 0; $i < $shown_records_num; $i ++ ) {
									$rating = $rw_ret_obj->data[ $i ];
									?>
									<tr<?php if ( $alternate ) {
										echo ' class="alternate"';
									} ?>>
										<td>
											<a href="<?php
												//                            $query_string = self::_getAddFilterQueryString($_SERVER["QUERY_STRING"], "report", WP_RW__REPORT_RATING);
												$query_string = self::_getAddFilterQueryString( $_SERVER["QUERY_STRING"], "urid", $rating->urid );
												$query_string = self::_getAddFilterQueryString( $query_string, "type", $rating_type );
												if ( "star" === $rating_type ) {
													$query_string = self::_getAddFilterQueryString( $query_string, "stars", $rating_stars );
												}

												echo WP_RW__SCRIPT_URL . "?" . $query_string;
											?>"><img src="<?php echo WP_RW__ADDRESS_IMG; ?>rw.pie.icon.png" alt=""
											         title="<?php _e( 'Rating Report', WP_RW__ID ) ?>"></a>
										</td>
										<td><strong><a href="<?php echo $rating->url; ?>" target="_blank"><?php
														echo ( mb_strlen( $rating->title ) > 40 ) ?
															trim( mb_substr( $rating->title, 0, 40 ) ) . "..." :
															$rating->title;
													?></a></strong></td>
										<td><?php echo $rating->urid; ?></td>
										<td><?php echo $rating->created; ?></td>
										<td><?php echo $rating->updated; ?></td>
										<td><?php echo $rating->votes; ?></td>
										<td>
											<?php
												$vars = array(
													"votes" => $rating->votes,
													"rate"  => $rating->rate * ( $rating_stars / WP_RW__DEF_STARS ),
													"dir"   => "ltr",
													"type"  => $rating_type,
													"stars" => $rating_stars,
												);

												if ( $rating_type == "star" ) {
													$vars["style"] = "yellow";
													rw_require_view( 'rating.php', $vars );
												} else {
													$likes    = floor( $rating->rate / WP_RW__DEF_STARS );
													$dislikes = max( 0, $rating->votes - $likes );

													$vars["style"] = "thumbs";
													$vars["rate"]  = 1;
													rw_require_view( 'rating.php', $vars );
													echo '<span style="line-height: 16px; color: darkGreen; padding-right: 5px;">' . $likes . '</span>';
													$vars["rate"] = - 1;
													rw_require_view( 'rating.php', $vars );
													echo '<span style="line-height: 16px; color: darkRed; padding-right: 5px;">' . $dislikes . '</span>';
												}
											?>
										</td>
									</tr>
									<?php
									$alternate = ! $alternate;
								}
							?>
							</tbody>
						<?php
						}
					?>
				</table>
				<?php
					if ( $shown_records_num > 0 ) {
						?>
						<div class="rw-control-bar">
							<div style="float: left;">
									<span style="font-weight: bold; font-size: 12px;"><?php echo( $rw_offset + 1 ); ?>
										-<?php echo( $rw_offset + $shown_records_num ); ?></span>
							</div>
							<div style="float: right;">
								<span><?php _e( 'Show rows', WP_RW__ID ) ?>:</span>
								<select name="rw_limit"
								        onchange="top.location = RWM.enrichQueryString(top.location.href, ['offset', 'limit'], [0, this.value]);">
									<?php
										$limits = array( WP_RW__REPORT_RECORDS_MIN, 25, WP_RW__REPORT_RECORDS_MAX );
										foreach ( $limits as $limit ) {
											?>
											<option value="<?php echo $limit; ?>"<?php if ( $rw_limit == $limit ) {
												echo ' selected="selected"';
											} ?>><?php echo $limit; ?></option>
										<?php
										}
									?>
								</select>
								<input type="button"<?php if ( $rw_offset == 0 ) {
									echo ' disabled="disabled"';
								} ?> class="button button-secondary action" style="margin-left: 20px;"
								       onclick="top.location = '<?php
									       $query_string = self::_getAddFilterQueryString( $_SERVER["QUERY_STRING"], "offset", max( 0, $rw_offset - $rw_limit ) );
									       echo WP_RW__SCRIPT_URL . "?" . $query_string;
								       ?>';" value="Previous"/>
								<input type="button"<?php if ( $shown_records_num == $records_num ) {
									echo ' disabled="disabled"';
								} ?> class="button button-secondary action" onclick="top.location = '<?php
									$query_string = self::_getAddFilterQueryString( $_SERVER["QUERY_STRING"], "offset", $rw_offset + $rw_limit );
									echo WP_RW__SCRIPT_URL . "?" . $query_string;
								?>';" value="Next"/>
							</div>
						</div>
					<?php
					}
				?>
				</form>
				</div>
			<?php
			}

			public static function _isValidPCId($pDeviceID)
			{
				// Length check.
				if (strlen($pDeviceID) !== 36){
					return false;
				}

				if ($pDeviceID[8] != "-" ||
				    $pDeviceID[13] != "-" ||
				    $pDeviceID[18] != "-" ||
				    $pDeviceID[23] != "-")
				{
					return false;
				}


				for ($i = 0; $i < 36; $i++)
				{
					if ($i == 8 || $i == 13 || $i == 18 || $i == 23){ $i++; }

					$code = ord($pDeviceID[$i]);
					if ($code < 48 ||
					    $code > 70 ||
					    ($code > 57 && $code < 65))
					{
						return false;
					}
				}

				return true;
			}

			function rw_report_example_page()
			{
				rw_require_view('pages/admin/report-dummy.php');
			}

			function rw_explicit_report_page__premium_only() {
				if ( ! $this->fs->is_plan_or_trial( 'professional' ) ) {
					return;
				}

				$filters = array(
					"vid"  => array(
						"label"      => "User Id",
						"validation" => create_function( '$val', 'return (is_numeric($val) && $val >= 0);' ),
					),
					"pcid" => array(
						"label"      => "PC Id",
						"validation" => create_function( '$val', 'return (RatingWidgetPlugin::_isValidPCId($val));' ),
					),
					"ip"   => array(
						"label"      => "IP",
						"validation" => create_function( '$val', 'return (1 === preg_match("/^([1-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])(\.([0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])){3}$/", $val));' ),
					),
				);

				$elements  = isset( $_REQUEST["elements"] ) ? $_REQUEST["elements"] : "posts";
				$orderby   = isset( $_REQUEST["orderby"] ) ? $_REQUEST["orderby"] : "created";
				$order     = isset( $_REQUEST["order"] ) ? $_REQUEST["order"] : "DESC";
				$date_from = isset( $_REQUEST["from"] ) ? $_REQUEST["from"] : date( WP_RW__DEFAULT_DATE_FORMAT, time() - WP_RW__PERIOD_MONTH );
				$date_to   = isset( $_REQUEST["to"] ) ? $_REQUEST["to"] : date( WP_RW__DEFAULT_DATE_FORMAT );
				$rw_limit  = isset( $_REQUEST["limit"] ) ? max( WP_RW__REPORT_RECORDS_MIN, min( WP_RW__REPORT_RECORDS_MAX, $_REQUEST["limit"] ) ) : WP_RW__REPORT_RECORDS_MIN;
				$rw_offset = isset( $_REQUEST["offset"] ) ? max( 0, (int) $_REQUEST["offset"] ) : 0;

				switch ( $elements ) {
					case "activity-updates":
						$rating_options = WP_RW__ACTIVITY_UPDATES_OPTIONS;
						$rclass         = "activity-update";
						break;
					case "activity-comments":
						$rating_options = WP_RW__ACTIVITY_COMMENTS_OPTIONS;
						$rclass         = "activity-comment";
						break;
					case "forum-posts":
						$rating_options = WP_RW__FORUM_POSTS_OPTIONS;
						$rclass         = "forum-post,new-forum-post";
						break;
					case "forum-replies":
						$rating_options = WP_RW__FORUM_POSTS_OPTIONS;
						$rclass         = "forum-reply";
						break;
					case "users":
						$rating_options = WP_RW__USERS_OPTIONS;
						$rclass         = "user";
						break;
					case "comments":
						$rating_options = WP_RW__COMMENTS_OPTIONS;
						$rclass         = "comment,new-blog-comment";
						break;
					case "pages":
						$rating_options = WP_RW__PAGES_OPTIONS;
						$rclass         = "page";
						break;
					case "posts":
					default:
						$rating_options = WP_RW__BLOG_POSTS_OPTIONS;
						$rclass         = "front-post,blog-post,new-blog-post";
						break;
				}

				$rating_options = $this->GetOption( $rating_options );
				$rating_type    = isset( $rating_options->type ) ? $rating_options->type : 'star';
				$rating_stars   = ( $rating_type === "star" ) ?
					( ( isset( $rating_options->advanced ) && isset( $rating_options->advanced->star ) && isset( $rating_options->advanced->star->stars ) ) ? $rating_options->advanced->star->stars : WP_RW__DEF_STARS ) :
					false;

				$details = array(
					"uid"           => $this->account->site_public_key,
					"rclasses"      => $rclass,
					"orderby"       => $orderby,
					"order"         => $order,
					"since_updated" => "{$date_from} 00:00:00",
					"due_updated"   => "{$date_to} 23:59:59",
					"limit"         => $rw_limit + 1,
					"offset"        => $rw_offset,
				);

				// Attach filters data.
				foreach ( $filters as $filter => $filter_data ) {
					if ( isset( $_REQUEST[ $filter ] ) && true === $filter_data["validation"]( $_REQUEST[ $filter ] ) ) {
						$details[ $filter ] = $_REQUEST[ $filter ];
					}
				}

				$rw_ret_obj = $this->RemoteCall( "action/report/explicit.php", $details, WP_RW__CACHE_TIMEOUT_REPORT );

				if ( false === $rw_ret_obj ) {
					return false;
				}

				// Decode RW ret object.
				$rw_ret_obj = json_decode( $rw_ret_obj );

				if ( RWLogger::IsOn() ) {
					RWLogger::Log( "ret_object", var_export( $rw_ret_obj, true ) );
				}

				if ( false == $rw_ret_obj->success ) {
					$this->rw_report_example_page();

					return false;
				}

				// Override token to client's call token for iframes.
				$details["token"] = self::GenerateToken( $details["timestamp"], false );

				$empty_result = ( ! is_array( $rw_ret_obj->data ) || 0 == count( $rw_ret_obj->data ) );
				?>
				<div class="wrap rw-dir-ltr rw-report">
				<?php $this->Notice( '<strong style="color: red;">Note: data may be delayed 30 minutes.</strong>' ); ?>
				<form method="post" action="">
				<div class="tablenav">
					<div>
						<span><?php _e( 'Date Range', WP_RW__ID ) ?>:</span>
						<input type="text" value="<?php echo $date_from; ?>" id="rw_date_from" name="rw_date_from"
						       style="width: 90px; text-align: center;"/>
						-
						<input type="text" value="<?php echo $date_to; ?>" id="rw_date_to" name="rw_date_to"
						       style="width: 90px; text-align: center;"/>
						<script type="text/javascript">
							jQuery.datepicker.setDefaults({
								dateFormat: "yy-mm-dd"
							});

							jQuery("#rw_date_from").datepicker({
								maxDate : 0,
								onSelect: function (dateText, inst) {
									jQuery("#rw_date_to").datepicker("option", "minDate", dateText);
								}
							});
							jQuery("#rw_date_from").datepicker("setDate", "<?php echo $date_from;?>");

							jQuery("#rw_date_to").datepicker({
								minDate : "<?php echo $date_from;?>",
								maxDate : 0,
								onSelect: function (dateText, inst) {
									jQuery("#rw_date_from").datepicker("option", "maxDate", dateText);
								}
							});
							jQuery("#rw_date_to").datepicker("setDate", "<?php echo $date_to;?>");
						</script>
						<span><?php _e( 'Order By', WP_RW__ID ) ?>:</span>
						<select id="rw_orderby">
							<?php
								$select = array(
									"rid"     => __( 'Rating Id', WP_RW__ID ),
									"created" => __( 'Start Date', WP_RW__ID ),
									"updated" => __( 'Last Update', WP_RW__ID ),
									"rate"    => __( 'Rate', WP_RW__ID ),
									"vid"     => __( 'User Id', WP_RW__ID ),
									"pcid"    => __( 'PC Id', WP_RW__ID ),
									"ip"      => __( 'IP', WP_RW__ID ),
								);
								foreach ( $select as $value => $option ) {
									$selected = '';
									if ( $value == $orderby ) {
										$selected = ' selected="selected"';
									}
									?>
									<option
										value="<?php echo $value; ?>" <?php echo $selected; ?>><?php echo $option; ?></option>
								<?php
								}
							?>
						</select>
						<input class="button-secondary action" type="button"
						       value="<?php _e( "Show", WP_RW__ID ); ?>"
						       onclick="top.location = RWM.enrichQueryString(top.location.href, ['from', 'to', 'orderby'], [jQuery('#rw_date_from').val(), jQuery('#rw_date_to').val(), jQuery('#rw_orderby').val()]);"/>
					</div>
				</div>
				<br/>

				<div class="rw-filters">
					<?php
						foreach ( $filters as $filter => $filter_data ) {
							if ( isset( $_REQUEST[ $filter ] ) && true === $filter_data["validation"]( $_REQUEST[ $filter ] ) ) {
								?>
								<div class="rw-ui-report-filter">
									<a class="rw-ui-close" href="<?php
										$query_string = self::_getRemoveFilterFromQueryString( $_SERVER["QUERY_STRING"], $filter );
										$query_string = self::_getRemoveFilterFromQueryString( $query_string, "offset" );
										echo WP_RW__SCRIPT_URL . "?" . $query_string;
									?>">x</a> |
									<span class="rw-ui-defenition"><?php echo $filter_data["label"]; ?>:</span>
									<span class="rw-ui-value"><?php echo $_REQUEST[ $filter ]; ?></span>
								</div>
							<?php
							}
						}
					?>
				</div>
				<br/>
				<br/>
				<iframe class="rw-chart" src="<?php
					$details["since"] = $details["since_updated"];
					$details["due"]   = $details["due_updated"];
					$details["date"]  = "updated";
					unset( $details["since_updated"], $details["due_updated"] );

					$details["width"]  = 750;
					$details["height"] = 200;

					$query = "";
					foreach ( $details as $key => $value ) {
						$query .= ( $query == "" ) ? "?" : "&";
						$query .= "{$key}=" . urlencode( $value );
					}
					echo WP_RW__ADDRESS . "/action/chart/column.php{$query}";
				?>" width="750" height="204" frameborder="0"></iframe>
				<br/><br/>
				<table class="widefat"><?php
						$records_num = $showen_records_num = 0;
						if ( ! is_array( $rw_ret_obj->data ) || count( $rw_ret_obj->data ) === 0 ) {
							?>
							<tbody>
						<tr>
							<td colspan="6"><?php printf( __( 'No votes here.', WP_RW__ID ) ); ?></td>
						</tr>
							</tbody><?php
						} else {
							?>
							<thead>
							<tr>
								<th scope="col" class="manage-column"><?php _e( 'Rating Id', WP_RW__ID ) ?></th>
								<th scope="col" class="manage-column"><?php _e( 'User Id', WP_RW__ID ) ?></th>
								<th scope="col" class="manage-column"><?php _e( 'PC Id', WP_RW__ID ) ?></th>
								<th scope="col" class="manage-column"><?php _e( 'IP', WP_RW__ID ) ?></th>
								<th scope="col" class="manage-column"><?php _e( 'Date', WP_RW__ID ) ?></th>
								<th scope="col" class="manage-column"><?php _e( 'Rate', WP_RW__ID ) ?></th>
							</tr>
							</thead>
							<tbody>
							<?php
								$alternate          = true;
								$records_num        = count( $rw_ret_obj->data );
								$showen_records_num = min( $records_num, $rw_limit );
								for ( $i = 0; $i < $showen_records_num; $i ++ ) {
									$vote = $rw_ret_obj->data[ $i ];
									if ( $vote->vid != "0" ) {
										$user = get_userdata( $vote->vid );
									} else {
										$user             = new stdClass();
										$user->user_login = "Anonymous";
									}
									?>
									<tr<?php if ( $alternate ) {
										echo ' class="alternate"';
									} ?>>
										<td>
											<a href="<?php
												$query_string = self::_getAddFilterQueryString( $_SERVER["QUERY_STRING"], "urid", $vote->urid );
												echo WP_RW__SCRIPT_URL . "?" . $query_string;
											?>"><?php echo $vote->urid; ?></a>
										</td>
										<td>
											<a href="<?php
												$query_string = self::_getAddFilterQueryString( $_SERVER["QUERY_STRING"], "vid", $vote->vid );
												echo WP_RW__SCRIPT_URL . "?" . $query_string;
											?>"><?php echo $user->user_login; ?></a>
										</td>
										<td>
											<a href="<?php
												$query_string = self::_getAddFilterQueryString( $_SERVER["QUERY_STRING"], "pcid", $vote->pcid );
												echo WP_RW__SCRIPT_URL . "?" . $query_string;
											?>"><?php echo ( $vote->pcid != "00000000-0000-0000-0000-000000000000" ) ? $vote->pcid : "Anonymous"; ?></a>
										</td>
										<td>
											<a href="<?php
												$query_string = self::_getAddFilterQueryString( $_SERVER["QUERY_STRING"], "ip", $vote->ip );
												echo WP_RW__SCRIPT_URL . "?" . $query_string;
											?>"><?php echo $vote->ip; ?></a>
										</td>
										<td><?php echo $vote->updated; ?></td>
										<td>
											<?php
												$vars = array(
													"votes" => 1,
													"rate"  => $vote->rate * ( $rating_stars / WP_RW__DEF_STARS ),
													"dir"   => "ltr",
													"type"  => "star",
													"stars" => $rating_stars,
												);

												if ( $rating_type == "star" ) {
													$vars["style"] = "yellow";
													rw_require_view( 'rating.php', $vars );
												} else {
													$vars["type"]  = "nero";
													$vars["style"] = "thumbs";
													$vars["rate"]  = ( $vars["rate"] > 0 ) ? 1 : - 1;
													rw_require_view( 'rating.php', $vars );
												}
											?>
										</td>
									</tr>
									<?php
									$alternate = ! $alternate;
								}
							?>
							</tbody>
						<?php
						}
					?>
				</table>
				<?php
					if ( $showen_records_num > 0 ) {
						?>
						<div class="rw-control-bar">
							<div style="float: left;">
									<span style="font-weight: bold; font-size: 12px;"><?php echo( $rw_offset + 1 ); ?>
										-<?php echo( $rw_offset + $showen_records_num ); ?></span>
							</div>
							<div style="float: right;">
								<span><?php _e( 'Show rows', WP_RW__ID ) ?>:</span>
								<select name="rw_limit"
								        onchange="top.location = RWM.enrichQueryString(top.location.href, ['offset', 'limit'], [0, this.value]);">
									<?php
										$limits = array( WP_RW__REPORT_RECORDS_MIN, 25, WP_RW__REPORT_RECORDS_MAX );
										foreach ( $limits as $limit ) {
											?>
											<option value="<?php echo $limit; ?>"<?php if ( $rw_limit == $limit ) {
												echo ' selected="selected"';
											} ?>><?php echo $limit; ?></option>
										<?php
										}
									?>
								</select>
								<input type="button"<?php if ( $rw_offset == 0 ) {
									echo ' disabled="disabled"';
								} ?> class="button button-secondary action" style="margin-left: 20px;"
								       onclick="top.location = '<?php
									       $query_string = self::_getAddFilterQueryString( $_SERVER["QUERY_STRING"], "offset", max( 0, $rw_offset - $rw_limit ) );
									       echo WP_RW__SCRIPT_URL . "?" . $query_string;
								       ?>';" value="<?php _e( 'Previous', WP_RW__ID ) ?>"/>
								<input type="button"<?php if ( $showen_records_num == $records_num ) {
									echo ' disabled="disabled"';
								} ?> class="button button-secondary action" onclick="top.location = '<?php
									$query_string = self::_getAddFilterQueryString( $_SERVER["QUERY_STRING"], "offset", $rw_offset + $rw_limit );
									echo WP_RW__SCRIPT_URL . "?" . $query_string;
								?>';" value="<?php _e( 'Next', WP_RW__ID ) ?>"/>
							</div>
						</div>
					<?php
					}
				?>
				</form>
				</div>
			<?php
			}

			function rw_rating_report_page__premium_only() {
				if ( ! $this->fs->is_plan_or_trial( 'professional' ) ) {
					return false;
				}

				$filters = array(
					"urid" => array(
						"label"      => "Rating Id",
						"validation" => create_function( '$val', 'return (is_numeric($val) && $val >= 0);' ),
					),
					"vid"  => array(
						"label"      => "User Id",
						"validation" => create_function( '$val', 'return (is_numeric($val) && $val >= 0);' ),
					),
					"pcid" => array(
						"label"      => "PC Id",
						"validation" => create_function( '$val', 'return (RatingWidgetPlugin::_isValidPCId($val));' ),
					),
					"ip"   => array(
						"label"      => "IP",
						"validation" => create_function( '$val', 'return (1 === preg_match("/^([1-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])(\.([0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])){3}$/", $val));' ),
					),
				);

				$orderby      = isset( $_REQUEST["orderby"] ) ? $_REQUEST["orderby"] : "created";
				$order        = isset( $_REQUEST["order"] ) ? $_REQUEST["order"] : "DESC";
				$date_from    = isset( $_REQUEST["from"] ) ? $_REQUEST["from"] : date( WP_RW__DEFAULT_DATE_FORMAT, time() - WP_RW__PERIOD_MONTH );
				$date_to      = isset( $_REQUEST["to"] ) ? $_REQUEST["to"] : date( WP_RW__DEFAULT_DATE_FORMAT );
				$rating_type  = ( isset( $_REQUEST["type"] ) && in_array( $_REQUEST["type"], array(
						"star",
						"nero"
					) ) ) ? $_REQUEST["type"] : "star";
				$rating_stars = isset( $_REQUEST["stars"] ) ? max( WP_RW__MIN_STARS, min( WP_RW__MAX_STARS, (int) $_REQUEST["stars"] ) ) : WP_RW__DEF_STARS;

				$rw_limit  = isset( $_REQUEST["limit"] ) ? max( WP_RW__REPORT_RECORDS_MIN, min( WP_RW__REPORT_RECORDS_MAX, $_REQUEST["limit"] ) ) : WP_RW__REPORT_RECORDS_MIN;
				$rw_offset = isset( $_REQUEST["offset"] ) ? max( 0, (int) $_REQUEST["offset"] ) : 0;

				$details = array(
					"uid"     => $this->account->site_public_key,
					"orderby" => $orderby,
					"order"   => $order,
					"since"   => "{$date_from} 00:00:00",
					"due"     => "{$date_to} 23:59:59",
					"date"    => "updated",
					"limit"   => $rw_limit + 1,
					"offset"  => $rw_offset,
					"stars"   => $rating_stars,
					"type"    => $rating_type,
				);

				// Attach filters data.
				foreach ( $filters as $filter => $filter_data ) {
					if ( isset( $_REQUEST[ $filter ] ) && true === $filter_data["validation"]( $_REQUEST[ $filter ] ) ) {
						$details[ $filter ] = $_REQUEST[ $filter ];
					}
				}

				$rw_ret_obj = $this->RemoteCall( "action/report/rating.php", $details, WP_RW__CACHE_TIMEOUT_REPORT );
				if ( false === $rw_ret_obj ) {
					return false;
				}

				// Decode RW ret object.
				$rw_ret_obj = json_decode( $rw_ret_obj );

				if ( false == $rw_ret_obj->success ) {
					$this->rw_report_example_page();

					return false;
				}

				$empty_result = ( ! is_array( $rw_ret_obj->data ) || 0 == count( $rw_ret_obj->data ) );

				// Override token to client's call token for iframes.
				$details["timestamp"] = time();
				$details["token"]     = self::GenerateToken( $details["timestamp"], false );
				?>
				<div class="wrap rw-dir-ltr rw-report">
				<?php $this->Notice( '<strong style="color: red;">Note: data may be delayed 30 minutes.</strong>' ); ?>
				<form method="post" action="">
				<div class="tablenav">
					<div>
						<span><?php _e( 'Date Range', WP_RW__ID ) ?>:</span>
						<input type="text" value="<?php echo $date_from; ?>" id="rw_date_from" name="rw_date_from"
						       style="width: 90px; text-align: center;"/>
						-
						<input type="text" value="<?php echo $date_to; ?>" id="rw_date_to" name="rw_date_to"
						       style="width: 90px; text-align: center;"/>
						<script type="text/javascript">
							jQuery.datepicker.setDefaults({
								dateFormat: "yy-mm-dd"
							})

							jQuery("#rw_date_from").datepicker({
								maxDate : 0,
								onSelect: function (dateText, inst) {
									jQuery("#rw_date_to").datepicker("option", "minDate", dateText);
								}
							});
							jQuery("#rw_date_from").datepicker("setDate", "<?php echo $date_from;?>");

							jQuery("#rw_date_to").datepicker({
								minDate : "<?php echo $date_from;?>",
								maxDate : 0,
								onSelect: function (dateText, inst) {
									jQuery("#rw_date_from").datepicker("option", "maxDate", dateText);
								}
							});
							jQuery("#rw_date_to").datepicker("setDate", "<?php echo $date_to;?>");
						</script>
						<span><?php _e( 'Order By', WP_RW__ID ) ?>:</span>
						<select id="rw_orderby">
							<?php
								$select = array(
									"rid"     => __( 'Id', WP_RW__ID ),
									"created" => __( 'Start Date', WP_RW__ID ),
									"updated" => __( 'Last Update', WP_RW__ID ),
									"rate"    => __( 'Rate', WP_RW__ID ),
									"vid"     => __( 'User Id', WP_RW__ID ),
									"pcid"    => __( 'PC Id', WP_RW__ID ),
									"ip"      => __( 'IP', WP_RW__ID ),
								);
								foreach ( $select as $value => $option ) {
									$selected = '';
									if ( $value == $orderby ) {
										$selected = ' selected="selected"';
									}
									?>
									<option
										value="<?php echo $value; ?>" <?php echo $selected; ?>><?php echo $option; ?></option>
								<?php
								}
							?>
						</select>
						<input class="button-secondary action" type="button"
						       value="<?php _e( "Show", WP_RW__ID ); ?>"
						       onclick="top.location = RWM.enrichQueryString(top.location.href, ['from', 'to', 'orderby'], [jQuery('#rw_date_from').val(), jQuery('#rw_date_to').val(), jQuery('#rw_orderby').val()]);"/>
					</div>
				</div>
				<br/>

				<div class="rw-filters">
					<?php
						foreach ( $filters as $filter => $filter_data ) {
							if ( isset( $_REQUEST[ $filter ] ) && true === $filter_data["validation"]( $_REQUEST[ $filter ] ) ) {
								?>
								<div class="rw-ui-report-filter">
									<a class="rw-ui-close" href="<?php
										$query_string = self::_getRemoveFilterFromQueryString( $_SERVER["QUERY_STRING"], $filter );
										$query_string = self::_getRemoveFilterFromQueryString( $query_string, "offset" );
										echo WP_RW__SCRIPT_URL . "?" . $query_string;
									?>">x</a> |
									<span class="rw-ui-defenition"><?php echo $filter_data["label"]; ?>:</span>
									<span class="rw-ui-value"><?php echo $_REQUEST[ $filter ]; ?></span>
								</div>
							<?php
							}
						}
					?>
				</div>
				<br/>
				<br/>
				<iframe class="rw-chart" src="<?php
					$details["width"]  = ( ! $empty_result ) ? 647 : 950;
					$details["height"] = 200;

					$query = "";
					foreach ( $details as $key => $value ) {
						$query .= ( $query == "" ) ? "?" : "&";
						$query .= "{$key}=" . urlencode( $value );
					}
					echo WP_RW__ADDRESS . "/action/chart/column.php{$query}";
				?>" width="<?php echo $details["width"]; ?>" height="<?php echo( $details["height"] + 4 ); ?>"
				        frameborder="0"></iframe>
				<?php
					if ( ! $empty_result ) {
						?>
						<iframe class="rw-chart" src="<?php
							$details["width"]  = 300;
							$details["height"] = 200;

							$query = "";
							foreach ( $details as $key => $value ) {
								$query .= ( $query == "" ) ? "?" : "&";
								$query .= "{$key}=" . urlencode( $value );
							}
							$query .= "&stars={$rating_stars}";
							echo WP_RW__ADDRESS . "/action/chart/pie.php{$query}";
						?>" width="<?php echo $details["width"]; ?>"
						        height="<?php echo( $details["height"] + 4 ); ?>" frameborder="0"></iframe>
					<?php
					}
				?>
				<br/><br/>
				<table class="widefat"><?php
						$records_num = $showen_records_num = 0;
						if ( ! is_array( $rw_ret_obj->data ) || count( $rw_ret_obj->data ) === 0 ) {
							?>
							<tbody>
						<tr>
							<td colspan="6"><?php printf( __( 'No votes here.', WP_RW__ID ) ); ?></td>
						</tr>
							</tbody><?php
						} else {
							?>
							<thead>
							<tr>
								<th scope="col" class="manage-column"><?php _e( 'User Id', WP_RW__ID ) ?></th>
								<th scope="col" class="manage-column"><?php _e( 'PC Id', WP_RW__ID ) ?></th>
								<th scope="col" class="manage-column"><?php _e( 'IP', WP_RW__ID ) ?></th>
								<th scope="col" class="manage-column"><?php _e( 'Date', WP_RW__ID ) ?></th>
								<th scope="col" class="manage-column"><?php _e( 'Rate', WP_RW__ID ) ?></th>
							</tr>
							</thead>
							<tbody>
							<?php
								$alternate          = true;
								$records_num        = count( $rw_ret_obj->data );
								$showen_records_num = min( $records_num, $rw_limit );
								for ( $i = 0; $i < $showen_records_num; $i ++ ) {
									$vote = $rw_ret_obj->data[ $i ];
									if ( $vote->vid != "0" ) {
										$user = get_userdata( $vote->vid );
									} else {
										$user             = new stdClass();
										$user->user_login = "Anonymous";
									}
									?>
									<tr<?php if ( $alternate ) {
										echo ' class="alternate"';
									} ?>>
										<td>
											<a href="<?php
												$query_string = self::_getAddFilterQueryString( $_SERVER["QUERY_STRING"], "vid", $vote->vid );
												echo WP_RW__SCRIPT_URL . "?" . $query_string;
											?>"><?php echo $user->user_login; ?></a>
										</td>
										<td>
											<a href="<?php
												$query_string = self::_getAddFilterQueryString( $_SERVER["QUERY_STRING"], "pcid", $vote->pcid );
												echo WP_RW__SCRIPT_URL . "?" . $query_string;
											?>"><?php echo ( $vote->pcid != "00000000-0000-0000-0000-000000000000" ) ? $vote->pcid : "Anonymous"; ?></a>
										</td>
										<td>
											<a href="<?php
												$query_string = self::_getAddFilterQueryString( $_SERVER["QUERY_STRING"], "ip", $vote->ip );
												echo WP_RW__SCRIPT_URL . "?" . $query_string;
											?>"><?php echo $vote->ip; ?></a>
										<td><?php echo $vote->updated; ?></td>
										<td>
											<?php
												$vars = array(
													"votes" => 1,
													"rate"  => $vote->rate * ( $rating_stars / WP_RW__DEF_STARS ),
													"dir"   => "ltr",
													"type"  => "star",
													"stars" => $rating_stars,
												);

												if ( $rating_type == "star" ) {
													$vars["style"] = "yellow";
													rw_require_view( 'rating.php', $vars );
												} else {
													$vars["type"]  = "nero";
													$vars["style"] = "thumbs";
													$vars["rate"]  = ( $vars["rate"] > 0 ) ? 1 : - 1;
													rw_require_view( 'rating.php', $vars );
												}
											?>
										</td>
									</tr>
									<?php
									$alternate = ! $alternate;
								}
							?>
							</tbody>
						<?php
						}
					?>
				</table>
				<?php
					if ( $showen_records_num > 0 ) {
						?>
						<div class="rw-control-bar">
							<div style="float: left;">
									<span style="font-weight: bold; font-size: 12px;"><?php echo( $rw_offset + 1 ); ?>
										-<?php echo( $rw_offset + $showen_records_num ); ?></span>
							</div>
							<div style="float: right;">
								<span><?php _e( 'Show rows', WP_RW__ID ) ?>:</span>
								<select name="rw_limit"
								        onchange="top.location = RWM.enrichQueryString(top.location.href, ['offset', 'limit'], [0, this.value]);">
									<?php
										$limits = array( WP_RW__REPORT_RECORDS_MIN, 25, WP_RW__REPORT_RECORDS_MAX );
										foreach ( $limits as $limit ) {
											?>
											<option value="<?php echo $limit; ?>"<?php if ( $rw_limit == $limit ) {
												echo ' selected="selected"';
											} ?>><?php echo $limit; ?></option>
										<?php
										}
									?>
								</select>
								<input type="button"<?php if ( $rw_offset == 0 ) {
									echo ' disabled="disabled"';
								} ?> class="button button-secondary action" style="margin-left: 20px;"
								       onclick="top.location = '<?php
									       $query_string = self::_getAddFilterQueryString( $_SERVER["QUERY_STRING"], "offset", max( 0, $rw_offset - $rw_limit ) );
									       echo WP_RW__SCRIPT_URL . "?" . $query_string;
								       ?>';" value="<?php _e( 'Previous', WP_RW__ID ) ?>"/>
								<input type="button"<?php if ( $showen_records_num == $records_num ) {
									echo ' disabled="disabled"';
								} ?> class="button button-secondary action" onclick="top.location = '<?php
									$query_string = self::_getAddFilterQueryString( $_SERVER["QUERY_STRING"], "offset", $rw_offset + $rw_limit );
									echo WP_RW__SCRIPT_URL . "?" . $query_string;
								?>';" value="<?php _e( 'Next', WP_RW__ID ) ?>"/>
							</div>
						</div>
					<?php
					}
				?>
				</form>
				</div>
				<?php

				return true;
			}

			function ReportsPageRender()
			{
				if ($this->fs->is_plan_or_trial__premium_only('professional')) {
					if ( isset( $_GET["urid"] ) && is_numeric( $_GET["urid"] ) ) {
						$this->rw_rating_report_page__premium_only();
					} else if ( isset( $_GET["ip"] ) || isset( $_GET["vid"] ) || isset( $_GET["pcid"] ) ) {
						$this->rw_explicit_report_page__premium_only();
					} else {
						$this->rw_general_report_page__premium_only();
					}
				}
				else
				{
					$this->rw_report_example_page();
				}
			}

			#endregion Reports ------------------------------------------------------------------

			function AccountPageRender()
			{
				rw_require_once_view('pages/admin/account-actions.php');
			}

			#region Advanced Settings ------------------------------------------------------------------

			private function RestoreDefaultSettings() {
				RWLogger::LogEnterence( 'RestoreDefaultSettings' );

				// Restore to defaults - clear all settings.
				$this->_options->clear();

				// Restore account details.
				$this->_options->set_option( WP_RW__DB_OPTION_SITE_PUBLIC_KEY, $this->account->site_public_key );
				$this->_options->set_option( WP_RW__DB_OPTION_SITE_ID, $this->account->site_id );
				$this->_options->set_option( WP_RW__DB_OPTION_SITE_SECRET_KEY, $this->account->site_secret_key );
				$this->_options->set_option( WP_RW__DB_OPTION_OWNER_ID, $this->account->user_id );
				$this->_options->set_option( WP_RW__DB_OPTION_OWNER_EMAIL, $this->account->user_email );

				$this->_options->store();

				RWLogger::LogDeparture( 'RestoreDefaultSettings' );
			}

			private function DeleteAndCreateNewAccount() {
				RWLogger::LogEnterence( 'DeleteAndCreateNewAccount' );

				// Delete user-key & secret.
				$this->UnsetOption( WP_RW__DB_OPTION_SITE_PUBLIC_KEY );
				$this->UnsetOption( WP_RW__DB_OPTION_SITE_ID );
				$this->UnsetOption( WP_RW__DB_OPTION_SITE_SECRET_KEY );
				$this->_options->store();

				RWLogger::LogDeparture( 'DeleteAndCreateNewAccount' );
			}

			function AdvancedSettingsPageRender()
			{
				// Variables for the field and option names
				$rw_form_hidden_field_name = "rw_form_hidden_field_name";

				// Get visitor identification method.
				$rw_identify_by = $this->GetOption(WP_RW__IDENTIFY_BY);

				// Get flash dependency.
				$rw_flash_dependency = $this->GetOption(WP_RW__FLASH_DEPENDENCY);

				// Get show on mobile flag.
				$rw_show_on_mobile =  $this->GetOption(WP_RW__SHOW_ON_MOBILE);

				if (isset($_POST[$rw_form_hidden_field_name]) && $_POST[$rw_form_hidden_field_name] == 'Y')
				{
					$this->settings->SetSaveMode();

					// Save advanced settings.
					// Get posted identification method.
					if (isset($_POST["rw_identify_by"]) && in_array($_POST["rw_identify_by"], array("ip", "laccount")))
					{
						$rw_identify_by = $_POST["rw_identify_by"];
						$this->SetOption(WP_RW__IDENTIFY_BY, $rw_identify_by);
					}

					// Get posted flash dependency.
					if (isset($_POST["rw_flash_dependency"]) && in_array($_POST["rw_flash_dependency"], array("true", "false")))
					{
						$rw_flash_dependency = ('true' == $_POST["rw_flash_dependency"]);
						// Save flash dependency.
						$this->SetOption(WP_RW__FLASH_DEPENDENCY, $rw_flash_dependency);
					}

					// Get mobile flag.
					if (isset($_POST["rw_show_on_mobile"]) && in_array($_POST["rw_show_on_mobile"], array("true", "false")))
					{
						$rw_show_on_mobile = ('true' == $_POST["rw_show_on_mobile"]);
						// Save show on mobile flag.
						$this->SetOption(WP_RW__SHOW_ON_MOBILE, $rw_show_on_mobile);
					}
					?>
					<div class="updated"><p><strong><?php _e('Settings successfully saved.', WP_RW__ID ); ?></strong></p></div>
				<?php
				}

				$this->settings->form_hidden_field_name = $rw_form_hidden_field_name;
				$this->settings->identify_by = $rw_identify_by;
				$this->settings->flash_dependency = $rw_flash_dependency;
				$this->settings->show_on_mobile = $rw_show_on_mobile;

				rw_require_once_view('pages/admin/advanced.php');

				// Store options if in save mode.
				if ($this->settings->IsSaveMode())
					$this->_options->store();
			}

			#endregion Advanced Settings ------------------------------------------------------------------

			/**
			 * Generates the content of the Affiliation Program page.
			 *
			 * @author Leo Fajardo (@leorw)
			 * @since  2.4.4
			 */
			function affiliation_settings_page_render() {
				rw_require_once_view('pages/admin/affiliation.php');
			}

			/**
			 * Generates the content of the Add Ons page.
			 *
			 * @author Leo Fajardo (@leorw)
			 * @since  2.5.0
			 */
			function addons_settings_page_render() {
				rw_require_once_view('pages/admin/addons.php');
			}

			function TopRatedSettingsPageLoad()
			{
				rw_enqueue_style('rw_toprated', rw_get_plugin_css_path('toprated.css'));
			}

			function TopRatedSettingsPageRender()
			{
				?>
				<div class="wrap rw-dir-ltr rw-wp-container">
					<h2><?php echo __( 'Increase User Retention and Pageviews', WP_RW__ID);?></h2>
					<div>
						<p style="font-weight: bold; font-size: 14px;"><?php _e('With the Top-Rated Sidebar Widget readers will stay on your site for a longer period of time.', WP_RW__ID) ?></p>
						<ul>
							<li>
								<ul id="screenshots">
									<li>
										<img src="<?php echo rw_get_plugin_img_path('top-rated/legacy.png');?>" alt="">
									</li>
									<li>
										<img src="<?php echo rw_get_plugin_img_path('top-rated/compact-thumbs.png');?>" alt="">
									</li>
									<li>
										<img src="<?php echo rw_get_plugin_img_path('top-rated/thumbs.png');?>" alt="">
									</li>
								</ul>
								<div style="clear: both;"> </div>
							</li>
							<li>
								<a href="<?php echo get_admin_url(null, 'widgets.php'); ?>" class="button-primary" style="margin-left: 20px; display: block; text-align: center; width: 720px;"><?php _e('Add Widget Now!', WP_RW__ID) ?></a>
							</li>
							<li>
								<h3><?php _e('How', WP_RW__ID) ?></h3>
								<p><?php _e('Expose your readers to the top rated posts onsite that they might not have otherwise noticed and increase your chance to reduce the bounce rate.', WP_RW__ID) ?></p>
							</li>
							<li>
								<h3><?php _e('What', WP_RW__ID) ?></h3>
								<p><?php _e('The Top-Rated Widget is a beautiful sidebar widget containing the top rated posts on your blog.', WP_RW__ID) ?></p>
							</li>
							<li>
								<h3><?php _e('Install', WP_RW__ID) ?></h3>
								<p><?php _e('Go to', WP_RW__ID) ?> <b><i><a href="<?php echo get_admin_url(null, 'widgets.php'); ?>" class="button-primary">Appearence > Widgets</a></i></b> and simply drag the <b>Rating-Widget: Top Rated</b> widget to your sidebar.</p>
								<img src="<?php echo rw_get_plugin_img_path('top-rated/add-widget.png');?>" alt="">
							</li>
							<li>
								<h3><?php _e('New', WP_RW__ID) ?></h3>
								<p><?php _e('Thumbnails: a beautiful new thumbnail display, for themes which use post thumbnails (featured images).', WP_RW__ID) ?></p>
							</li>
							<li>
								<h3><?php _e('Performance', WP_RW__ID) ?></h3>
								<p><?php _e('The widget is performant, caching the top posts and featured images\' thumbnails as your site is visited.', WP_RW__ID) ?></p>
							</li>
							<li>
								<a href="<?php echo get_admin_url(null, 'widgets.php'); ?>" class="button-primary"><?php _e('Add Widget Now!', WP_RW__ID) ?></a>
							</li>
						</ul>
					</div>
					<br />
				</div>
			<?php
			}

			private function GetMenuSlug($pSlug = '')
			{
				return WP_RW__ADMIN_MENU_SLUG . (empty($pSlug) ? '' : ('-' . $pSlug));
			}

			private function GetFirstKey(array $associative)
			{
				reset($associative);
				return key($associative);
			}

			/**
			 * To get a list of all custom user defined posts:
			 *
			 *       get_post_types(array('public'=>true,'_builtin' => false))
			 */
			function SettingsPage()
			{
				RWLogger::LogEnterence("SettingsPage");

				// Must check that the user has the required capability.
				if (!current_user_can('manage_options'))
					wp_die(__('You do not have sufficient permissions to access this page.', WP_RW__ID));

				global $plugin_page;

				// Variables for the field and option names
				$rw_form_hidden_field_name = "rw_form_hidden_field_name";

				if ($plugin_page === $this->GetMenuSlug('buddypress') && $this->IsBuddyPressInstalled())
				{
					$settings_data = array(
						"activity-blog-posts" => array(
							"tab" => "Activity Blog Posts",
							"class" => "new-blog-post",
							"options" => WP_RW__ACTIVITY_BLOG_POSTS_OPTIONS,
							"align" => WP_RW__ACTIVITY_BLOG_POSTS_ALIGN,
							"default_align" => $this->_OPTIONS_DEFAULTS[WP_RW__ACTIVITY_BLOG_POSTS_ALIGN],
							"excerpt" => false,
							"show_align" => true,
						),
						"activity-blog-comments" => array(
							"tab" => "Activity Blog Comments",
							"class" => "new-blog-comment",
							"options" => WP_RW__ACTIVITY_BLOG_COMMENTS_OPTIONS,
							"align" => WP_RW__ACTIVITY_BLOG_COMMENTS_ALIGN,
							"default_align" => $this->_OPTIONS_DEFAULTS[WP_RW__ACTIVITY_BLOG_COMMENTS_ALIGN],
							"excerpt" => false,
							"show_align" => true,
						),
						"activity-updates" => array(
							"tab" => "Activity Updates",
							"class" => "activity-update",
							"options" => WP_RW__ACTIVITY_UPDATES_OPTIONS,
							"align" => WP_RW__ACTIVITY_UPDATES_ALIGN,
							"default_align" => $this->_OPTIONS_DEFAULTS[WP_RW__ACTIVITY_UPDATES_ALIGN],
							"excerpt" => false,
							"show_align" => true,
						),
						"activity-comments" => array(
							"tab" => "Activity Comments",
							"class" => "activity-comment",
							"options" => WP_RW__ACTIVITY_COMMENTS_OPTIONS,
							"align" => WP_RW__ACTIVITY_COMMENTS_ALIGN,
							"default_align" => $this->_OPTIONS_DEFAULTS[WP_RW__ACTIVITY_COMMENTS_ALIGN],
							"excerpt" => false,
							"show_align" => true,
						),
						"users" => array(
							"tab" => "Users Profiles",
							"class" => "user",
							"options" => WP_RW__USERS_OPTIONS,
							"align" => WP_RW__USERS_ALIGN,
							"default_align" => $this->_OPTIONS_DEFAULTS[WP_RW__USERS_ALIGN],
							"excerpt" => false,
							"show_align" => false,
						),
					);

					$selected_key = isset($_GET["rating"]) ? $_GET["rating"] : "activity-blog-posts";
					if (!isset($settings_data[$selected_key]))
						$selected_key = "activity-blog-posts";
				}
				else if ($plugin_page === $this->GetMenuSlug('bbpress') && $this->IsBBPressInstalled())
				{
					$settings_data = array(
						/*"forum-topics" => array(
                    "tab" => "Forum Topics",
                    "class" => "forum-topic",
                    "options" => WP_RW__FORUM_TOPICS_OPTIONS,
                    "align" => WP_RW__FORUM_TOPICS_ALIGN,
                    "default_align" => $this->_OPTIONS_DEFAULTS[WP_RW__FORUM_TOPICS_ALIGN],
                    "excerpt" => false,
                ),*/
						"forum-posts" => array(
							"tab" => "Forum Posts",
							"class" => "forum-post",
							"options" => WP_RW__FORUM_POSTS_OPTIONS,
							"align" => WP_RW__FORUM_POSTS_ALIGN,
							"default_align" => $this->_OPTIONS_DEFAULTS[WP_RW__FORUM_POSTS_ALIGN],
							"excerpt" => false,
							"show_align" => true,
						),
						/*"activity-forum-topics" => array(
                    "tab" => "Activity Forum Topics",
                    "class" => "new-forum-topic",
                    "options" => WP_RW__ACTIVITY_FORUM_TOPICS_OPTIONS,
                    "align" => WP_RW__ACTIVITY_FORUM_TOPICS_ALIGN,
                    "default_align" => $this->_OPTIONS_DEFAULTS[WP_RW__ACTIVITY_FORUM_TOPICS_ALIGN],
                    "excerpt" => false,
                ),*/
						"activity-forum-posts" => array(
							"tab" => "Activity Forum Posts",
							"class" => "new-forum-post",
							"options" => WP_RW__ACTIVITY_FORUM_POSTS_OPTIONS,
							"align" => WP_RW__ACTIVITY_FORUM_POSTS_ALIGN,
							"default_align" => $this->_OPTIONS_DEFAULTS[WP_RW__ACTIVITY_FORUM_POSTS_ALIGN],
							"excerpt" => false,
							"show_align" => true,
						),
						"users" => array(
							"tab" => "Users Profiles",
							"class" => "user",
							"options" => WP_RW__USERS_OPTIONS,
							"align" => WP_RW__USERS_ALIGN,
							"default_align" => $this->_OPTIONS_DEFAULTS[WP_RW__USERS_ALIGN],
							"excerpt" => false,
							"show_align" => false,
						),
					);

					$selected_key = isset($_GET["rating"]) ? $_GET["rating"] : "forum-posts";
					if (!isset($settings_data[$selected_key]))
						$selected_key = "forum-posts";
				}
				else if ($plugin_page === $this->GetMenuSlug('user'))
				{
					$settings_data = array(
						"users-posts" => array(
							"tab" => "Posts",
							"class" => "user-post",
							"options" => WP_RW__USERS_POSTS_OPTIONS,
							"align" => WP_RW__USERS_POSTS_ALIGN,
							"default_align" => $this->_OPTIONS_DEFAULTS[WP_RW__USERS_POSTS_ALIGN],
							"excerpt" => false,
							"show_align" => false,
						),
						"users-pages" => array(
							"tab" => "Pages",
							"class" => "user-page",
							"options" => WP_RW__USERS_PAGES_OPTIONS,
							"align" => WP_RW__USERS_PAGES_ALIGN,
							"default_align" => $this->_OPTIONS_DEFAULTS[WP_RW__USERS_PAGES_ALIGN],
							"excerpt" => false,
							"show_align" => false,
						),
						"users-comments" => array(
							"tab" => "Comments",
							"class" => "user-comment",
							"options" => WP_RW__USERS_COMMENTS_OPTIONS,
							"align" => WP_RW__USERS_COMMENTS_ALIGN,
							"default_align" => $this->_OPTIONS_DEFAULTS[WP_RW__USERS_COMMENTS_ALIGN],
							"excerpt" => false,
							"show_align" => false,
						),
					);

					if ($this->IsBuddyPressInstalled())
					{
						$settings_data["users-activity-updates"] = array(
							"tab" => "Activity Updates",
							"class" => "user-activity-update",
							"options" => WP_RW__USERS_ACTIVITY_UPDATES_OPTIONS,
							"align" => WP_RW__USERS_ACTIVITY_UPDATES_ALIGN,
							"default_align" => $this->_OPTIONS_DEFAULTS[WP_RW__USERS_ACTIVITY_UPDATES_ALIGN],
							"excerpt" => false,
							"show_align" => false,
						);
						$settings_data["users-activity-comments"] = array(
							"tab" => "Activity Comments",
							"class" => "user-activity-comment",
							"options" => WP_RW__USERS_ACTIVITY_COMMENTS_OPTIONS,
							"align" => WP_RW__USERS_ACTIVITY_COMMENTS_ALIGN,
							"default_align" => $this->_OPTIONS_DEFAULTS[WP_RW__USERS_ACTIVITY_COMMENTS_ALIGN],
							"excerpt" => false,
							"show_align" => false,
						);

						if ($this->IsBBPressInstalled())
						{
							$settings_data["users-forum-posts"] = array(
								"tab" => "Forum Posts",
								"class" => "user-forum-post",
								"options" => WP_RW__USERS_FORUM_POSTS_OPTIONS,
								"align" => WP_RW__USERS_FORUM_POSTS_ALIGN,
								"default_align" => $this->_OPTIONS_DEFAULTS[WP_RW__USERS_FORUM_POSTS_ALIGN],
								"excerpt" => false,
								"show_align" => false,
							);
						}
					}

					$selected_key = isset($_GET["rating"]) ? $_GET["rating"] : "users-posts";
					if (!isset($settings_data[$selected_key]))
						$selected_key = "users-posts";
				}
				else
				{
					$is_extension = false;

					foreach ($this->_extensions as $ext)
					{
						if ($plugin_page !== $this->GetMenuSlug($ext->GetSlug()))
							continue;

						$is_extension = true;

						$settings_data = $ext->GetSettings();

						$selected_key = isset($_GET["rating"]) && isset($settings_data[$_GET["rating"]]) ?
							$_GET["rating"] :
							$this->GetFirstKey($settings_data);
					}

					if (!$is_extension) {
						$settings_data = array(
							"blog-posts"  => array(
								"tab"           => "Blog Posts",
								"class"         => "blog-post",
								"options"       => WP_RW__BLOG_POSTS_OPTIONS,
								"align"         => WP_RW__BLOG_POSTS_ALIGN,
								"default_align" => $this->_OPTIONS_DEFAULTS[ WP_RW__BLOG_POSTS_ALIGN ],
								"excerpt"       => true,
								"show_align"    => true,
							),
							"front-posts" => array(
								"tab"           => "Front Page Posts",
								"class"         => "front-post",
								"options"       => WP_RW__FRONT_POSTS_OPTIONS,
								"align"         => WP_RW__FRONT_POSTS_ALIGN,
								"default_align" => $this->_OPTIONS_DEFAULTS[ WP_RW__FRONT_POSTS_ALIGN ],
								"excerpt"       => false,
								"show_align"    => true,
							),
							"comments"    => array(
								"tab"           => "Comments",
								"class"         => "comment",
								"options"       => WP_RW__COMMENTS_OPTIONS,
								"align"         => WP_RW__COMMENTS_ALIGN,
								"default_align" => $this->_OPTIONS_DEFAULTS[ WP_RW__COMMENTS_ALIGN ],
								"excerpt"       => false,
								"show_align"    => true,
							),
							"pages"       => array(
								"tab"           => "Pages",
								"class"         => "page",
								"options"       => WP_RW__PAGES_OPTIONS,
								"align"         => WP_RW__PAGES_ALIGN,
								"default_align" => $this->_OPTIONS_DEFAULTS[ WP_RW__PAGES_ALIGN ],
								"excerpt"       => false,
								"show_align"    => true,
							),
						);

						$selected_key = isset( $_GET["rating"] ) ? $_GET["rating"] : "blog-posts";
						if ( ! isset( $settings_data[ $selected_key ] ) ) {
							$selected_key = "blog-posts";
						}
					}
				}

				$rw_current_settings = $settings_data[$selected_key];

				// Some alias.
				$rw_class = $rw_current_settings["class"];

				$is_blog_post = ('blog-post' === $rw_current_settings['class']);
				$item_with_category = in_array($rw_current_settings['class'], array('blog-post', 'front-post', 'comment'));

				// Visibility list must be loaded anyway.
				$this->_visibilityList = $this->GetOption(WP_RW__VISIBILITY_SETTINGS);

				if ($item_with_category) {
					// Categories Availability list must be loaded anyway.
					$this->categories_list = $this->GetOption(WP_RW__CATEGORIES_AVAILABILITY_SETTINGS);
				}

				// Availability list must be loaded anyway.
				$this->availability_list = $this->GetOption(WP_RW__AVAILABILITY_SETTINGS);

				$this->custom_settings_enabled_list = $this->GetOption(WP_RW__CUSTOM_SETTINGS_ENABLED);

				$this->custom_settings_list = $this->GetOption(WP_RW__CUSTOM_SETTINGS);

				$this->multirating_settings_list = $this->GetOption(WP_RW__MULTIRATING_SETTINGS);

				// Accumulated user ratings support.
				if ('users' === $selected_key && $this->IsBBPressInstalled())
					$rw_is_user_accumulated = $this->GetOption(WP_RW__IS_ACCUMULATED_USER_RATING);

				// Comment "Reviews" mode support.
				if ( 'comments' === $selected_key ) {
					$comment_ratings_mode_settings = $this->get_comment_ratings_mode_settings();
					$comment_ratings_mode = $comment_ratings_mode_settings->comment_ratings_mode;
				}

				// Reset categories.
				$rw_categories = array();

				// See if the user has posted us some information
				// If they did, this hidden field will be set to 'Y'
				if (isset($_POST[$rw_form_hidden_field_name]) && $_POST[$rw_form_hidden_field_name] == 'Y')
				{
					// Set settings into save mode.
					$this->settings->SetSaveMode();

					/* Multi-rating options.
            ---------------------------------------------------------------------------------------------------------------*/
					if (isset($_POST['multi_rating'])) {
						$multi_rating = $_POST['multi_rating'];

						if (!$this->fs->is_plan_or_trial__premium_only('professional')) {
							if ( count( $multi_rating['criteria'] ) > 3 ) {
								$multi_rating['criteria'] = array_splice( $multi_rating['criteria'], 0, 3 );
							}
						}

						// Unset empty labels
						foreach ($multi_rating['criteria'] as $criteria_id => $criteria) {
							$criteria_label = isset($criteria['label']) ? trim($criteria['label']) : '';
							if (empty($criteria_label)) {
								unset($multi_rating['criteria'][$criteria_id]['label']);
							}
						}

						// Retrieve the current multi-rating options
						if (!isset($this->multirating_settings_list))
							$this->multirating_settings_list = new stdClass();

						$multirating_options = $this->multirating_settings_list->{$rw_class};

						// Save the new criteria IDs and labels
						$multirating_options->criteria = $multi_rating['criteria'];

						// Save the summary label
						$summary_label = isset($multi_rating['summary_label']) ? trim($multi_rating['summary_label']) : '';
						if (!empty($summary_label)) {
							$multirating_options->summary_label = $summary_label;
						} else {
							unset($multirating_options->summary_label);
						}

						// Save the state of the Show Summary Rating option
						$multirating_options->show_summary_rating = isset($multi_rating['show_summary_rating']) ? true : false;

						// Save the updated multi-rating options
						if (!isset($this->multirating_settings_list))
							$this->multirating_settings_list = new stdClass();

						$this->multirating_settings_list->{$rw_class} = $multirating_options;
						$this->SetOption(WP_RW__MULTIRATING_SETTINGS, $this->multirating_settings_list);
					}

					/* Widget align options.
            ---------------------------------------------------------------------------------------------------------------*/
					$rw_show_rating = isset($_POST["rw_show"]) ? true : false;
					$rw_align =  (!$rw_show_rating) ? new stdClass() : $rw_current_settings["default_align"];
					if ($rw_show_rating && isset($_POST["rw_align"]))
					{
						$align = explode(" ", $_POST["rw_align"]);
						if (is_array($align) && count($align) == 2)
						{
							if (in_array($align[0], array("top", "bottom")) &&
							    in_array($align[1], array("left", "center", "right")))
							{
								$rw_align->ver = $align[0];
								$rw_align->hor = $align[1];
							}
						}
					}
					$this->SetOption($rw_current_settings["align"], $rw_align);

					/* Rating-Widget options.
            ---------------------------------------------------------------------------------------------------------------*/
					$rw_options = json_decode(preg_replace('/\%u([0-9A-F]{4})/i', '\\u$1', urldecode(stripslashes($_POST["rw_options"]))));
					if (null !== $rw_options)
						$this->SetOption($rw_current_settings["options"], $rw_options);

					/* Availability settings.
            ---------------------------------------------------------------------------------------------------------------*/
					$rw_availability = isset($_POST["rw_availability"]) ? max(0, min(2, (int)$_POST["rw_availability"])) : 0;

					$this->availability_list->{$rw_class} = $rw_availability;
					$this->SetOption(WP_RW__AVAILABILITY_SETTINGS, $this->availability_list);

					if ($item_with_category)
					{
						/* Categories Availability settings.
                ---------------------------------------------------------------------------------------------------------------*/
						$rw_categories = isset($_POST["rw_categories"]) && is_array($_POST["rw_categories"]) ? $_POST["rw_categories"] : array();

						if (!isset($this->categories_list))
							$this->categories_list = new stdClass();

						$this->categories_list->{$rw_class} = (in_array("-1", $rw_categories) ? array("-1") : $rw_categories);
						$this->SetOption(WP_RW__CATEGORIES_AVAILABILITY_SETTINGS, $this->categories_list);
					}

					// Accumulated user ratings support.
					if ('users' === $selected_key && $this->IsBBPressInstalled() && isset($_POST['rw_accumulated_user_rating']))
					{
						$rw_is_user_accumulated = ('true' == (in_array($_POST['rw_accumulated_user_rating'], array('true', 'false')) ? $_POST['rw_accumulated_user_rating'] : 'true'));
						$this->SetOption(WP_RW__IS_ACCUMULATED_USER_RATING, $rw_is_user_accumulated);
					}

					// Comment ratings mode
					if ( 'comments' === $selected_key && isset( $_POST['rw_comment_review_mode'] ) ) {
						$comment_ratings_mode = $_POST['rw_comment_review_mode'];

						// Save the new comment ratings mode.
						$comment_ratings_mode_settings = $this->get_comment_ratings_mode_settings();
						$comment_ratings_mode_settings->comment_ratings_mode = $comment_ratings_mode;

						$this->SetOption(WP_RW__DB_OPTION_IS_ADMIN_COMMENT_RATINGS_SETTINGS, $comment_ratings_mode_settings);
					}

					/* Visibility settings
            ---------------------------------------------------------------------------------------------------------------*/
					$rw_visibility = isset($_POST["rw_visibility"]) ? max(0, min(2, (int)$_POST["rw_visibility"])) : 0;
					$rw_visibility_exclude  = isset($_POST["rw_visibility_exclude"]) ? $_POST["rw_visibility_exclude"] : "";
					$rw_visibility_include  = isset($_POST["rw_visibility_include"]) ? $_POST["rw_visibility_include"] : "";

					$rw_custom_settings_enabled = isset($_POST["rw_custom_settings_enabled"]) ? true : false;
					if (!isset($this->custom_settings_enabled_list))
						$this->custom_settings_enabled_list = new stdClass();
					$this->custom_settings_enabled_list->{$rw_class} = $rw_custom_settings_enabled;
					$this->SetOption(WP_RW__CUSTOM_SETTINGS_ENABLED, $this->custom_settings_enabled_list);

					$rw_custom_settings = isset($_POST["rw_custom_settings"]) ? $_POST["rw_custom_settings"] : '';
					if (!isset($this->custom_settings_list))
						$this->custom_settings_list = new stdClass();
					$this->custom_settings_list->{$rw_class} = $rw_custom_settings;
					$this->SetOption(WP_RW__CUSTOM_SETTINGS, $this->custom_settings_list);

					if (!isset($this->_visibilityList))
						$this->_visibilityList = new stdClass();
					if (!isset($this->_visibilityList->{$rw_class}))
						$this->_visibilityList->{$rw_class} = new stdClass();
					$this->_visibilityList->{$rw_class}->selected = $rw_visibility;
					$this->_visibilityList->{$rw_class}->exclude = self::IDsCollectionToArray($rw_visibility_exclude);
					$this->_visibilityList->{$rw_class}->include = self::IDsCollectionToArray($rw_visibility_include);
					$this->SetOption(WP_RW__VISIBILITY_SETTINGS, $this->_visibilityList);
					?>
					<div class="updated"><p><strong><?php _e('Settings successfully saved.', WP_RW__ID ); ?></strong></p></div>
				<?php
				}
				else
				{
					/* Get rating alignment.
            ---------------------------------------------------------------------------------------------------------------*/
					$rw_align = $this->GetOption($rw_current_settings["align"]);

					/* Get show on excerpts option.
            ---------------------------------------------------------------------------------------------------------------*/
					// Already loaded.

					/* Get rating options.
            ---------------------------------------------------------------------------------------------------------------*/
					$rw_options = $this->GetOption($rw_current_settings["options"]);

					/* Get availability settings.
            ---------------------------------------------------------------------------------------------------------------*/
					// Already loaded.

					/* Get visibility settings
            ---------------------------------------------------------------------------------------------------------------*/
					// Already loaded.
				}


				$rw_language_str = isset($rw_options->lng) ? $rw_options->lng : WP_RW__DEFAULT_LNG;

				if (!isset($this->_visibilityList->{$rw_class}))
				{
					$this->_visibilityList->{$rw_class} = new stdClass();
					$this->_visibilityList->{$rw_class}->selected = 0;
					$this->_visibilityList->{$rw_class}->exclude = "";
					$this->_visibilityList->{$rw_class}->include = "";
				}
				$rw_visibility_settings = $this->_visibilityList->{$rw_class};

				if (!isset($this->availability_list->{$rw_class})){
					$this->availability_list->{$rw_class} = 0;
				}
				$rw_availability_settings = $this->availability_list->{$rw_class};

				if ($item_with_category)
				{
					if (!isset($this->categories_list->{$rw_class})){
						$this->categories_list->{$rw_class} = array(-1);
					}
					$rw_categories = $this->categories_list->{$rw_class};
				}


				if (!isset($this->custom_settings_enabled_list->{$rw_class}))
					$this->custom_settings_enabled_list->{$rw_class} = false;
				$rw_custom_settings_enabled = $this->custom_settings_enabled_list->{$rw_class};

				if (!isset($this->custom_settings_list->{$rw_class}))
					$this->custom_settings_list->{$rw_class} = '';
				$rw_custom_settings = $this->custom_settings_list->{$rw_class};

				require_once(WP_RW__PLUGIN_DIR . "/languages/{$rw_language_str}.php");
				require_once(WP_RW__PLUGIN_DIR . "/lib/defaults.php");
				require_once(WP_RW__PLUGIN_DIR . "/lib/def_settings.php");

				global $DEFAULT_OPTIONS;
				rw_set_language_options($DEFAULT_OPTIONS, $dictionary, $dir, $hor);

				$theme_font_size_set = false;
				$theme_line_height_set = false;

				$rating_font_size_set = (isset($rw_options->advanced) && isset($rw_options->advanced->font) && isset($rw_options->advanced->font->size));
				$rating_line_height_set = (isset($rw_options->advanced) && isset($rw_options->advanced->layout) && isset($rw_options->advanced->layout->lineHeight));

				$def_options = $DEFAULT_OPTIONS;
				if (isset($rw_options->theme) && $rw_options->theme !== "")
				{
					require_once(WP_RW__PLUGIN_DIR . "/themes/dir.php");

					global $RW_THEMES;

					if (!isset($rw_options->type)){
						$rw_options->type = isset($RW_THEMES["star"][$rw_options->theme]) ? "star" : "nero";
					}
					if (isset($RW_THEMES[$rw_options->type][$rw_options->theme]))
					{
						require(WP_RW__PLUGIN_DIR . "/themes/" . $RW_THEMES[$rw_options->type][$rw_options->theme]["file"]);

						$theme_font_size_set = (isset($theme["options"]->advanced) && isset($theme["options"]->advanced->font) && isset($theme["options"]->advanced->font->size));
						$theme_line_height_set = (isset($theme["options"]->advanced) && isset($theme["options"]->advanced->layout) && isset($theme["options"]->advanced->layout->lineHeight));

						// Enrich theme options with defaults.
						$def_options = rw_enrich_options1($theme["options"], $DEFAULT_OPTIONS);
					}
				}

				// Enrich rating options with calculated default options (with theme reference).
				$rw_options = rw_enrich_options1($rw_options, $def_options);

				// If font size and line height isn't explicitly specified on rating
				// options or rating's theme, updated theme correspondingly
				// to rating size.
				if (isset($rw_options->size))
				{
					$SIZE = strtoupper($rw_options->size);
					if (!$rating_font_size_set && !$theme_font_size_set)
					{
						global $DEF_FONT_SIZE;
						if (!isset($rw_options->advanced)){ $rw_options->advanced = new stdClass(); }
						if (!isset($rw_options->advanced->font)){ $rw_options->advanced->font = new stdClass(); }
						$rw_options->advanced->font->size = $DEF_FONT_SIZE->$SIZE;
					}
					if (!$rating_line_height_set && !$theme_line_height_set)
					{
						global $DEF_LINE_HEIGHT;
						if (!isset($rw_options->advanced)){ $rw_options->advanced = new stdClass(); }
						if (!isset($rw_options->advanced->layout)){ $rw_options->advanced->layout = new stdClass(); }
						$rw_options->advanced->layout->lineHeight = $DEF_LINE_HEIGHT->$SIZE;
					}
				}

				$rw_languages = $this->languages;

				$this->settings->rating_type = $selected_key;
				$this->settings->options = $rw_options;
				$this->settings->languages = $rw_languages;
				$this->settings->language_str = $rw_language_str;
				$this->settings->categories = $rw_categories;
				$this->settings->availability = $rw_availability_settings;
				$this->settings->visibility= $rw_visibility_settings;
				$this->settings->form_hidden_field_name = $rw_form_hidden_field_name;
				$this->settings->custom_settings_enabled = $rw_custom_settings_enabled;
				$this->settings->custom_settings = $rw_custom_settings;
				// Accumulated user ratings support.
				if ( 'users' === $selected_key && $this->IsBBPressInstalled() ) {
					$this->settings->is_user_accumulated = $rw_is_user_accumulated;
				} else if ( 'comments' === $selected_key ) { // Comment ratings mode support
					$this->settings->comment_ratings_mode = $comment_ratings_mode;
				}

				?>
				<div class="wrap rw-dir-ltr rw-wp-container">
					<h2 class="nav-tab-wrapper rw-nav-tab-wrapper">
						<?php foreach ($settings_data as $key => $settings) : ?>
							<a href="<?php echo esc_url(add_query_arg(array('rating' => $key, 'message' => false)));?>" class="nav-tab<?php if ($settings_data[$key] == $rw_current_settings) echo ' nav-tab-active' ?>"><?php _e($settings["tab"], WP_RW__ID);?></a>
						<?php endforeach; ?>
					</h2>

					<form method="post" action="">
						<div id="poststuff">
							<div id="rw_wp_set">
								<?php rw_require_once_view('preview.php'); ?>
								<div class="has-sidebar has-right-sidebar">
									<div class="has-sidebar-content">
										<div class="postbox rw-body">
											<?php
												$enabled = isset($rw_align->ver);
											?>
											<div style="padding: 10px;">
												<label for="rw_show">
													<input id="rw_show" type="checkbox" name="rw_show" value="true"<?php if ($enabled) echo ' checked="checked"';?> onclick="RWM_WP.enable(this);" /> Enable for <?php echo $rw_current_settings["tab"];?>
												</label>
												<?php
													if (true === $rw_current_settings["show_align"])
													{
														?>
														<div class="rw-post-rating-align"<?php if (!$enabled) echo ' style="display: none;"';?>>
															<?php
																$vers = array("top", "bottom");
																$hors = array("left", "center", "right");
															?>
															<select>
																<?php
																	foreach ($vers as $ver) {
																		foreach ( $hors as $hor ) {
																			$checked = false;
																			if ( $enabled ) {
																				$checked = ( $ver == $rw_align->ver && $hor == $rw_align->hor );
																			}
																			?>
																			<option
																				value="<?php echo $ver . " " . $hor; ?>"<?php if ( $checked ) {
																				echo ' selected="selected"';
																			} ?>><?php echo ucwords( $ver ) . ' ' . ucwords( $hor ); ?></option>
																		<?php
																		}
																	}
																?>
															</select>
															<input id="rw_align" name="rw_align" type="hidden" value="<?php echo $rw_align->ver . ' ' . $rw_align->hor ?>">
															<script>
																(function($) {
																	$('.rw-post-rating-align select').chosen({width: '100%'}).change(function(evt, params){
																		$('#rw_align').val(params.selected);
																	});
																})(jQuery);
															</script>
														</div>
													<?php
													}
												?>
											</div>
										</div>
									</div>
								</div>
								<?php
									if ( 'users' === $selected_key ) {
										rw_require_once_view('user_rating_type_options.php');
									} else if ( 'comments' === $selected_key ) {
										rw_require_once_view('comment_rating_mode_options.php');
									}
									rw_require_once_view('options.php');
									rw_require_once_view('availability_options.php');
									rw_require_once_view('visibility_options.php');

									if ($is_blog_post)
										rw_require_once_view('post_views_visibility.php');

									if ($item_with_category)
										rw_require_once_view('categories_availability_options.php');

									rw_require_once_view('settings/frequency.php');
									rw_require_once_view('powerusers.php');
								?>
							</div>
							<div id="rw_wp_set_widgets">
								<?php
									if ($this->fs->is_not_paying())
									{
										// Show random.
										if (0 == rand(0, 1))
											rw_require_once_view('rich-snippets.php');
										else
											rw_require_once_view('upgrade.php');
									}
								?>
								<?php //rw_require_once_view('fb.php'); ?>
								<?php //rw_require_once_view('twitter.php'); ?>
							</div>
						</div>
					</form>
					<div class="rw-body">
						<?php rw_include_once_view('settings/custom_color.php'); ?>
					</div>
				</div>
				<?php fs_require_template('powered-by.php') ?>
				<?php

				// Store options if in save mode.
				if ($this->settings->IsSaveMode())
					$this->_options->store();
			}

			#region Posts/Pages & Comments Support ------------------------------------------------------------------

			var $post_align = false;
			var $post_class = "";
			var $comment_align = false;
			var $activity_align = array();
			var $forum_post_align = false;
			/**
			 * This action invoked when WP starts looping over
			 * the posts/pages. This function checks if Rating-Widgets
			 * on posts/pages and/or comments are enabled, and saved
			 * the settings alignment.
			 */
			function rw_before_loop_start() {
				if ( RWLogger::IsOn() ) {
					$params = func_get_args();
					RWLogger::LogEnterence( "rw_before_loop_start", $params );
				}

				foreach ( $this->_extensions as $ext ) {
					if ( $ext->BlockLoopRatings() ) {
						if ( RWLogger::IsOn() ) {
							RWLogger::Log( 'rw_before_loop_start', 'Blocked by ' . $ext->GetSlug() );
						}

						return;
					}
				}

				// Check if shown on search results.
				if ( is_search() && false === $this->GetOption( WP_RW__SHOW_ON_SEARCH ) ) {
					return;
				}

				// Checks if category.
				if ( is_category() && false === $this->GetOption( WP_RW__SHOW_ON_CATEGORY ) ) {
					return;
				}

				// Checks if shown on archive.
				if ( is_archive() && ! is_category() && false === $this->GetOption( WP_RW__SHOW_ON_ARCHIVE ) ) {
					return;
				}

				if ( $this->InBuddyPressPage() ) {
					return;
				}

				if ( $this->InBBPressPage() ) {
					return;
				}

				$comment_align = $this->GetRatingAlignByType( WP_RW__COMMENTS_ALIGN );
				if ( false !== $comment_align && ! $this->IsHiddenRatingByType( 'comment' ) ) {
					$this->comment_align = $comment_align;

					// Hook comment rating showup.
					add_action( 'comment_text', array( &$this, 'AddCommentRating' ) );
				}

				$postType = get_post_type();

				RWLogger::Log( "rw_before_loop_start", 'Post Type = ' . $postType );

				if ( in_array( $postType, array( 'forum', 'topic', 'reply' ) ) ) {
					return;
				}

				if ( is_home() ) {
					// Get rating front posts alignment.
					$post_align = $this->GetRatingAlignByType( WP_RW__FRONT_POSTS_ALIGN );
					$post_class = "front-post";
				} else if ( is_page() ) {
					// Get rating pages alignment.
					$post_align = $this->GetRatingAlignByType( WP_RW__PAGES_ALIGN );
					$post_class = "page";
				} else {
					// Get rating blog posts alignment.
					$post_align = $this->GetRatingAlignByType( WP_RW__BLOG_POSTS_ALIGN );
					$post_class = "blog-post";
				}

				if ( false !== $post_align && ! $this->IsHiddenRatingByType( $post_class ) ) {
					$this->post_align = $post_align;
					$this->post_class = $post_class;

					// Hook post rating showup.
					add_action( 'the_content', array( &$this, 'AddPostRating' ) );

					RWLogger::Log( "rw_before_loop_start", 'Hooked to the_content()' );

					if ( false !== $this->GetOption( WP_RW__SHOW_ON_EXCERPT ) ) {
						// Hook post excerpt rating showup.
						add_action( 'the_excerpt', array( &$this, 'AddPostRating' ) );

						RWLogger::Log( "rw_before_loop_start", 'Hooked to the_excerpt()' );
					}
				}

				if ( RWLogger::IsOn() ) {
					RWLogger::LogDeparture( "rw_before_loop_start" );
				}
			}

			static function IDsCollectionToArray(&$pIds)
			{
				if (null == $pIds || (is_string($pIds) && empty($pIds)))
					return array();

				if (!is_string($pIds) && is_array($pIds))
					return $pIds;

				$ids = explode(",", $pIds);
				$filtered = array();
				foreach ($ids as $id)
				{
					$id = trim($id);

					if (is_numeric($id))
						$filtered[] = $id;
				}

				return array_unique($filtered);
			}

			function rw_validate_category_availability($pId, $pClass)
			{
				if (RWLogger::IsOn()){ $params = func_get_args(); RWLogger::LogEnterence("rw_validate_category_availability", $params); }

				if (!isset($this->categories_list))
				{
					$this->categories_list = $this->GetOption(WP_RW__CATEGORIES_AVAILABILITY_SETTINGS);

					if (RWLogger::IsOn())
						RWLogger::Log("categories_list", var_export($this->categories_list, true));
				}

				if (!isset($this->categories_list->{$pClass}) ||
				    empty($this->categories_list->{$pClass}))
					return true;

				// Alias.
				$categories = $this->categories_list->{$pClass};

				// Check if all categories.
				if (!is_array($categories) || in_array("-1", $categories))
					return true;

				// No category selected.
				if (count($categories) == 0)
					return false;

				// Get post categories.
				$post_categories = get_the_category($pId);

				$post_categories_ids = array();

				if (is_array($post_categories) && count($post_categories) > 0)
				{
					foreach ($post_categories as $category)
					{
						$post_categories_ids[] = $category->cat_ID;
					}
				}

				$common_categories = array_intersect($categories, $post_categories_ids);

				return (is_array($common_categories) && count($common_categories) > 0);
			}

			function rw_validate_visibility($pId, $pClasses = false)
			{
				if (RWLogger::IsOn()){ $params = func_get_args(); RWLogger::LogEnterence("rw_validate_visibility", $params); }

				if (!isset($this->_visibilityList))
				{
					$this->_visibilityList = $this->GetOption(WP_RW__VISIBILITY_SETTINGS);

					if (RWLogger::IsOn())
						RWLogger::Log("_visibilityList", var_export($this->_visibilityList, true));
				}

				if (is_string($pClasses))
				{
					$pClasses = array($pClasses);
				}
				else if (false === $pClasses)
				{
					foreach ($this->_visibilityList as $class => $val)
					{
						$pClasses[] = $class;
					}
				}

				foreach ($pClasses as $class)
				{
					if (!isset($this->_visibilityList->{$class}))
						continue;

					// Alias.
					$visibility_list = $this->_visibilityList->{$class};

					// All visible.
					if ($visibility_list->selected === WP_RW__VISIBILITY_ALL_VISIBLE)
						continue;

					$visibility_list->exclude = self::IDsCollectionToArray($visibility_list->exclude);
					$visibility_list->include = self::IDsCollectionToArray($visibility_list->include);

					if (($visibility_list->selected === WP_RW__VISIBILITY_EXCLUDE && in_array($pId, $visibility_list->exclude)) ||
					    ($visibility_list->selected === WP_RW__VISIBILITY_INCLUDE && !in_array($pId, $visibility_list->include)))
					{
						return false;
					}
				}

				return true;
			}

			/**
			 * Determine if this post's rating is read-only.
			 *
			 * @param int $post_id The post's ID in the _posts table.
			 * @param string $class A post type which is also a name of a class
			 * that contains the post's read-only-related settings.
			 * @return boolean True if the rating is read-only.
			 */
			function is_rating_readonly($post_id, $class) {
				if (RWLogger::IsOn()) {
					$params = func_get_args();
					RWLogger::LogEnterence('rw_is_rating_readonly', $params);
				}

				// Avoid further checking, return immediately if the post type is not supported.
				if (!in_array($class, array('comment', 'post', 'page', 'product', 'topic', 'reply'))) {
					return false;
				}

				if (!isset($this->_readonly_list)) {
					$this->_readonly_list = $this->GetOption(WP_RW__READONLY_SETTINGS);

					if(RWLogger::IsOn()) {
						RWLogger::Log('_readonly_list', var_export($this->_readonly_list, true));
					}
				}

				switch ($class) {
					case 'page':
						$option_name = WP_RW__PAGES_OPTIONS;
						break;
					case 'comment':
						$option_name = WP_RW__COMMENTS_OPTIONS;
						break;
					case 'product':
						$option_name = WP_RW__WOOCOMMERCE_PRODUCTS_OPTIONS;
						break;
					case 'topic':
					case 'reply':
						$option_name = WP_RW__FORUM_POSTS_OPTIONS;
						break;
					default:
						$option_name = WP_RW__BLOG_POSTS_OPTIONS;
				}

				/*
				 * If there is no saved option yet,
				 * return the default state based on the Read Only admin setting of this post type.
				 */
				if (!isset($this->_readonly_list->{$class})) {
					$options = $this->GetOption($option_name);
					return isset( $options->readOnly ) ? $options->readOnly : false;
				}

				// Alias.
				$readonly_list = $this->_readonly_list->{$class};

				$readonly_list->active = self::IDsCollectionToArray($readonly_list->active);
				$readonly_list->readonly = self::IDsCollectionToArray($readonly_list->readonly);

				/*
				 * If the read-only state of this post's rating has not been set before,
				 * return the default state based on the Read Only admin setting of this post type.
				 */
				if ((!in_array($post_id, $readonly_list->active)) &&
				    (!in_array($post_id, $readonly_list->readonly))) {
					$options = $this->GetOption($option_name);
					return isset( $options->readOnly ) ? $options->readOnly : false;
				}

				/*
				 * If the post ID is not present in the list of active post IDs or
				 * the post ID is present in the list of readonly post IDs
				 * then this post's rating is read-only.
				 */
				if ((!in_array($post_id, $readonly_list->active)) ||
				    (in_array($post_id, $readonly_list->readonly))) {
					return true;
				}

				return false;
			}

			function add_to_visibility_list($pId, $pClasses, $pIsVisible = true) {
				if ( RWLogger::IsOn() ) {
					$params = func_get_args();
					RWLogger::LogEnterence( "add_to_visibility_list", $params, true );
				}

				if ( ! isset( $this->_visibilityList ) ) {
					$this->_visibilityList = $this->GetOption( WP_RW__VISIBILITY_SETTINGS );
				}

				if ( is_string( $pClasses ) ) {
					$pClasses = array( $pClasses );
				} else if ( ! is_array( $pClasses ) || 0 == count( $pClasses ) ) {
					return;
				}

				foreach ( $pClasses as $class ) {
					if ( RWLogger::IsOn() ) {
						RWLogger::Log( "add_to_visibility_list", "CurrentClass = " . $class );
					}

					if ( ! isset( $this->_visibilityList->{$class} ) ) {
						$this->_visibilityList->{$class}           = new stdClass();
						$this->_visibilityList->{$class}->selected = WP_RW__VISIBILITY_ALL_VISIBLE;
					}

					$visibility_list = $this->_visibilityList->{$class};

					if ( ! isset( $visibility_list->include ) || empty( $visibility_list->include ) ) {
						$visibility_list->include = array();
					}

					$visibility_list->include = self::IDsCollectionToArray( $visibility_list->include );

					if ( ! isset( $visibility_list->exclude ) || empty( $visibility_list->exclude ) ) {
						$visibility_list->exclude = array();
					}

					$visibility_list->exclude = self::IDsCollectionToArray( $visibility_list->exclude );

					if ( $visibility_list->selected == WP_RW__VISIBILITY_ALL_VISIBLE ) {
						if ( RWLogger::IsOn() ) {
							RWLogger::Log( "add_to_visibility_list", "Currently All-Visible for {$class}" );
						}

						if ( true == $pIsVisible ) {
							// Already all visible so just ignore this.
						} else {
							// If all visible, and selected to hide this post - exclude specified post/page.
							$visibility_list->selected  = WP_RW__VISIBILITY_EXCLUDE;
							$visibility_list->exclude[] = $pId;
						}
					} else {
						// If not all visible, move post id from one list to another (exclude/include).

						if ( RWLogger::IsOn() ) {
							RWLogger::Log( "add_to_visibility_list", "Currently NOT All-Visible for {$class}" );
						}

						$remove_from = ( $pIsVisible ? "exclude" : "include" );
						$add_to      = ( $pIsVisible ? "include" : "exclude" );

						if ( RWLogger::IsOn() ) {
							RWLogger::Log( "add_to_visibility_list", "Remove {$pId} from {$class}'s " . strtoupper( ( $pIsVisible ? "exclude" : "include" ) ) . "list." );
						}
						if ( RWLogger::IsOn() ) {
							RWLogger::Log( "add_to_visibility_list", "Add {$pId} to {$class}'s " . strtoupper( ( ! $pIsVisible ? "exclude" : "include" ) ) . "list." );
						}

						if ( ! in_array( $pId, $visibility_list->{$add_to} ) ) // Add to include list.
						{
							$visibility_list->{$add_to}[] = $pId;
						}

						if ( ( $key = array_search( $pId, $visibility_list->{$remove_from} ) ) !== false ) // Remove from exclude list.
						{
							$remove_from = array_splice( $visibility_list->{$remove_from}, $key, 1 );
						}

						if ( WP_RW__VISIBILITY_EXCLUDE == $visibility_list->selected && 0 === count( $visibility_list->exclude ) ) {
							$visibility_list->selected = WP_RW__VISIBILITY_ALL_VISIBLE;
						}
					}
				}

				if ( RWLogger::IsOn() ) {
					RWLogger::LogDeparture( "add_to_visibility_list" );
				}
			}

			/**
			 * Add/remove this post's ID from/to the active or readonly list of post IDs.
			 *
			 * @param number   $post_id The post ID in the _posts table.
			 * @param string[] $classes A collection of post types. Each post type is a name of a class that holds this post type's read-only-related settings.
			 * @param bool     $is_readonly
			 */
			function add_to_readonly($post_id, $classes, $is_readonly = true) {
				if (RWLogger::IsOn()) {
					$params = func_get_args();
					RWLogger::LogEnterence('add_to_readonly', $params, true);
				}

				if (!isset($this->_readonly_list)) {
					$this->_readonly_list = $this->GetOption(WP_RW__READONLY_SETTINGS);
				}

				if (is_string($classes)) {
					$classes = array($classes);
				} elseif (!is_array($classes) || 0 == count($classes)) {
					return;
				}

				foreach ($classes as $class) {
					if (RWLogger::IsOn()) {
						RWLogger::Log('add_to_readonly', "CurrentClass = $class");
					}

					if (!isset($this->_readonly_list->{$class})) {
						$this->_readonly_list->{$class} = new stdClass();
					}

					$readonly_list = $this->_readonly_list->{$class};

					if (!isset($readonly_list->active) || empty($readonly_list->active)) {
						$readonly_list->active = array();
					}

					$readonly_list->active = self::IDsCollectionToArray($readonly_list->active);

					if (!isset($readonly_list->readonly) || empty($readonly_list->readonly)) {
						$readonly_list->readonly = array();
					}

					$readonly_list->readonly = self::IDsCollectionToArray($readonly_list->readonly);

					$remove_from = ($is_readonly ? 'active' : 'readonly');
					$add_to = ($is_readonly ? 'readonly' : 'active');

					if (RWLogger::IsOn()) {
						RWLogger::Log('add_to_readonly', "Remove {$post_id} from {$class}'s " . strtoupper(($is_readonly ? 'active' : 'readonly')) . ' list.');
					}

					if (RWLogger::IsOn()) {
						RWLogger::Log('add_to_readonly', "Add {$post_id} to {$class}'s " . strtoupper((!$is_readonly ? 'readonly' : 'active')) . ' list.');
					}

					if (!in_array($post_id, $readonly_list->{$add_to})) {
						// Add to the include list.
						$readonly_list->{$add_to}[] = $post_id;
					}

					if (($key = array_search($post_id, $readonly_list->{$remove_from})) !== false) {
						// Remove from the exclude list.
						$remove_from = array_splice($readonly_list->{$remove_from}, $key, 1);
					}
				}

				if (RWLogger::IsOn()) {
					RWLogger::LogDeparture('add_to_readonly');
				}
			}

			var $is_user_logged_in;
			function rw_validate_availability($pClass)
			{
				if (!isset($this->is_user_logged_in))
				{
					// Check if user logged in for availability check.
					$this->is_user_logged_in = is_user_logged_in();

					$this->availability_list = $this->GetOption(WP_RW__AVAILABILITY_SETTINGS);
				}

				if (true === $this->is_user_logged_in ||
				    !isset($this->availability_list->{$pClass}))
				{
					return WP_RW__AVAILABILITY_ACTIVE;
				}

				return $this->availability_list->{$pClass};
			}

			function GetCustomSettings($pClass)
			{
				$this->custom_settings_enabled_list = $this->GetOption(WP_RW__CUSTOM_SETTINGS_ENABLED);

				if (!isset($this->custom_settings_enabled_list->{$pClass}) || false === $this->custom_settings_enabled_list->{$pClass})
					return '';

				$this->custom_settings_list = $this->GetOption(WP_RW__CUSTOM_SETTINGS);

				return isset($this->custom_settings_list->{$pClass}) ? stripslashes($this->custom_settings_list->{$pClass}) : '';
			}

			function IsVisibleRating($pElementID, $pClass, $pValidateCategory = true, $pValidateVisibility = true)
			{
				RWLogger::LogEnterence('IsVisibleRating');

				RWLogger::Log('IsVisibleRating', 'class = ' . $pClass);

				// Check if post category is selected.
				if ($pValidateCategory && false === $this->rw_validate_category_availability($pElementID, $pClass))
					return false;
				// Checks if item isn't specificaly excluded.
				if ($pValidateVisibility && false === $this->rw_validate_visibility($pElementID, $pClass))
					return false;

				return true;
			}

			function IsVisibleCommentRating($pComment)
			{
				/**
				 * Check if comment category is selected.
				 *
				 *   NOTE:
				 *       $pComment->comment_post_ID IS NOT A MISTAKE
				 *       We transfer the comment parent post id because the availability
				 *       method loads the element categories by get_the_category() which only
				 *       works on post ids.
				 */
				if (false === $this->rw_validate_category_availability($pComment->comment_post_ID, 'comment'))
					return false;
				// Checks if item isn't specificaly excluded.
				if (false === $this->rw_validate_visibility($pComment->comment_ID, 'comment'))
					return false;

				return true;
			}

			function GetPostImage($pPost, $pExpiration = false)
			{
				if (RWLogger::IsOn()){ $params = func_get_args(); RWLogger::LogEnterence("GetPostImage", $params); }

				$cacheKey = 'rw_post_thumb_' . $pPost->ID;
				$img = false;
				if (false !== $pExpiration)
				{
					// Try to get cached item.
					$img = get_transient($cacheKey);

					if (RWLogger::IsOn())
						RWLogger::Log('IS_CACHED', (false !== $img) ? 'true' : 'false');
				}

				if (false === $img)
				{
					if (function_exists('has_post_thumbnail') && has_post_thumbnail($pPost->ID))
					{
						$img = wp_get_attachment_image_src(get_post_thumbnail_id($pPost->ID), 'single-post-thumbnail');

						if (RWLogger::IsOn())
							RWLogger::Log('GetPostImage', 'Featured Image = ' . $img[0]);

						$img = $img[0];
					}
					else
					{
						ob_start();
						ob_end_clean();

						$images = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $pPost->post_content, $matches);

						if($images > 0)
						{
							if (RWLogger::IsOn())
								RWLogger::Log('GetPostImage', 'Extracted post image = ' . $matches[1][0]);

							// Return first image out of post's content.
							$img = $matches[1][0];
						}
						else
						{
							if (RWLogger::IsOn())
								RWLogger::Log('GetPostImage', 'No post image');

							$img = '';
						}
					}

					if (false !== $pExpiration && !empty($cacheKey))
						set_transient($cacheKey, $img, $pExpiration);
				}

				return !empty($img) ? $img : false;
			}

			/**
			 * @author Vova Feldman (@svovaf)
			 * @since  2.3.7
			 *
			 * @param string $content
			 * @param string $rclass
			 *
			 * @return string
			 */
			function add_post_rating($content, $rclass)
			{
				global $post;

				RWLogger::LogEnterence('add_post_rating');

				if ($this->InBuddyPressPage())
				{
					RWLogger::LogDeparture('add_post_rating');
					return $content;
				}

				$ratingHtml = $this->EmbedRatingIfVisibleByPost($post, $rclass, true, $this->post_align->hor, false);

				return ('top' === $this->post_align->ver) ?
					$ratingHtml . $content :
					$content . $ratingHtml;
			}

			/**
			 * @author Vova Feldman (@svovaf)
			 * @since  2.3.7
			 *
			 * @param string $content
			 *
			 * @return string
			 */
			function add_front_post_rating($content)
			{
				return $this->add_post_rating($content, 'front-post');
			}

			/**
			 * If Rating-Widget enabled for Posts, attach it
			 * html container to the post content.
			 *
			 * @param string $content
			 * @return string
			 */
			function AddPostRating($content)
			{
				return $this->add_post_rating($content, $this->post_class);
			}

			/**
			 * If Rating-Widget enabled for Comments, attach it
			 * html container to the comment content.
			 *
			 * @param string $content
			 *
			 * @return string
			 */
			function AddCommentRating($content)
			{
				if (RWLogger::IsOn()){ $params = func_get_args(); RWLogger::LogEnterence('AddCommentRating', $params); }

				global $comment;

				if (!$this->IsVisibleCommentRating($comment))
					return $content;

				$ratingHtml = $this->EmbedRatingByComment($comment, 'comment', $this->comment_align->hor);

				return ('top' === $this->comment_align->ver) ?
					$ratingHtml . $content :
					$content . $ratingHtml;
			}

			#endregion Posts/Pages & Comments Support ------------------------------------------------------------------

			/**
			 * Generate rating container HTML.
			 *
			 * @param $pUrid
			 * @param $pElementClass
			 * @param bool $pAddSchema
			 * @param string $pTitle
			 * @param array $pOptions
			 * @return string Rating container HTML.
			 */
			private function GetRatingHtml($pUrid, $pElementClass, $pAddSchema = false, $pTitle = "", $pPermalink = '', $pOptions = array()) {
				if ( RWLogger::IsOn() ) {
					$params = func_get_args();
					RWLogger::LogEnterence( "GetRatingHtml", $params );
				}

				$ratingData = '';
				foreach ( $pOptions as $key => $val ) {
					if ( ! empty( $val ) && '' !== trim( $val ) ) {
						RWLogger::Log( 'GetRatingHtml', "Adding options for: urid={$pUrid}; data-{$key}={$val}" );
						$ratingData .= ' data-' . $key . '="' . esc_attr( trim( $val ) ) . '"';
					}
				}

				$rating_html = '<div class="rw-ui-container rw-class-' . $pElementClass . ' rw-urid-' . $pUrid . '"' . $ratingData . '></div>';

				if ( $this->fs->is_plan_or_trial__premium_only( 'professional' ) ) {
					if ( true === $pAddSchema && 'front-post' !== $pElementClass && ! isset( $_REQUEST['schema_test'] ) ) {
						$rich_snippet_settings   = $this->get_rich_snippet_settings();
						$type_wrapper_available  = $rich_snippet_settings->type_wrapper_available;
						$properties_availability = $rich_snippet_settings->properties_availability;

						RWLogger::Log( 'GetRatingHtml', "Adding schema for: urid={$pUrid}; rclass={$pElementClass}" );

						/*
						 * Replace the value of the $pUrid variable with the value of the $pOptions['uarid'] array element
						 * if the rating is multi-criterion.
						 *
						 * If the rating is multi-criterion, each criterion rating has a uarid property whose value is the ID of the summary rating.
						 * Each criterion rating's urid is in this format: {{$pUrid-$criterionId}}.
						 *
						 * If $pUrid contains the hyphen ( - ) character and the uarid property is set, we assume that this is a criterion rating,
						 * and replacing the value of the $pUrid with the value of the uarid property is necessary in order to set the correct
						 * number of votes in the rich snippet schema which is the number of votes of the summary rating.
						 */
						if ( false !== strpos( $pUrid, '-' ) && isset( $pOptions['uarid'] ) ) {
							$pUrid = $pOptions['uarid'];
						}

						$data = $this->GetRatingDataByRatingID( $pUrid, 2 );
						if ( false !== $data && $data['votes'] > 0 ) {
							if ( RWLogger::IsOn() ) {
								RWLogger::Log( 'ratingValue', $data['rate'] );
								RWLogger::Log( 'ratingCount', $data['votes'] );
							}

							// WooCommerce is already adding all the product schema metadata.
							/*$schema_root = 'itemscope itemtype="http://schema.org/Product"';
							$schema_title_prop = 'itemprop="name"';
							*/
							if ( false === strpos($pElementClass, 'product') ) {
								if ( !$type_wrapper_available ) {
									$rating_html = '<div itemscope itemtype="http://schema.org/Article">' . $rating_html;
								}

								global $post;

								if ( !empty($pTitle) ) {
									if ( !$properties_availability['name'] ) {
										$rating_html .= '<meta itemprop="name" content="' . esc_attr($pTitle) . '" />';
									}

									if ( !$properties_availability['headline'] ) {
										$rating_html .= '<meta itemprop="headline" content="' . esc_attr($pTitle) . '" />';
									}

									if ( !$properties_availability['description'] ) {
										$post_excerpt =	$this->GetPostExcerpt($post);

										$rating_html .= '<meta itemprop="description" content="' . esc_attr($post_excerpt) . '" />';
									}
								}

								if ( !$properties_availability['image'] ) {
									$image_url = $this->get_rich_snippet_default_image($post->ID);
									$rating_html .= '<meta itemprop="image" content="' . $image_url . '" />';
								}

								if ( !$properties_availability['datePublished'] ) {
									// Use 'c' for ISO 8601 date format.
									$iso8601_date = mysql2date( 'c', $post->post_date );
									$rating_html .= '<meta itemprop="datePublished" content="' . $iso8601_date . '" />';
								}

								if ( !$properties_availability['url'] && !empty($pPermalink) ) {
									$rating_html .= '<meta itemprop="url" content="' . esc_attr($pPermalink) . '" />';
								}
							}

//						$title = mb_convert_to_utf8(trim($pTitle));
							$rating_html .= '
    <div itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating">
        <meta itemprop="worstRating" content="0" />
        <meta itemprop="bestRating" content="5" />
        <meta itemprop="ratingValue" content="' . $data['rate'] . '" />
        <meta itemprop="ratingCount" content="' . $data['votes'] . '" />
    </div>';

							if ( ! $type_wrapper_available ) {
								$rating_html .= '</div>';
							}
						}
					}
				}

				return $rating_html;
			}

			/**
			 * Retrieves the post's featured image or an image from the gallery if the featured image is not available. Returns a placeholder image if no image is retrieved.
			 *
			 * @author Leo Fajardo (@leorw)
			 * @since 2.5.8
			 *
			 * @param int $wp_post_id The ID of a WordPress post.
			 *
			 * @return string
			 */
			function get_rich_snippet_default_image($wp_post_id) {
				// Retrieve the post's featured image URL if available.
				$image_url = $this->GetPostFeaturedImage($wp_post_id);

				// Retrieve an image from the gallery if the post's featured image is not available.
				if ( empty($image_url) ) {
					$image_url = $this->get_attachment_image();
				}

				// Retrieve the URL of the placeholder image if there is no available image from the site.
				if ( empty($image_url) ) {
					$image_url = rw_get_plugin_img_path('top-rated/placeholder.png');
				}

				return $image_url;
			}

			/**
			 * Retrieves an image from the gallery and returns its URL.
			 *
			 * @author Leo Fajardo (@leorw)
			 * @since 2.5.8
			 *
			 * @return string
			 */
			function get_attachment_image() {
				$images = get_posts( array(
					'post_type' => 'attachment',
					'posts_per_page' => 1,
					'post_status' => 'inherit',
					'post_mime_type' => 'image'
				) );

				$image_url = '';

				if ( is_array($images) && !empty($images) ) {
					$image = $images[0];
					$image_url = $image->guid;
				}

				return $image_url;
			}

			#region BuddyPress ------------------------------------------------------------------

			function InBuddyPressPage()
			{
				if (!$this->IsBuddyPressInstalled())
					return;

				if (!isset($this->_inBuddyPress))
				{
					$this->_inBuddyPress = false;

					if (function_exists('bp_is_blog_page'))
						$this->_inBuddyPress =  !bp_is_blog_page();
					/*if (function_exists('bp_is_activity_front_page'))
                $this->_inBuddyPress =  $this->_inBuddyPress || bp_is_blog_page();
            if (function_exists('bp_is_current_component'))
                $this->_inBuddyPress =  $this->_inBuddyPress || bp_is_current_component($bp->current_component);*/

					if (RWLogger::IsOn())
						RWLogger::Log("InBuddyPressPage", ($this->_inBuddyPress ? 'TRUE' : 'FALSE'));
				}

				return $this->_inBuddyPress;
			}

			function IsBuddyPressInstalled()
			{
				return (defined('WP_RW__BP_INSTALLED') && WP_RW__BP_INSTALLED && (!function_exists('is_plugin_active') || is_plugin_active(WP_RW__BP_CORE_FILE)));
			}

			function BuddyPressBeforeActivityLoop($has_activities)
			{
				if (RWLogger::IsOn()){ $params = func_get_args(); RWLogger::LogEnterence("BuddyPressBeforeActivityLoop", $params); }

				$this->_inBuddyPress = true;

				/**
				 * New BuddyPress versions activity is loaded as part of regular post,
				 * thus we want to remove standard post rating because it's useless.
				 */
				remove_action('the_content', array(&$this, 'AddPostRating'));
				remove_action('the_excerpt', array(&$this, 'AddPostRating'));

				if (!$has_activities)
					return false;

				$items = array(
					"activity-update" => array(
						"align_key" => WP_RW__ACTIVITY_UPDATES_ALIGN,
						"enabled" => false,
					),
					"activity-comment" => array(
						"align_key" => WP_RW__ACTIVITY_COMMENTS_ALIGN,
						"enabled" => false,
					),
					"new-blog-post" => array(
						"align_key" => WP_RW__ACTIVITY_BLOG_POSTS_ALIGN,
						"enabled" => false,
					),
					"new-blog-comment" => array(
						"align_key" => WP_RW__ACTIVITY_BLOG_COMMENTS_ALIGN,
						"enabled" => false,
					),
					/*"new-forum-topic" => array(
                "align_key" => WP_RW__ACTIVITY_FORUM_TOPICS_ALIGN,
                "enabled" => false,
            ),*/
					"new-forum-post" => array(
						"align_key" => WP_RW__ACTIVITY_FORUM_POSTS_ALIGN,
						"enabled" => false,
					),
				);

				$ver_top = false;
				$ver_bottom = false;
				foreach ($items as $key => &$item)
				{
					$align = $this->GetRatingAlignByType($item["align_key"]);
					$item["enabled"] = (false !== $align);

					if (!$item["enabled"] || $this->IsHiddenRatingByType($key))
						continue;

					$this->activity_align[$key] = $align;

					if ($align->ver === "top")
						$ver_top = true;
					else
						$ver_bottom = true;

				}

				if ( $ver_top ) {
					// Hook activity TOP rating.
					add_filter("bp_get_activity_action", array(&$this, "rw_display_activity_rating_top"));
				}

				if ( $ver_bottom ) {
					// Hook activity BOTTOM rating.
					if ( is_user_logged_in() ) {
						// The methods hooked into this action are invoked when the user is logged in.
						// We hook into this action to align the rating to BuddyPress' Comment, Favorite, or Delete button which is available for logged in user.
						add_action("bp_activity_entry_meta", array(&$this, "rw_display_activity_rating_bottom"));
					} else {
						// This is the only good action that we can use when there is no logged in user.
						add_action("bp_activity_entry_content", array(&$this, "rw_display_activity_rating_bottom"));
					}
				}

				if ( true === $items["activity-comment"]["enabled"] ) {
					// Hook activity-comment rating showup.
					add_filter("bp_get_activity_content", array(&$this, "rw_display_activity_comment_rating"));
				}

				return true;
			}

			private function GetBuddyPressRating($ver, $horAlign = true)
			{
				RWLogger::LogEnterence("GetBuddyPressRating");

				global $activities_template;

				// Set current activity-comment to current activity update (recursive comments).
				$this->current_comment = $activities_template->activity;

				$rclass = str_replace("_", "-", bp_get_activity_type());

				$is_forum_topic = ($rclass === "new-forum-topic");

				if ($is_forum_topic && !$this->IsBBPressInstalled())
					return false;

				if (!in_array($rclass, array('forum-post', 'forum-reply', 'new-forum-post', 'user-forum-post', 'user', 'activity-update', 'user-activity-update', 'activity-comment', 'user-activity-comment')))
					// If unknown activity type, change it to activity update.
					$rclass = 'activity-update';

				if ($is_forum_topic)
					$rclass = "new-forum-post";

				// Check if item rating is top positioned.
				if (!isset($this->activity_align[$rclass]) || $ver !== $this->activity_align[$rclass]->ver)
					return false;

				// Get item id.
				$item_id = ("activity-update" === $rclass || "activity-comment" === $rclass) ?
					bp_get_activity_id() :
					bp_get_activity_secondary_item_id();

				if ($is_forum_topic)
				{
					// If forum topic, then we must extract post id
					// from forum posts table, because secondary_item_id holds
					// topic id.
					if (function_exists("bb_get_first_post"))
					{
						$post = bb_get_first_post($item_id);
					}
					else
					{
						// Extract post id straight from the BB DB.
						global $bb_table_prefix;
						// Load bbPress config file.
						@include_once(WP_RW__BBP_CONFIG_LOCATION);

						// Failed loading config file.
						if (!defined("BBDB_NAME"))
							return false;

						$connection = null;
						if (!$connection = mysql_connect(BBDB_HOST, BBDB_USER, BBDB_PASSWORD, true)){ return false; }
						if (!mysql_selectdb(BBDB_NAME, $connection)){ return false; }
						$results = mysql_query("SELECT * FROM {$bb_table_prefix}posts WHERE topic_id={$item_id} AND post_position=1", $connection);
						$post = mysql_fetch_object($results);
					}

					if (!isset($post->post_id) && empty($post->post_id))
						return false;

					$item_id = $post->post_id;
				}

				// If the item is post, queue rating with post title.
				$title = ("new-blog-post" === $rclass) ?
					get_the_title($item_id) :
					bp_get_activity_content_body();// $activities_template->activity->content;

				$options = array();

				$owner_id = bp_get_activity_user_id();

				// Add accumulator id if user accumulated rating.
				if ($this->IsUserAccumulatedRating())
					$options['uarid'] = $this->_getUserRatingGuid($owner_id);

				return $this->EmbedRatingIfVisible(
					$item_id,
					$owner_id,
					strip_tags($title),
					bp_activity_get_permalink(bp_get_activity_id()),
					$rclass,
					false,
					($horAlign ? $this->activity_align[$rclass]->hor : false),
					false,
					$options,
					false   // Don't validate category - there's no category for bp items
				);
				/*
        // Queue activity rating.
        $this->QueueRatingData($urid, strip_tags($title), bp_activity_get_permalink($activities_template->activity->id), $rclass);

        // Return rating html container.
        return '<div class="rw-ui-container rw-class-' . $rclass . ' rw-urid-' . $urid . '"></div>';*/
			}

			// Activity item top rating.
			function rw_display_activity_rating_top($action)
			{
				RWLogger::LogEnterence("rw_display_activity_rating_top");

				$rating_html = $this->GetBuddyPressRating("top");

				return $action . ((false === $rating_html) ? '' : $rating_html);
			}

			// Activity item bottom rating.
			function rw_display_activity_rating_bottom($id = "", $type = "")
			{
				RWLogger::LogEnterence("rw_display_activity_rating_bottom");

				$rating_html = $this->GetBuddyPressRating("bottom", false);

				if (false !== $rating_html)
					// Echo rating html container on bottom actions line.
					echo $rating_html;
			}

			/*var $current_comment;
    function rw_get_current_activity_comment($action)
    {
        global $activities_template;

        // Set current activity-comment to current activity update (recursive comments).
        $this->current_comment = $activities_template->activity;

        return $action;
    }*/

			// Activity-comment.
			function rw_display_activity_comment_rating($comment_content)
			{
				if (RWLogger::IsOn()){ $params = func_get_args(); RWLogger::LogEnterence("rw_display_activity_comment_rating", $params); }

				// If this is a newly inserted comment, assign it to $this->current_comment
				if (isset($_POST['action']) && 'new_activity_comment' == $_POST['action']) {
					global $activities_template;

					$current_comment = $activities_template->activity->current_comment;
					$parent_comment = $activities_template->activity_parents[$current_comment->item_id];

					$current_comment->parent = $parent_comment;

					$this->current_comment = $parent_comment;
					$this->current_comment->children = array($current_comment->id => $current_comment);
				}

				if (!isset($this->current_comment) || null === $this->current_comment)
				{
					if (RWLogger::IsOn()){ RWLogger::Log("rw_display_activity_comment_rating", "Current comment is not set."); }

					return $comment_content;
				}

				// Find current comment.
				while (!$this->current_comment->children || false === current($this->current_comment->children))
				{
					$this->current_comment = $this->current_comment->parent;
					next($this->current_comment->children);
				}

				$parent = $this->current_comment;
				$this->current_comment = current($this->current_comment->children);
				$this->current_comment->parent = $parent;

				/*
        // Check if comment rating isn't specifically excluded.
        if (false === $this->rw_validate_visibility($this->current_comment->id, "activity-comment"))
            return $comment_content;

        // Get activity comment user-rating-id.
        $comment_urid = $this->_getActivityRatingGuid($this->current_comment->id);

        // Queue activity-comment rating.
        $this->QueueRatingData($comment_urid, strip_tags($this->current_comment->content), bp_activity_get_permalink($this->current_comment->id), "activity-comment");

        $rw = '<div class="rw-' . $this->activity_align["activity-comment"]->hor . '"><div class="rw-ui-container rw-class-activity-comment rw-urid-' . $comment_urid . '"></div></div><p></p>';
        */

				$options = array();

				// Add accumulator id if user accumulated rating.
				if ($this->IsUserAccumulatedRating())
					$options['uarid'] = $this->_getUserRatingGuid($this->current_comment->user_id);

				$rw = $this->EmbedRatingIfVisible(
					$this->current_comment->id,
					$this->current_comment->user_id,
					strip_tags($this->current_comment->content),
					bp_activity_get_permalink($this->current_comment->id),
					'activity-comment',
					false,
					$this->activity_align['activity-comment']->hor,
					false,
					$options,
					false
				);

				// Attach rating html container.
				return ($this->activity_align["activity-comment"]->ver == "top") ?
					$rw . $comment_content :
					$comment_content . $rw;
			}

			private function GetRatingAlignByType($pType)
			{
				$align = $this->GetOption($pType);

				return (isset($align) && isset($align->hor)) ? $align : false;
			}

			private function IsHiddenRatingByType($pType)
			{
				return (WP_RW__AVAILABILITY_HIDDEN === $this->rw_validate_availability($pType));
			}

			// User profile.
			function rw_display_user_profile_rating()
			{
				if (RWLogger::IsOn()){ $params = func_get_args(); RWLogger::LogEnterence("rw_display_user_profile_rating", $params); }

				$align = $this->GetRatingAlignByType(WP_RW__USERS_ALIGN);

				if (false === $align || $this->IsHiddenRatingByType('user'))
					return;

				$ratingHtml = $this->EmbedRatingIfVisibleByUser(buddypress()->displayed_user, 'user', 'display: block;');

				echo $ratingHtml;
			}

			function InitBuddyPress()
			{
				if (RWLogger::IsOn())
					RWLogger::LogEnterence("InitBuddyPress");

				if (!defined('WP_RW__BP_INSTALLED'))
					define('WP_RW__BP_INSTALLED', true);

				// Add activity-related action if BuddyPress is inserting a new status update or comment
				$bp_post_request = false;
				if (isset($_POST['action'])
				    && isset($_POST['cookie'])
				    && 0 === strpos($_POST['cookie'], 'bp-activity')) {

					$bp_post_request = true;
				}

				if (!is_admin() || $bp_post_request) {
					// Activity page.
					add_action("bp_has_activities", array(&$this, "BuddyPressBeforeActivityLoop"));
				}

				if (!is_admin()) {
					// Forum topic page.
					add_filter("bp_has_topic_posts", array(&$this, "rw_before_forum_loop"));

					// User profile page.
					add_action("bp_before_member_header_meta", array(&$this, "rw_display_user_profile_rating"));
				}
			}

			function SetupBuddyPress()
			{
				if (RWLogger::IsOn())
					RWLogger::LogEnterence("SetupBuddyPress");

				if (function_exists('bp_activity_get_specific'))
				{
					// BuddyPress earlier than v.1.5
					$this->InitBuddyPress();
				}
				else
				{
					// BuddyPress v.1.5 and latter.
					add_action("bp_include", array(&$this, "InitBuddyPress"));
				}
			}

			#endregion BuddyPress Support Actions ------------------------------------------------------------------

			#region bbPress ------------------------------------------------------------------

			function InBBPressPage()
			{
				if (!$this->IsBBPressInstalled())
					return false;

				if (!isset($this->_inBBPress))
				{
					$this->_inBBPress = false;
					if (function_exists('bbp_is_forum'))
					{
//                $this->_inBBPress = $this->_inBBPress || ('' !== bb_get_location());
						$this->_inBBPress = $this->_inBBPress || bbp_is_forum(get_the_ID());
						$this->_inBBPress = $this->_inBBPress || bbp_is_single_user();
//                bbp_is_user
//                $this->_inBBPress = $this->_inBBPress || bb_is_feed();
					}

					if (RWLogger::IsOn())
						RWLogger::Log("InBBPressPage", ($this->_inBuddyPress ? 'TRUE' : 'FALSE'));
				}

				return $this->_inBBPress;
			}

			function IsBBPressInstalled()
			{
				if (!defined('WP_RW__BBP_INSTALLED'))
					define('WP_RW__BBP_INSTALLED', false);

				return WP_RW__BBP_INSTALLED;// && (!function_exists('is_plugin_active') || is_plugin_active(WP_RW__BP_CORE_FILE));
			}

			function SetupBBPress()
			{
				if (RWLogger::IsOn())
					RWLogger::LogEnterence("SetupBBPress");

				if ($this->fs->is_plan_or_trial__premium_only('professional'))
				{
					define('WP_RW__BBP_CONFIG_LOCATION', get_site_option('bb-config-location', ''));

					if (!defined('WP_RW__BBP_INSTALLED'))
					{
						if ('' !== WP_RW__BBP_CONFIG_LOCATION)
							define('WP_RW__BBP_INSTALLED', true);
						else
						{
							include_once(ABSPATH . 'wp-admin/includes/plugin.php');
							define('WP_RW__BBP_INSTALLED', is_plugin_active('bbpress/bbpress.php'));
						}
					}
				}
				else
				{
					define('WP_RW__BBP_INSTALLED', false);
				}

				if (WP_RW__BBP_INSTALLED && !is_admin() /* && is_bbpress()*/)
					$this->SetupBBPressActions();
			}

			function SetupBBPressActions()
			{
				add_filter('bbp_has_replies', array(&$this, 'SetupBBPressTopicActions'));
				add_action('bbp_template_after_user_profile', array(&$this, 'AddBBPressUserProfileRating'));
			}

			function SetupBBPressTopicActions($has_replies)
			{
				RWLogger::LogEnterence("SetupBBPressActions");

				$align = $this->GetRatingAlignByType(WP_RW__FORUM_POSTS_ALIGN);

				// If set to hidden, break.
				if (false !== $align && !$this->IsHiddenRatingByType('forum-post'))
				{
					$this->forum_post_align = $align;

					if ('bottom' === $align->ver)
					{
						// If verticaly bottom aligned.
						add_filter('bbp_get_reply_content', array(&$this, 'AddBBPressBottomRating'));
					}
					else
					{
						// If vertically top aligned.
						if ('center' === $align->hor)
							// If horizontal align is center.
							add_action('bbp_theme_after_reply_admin_links', array(&$this, 'AddBBPressTopCenterRating'));
						else
							// If horizontal align is left or right.
							add_action('bbp_theme_before_reply_admin_links', array(&$this, 'AddBBPressTopLeftOrRightRating'));
						//        add_filter('bbp_get_topic_content', array(&$this, 'bbp_get_reply_content'));
					}
				}

				if (false !== $this->GetRatingAlignByType(WP_RW__USERS_ALIGN) && !$this->IsHiddenRatingByType('user'))
					// Add user ratings into forum threads.
					add_filter('bbp_get_reply_author_link', array(&$this, 'AddBBPressForumThreadUserRating'), 10, 2);

				return $has_replies;
			}

			function AddBBPressUserProfileRating()
			{
				if ($this->IsHiddenRatingByType('user'))
					return;

				echo $this->EmbedRatingIfVisibleByUser(bbpress()->displayed_user, 'user');
			}

			function AddBBPressForumThreadUserRating($author_link, $args) {
				RWLogger::LogEnterence( 'AddBBPressForumThreadUserRating' );

				$defaults = array(
					'post_id'    => 0,
					'link_title' => '',
					'type'       => 'both',
					'size'       => 80,
					'sep'        => '&nbsp;'
				);
				$r        = wp_parse_args( $args, $defaults );
				extract( $r );

				$reply_id = bbp_get_reply_id( $post_id );

				RWLogger::Log( 'AddBBPressForumThreadUserRating', 'post_id = ' . $post_id );
				RWLogger::Log( 'AddBBPressForumThreadUserRating', 'reply_id = ' . $reply_id );

				if ( bbp_is_reply_anonymous( $reply_id ) ) {
					return $author_link;
				}

				$options = array( 'show-info' => 'false' );
				// If accumulated user rating, then make sure it can not be directly rated.
				if ( $this->IsUserAccumulatedRating() ) {
					$options['read-only']   = 'true';
					$options['show-report'] = 'false';
				}

				$author_id = bbp_get_reply_author_id( $reply_id );

				return $author_link . $this->EmbedRatingIfVisible(
					$author_id,
					$author_id,
					bbp_get_reply_author_display_name( $reply_id ),
					bbp_get_reply_author_url( $reply_id ),
					'user',
					false,
					false,
					false,
					$options
				);
			}

			/**
			 * Add bbPress bottom ratings.
			 * Invoked on bbp_get_reply_content
			 *
			 * @param string $content
			 * @param int    $reply_id
			 *
			 * @return string
			 */
			function AddBBPressBottomRating($content, $reply_id = 0)
			{
				if (RWLogger::IsOn()){ $params = func_get_args(); RWLogger::LogEnterence('AddBBPressBottomRating', $params); }

				$forum_item = bbp_get_reply(bbp_get_reply_id());

				$is_reply = is_object($forum_item);

				if (!$is_reply)
					$forum_item = bbp_get_topic(bbp_get_topic_id());

				$class = ($is_reply ? 'forum-reply' : 'forum-post');

				if (RWLogger::IsOn())
					RWLogger::Log('AddBBPressBottomRating', $class . ': ' . var_export($forum_item, true));

				$ratingHtml = $this->EmbedRatingIfVisibleByPost($forum_item, $class, false, $this->forum_post_align->hor);

				return $content . $ratingHtml;
			}

			/**
			 * Add bbPress top center rating - just before metadata.
			 * Invoked on bbp_theme_after_reply_admin_links
			 */
			function AddBBPressTopCenterRating()
			{
				if (RWLogger::IsOn()){ $params = func_get_args(); RWLogger::LogEnterence('AddBBPressTopCenterRating', $params); }

				$forum_item = bbp_get_reply(bbp_get_reply_id());

				$is_reply = is_object($forum_item);

				if (!$is_reply)
					$forum_item = bbp_get_topic(bbp_get_topic_id());

				$class = ($is_reply ? 'forum-reply' : 'forum-post');

				if (RWLogger::IsOn())
					RWLogger::Log('AddBBPressTopCenterRating', $class . ': ' . var_export($forum_item, true));

				$ratingHtml = $this->EmbedRatingIfVisibleByPost($forum_item, $class, false, 'fright', 'display: inline; margin-right: 10px;');

				echo $ratingHtml;
			}

			/**
			 * Add bbPress top left & right ratings.
			 * Invoked on bbp_theme_before_reply_admin_links.
			 */
			function AddBBPressTopLeftOrRightRating()
			{
				if (RWLogger::IsOn()){ $params = func_get_args(); RWLogger::LogEnterence('AddBBPressTopLeftOrRightRating', $params); }

				$forum_item = bbp_get_reply(bbp_get_reply_id());

				$is_reply = is_object($forum_item);

				if (!$is_reply)
					$forum_item = bbp_get_topic(bbp_get_topic_id());

				$class = ($is_reply ? 'forum-reply' : 'forum-post');

				if (RWLogger::IsOn())
					RWLogger::Log('AddBBPressTopLeftOrRightRating', $class . ': ' . var_export($forum_item, true));

				$ratingHtml = $this->EmbedRatingIfVisibleByPost($forum_item, $class, false, 'f' . $this->forum_post_align->hor, 'display: inline; margin-' . ('left' === $this->forum_post_align->hor ? 'right' : 'left') . ': 10px;');

				echo $ratingHtml;
			}

			var $forum_align = array();
			function rw_before_forum_loop($has_posts)
			{
				if (RWLogger::IsOn()){ $params = func_get_args(); RWLogger::LogEnterence("rw_before_forum_loop", $params); }

				if (!$has_posts){ return false; }

				$items = array(
					/*"forum-topic" => array(
                "align_key" => WP_RW__FORUM_TOPICS_ALIGN,
                "enabled" => false,
            ),*/
					"forum-post" => array(
						"align_key" => WP_RW__FORUM_POSTS_ALIGN,
						"enabled" => false,
					),
				);

				$hook = false;
				foreach ($items as $key => &$item)
				{
					$align = $this->GetRatingAlignByType($item["align_key"]);
					$item["enabled"] = (false !== $align);

					if (!$item["enabled"] || $this->IsHiddenRatingByType($key))
						continue;

					$this->forum_align[$key] = $align;
					$hook = true;
				}

				if ($hook)
					// Hook forum posts.
					add_filter("bp_get_the_topic_post_content", array(&$this, "rw_display_forum_post_rating"));

				return true;
			}

			/**
			 * Add bbPress forum post ratings. This method is for old versions of bbPress & BuddyPress bundle.
			 *
			 * @param mixed $content
			 *
			 * @return string
			 */
			function rw_display_forum_post_rating($content)
			{
				if (RWLogger::IsOn()){ $params = func_get_args(); RWLogger::LogEnterence("rw_display_forum_post_rating", $params); }

				$rclass = "forum-post";

				// Check if item rating is top positioned.
				if (!isset($this->forum_align[$rclass]))
					return $content;

				$post_id = bp_get_the_topic_post_id();

				/*
        // Validate that item isn't explicitly excluded.
        if (false === $this->rw_validate_visibility($post_id, $rclass))
            return $content;

        // Get forum-post user-rating-id.
        $post_urid = $this->_getForumPostRatingGuid($post_id);

        // Queue activity-comment rating.
        $this->QueueRatingData($post_urid, strip_tags($topic_template->post->post_text), bp_get_the_topic_permalink() . "#post-" . $post_id, $rclass);

        $rw = '<div class="rw-' . $this->forum_align[$rclass]->hor . '"><div class="rw-ui-container rw-class-' . $rclass . ' rw-urid-' . $post_urid . '"></div></div>';
        */

				global $topic_template;

				// Add accumulator id if user accumulated rating.
				if ($this->IsUserAccumulatedRating())
					$options['uarid'] = $this->_getUserRatingGuid($topic_template->post->poster_id);

				$rw = $this->EmbedRatingIfVisible(
					$post_id,
					$topic_template->post->poster_id,
					strip_tags(bp_get_the_topic_post_content()),
					bp_get_the_topic_permalink() . "#post-" . $post_id,
					$rclass,
					false,
					$this->forum_align[$rclass]->hor,
					false,
					$options,
					false);


				// Attach rating html container.
				return ($this->forum_align[$rclass]->ver == "top") ?
					$rw . $content :
					$content . $rw;
			}

			#endregion BuddyPress && bbPress ------------------------------------------------------------------

			/* Final Rating-Widget JS attach (before </body>)
    ---------------------------------------------------------------------------------------------------------------*/

			/**
			 * Generates the main JavaScript which renders all the ratings on the page.
			 *
			 * @param bool $pElement
			 */
			function rw_attach_rating_js($pElement = false)
			{
				if (RWLogger::IsOn()){ $params = func_get_args(); RWLogger::LogEnterence("rw_attach_rating_js", $params); }

				$rw_settings = array(
					"blog-post" => array("options" => WP_RW__BLOG_POSTS_OPTIONS),
					"front-post" => array("options" => WP_RW__FRONT_POSTS_OPTIONS),
					"comment" => array("options" => WP_RW__COMMENTS_OPTIONS),
					"page" => array("options" => WP_RW__PAGES_OPTIONS),

					"activity-update" => array("options" => WP_RW__ACTIVITY_UPDATES_OPTIONS),
					"activity-comment" => array("options" => WP_RW__ACTIVITY_COMMENTS_OPTIONS),
//            "new-forum-topic" => array("options" => WP_RW__ACTIVITY_FORUM_TOPICS_OPTIONS),
					"new-forum-post" => array("options" => WP_RW__ACTIVITY_FORUM_POSTS_OPTIONS),
					"new-blog-post" => array("options" => WP_RW__ACTIVITY_BLOG_POSTS_OPTIONS),
					"new-blog-comment" => array("options" => WP_RW__ACTIVITY_BLOG_COMMENTS_OPTIONS),

//            "forum-topic" => array("options" => WP_RW__ACTIVITY_FORUM_TOPICS_OPTIONS),
					"forum-post" => array("options" => WP_RW__ACTIVITY_FORUM_POSTS_OPTIONS),
					"forum-reply" => array("options" => WP_RW__ACTIVITY_FORUM_POSTS_OPTIONS),

					"user" => array("options" => WP_RW__USERS_OPTIONS),
					"user-post" => array("options" => WP_RW__USERS_POSTS_OPTIONS),
					"user-page" => array("options" => WP_RW__USERS_PAGES_OPTIONS),
					"user-comment" => array("options" => WP_RW__USERS_COMMENTS_OPTIONS),
					"user-activity-update" => array("options" => WP_RW__USERS_ACTIVITY_UPDATES_OPTIONS),
					"user-activity-comment" => array("options" => WP_RW__USERS_ACTIVITY_COMMENTS_OPTIONS),
					"user-forum-post" => array("options" => WP_RW__USERS_FORUM_POSTS_OPTIONS),
				);

				foreach ($this->_extensions as $ext) {
					$ext_settings = $ext->GetSettings();
					foreach ( $ext_settings as $type => $options ) {
						$rw_settings[ $options['class'] ] = array( 'options' => $options['options'] );
					}
				}

				$attach_js = false;

				$criteria_suffix_part = '-criteria';

				if (is_array(self::$ratings) && count(self::$ratings) > 0)
				{
					foreach (self::$ratings as $urid => $data)
					{
						$rclass = $data["rclass"];

						if ( RWLogger::IsOn() )
							RWLogger::Log( 'rw_attach_rating_js', 'Urid = ' . $urid . '; Class = ' . $rclass . ';' );

						$suffix_pos = strpos($rclass, $criteria_suffix_part);
						if (false !== $suffix_pos) {
							/* Use dummy value for the criteria options but
							 * use the settings of the summary rating when
							 * calling RW.initClass below
							 */
							$rw_settings[$rclass] = 'DUMMY';

							/*
							 * Make sure that the following code (the if block) will have the main option class, e.g. blog-post,
							 * and not the criterion class, e.g. blog-post-criteria-1. This is because the following
							 * code needs the main option class in order to load the type (blog, page, etc.) settings which include
							 * the themes and other display options. A criterion rating will use these display options,
							 * so this code extracts the main option class from the criterion class.
							 */
							$rclass = substr($rclass, 0, $suffix_pos);
						}

						if (isset($rw_settings[$rclass]) && is_array($rw_settings[$rclass]) && !isset($rw_settings[$rclass]["enabled"]))
						{
							if ( RWLogger::IsOn() )
								RWLogger::Log( 'rw_attach_rating_js', 'Class = ' . $rclass . ';' );

							// Forum reply should have exact same settings as forum post.
							$alias = ('forum-reply' === $rclass) ? 'forum-post' : $rclass;

							$rw_settings[$rclass]["enabled"] = true;

							// Get rating front posts settings.
							$rw_settings[$rclass]["options"] = $this->GetOption($rw_settings[$rclass]["options"]);

							/*
							 * We don't want to display the number of votes when the comment rating mode is "Review" or "Admin-only ratings".
							 * So we're modifying the rating label so that it will be based on the vote. e.g.: 5-star vote = "Excellent".
							 */
							if ( 'comment' === $rclass && ( $this->is_comment_review_mode() || $this->is_comment_admin_ratings_mode() ) ) {
								$options = $rw_settings[$rclass]["options"];

								if ( !isset($options->label) ) {
									$options->label = new stdClass();
								}

								if ( !isset($options->label->text) ) {
									$options->label->text = new stdClass();
								}

								if ( !isset($options->label->text->star) ) {
									$options->label->text->star = new stdClass();
								}

								if ( !isset($options->label->text->nero) ) {
									$options->label->text->nero = new stdClass();
								}

								/**
								 * The following will show the same label when the rating is not empty whether the viewer has already voted or has not voted yet.
								 *
								 * e.g.: Instead of showing "Rate this (2 Votes)" or "5 Votes", the label will be "Excellent", "Good", or "Awful", depending on the label settings.
								 */
								$options->label->text->star->normal  = '{{rating.lastVote}}';
								$options->label->text->star->rated = '{{rating.lastVote}}';

								$options->label->text->nero->rated = '{{rating.lastVote}}';
								$options->label->text->nero->normal = '{{text.rateThis}}';

								$options->showToolip = false;
								$options->showReport = false;
							}

							if (WP_RW__AVAILABILITY_DISABLED === $this->rw_validate_availability($alias))
							{
								// Disable ratings (set them to be readOnly).
								$rw_settings[$rclass]["options"]->readOnly = true;
							}

							$attach_js = true;
						}
					}
				}

				$is_bp_activity_component = function_exists('bp_is_activity_component') && bp_is_activity_component();

				if (!$attach_js) {
					// Necessary for rendering newly inserted activity ratings
					// when the are no status updates or comments yet
					if ($is_bp_activity_component) {
						$bp_rclasses = array('activity-update', 'activity-comment');

						foreach ($bp_rclasses as $rclass) {
							if (isset($rw_settings[$rclass]) && !isset($rw_settings[$rclass]["enabled"])) {
								if ( RWLogger::IsOn() )
									RWLogger::Log( 'rw_attach_rating_js', 'Class = ' . $rclass . ';' );

								$rw_settings[$rclass]["enabled"] = true;

								// Get rating class settings.
								$rw_settings[$rclass]["options"] = $this->GetOption($rw_settings[$rclass]["options"]);

								if (WP_RW__AVAILABILITY_DISABLED === $this->rw_validate_availability($rclass))
								{
									// Disable ratings (set them to be readOnly).
									$rw_settings[$rclass]["options"]->readOnly = true;
								}

								$attach_js = true;
							}
						}
					}
				}

				if ($attach_js || $this->_TOP_RATED_WIDGET_LOADED)
				{
					?>
					<!-- This site's ratings are powered by RatingWidget plugin v<?php echo WP_RW__VERSION ?> - https://rating-widget.com/wordpress-plugin/ -->
					<div class="rw-js-container">
						<?php
						rw_wf()->print_site_script();
						?>
						<script type="text/javascript">

							// Initialize ratings.
							function RW_Async_Init(){
								RW.init({<?php
                        // User key (uid).
                        echo 'uid: "' . $this->account->site_public_key . '"';

                        // User id (huid).
                        if ($this->account->has_site_id())
                            echo ', huid: "' . $this->account->site_id . '"';

                            global $pagenow;
                            
                            $vid = 0;
                            
                            // Only set the vid to 1 if the comment ratings mode is set to "Admin ratings only".
                            if ( 'comment.php' === $pagenow && $this->is_comment_admin_ratings_mode() ) {
                                $vid = 1;
                            } else {
                                // User logged-in.
                                $user = wp_get_current_user();
                                $vid = $user->ID;
                            }
                            
                            if ( $vid !== 0) {
                                // Set voter id to logged user id.
                                echo ", vid: {$vid}";
                            }
                    ?>,
									source: "wordpress",
									options: {
									<?php if ($this->fs->is_plan_or_trial__premium_only('professional')) :
													if (defined('ICL_LANGUAGE_CODE') &&
													isset($this->languages[ICL_LANGUAGE_CODE])) : ?>
									lng: "<?php echo ICL_LANGUAGE_CODE; ?>"
									<?php endif ?>
									<?php endif ?>
								},
								identifyBy: "<?php echo $this->GetOption(WP_RW__IDENTIFY_BY) ?>"
							});
							<?php
							foreach ($rw_settings as $rclass => $options)
							{
								$criteria_class = $rclass;

								$suffix_pos = strpos($rclass, $criteria_suffix_part);
								if (false !== $suffix_pos) {
									$rclass = substr($rclass, 0, $suffix_pos);
								}

								if (isset($rw_settings[$rclass]["enabled"]) && (true === $rw_settings[$rclass]["enabled"])) {
									$alias = ('forum-reply' === $rclass) ? 'forum-post' : $rclass;
									?>
							var options = <?php echo !empty($rw_settings[$alias]["options"]) ? json_encode($rw_settings[$rclass]["options"]) : '{}'; ?>;
							<?php echo $this->GetCustomSettings($alias); ?>
							if ( WF_Engine ) {
								var _beforeRate = options.beforeRate ? options.beforeRate : false;
								options.beforeRate = function(rating, score) {
									var returnValue = true;
									if (false !== _beforeRate) {
										returnValue = _beforeRate(rating, score);
									}
									
									return WF_Engine.eval( 'beforeVote', rating, score, returnValue );
								};
								
								var _afterRate = options.afterRate ? options.afterRate : false;
								options.afterRate = function(success, score, rating) {
									if (false !== _afterRate) {
										_afterRate(success, score, rating);
									}
									
									WF_Engine.eval( 'afterVote', rating, score );
									
									return true;
								};
							}
							
							RW.initClass("<?php echo $criteria_class; ?>", options);
							<?php
						}
					}

					foreach (self::$ratings as $urid => $data)
					{
						if ((is_string($data["title"]) && !empty($data["title"])) ||
						(is_string($data["permalink"]) && !empty($data["permalink"])) ||
						isset($data["img"]))
						{
							$properties = array();
							if (is_string($data["title"]) && !empty($data["title"]))
								$properties[] = 'title: ' . json_encode(esc_js($data["title"]));
							if (is_string($data["permalink"]) && !empty($data["permalink"]))
								$properties[] = 'url: ' . json_encode(esc_js($data["permalink"]));
							if (isset($data["img"]))
								$properties[] = 'img: ' . json_encode(esc_js($data["img"]));


							echo 'RW.initRating("' . $urid . '", {' . implode(', ', $properties) .'});';
						}
					}
				?>
							RW.render(function() {
								(function($) {
									$('.rw-rating-table:not(.rw-no-labels):not(.rw-comment-admin-rating)').each(function() {
										var ratingTable = $(this);

										// Find the current width before floating left or right to
										// keep the ratings aligned
										var col1 = ratingTable.find('td:first');
										var widthCol1 = col1.width();
										ratingTable.find('td:first-child').width(widthCol1);

										if (ratingTable.hasClass('rw-rtl')) {
											ratingTable.find('td').css({float: 'right'});
										} else {
											ratingTable.find('td').css({float: 'left'});
										}
									});
								})(jQuery);
							}, <?php
                        echo (!$this->_TOP_RATED_WIDGET_LOADED) ? "true" : "false";
                    ?>);
							}

							RW_Advanced_Options = {
								blockFlash: !(<?php
                        $flash = $this->GetOption(WP_RW__FLASH_DEPENDENCY, true);
                        echo in_array($flash, array('true', 'false')) ? $flash : ((false === $flash) ? 'false' : 'true');
                    ?>)
							};

							// Append RW JS lib.
							if (typeof(RW) == "undefined"){
								(function(){
									var rw = document.createElement("script");
									rw.type = "text/javascript"; rw.async = true;
									rw.src = "<?php echo rw_get_js_url('external' . (!WP_RW__DEBUG ? '.min' : '') . '.php');?>?wp=<?php echo WP_RW__VERSION;?>";
									var s = document.getElementsByTagName("script")[0]; s.parentNode.insertBefore(rw, s);
								})();
							}
						</script>
					</div>
					<!-- / RatingWidget plugin -->
					<?php
					// Enqueue the script that will handle the rendering
					// of the rating of the newly inserted BuddyPress status update
					// or comment
					if ($is_bp_activity_component) {
						rw_enqueue_script('rw-site-ajax-handler', WP_RW__PLUGIN_URL . 'resources/js/site-ajax-handler.js');
					}
				}
			}

			/**
			 * Modifies post for Rich Snippets Compliance.
			 *
			 */
			function rw_add_title_metadata($title, $id = '')
			{
				return '<mark itemprop="name" style="background: none; color: inherit;">' . $title . '</mark>';
			}

			function rw_add_article_metadata($classes, $class = '', $post_id = '')
			{
				$classes[] = '"';
				$classes[] = 'itemscope';
				$classes[] = 'itemtype="http://schema.org/Product';
				return $classes;
			}

			/* wp_footer() execution validation
 * Inspired by http://paste.sivel.net/24
 --------------------------------------------------------------------------------------------------------------*/
			function test_footer_init()
			{
				// Hook in at admin_init to perform the check for wp_head and wp_footer
				add_action('admin_init', array(&$this, 'check_head_footer'));

				// If test-footer query var exists hook into wp_footer
				if (isset( $_GET['test-footer']))
					add_action('wp_footer', array(&$this, 'test_footer'), 99999); // Some obscene priority, make sure we run last
			}

			// Echo a string that we can search for later into the footer of the document
			// This should end up appearing directly before </body>
			function test_footer()
			{
				echo '<!--wp_footer-->';
			}

			// Check for the existence of the strings where wp_head and wp_footer should have been called from
			function check_head_footer()
			{
				// NOTE: uses home_url and thus requires WordPress 3.0
				if (!function_exists('home_url'))
					return;

				// Build the url to call,
				$url = add_query_arg(array('test-footer' => ''), home_url());

				// Perform the HTTP GET ignoring SSL errors
				$response = wp_remote_get($url, array('sslverify' => false));

				// Grab the response code and make sure the request was sucessful
				$code = (int)wp_remote_retrieve_response_code($response);

				if ($code == 200)
				{
					// Strip all tabs, line feeds, carriage returns and spaces
					$html = preg_replace('/[\t\r\n\s]/', '', wp_remote_retrieve_body($response));

					// Check to see if we found the existence of wp_footer
					if (!strstr($html, '<!--wp_footer-->'))
					{
						add_action('admin_notices', array(&$this, 'test_head_footer_notices'));
					}
				}
			}

			// Output the notices
			function test_head_footer_notices()
			{
				// If we made it here it is because there were errors, lets loop through and state them all
				echo '<div class="updated highlight"><p><strong>' .
				     esc_html('If the Rating-Widget\'s ratings don\'t show up on your blog it\'s probably because your active theme is missing the call to <?php wp_footer(); ?> which should appear directly before </body>.').
				     '</strong> '.
				     'For more details check out our <a href="' . WP_RW__ADDRESS . '/faq/" target="_blank">FAQ</a>.</p></div>';
			}

			#region Post/Page Metabox ------------------------------------------------------------------

			function get_all_post_types()
			{
				$post_types = array('post', 'page');
				$custom_post_types = get_post_types(array(
					'public'   => true,
					'_builtin' => false
				));

				if (is_array($custom_post_types) && 0 < count($custom_post_types))
					$post_types = array_merge($post_types, $custom_post_types);

				return $post_types;
			}

			function AddPostMetaBox()
			{
				// Make sure only admin can exclude ratings.
				if (!(bool)current_user_can('manage_options'))
					return;

				$post_types = $this->get_all_post_types();

				// Add the meta box.
				foreach ($post_types as $t)
					add_meta_box('rw-post-meta-box', WP_RW__NAME, array(&$this, 'ShowPostMetaBox'), $t, 'side', 'high');
			}

			// Callback function to show fields in meta box.
			function ShowPostMetaBox()
			{
				rw_require_view('pages/admin/post-metabox.php');
			}

			// Save data from meta box.
			function SavePostData($post_id)
			{
				if (RWLogger::IsOn()){ $params = func_get_args(); RWLogger::LogEnterence("SavePostData", $params, true); }

				// Verify nonce.
				if (!isset($_POST['rw_post_meta_box_nonce']) || !wp_verify_nonce($_POST['rw_post_meta_box_nonce'], basename(WP_RW__PLUGIN_FILE_FULL)))
					return $post_id;

				// Check auto-save.
				if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
					return $post_id;

				if (RWLogger::IsOn()){ RWLogger::Log("post_type", $_POST['post_type']); }

				// Check permissions.
				if ('page' == $_POST['post_type'])
				{
					if (!current_user_can('edit_page', $post_id))
						return $post_id;
				}
				else if (!current_user_can('edit_post', $post_id))
				{
					return $post_id;
				}

				//check whether this post/page is to be excluded
				$includePost = (isset($_POST['rw_include_post']) && "1" == $_POST['rw_include_post']);

				// Checks whether this post/page has read-only rating.
				$readonly_post = (!isset($_POST['rw_readonly_post']) || '1' !== $_POST['rw_readonly_post']);

				switch ($_POST['post_type']) {
					case 'page':
						$classes = array('page');
						break;
					case 'product':
						$classes = array('collection-product', 'product');
						break;
					case 'topic':
					case 'reply':
						$classes = array('forum-post');
						break;
					case 'post':
					default:
						$classes = array('front-post', 'blog-post');
						break;
				}

				$this->add_to_visibility_list(
					$_POST['ID'],
					$classes,
					$includePost);

				$this->SetOption(WP_RW__VISIBILITY_SETTINGS, $this->_visibilityList);

				// Only proceed if the post type is supported.
				if (in_array($_POST['post_type'], array('post', 'page', 'product', 'topic', 'reply'))) {
					// Add/remove to/from the read-only list of post IDs based on the state of the read-only checkbox.
					$this->add_to_readonly(
						$_POST['ID'],
						array($_POST['post_type']),
						$readonly_post);

					$this->SetOption(WP_RW__READONLY_SETTINGS, $this->_readonly_list);
				}

				$this->_options->store();

				if (RWLogger::IsOn()) {
					RWLogger::LogDeparture("SavePostData");
				}

				return $post_id;
			}

			function DeletePostData($post_id) {
				RWLogger::LogEnterence('DeletePostData');

				if ( ! current_user_can( 'delete_posts' ) ) {
					return;
				}

				$rating_id = $this->_getPostRatingGuid( $post_id );

				rwapi()->call( '/ratings/' . $rating_id . '.json?is_external=true', 'DELETE' );
			}

			#endregion Post/Page Metabox ------------------------------------------------------------------

			/**
			 * Registers the dashboard widgets
			 *
			 * @author Leo Fajardo (@leorw)
			 */
			function add_dashboard_widgets() {
				// Initialize statistics
				$stats = array(
					'ratings' => 0,
					'votes' => 0
				);

				// Retrieve ratings and votes count
				$response = rwapi()->get("/votes/count.json", false, WP_RW__CACHE_TIMEOUT_DASHBOARD_STATS);
				if (!isset($response->error)) {
					$stats['votes'] = $response->count;

					$response = rwapi()->get("/ratings/count.json", false, WP_RW__CACHE_TIMEOUT_DASHBOARD_STATS);
					if (!isset($response->error)) {
						$stats['ratings'] = $response->count;
					}
				}

				// Add the widget if there is at least 1 vote
				if ($stats['votes'] >= 1) {
					wp_add_dashboard_widget(
						'rw-stats-dashboard-widget',			// Widget slug
						__(' ', WP_RW__ID),						// Title
						array(&$this, 'stats_widget_callback'),	// Display callback function
						null,
						$stats									// Arguments to pass to the callback function
					);
				}
			}

			/**
			 * The stats dashboard widget callback function that handles the displaying of the widget content
			 *
			 * @author Leo Fajardo (@leorw)
			 * @param mixed $object object passed to the callback function
			 * @param object $callback_args the dashboard widget details, including the arguments passed
			 */
			function stats_widget_callback($object, $callback_args) {
				rw_require_view('pages/admin/dashboard-stats.php', $callback_args['args']);
			}

			function PurgePostFeaturedImageTransient($meta_id = 0, $post_id = 0, $meta_key = '', $_meta_value = '')
			{
				if ('_thumbnail_id' === $meta_key)
					delete_transient('post_thumb_' . $post_id);
			}

			function DumpLog($pElement = false)
			{
				if (RWLogger::IsOn())
				{
//					echo "\n<!-- RATING-WIDGET LOG START\n\n";
//					RWLogger::Output("    ");
//					echo "\n RATING-WIDGET LOG END-->\n";

					fs_dump_log();
				}
			}

			function GetPostExcerpt($pPost, $pWords = 15)
			{
				if (!empty($pPost->post_excerpt))
					return wp_trim_words(trim(strip_tags($pPost->post_excerpt)), $pWords);

				$strippedContent = trim(strip_tags(strip_shortcodes($pPost->post_content)));
				$excerpt = implode(' ', array_slice(explode(' ', $strippedContent), 0, $pWords));

				return (mb_strlen($strippedContent) !== mb_strlen($excerpt)) ?
					$excerpt . "..." :
					$strippedContent;
			}

			function GetPostFeaturedImage($pPostID)
			{
				if (!has_post_thumbnail($pPostID))
					return '';

				$image = wp_get_attachment_image_src(get_post_thumbnail_id($pPostID), 'single-post-thumbnail');
				return $image[0];
			}

			/**
			 * Retrieves the user's avatar URL
			 *
			 * @author Leo Fajardo (@leorw)
			 * @since 2.4.1
			 * @param int $user_id
			 * @return string
			 */
			function get_user_avatar($user_id) {
				$avatar_url = '';

				$avatar = get_avatar($user_id);
				if ($avatar) {
					// Extract the avatar URL from the <img> tag
					preg_match('/src=[\'"](.*?)[\'"]/i', $avatar, $matches);
					if ($matches) {
						$avatar_url = $matches[1];
					}
				}

				return $avatar_url;
			}

			function GetTopRatedData($pTypes = array(), $pLimit = 5, $pOffset = 0, $pMinVotes = 1, $pInclude = false, $pShowOrder = false, $pOrderBy = 'avgrate', $pOrder = 'DESC', $since_created = -1)
			{
				if (RWLogger::IsOn()){ $params = func_get_args(); RWLogger::LogEnterence("GetTopRatedData", $params); }

				if (!is_array($pTypes) || count($pTypes) == 0)
					return false;

				$types = $this->get_rating_types();

				$typesKeys = array_keys($types);

				$availableTypes = array_intersect($typesKeys, $pTypes);

				if (!is_array($availableTypes) || count($availableTypes) == 0)
					return false;

				$details = array(
					"uid" => $this->account->site_public_key,
				);

				$queries = array();

				foreach ($availableTypes as $type)
				{
					$options = ratingwidget()->GetOption($types[$type]["options"]);

					$queries[$type] = array(
						"rclasses" => $types[$type]["classes"],
						"votes" => $pMinVotes,
						"orderby" => $pOrderBy,
						"order" => $pOrder,
						"show_order" => ($pShowOrder ? "true" : "false"),
						"offset" => $pOffset,
						"limit" => $pLimit,
						"types" => isset($options->type) ? $options->type : "star",
					);

					if ( $since_created >= WP_RW__TIME_24_HOURS_IN_SEC ) {
						$time = current_time( 'timestamp', true ) - $since_created;

						// c: ISO 8601 full date/time, e.g.: 2004-02-12T15:19:21+00:00
						$queries[$type]['since_created'] = date( 'c', $time );
					}

					if (is_array($pInclude) && count($pInclude) > 0)
						$queries[$type]['urids'] = implode(',', $pInclude);
				}

				$details["queries"] = urlencode(json_encode($queries));

				$rw_ret_obj = ratingwidget()->RemoteCall("action/query/ratings.php", $details, WP_RW__CACHE_TIMEOUT_TOP_RATED);

				if (false === $rw_ret_obj)
					return false;

				$rw_ret_obj = json_decode($rw_ret_obj);

				if (null === $rw_ret_obj || true !== $rw_ret_obj->success)
					return false;

				return $rw_ret_obj;
			}

			/**
			 * Creates an array of rating post type settings
			 *
			 * @author Leo Fajardo (leorw)
			 * @since 2.4.1
			 * @return array
			 */
			function get_rating_types() {
				$types = array(
					"pages" => array(
						"rclass" => "page",
						"classes" => "page,user-page",
						"options" => WP_RW__PAGES_OPTIONS,
					),
					"posts" => array(
						"rclass" => "blog-post",
						"classes" => "front-post,blog-post,new-blog-post,user-post",
						"options" => WP_RW__BLOG_POSTS_OPTIONS,
					)
				);

				if (function_exists('is_bbpress')) {
					$types["forum_posts"] = array(
						"rclass" => "forum-post",
						"classes" => "forum-post,new-forum-post,user-forum-post",
						"options" => WP_RW__FORUM_POSTS_OPTIONS,
					);
				}

				if (function_exists('is_bbpress') || function_exists('is_buddypress')) {
					$types["users"] = array(
						"rclass" => "user",
						"classes" => "user",
						"options" => WP_RW__USERS_OPTIONS,
					);
				}

				$extensions = $this->GetExtensions();

				foreach ( $extensions as $ext ) {
					$types = array_merge( $types, $ext->GetTopRatedInfo() );
				}

				return $types;
			}

			/**
			 * Retrieves the generated top-rated HTML string
			 *
			 * @author Leo Fajardo (@leorw)
			 * @since 2.4.1
			 * @param array $shortcode_atts
			 * @return string
			 */
			function get_toprated_from_shortcode($shortcode_atts) {
				ob_start();
				rw_require_view('site/top-rated.php', $shortcode_atts);
				$html = ob_get_contents();
				ob_end_clean();

				return $html;
			}

			function get_rating_id_by_element($element_id, $element_type, $criteria_id = false)
			{
				$urid = false;

				switch ($element_type)
				{
					case 'blog-post':
					case 'front-post':
					case 'page':
					case 'user-page':
					case 'new-blog-post':
					case 'user-post':
						$urid = $this->_getPostRatingGuid($element_id, $criteria_id);
						break;
					case 'comment':
					case 'new-blog-comment':
					case 'user-comment':
						$urid = $this->_getCommentRatingGuid($element_id, $criteria_id);
						break;
					case 'forum-post':
					case 'forum-reply':
					case 'new-forum-post':
					case 'user-forum-post':
						$urid = $this->_getForumPostRatingGuid($element_id, $criteria_id);
						break;
					case 'user':
						$urid = $this->_getUserRatingGuid($element_id);
						break;
					case 'activity-update':
					case 'user-activity-update':
					case 'activity-comment':
					case 'user-activity-comment':
						$urid = $this->_getActivityRatingGuid($element_id);
						break;
				}

				if (false === $urid) {
					foreach ($this->_extensions as $ext) {
						if (in_array($element_type, $ext->GetRatingClasses())) {
							$urid = $ext->GetRatingGuid($element_id, $element_type, $criteria_id);
							break;
						}
					}
				}

				return $urid;
			}

			#region Embed Ratings ------------------------------------------------------------------

			/**
			 * Queue rating data for footer JS hook and return rating's html.
			 *
			 * @param int $pElementID
			 * @param $pOwnerID
			 * @param $pTitle
			 * @param $pPermalink
			 * @param $pElementClass
			 * @param bool $pAddSchema
			 * @param bool $pHorAlign
			 * @param bool $pCustomStyle
			 * @param array $pOptions
			 * @param bool $pValidateVisibility
			 * @param bool $pValidateCategory
			 *
			 * @return string Rating HTML container.
			 *
			 * @uses GetRatingHtml
			 * @version 1.3.3
			 */
			function EmbedRating(
				$pElementID,
				$pOwnerID,
				$pTitle,
				$pPermalink,
				$pElementClass,
				$pAddSchema = false,
				$pHorAlign = false,
				$pCustomStyle = false,
				$pOptions = array(),
				$pValidateVisibility = false,
				$pValidateCategory = true)
			{
				if (RWLogger::IsOn()){ $params = func_get_args(); RWLogger::LogEnterence("EmbedRating", $params); }

				$result = apply_filters('rw_filter_embed_rating', $pElementID, $pOwnerID);

				if (false === $result)
					return '';

				if ($pValidateVisibility && !$this->IsVisibleRating($pElementID, ('forum-reply' !== $pElementClass) ? $pElementClass : 'forum-post', $pValidateCategory))
					return '';

				$urid = $this->get_rating_id_by_element($pElementID, $pElementClass);

				if ( 'comment' === $pElementClass ) {
					// Get the read-only state of the comment rating
					$is_rating_readonly = $this->is_rating_readonly( $pElementID, 'comment' );
				} else {
					// Get the read-only state of the exact post type, e.g.: post or product
					$is_rating_readonly = $this->is_rating_readonly($pElementID, get_post_type($pElementID));
				}

				if (!$is_rating_readonly) {
					if (function_exists('is_buddypress') && is_buddypress()) {
						// Get the user ID associated with the current BuddyPress page being viewed.
						$buddypress_user_id = ('user' === $pElementClass) ? $pElementID : $pOwnerID;

						// Set the rating to read-only if the current logged in user ID
						// is equal to the current BuddyPress user ID.
						$is_rating_readonly = (get_current_user_id() == $buddypress_user_id);
					} else if (function_exists('is_bbpress') && is_bbpress()) {
						// Get the user ID associated with the current bbPress item being viewed.
						$bbpress_user_id = ('user' === $pElementClass) ? $pElementID : $pOwnerID;

						// Set the rating to read-only if the current logged in user ID
						// is equal to the current bbPress user ID.
						$is_rating_readonly = (get_current_user_id() == $bbpress_user_id);
					}
				}

				if ($is_rating_readonly) {
					$pOptions['read-only'] = 'true';
				}

				if ( ! $this->has_multirating_options( $pElementClass ) || ( ( $this->is_comment_review_mode() || $this->is_comment_admin_ratings_mode() ) && 'comment' === $pElementClass ) ) {
					RWLogger::Log('EmbedRating', 'Not multi-criteria rating');

					return $this->EmbedRawRating($urid, $pTitle, $pPermalink, $pElementClass, $pAddSchema, $pHorAlign, $pCustomStyle, $pOptions);
				} else {
					RWLogger::Log('EmbedRating', 'Multi-criteria rating');

					//Prefixed with mr_ to avoid possible collisions after calling extract()
					$vars = array(
						'mr_add_schema' => $pAddSchema,
						'mr_custom_style' => $pCustomStyle,
						'mr_element_class' => $pElementClass,
						'mr_element_id' => $pElementID,
						'mr_embed_options' => $pOptions,
						'mr_hor_align' => $pHorAlign,
						'mr_permalink' => $pPermalink,
						'mr_summary_urid' => $urid,
						'mr_title' => $pTitle
					);

					return $this->embed_multi_rating($vars);
				}
			}

			/**
			 * Loads the multi-rating view using the data passed to $vars
			 * @param array $vars
			 * @return string Returns the generated multi-rating HTML
			 */
			function embed_multi_rating($vars) {
				$multirating_options = $this->get_multirating_options_by_class($vars['mr_element_class']);
				$general_options = $this->get_options_by_class($vars['mr_element_class']);

				$vars['mr_general_options'] = $general_options;
				$vars['mr_multi_options'] = $multirating_options;

				// Retrieve the generated HTML, necessary for proper placement in the site, e.g.: bottom center
				ob_start();
				rw_require_view('site/multi-rating.php', $vars);
				$html = ob_get_contents();
				ob_end_clean();

				return $html;
			}

			function EmbedRawRating($urid, $title, $permalink, $class, $add_schema, $hor_align = false, $custom_style = false, $options = array())
			{
				$this->QueueRatingData($urid, $title, $permalink, $class);

				$html = $this->GetRatingHtml($urid, $class, $add_schema, $title, $permalink, $options);

				if (false !== ($hor_align || $custom_style))
					$html = '<div' .
					        (false !== $custom_style ? ' style="' . $custom_style . '"' : '') .
					        (false !== $hor_align ? ' class="rw-' . $hor_align . '"' : '') . '>'
					        . $html .
					        '</div>';

				return $html;
			}

			function EmbedRatingIfVisible($pElementID, $pOwnerID, $pTitle, $pPermalink, $pElementClass, $pAddSchema = false, $pHorAlign = false, $pCustomStyle = false, $pOptions = array(), $pValidateCategory = true) {
				if ( RWLogger::IsOn() ) {
					$params = func_get_args();
					RWLogger::LogEnterence( "EmbedRatingIfVisible", $params );
				}

				return $this->EmbedRating( $pElementID, $pOwnerID, $pTitle, $pPermalink, $pElementClass, $pAddSchema, $pHorAlign, $pCustomStyle, $pOptions, true, $pValidateCategory );
			}

			function EmbedRatingByPost($pPost, $pClass = 'blog-post', $pAddSchema = false, $pHorAlign = false, $pCustomStyle = false, $pOptions = array(), $pValidateVisibility = false) {
				$postImg = $this->GetPostImage( $pPost );
				if ( false !== $postImg ) {
					$pOptions['img'] = $postImg;
				}

				// Add accumulator id if user accumulated rating.
				if ( $this->IsUserAccumulatedRating() ) {
					$pOptions['uarid'] = $this->_getUserRatingGuid( $pPost->post_author );
				}

				return $this->EmbedRating(
					$pPost->ID,
					$pPost->post_author,
					$pPost->post_title,
					get_permalink( $pPost->ID ),
					$pClass,
					$pAddSchema,
					$pHorAlign,
					$pCustomStyle,
					$pOptions,
					$pValidateVisibility );
			}

			function EmbedRatingIfVisibleByPost($pPost, $pClass = 'blog-post', $pAddSchema = false, $pHorAlign = false, $pCustomStyle = false, $pOptions = array()) {
				if ( RWLogger::IsOn() ) {
					$params = func_get_args();
					RWLogger::LogEnterence( "EmbedRatingIfVisibleByPost", $params );
				}

				return $this->EmbedRatingByPost(
					$pPost,
					$pClass,
					$pAddSchema,
					$pHorAlign,
					$pCustomStyle,
					$pOptions,
					true
				);
			}

			function EmbedRatingByUser($pUser, $pClass = 'user', $pCustomStyle = false, $pOptions = array(), $pValidateVisibility = false) {
				if ( RWLogger::IsOn() ) {
					$params = func_get_args();
					RWLogger::LogEnterence( "EmbedRatingByUser", $params );
				}

				// If accumulated user rating, then make sure it can not be directly rated.
				if ( $this->IsUserAccumulatedRating() ) {
					$pOptions['read-only']   = 'true';
					$pOptions['force-sync']	 = 'true';
					$pOptions['show-report'] = 'false';
				}

				return $this->EmbedRating(
					$pUser->id,
					$pUser->id,
					$pUser->fullname,
					$pUser->domain,
					$pClass,
					false,
					false,
					$pCustomStyle,
					$pOptions,
					$pValidateVisibility,
					false );
			}

			function EmbedRatingIfVisibleByUser($pUser, $pClass = 'user', $pCustomStyle = false, $pOptions = array()) {
				if ( RWLogger::IsOn() ) {
					$params = func_get_args();
					RWLogger::LogEnterence( "EmbedRatingIfVisibleByUser", $params );
				}

				return $this->EmbedRatingByUser(
					$pUser,
					$pClass,
					$pCustomStyle,
					$pOptions,
					true );
			}

			function EmbedRatingByComment($pComment, $pClass = 'comment', $pHorAlign = false, $pCustomStyle = false, $pOptions = array()) {
				if ( RWLogger::IsOn() ) {
					$params = func_get_args();
					RWLogger::LogEnterence( 'EmbedRatingByComment', $params );
				}

				// Add accumulator id if user accumulated rating.
				if ( $this->IsUserAccumulatedRating() && (int) $pComment->user_id > 0 ) {
					$pOptions['uarid'] = $this->_getUserRatingGuid( $pComment->user_id );
				}

				$comment_ratings_mode = $this->get_comment_ratings_mode();
				if ( RWLogger::IsOn() ) {
					RWLogger::Log( 'comment_ratings_mode', $comment_ratings_mode );
				}

				/**
				 * If reviews mode, check if the previous submission of rating's value and vote has failed.
				 * If the submission has failed, submit again.
				 */
				if ( $this->is_comment_review_mode() ) {
					$comment_review_mode_settings = $this->get_comment_review_mode_settings();
					if ( RWLogger::IsOn() ) {
						RWLogger::Log( 'comment_review_mode_options', json_encode( $comment_review_mode_settings ) );
					}

					$failed_requests = $comment_review_mode_settings->failed_requests;

					if ( isset($failed_requests[$pComment->comment_ID]) ) {
						$request = $failed_requests[$pComment->comment_ID];
						$this->set_comment_review_vote($pComment->comment_ID, $request['request_params']);
					}
				}

				if ( $this->is_comment_review_mode() || $this->is_comment_admin_ratings_mode() ) {
					// Set the rating to read-only so that no other people can vote for the comment.
					$pOptions['read-only'] = 'true';
				}

				return $this->EmbedRating(
					$pComment->comment_ID,
					(int) $pComment->user_id,
					strip_tags( $pComment->comment_content ),
					get_permalink( $pComment->comment_post_ID ) . '#comment-' . $pComment->comment_ID,
					$pClass,
					false,
					$pHorAlign,
					$pCustomStyle,
					$pOptions );
			}

			#endregion Embed Ratings ------------------------------------------------------------------

			function IsUserAccumulatedRating()
			{
				if (!$this->IsBBPressInstalled())
					return false;

				return $this->GetOption(WP_RW__IS_ACCUMULATED_USER_RATING);
			}

			function GetRatingDataByRatingID($pRatingID, $pAccuracy = false)
			{
				if (RWLogger::IsOn()){ $params = func_get_args(); RWLogger::LogEnterence('GetRatingDataByRatingID', $params); }

				// API only supported in the Professional plan, so no reason to make calls that will return errors.
				if (!$this->fs->is_plan_or_trial('professional') || !$this->is_api_supported())
					return false;

				$rating = rwapi()->get(
					'/ratings/' . $pRatingID . '.json?is_external=true&fields=id,approved_count,avg_rate',
					false,
					WP_RW__CACHE_TIMEOUT_RICH_SNIPPETS
				);

				if (isset($rating->error))
					return false;

				$avg_rate = (float)$rating->avg_rate;
				$votes = (int)$rating->approved_count;
				$rate = $votes * $avg_rate;

				if (is_numeric($pAccuracy))
				{
					$pAccuracy = (int)$pAccuracy;
					$avg_rate = (float)sprintf("%.{$pAccuracy}f", $avg_rate);
					$rate = (float)sprintf("%.{$pAccuracy}f", $rate);
				}

				return array(
					'votes' => $votes,
					'totalRate' => $rate,
					'rate' => $avg_rate,
				);
			}

			function RegisterShortcodes()
			{
				add_shortcode('ratingwidget', 'rw_the_post_shortcode');
				add_shortcode('ratingwidget_raw', 'rw_the_rating_shortcode');

				if ($this->account->is_registered()) {
					add_shortcode('ratingwidget_toprated', 'rw_toprated_shortcode');
				}
			}

			/* Email Confirmation Handlers
    --------------------------------------------------------------------------------------------------------------------*/

			private function GetEmailConfirmationUrl()
			{
				$uri = rw_get_admin_url();// ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

				$timestamp = time();

				$params = array(
					'ts' => $timestamp,
					'key' => $this->account->site_public_key,
					'src' =>  $uri,
				);

				$confirmation = $timestamp . $this->account->site_public_key . $uri;

				$params['s'] = md5($confirmation);

				$query = http_build_query($params);

				return rw_get_site_url('/signup/wordpress/confirm/') . '?' . $query;
			}

			private function TryToConfirmEmail() {
				if ( ! rw_request_is_action( 'confirm', 'rw_action' ) ) {
					return false;
				}

				// Remove confirmation notice.
				remove_action( 'all_admin_notices', array( &$this, 'ConfirmationNotice' ) );

				$user_id   = rw_request_get( 'user_id' );
				$site_id   = rw_request_get( 'site_id' );
				$email     = rw_request_get( 'email' );
				$timestamp = rw_request_get( 'ts' );
				$secure    = rw_request_get( 's' );

				$is_valid = true;

				if ( $secure !== md5( strtolower(
						$user_id . $email . $timestamp . $this->account->site_public_key ) )
				) {
					$is_valid = false;
				}
				if ( ! is_numeric( $user_id ) ) {
					$is_valid = false;
				}
				if ( ! is_numeric( $timestamp ) ) {
					$is_valid = false;
				}
				if ( time() > ( $timestamp + 14 * 86400 ) ) {
					$is_valid = false;
				}

				if ( ! $is_valid ) {
					add_action( 'all_admin_notices', array( &$this, 'InvalidEmailConfirmNotice' ) );

					return true;
				}

				$this->SetOption( WP_RW__DB_OPTION_OWNER_ID, $user_id );
				$this->SetOption( WP_RW__DB_OPTION_OWNER_EMAIL, $email );
				$this->SetOption( WP_RW__DB_OPTION_SITE_ID, $site_id );

				$this->_options->store();

//				$this->fs->update_account($user_id, $email, $site_id);

				add_action( 'all_admin_notices', array( &$this, 'SuccessfulEmailConfirmNotice' ) );

				return true;
			}

			function fs_connect_message($message){
				$params = array();
				return rw_get_view('pages/admin/connect-message.php', $params);
			}

			function connect_account(FS_User $user, FS_Site $site)
			{
				// Generate secret signature.
				$context_params = FS_Security::instance()->get_context_params(
					$site,
					time(),
					'install'
				);
				$result = $this->RemoteCall('action/api/user/connect/', $context_params);

				$result = json_decode($result);

				if (!is_object($result) || !isset($result->success) || false === $result->success)
					return;

				$result = $result->data;

				$this->account->set(
					$result->site_id,
					$result->public_key,
					$result->secret_key,
					$result->user_id,
					$result->user_email
				);
			}

			function delete_account()
			{
				$this->account->clear();
			}

			function delete_account_and_settings()
			{
				$this->_options->delete();
			}
		}

		/* Plugin page extra links.
--------------------------------------------------------------------------------------------*/
		/**
		 * The main function responsible for returning the one true RatingWidgetPlugin instance
		 * to functions everywhere.
		 *
		 * Use this function like you would a global variable, except without needing
		 * to declare the global.
		 *
		 * Example: <?php $rw = ratingwidget(); ?>
		 *
		 * @return RatingWidgetPlugin The one true RatingWidgetPlugin instance
		 */
		function ratingwidget() {
			global $rwp;
			if (!isset($rwp)) {
				// Init Freemius.
				rw_fs();

//				rw_load_constants();
				$rwp = RatingWidgetPlugin::Instance();
				$rwp->Init();
			}

			return $rwp;
		}

		#region Freemius Helpers ------------------------------------------------------------------

		// Create a helper function for easy SDK access.
		/**
		 * @return Freemius
		 */
		function rw_fs() {
			/**
			 * @var Freemius $rw_fs
			 */
			global $rw_fs;
			if ( ! isset( $rw_fs ) ) {
				// Include Freemius SDK.
				require_once dirname(__FILE__) . '/freemius/start.php';

				if (WP_FS__IS_PRODUCTION_MODE)
				{
					$id = 56;
					$public_key = 'pk_74be465babd9d3d6d5ff578d56745';
				}
				else
				{
					$id = 30;
					$public_key = 'pk_d859cee50e9d63917b6d3f324cbaf';
				}

				$rw_fs = fs_dynamic_init( array(
					'id'                => $id,
					'public_key'        => $public_key,
					'slug'              => 'rating-widget',
					'menu_slug'         => 'rating-widget',
					'is_live'           => true,
					'is_premium'        => false,
					'has_addons'        => false,
					'has_paid_plans'    => true,
					'enable_anonymous'  => false,
					// Set the SDK to work in a sandbox mode (for development & testing).
					// Localhost FS environment params.
					'secret_key'        => (WP_FS__IS_PRODUCTION_MODE ?
						'sk_ErC9)z[}T{)n_QbB>6B!lVfxVoK?a' :
						'sk_j2nR2@g<Ts3jl]));Oi.k<wO]kKSH'),
				) );
			}

			return $rw_fs;
		}

		function rw_wf() {
			global $rw_wf;

			if ( ! isset( $rw_wf ) ) {
				$rw_wf = wf_init();
			}

			return $rw_wf;
		}

		function rw_migration_to_freemius()
		{
			if (!rwapi()->is_supported())
				// RW identity is not complete, cannot make API calls.
				return true;

			// Pull Freemius information from RatingWidget.
			$result = rwapi()->get(
				'freemius.json',
				false,
				// Cache result for 5 min, we don't want to cause slowdowns.
				WP_RW__TIME_5_MIN_IN_SEC
			);

			if (!is_object($result) || isset($result->error))
			{
				// Failed to pull account information.
				return false;
			}

			rw_fs()->setup_account(
				new FS_User($result->user),
				new FS_Site($result->install)
			);

			return true;
		}

		function rw_reset_account() {
			global $rw_fs;

			$rw_account = rw_account();
			$rw_api     = rwapi();

			// Before making any changes, make sure site credentials are good.

			// Preserve previous credentials.
			$prev_site_id         = $rw_account->site_id;
			$prev_site_public_key = $rw_account->site_public_key;
			$prev_site_secret_key = $rw_account->site_secret_key;

			// Get install information from request.
			$site_id         = rw_request_get( 'site_id' );
			$site_public_key = rw_request_get( 'site_public_key' );
			$site_secret_key = rw_request_get( 'site_secret_key' );

			$admin_notices = FS_Admin_Notice_Manager::instance( WP_RW__ID, 'Rating-Widget' );

			if ( ! is_numeric( $site_id ) ) {
				$admin_notices->add( 'Invalid site ID. Please contact our support for more details.' );

				return false;
			}
			if ( 32 !== strlen( $site_public_key ) ) {
				$admin_notices->add( 'Invalid public key. Please contact our support for more details.' );

				return false;
			}
			if ( 20 > strlen( $site_secret_key ) ) {
				$admin_notices->add( 'Invalid secret key. Please contact our support for more details.' );

				return false;
			}

			// Override site details.
			$rw_account->set_site( $site_id, $site_public_key, $site_secret_key, false );

			// Reload API with new account details.
			$rw_api->reload();

			if ( ! $rw_api->test() ) {
				// Fallback to previous account.
				$rw_account->set_site( $prev_site_id, $prev_site_public_key, $prev_site_secret_key, false );

				// Reload API with new account details.
				$rw_api->reload();

				$admin_notices->add( 'Invalid site credentials. Failed pining RatingWidget\'s API. Please contact our support for more details.' );

				return false;
			}

			// Save new RW credentials.
			$rw_account->save();

			if ($rw_fs->is_registered()) {
				// Send uninstall event.
				$rw_fs->_uninstall_plugin_event( false );

				if ( 'true' === rw_request_get( 'delete_account' ) ) // Delete account.
				{
					$rw_fs->delete_account_event( false );
				}
			}

			if ( rw_migration_to_freemius() ) {
				fs_redirect( $rw_fs->_get_admin_page_url( 'account' ) );

				exit;
			}

			return true;
		}

		#endregion Freemius Helpers ------------------------------------------------------------------

		#region Plugin Initialization ------------------------------------------------------------------

		// Init Freemius.
		$fs = rw_fs();

		// Init options
		rw_fs_options();

		// Try to load RatingWidget's account.
		$account = rw_account();

		// Init RW API (must be called after account is loaded).
		rwapi();

		if (rw_request_is_action('rw_reset_account') && !$rw_fs->is_ajax())
		{
			rw_reset_account();
		}
		else if (!$fs->is_registered() &&
		         ($fs->is_plugin_upgrade_mode() ||
		         rw_request_is_action('rw_migrate_to_freemius'))
		) {
			// Migration to new Freemius account management.
			if ( rw_migration_to_freemius() ) {
				$fs->set_plugin_upgrade_complete();
			}
		}

		/**
		 * Hook Rating-Widget early onto the 'plugins_loaded' action.
		 *
		 * This gives all other plugins the chance to load before Rating-Widget, to get
		 * their actions, filters, and overrides setup without RatingWidgetPlugin being in the
		 * way.
		 */
//define('WP_RW___LATE_LOAD', 20);
		if (defined('WP_RW___LATE_LOAD')) {
			add_action( 'plugins_loaded', 'ratingwidget', (int) WP_RW___LATE_LOAD );
		} else {
			$GLOBALS['rw'] = ratingwidget();
		}

		#endregion Plugin Initialization ------------------------------------------------------------------
	endif;
