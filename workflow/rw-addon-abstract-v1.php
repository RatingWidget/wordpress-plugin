<?php
    /**
     * @package     RatingWidget
     * @copyright   Copyright (c) 2015, Rating-Widget, Inc.
     * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
     * @since       1.0.0
     */

    if ( ! defined( 'ABSPATH' ) ) {
        exit;
    }

    if ( ! class_exists( 'RW_AddOn_Abstract_v1' ) ) {
        abstract class RW_AddOn_Abstract_v1 {

            /**
             * Registers the needed hooks and filters.
             * 
             * @author Leo Fajardo (@leorw)
             * @since 1.0.0
             */
            function _add_hooks() {
                add_filter( 'rw_wf_addons_settings_tab', array( &$this, '_add_addon_settings_tab' ) );
                add_filter( 'rw_wf_actions', array( &$this, '_add_workflow_action' ) );
                add_action( 'init_workflow_action', array( &$this, '_add_workflow_action_code' ) );
            }

            abstract function _add_addon_settings_tab( $settings_tabs );

            abstract function _add_workflow_action( $actions );

            abstract function _add_workflow_action_code( $action_id );
        }
    }