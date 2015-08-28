<?php
	if ( ! class_exists( 'RW_Workflows' ) ) {
		require_once dirname( __FILE__ ) . '/config.php';
		require_once WP_WF__DIR_INCLUDES . '/workflows-core-functions.php';
		require_once WP_WF__DIR_INCLUDES . '/class-workflows.php';
		
		/**
		 * Returns an instance of RW_Workflows.
		 * 
         * @author Leo Fajardo (@leorw)
         * @since 1.0.0
         * 
		 * @return RW_Workflows
		 */
		function wf() {
			return RW_Workflows::instance();
		}

		/**
		 * Initializes an instance of RW_Workflows.
		 * 
         * @author Leo Fajardo (@leorw)
         * @since 1.0.0
         * 
		 * @return RW_Workflows
		 */
		function wf_init() {
			$wf = wf();
			$wf->init();
			return $wf;
		}
	}