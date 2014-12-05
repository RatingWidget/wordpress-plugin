<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	/**
	 * 3-layer lazy options manager.
	 *      layer 3: Memory
	 *      layer 2: Cache (if there's any caching plugin)
	 *      layer 1: Database (options table). All options stored as one option record in the DB to reduce number of DB queries.
	 *
	 * If load() is not explicitly called, starts as empty manager. Same thing about saving the data - you have to explicitly call store().
	 *
	 * Class FreemiusOptionManager
	 */
	class FS_Option_Manager {
		private $_id;
		private $_options;
		private $_logger;

		private static $_MANAGERS = array();

		private function __construct($id, $load = false) {
			$this->_logger = FS_Logger::get_logger( WP_FS__SLUG . '_opt_mngr_' . $id, WP_FS__DEBUG_SDK, WP_FS__ECHO_DEBUG_SDK );

			$this->_logger->entrance();
			$this->_logger->log( 'id = ' . $id );

			$this->_id = $id;

			if ( $load ) {
				$this->load();
			}
		}

		/**
		 * @param $id
		 * @param $load
		 *
		 * @return FS_Option_Manager
		 */
		static function get_manager($id, $load = false) {
			$id = strtolower( $id );

			if ( ! isset( self::$_MANAGERS[ $id ] ) ) {
				self::$_MANAGERS[ $id ] = new FS_Option_Manager( $id, $load );
			} // If load required but not yet loaded, load.
			else if ( $load && ! self::$_MANAGERS[ $id ]->is_loaded() ) {
				self::$_MANAGERS[ $id ]->load();
			}

			return self::$_MANAGERS[ $id ];
		}

		private function _get_option_manager_name()
		{
//			return WP_FS__SLUG . '_' . $this->_id;
			return $this->_id;
		}

		function load($flush = false)
		{
			$this->_logger->entrance();

			$option_name = $this->_get_option_manager_name();

			if ($flush || !isset($this->_options))
			{
				$this->_options = wp_cache_get($option_name, WP_FS__SLUG);

				if (is_array($this->_options))
					$this->clear();

				$cached = true;

				if (false === $this->_options)
				{
					$this->_options = get_option($option_name);

					if (false !== $this->_options)
						$this->_options = json_decode($this->_options);

					if (is_array($this->_options))
						$this->clear();

					$cached = false;
				}

				if (!$cached)
					// Set non encoded cache.
					wp_cache_set($option_name, $this->_options, WP_FS__SLUG);
			}
		}

		function is_loaded()
		{
			return isset($this->_options);
		}

		function is_empty()
		{
			return ($this->is_loaded() && false === $this->_options);
		}

		function clear($flush = false)
		{
			$this->_logger->entrance();

			$this->_options = new stdClass();

			if ($flush)
				$this->store();
		}

		function get_option($option, $default = null)
		{
			$this->_logger->entrance('option = ' . $option);

			return isset($this->_options->{$option}) ? $this->_options->{$option} : $default;
		}

		function set_option($option, $value, $flush = false)
		{
			$this->_logger->entrance('option = ' . $option);

			if (!$this->is_loaded())
				$this->clear();

			$this->_options->{$option} = $value;

			if ($flush)
				$this->store();
		}

		function unset_option($option, $flush = false)
		{
			$this->_logger->entrance('option = ' . $option);

			if (!isset($this->_options->{$option}))
				return;

			unset($this->_options->{$option});

			if ($flush)
				$this->store();
		}

		function store()
		{
			$this->_logger->entrance();

			$option_name = $this->_get_option_manager_name();

			// Update DB.
			update_option($option_name, json_encode($this->_options));

			wp_cache_set($option_name, $this->_options, WP_FS__SLUG);
		}
	}