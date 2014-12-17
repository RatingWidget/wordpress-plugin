<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	define('WP_FS__SLUG', 'freemius');

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