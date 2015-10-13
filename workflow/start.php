<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	if ( ! class_exists( 'RW_Workflows' ) ) {
		require_once dirname( __FILE__ ) . '/config.php';
		require_once WP_WF__DIR_INCLUDES . '/workflows-core-functions.php';
		require_once WP_WF__DIR_INCLUDES . '/class-workflows.php';

		/**
		 * Returns an instance of RW_Workflows.
		 *
		 * @author Leo Fajardo (@leorw)
		 * @since  1.0.0
		 *
		 * @return RW_Workflows
		 */
		function rw_wf() {
			global $rw_wf;

			if ( ! isset( $rw_wf ) ) {
				$rw_wf = RW_Workflows::instance();
				$rw_wf->init();
			}

			return $rw_wf;
		}
	}