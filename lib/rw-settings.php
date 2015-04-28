<?php
	if ( ! defined( 'ABSPATH' ) ) exit;

	if ( ! class_exists( 'RatingWidgetPlugin_Settings' ) ) :

		class RatingWidgetPlugin_Settings {
			public $options;
			public $visibility;
			public $availability;
			public $categories;
			public $show_on_excerpt;
			public $show_on_archive;
			public $show_on_category;
			public $show_on_search;
			public $custom_settings_enabled;
			public $custom_settings;
			public $languages;
			public $language_str;

			public $form_hidden_field_name;

			public $flash_dependency;
			public $show_on_mobile;
			public $identify_by;

			public $rating_type;

			public $is_user_accumulated;

			private $_saveMode = false;

			public function SetSaveMode() {
				$this->_saveMode = true;
			}

			public function IsSaveMode() {
				return $this->_saveMode;
			}
		}

		/**
		 * put your comment there...
		 *
		 * @return RatingWidgetPlugin_Settings
		 */
		function rw_settings() {
			return ratingwidget()->settings;
		}

		function rw_settings_rating_type() {
			return rw_settings()->rating_type;
		}

		function rw_options() {
			return rw_settings()->options;
		}

	endif;