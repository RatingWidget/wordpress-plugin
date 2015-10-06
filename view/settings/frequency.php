<?php
	$frequency = rw_options()->frequency;
?>
<div id="rw_frequency_settings" class="has-sidebar has-right-sidebar">
	<div class="has-sidebar-content">
		<div class="postbox rw-body">
			<h3><?php _erw( 'voting-frequency-settings' ) ?></h3>

			<div class="inside rw-ui-content-container rw-no-radius" style="padding: 5px; width: 610px;">
				<?php
					$frquencies = array(
						array(
							'f'     => 'ONCE',
							'label' => __rw( 'once' ),
							'days'  => - 1,
							'desc'  => __rw( 'once_desc' )
						),
						array( 'f'     => 'DAILY',
						       'label' => __rw( 'daily' ),
						       'days'  => 1,
						       'desc'  => __rw( 'daily_desc' )
						),
						array( 'f'     => 'WEEKLY',
						       'label' => __rw( 'weekly' ),
						       'days'  => 7,
						       'desc'  => __rw( 'weekly_desc' )
						),
						array( 'f'     => 'MONTHLY',
						       'label' => __rw( 'monthly' ),
						       'days'  => 30,
						       'desc'  => __rw( 'monthly_desc' )
						),
						array( 'f'     => 'YEARLY',
						       'label' => __rw( 'annually' ),
						       'days'  => 365,
						       'desc'  => __rw( 'annually_desc' )
						),
						array( 'f'     => 'UNLIMITED',
						       'label' => __rw( 'unlimited' ),
						       'days'  => 0,
						       'desc'  => __rw( 'unlimited_desc' )
						),
					);
				?>
				<select>
					<?php foreach ( $frquencies as $f ) : $selected = ( $frequency == $f['days'] ); ?>
						<option name="rw_frequency" data-frequency="<?php echo $f['f'] ?>"
						        value="<?php echo $f['days'] ?>" <?php if ( $selected ) {
							echo ' selected="selected"';
						} ?>><?php echo $f['label'] . ' - ' . $f['desc'] ?></option>
					<?php endforeach ?>
				</select>
				<script type="text/javascript">
					(function ($) {
						$('#rw_frequency_settings select').chosen({width: '100%'}).change(function (evt, params) {
							RWM.Set.frequency(RW.FREQUENCY[$(this).find('option[value=' + params.selected + ']').attr('data-frequency')]);
						});
					})(jQuery);
				</script>
			</div>
		</div>
	</div>
</div>
