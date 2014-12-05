<?php
	if ( ! defined( 'ABSPATH' ) ) exit;

	function rw_activated() {
		add_option( 'rw_do_activation_redirect', true );
	}

	function rw_deactivated() {

	}


// Load the admin
	if (is_admin())
		add_action('plugins_loaded', 'rw_admin', 10);