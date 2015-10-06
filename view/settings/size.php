<?php
	$selected_size = rw_options()->size;
?>
<td class="rw-ui-def-width">
	<span class="rw-ui-def"><?php _erw( 'size' ) ?>:</span>
</td>
<td>
	<?php
		$sizes = array(
			'tiny'   => __rw( 'tiny' ),
			'small'  => __rw( "small" ),
			'medium' => __rw( "medium" ),
			'large'  => __rw( "large" )
		);

		$tab_index = 5;
		foreach ( $sizes as $size => $label ) {
			?>
			<div class="rw-ui-img-radio<?php if ( $selected_size == $size ) {
				echo " rw-selected";
			} ?>" onclick="RWM.Set.size(RW.SIZE.<?php echo strtoupper( $size ); ?>);">
				<i class="rw-ui-holder"><i
						class="rw-ui-sprite rw-ui-star rw-ui-<?php echo $size ?> rw-ui-yellow"></i></i>
				<span><?php echo $label ?></span>
				<input type="radio" tabindex="<?php echo $tab_index; ?>" name="rw-size"
				       value="0"<?php if ( $selected_size == $size ) {
					echo ' checked="checked"';
				} ?> />
			</div>
			<?php
			$tab_index ++;
		}
	?>
</td>