<?php
	global $fs_core_logger;

	$fs_core_logger = FS_Logger::get_logger( WP_FS__SLUG . '_core', WP_FS__DEBUG_SDK, WP_FS__ECHO_DEBUG_SDK );

	function fs_dummy(){ }

	/* Url.
	--------------------------------------------------------------------------------------------*/
	function fs_admin_url( $page = WP_RW__ADMIN_MENU_SLUG, $path = 'admin.php', $scheme = 'admin' ) {
		echo fs_get_admin_url( $path, $page, $scheme );
	}

	function fs_get_admin_url( $page = WP_RW__ADMIN_MENU_SLUG, $path = 'admin.php', $scheme = 'admin' ) {
		return add_query_arg( array( 'page' => $page ), admin_url( $path, $scheme ) );
	}

	function fs_admin_plugin_url( $page ) {
		echo fs_get_admin_plugin_url( $page );
	}

	function fs_get_admin_plugin_url( $page ) {
		return fs_get_admin_url( WP_RW__ADMIN_MENU_SLUG . '-' . $page, 'admin.php' );
	}

	function fs_get_url_daily_cache_killer() {
		return date( '\YY\Mm\Dd' );
	}

	/* Redirect.
	--------------------------------------------------------------------------------------------*/
	function fs_admin_redirect( $location = 'admin.php' ) {
		fs_redirect( fs_get_admin_url( $location ) );
		exit();
	}

	function fs_site_redirect( $location = '' ) {
		fs_redirect( fs_get_site_url( $location ) );
		exit();
	}

	/* Templates / Views.
	--------------------------------------------------------------------------------------------*/
	function fs_get_template_path( $path ) {
		return WP_FS__DIR_TEMPLATES . '/' . trim( $path, '/' );
	}

	function fs_include_template( $path, &$params = null ) {
		$VARS = &$params;
		include( fs_get_template_path( $path ) );
	}

	function fs_include_once_template( $path, &$params = null ) {
		$VARS = &$params;
		include_once( fs_get_template_path( $path ) );
	}

	function fs_require_template( $path, &$params = null ) {
		$VARS = &$params;
		require( fs_get_template_path( $path ) );
	}

	function fs_require_once_template( $path, &$params = null ) {
		$VARS = &$params;
		require_once( fs_get_template_path( $path ) );
	}

	/* Scripts and styles including.
	--------------------------------------------------------------------------------------------*/
	function fs_enqueue_local_style($handle, $path, $deps = array(), $ver = false, $media = 'all') {
		global $fs_core_logger;
		if ( $fs_core_logger->is_on() ) {
			$fs_core_logger->info( 'handle = ' . $handle . '; path = ' . $path . ';' );
			$fs_core_logger->info( 'plugin_basename = ' . plugins_url( WP_FS__DIR_CSS . trim( $path, '/' ) ) );
			$fs_core_logger->info( 'plugins_url = ' . plugins_url( plugin_basename( WP_FS__DIR_CSS . '/' . trim( $path, '/' ) ) ) );
		}

		wp_enqueue_style( $handle, plugins_url( plugin_basename( WP_FS__DIR_CSS . '/' . trim( $path, '/' ) ) ), $deps, $ver, $media );
	}

	function fs_enqueue_local_script($handle, $path, $deps = array(), $ver = false, $in_footer = 'all') {
		global $fs_core_logger;
		if ( $fs_core_logger->is_on() ) {
			$fs_core_logger->info( 'handle = ' . $handle . '; path = ' . $path . ';' );
			$fs_core_logger->info( 'plugin_basename = ' . plugins_url( WP_FS__DIR_JS . trim( $path, '/' ) ) );
			$fs_core_logger->info( 'plugins_url = ' . plugins_url( plugin_basename( WP_FS__DIR_JS . '/' . trim( $path, '/' ) ) ) );
		}

		wp_enqueue_script( $handle, plugins_url( plugin_basename( WP_FS__DIR_JS . '/' . trim( $path, '/' ) ) ), $deps, $ver, $in_footer );
	}

	/* Request handlers.
	--------------------------------------------------------------------------------------------*/
	function fs_request_get( $key, $def = false ) {
		return isset( $_REQUEST[ $key ] ) ? $_REQUEST[ $key ] : $def;
	}

	function fs_request_is_post() {
		return ( 'post' === strtolower( $_SERVER['REQUEST_METHOD'] ) );
	}

	function fs_request_is_get() {
		return ( 'get' === strtolower( $_SERVER['REQUEST_METHOD'] ) );
	}

	function fs_request_is_action( $action, $action_key = 'action' ) {
		$is_action = ( ! empty( $_REQUEST[ $action_key ] ) && $action === $_REQUEST[ $action_key ] );

		if ( $is_action ) {
			return true;
		}

		if ( 'action' == $action_key ) {
			$action_key = 'fs_action';

			return ( ! empty( $_REQUEST[ $action_key ] ) && $action === $_REQUEST[ $action_key ] );
		}

		return false;
	}

	/* Core Redirect (copied from BuddyPress).
	--------------------------------------------------------------------------------------------*/
	/**
	 * Redirects to another page, with a workaround for the IIS Set-Cookie bug.
	 *
	 * @link http://support.microsoft.com/kb/q176113/
	 * @since 1.5.1
	 * @uses apply_filters() Calls 'wp_redirect' hook on $location and $status.
	 *
	 * @param string $location The path to redirect to
	 * @param int $status Status code to use
	 *
	 * @return bool False if $location is not set
	 */
	function fs_redirect( $location, $status = 302 ) {
		global $is_IIS;

		if ( headers_sent() ) {
			return false;
		}

		if ( ! $location ) // allows the wp_redirect filter to cancel a redirect
		{
			return false;
		}

		$location = fs_sanitize_redirect( $location );

		if ( $is_IIS ) {
			header( "Refresh: 0;url=$location" );
		} else {
			if ( php_sapi_name() != 'cgi-fcgi' ) {
				status_header( $status );
			} // This causes problems on IIS and some FastCGI setups
			header( "Location: $location" );
		}
	}

	/**
	 * Sanitizes a URL for use in a redirect.
	 *
	 * @since 2.3
	 *
	 * @return string redirect-sanitized URL
	 **/
	function fs_sanitize_redirect( $location ) {
		$location = preg_replace( '|[^a-z0-9-~+_.?#=&;,/:%!]|i', '', $location );
		$location = fs_kses_no_null( $location );

		// remove %0d and %0a from location
		$strip = array( '%0d', '%0a' );
		$found = true;
		while ( $found ) {
			$found = false;
			foreach ( (array) $strip as $val ) {
				while ( strpos( $location, $val ) !== false ) {
					$found    = true;
					$location = str_replace( $val, '', $location );
				}
			}
		}

		return $location;
	}

	/**
	 * Removes any NULL characters in $string.
	 *
	 * @since 1.0.0
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	function fs_kses_no_null( $string ) {
		$string = preg_replace( '/\0+/', '', $string );
		$string = preg_replace( '/(\\\\0)+/', '', $string );

		return $string;
	}
