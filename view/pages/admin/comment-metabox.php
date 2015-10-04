<?php
	/**
	 * The content of the comment rating metabox. Loaded by ratingwidget()->show_comment_rating_metabox() method.
	 */

	global $comment, $rwp;

	RWLogger::Log( 'show_comment_rating_metabox' );

	add_action( 'admin_footer', array( &$rwp, 'rw_attach_rating_js' ), 5 );

	$rclass = 'comment';

	$multirating_options = $rwp->get_multirating_options_by_class( $rclass );

	$multi_criterion = count( $multirating_options->criteria ) > 1;

	$options = $rwp->get_options_by_class( $rclass );

	$default_hide_recommendations = isset( $options->hideRecommendations ) ? $options->hideRecommendations : false;
?>
<p>
	<input type="hidden" name="rw_comment_meta_box_nonce"
	       value="<?php echo wp_create_nonce( basename( WP_RW__PLUGIN_FILE_FULL ) ); ?>"/>
<table class="rw-rating-table rw-comment-admin-rating rw-left">
	<?php
		$urid_summary = $rwp->get_rating_id_by_element( $comment->comment_ID, $rclass, false );

		$criterion_id = 1;
		foreach ( $multirating_options->criteria as $criterion_key => $criterion ) {
			$criterion_rclass = $rclass;
			if ( $multi_criterion ) {
				$criterion_rclass .= '-criteria-' . $criterion_id;
			}

			$urid = $rwp->get_rating_id_by_element( $comment->comment_ID, $rclass, $multi_criterion ? $criterion_id ++ : false );
			$rwp->QueueRatingData( $urid, '', '', $criterion_rclass );
			?>
			<tr>
				<td>
					<div>
						<nobr><?php echo ( isset( $criterion['label'] ) && ! empty( $criterion['label'] ) ) ? $criterion['label'] : ''; ?></nobr>
					</div>
					<div
						class="rw-ui-container rw-class-<?php echo $criterion_rclass ?>" <?php echo $multi_criterion ? "data-uarid=\"$urid_summary\"" : ''; ?> <?php echo ( $multi_criterion || $default_hide_recommendations ) ? ' data-hide-recommendations="true" ' : ''; ?>
						data-urid="<?php echo $urid; ?>" data-sync="false"></div>
					<p></p>
				</td>
			</tr>
		<?php
		}

		if ( $multirating_options->show_summary_rating && $multi_criterion ) {
			$rwp->QueueRatingData( $urid_summary, '', '', $rclass );
			?>
			<tr>
				<td>
					<div>
						<nobr><?php echo ( isset( $multirating_options->summary_label ) && ! empty( $multirating_options->summary_label ) ) ? $multirating_options->summary_label : ''; ?></nobr>
					</div>
					<div
						class="rw-ui-container rw-class-<?php echo $rclass ?>" <?php echo $default_hide_recommendations ? ' data-hide-recommendations="true" ' : ''; ?>
						data-urid="<?php echo $urid_summary; ?>" data-read-only="true" data-force-sync="true"></div>
					<p></p>
				</td>
			</tr>
		<?php
		}
	?>
</table>
<label for="rw_include_comment_rating"><input type="checkbox" name="rw_include_comment_rating"
                                              id="rw_include_comment_rating"
                                              value="1"<?php checked( true, $rwp->rw_validate_visibility( $comment->comment_ID, 'comment' ) ); ?> /><?php _erw( 'show' ) ?>
	(<?php _erw( 'uncheck-to-hide' ) ?>)
</label>
<?php
	// Do not show this option when the comment ratings mode is "Admin ratings only" since in that mode the ratings should be read-only.
	if ( ! $rwp->is_comment_admin_ratings_mode() ) {
		?>
		<br/>
		<label for="rw_readonly_comment_rating"><input type="checkbox" name="rw_readonly_comment_rating"
		                                               id="rw_readonly_comment_rating"
		                                               value="1"<?php checked( true, ! $rwp->is_rating_readonly( $comment->comment_ID, 'comment' ) ); ?> /><?php _erw( 'active' ) ?> (<?php _erw( 'uncheck-to-readonly' ) ?>)
		</label>
	<?php } ?>
</p>