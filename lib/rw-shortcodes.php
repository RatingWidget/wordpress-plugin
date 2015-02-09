<?php
	if ( ! defined( 'ABSPATH' ) ) exit;

	/* Ratings PHP Shortcodes.
	--------------------------------------------------------------------------------------------*/
	function rw_get_post_rating( $postID = false, $class = 'blog-post', $schema = false ) {
		$postID = ( false === $postID ) ? get_the_ID() : $postID;

		return ratingwidget()->EmbedRatingByPost( get_post( $postID ), $class, $schema );
	}

	function rw_the_post_rating( $postID = false, $class = 'blog-post', $schema = false ) {
		echo rw_get_post_rating( $postID, $class, $schema );
	}

	function rw_get_rating( $urid, $title = '', $permalink = '', $class = 'blog-post', $schema = false ) {
		return ratingwidget()->EmbedRawRating($urid, $title, $permalink, $class, $schema);
	}

	function rw_the_rating( $urid, $title = '', $permalink = '', $class = 'blog-post', $schema = false ) {
		echo rw_get_rating( $urid, $title, $permalink, $class, $schema );
	}

	/**
	 * Return rating metadata.
	 *
	 * @param mixed $postID Post id. Defaults to current loop post id.
	 * @param mixed $accuracy The number of digits after floating point.
	 */
	function rw_get_post_rating_data( $postID = false, $accuracy = false ) {
		$rwp = ratingwidget();

		$postID = ( false === $postID ) ? get_the_ID() : $postID;

		return $rwp->GetRatingDataByRatingID( $rwp->_getPostRatingGuid( $postID ), $accuracy );
	}

	function rw_get_user_rating( $userID = false ) {
		$userID = ( false === $userID ) ? get_current_user_id() : $userID;

		return ratingwidget()->EmbedRatingByUser( get_user_by( 'id', $userID ) );
	}

	function rw_the_user_rating( $userID = false ) {
		echo rw_get_user_rating( $userID );
	}

	/* General rating shortcode.
	--------------------------------------------------------------------------------------------*/
	function rw_the_rating_shortcode( $atts ) {
		RWLogger::LogEnterence( 'rw_the_rating_shortcode' );

		if ( RWLogger::IsOn() ) {
			RWLogger::Log( 'rw_the_rating_shortcode', var_export( $atts, true ) );
		}

		extract( shortcode_atts( array(
			'id'       => 1,
			'title'      => '',
			'permalink'  => '',
			'type'       => 'blog-post',
			'add_schema' => false,
		), $atts ) );

		if ( is_string( $add_schema ) ) {
			$add_schema = ( 'true' === strtolower( $add_schema ) );
		}

		return rw_get_rating( $id, $title, $permalink, $type, $add_schema );
	}
	
	/**
	 * Top-rated shortcode
	 * 
	 * @author Leo Fajardo (@leorw)
	 * @since 2.4.1
	 * @param array $atts
	 * @return string
	 */
	function rw_toprated_shortcode($atts) {
		RWLogger::LogEnterence( 'rw_toprated_shortcode' );

		if ( RWLogger::IsOn() ) {
			RWLogger::Log( 'rw_toprated_shortcode', var_export($atts, true) );
		}

		$atts = shortcode_atts(array(
			'type'       => 'posts',
			'direction'  => 'ltr',
			'max_items'  => '5',
			'min_votes'  => '1',
			'order'      => 'DESC',
			'order_by'   => 'avgrate',
			'created_in' => 'all_time'
		), $atts);
		
		return ratingwidget()->get_toprated_from_shortcode($atts);
	}

	/* Post inline Shortcodes.
	--------------------------------------------------------------------------------------------*/
	function rw_the_post_shortcode( $atts ) {
		RWLogger::LogEnterence( 'rw_the_post_shortcode' );

		if ( RWLogger::IsOn() ) {
			RWLogger::Log( 'rw_the_post_shortcode', var_export( $atts, true ) );
		}

		extract( shortcode_atts( array(
			'post_id'    => 1,
			'type'       => 'blog-post',
			'add_schema' => false,
		), $atts ) );

		if (is_string($add_schema))
			$add_schema = ('true' === strtolower($add_schema));

		return rw_get_post_rating( $post_id, $type, $add_schema );
	}