<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	define('WP_FS__SLUG', 'freemius');
	define( 'WP_FS__DEV_MODE', false );

	/**
	 * Directories
	 */
	define('WP_FS__DIR', dirname(__FILE__));
	define('WP_FS__DIR_INCLUDES', WP_FS__DIR . '/includes');
	define('WP_FS__DIR_TEMPLATES', WP_FS__DIR . '/templates');
	define('WP_FS__DIR_ASSETS', WP_FS__DIR . '/assets');
	define('WP_FS__DIR_CSS', WP_FS__DIR_ASSETS . '/css');
	define('WP_FS__DIR_JS', WP_FS__DIR_ASSETS . '/js');


	if (!defined('WP_FS__ACCOUNTS_OPTION_NAME')) {
		define( 'WP_FS__ACCOUNTS_OPTION_NAME', 'fs_accounts' );
	}

	/**
	 * Domain / URL / Address
	 */
	define( 'WP_FS__TESTING_DOMAIN', 'fswp:8080' );
	define( 'WP_FS__DOMAIN_PRODUCTION', 'wp.freemius.com' );
	define( 'WP_FS__DOMAIN_LOCALHOST', 'wp.freemius' );
	define( 'WP_FS__ADDRESS_LOCALHOST', 'http://' . WP_FS__DOMAIN_LOCALHOST . ':8080' );
	define( 'WP_FS__ADDRESS_PRODUCTION', 'https://' . WP_FS__DOMAIN_PRODUCTION );

	define( 'WP_FS__IS_PRODUCTION', !defined('WP_FS__DEV_MODE') || !WP_FS__DEV_MODE || (WP_FS__TESTING_DOMAIN !== $_SERVER['HTTP_HOST'] ) );

	define( 'WP_FS__ADDRESS', ( WP_FS__IS_PRODUCTION ? WP_FS__ADDRESS_PRODUCTION : WP_FS__ADDRESS_LOCALHOST ) );

	// Set API address for local testing.
	if ( ! WP_FS__IS_PRODUCTION ) {
		define( 'FS_API__ADDRESS', 'http://api.freemius:8080' );
		define('FS_API__SANDBOX_ADDRESS', 'http://sandbox-api.freemius:8080');
	}

	/**
	 * Billing Frequencies
	 */
	define('WP_FS__PERIOD_ANNUALLY', 'annually');
	define('WP_FS__PERIOD_MONTHLY', 'monthly');
	define('WP_FS__PERIOD_LIFETIME', 'lifetime');

	/**
	 * Plans
	 */
	define('WP_FS__PLAN_DEFAULT_PAID', false);
	define('WP_FS__PLAN_FREE', 'free');
	define('WP_FS__PLAN_TRIAL', 'trial');

	/**
	 * Debugging
	 */
	define('WP_FS__DEBUG_SDK', !empty( $_GET['fs_dbg'] ));
	define('WP_FS__ECHO_DEBUG_SDK', !empty( $_GET['fs_dbg_echo'] ));
	define('WP_FS__LOG_DATETIME_FORMAT', 'Y-n-d H:i:s');


	define('WP_FS__LOWEST_PRIORITY', 999999999);