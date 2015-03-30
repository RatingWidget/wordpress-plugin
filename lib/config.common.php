<?php
	// Exit if accessed directly.
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	/* Version
	-----------------------------------------------------------------------------------------*/
	define( 'WP_RW__VERSION', rw_get_plugin_version() );

	/* Localhost.
	-----------------------------------------------------------------------------------------*/
	define( 'WP_RW__LOCALHOST', ( $_SERVER['HTTP_HOST'] == 'localhost:8080' ) );

	/* Plugin dir and url
	-----------------------------------------------------------------------------------------*/
	define( 'WP_RW__PLUGIN_DIR', dirname( dirname( __FILE__ ) ) );
	define( 'WP_RW__PLUGIN_FILE', 'rating-widget.php' );
	define( 'WP_RW__PLUGIN_FILE_FULL', WP_RW__PLUGIN_DIR . '/' . WP_RW__PLUGIN_FILE );
	define( 'WP_RW__PLUGIN_LIB_DIR', WP_RW__PLUGIN_DIR . '/lib/' );
	define( 'WP_RW__PLUGIN_LIB_DIR_EXT', WP_RW__PLUGIN_LIB_DIR . '/extensions/' );
	define( 'RW__PATH_THEMES', WP_RW__PLUGIN_DIR . '/themes/' );
	define( 'WP_RW__PLUGIN_VIEW_DIR', WP_RW__PLUGIN_DIR . '/view/' );
	define( 'WP_RW__PLUGIN_URL', plugins_url() . '/' . dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/' );

	/* Load Unique-User-Key & API Secret
	-----------------------------------------------------------------------------------------*/
	if ( file_exists( dirname( __FILE__ ) . '/key.php' ) ) {
		require_once( dirname( __FILE__ ) . '/key.php' );
	}

	/* For Rating-Widget development mode.
	-----------------------------------------------------------------------------------------*/
	if ( file_exists( WP_RW__PLUGIN_LIB_DIR . '_dev_.php' ) ) {
		require_once( WP_RW__PLUGIN_LIB_DIR . '_dev_.php' );
	}

	if ( defined( 'WP_RW__USER_ID' ) ) {
		define( 'WP_RW__SITE_ID', WP_RW__USER_ID );
	}
	if ( defined( 'WP_RW__USER_KEY' ) ) {
		define( 'WP_RW__SITE_PUBLIC_KEY', WP_RW__USER_KEY );
	}
	if ( defined( 'WP_RW__USER_SECRET' ) ) {
		define( 'WP_RW__SITE_SECRET_KEY', WP_RW__USER_SECRET );
	}

	/* Load Custom Config.
	-----------------------------------------------------------------------------------------*/
	if ( file_exists( dirname( __FILE__ ) . '/rw-config-custom.php' ) ) {
		require_once( dirname( __FILE__ ) . '/rw-config-custom.php' );
	}

	/* Server Address & Remote Address
	-----------------------------------------------------------------------------------------*/
	// To run your tests on a local machine, hardcode your IP here.
	// To find your IP go to http://www.ip-adress.com/
	if ( WP_RW__LOCALHOST ) {
		define( 'WP_RW__SERVER_ADDR', '123.123.123.123' );
		define( 'WP_RW__CLIENT_ADDR', '123.123.123.123' );
	}

	/* Uncomment for debug mode.
	-----------------------------------------------------------------------------------------*/
	define( 'WP_RW__DEBUG_PARAMS', false || ( ! empty( $_GET['rwdbge'] ) && 'true' === $_GET['rwdbge'] ) );
	define( 'WP_RW__DEBUG', WP_RW__DEBUG_PARAMS || false || ( ! empty( $_GET['rwdbg'] ) && 'true' === $_GET['rwdbg'] ) );
	define( 'WP_RW__SHOW_PHP_ERRORS', false );
	define( 'WP_RW__LOCALHOST_SCRIPTS', WP_RW__DEBUG && false );
	define( 'WP_RW__CACHING_ON', ! WP_RW__DEBUG );
	define( 'WP_RW__STAGING', false );
	define( 'WP_RW__LOG_DUMP', WP_RW__DEBUG &&  ! empty( $_GET['rwdbge'] ));

	// This gives all other plugins the chance to load before RatingWidget.
//    define('WP_RW___LATE_LOAD', 999);

	if ( WP_RW__SHOW_PHP_ERRORS ) {
		error_reporting( E_ALL );
		ini_set( 'error_reporting', E_ALL );
		ini_set( 'display_errors', true );
		ini_set( 'html_errors', true );
	}

	/* General Consts
	-----------------------------------------------------------------------------------------*/
	define( 'WP_RW__ID', 'rating-widget' );
	define( 'WP_RW__NAME', 'RatingWidget' );
	define( 'WP_RW__DEFAULT_LNG', 'en' );
	define( 'WP_RW__ADMIN_MENU_SLUG', WP_RW__ID );

	define( 'WP_RW__OPTIONS', 'rw_options' );

	define( 'WP_RW__BLOG_POSTS_ALIGN', 'rw_blog_posts_align' );
	define( 'WP_RW__BLOG_POSTS_OPTIONS', 'rw_blog_posts_options' );

	define( 'WP_RW__COMMENTS_ALIGN', 'rw_comments_align' );
	define( 'WP_RW__COMMENTS_OPTIONS', 'rw_comments_options' );

	define( 'WP_RW__PAGES_ALIGN', 'rw_pages_align' );
	define( 'WP_RW__PAGES_OPTIONS', 'rw_pages_options' );

	define( 'WP_RW__FRONT_POSTS_ALIGN', 'rw_front_posts_align' );
	define( 'WP_RW__FRONT_POSTS_OPTIONS', 'rw_front_posts_options' );

	/* User-Key Options Consts.
	-----------------------------------------------------------------------------------------*/
	define( 'WP_RW__DB_OPTION_SITE_ID', 'rw_user_id' );
	define( 'WP_RW__DB_OPTION_SITE_PUBLIC_KEY', 'rw_user_key' );
	define( 'WP_RW__DB_OPTION_SITE_SECRET_KEY', 'rw_user_secret' );
	define( 'WP_RW__DB_OPTION_SITE_PLAN', 'rw_site_plan' );
	define( 'WP_RW__DB_OPTION_SITE_PLAN_UPDATE', 'rw_site_plan_update' );
	define( 'WP_RW__DB_OPTION_OWNER_ID', 'rw_owner_id' );
	define( 'WP_RW__DB_OPTION_OWNER_EMAIL', 'rw_owner_email' );
	define( 'WP_RW__DB_OPTION_TRACKING', 'rw_tracking' );
	define( 'WP_RW__DB_OPTION_WP_RATE_NOTICE_MIN_VOTES_TRIGGER', 'rw_wp_rate_notice_min_votes_trigger' );

	/* BuddyPress
	-----------------------------------------------------------------------------------------*/
	// BuddyPress plugin core file.
	define( 'WP_RW__BP_CORE_FILE', 'buddypress/bp-loader.php' );

	define( 'WP_RW__ACTIVITY_BLOG_POSTS_ALIGN', 'rw_activity_blog_posts_align' );
	define( 'WP_RW__ACTIVITY_BLOG_POSTS_OPTIONS', 'rw_activity_blog_posts_options' );

	define( 'WP_RW__ACTIVITY_BLOG_COMMENTS_ALIGN', 'rw_activity_blog_comments_align' );
	define( 'WP_RW__ACTIVITY_BLOG_COMMENTS_OPTIONS', 'rw_activity_blog_comments_options' );

	define( 'WP_RW__ACTIVITY_UPDATES_ALIGN', 'rw_activity_updates_align' );
	define( 'WP_RW__ACTIVITY_UPDATES_OPTIONS', 'rw_activity_updates_options' );

	define( 'WP_RW__ACTIVITY_COMMENTS_ALIGN', 'rw_activity_comments_align' );
	define( 'WP_RW__ACTIVITY_COMMENTS_OPTIONS', 'rw_activity_comments_options' );

	// bbPress component
	/*define('WP_RW__FORUM_TOPICS_ALIGN', 'rw_forum_topics_align');
	define('WP_RW__FORUM_TOPICS_OPTIONS', 'rw_forum_topics_options');*/

	define( 'WP_RW__FORUM_POSTS_ALIGN', 'rw_forum_posts_align' );
	define( 'WP_RW__FORUM_POSTS_OPTIONS', 'rw_forum_posts_options' );

	/*define('WP_RW__ACTIVITY_FORUM_TOPICS_ALIGN', 'rw_activity_forum_topics_align');
	define('WP_RW__ACTIVITY_FORUM_TOPICS_OPTIONS', 'rw_activity_forum_topics_options');*/

	define( 'WP_RW__ACTIVITY_FORUM_POSTS_ALIGN', 'rw_activity_forum_posts_align' );
	define( 'WP_RW__ACTIVITY_FORUM_POSTS_OPTIONS', 'rw_activity_forum_posts_options' );

	// User
	define( 'WP_RW__USERS_ALIGN', 'rw_users_align' );
	define( 'WP_RW__USERS_OPTIONS', 'rw_users_options' );
	// User accamulated ratings
	// Posts
	define( 'WP_RW__USERS_POSTS_ALIGN', 'rw_users_posts_align' );
	define( 'WP_RW__USERS_POSTS_OPTIONS', 'rw_users_posts_options' );
	// Pages
	define( 'WP_RW__USERS_PAGES_ALIGN', 'rw_users_pages_align' );
	define( 'WP_RW__USERS_PAGES_OPTIONS', 'rw_users_pages_options' );
	// Comments
	define( 'WP_RW__USERS_COMMENTS_ALIGN', 'rw_users_comments_align' );
	define( 'WP_RW__USERS_COMMENTS_OPTIONS', 'rw_users_comments_options' );
	// Activity-Updates
	define( 'WP_RW__USERS_ACTIVITY_UPDATES_ALIGN', 'rw_users_activity_updates_align' );
	define( 'WP_RW__USERS_ACTIVITY_UPDATES_OPTIONS', 'rw_users_activity_updates_options' );
	// Activity-Comments
	define( 'WP_RW__USERS_ACTIVITY_COMMENTS_ALIGN', 'rw_users_activity_comments_align' );
	define( 'WP_RW__USERS_ACTIVITY_COMMENTS_OPTIONS', 'rw_users_activity_comments_options' );
	// Forum-Posts
	define( 'WP_RW__USERS_FORUM_POSTS_ALIGN', 'rw_users_forum_posts_align' );
	define( 'WP_RW__USERS_FORUM_POSTS_OPTIONS', 'rw_users_forum_posts_options' );


	/* Settings
	-----------------------------------------------------------------------------------------*/
	define( 'WP_RW__SHOW_ON_EXCERPT', 'rw_show_on_excerpt' );
	define( 'WP_RW__SHOW_ON_ARCHIVE', 'rw_show_on_archive' );
	define( 'WP_RW__SHOW_ON_CATEGORY', 'rw_show_on_category' );
	define( 'WP_RW__SHOW_ON_SEARCH', 'rw_show_on_search' );
	define( 'WP_RW__VISIBILITY_SETTINGS', 'rw_visibility_settings' );
	define( 'WP_RW__READONLY_SETTINGS', 'rw_readonly_settings' );
	define( 'WP_RW__AVAILABILITY_SETTINGS', 'rw_availability_settings' );
	define( 'WP_RW__CATEGORIES_AVAILABILITY_SETTINGS', 'rw_categories_availability_settings' );
	define( 'WP_RW__CUSTOM_SETTINGS_ENABLED', 'rw_custom_settings_enabled' );
	define( 'WP_RW__CUSTOM_SETTINGS', 'rw_custom_settings' );
	define( 'WP_RW__MULTIRATING_SETTINGS', 'rw_multirating_settings' );
	define( 'WP_RW__IS_ACCUMULATED_USER_RATING', 'rw_accumulated_user_rating' );
	
	/* Visibility Options
	-----------------------------------------------------------------------------------------*/
	define( 'WP_RW__VISIBILITY_ALL_VISIBLE', 0 );
	define( 'WP_RW__VISIBILITY_EXCLUDE', 1 );
	define( 'WP_RW__VISIBILITY_INCLUDE', 2 );

	/* Availability Options
	-----------------------------------------------------------------------------------------*/
	define( 'WP_RW__AVAILABILITY_ACTIVE', 0 );    // Active for all users.
	define( 'WP_RW__AVAILABILITY_DISABLED', 1 );  // Disabled for logged out users.
	define( 'WP_RW__AVAILABILITY_HIDDEN', 2 );    // Hidden from logged out users.

	/* Advanced Settings
	-----------------------------------------------------------------------------------------*/
	define( 'WP_RW__IDENTIFY_BY', 'rw_identify_by' );
	define( 'WP_RW__FLASH_DEPENDENCY', 'rw_flash_dependency' );
	define( 'WP_RW__SHOW_ON_MOBILE', 'rw_show_on_mobile' );
	define( 'WP_RW__LOGGER', 'rw_logger' );

	define( 'WP_RW__USER_SECONDERY_ID', '00' );
	define( 'WP_RW__POST_SECONDERY_ID', '01' );
	define( 'WP_RW__PAGE_SECONDERY_ID', '02' );
	define( 'WP_RW__COMMENT_SECONDERY_ID', '03' );
	define( 'WP_RW__ACTIVITY_UPDATE_SECONDERY_ID', '04' );
	define( 'WP_RW__ACTIVITY_COMMENT_SECONDERY_ID', '05' );
	define( 'WP_RW__FORUM_POST_SECONDERY_ID', '06' );

	/* Reports Consts
	-----------------------------------------------------------------------------------------*/
	define( 'WP_RW__REPORT_RECORDS_MIN', 10 );
	define( 'WP_RW__REPORT_RECORDS_MAX', 50 );
	define( 'WP_RW__PERIOD_MONTH', 2678400 );
	define( 'WP_RW__DEFAULT_DATE_FORMAT', 'Y-n-d' );
	define( 'WP_RW__DEFAULT_TIME_FORMAT', 'H:i:s' );

	/* Stars Consts
	-----------------------------------------------------------------------------------------*/
	define( 'WP_RW__DEF_STARS', 5 );
	define( 'WP_RW__MIN_STARS', 1 );
	define( 'WP_RW__MAX_STARS', 20 );

	define( 'WP_RW__TIME_5_MIN_IN_SEC', 300 );
	define( 'WP_RW__TIME_10_MIN_IN_SEC', 600 );
	define( 'WP_RW__TIME_15_MIN_IN_SEC', 900 );
	define( 'WP_RW__TIME_24_HOURS_IN_SEC', 86400 );
	define( 'WP_RW__TIME_WEEK_IN_SEC', 7 * WP_RW__TIME_24_HOURS_IN_SEC );
	define( 'WP_RW__TIME_30_DAYS_IN_SEC', 30 * WP_RW__TIME_24_HOURS_IN_SEC );
	define( 'WP_RW__TIME_6_MONTHS_IN_SEC', 6 * WP_RW__TIME_30_DAYS_IN_SEC );
	define( 'WP_RW__TIME_YEAR_IN_SEC', 365 * WP_RW__TIME_30_DAYS_IN_SEC );
	define( 'WP_RW__TIME_ALL_TIME', -1 );

	/* Local caching
	-----------------------------------------------------------------------------------------*/
	define( 'WP_RW__CACHE_TIMEOUT_REPORT', WP_RW__TIME_15_MIN_IN_SEC );
	define( 'WP_RW__CACHE_TIMEOUT_RICH_SNIPPETS', WP_RW__TIME_24_HOURS_IN_SEC );
	define( 'WP_RW__CACHE_TIMEOUT_TOP_RATED', WP_RW__TIME_5_MIN_IN_SEC );
	define( 'WP_RW__CACHE_TIMEOUT_POST_THUMB_EXTRACT', WP_RW__TIME_24_HOURS_IN_SEC );
	define( 'WP_RW__CACHE_TIMEOUT_DASHBOARD_STATS', WP_RW__TIME_24_HOURS_IN_SEC );

	/* Freemius Overrides
	-----------------------------------------------------------------------------------------*/
//	define('WP_FS__ACCOUNT_OPTION_NAME', WP_RW__OPTIONS);

	/* Rating-Widget URIs
	-----------------------------------------------------------------------------------------*/
	if ( ! defined( 'WP_RW__DOMAIN' ) ) {
		if ( WP_RW__LOCALHOST_SCRIPTS ) {
			define( 'WP_RW__DOMAIN', 'localhost:8080' );
		} else if ( defined( 'WP_RW__STAGING' ) && true === WP_RW__STAGING ) {
			define( 'WP_RW__DOMAIN', 'staging.rating-widget.com' );
		} else if ( WP_RW__LOCALHOST && WP_RW__DEBUG ) {
			define( 'WP_RW__DOMAIN', $_SERVER['HTTP_HOST'] );
		} else {
			define( 'WP_RW__DOMAIN', 'rating-widget.com' );
		}
	}

	define('WP_RW__SCRIPT_URL', substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], '?')));

	if ( ! defined( 'WP_RW__SECURE_DOMAIN' ) ) {
		define( 'WP_RW__SECURE_DOMAIN', 'rating-widget.com' );
	}


	define( 'WP_RW__HTTPS',
		// Checks if CloudFlare's HTTPS (Flexible SSL support)
		( isset( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && 'https' === strtolower( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) ) ||
		// Check if HTTPS request.
		( isset( $_SERVER['HTTPS'] ) && 'on' == $_SERVER['HTTPS'] ) ||
		( isset( $_SERVER['SERVER_PORT'] ) && 443 == $_SERVER['SERVER_PORT'] )
	);

	define( 'WP_RW__PROTOCOL', ( WP_RW__HTTPS ? 'https' : 'http' ) );

	define( 'WP_RW__ADDRESS', 'http://' . WP_RW__DOMAIN );
	define( 'WP_RW__SECURE_ADDRESS', 'https://' . WP_RW__SECURE_DOMAIN );

	/* Server Address & Remote Address
	-----------------------------------------------------------------------------------------*/
	if ( ! defined( 'WP_RW__SERVER_ADDR' ) ) {
		define( 'WP_RW__SERVER_ADDR', $_SERVER['SERVER_ADDR'] );
	}
	if ( ! defined( 'WP_RW__CLIENT_ADDR' ) ) {
		define( 'WP_RW__CLIENT_ADDR', $_SERVER['REMOTE_ADDR'] );
	}
