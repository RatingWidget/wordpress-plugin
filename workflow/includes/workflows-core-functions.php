<?php
	/* Templates / Views.
	--------------------------------------------------------------------------------------------*/
	function wf_get_template_path( $path ) {
		return WP_WF__DIR_TEMPLATES . '/' . trim( $path, '/' );
	}

	function wf_include_template( $path, &$params = null ) {
		$VARS = &$params;
		include( wf_get_template_path( $path ) );
	}

	function wf_include_once_template( $path, &$params = null ) {
		$VARS = &$params;
		include_once( wf_get_template_path( $path ) );
	}

	function wf_require_template( $path, &$params = null ) {
		$VARS = &$params;
		require( wf_get_template_path( $path ) );
	}

	function wf_require_once_template( $path, &$params = null ) {
		$VARS = &$params;
		require_once( wf_get_template_path( $path ) );
	}
	
	/* Scripts and styles including.
	--------------------------------------------------------------------------------------------*/
	function wf_enqueue_local_style( $handle, $path, $deps = array(), $ver = false, $media = 'all' ) {
		wp_enqueue_style( $handle, plugins_url( plugin_basename( WP_WF__DIR_CSS . '/' . trim( $path, '/' ) ) ), $deps, $ver, $media );
	}

	function wf_enqueue_local_script( $handle, $path = false, $deps = array(), $ver = false, $in_footer = 'all' ) {
		if ( $path ) {
			wp_enqueue_script( $handle, plugins_url( plugin_basename( WP_WF__DIR_JS . '/' . trim( $path, '/' ) ) ), $deps, $ver, $in_footer );
		} else {
			wp_enqueue_script( $handle );
		}
	}