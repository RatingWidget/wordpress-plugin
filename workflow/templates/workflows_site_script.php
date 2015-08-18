<?php
$wf = rw_wf();
?>

<script type="text/javascript">
function Class_WF_Engine( options, $ ) {
	var _options = $.extend({}, options),
		_init = function() {
			if ( ! _options.workflows ) {
				_options.workflows = {};
			}
		},
		WF_Operators = (function() {
			var _methods = {
				is: function( operands ) {
					return operands[0] == operands[1];
				},
				isNot: function( operands ) {
					return ( ! _methods.is( operands[0], operands[1] ) );
				},
				isEqual: function( firstOperand, secondOperand ) {
					return ( firstOperand === secondOperand );
				},
				isLessThan: function( firstOperand, secondOperand ) {
					return ( firstOperand < secondOperand );
				},
				isGreaterThan: function( firstOperand, secondOperand ) {
					return ( firstOperand > secondOperand );
				},
				isLessThanOrEqual: function( firstOperand, secondOperand ) {
					return ( _methods.isLessThan( firstOperand, secondOperand )  || _methods.isEqual( firstOperand, secondOperand ) );
				},
				isGreaterThanOrEqual: function( firstOperand, secondOperand ) {
					return ( _methods.isGreaterThan( firstOperand, secondOperand )  || _methods.isEqual( firstOperand, secondOperand ) );
				}
			};

			_methods = $.extend(_methods, _options.methods);

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
						operandType = _options.operandTypes[ orCondition.operandType ],
						operands = [
							orCondition.operand,
							""
						];
						
					if ( 'categories' === operandType.slug ) {
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
					} else if ( 'post_types' === operandType.slug ) {
						operands[1] = _options.currentPost.post_type;
					}

					orEvaluation = WF_Operators.process( orCondition.operator, operands );
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
						
						_options.actions[ workflow.actions[ idx ].actionID ] ();
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
			if ( 0 === Object.keys( _options.actions ).length ) {
				return defaultReturnValue;
			}
			
			return _evaluateWorkflows( eventType );
		}
	}
};

var engineOptions = {
	workflows: <?php echo json_encode( $wf->get_workflows() ); ?>,
	operandTypes: <?php echo json_encode( $wf->get_operand_types() ); ?>,
	currentPost: <?php echo json_encode( get_post() ); ?>,
	currentPostCategories: <?php echo json_encode( get_the_category() ); ?>,
	actions: {}
};

<?php do_action('rw_wf_after_init_engine_options'); ?>

var WF_Engine = new Class_WF_Engine( engineOptions, jQuery );
</script>