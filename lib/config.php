<?php
	// Exit if accessed directly.
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	if ( WP_RW__LOCALHOST ) {
		define( "WP_RW__ADDRESS_CSS", "http://" . WP_RW__DOMAIN . "/css/" );
		define( "WP_RW__ADDRESS_JS", "http://" . WP_RW__DOMAIN . "/js/" );
		define( "WP_RW__ADDRESS_IMG", "http://" . WP_RW__DOMAIN . "/img/" );
		define( "WP_RW__ADDRESS_TMB", "http://" . WP_RW__DOMAIN . "/apps/thumb/" );
	} else if ( WP_RW__LOCALHOST_SCRIPTS ) {
		// For development testing on remote machine with local scripts.
		define( "WP_RW__ADDRESS_CSS", "http://localhost:8080/css/" );
		define( "WP_RW__ADDRESS_JS", "http://localhost:8080/js/" );
		define( "WP_RW__ADDRESS_IMG", "http://localhost:8080/img/" );
		define( "WP_RW__ADDRESS_TMB", "http://localhost:8080/apps/thumb/" );
	} else if ( WP_RW__HTTPS ) {
		define( "WP_RW__ADDRESS_CSS", "https://secure." . WP_RW__DOMAIN . "/css/" );
		define( "WP_RW__ADDRESS_JS", "https://secure." . WP_RW__DOMAIN . "/js/" );
		define( "WP_RW__ADDRESS_IMG", "https://secure." . WP_RW__DOMAIN . "/img/" );
		define( "WP_RW__ADDRESS_TMB", "https://secure." . WP_RW__DOMAIN . "/apps/thumb/" );
	} else if ( defined( 'WP_RW__STAGING' ) && true === WP_RW__STAGING ) {
		define( "WP_RW__ADDRESS_CSS", "http://scss.rating-widget.com/" );
		define( "WP_RW__ADDRESS_JS", "http://sjs.rating-widget.com/" );
		define( "WP_RW__ADDRESS_IMG", "http://simg.rating-widget.com/" );
		define( "WP_RW__ADDRESS_TMB", "http://stmb.rating-widget.com/" );
	} else {
		define( "WP_RW__ADDRESS_CSS", "http://css.rating-widget.com/" );
		define( "WP_RW__ADDRESS_JS", "http://js.rating-widget.com/" );
		define( "WP_RW__ADDRESS_IMG", "http://img.rating-widget.com/" );
		define( "WP_RW__ADDRESS_TMB", "http://tmb.rating-widget.com/" );
	}