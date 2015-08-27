<?php
	if ( ! class_exists( 'Workflows' ) ) {
		require_once dirname( __FILE__ ) . '/config.php';
		require_once WP_WF__DIR_INCLUDES . '/workflows-core-functions.php';
		require_once WP_WF__DIR_INCLUDES . '/class-workflows-option-manager.php';
		require_once WP_WF__DIR_INCLUDES . '/class-workflows.php';
		
		/**
		 * Returns an instance of this class.
		 * 
		 * @return Workflows
		 */
		function wf( $slug ) {
			return Workflows::instance( $slug );
		}

		/**
		 * Initializes an instance of this class.
		 * 
		 * @param string $slug
		 * @param array $options
		 *
		 * @return Workflows
		 */
		function wf_init( $slug, $options = array() ) {
			$wf = wf( $slug );
			$wf->init( $options );
			return $wf;
		}
	}