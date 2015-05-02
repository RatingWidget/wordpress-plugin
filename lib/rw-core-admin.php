<?php
	if ( ! defined( 'ABSPATH' ) ) exit;

	if (!class_exists('RatingWidgetPlugin_Admin')) :

		class RatingWidgetPlugin_Admin {
			public function __construct() {
//        register_activation_hook(WP_RW__PLUGIN_FILE_FULL, array(&$this, 'Activated'));

				add_action( 'admin_init', array( &$this, 'RedirectOnActivation' ) );
			}

			public function RedirectOnActivation() {
				if ( get_option( 'rw_do_activation_redirect', false ) ) {
					delete_option( 'rw_do_activation_redirect' );
					wp_redirect( rw_get_admin_url() );
					exit();
				}
			}
		}

		function rw_admin() {
			ratingwidget()->admin = new RatingWidgetPlugin_Admin();
		}

	endif;