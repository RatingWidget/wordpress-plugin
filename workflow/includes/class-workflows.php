<?php

	final class RW_Workflows {

		/**
		 * @var string
		 */
		public $version = '1.0.0';

		/**
		 * @var object
		 */
		private $_workflows;

		/**
		 * @var object
		 */
		private $_addons_settings;

		/**
		 * @var array
		 */
		private $_active_actions;

		/**
		 * @var array
		 */
		private $_workflows_id_order;

		/**
		 * @var Workflows_Option_Manager
		 */
		private $_options;

		/**
		 * @var RW_Workflows
		 */
		private static $_instance;

		private function __construct() {
			$this->_active_actions = array();

			$this->_load_options();
		}

		/**
		 * Returns an instance of this class.
		 *
		 * @author Leo Fajardo (@leorw)
		 * @since  1.0.0
		 *
		 * @return RW_Workflows
		 */
		static function instance() {
			if ( ! isset( self::$_instance ) ) {
				self::$_instance = new RW_Workflows();
			}

			return self::$_instance;
		}

		/**
		 * Loads the options for the workflows and add-ons.
		 *
		 * @author Leo Fajardo (@leorw)
		 * @since  1.0.0
		 */
		private function _load_options() {
			if ( ! isset( $this->_options ) ) {
				$this->_options = rw_fs_options();
			}

			// Load workflows
			$workflows = $this->_options->get_option( 'workflows' );
			if ( ! is_object( $workflows ) ) {
				$workflows = new stdClass();
			}

			// Load sorted workflows' IDs
			$workflows_id_order = $this->_options->get_option( 'workflows_id_order' );
			if ( ! is_array( $workflows_id_order ) ) {
				$workflows_id_order = array();
			}

			// Load the add-ons' settings
			$addons_settings = $this->_options->get_option( 'addons_settings' );
			if ( ! is_object( $addons_settings ) ) {
				$addons_settings = new stdClass();
			}

			$this->_workflows          = $workflows;
			$this->_workflows_id_order = $workflows_id_order;
			$this->_addons_settings    = $addons_settings;
		}

		/**
		 * Configures the admin menus and registers the hooks and filters needed for the admin area and site.
		 *
		 * @author Leo Fajardo (@leorw)
		 */
		function init() {
			if ( is_admin() && rw_fs()->has_installed_addons() ) {
				$this->_init_admin();
			} else {
				$this->_init_site();
			}
		}

		/**
		 * Registers the hooks and filters needed for the admin area.
		 *
		 * @author Leo Fajardo (@leorw)
		 * @since  1.0.0
		 */
		private function _init_admin() {
			// Create sub menu items under the RatingWidget dashboard menu item.
			add_filter( 'ratingwidget_dashboard_submenus', array( &$this, '_add_dashboard_menu' ) );

			// AJAX request handlers
			add_action( 'wp_ajax_new-workflow', array( &$this, 'new_workflow' ) );
			add_action( 'wp_ajax_update-workflow', array( &$this, 'update_workflow' ) );
			add_action( 'wp_ajax_update-workflows-id-order', array( &$this, 'update_workflows_id_order' ) );
			add_action( 'wp_ajax_delete-workflow', array( &$this, 'delete_workflow' ) );
		}

		/**
		 * This is where the scripts and styles are registered.
		 *
		 * @author Leo Fajardo (@leorw)
		 * @since  1.0.0
		 */
		private function _init_site() {
			add_action( 'wp_enqueue_scripts', array( &$this, 'add_site_scripts' ) );
		}

		/**
		 * Registers the style needed for the site.
		 *
		 * @author Leo Fajardo (@leorw)
		 * @since  1.0.0
		 */
		function add_site_scripts() {
			wf_enqueue_local_style( 'workflows-site-style', 'workflow-site.css' );
		}

		/**
		 * Retrieves all workflows.
		 *
		 * @author Leo Fajardo (@leorw)
		 * @since  1.0.0
		 *
		 * @return object
		 */
		function get_workflows() {
			return $this->_workflows;
		}

		/**
		 * Retrieves all add-ons' settings.
		 *
		 * @author Leo Fajardo (@leorw)
		 * @since  1.0.0
		 *
		 * @return object
		 */
		function get_addons_settings() {
			return is_object( $this->_addons_settings ) ? $this->_addons_settings : false;
		}

		/**
		 * Retrieves the settings for the specified add-on.
		 *
		 * @author Leo Fajardo (@leorw)
		 * @since  1.0.0
		 *
		 * @param string $addon The id of the target add-on.
		 *
		 * @return boolean|object
		 */
		function get_single_addon_settings( $addon ) {
			if ( ! isset( $this->_addons_settings->{$addon} ) ) {
				return false;
			}

			if ( ! is_object( $this->_addons_settings->{$addon} ) ) {
				return false;
			}

			return $this->_addons_settings->{$addon};
		}

		/**
		 * Updates the settings of the specified add-on.
		 *
		 * @author Leo Fajardo (@leorw)
		 * @since  1.0.0
		 *
		 * @param string $addon    The id of the target add-on.
		 * @param object $settings The new settings of the add-on.
		 */
		function update_single_addon_settings( $addon, $settings ) {
			$this->_addons_settings->{$addon} = $settings;
			$this->_options->set_option( 'addons_settings', $this->_addons_settings, true );
		}

		/**
		 * Retrieves the active workflows in the order specified by the user.
		 * Reordering the workflows can be done in the workflows list page via drag and drop method.
		 *
		 * @author Leo Fajardo (leorw)
		 * @since  1.0.0
		 *
		 * @return array
		 */
		function get_active_workflows() {
			$active_workflows = array();

			foreach ( $this->_workflows_id_order as $workflow_id ) {
				if ( isset( $this->_workflows->{$workflow_id} ) ) {
					$workflow = $this->_workflows->{$workflow_id};

					if ( isset( $workflow->active ) && ( true === $workflow->active ) ) {
						if ( ! empty( $workflow->actions ) ) {
							// Get all actions used by this workflow that are not yet added to the active actions array.
							$action_ids = array_diff( $workflow->actions, $this->_active_actions );

							// Add the actions to the active actions array.
							if ( ! empty( $action_ids ) ) {
								$this->_active_actions = array_merge( $this->_active_actions, $action_ids );
							}
						}

						$active_workflows[ $workflow_id ] = $workflow;
					}
				}
			}

			return $active_workflows;
		}

		/**
		 * Returns all actions in use by active workflows.
		 *
		 * @author Leo Fajardo (@leorw)
		 * @since  1.0.0
		 *
		 * @return array
		 */
		function get_active_actions() {
			return $this->_active_actions;
		}

		/**
		 * Retrieves an array of workflow IDs. The visual workflows list is sorted based on the order these IDs.
		 *
		 * @author Leo Fajardo (@leorw)
		 * @since  1.0.0
		 *
		 * @return array
		 */
		function get_workflow_ids() {
			return $this->_workflows_id_order;
		}

		/**
		 * Returns the default supported operators.
		 *
		 * @author Leo Fajardo (@leorw)
		 * @since  1.0.0
		 *
		 * @return array
		 */
		function get_operators() {
			return array(
				'boolean' => array(
					'is' => array( 'title' => __rw( 'is' ) )
				),
				'string'  => array(
					'is'    => array( 'title' => __rw( 'is' ) ),
					'isNot' => array( 'title' => __rw( 'is-not' ) )
				),
				'number'  => array(
					'isEqualTo'              => array( 'title' => __rw( 'is-equal-to' ) ),
					'isLessThan'             => array( 'title' => __rw( 'is-less-than' ) ),
					'isLessThanOrEqualTo'    => array( 'title' => __rw( 'is-less-than-or-equal-to' ) ),
					'isGreaterThan'          => array( 'title' => __rw( 'is-greater-than' ) ),
					'isGreaterThanOrEqualTo' => array( 'title' => __rw( 'is-greater-than-or-equal-to' ) )
				)
			);
		}

		/**
		 * Returns an array of variable types.
		 *
		 * @author Leo Fajardo (@leorw)
		 * @since  1.0.0
		 *
		 * @return array
		 */
		function get_variable_types() {
			$_post_types = get_post_types();
			$post_types  = array();
			foreach ( $_post_types as $_post_type => $_post_type_title ) {
				$post_types[ $_post_type ] = array( 'title' => $_post_type_title );
			}

			$_categories = get_categories();
			$categories  = array();
			foreach ( $_categories as $_category ) {
				$categories[ $_category->cat_ID ] = array( 'title' => $_category->name );
			}


			$rating_types = array(
				'blog-post'          => array( 'title' => __rw( 'blog-post' ) ),
				'front-post'         => array( 'title' => __rw( 'front-page-post' ) ),
				'page'               => array( 'title' => __rw( 'page' ) ),
				'product'            => array( 'title' => __rw( 'product' ) ),
				'collection-product' => array( 'title' => __rw( 'collection-product' ) ),
				'comment'            => array( 'title' => __rw( 'comment' ) )
			);

			return array(
				'category'     => array(
					'title'    => __rw( 'category' ),
					'dataType' => 'string',
					'field'    => array(
						'type' => 'dropdown'
					),
					'values'   => $categories
				),
				'post-type'    => array(
					'title'    => __rw( 'post-type' ),
					'dataType' => 'string',
					'field'    => array(
						'type' => 'dropdown'
					),
					'values'   => $post_types
				),
				'rating-type'  => array(
					'title'    => __rw( 'rating-type' ),
					'dataType' => 'string',
					'field'    => array(
						'type' => 'dropdown'
					),
					'values'   => $rating_types
				),
				'average-rate' => array(
					'title'    => __rw( 'average-rate' ),
					'dataType' => 'number',
					'field'    => array(
						'type'        => 'textfield',
						'placeholder' => sprintf( __rw( 'eg' ), '4.3' ),
					),
					'values'   => 'false'
				),
				'votes-count'  => array(
					'title'    => __rw( 'votes-count' ),
					'dataType' => 'number',
					'field'    => array(
						'type'        => 'textfield',
						'placeholder' => sprintf( __rw( 'eg' ), '10' ),
					),
					'values'   => 'false'
				),
				'star-vote'    => array(
					'title'    => __rw( 'current-star-vote' ),
					'dataType' => 'number',
					'field'    => array(
						'type' => 'dropdown'
					),
					'values'   => array(
						'1' => array( 'title' => 1 ),
						'2' => array( 'title' => 2 ),
						'3' => array( 'title' => 3 ),
						'4' => array( 'title' => 4 ),
						'5' => array( 'title' => 5 )
					)
				),
				'thumb-vote'   => array(
					'title'    => __rw( 'current-thumbs-vote' ),
					'dataType' => 'string',
					'field'    => array(
						'type' => 'dropdown'
					),
					'values'   => array(
						'like'    => array( 'title' => 'Like' ),
						'dislike' => array( 'title' => 'Dislike' ),
					)
				),
				'user'         => array(
					'title'    => __rw( 'user' ),
					'dataType' => 'string',
					'field'    => array(
						'type' => 'dropdown'
					),
					'values'   => array(
						'anonymous'  => array( 'title' => __rw( 'anonymous' ) ),
						'registered' => array( 'title' => __rw( 'registered' ) )
					)
				)
			);
		}

		/**
		 * This function is called by the RatingWidgetPlugin class' rw_attach_rating_js method.
		 *
		 * @author Leo Fajardo (@leorw)
		 * @since  1.0.0
		 */
		function print_site_script() {
			wf_require_once_template( 'workflows-site-script.php' );
		}

		/**
		 * Generates a new workflow ID string.
		 *
		 * @author Leo Fajardo (@leorw)
		 * @since  1.0.0
		 *
		 * @param string $name The workflow name used in generating the workflow ID.
		 *
		 * @return string
		 */
		private function generate_workflow_id( $name ) {
			return md5( uniqid( $name ) );
		}

		/* AJAX Events
		------------------------------------------------------------------------------------------------------------------*/

		/**
		 * @author Leo Fajardo (@leorw)
		 * @since  1.0.0
		 */
		function update_workflows_id_order() {
			$ids = isset( $_POST['ids'] ) ? $_POST['ids'] : array();

			if ( ! empty( $ids ) ) {
				$this->_workflows_id_order = $ids;
				$this->_options->set_option( 'workflows_id_order', $this->_workflows_id_order, true );
			}

			echo 1;
			exit;
		}

		/**
		 * Creates a new workflow via AJAX request.
		 *
		 * @author Leo Fajardo (@leorw)
		 * @since  1.0.0
		 */
		function new_workflow() {
			$name = isset( $_POST['name'] ) ? trim( stripslashes( $_POST['name'] ) ) : '';

			// Validate the name of the new workflow.
			if ( empty( $name ) ) {
				$message = array(
					'success' => 0,
					'errors'  => array(
						__rw( 'invalid-workflow-name' )
					)
				);

				echo json_encode( $message );
				exit;
			}

			$id = $this->insert_workflow( array( 'name' => $name ) );

			$message = array(
				'success' => 1,
				'data'    => array(
					'id'       => $id,
					'workflow' => $this->_workflows->{$id}
				)
			);

			echo json_encode( $message );

			exit;
		}

		/**
		 * Updates the details of a workflow via AJAX request.
		 *
		 * @author Leo Fajardo (@leorw)
		 * @since  1.0.0
		 */
		function update_workflow() {
			// Target workflow's ID
			$id = isset( $_POST['id'] ) ? trim( $_POST['id'] ) : '';

			if ( empty( $id ) ) {
				$message = array(
					'success' => 0,
					'errors'  => array(
						__rw( 'invalid-workflow-name' )
					)
				);
                                
                                echo json_encode( $message );
				exit;
			}

			// The new condition
			$conditions  = isset( $_POST['conditions'] ) ? $_POST['conditions'] : false;
			$actions     = isset( $_POST['actions'] ) ? $_POST['actions'] : false;
			$event_types = isset( $_POST['event_types'] ) ? $_POST['event_types'] : false;
			$name        = isset( $_POST['name'] ) ? trim( stripslashes( $_POST['name'] ) ) : false;

			$workflow = $this->_workflows->{$id};

			$update = false;

			if ( $conditions ) {
				$workflow->conditions = $conditions;
				$update               = true;
			}

			if ( $actions ) {
				$workflow->actions = $actions;
				$update            = true;
			}

			if ( $event_types ) {
				$workflow->eventTypes = $event_types;
				$update               = true;
			}

			if ( $name ) {
				$workflow->name = $name;
				$update         = true;
			}

			if ( isset( $_POST['active'] ) ) {
				$workflow->active = ( 'true' === $_POST['active'] ) ? true : false;
				$update           = true;
			}

			if ( $update ) {
				$this->_options->set_option( 'workflows', $this->_workflows, true );
			}

			$message = array(
				'success' => 1,
				'data'    => array(
					'id'       => $id,
					'workflow' => $workflow
				)
			);

			echo json_encode( $message );
			exit;
		}

		/**
		 * Deletes a workflow via AJAX request.
		 *
		 * @author Leo Fajardo (@leorw)
		 * @since  1.0.0
		 */
		function delete_workflow() {
			$id = isset( $_POST['id'] ) ? trim( $_POST['id'] ) : '';

			$this->_delete_workflow( $id );

			$message = array(
				'success' => 1,
				'data'    => array(
					'id' => $id
				)
			);

			echo json_encode( $message );

			exit;
		}

		/**
		 * Inserts an add-on's workflow into the database option.
		 *
		 * @author Leo Fajardo (@leorw)
		 * @since  1.0.0
		 *
		 * @param string $addon_slug
		 * @param array  $workflow
		 */
		function insert_addon_workflow( $addon_slug, $workflow = array() ) {
			if ( empty( $workflow ) ) {
				return;
			}

			$addon_settings = $this->get_single_addon_settings( $addon_slug );
			if ( ! is_object( $addon_settings ) ) {
				$addon_settings = new stdClass();
			}

			$workflow_id = isset( $addon_settings->workflow_id ) ? $addon_settings->workflow_id : false;

			// Only add the workflow if it has not been added before.
			if ( false === $workflow_id ) {
				$workflow_id = $this->insert_workflow( $workflow );

				// Save the workflow ID so that it will not be added again everytime the add-on is activated.
				$addon_settings->workflow_id = $workflow_id;

				// Update the add-on's settings.
				$this->_addons_settings->{$addon_slug} = $addon_settings;

				$this->_options->set_option( 'addons_settings', $this->_addons_settings, true );
			}
		}

		/**
		 * Removes an add-on's workflow from the database option.
		 *
		 * @author Leo Fajardo (@leorw)
		 * @since  1.0.0
		 *
		 * @param string $addon_slug
		 */
		function remove_addon_workflow( $addon_slug ) {
			$addon_settings = $this->get_single_addon_settings( $addon_slug );
			if ( ! is_object( $addon_settings ) ) {
				return;
			}

			if ( ! isset( $addon_settings->workflow_id ) && ! isset( $addon_settings->show_install_message ) ) {
				return;
			}

			$workflow_id = $addon_settings->workflow_id;

			$this->_delete_workflow( $workflow_id );

			unset( $addon_settings->workflow_id );
			unset( $addon_settings->show_install_message );

			$this->_options->set_option( 'addons_settings', $this->_addons_settings, true );
		}

		/**
		 * Inserts a new workflow into the database option.
		 *
		 * @author Leo Fajardo (@leorw)
		 * @since  1.0.0
		 *
		 * @param array $properties
		 *
		 * @return string
		 */
		function insert_workflow( $properties = array() ) {
			// Generate a unique ID based on the name of the new workflow.
			$id = $this->generate_workflow_id( $properties['name'] );

			$workflow = (object) array(
				'name'       => $properties['name'],
				'active'     => ( isset( $properties['active'] ) ? $properties['active'] : false ),
				'conditions' => ( isset( $properties['conditions'] ) ? $properties['conditions'] : array() ),
				'actions'    => ( isset( $properties['actions'] ) ? $properties['actions'] : array() ),
				'eventTypes' => ( isset( $properties['eventTypes'] ) ? $properties['eventTypes'] : array() )
			);

			$this->_workflows->{$id}     = $workflow;
			$this->_workflows_id_order[] = $id;

			$this->_options->set_option( 'workflows', $this->_workflows, true );
			$this->_options->set_option( 'workflows_id_order', $this->_workflows_id_order, true );

			return $id;
		}

		/**
		 * Removes the workflow specified by $id from the database option.
		 *
		 * @author Leo Fajardo (@leorw)
		 * @since  1.0.0
		 */
		function _delete_workflow( $id ) {
                        if ( empty( $id ) ) {
                            return;
                        }
                        
			$update = false;

			if ( isset( $this->_workflows->{$id} ) ) {
				unset( $this->_workflows->{$id} );
				$update = true;
			}

			$idx = array_search( $id, $this->_workflows_id_order );
			if ( false !== $idx ) {
				unset( $this->_workflows_id_order[ $idx ] );
				$this->_options->set_option( 'workflows_id_order', $this->_workflows_id_order, true );
				$update = true;
			}

			if ( $update ) {
				$this->_options->set_option( 'workflows', $this->_workflows, true );
			}
		}

		/* Management Dashboard Menu
		------------------------------------------------------------------------------------------------------------------*/

		/**
		 * Adds submenu items under the RatingWidget dashboard menu item.
		 *
		 * @author Leo Fajardo (@leorw)
		 * @since  1.0.0
		 *
		 * @param array $ratingwidget_submenus Current submenu items added by RatingWiget.
		 *
		 * @return array
		 */
		function _add_dashboard_menu( $ratingwidget_submenus ) {
			// Add-On Settings submenu
			// Check if there is any active add-on
			$settings_tab = apply_filters( 'rw_wf_addons_settings_tab', array() );
			if ( ! empty( $settings_tab ) ) {
				$ratingwidget_submenus[] = array(
					'menu_title'    => __rw( 'addon-settings' ),
					'function'      => array( &$this, '_addons_config_page_render' ),
					'load_function' => array( &$this, '_addons_config_page_load' ),
					'slug'          => 'addon-settings'
				);
			}

			// Workflows submenu
			$ratingwidget_submenus[] = array(
				'menu_title'    => __rw( 'workflows' ),
				'function'      => array( &$this, '_workflows_page_render' ),
				'load_function' => array( &$this, '_workflows_page_load' ),
				'slug'          => 'workflows'
			);

			return $ratingwidget_submenus;
		}

		/* Page load/render
		------------------------------------------------------------------------------------------------------------------*/

		/**
		 * Registers the scripts and styles for the Workflows admin page when it is loaded.
		 *
		 * @author Leo Fajardo (@leorw)
		 * @since  1.0.0
		 */
		function _workflows_page_load() {
			add_action( 'admin_footer', array( &$this, '_admin_footer' ) );
			wf_enqueue_local_script( 'jquery-ui-sortable' );
			wf_enqueue_local_script( 'workflows', 'workflow.js' );
			wf_enqueue_local_script( 'workflows-modal', 'modal.js' );
			wf_enqueue_local_style( 'workflows', 'workflow.css' );
		}

		/**
		 * Registers the style for the Add Ons Config admin page when it is loaded.
		 *
		 * @author Leo Fajardo (@leorw)
		 * @since  1.0.0
		 */
		function _addons_config_page_load() {
			$this->handle_addons_settings_save();

			wf_enqueue_local_style( 'addons-config', 'addons-config.css' );
		}

		/**
		 * Loads the content of the Workflows admin page.
		 *
		 * @author Leo Fajardo (@leorw)
		 * @since  1.0.0
		 */
		function _workflows_page_render() {
			wf_require_once_template( 'workflows.php' );
		}

		/**
		 * Loads the content of the Add Ons Config admin page.
		 *
		 * @author Leo Fajardo (@leorw)
		 * @since  1.0.0
		 */
		function _addons_config_page_render() {
			wf_require_once_template( 'addon-settings.php' );
		}

		/**
		 * Checks if the add-ons settings are being saved.
		 *
		 * @author Leo Fajardo (@leorw)
		 * @since  1.0.0
		 */
		function handle_addons_settings_save() {
			if ( isset( $_POST['rw-save-addons-settings'] ) ) {
				$addon = isset( $_POST['add-on'] ) ? trim( $_POST['add-on'] ) : '';
				if ( ! empty( $addon ) ) {
					$settings = isset( $this->_addons_settings->{$addon} ) ? $this->_addons_settings->{$addon} : false;
					if ( false === $settings ) {
						$settings                         = new stdClass();
						$this->_addons_settings->{$addon} = $settings;
					}

					if ( isset( $_POST['addon-fields'] ) ) {
						foreach ( $_POST['addon-fields'] as $field_name => $field_value ) {
							// Remove the extra slashes added by WordPress.
							$settings->{$field_name} = stripslashes( $field_value );
						}

						$this->_options->set_option( 'addons_settings', $this->_addons_settings, true );
					}
				}
			}
		}

		/**
		 * Loads the workflows settings into a JavaScript variable.
		 * These settings are used when creating, editing, and sorting workflows.
		 *
		 * @author Leo Fajardo (@leorw)
		 * @since  1.0.0
		 */
		function _admin_footer() {
			$data = array(
				'workflows'          => $this->get_workflows(),
				'workflows_id_order' => $this->get_workflow_ids(),
				'text'               => array(
					'invalid_workflow' => __rw( 'invalid-workflow-name' ),
					'no_workflows'     => __rw( 'no-workflows-to-load' ),
					'has_workflows'    => __rw( 'workflows-processed-top-to-bottom' ),
					'and'              => strtoupper( __rw( 'and' ) ),
					'or'               => strtoupper( __rw( 'or' ) ),
					'add_and'          => '+ ' . strtoupper( __rw( 'and' ) ),
					'add_or'           => '+ ' . strtoupper( __rw( 'or' ) ),
					'activate'         => __rw( 'activate' ),
					'deactivate'       => __rw( 'deactivate' ),
					'confirm_delete'   => __rw( 'workflow-delete-confirm' ),
					'delete_button'    => __rw( 'delete' ),
					'cancel_button'    => __rw( 'cancel' ),
					'close_button'     => __rw( 'close' )
				),
				'operators'          => $this->get_operators(),
				'variable-types'     => $this->get_variable_types(),
				'actions'            => apply_filters( 'rw_wf_actions', array() ),
				'event-types'        => array(
					'afterVote'  => array( 'title' => __rw( 'after-vote' ) ),
					'beforeVote' => array( 'title' => __rw( 'before-vote' ) )
				)
			);

			echo '<script>var WORKFLOWS_SETTINGS = ' . json_encode( $data ) . '</script>';
		}
	}