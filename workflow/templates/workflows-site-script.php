<?php
    /**
     * Template file called from RatingWidgetPlugin->rw_attach_rating_js method.
     * 
     * @package     RatingWidget
     * @copyright   Copyright (c) 2015, Rating-Widget, Inc.
     * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
     * @since       1.0.0
     */

    if ( ! defined( 'ABSPATH' ) ) {
        exit;
    }
?>
<script type="text/javascript">
function Class_WF_Engine( options, $, undef ) {
	var _options = $.extend({}, options ),
		_init = function() {
			if ( ! _options.workflows ) {
				_options.workflows = {};
			}
		},
		Util	  = (function() {
			return {
				toNumber: function( number ) {
					number = parseFloat( number, 10 );
					
					if ( isNaN( number ) ) {
						number = 0;
					}
					
					return number;
				},
				getAverageRating: function() {
					var rating = _options.ratingData.rating,
						ratingInstance = rating.getFirstInstance(),
						averageRatingString = ratingInstance.formatLabel( "{{rating.avg_rate}}" );
				
					return Util.toNumber( averageRatingString );
				},
				formatText: function( string ) {
					string = string.replace( '{{vote}}', _options.ratingData.score );
					string = string.replace( '{{avg_rate}}', Util.getAverageRating() );
					string = string.replace( '{{post.title}}', _options.currentPost.post_title );
					string = string.replace( '{{url}}', encodeURIComponent( window.location.href ) );
					
					return string;
				}
			};
		})(),
		Modal	  = (function() {
			var _modalHTML =
				'<div class="rw-wf-modal" aria-hidden="true">'
				+	'	<div class="rw-wf-modal-dialog">'
				+	'		<div class="rw-wf-modal-header">'
				+	'			<h2>{{modal.title}}</h2>'
				+	'			<a href="#close" class="rw-wf-button-close" aria-hidden="true">×</a>'
				+	'		</div>'
				+	'		<div class="rw-wf-modal-body">'
				+	'			{{modal.body}}'
				+	'		</div>'
				+	'		<div class="rw-wf-modal-footer">'
				+	'			<input type="button" href="#close" class="rw-wf-button-close rw-wf-button" value="<?php _erw( 'cancel' ) ?>" />'
				+	'		</div>'
				+	'	</div>'
				+	'</div>',
				_init = function() {
					$( 'body' ).delegate( '.rw-wf-button-close', 'click', function() {
						$( this ).parents( '.rw-wf-modal:first' ).remove();
					});
				};
			
			_init();
			
			return {
				show: function( args ) {
					args.body = Util.formatText( args.body );
					
					var modalHTML = _modalHTML;
					
					if ( args.title ) {
						args.title = Util.formatText( args.title );
						modalHTML = modalHTML.replace( '{{modal.title}}', args.title );
						modalHTML = modalHTML.replace( '{{modal.body}}', args.body );
					} else {
						modalHTML = _modalHTML.replace( '{{modal.title}}', args.body );
					}
					
					var $modal = $( modalHTML );
					
                                        if ( undef !== args.id ) {
                                            $modal.attr( 'id', args.id );
                                        }

					if ( ! args.title ) {
						$modal.addClass( 'no-body' );
					}
					
					for ( var buttonId in args.buttons ) {
						var button = args.buttons[ buttonId ];
						button.html = Util.formatText( button.html );
						
						var $button = $( button.html );
						$button.attr( 'id', buttonId );
						if ( button.click ) {
							$button.click( function( evt ) {
								button.click( evt, $modal );
							});
						}
						$button.addClass( 'rw-wf-button rw-wf-button-primary' );
						$button.insertBefore( $modal.find( '.rw-wf-modal-footer .rw-wf-button-close' ) );
					}
					
					setTimeout(function() {
						$modal.addClass( 'active' ).appendTo( $( 'body' ) );

						if ( args.width ) {
							$modal.find( '.rw-wf-modal-dialog' ).css({
								width: args.width,
								marginLeft: - ( args.width / 2 )
							});
						}

						$modal.css({
							display: 'block'
						});
					}, 1000 );
				}
			};
		})(),
		Operators = (function() {
			var _methods = {
				is: function( operands ) {
					return operands[0] == operands[1];
				},
				isNot: function( operands ) {
					return ( ! _methods.is( operands[0], operands[1] ) );
				},
				isEqualTo: function( operands ) {
					return ( Util.toNumber( operands[0] ) === Util.toNumber( operands[1] ) );
				},
				isNotEqualTo: function( operands ) {
					return ! _methods.isEqualTo( operands );
				},
				isLessThan: function( operands ) {
					return ( Util.toNumber( operands[0] ) < Util.toNumber( operands[1] ) );
				},
				isGreaterThan: function( operands ) {
					return ( Util.toNumber( operands[0] ) > Util.toNumber( operands[1] ) );
				},
				isLessThanOrEqualTo: function( operands ) {
					return ( _methods.isLessThan( operands )  || _methods.isEqualTo( operands ) );
				},
				isGreaterThanOrEqualTo: function( operands ) {
					return ( _methods.isGreaterThan( operands )  || _methods.isEqualTo( operands ) );
				}
			};

			_methods = $.extend( _methods, _options.methods );

			return {
				process: function( method, operands ) {
					if ( ! _methods[ method ] ) {
						return false;
					}

					return _methods[ method ]( operands );
				}
			}
		})(),
		_evaluateSingleWorkflow = function( workflow ) {
			var andEvaluation = true,
				andConditions = workflow.conditions;

			for ( var andIdx in andConditions ) {
				if ( ! andConditions.hasOwnProperty( andIdx ) ) {
					continue;
				}

				// Break if there is at least 1 false value since this AND.
				if ( false === andEvaluation ) {
					break;
				}

				var orEvaluation = false;
				var orConditions = andConditions[ andIdx ];
				for ( var orIdx in orConditions ) {
					if ( ! orConditions.hasOwnProperty( orIdx ) ) {
						continue;
					}

					// Break if there is at least 1 true value since this OR.
					if ( orEvaluation === true ) {
						break;
					}

					var orCondition = orConditions[ orIdx ],
						operandType = orCondition.operandType,
						operands = [
							orCondition.operand,
							""
						];
						
					var rating = _options.ratingData.rating,
						ratingInstance = rating.getFirstInstance();
					
					if ( 'category' === operandType ) {
						for ( var catIdx in _options.currentPostCategories ) {
							if ( ! _options.currentPostCategories.hasOwnProperty( catIdx ) ) {
								continue;
							}

							var category = _options.currentPostCategories[ catIdx ];
							if ( category.cat_ID == orCondition.operand ) {
								operands[1] = category.cat_ID;
								break;
							}
						}
					} else if ( 'post-type' === operandType ) {
						operands[1] = _options.currentPost.post_type;
					} else if ( 'rating-type' === operandType ) {
						var rclass = rating.rclass,
							criteriaSuffixPos = rclass.indexOf( '-criteria' );
						if ( -1 !== criteriaSuffixPos ) {
							rclass = rclass.substring( 0, criteriaSuffixPos );
						}
						
						operands[1] = rclass;
					} else if ( ratingInstance.isStar() && ( 'average-rate' === operandType ) ) {
						operands[1] = operands[0];
						operands[0] = Util.getAverageRating();
					} else if ( 'votes-count' === operandType ) {
						operands[1] = operands[0];
						operands[0] = rating.votes;
					} else if ( 'star-vote' === operandType ) {
						operands[1] = operands[0];
						operands[0] = _options.ratingData.score;
					} else if ( 'thumb-vote' === operandType ) {
						operands[1] = ( 0 === rating.rate ) ? 'dislike' : 'like';
					} else if ( 'user' === operandType ) {
						operands[1] = ( _options.currentUserId > 0 ) ? 'registered' : 'anonymous';
					}

					orEvaluation = Operators.process( orCondition.operator, operands );
				}

				andEvaluation = orEvaluation;
			}

			return andEvaluation;
		},
		_evaluateWorkflows = function( eventType ) {
			var evaluatedWorkflowsCount = 0;
			
			for ( var workflowId in _options.workflows ) {
				var workflow = _options.workflows[ workflowId ];
				if ( -1 === $.inArray( eventType, workflow.eventTypes ) ) {
					continue;
				}

				var result = _evaluateSingleWorkflow( workflow );
				if ( result ) {
					for ( var idx in workflow.actions ) {
						if ( ! workflow.actions.hasOwnProperty( idx ) ) {
							continue;
						}
						
						var action = _options.actions[ workflow.actions[ idx ] ];
						if ( undef === action ) {
                                                    continue;
						}
                                                
                                                action( workflow, idx );
                                                evaluatedWorkflowsCount++;
					}
				}
			}
			
			if ( 'beforeVote' === eventType && evaluatedWorkflowsCount > 0 ) {
				return false;
			}
			
			return true;
		};

	_init();

	return {
		eval: function( eventType, rating, score, defaultReturnValue ) {
			// Check if there is any active workflow.
			if ( 0 === Object.keys( _options.workflows ).length ) {
				return defaultReturnValue;
			}
			
			_options.ratingData = {
				rating: rating,
				score: score
			};
			
			_options.score = score;
			
			if ( 0 === Object.keys( _options.actions ).length ) {
				return defaultReturnValue;
			}
			
			return _evaluateWorkflows( eventType );
		},
		Modal: Modal,
		Util : Util
	}
};

var engineOptions = {
	workflows: <?php echo json_encode( rw_wf()->get_active_workflows() ); ?>,
	operandTypes: <?php echo json_encode( rw_wf()->get_variable_types() ); ?>,
	currentPost: <?php echo json_encode( get_post() ); ?>,
	currentPostCategories: <?php echo json_encode( get_the_category() ); ?>,
	currentUserId: <?php echo json_encode( get_current_user_id() ); ?>,
	actions: {}
};

<?php
$active_actions = rw_wf()->get_active_actions();

if ( ! empty( $active_actions ) ) {
	foreach( $active_actions as $action_id ) {
		do_action( 'init_workflow_action', $action_id );
	}
}
?>
	
<?php do_action( 'rw_wf_after_init_engine_options' ); ?>

var WF_Engine = new Class_WF_Engine( engineOptions, jQuery );
</script>