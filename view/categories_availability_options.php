<?php
    // Get all post categories.
    $all_categories = get_categories();
    
    if (is_array($all_categories) && count($all_categories) > 0)
    {
        $all = in_array("-1", rw_settings()->categories);
?>
<div id="rw_categories_availability_settings" class="has-sidebar has-right-sidebar">
    <div class="has-sidebar-content">
        <div class="postbox rw-body">
            <h3><?php _e('Categories Visibility Settings', WP_RW__ID) ?></h3>
            <div class="inside rw-ui-content-container rw-no-radius">
	            <select data-placeholder="Choose Categories..." multiple>
		            <option value="-1" <?php if ($all) echo ' selected="selected"';?>><?php _e('All Categories', WP_RW__ID) ?></option>
		            <?php foreach ($all_categories as $category) : $selected = ($all || in_array($category->cat_ID, rw_settings()->categories)); ?>
	                    <option value="<?php echo $category->cat_ID ?>" <?php if ($selected) echo ' selected="selected"';?>><?php echo $category->cat_name ?></option>
	                <?php endforeach ?>
	            </select>
	            <div style="display: none">
		            <input type="checkbox" name="rw_categories[]" value="-1" <?php if ($all) echo ' checked="checked"';?>>
		            <?php foreach ($all_categories as $category) : $selected = ($all || in_array($category->cat_ID, rw_settings()->categories)); ?>
			            <input type="checkbox" name="rw_categories[]" value="<?php echo $category->cat_ID;?>" <?php if ($selected) echo ' checked="checked"';?>>
		            <?php endforeach ?>
	            </div>
	            <script>
		            var $ = $ || jQuery;

		            $('#rw_categories_availability_settings select').chosen({width: '100%'}).change(function(evt, params){
			            var sel_all = $(this).find('option[value=-1]'),
				            sel_all_check = $(this).parents().find('input[type=checkbox][value=-1]');

			            if (params.selected)
			            {
				            $(this).parents().find('input[type=checkbox][value=' + params.selected + ']').prop('checked', true);

				            if ('-1' === params.selected) {
					            // Selected all categories.
					            $(this).find('option').prop('selected', true);
					            $(this).parents().find('input[type=checkbox]').prop('checked', true);
				            }
				            else
				            {
					            if (!sel_all.is(':selected') && ($(this).find('option').length - 1) == $(this).find('option:selected').length) {
						            // Select all.
						            sel_all.prop('selected', true);
						            sel_all_check.prop('checked', true);
					            }
				            }
			            }
			            else if (params.deselected)
			            {
				            $(this).parents().find('input[type=checkbox][value=' + params.deselected + ']').prop('checked', false);

				            // Deselect "All Categories".
				            sel_all.prop('selected', false);
				            sel_all_check.prop('checked', false);
			            }

			            $(this).trigger("chosen:updated");
		            });
	            </script>
            </div>
        </div>
    </div>
</div>
<?php
    }