<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	/**
	 * Freemius hooks collection:
	 *  fs_after_license_loaded
	 */

	if (!class_exists('Freemius')) {

		// Configuration should be loaded first.
		require_once dirname(__FILE__) . '/config.php';

		// Logger must be loaded before any other.
		require_once WP_FS__DIR_INCLUDES . '/class-fs-logger.php';

		require_once WP_FS__DIR_INCLUDES . '/fs-core-functions.php';
		require_once WP_FS__DIR_INCLUDES . '/class-fs-option-manager.php';
		require_once WP_FS__DIR_INCLUDES . '/class-fs-user.php';
		require_once WP_FS__DIR_INCLUDES . '/class-fs-site.php';
		require_once WP_FS__DIR_INCLUDES . '/class-freemius.php';

		if (file_exists(WP_FS__DIR_INCLUDES . '/_class-fs-debug.php'))
			require_once WP_FS__DIR_INCLUDES . '/_class-fs-debug.php';

		/**
		 * @return Freemius
		 */
		function fs($slug)
		{
			return Freemius::instance($slug);
		}

		/**
		 * @param string $slug
		 * @param string $developer_id
		 * @param string $public_key
		 * @param array $options
		 *
		 * @return Freemius
		 */
		function fs_init($slug, $developer_id, $public_key, array $options)
		{
			$fs = fs($slug);
			$fs->init($developer_id, $public_key, $options);
			return $fs;
		}

		function fs_dump_log()
		{
			FS_Logger::dump();
		}
	}