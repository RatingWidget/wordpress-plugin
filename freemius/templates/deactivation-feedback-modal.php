<?php
	$reasons = $VARS['reasons'];
	
	$reasons_list_items_html = '';

	foreach ( $reasons as $reason ) {
		$text_i18n_key = '';
		$input_type = '';
		$placeholder = '';

		$list_item_classes = 'reason';

		if ( ! is_array( $reason ) ) {
			$text_i18n_key = $reason;
		} else {
			$text_i18n_key = $reason['text'];
			$input_type = $reason['type'];
			$placeholder = $reason['placeholder'];
			
			$list_item_classes .= ' has-input';
		}
		
		$reasons_list_items_html .= '<li class="' . $list_item_classes . '" data-input-type="' . $input_type . '" data-input-placeholder="' . $placeholder . '"><label><input type="radio" name="selected-reason" value="' . $text_i18n_key . '"/> ' . __fs( $text_i18n_key ) . '</label></li>';
	}
	?>
	<script>
		(function( $ ) {
			var reasonsHtml		= <?php echo json_encode( $reasons_list_items_html ); ?>,
				modalHtml		=
				'<div class="freemius-modal no-body">'
				+	'	<div class="freemius-modal-dialog">'
				+	'		<div class="freemius-modal-header">'
				+	'			<div class="freemius-modal-panel panel-headsup active"><p><?php printf( $VARS['confirm-message'] ); ?></p></div>'
				+	'			<div class="freemius-modal-panel panel-reasons"><p><strong><?php printf( __fs( 'deactivation-share-reason' ) ); ?>:</strong></p><ul id="reasons-list">' + reasonsHtml + '</ul></div>'
				+	'		</div>'
				+	'		<div class="freemius-modal-body">'
				+	'		</div>'
				+	'		<div class="freemius-modal-footer">'
				+	'			<a href="#" class="button button-secondary button-deactivate"></a>'
				+	'			<a href="#" class="button button-primary button-close"><?php printf( __fs( 'deactivation-modal-button-cancel' ) ); ?></a>'
				+	'		</div>'
				+	'	</div>'
				+	'</div>',
				$modal			= $( modalHtml ),
				$deactivateLink = $( '#the-list [data-slug=<?php echo $VARS['slug']; ?>].active .deactivate a' );
				
			$modal.appendTo( $( 'body' ) );

			registerEventHandlers();
			
			function registerEventHandlers() {
				$deactivateLink.click(function ( evt ) {
					evt.preventDefault();

					showModal();
				});
				
				$modal.on( 'click', '.button', function( evt ) {
					evt.preventDefault();
					
					var _parent = $( this ).parents( '.freemius-modal:first' );
					var _this = $( this );

					if ( _this.hasClass( 'button-close' ) ) {
						$modal.removeClass( 'active' );
					} else if ( _this.hasClass( 'allow-deactivate' ) ) {
						// Do not show the dialog box, deactivate the plugin.
						window.location.href = $deactivateLink.attr( 'href' );
					} else if ( _this.hasClass( 'button-deactivate' ) ) {
						// Change the Deactivate button's text and show the reasons panel.
						_parent.find( '.button-deactivate').addClass( 'allow-deactivate' );
						_parent.find( '.panel-headsup').hide();
						_parent.find( '.panel-reasons').show();
					}
				});

				$modal.on( 'click', 'input[type="radio"]', function() {
					var _parent = $( this ).parents( 'li:first' );
					
					$modal.find( '.reason-input' ).remove();
					$modal.find( '.button-deactivate').text( '<?php printf( __fs( 'deactivation-modal-button-submit' ) ); ?>' );

					if ( _parent.hasClass( 'has-input' ) ) {
						var inputType		 = _parent.data( 'input-type' ),
							inputPlaceholder = _parent.data( 'input-placeholder' ),
							reasonInputHtml  = '<div class="reason-input">' + ( ( 'textfield' === inputType ) ? '<input type="text" />' : '<textarea rows="5"></textarea>' ) + '</div>'; 
						
						_parent.append( $( reasonInputHtml ) );
						_parent.find( 'input, textarea' ).attr( 'placeholder', inputPlaceholder ).focus();
					}
				});
			}
			
			function showModal() {
				resetModal();
				
				// Display the dialog box.
				$modal.addClass( 'active' );
			}
			
			function resetModal() {
				// Reset the deactivate button's text.
				$modal.find( '.button-deactivate' ).removeClass( 'allow-deactivate' ).text( '<?php printf( __fs( 'deactivation-modal-button-deactivate' ) ); ?>' );
				
				// Uncheck all radio buttons.
				$modal.find( 'input[type="radio"]' ).prop( 'checked', false );

				// Remove all input fields ( textfield, textarea ).
				$modal.find( '.reason-input' ).remove();
				
				// Display the first panel (heads-up panel).
				$modal.find( '.panel-headsup').show();
				
				// Hide the second panel (reasons panel).
				$modal.find( '.panel-reasons').hide();
			}
		})( jQuery );
	</script>
