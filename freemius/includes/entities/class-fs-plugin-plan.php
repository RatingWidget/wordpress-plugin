<?php
	/**
	 * @package     Freemius
	 * @copyright   Copyright (c) 2015, Freemius, Inc.
	 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
	 * @since       1.0.5
	 */

	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	class FS_Plugin_Plan extends FS_Entity {
		public $title;
		public $name;
		public $trial_period;
		public $is_require_subscription;

		/**
		 * @param stdClass|bool $plan
		 */
		function __construct( $plan = false )
		{
			if ( ! ( $plan instanceof stdClass ) ) {
				return;
			}

			parent::__construct( $plan );

			$this->title                   = $plan->title;
			$this->name                    = strtolower( $plan->name );
			$this->trial_period            = $plan->trial_period;
			$this->is_require_subscription = $plan->is_require_subscription;
		}

		static function get_type()
		{
			return 'plan';
		}

		/**
		 * @author Vova Feldman (@svovaf)
		 * @since  1.0.9
		 *
		 * @return bool
		 */
		function is_free()
		{
			return ('free' === $this->name);
		}

		/**
		 * @author Vova Feldman (@svovaf)
		 * @since  1.0.9
		 *
		 * @return bool
		 */
		function has_trial()
		{
			return ! $this->is_free() &&
			       is_numeric( $this->trial_period ) && ( $this->trial_period > 0 );
		}
	}