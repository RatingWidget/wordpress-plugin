<?php
    $frequency = rw_options()->frequency;
?>
<div id="rw_frequency_settings" class="has-sidebar has-right-sidebar">
    <div class="has-sidebar-content">
        <div class="postbox rw-body">
            <h3>Voting Frequency Settings</h3>
            <div class="inside rw-ui-content-container rw-no-radius" style="padding: 5px; width: 610px;">
	            <?php
		            $frquencies = array(
			            array('f' => 'ONCE', 'label' => __('Once', WP_RW__ID), 'days' => -1, 'desc' => __('user can vote only once per rating', WP_RW__ID)),
			            array('f' => 'DAILY', 'label' => __('Daily', WP_RW__ID), 'days' => 1, 'desc' => __('user can vote once a day for every rating', WP_RW__ID)),
			            array('f' => 'WEEKLY', 'label' => __('Weekly', WP_RW__ID), 'days' => 7, 'desc' => __('user can vote once a week (7 days) for every rating', WP_RW__ID)),
			            array('f' => 'MONTHLY', 'label' => __('Monthly', WP_RW__ID), 'days' => 30, 'desc' => __('user can vote once a month (30 days) for every rating', WP_RW__ID)),
			            array('f' => 'YEARLY', 'label' => __('Annually', WP_RW__ID), 'days' => 365, 'desc' => __('user can vote once a year (365 days) for every rating', WP_RW__ID)),
			            array('f' => 'UNLIMITED', 'label' => __('Unlimited', WP_RW__ID), 'days' => 0, 'desc' => __('user can vote as many times as he likes', WP_RW__ID)),
		            );
	            ?>
	            <select>
		            <?php foreach ($frquencies as $f) : $selected = ($frequency == $f['days']); ?>
			            <option name="rw_frequency" data-frequency="<?php echo $f['f'] ?>" value="<?php echo $f['days'] ?>" <?php if ($selected) echo ' selected="selected"';?>><?php echo $f['label'] . ' - ' . $f['desc'] ?></option>
		            <?php endforeach ?>
	            </select>
				<script type="text/javascript">
					$('#rw_frequency_settings select').chosen({width: '100%'}).change(function(evt, params) {
						RWM.Set.frequency(RW.FREQUENCY[$(this).find('option[value=' + params.selected + ']').attr('data-frequency')]);
					});
				</script>
            </div>
        </div>
    </div>
</div>
