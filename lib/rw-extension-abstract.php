<?php
	if (!defined('ABSPATH')) exit;

	if (class_exists('RatingWidgetPlugin') && !class_exists('RW_AbstractExtension')) :

		abstract class RW_AbstractExtension {
			/**
			 * @return string
			 */
			abstract function GetSlug();

			/**
			 * @return boolean
			 */
			abstract function HasSettingsMenu();

			/**
			 * @return array
			 */
			abstract function GetSettingsMenuItem();

			/**
			 * @return array
			 */
			abstract function GetSettings();

			/**
			 * @return array
			 */
			abstract function GetRatingClasses();

			/**
			 * @return array
			 */
			abstract function GetDefaultOptions();

			/**
			 * @return array
			 */
			abstract function GetDefaultAlign();

			/**
			 * @param $class string
			 *
			 * @return string
			 */
			abstract function GetAlignOptionNameByClass($class);

			/**
			 * If true, page/post/comment ratings would be disabled on current page.
			 *
			 * @return boolean
			 */
			abstract function BlockLoopRatings();

			/**
			 * Check if the extension supports ratings for current page.
			 *
			 * @return boolean
			 */
			abstract function IsExtensionPage();

			/**
			 * Return the rating class of the current's page.
			 *
			 * @return string
			 */
			abstract function GetCurrentPageClass();

			abstract function Hook($rclass);

			/**
			 * Retrieve unique global rating ID for the specific element.
			 *
			 * @param $element_id int
			 * @param $rclass string
			 *
			 * @return mixed
			 */
			abstract function GetRatingGuid($element_id, $rclass);

			/**
			 * @return array
			 */
			abstract function GetTopRatedInfo();

			/**
			 * @param $type string
			 * @param $rating object
			 *
			 * @return array of string
			 */
			abstract function GetElementInfoByRating($type, $rating);
		}

	endif;