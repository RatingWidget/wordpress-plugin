<?php
	/**
	 * @package     Freemius
	 * @copyright   Copyright (c) 2015, Freemius, Inc.
	 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
	 * @since       1.0.6
	 */

	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	abstract class FS_Abstract_Manager {
		/**
		 * @var string
		 */
		protected $_plugin_identifier;
		/**
		 * @var FS_Entity[]
		 */
		protected $_plugin_entities;
		/**
		 * @var FS_Logger
		 */
		protected  $_logger;
		/**
		 * @var Freemius
		 */
		protected  $_fs;

		protected function __construct($plugin_identifier, $fs = null) {
			$this->_logger = FS_Logger::get_logger( WP_FS__SLUG . '_' . $plugin_identifier . '_' . $this->entry_id(), WP_FS__DEBUG_SDK, WP_FS__ECHO_DEBUG_SDK );

			$this->_fs = $fs;
			$this->_plugin_identifier = $plugin_identifier;
			$this->load();
		}

		abstract function entry_id();

		protected function get_option_manager()
		{
			return FS_Option_Manager::get_manager( WP_FS__ACCOUNTS_OPTION_NAME, true );
		}

		protected function get_all_entities()
		{
			return $this->get_option_manager()->get_option( $this->entry_id(get_called_class()), array() );
		}

		function load() {
			$all_entities           = $this->get_all_entities();
			$this->_plugin_entities = isset( $all_entities[ $this->_plugin_identifier ] ) ?
				$all_entities[ $this->_plugin_identifier ] :
				array();
		}

		function store($secondary_id = false, $entities = false, $flush = true) {
			$all_entities = $this->get_all_entities();

			if (false !== $entities)
				$this->_plugin_entities = $entities;

			if ( false === $secondary_id ) {
				$all_entities[ $this->_plugin_identifier ] = $this->_plugin_entities;
			} else {
				$all_entities[ $this->_plugin_identifier ][ $secondary_id ] = $this->_plugin_entities;
			}

			$options_manager = $this->get_option_manager();
			$options_manager->set_option( $this->entry_id(), $all_entities, $flush );
		}

		/**
		 * @param \FS_Entity $entity
		 * @param bool       $store
		 */
		function set(FS_Entity $entity, $store = false)
		{
			$this->_plugin_entities[ $entity->id ] = $entity;

			if ( $store ) {
				$this->store();
			}
		}

		/**
		 * @param $id
		 *
		 * @return bool|\FS_Entity
		 */
		function get($id) {
			return isset( $this->_plugin_entities[ $id ] ) ?
				$this->_plugin_entities[ $id ] :
				false;
		}
	}