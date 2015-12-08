var RW_WF_Modal = (function( $ ) {
	var _modalHTML =
		'<div class="rw-wf-modal" aria-hidden="true">'
		+	'	<div class="rw-wf-modal-dialog">'
		+	'		<div class="rw-wf-modal-header">'
		+	'			<div>{{modal.title}}</div>'
		+	'			<a href="#close" class="rw-wf-button-close" aria-hidden="true">×</a>'
		+	'		</div>'
		+	'		<div class="rw-wf-modal-body">'
		+	'			{{modal.body}}'
		+	'		</div>'
		+	'		<div class="rw-wf-modal-footer">'
		+	'		</div>'
		+	'	</div>'
		+	'</div>',
		_init = function() {
			$( 'body' ).delegate( '.rw-wf-button-close', 'click', function() {
				$( 'body' ).removeClass( 'has-rw-wf-modal' );
				$( this ).parents( '.rw-wf-modal:first' ).remove();
			});
		};

	_init();

	return {
		show: function( args ) {
			var modalHTML = _modalHTML;

			if ( args.title ) {
				modalHTML = modalHTML.replace( '{{modal.title}}', '<h2>' + args.title + '</h2>' );
				modalHTML = modalHTML.replace( '{{modal.body}}', args.body );
			} else {
				modalHTML = _modalHTML.replace( '{{modal.title}}', args.body );
			}

			var $modal = $( modalHTML );
			
			if ( args.id ) {
				$modal.attr( 'id', args.id );
			}
			
			if ( args.data ) {
				for ( var dataKey in args.data ) {
					$modal.attr( 'data-' + dataKey, args.data[ dataKey ] );
				}
			}
			
			if ( ! args.title ) {
				$modal.addClass( 'no-body' );
				$modal.find( '.rw-wf-modal-body' ).remove();
			}

			for ( var buttonId in args.buttons ) {
				var button = args.buttons[ buttonId ];

				var $button = $( button.html );
				$button.attr( 'id', buttonId );
				if ( button.click ) {
					$button.click( button.click );
				}
				
				$button.addClass( 'button' );
				
				if ( button.primary ) {
					$button.addClass( 'button-primary' );
				} else {
					$button.addClass( 'button-secondary' );
				}
				
				$button.appendTo( $modal.find( '.rw-wf-modal-footer' ) );
			}

			setTimeout(function() {
				$( 'body' ).addClass( 'has-rw-wf-modal' );
				
				$modal.addClass( 'active' ).appendTo( $( 'body' ) );
				
				if ( args.width ) {
					$modal.find( '.rw-wf-modal-dialog' ).css({
						width: args.width,
						marginLeft: - ( args.width / 2 )
					});
				}
				
				if ( args.height ) {
					if ( ! args.title ) {
						$modal.find( '.rw-wf-modal-header > div:first' ).css({
							height: args.height,
							overflow: 'auto'
						});
					}
				}
			}, args.delay ? args.delay : 1000 );
		},
		hide: function() {
			$( 'body' ).removeClass( 'has-rw-wf-modal' );
			$( '.rw-wf-modal' ).remove();
		}
	};
})( jQuery );