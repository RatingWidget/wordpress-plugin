<?php
	/**
	 * @package     Freemius
	 * @copyright   Copyright (c) 2015, Freemius, Inc.
	 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
	 * @since       1.0.7
	 */
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}


	class Freemius extends Freemius_Abstract {
		/**
		 * Check if plugin using the free plan.
		 *
		 * @since 1.0.4
		 *
		 * @return bool
		 */
		function is_free_plan() {
			return false;
		}

		/**
		 * Check if the user has an activated and valid paid license on current plugin's install.
		 *
		 * @since 1.0.4
		 *
		 * @return bool
		 */
		function is_paying__fs__() {
			return true;
		}

		/**
		 * Check if the user in a trial.
		 *
		 * @since 1.0.3
		 *
		 * @return bool
		 */
		function is_trial() {
			return false;
		}

		/**
		 * @since  1.0.2
		 *
		 * @param string $plan  Plan name
		 * @param bool   $exact If true, looks for exact plan. If false, also check "higher" plans.
		 *
		 * @return bool
		 */
		function is_plan( $plan, $exact = false ) {
			if ( 'free' === $plan ) {
				return false;
			}

			return true;
		}

		/**
		 * Check if running payments in sandbox mode.
		 *
		 * @since 1.0.4
		 *
		 * @return bool
		 */
		function is_payments_sandbox() {
			return false;
		}

		/**
		 * Check if running test vs. live plugin.
		 *
		 * @since 1.0.5
		 *
		 * @return bool
		 */
		function is_live() {
			return true;
		}

		/**
		 * Check if running premium plugin code.
		 *
		 * @since 1.0.5
		 *
		 * @return bool
		 */
		function is_premium() {
			return true;
		}

		/**
		 * Check if plugin must be wordpress.org compliant.
		 *
		 * @since 1.0.7
		 *
		 * @return bool
		 */
		function is_org_repo_compliant() {
			return false;
		}

		/**
		 * Check if plugin is allowed to install executable files.
		 *
		 * @author Vova Feldman (@svovaf)
		 * @since  1.0.5
		 *
		 * @return bool
		 */
		function is_allowed_to_install() {
			return ( $this->is_premium() || ! $this->is_org_repo_compliant() );
		}

		/**
		 * Check if the user skipped connecting the account with Freemius.
		 *
		 * @since 1.0.7
		 *
		 * @return bool
		 */
		function is_anonymous() {
			return false;
		}

		/**
		 * Check if user registered with Freemius by connecting his account.
		 *
		 * @since 1.0.1
		 * @return bool
		 */
		function is_registered() {
			return true;
		}
	}