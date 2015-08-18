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
		 * @var array 
		 */
		private $_workflows;
		
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
			while ( $i < count($bt) - 1 && false !== strpos( $bt[ $i ]['file'], DIRECTORY_SEPARATOR . 'workflow' . DIRECTORY_SEPARATOR ) ) {
				$i++;
			}

			$this->_plugin_main_file_path = $bt[ $i ]['file'];
			$this->_plugin_dir_path       = plugin_dir_path( $this->_plugin_main_file_path );
			$this->_plugin_basename       = plugin_basename( $this->_plugin_main_file_path );
			$this->_plugin_data           = get_plugin_data( $this->_plugin_main_file_path );

			$base_name_split = explode( '/', $this->_plugin_basename );
			$this->_plugin_dir_name = $base_name_split[0];

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
			$workflows = self::$_options->get_option( 'workflows' );
			if ( ! is_array( $workflows ) ) {
				$workflows = array();
			}
			
			$workflows_id_order = self::$_options->get_option( 'workflows_id_order' );
			if ( ! is_array( $workflows_id_order ) ) {
				$workflows_id_order = array();
			}

			$this->_workflows = $workflows;
			$this->_workflows_id_order = $workflows_id_order;
		}

		function init( $options = array() ) {
			$this->get_plugin_version();

			if ( is_admin() ) {
				if ( isset( $options['menu'] ) ) { // Plugin has menu.
					$this->set_has_menu();
				}

				$this->_init_admin();
			}
		}

		private function _init_admin() {
			// Create sub menu items under the RatingWidget dashboard menu item.
			add_filter( 'ratingwidget_dashboard_submenus', array( &$this, '_add_dashboard_menu' ) );
			
			// AJAX request handlers
			add_action( 'wp_ajax_create-workflow', array( &$this, 'create_workflow' ) );
			add_action( 'wp_ajax_update-workflow', array( &$this, 'update_workflow' ) );
			add_action( 'wp_ajax_delete-workflow', array( &$this, 'delete_workflow' ) );
			add_action( 'wp_ajax_update-workflows-id-order', array( &$this, 'update_workflows_id_order' ) );
		}

		/**
		 * Retrieves an array of workflows.
		 * 
		 * @return array
		 */
		function get_workflows() {
			return $this->_workflows;
		}
		
		/**
		 * Retrieves an array of workflow IDs. The workflows list is sorted based on the order these IDs.
		 * 
		 * @return array
		 */
		function get_workflow_ids() {
			return $this->_workflows_id_order;
		}
		
		function get_operators() {
			return array(
				'is'	=> array(
					'title' => __( 'is', $this->_slug ),
					'min' => 40,
					'max' => 70
				),
				'isnot' => array(
					'title' => __( 'is not', $this->_slug ),
					'min' => 40,
					'max' => 70
				),
//						'=' => __( 'is equal to', $this->_slug ),
//						'!=' => __( 'is not equal to', $this->_slug ),
//						'<' => __( 'is less than', $this->_slug ),
//						'<=' => __( 'is less than or equal to', $this->_slug ),
//						'>' => __( 'is greater than', $this->_slug ),
//						'>=' => __( 'is greater than or equal to', $this->_slug )
			);
		}
		
		function get_operand_types() {
			$_post_types = get_post_types();
			$post_types = array();
			
			foreach ( $_post_types as $_post_type => $_post_type_title ) {
				$post_types[ $_post_type ] = array(
					'ID' => $_post_type,
					'value' => $_post_type_title
				);
			}
			
			
			$_categories = get_categories();
			$categories = array();
			
			foreach ( $_categories as $_category ) {
				$categories[ $_category->cat_ID ] = array(
					'ID' => $_category->cat_ID,
					'value' => $_category->name
				);
			}
			
			return array(
				40 => array(
					'title' => __( 'Category', $this->_slug ),
					'slug'	=> 'categories',
					'value' => $categories
				),
				41 => array(
					'title' => __( 'Item Type', $this->_slug ),
					'slug' => 'post_types',
					'value' => $post_types
				),
//						70 => __( 'Average Rate', $this->_slug ),
//						71 => __( 'Votes Count', $this->_slug ),
//						72 => __( 'Current Star Vote', $this->_slug ),
//						10 => __( 'Current Thumbs Vote', $this->_slug ),
//						42 => __( 'User', $this->_slug )
			);
		}
		
		function print_site_script() {
			wf_require_once_template( 'workflows_site_script.php' );
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
				'name'		=> $name,
				'conditions' => array(),
				'actions'	=> array(),
				'eventTypes'	=> array()
			);
			
			$this->_workflows[ $id ] = $workflow;
			$this->_workflows_id_order[] = $id;
			
			self::$_options->set_option( 'workflows', $this->_workflows, true );
			self::$_options->set_option( 'workflows_id_order', $this->_workflows_id_order, true );
			
			$message = array(
				'success' => 1,
				'data'    => array(
					'id' => $id,
					'workflow' => $workflow
				)
			);

			echo json_encode( $message );
			
			exit;
		}
		
		/**
		 * Updates the information of a specific of workflow.
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
			
			$workflow = $this->_workflows[ $id ];
			
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
			
			if ( $update ) {
				self::$_options->set_option( 'workflows', $this->_workflows, true );
			}
			
			$message = array(
				'success' => 1,
				'data'    => array(
					'id' => $id,
					'workflow' => $workflow
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
			
			$error_message = array(
				'success' => 0,
				'errors'  => array(
					'Invalid ID'
				)
			);

			// Validate the workflow's ID.
			if ( empty( $id ) ) {
				echo json_encode( $error_message );
				exit;
			}
			
			if ( ! isset( $this->_workflows[ $id ] ) ) {
				echo json_encode( $error_message );
				exit;
			}
			
			unset( $this->_workflows[ $id ] );
			$idx = array_search( $id, $this->_workflows_id_order );
			if ( false !== $idx ) {
				unset( $this->_workflows_id_order[ $idx ] );
				self::$_options->set_option( 'workflows_id_order', $this->_workflows_id_order, true );
			}
			
			self::$_options->set_option( 'workflows', $this->_workflows, true );
			
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
			if (!isset($this->_menu_items[$priority]))
				$this->_menu_items[$priority] = array();

			$this->_menu_items[$priority][] = array(
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
			wp_enqueue_script( 'jquery-ui-sortable' );
			wf_enqueue_local_script( 'bootstrap-script', 'bootstrap.min.js' );
			wf_enqueue_local_script( 'workflows-script', 'workflow.js' );
			wf_enqueue_local_style( 'workflows-style', 'workflow.css' );
		}
		
		function _admin_footer() {
			$data = array(
				'workflows' => $this->get_workflows(),
				'workflows_id_order' => ($this->get_workflow_ids()),
				'text' => array(
					'no_workflows' => __( 'No workflows to load', $this->_slug ),
					'has_workflows' => __( 'All workflows, processed from top to bottom', $this->_slug )
				),
				'operators' => $this->get_operators(),
				'operandTypes' => $this->get_operand_types(),
				'actions' => apply_filters( 'rw_wf_actions', array() ),
				'eventTypes' => array(
					'afterVote' => array(
						'ID' => 'afterVote',
						'value' => __( 'After Vote', $this->_slug )
					),
					'beforeVote' => array(
						'ID' => 'beforeVote',
						'value' => __( 'Before Vote', $this->_slug )
					)
				)
			);
			
			echo '<script>WORKFLOWS_SETTINGS = ' . json_encode( $data ) . '</script>';
		}
		
		function _workflows_page_render() {
			$vars = array( 'slug' => $this->_slug );
			wf_require_once_template( 'workflows.php', $vars );
		}
	}