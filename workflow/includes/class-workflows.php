<?php
	final class Workflows {
		/**
		 * @var string
		 */
		public $version = '0.0.1';

		private $_slug;
		private $_plugin_basename;
		private $_plugin_dir_path;
		private $_plugin_dir_name;
		private $_plugin_main_file_path;
		private $_plugin_data;

		private static $_instances = array();
		
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
		private static $_options;
		
		private function __construct( $slug ) {
			$this->_slug = $slug;

			$bt = debug_backtrace();
			$i  = 1;
			while ( $i < count( $bt ) - 1 && false !== strpos( $bt[ $i ]['file'], DIRECTORY_SEPARATOR . 'workflow' . DIRECTORY_SEPARATOR ) ) {
				$i++;
			}

			$this->_plugin_main_file_path = $bt[ $i ]['file'];
			$this->_plugin_dir_path       = plugin_dir_path( $this->_plugin_main_file_path );
			$this->_plugin_basename       = plugin_basename( $this->_plugin_main_file_path );
			$this->_plugin_data           = get_plugin_data( $this->_plugin_main_file_path );

			$base_name_split = explode( '/', $this->_plugin_basename );
			$this->_plugin_dir_name = $base_name_split[0];
			
			$this->_active_actions = array();
			
			$this->_load_options();
		}

		static function instance( $slug ) {
			$slug = strtolower( $slug );

			if ( ! isset( self::$_instances[ $slug ] ) ) {
				if ( 0 === count( self::$_instances ) ) {
					self::_load_required_static();
				}

				self::$_instances[ $slug ] = new Workflows( $slug );
			}

			return self::$_instances[ $slug ];
		}

		/**
		 * @param $plugin_file
		 *
		 * @return bool|Workflows
		 */
		static function load_instance_by_file( $plugin_file ) {
			$sites = self::$_options->get_option( 'options' );

			return isset( $sites[ $plugin_file ] ) ? self::instance( $sites[ $plugin_file ]->slug ) : false;
		}

		private static $_statics_loaded = false;
		private static function _load_required_static() {
			if (self::$_statics_loaded)
				return;

			self::$_options = Workflows_Option_Manager::get_manager( WP_WF__OPTION_NAME, true );

			self::$_statics_loaded = true;
		}

		/**
		 * Loads options.
		 */
		private function _load_options() {
			// Load workflows
			$workflows = self::$_options->get_option( 'workflows' );
			if ( ! is_object( $workflows ) ) {
				$workflows = new stdClass();
			}
			
			// Load sorted workflows' IDs
			$workflows_id_order = self::$_options->get_option( 'workflows_id_order' );
			if ( ! is_array( $workflows_id_order ) ) {
				$workflows_id_order = array();
			}
			
			// Load add-ons
			$addons_settings = self::$_options->get_option( 'addons' );
			if ( ! is_object( $addons_settings ) ) {
				$addons_settings = new stdClass();
			}
			
			$this->_workflows = $workflows;
			$this->_workflows_id_order = $workflows_id_order;
			$this->_addons_settings = $addons_settings;
		}

		function init( $options = array() ) {
			$this->get_plugin_version();

			if ( is_admin() ) {
				if ( isset( $options['menu'] ) ) { // Plugin has menu.
					$this->set_has_menu();
				}

				$this->_init_admin();
			} else {
				$this->_init_site();
			}
		}

		private function _init_admin() {
			// Create sub menu items under the RatingWidget dashboard menu item.
			add_filter( 'ratingwidget_dashboard_submenus', array( &$this, '_add_dashboard_menu' ) );
			
			// AJAX request handlers
			add_action( 'wp_ajax_create-workflow', array( &$this, 'create_workflow' ) );
			add_action( 'wp_ajax_update-workflow', array( &$this, 'update_workflow' ) );
			add_action( 'wp_ajax_update-workflows-id-order', array( &$this, 'update_workflows_id_order' ) );
			add_action( 'wp_ajax_delete-workflow', array( &$this, 'delete_workflow' ) );
		}
		
		private function _init_site() {
			add_action( 'wp_enqueue_scripts', array( &$this, 'add_site_scripts' ) );
		}

		function add_site_scripts() {
			wf_enqueue_local_style( 'workflows-site-style', 'workflow-site.css' );
		}
		
		/**
		 * Retrieves all workflows.
		 * 
		 * @return object
		 */
		function get_workflows() {
			return $this->_workflows;
		}
		
		/**
		 * Retrieves all add-ons' settings.
		 * 
		 * @return object
		 */
		function get_addons_settings() {
			return is_object( $this->_addons_settings ) ? $this->_addons_settings : false;
		}
		
		/**
		 * Retrieves the settings of the specified add-on.
		 * 
		 * @param type $addon The id of the target add-on.
		 * 
		 * @return boolean|object
		 */
		function get_single_addon_settings( $addon ) {
			if ( ! isset( $this->_addons_settings->{ $addon } ) ) {
				return false;
			}
			
			if ( ! is_object( $this->_addons_settings->{ $addon } ) ) {
				return false;
			}
			
			return $this->_addons_settings->{ $addon };
		}
		
		/**
		 * Retrieves the active workflows in the order specified by the user.
		 * Reordering of the workflows can be done in the workflows list page via drag and drop method.
		 */
		function get_active_workflows() {
			$active_workflows = array();
			
			foreach ( $this->_workflows_id_order as $workflow_id ) {
				if ( isset( $this->_workflows->{ $workflow_id } ) ) {
					$workflow = $this->_workflows->{ $workflow_id };
					
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
		 * @return array
		 */
		function get_active_actions() {
			return $this->_active_actions;
		}
		
		/**
		 * Retrieves an array of workflow IDs. The visual workflows list is sorted based on the order these IDs.
		 * 
		 * @return array
		 */
		function get_workflow_ids() {
			return $this->_workflows_id_order;
		}
		
		/**
		 * Returns the default supported operators.
		 * 
		 * @return array
		 */
		function get_operators() {
			return array(
				'boolean'	=> array(
					'is'	=> array( 'title' => __( 'is', $this->_slug ) )
				),
				'string' => array(
					'is'	=> array( 'title' => __( 'is', $this->_slug ) ),
					'isNot' => array( 'title' => __( 'is not', $this->_slug ) )
				),
				'number' => array(
					'isEqualTo'					=> array( 'title' => __( 'is equal to', $this->_slug ) ),
					'isLessThan'				=> array( 'title' => __( 'is less than', $this->_slug ) ),
					'isLessThanOrEqualTo'		=> array( 'title' => __( 'is less than or equal to', $this->_slug ) ),
					'isGreaterThan'				=> array( 'title' => __( 'is greater than', $this->_slug ) ),
					'isGreaterThanOrEqualTo'	=> array( 'title' => __( 'is greater than or equal to', $this->_slug ) )
				)
			);
		}
		
		/**
		 * Returns an array of variable types.
		 * 
		 * @return array
		 */
		function get_variable_types() {
			$_post_types = get_post_types();
			$post_types = array();
			foreach ( $_post_types as $_post_type => $_post_type_title ) {
				$post_types[ $_post_type ] = array( 'title' => $_post_type_title );
			}
			
			$_categories = get_categories();
			$categories = array();
			foreach ( $_categories as $_category ) {
				$categories[ $_category->cat_ID ] = array( 'title' => $_category->name );
			}
			
			
			$rating_types = array(
				'blog-post'				=> array( 'title' => __( 'Blog Post', $this->_slug ) ),
				'front-post'			=> array( 'title' => __( 'Front Post', $this->_slug ) ),
				'page'					=> array( 'title' => __( 'Page', $this->_slug ) ),
				'product'				=> array( 'title' => __( 'Product', $this->_slug ) ),
				'collection-product'	=> array( 'title' => __( 'Collection Product', $this->_slug ) ),
				'comment'				=> array( 'title' => __( 'Comment', $this->_slug ) )
			);
			
			return array(
				'category'		=> array(
					'title'			=> __( 'Category', $this->_slug ),
					'dataType'		=> 'string',
					'field'				=> array(
						'type'			=> 'dropdown'
					),
					'values'		=> $categories
				),
				'post-type'		=> array(
					'title'			=> __( 'Post Type', $this->_slug ),
					'dataType'		=> 'string',
					'field'				=> array(
						'type'			=> 'dropdown'
					),
					'values'		=> $post_types
				),
				'rating-type'	=> array(
					'title'			=> __( 'Rating Type', $this->_slug ),
					'dataType'		=> 'string',
					'field'				=> array(
						'type'			=> 'dropdown'
					),
					'values'		=> $rating_types
				),
				'average-rate'	=> array(
					'title'				=> __( 'Average Rate', $this->_slug ),
					'dataType'			=> 'number',
					'field'				=> array(
						'type'			=> 'textfield',
						'placeholder'	=> __( 'e.g. 4.3', $this->_slug )
					),
					'values'			=> 'false'
				),
				'votes-count'	=> array(
					'title'			=> __( 'Votes Count', $this->_slug ),
					'dataType'		=> 'number',
					'field'				=> array(
						'type'			=> 'textfield',
						'placeholder'	=> __( 'e.g. 10', $this->_slug )
					),
					'values'		=> 'false'
				),
				'star-vote'		=> array(
					'title'			=> __( 'Current Star Vote', $this->_slug ),
					'dataType'		=> 'number',
					'field'				=> array(
						'type'			=> 'dropdown'
					),
					'values'	=> array(
						'1'			=> array( 'title' => 1 ),
						'2'			=> array( 'title' => 2 ),
						'3'			=> array( 'title' => 3 ),
						'4'			=> array( 'title' => 4 ),
						'5'			=> array( 'title' => 5 )
					)
				),
				'thumb-vote'		=> array(
					'title'			=> __( 'Current Thumbs Vote', $this->_slug ),
					'dataType'		=> 'string',
					'field'				=> array(
						'type'			=> 'dropdown'
					),
					'values'		=> array(
						'like'			=> array( 'title' => 'Like' ),
						'dislike'		=> array( 'title' => 'Dislike' ),
					)
				),
				'user'			=> array(
					'title'			=> __( 'User', $this->_slug ),
					'dataType'		=> 'string',
					'field'				=> array(
						'type'			=> 'dropdown'
					),
					'values'		=> array(
						'anonymous'		=> array( 'title' =>  __( 'Anonymous', $this->_slug ) ),
						'registered'	=> array( 'title' =>  __( 'Registered', $this->_slug ) )
					)
				)
			);
		}
		
		function print_site_script() {
			$vars = array( 'slug' => $this->_slug );
			wf_require_once_template( 'workflows-site-script.php', $vars );
		}

		/**
		 * Generates a new workflow ID string.
		 * 
		 * @param string $name The workflow name used in generating the workflow ID.
		 * 
		 * @return string
		 */
		private function generate_workflow_id( $name ) {
			return md5( uniqid( $name ) );
		}
	
		function get_plugin_folder_name() {
			$plugin_folder = $this->_plugin_basename;

			while ( '.' !== dirname( $plugin_folder ) ) {
				$plugin_folder = dirname( $plugin_folder );
			}

			return $plugin_folder;
		}

		function get_plugin_version() {
			if ( ! function_exists( 'get_plugins' ) ) {
				require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			}

			$plugins_data = get_plugins( '/' . $this->get_plugin_folder_name() );

			$version = $plugins_data[ basename( $this->_plugin_main_file_path ) ]['Version'];

			return $version;
		}

		/* AJAX Events
		------------------------------------------------------------------------------------------------------------------*/
		function update_workflows_id_order() {
			$ids = isset( $_POST['ids'] ) ? $_POST['ids'] : array();
			
			if ( ! empty( $ids ) ) {
				$this->_workflows_id_order = $ids;
				self::$_options->set_option( 'workflows_id_order', $this->_workflows_id_order, true );
			}
			
			echo 1;
			exit;
		}
		
		/**
		 * Creates a new workflow
		 */
		function create_workflow() {
			$name = isset( $_POST['name'] ) ? trim( stripslashes( $_POST['name'] ) ) : $_POST['name'];
			
			// Validate the name of the new workflow.
			if ( empty( $name ) ) {
				$message = array(
					'success' => 0,
					'errors'  => array(
						__( 'Invalid Workflow Name', $this->_slug )
					)
				);
				
				echo json_encode( $message );
				exit;
			}
			
			// Generate a unique ID based on the name of the new workflow.
			$id = $this->generate_workflow_id( $name );
			
			$workflow = (object) array(
				'name'			=> $name,
				'active'		=> false,
				'conditions'	=> array(),
				'actions'		=> array(),
				'eventTypes'	=> array()
			);
			
			$this->_workflows->{ $id } = $workflow;
			$this->_workflows_id_order[] = $id;
			
			self::$_options->set_option( 'workflows', $this->_workflows, true );
			self::$_options->set_option( 'workflows_id_order', $this->_workflows_id_order, true );
			
			$message = array(
				'success' => 1,
				'data'    => array(
					'id'		=> $id,
					'workflow'	=> $workflow
				)
			);

			echo json_encode( $message );
			
			exit;
		}
		
		/**
		 * Updates the information of a workflow specified by ID.
		 */
		function update_workflow() {
			// Target workflow's ID
			$id = isset( $_POST['id'] ) ? trim( $_POST['id'] ) : '';
			
			if ( empty( $id ) ) {
				$message = array(
					'success' => 0,
					'errors'  => array(
						__( 'Invalid Workflow Name', $this->_slug )
					)
				);
				
				exit;
			}
			
			// The new condition
			$conditions = isset( $_POST['conditions'] ) ? $_POST['conditions'] : false;
			$actions = isset( $_POST['actions'] ) ? $_POST['actions'] : false;
			$event_types = isset( $_POST['event_types'] ) ? $_POST['event_types'] : false;
			$name = isset( $_POST['name'] ) ? trim( stripslashes( $_POST['name'] ) ) : false;
			
			$workflow = $this->_workflows->{ $id };
			
			$update = false;
			
			if ( $conditions ) {
				$workflow->conditions = $conditions;
				$update = true;
			}
			
			if ( $actions ) {
				$workflow->actions = $actions;
				$update = true;
			}
			
			if ( $event_types ) {
				$workflow->eventTypes = $event_types;
				$update = true;
			}
			
			if ( $name ) {
				$workflow->name = $name;
				$update = true;
			}
			
			if ( isset( $_POST['active'] ) ) {
				$workflow->active = ( 'true' === $_POST['active'] ) ? true : false;
				$update = true;
			}
			
			if ( $update ) {
				self::$_options->set_option( 'workflows', $this->_workflows, true );
			}
			
			$message = array(
				'success' => 1,
				'data'	  => array(
					'id'		=> $id,
					'workflow'	=> $workflow
				)
			);

			echo json_encode( $message );
			exit;
		}
		
		/**
		 * Deletes a workflow based on the provided ID.
		 */
		function delete_workflow() {
			$id = isset( $_POST['id'] ) ? trim( $_POST['id'] ) : $_POST['id'];
			
			$update = false;
			
			if ( isset( $this->_workflows->{ $id } ) ) {
				unset( $this->_workflows->{ $id } );
				$update = true;
			}
			
			$idx = array_search( $id, $this->_workflows_id_order );
			if ( false !== $idx ) {
				unset( $this->_workflows_id_order[ $idx ] );
				self::$_options->set_option( 'workflows_id_order', $this->_workflows_id_order, true );
				$update = true;
			}
			
			if ( $update ) {
				self::$_options->set_option( 'workflows', $this->_workflows, true );
			}
			
			$message = array(
				'success' => 1,
				'data'    => array(
					'id'  => $id
				)
			);

			echo json_encode( $message );
			
			exit;
		}
		
		/* Management Dashboard Menu
		------------------------------------------------------------------------------------------------------------------*/
		private $_has_menu = false;
		private $_menu_items = array();

		function _add_dashboard_menu( $ratingwidget_submenus ) {
			// Add Ons Config submenu
			
			// Check if there is any active add ons
			$settings_tab = apply_filters( 'rw_wf_addons_settings_tab', array() );
			if ( ! empty( $settings_tab ) ) {
				$ratingwidget_submenus[] = array(
					'menu_title'	=> __( 'Add Ons Config', $this->_slug ),
					'function'		=> array( &$this, '_addons_config_page_render' ),
					'load_function' => array( &$this, '_addons_config_page_load' ),
					'slug'			=> 'addons-config'
				);
			}
			
			// Workflows submenu
			$ratingwidget_submenus[] = array(
				'menu_title'	=> __( 'Workflows', $this->_slug ),
				'function'		=> array( &$this, '_workflows_page_render' ),
				'load_function' => array( &$this, '_workflows_page_load' ),
				'slug'			=> 'workflows'
			);
			
			return $ratingwidget_submenus;
		}

		function set_has_menu() {
			$this->_has_menu = true;
		}

		private function _get_menu_slug( $slug = '' ) {
			return $this->_slug . ( empty( $slug ) ? '' : ( '-' . $slug ) );
		}

		function add_submenu_item( $menu_title, $render_function, $page_title = false, $capability = 'manage_options', $menu_slug = false, $before_render_function = false, $priority = 10  ) {
			if ( ! isset( $this->_menu_items[ $priority ] ) )
				$this->_menu_items[ $priority ] = array();

			$this->_menu_items[ $priority ][] = array(
				'page_title'             => is_string( $page_title ) ? $page_title : $menu_title,
				'menu_title'             => $menu_title,
				'capability'             => $capability,
				'menu_slug'              => $this->_get_menu_slug( is_string( $menu_slug ) ? $menu_slug : strtolower( $menu_title ) ),
				'render_function'        => $render_function,
				'before_render_function' => $before_render_function,
			);

			$this->_has_menu = true;
		}

		function _workflows_page_load() {
			add_action( 'admin_footer', array( &$this, '_admin_footer' ) );
			wf_enqueue_local_script( 'jquery-ui-sortable' );
			wf_enqueue_local_script( 'bootstrap', 'bootstrap.min.js' );
			wf_enqueue_local_script( 'workflows', 'workflow.js' );
			wf_enqueue_local_style( 'workflows', 'workflow.css' );
		}
		
		function _addons_config_page_load() {
			$this->handle_addons_settings_save();
			
			wf_enqueue_local_style( 'addons-config', 'addons-config.css' );
		}
		
		/**
		 * Checks if the add-ons settings are being saved.
		 */
		function handle_addons_settings_save() {
			if ( isset( $_POST['rw-save-addons-settings'] ) ) {
				$addon = isset( $_POST['add-on'] ) ? trim( $_POST['add-on'] ) : '';
				if ( ! empty( $addon ) ) {
					$settings = isset( $this->_addons_settings->{ $addon } ) ? $this->_addons_settings->{ $addon } : false;
					if ( false === $settings ) {
						$settings = new stdClass();
						$this->_addons_settings->{ $addon } = $settings;
					}

					if ( isset( $_POST['addon-fields'] ) ) {
						foreach ( $_POST['addon-fields'] as $field_name => $field_value ) {
							// Remove the extra slashes added by WordPress.
							$settings->{ $field_name } = stripslashes( $field_value );
						}

						self::$_options->set_option( 'addons', $this->_addons_settings, true );
					}
				}
			}
		}
		
		/**
		 * Loads the workflows settings into a JavaScript variable.
		 * These settings are used when creating, editing, and sorting workflows.
		 */
		function _admin_footer() {
			$data = array(
				'workflows'				=> $this->get_workflows(),
				'workflows_id_order'	=> $this->get_workflow_ids(),
				'text'			=> array(
					'invalid_workflow'	=> __( 'Invalid Workflow Name', $this->_slug ),
					'no_workflows'		=> __( 'No workflows to load', $this->_slug ),
					'has_workflows'		=> __( 'All workflows, processed from top to bottom', $this->_slug ),
					'and'				=> __( 'AND', $this->_slug ),
					'or'				=> __( 'OR', $this->_slug ),
					'add_and'			=> __( '+ AND', $this->_slug ),
					'add_or'			=> __( '+ OR', $this->_slug ),
					'activate'			=> __( 'Activate', $this->_slug ),
					'deactivate'		=> __( 'Deactivate', $this->_slug )
				),
				'operators'				=> $this->get_operators(),
				'variable-types'		=> $this->get_variable_types(),
				'actions'				=> apply_filters( 'rw_wf_actions', array() ),
				'event-types'	=> array(
					'afterVote'			=> array( 'title' => __( 'After Vote', $this->_slug ) ),
					'beforeVote'		=> array( 'title' => __( 'Before Vote', $this->_slug ) )
				)
			);
			
			echo '<script>var WORKFLOWS_SETTINGS = ' . json_encode( $data ) . '</script>';
		}
		
		function _workflows_page_render() {
			$vars = array( 'slug' => $this->_slug );
			wf_require_once_template( 'workflows.php', $vars );
		}
		
		function _addons_config_page_render() {
			$vars = array( 'slug' => $this->_slug );
			wf_require_once_template( 'addons-config.php', $vars );
		}
	}