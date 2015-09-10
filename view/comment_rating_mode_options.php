<?php
    /**
     * Comment Ratings Mode view file called by ratingwidget()->SettingsPage() method.
     * 
     * Generates the HTML content for the Comment Ratings Mode section in WP admin dashboard > RatingWidget > Settings > Comments tab.
     */

    $comment_ratings_mode = rw_settings()->comment_ratings_mode;
?>
<div id="rw_comment_rating_mode_settings" class="has-sidebar has-right-sidebar">
    <div class="has-sidebar-content">
        <div class="postbox rw-body">
            <h3><?php _e( 'Comment Ratings Mode', WP_RW__ID ); ?></h3>
            <div class="inside rw-ui-content-container rw-no-radius">
                <div class="rw-ui-img-radio rw-ui-hor<?php if ( 'false' === $comment_ratings_mode ) { echo ' rw-selected'; } ?>">
                    <i class="rw-ui-sprite"></i> <input type="radio" name="rw_comment_review_mode" value="false" <?php checked( 'false', $comment_ratings_mode ); ?>> <span><?php _e( 'Comment Ratings - Users can vote each other\'s comments.', WP_RW__ID ); ?></span>
                </div>
                <div class="rw-ui-img-radio rw-ui-img-radio-review-mode rw-ui-hor<?php if ( 'true' === $comment_ratings_mode ) echo ' rw-selected';?>">
                    <i class="rw-ui-sprite"></i> <input type="radio" name="rw_comment_review_mode" value="true" <?php checked( 'true', $comment_ratings_mode ); ?>> <span><?php _e( 'Reviews Ratings - Users can submit a rating with their comments.', WP_RW__ID ); ?></span>
                </div>
                <div class="rw-ui-img-radio rw-ui-img-radio-review-mode rw-ui-hor<?php if ( 'admin_ratings' === $comment_ratings_mode ) echo ' rw-selected';?>">
                    <i class="rw-ui-sprite"></i> <input type="radio" name="rw_comment_review_mode" value="admin_ratings" <?php checked( 'admin_ratings', $comment_ratings_mode ); ?>> <span><?php _e( 'Moderator Ratings - Only admin users can rate the comments [via the comment editor].', WP_RW__ID ); ?></span>
                </div>
            </div>
        </div>
    </div>
</div>