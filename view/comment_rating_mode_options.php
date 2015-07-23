<?php
    $is_comment_review_mode = rw_settings()->is_comment_review_mode;
?>
<div id="rw_comment_rating_mode_settings" class="has-sidebar has-right-sidebar">
    <div class="has-sidebar-content">
        <div class="postbox rw-body">
            <h3><?php _e('Rating Mode', WP_RW__ID) ?></h3>
            <div class="inside rw-ui-content-container rw-no-radius">
                <div class="rw-ui-img-radio rw-ui-hor<?php if (!$is_comment_review_mode) echo ' rw-selected';?>">
                    <i class="rw-ui-sprite"></i> <input type="radio" name="rw_comment_review_mode" value="false" <?php checked($is_comment_review_mode, true); ?>> <span><?php _e('Standard - Ratings are not read-only. Visitors can vote for the comments.', WP_RW__ID) ?></span>
                </div>
                <div class="rw-ui-img-radio rw-ui-img-radio-review-mode rw-ui-hor<?php if ($is_comment_review_mode) echo ' rw-selected';?>">
                    <i class="rw-ui-sprite"></i> <input type="radio" name="rw_comment_review_mode" value="true" <?php checked($is_comment_review_mode, true); ?>> <span><?php _e('Reviews &nbsp;&nbsp;- Ratings are read-only. Allows users to submit a comment (review) with a rating.', WP_RW__ID) ?></span>
                </div>
            </div>
        </div>
    </div>
</div>
