<?php
	/**
	 * @package     RatingWidget
	 * @copyright   Copyright (c) 2015, Rating-Widget, Inc.
	 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
	 * @since       2.6.0
	 */

	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	class RW_Api {
		/**
		 * @var RW_Api
		 */
		private static $_instance;

		/**
		 * @var FS_Option_Manager Freemius options, options-manager.
		 */
		private static $_options;

		/**
		 * @var FS_Option_Manager API Caching layer
		 */
		private static $_cache;

		/**
		 * @var int Clock diff in seconds between current server to API server.
		 */
		private static $_clock_diff;

		/**
		 * @var RatingWidget
		 */
		private $_api;

		/**
		 * @var FS_Logger
		 */
		private $_logger;

		/**
		 * @return \RW_Api
		 */
		static function instance( ) {
			if ( ! isset( self::$_instance ) ) {
					self::_init();
					self::$_instance = new RW_Api();
			}

			return self::$_instance;
		}

		private static function _init() {
			if ( ! class_exists( 'RatingWidget' ) ) {
				require_once( WP_RW__PLUGIN_LIB_DIR . 'sdk/ratingwidget.php' );
			}

			self::$_options    = FS_Option_Manager::get_manager( WP_RW__OPTIONS, true );
			self::$_cache      = FS_Option_Manager::get_manager( 'rw_api_cache', true );

			self::$_clock_diff = self::$_options->get_option( 'api_clock_diff', 0 );

			RatingWidget::SetClockDiff( self::$_clock_diff );

			if ( self::$_options->get_option( 'api_force_http', false ) ) {
				RatingWidget::SetHttp();
			}
		}

		private function __construct()
		{
			$rw_account = rw_account();

			if ($rw_account->has_public_key() &&
			    !$rw_account->has_secret_key())
			{
				// Try to claim for a secret key.
				$this->claim_secret_key();
			}

			$this->reload();

			$this->_logger = FS_Logger::get_logger(WP_RW__ID . '_api', WP_FS__DEBUG_SDK, WP_FS__ECHO_DEBUG_SDK);
		}

		function reload()
		{
			if (!$this->is_supported())
				return;

			$rw_account = rw_account();

			$this->_api = new RatingWidget(
				'site',
				$rw_account->site_id,
				$rw_account->site_public_key,
				$rw_account->site_secret_key
			);
		}

		/**
		 * Claim secret key from RW server to enable API calls.
		 *
		 * This is a workaround for very old accounts who does not have secret key generated.
		 *
		 * @author Vova Feldman (svovaf)
		 * @since 2.6.6
		 */
		function claim_secret_key() {
			$rw_account = rw_account();

			$response_body = get_transient( 'rw_claim_secret_key' );

			if ( false === $response_body ) {
				// Try to claim secret key.
				$response = wp_remote_post( WP_RW__ADDRESS . '/action/api/site/claim-secret-key/', array(
					'body' => array(
						'site_public_key' => $rw_account->site_public_key
					)
				) );

				if ( ! is_wp_error( $response ) ) {
					$response_body = wp_remote_retrieve_body( $response );
				} else {
					$response_body = '';
				}

				set_transient( 'rw_claim_secret_key', $response_body, WP_RW__TIME_5_MIN_IN_SEC );
			}

			$result = json_decode( $response_body );

			if ( is_object( $result ) &&
			     is_object( $result->data->site ) &&
			     isset( $result->data->site->id )
			) {
				$site = $result->data->site;
				$rw_account->set_site( $site->id, $site->public_key, $site->secret_key );

				if ( isset( $result->data->user ) ) {
					$user = $result->data->user;
					$rw_account->set_user( $user->id, $user->email );
				}
			}
		}

		/**
		 * Find clock diff between server and API server, and store the diff locally.
		 *
		 * @return bool|int False if clock diff didn't change, otherwise returns the clock diff in seconds.
		 */
		private function _sync_clock_diff()
		{
			$this->_logger->entrance();

			// Sync clock and store.
			$new_clock_diff = $this->_api->FindClockDiff();

			if ($new_clock_diff === self::$_clock_diff)
				return false;

			// Update API clock's diff.
			$this->_api->SetClockDiff(self::$_clock_diff);

			// Store new clock diff in storage.
			self::$_options->set_option('api_clock_diff', self::$_clock_diff, true);

			return $new_clock_diff;
		}

		/**
		 * Override API call to enable retry with servers' clock auto sync method.
		 *
		 * @param string $path
		 * @param string $method
		 * @param array  $params
		 * @param bool   $retry Is in retry or first call attempt.
		 *
		 * @return array|mixed|string|void
		 */
		private function _call($path, $method = 'GET', $params = array(), $retry = false) {
			$this->_logger->entrance();

			$result = $this->_api->Api( $path, $method, $params );

			if ( null !== $result &&
			     isset( $result->error ) &&
			     'request_expired' === $result->error->code
			) {

				if ( ! $retry ) {
					// Try to sync clock diff.
					if ( false !== $this->_sync_clock_diff() ) // Retry call with new synced clock.
					{
						return $this->_call( $path, $method, $params, true );
					}
				}
			}

			if ( null !== $result && isset( $result->error ) ) {
				// Log API errors.
				$this->_logger->error( $result->error->message );
			}

			return $result;
		}

		/**
		 * Override API call to wrap it in servers' clock sync method.
		 *
		 * @param string $path
		 * @param string $method
		 * @param array  $params
		 *
		 * @return array|mixed|string|void
		 * @throws Freemius_Exception
		 */
		function call($path, $method = 'GET', $params = array())
		{
			return $this->_call($path, $method, $params);
		}

		/**
		 * @param string $path
		 * @param bool   $flush
		 * @param int    $expiration (optional) Time until expiration in seconds from now, defaults to 24 hours
		 *
		 * @return stdClass|mixed
		 */
		function get($path = '/', $flush = false, $expiration = WP_RW__TIME_24_HOURS_IN_SEC)
		{
			$cache_key = $this->get_cache_key($path);

			// Always flush during development.
			if (WP_RW__DEBUG)
				$flush = true;

			// Get result from cache anyways, because we want to fallback on error.
			$cache_entry = self::$_cache->get_option($cache_key, false);

			$fetch = false;
			if ($flush ||
			    false === $cache_entry ||
				!isset($cache_entry->timestamp) ||
				!is_numeric($cache_entry->timestamp) ||
			    $cache_entry->timestamp < WP_RW__SCRIPT_START_TIME)
			{
				$fetch = true;
			}

			if ($fetch)
			{
				$result = $this->call($path);

				if (!is_object($result) || isset($result->error))
				{
					// If there was an error during a newer data fetch,
					// then fallback to older data version.
					if (is_object($cache_entry) &&
						isset($cache_entry->result) &&
						!isset($cache_entry->result->error))
					{
						$result = $cache_entry->result;
					}
				}

				$cache_entry = new stdClass();
				$cache_entry->result = $result;
				$cache_entry->timestamp = WP_RW__SCRIPT_START_TIME + $expiration;
				self::$_cache->set_option($cache_key, $cache_entry, true);
			}

			return $cache_entry->result;
		}

		private function get_cache_key($path, $method = 'GET', $params = array())
		{
			$canonized = $this->_api->CanonizePath($path);
//			$exploded = explode('/', $canonized);
//			return $method . '_' . array_pop($exploded) . '_' . md5($canonized . json_encode($params));
			return $method . ':' . $canonized . (!empty($params) ? '#' . md5(json_encode($params))  : '');
		}
		
		/**
		 * Clears the cache for the specific endpoint.
		 * 
		 * @param string $path
		 */
		function unset_get_cache( $path = '/' ) {
			$cache_key = $this->get_cache_key( $path );

			self::$_cache->unset_option( $cache_key, true );
		}
		
		/**
		 * @return bool True if successful connectivity to the API.
		 */
		function test()
		{
			$this->_logger->entrance();

			$test = $this->_api->Test();

			if ( false === $test && $this->_api->IsHttps() ) {
				// Fallback to HTTP, since HTTPS fails.
				$this->_api->SetHttp();

				self::$_options->set_option( 'api_force_http', true, true );

				$test = $this->_api->Test();
			}

			return $test;
		}

		function get_url($path = '')
		{
			return $this->_api->GetUrl($path);
		}

		function is_supported()
		{
			$rw_account = rw_account();
			return ($rw_account->is_registered() && $rw_account->has_secret_key());
		}
	}

	/**
	 * @return \RW_Api|bool
	 */
	function rwapi()
	{
		/**
		 * @var RW_Api $rwapi
		 */
		global $rwapi;

		if (!isset($rwapi))
		{
			$rwapi = RW_Api::instance();
		}

		return $rwapi;
	}