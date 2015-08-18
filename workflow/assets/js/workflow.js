(function($) {
	init();
	
	function init() {
		initWorkflows();
		
		$(function() {
			// Make the workflows list sortable
			$('.list-group').sortable({
				// When the order of the items has changed, notify the server via AJAX request.
				update: function(event, ui ) {
					var sortedIDs = $('.list-group').sortable( "toArray", {attribute: 'data-workflow-id'} );

					$.ajax({
						url: ajaxurl,
						method: 'POST',
						data: {
							action: 'update-workflows-id-order',
							ids: sortedIDs
						}
					});
				}
			});

			/*
			 * Event Handlers
			***************************************************************************************/
			$('body').on('change', 'select.operand-types', function() {
				var operandId = parseInt( $(this).val(), 10 ),
					$facet = $(this).parents('.facet:first'),
					$operandsContainer = $facet.find('.facetInputs');
				
				if ( -1 === operandId ) {
					$operandsContainer.hide();
					return;
				}
				
				$operandsContainer.css('display', 'block');
				
				var $colOperator = $operandsContainer.find('.col:first'),
					$colOperand = $operandsContainer.find('.col:last'),
					$operators = createFromTemplate('operators'),
					operators = WORKFLOWS_SETTINGS.operators;

				$colOperator.html('');
				$colOperand.html('');

				$colOperator.append($operators);
				
				for ( var operatorId in operators ) {
					var operator = operators[ operatorId ];

					if ( operandId >= operator.min && operandId < operator.max ) {
						$operators.append('<option value="' + operatorId + '">' + operator.title + '</option>');
					}
				}
				
				// Operand
				var operandSlug = WORKFLOWS_SETTINGS.operandTypes[ operandId ].slug;
				$colOperand.append(createFromTemplate(operandSlug));
			});

			$('body').on('click', '.btn', function(e) {
				e.preventDefault();
				
				var	$button	 = $(this),
					$currentTab = $button.parents('.tab-pane:first'),
					$nextTab = $($button.data('next-step-tab'));
					
				if ( $(this).hasClass('save-name') ) {
					var name = $('#workflow-name').val().trim();
					if ( ! name ) {
						alert('Invalid Workflow Name');
						return false;
					}

					var workflowId = $('#edit-workflow').attr('data-id');

					$.ajax({
						url: ajaxurl,
						method: 'POST',
						data: {
							action: 'update-workflow',
							name: name,
							id: workflowId ? workflowId : ''
						},
						success: function( result ) {
							updateSummary();
							showSummary();
						},
						complete: function(a, b) {
							$button.button('reset');
						},
						beforeSend: function() {
							$button.button('loading');
						}
					});
				} else if ( $(this).hasClass('save-conditions') ) {
					$button.button('loading');
					updateConditions(function() {
						updateSummary();
						showSummary();
					}, function() {
						$button.button('reset');
					});
				} else if ( $(this).hasClass('save-actions') ) {
					$button.button('loading');
					updateActions(function() {
						updateSummary();
						showSummary();
					}, function() {
						$button.button('reset');
					});
				} else if ( $(this).hasClass('save-event-types') ) {
					$button.button('loading');
					updateTriggers(function() {
						updateSummary();
						showSummary();
					}, function() {
						$button.button('reset');
					});
				} else if ( $(this).hasClass('edit-workflow-next-step') ) {
					currentStep = $currentTab.data('step');

					if ( 'enter-workflow-name' === currentStep ) {
						var name = $('#workflow-name').val().trim();
						if ( ! name ) {
							alert('Invalid Workflow Name');
							return false;
						}

						var workflowId = $('#edit-workflow').attr('data-id');

						$.ajax({
							url: ajaxurl,
							method: 'POST',
							data: {
								action: workflowId ? 'update-workflow' : 'create-workflow',
								name: name,
								id: workflowId ? workflowId : ''
							},
							success: function( result ) {
								if ( ! workflowId ) {
									var jsonResult = JSON.parse( result ),
										workflowId = jsonResult.data.id,
										$editConditions = $('#edit-workflow-step2').find('.edit-conditions');

									if ( 0 === $editConditions.length ) {
										$editConditions = createFromTemplate('edit-conditions');
										$('#edit-workflow-step2').prepend( $editConditions );

										var $newCondition = createFromTemplate('condition-template');
										$editConditions.find('.workflowCondActions').append($newCondition);

										var $newFacet = createFromTemplate('facet');
										var $facetList = $newCondition.find('.facetList');
										$facetList.append($newFacet);
									}

									$('#edit-workflow').attr('data-id', workflowId);
									WORKFLOWS_SETTINGS.workflows[ workflowId ] = jsonResult.data.workflow;
								}

								$('.nav-pills li:nth-child(1)').removeClass('active');
								$('.nav-pills li:nth-child(2)').removeClass('disabled').find('a').attr('data-toggle', 'tab').tab('show');
							},
							complete: function(a, b) {
								$button.button('reset');
							},
							beforeSend: function() {
								$button.button('loading');
							}
						});
					} else if ( 'select-condition' === currentStep ) {
						$button.button('loading');
						
						updateConditions(function() {
							var $editActions = $('#edit-workflow-step3').find('.edit-actions');

							if ( 0 === $editActions.length ) {
								$editActions = createFromTemplate('edit-actions');
								$('#edit-workflow-step3').prepend( $editActions );

								$editActions.find('section.condAction .facet > .col').append( createFromTemplate('actions') );
							}

							$('.nav-pills li:nth-child(2)').removeClass('active');
							$('.nav-pills li:nth-child(3)').removeClass('disabled').find('a').attr('data-toggle', 'tab').tab('show');
						}, function() {
							$button.button('reset');
						});
					} else if ( 'select-action' === currentStep ) {
						$button.button('loading');
						
						updateActions(function() {
							var $editEventTypes = $('#edit-workflow-step4').find('.edit-event-types');

							if ( 0 === $editEventTypes.length ) {
								$editEventTypes = createFromTemplate('edit-event-types');
								$('#edit-workflow-step4').prepend( $editEventTypes );

								$editEventTypes.find('section.condAction .facet > .col').append( createFromTemplate('event-types') );
							}

							$('.nav-pills li:nth-child(3)').removeClass('active');
							$('.nav-pills li:nth-child(4)').removeClass('disabled').find('a').attr('data-toggle', 'tab').tab('show');
						}, function() {
							$button.button('reset');
						});
					} else if ( 'select-event-type' === currentStep ) {
						$button.button('loading');
						
						updateTriggers(function() {
							var $workflowSummary = $('#edit-workflow-summary').find('.workflow-summary');

							if ( 0 === $workflowSummary.length ) {
								$workflowSummary = createFromTemplate('workflow-summary');
								$('#edit-workflow-summary').prepend( $workflowSummary );
							} else {
								$workflowSummary.find('.list-group:first').html('');
								$workflowSummary.find('.list-group:last').html('');
							}

							var title = $('#workflow-name').val(),
								$allConditions = $('#edit-workflow-step2 .edit-conditions .workflowCondActions').children(),
								$allActions = $('#edit-workflow-step3 .edit-actions .workflowCondActions').children(),
								$allTriggers = $('#edit-workflow-step4 .edit-event-types .workflowCondActions').children();

							var conditionsHtml = '';
							$allConditions.each(function() {
								var conditions = [];

								$(this).find('.facetList').children().each(function() {
									var $condition = $(this),
										operandId = $condition.find('.operand-types').val(),
										operatorId = $condition.find('.operators').val(),
										operand = $condition.find('.facetInputs .col:last select').val(),
										condition = '',
										operandTypeValues = WORKFLOWS_SETTINGS.operandTypes[ operandId ].value;
										
									
									conditions.push(
										WORKFLOWS_SETTINGS.operandTypes[ operandId ].title
										+ ' ' + WORKFLOWS_SETTINGS.operators[ operatorId ].title
										+ ' ' + '<strong>' + ( undefined != operandTypeValues[ operand ] ? operandTypeValues[ operand ].value : '' ) + '</strong>');
								});

								var condition = '<li class="list-group-item">';

								if ( '' != conditionsHtml ) {
									condition += '<span class="label label-default and">and</span>';
								}

								condition += conditions.join('<span class="label label-default or">or</span>') + '</li>';

								conditionsHtml += condition;
							});

							var actionsHtml = '';
							$allActions.each(function() {
								var $action = $(this),
									actionId = $action.find('select.actions').val();


								var action = '<li class="list-group-item">';

								if ( '' != actionsHtml ) {
									action += '<span class="label label-default and">and</span>';
								}

								action += WORKFLOWS_SETTINGS.actions[ actionId ].value + '</li>';

								actionsHtml += action;
							});

							var eventTypesHtml = '';
							$allTriggers.each(function() {
								var $eventType = $(this),
									eventTypeId = $eventType.find('select.event-types').val();


								var eventType = '<li class="list-group-item">';

								if ( '' != eventTypesHtml ) {
									eventType += '<span class="label label-default and">or</span>';
								}

								eventType += WORKFLOWS_SETTINGS.eventTypes[ eventTypeId ].value + '</li>';

								eventTypesHtml += eventType;
							});

							$workflowSummary.find('.workflow-title').text(title);
							$workflowSummary.find('.list-group:eq(0)').html(conditionsHtml);
							$workflowSummary.find('.list-group:eq(1)').html(actionsHtml);
							$workflowSummary.find('.list-group:eq(2)').html(eventTypesHtml);

							$('.nav-pills li:nth-child(4)').removeClass('active');
							$('.nav-pills li:nth-child(5)').removeClass('disabled').find('a').attr('data-toggle', 'tab').tab('show');

							$('#edit-workflow').addClass('is-editing');
						}, function() {
							$button.button('reset');
						});
					}
				}
			});
			
			$('#new-workflow-btn').click(function(e) {
				e.preventDefault();
				
				$('#edit-workflow').removeClass('is-editing').addClass('is-creating').attr('data-id', '');
				$('div.edit-actions, div.edit-conditions, div.edit-event-types').remove();
				
				$('a[href="#edit-workflow-step1"]').tab('show');
				$(this).tab('show');
			});
			
			$('body').on('click', '.workflow-summary .btn.edit-workflow', function(e) {
				e.preventDefault();
				
				if ( $(this).hasClass('edit-conditions') ) {
					var href = $(this).attr('href');
					$('.nav-pills').children().removeClass('active').find('a[href="' + href + '"]').parent().addClass('active');
					var workflowId = $('#edit-workflow').attr('data-id'),
						workflow = WORKFLOWS_SETTINGS.workflows[ workflowId ],
						$editConditions = $('#edit-workflow-step2').find('.edit-conditions');

					if ( 0 === $editConditions.length ) {
						$editConditions = createFromTemplate('edit-conditions');
						$('#edit-workflow-step2').prepend( $editConditions );
					} else {
						$editConditions.find('.workflowCondActions').html('');
					}
					
					for ( var idx in workflow.conditions ) {
						if ( ! workflow.conditions.hasOwnProperty( idx ) ) {
							continue;
						}

						if ( idx > 0 ) {
							var $lastAction = $editConditions.find('.workflowCondActions > div:last');
							$lastAction.find(' > p.and').replaceWith('<div><p class="and"><em>AND</em></p></div>');
						}
						
						var $newCondition = createFromTemplate('condition-template');
						$editConditions.find('.workflowCondActions').append($newCondition);
						
						var conditionsArr = workflow.conditions[ idx ];
						for ( var idx2 in conditionsArr ) {
							if ( ! conditionsArr.hasOwnProperty( idx2 ) ) {
								continue;
							}

							var singleCondition = conditionsArr[ idx2 ],
								operandId = singleCondition.operandType,
								operatorId = singleCondition.operator,
								operand = singleCondition.operand;
								
							var $newFacet = createFromTemplate('facet');
							var $facetList = $newCondition.find('.facetList');
							$facetList.append($newFacet);
							
							$newFacet.find('select.operand-types').val(operandId).change();
							$newFacet.find('select.operator').val(operatorId);
							$newFacet.find('select.' + WORKFLOWS_SETTINGS.operandTypes[ operandId ].slug).val(operand);
						}
					}
				} else if ( $(this).hasClass('edit-actions') ) {
					var href = $(this).attr('href');
					$('.nav-pills').children().removeClass('active').find('a[href="' + href + '"]').parent().addClass('active');
					var workflowId = $('#edit-workflow').attr('data-id'),
						workflow = WORKFLOWS_SETTINGS.workflows[ workflowId ],
						$editActions = $('#edit-workflow-step3').find('.edit-actions');

					if ( 0 === $editActions.length ) {
						$editActions = createFromTemplate('edit-actions');
						$editActions.find('.workflowCondActions').html('');
						$('#edit-workflow-step3').prepend( $editActions );
					} else {
						$editActions.find('.workflowCondActions').html('');
					}
					
					$editActions.find('select').html('');
					
					for ( var idx in workflow.actions ) {
						if ( ! workflow.actions.hasOwnProperty( idx ) ) {
							continue;
						}

						if ( idx > 0 ) {
							var $lastAction = $editActions.find('.workflowCondActions > div:last');
							$lastAction.find(' > p.and').replaceWith('<div><p class="and"><em>AND</em></p></div>');
						}
						
						var $newAction = createFromTemplate('action-template');
						$editActions.find('.workflowCondActions').append($newAction);
						
						var singleAction = workflow.actions[ idx ],
							actionId = singleAction.actionID;
							
						$newAction.find('.facet > .col').append( createFromTemplate('actions') );
						$newAction.find('select').val(actionId);
					}
				} else if ( $(this).hasClass('edit-event-types') ) {
					var href = $(this).attr('href');
					$('.nav-pills').children().removeClass('active').find('a[href="' + href + '"]').parent().addClass('active');
					var workflowId = $('#edit-workflow').attr('data-id'),
						workflow = WORKFLOWS_SETTINGS.workflows[ workflowId ],
						$editEventTypes = $('#edit-workflow-step4').find('.edit-event-types');

					if ( 0 === $editEventTypes.length ) {
						$editEventTypes = createFromTemplate('edit-event-types');
						$editEventTypes.find('.workflowCondActions').html('');
						$('#edit-workflow-step4').prepend( $editEventTypes );
					} else {
						$editEventTypes.find('.workflowCondActions').html('');
					}
					
					$editEventTypes.find('select').html('');
					
					for ( var idx in workflow.eventTypes ) {
						if ( ! workflow.eventTypes.hasOwnProperty( idx ) ) {
							continue;
						}

						if ( idx > 0 ) {
							var $lastTrigger = $editEventTypes.find('.workflowCondActions > div:last');
							$lastTrigger.find(' > p.and').replaceWith('<div><p class="and"><em>OR</em></p></div>');
						}
						
						var $newTrigger = createFromTemplate('event-type-template');
						$editEventTypes.find('.workflowCondActions').append($newTrigger);
						
						var eventTypeId = workflow.eventTypes[ idx ];
							
						$newTrigger.find('.facet > .col').append( createFromTemplate('event-types') );
						$newTrigger.find('select').val(eventTypeId);
					}
				} else {
					return;
				}
				
				$(this).tab('show');
			});
			
			$('#workflows').on('click', '.glyphicon-trash', function() {
				var targetWorkflowId = $(this).parents('.list-group-item:first').data('workflow-id');
				$('#confirm-delete-workflow').data('target-workflow-id', targetWorkflowId);
				$('#confirm-delete-workflow').modal('show');
			});

			$('#confirm-delete-workflow').on('click', '.btn-primary', function() {
				$('#confirm-delete-workflow').modal('hide');
				
				var workflowId		= $('#confirm-delete-workflow').data('target-workflow-id'),
					$listGroupItem	= $('.list-group.ui-sortable a.list-group-item[data-workflow-id="'+workflowId+'"]'),
					len				= $listGroupItem.length;

				if ( workflowId ) {
					$.ajax({
						url: ajaxurl,
						method: 'POST',
						data: {
							action: 'delete-workflow',
							id: workflowId
						},
						success: function(result) {
							result = JSON.parse(result);
							if (result.success) {
								$('.list-group-item[data-workflow-id="'+workflowId+'"]').remove();
							}
						},
						beforeSend: function() {
							$listGroupItem.addClass('disabled');

							text = $listGroupItem.find('.list-group-item-content').text();
							$listGroupItem.find('.list-group-item-content').text(text + ' (deleting...)');
						},
						complete: function(a, b) {
							var a;
						}
					});
				}
			});

			$('#workflows').on('click', '.list-group-item', function(evt) {
				evt.preventDefault();

				var $target = $(evt.target);
				if($target.hasClass('pull-right')
					|| $target.parents('.pull-right').length ) {
					return false;
				}
				
				var $workflowSummary = $('#edit-workflow-summary').find('.workflow-summary');

				if ( 0 === $workflowSummary.length ) {
					$workflowSummary = createFromTemplate('workflow-summary');
					$('#edit-workflow-summary').prepend( $workflowSummary );
				} else {
					$workflowSummary.find('.list-group:first').html('');
					$workflowSummary.find('.list-group:last').html('');
				}
				
				var workflowId = $(this).data('workflow-id');
				var workflow = WORKFLOWS_SETTINGS.workflows[ workflowId ];
				
				var conditionsHtml = '';
				for ( var idx in workflow.conditions ) {
					if ( ! workflow.conditions.hasOwnProperty( idx ) ) {
						continue;
					}
					
					var conditions = [];
					
					var conditionsArr = workflow.conditions[ idx ];
					for ( var idx2 in conditionsArr ) {
						if ( ! conditionsArr.hasOwnProperty( idx2 ) ) {
							continue;
						}
						
						var singleCondition = conditionsArr[ idx2 ],
							operandId = singleCondition.operandType,
							operatorId = singleCondition.operator,
							operand = singleCondition.operand,
							condition = '',
							operandTypeValues = WORKFLOWS_SETTINGS.operandTypes[ operandId ].value;

						conditions.push(
							WORKFLOWS_SETTINGS.operandTypes[ operandId ].title
							+ ' ' + WORKFLOWS_SETTINGS.operators[ operatorId ].title
							+ ' ' + '<strong>' + ( undefined != operandTypeValues[ operand ] ? operandTypeValues[ operand ].value : '' ) + '</strong>');
					}

					var condition = '<li class="list-group-item">';

					if ( '' != conditionsHtml ) {
						condition += '<span class="label label-default and">and</span>';
					}

					condition += conditions.join('<span class="label label-default or">or</span>') + '</li>';

					conditionsHtml += condition;
				}

				var actionsHtml = '';
				for ( var idx in workflow.actions ) {
					if ( ! workflow.actions.hasOwnProperty( idx ) ) {
						continue;
					}
					
					var singleCondition = workflow.actions[ idx ],
						actionId = singleCondition.actionID;

					var action = '<li class="list-group-item">';

					if ( '' != actionsHtml ) {
						action += '<span class="label label-default and">and</span>';
					}

					action += WORKFLOWS_SETTINGS.actions[ actionId ].value + '</li>';

					actionsHtml += action;
				}

				var eventTypesHtml = '';
				for ( var idx in workflow.eventTypes ) {
					if ( ! workflow.eventTypes.hasOwnProperty( idx ) ) {
						continue;
					}
					
					var eventTypeId = workflow.eventTypes[ idx ];

					var eventType = '<li class="list-group-item">';

					if ( '' != eventTypesHtml ) {
						eventType += '<span class="label label-default and">or</span>';
					}

					eventType += WORKFLOWS_SETTINGS.eventTypes[ eventTypeId ].value + '</li>';

					eventTypesHtml += eventType;
				}

				$workflowSummary.find('.workflow-title').text(workflow.name);
				$workflowSummary.find('.list-group:eq(0)').html(conditionsHtml);
				$workflowSummary.find('.list-group:eq(1)').html(actionsHtml);
				$workflowSummary.find('.list-group:eq(2)').html(eventTypesHtml);
				
				$('#edit-workflow').addClass('is-editing').attr('data-id', workflowId);
				$('a[href="#edit-workflow-summary"]').tab('show');
			});

			$('body').on('click', '.workflowCondActions .add-or, .workflowCondActions .remove-condition, .workflowCondActions .remove-action, .workflowCondActions .remove-event-type, .workflowCondActions .addCondition, .workflowCondActions .addAction, .workflowCondActions .addTrigger', function() {
				if ( $(this).hasClass('add-or') ) {
					var $newFacet = createFromTemplate('facet');
					var $facetList = $(this).prev();
					$facetList.append($newFacet);
				} else if( $(this).hasClass('addCondition') ) {
					var $editCondition = $(this).parents('.edit-conditions:first');
					var $newCondition = createFromTemplate('condition-template');

					var $lastCondition = $editCondition.find('.workflowCondActions > div:last');
					$lastCondition.find(' > p.and').replaceWith('<div><p class="and"><em>AND</em></p></div>');
					
					$editCondition.find('.workflowCondActions').append($newCondition);
					
					var $newFacet = createFromTemplate('facet');
					var $facetList = $newCondition.find('.facetList');
					$facetList.append($newFacet);
				} else if( $(this).hasClass('addAction') ) {
					var $editAction = $(this).parents('.edit-actions:first');
					var $newAction = createFromTemplate('action-template', true);

					var $lastAction = $editAction.find('.workflowCondActions > div:last');
					$lastAction.find(' > p.and').replaceWith('<div><p class="and"><em>AND</em></p></div>');
					
					$editAction.find('.workflowCondActions').append($newAction);
					$newAction.find('section.condAction .facet > .col').append( createFromTemplate('actions') );
				} else if( $(this).hasClass('addTrigger') ) {
					var $editTrigger = $(this).parents('.edit-event-types:first');
					var $newTrigger = createFromTemplate('event-type-template', true);

					var $lastTrigger = $editTrigger.find('.workflowCondActions > div:last');
					$lastTrigger.find(' > p.and').replaceWith('<div><p class="and"><em>OR</em></p></div>');
					
					$editTrigger.find('.workflowCondActions').append($newTrigger);
					$newTrigger.find('section.condAction .facet > .col').append( createFromTemplate('event-types') );
				} else if ( $(this).hasClass('remove-condition') ) {
					var $editCondition = $(this).parents('.edit-conditions:first'),
						$facetList = $(this).parents('.facetList:first'),
						totalFacets = $facetList.children('.facet').length;
					
					if ( totalFacets > 1 ) {
						var $facet = $(this).parents('.facet:first');
						var idx = $facet.index();

						$facet.remove();
						totalFacets--;

						if ( 0 === idx ) {
							$facet = $facetList.find('.facet:first');
							$facet.children('.wf-badge').remove();
						}
					} else {
						var totalList = $editCondition.find('.workflowCondActions > div').length;
						var $condition = $facetList.parents('div:first');
						var conditionIdx = $condition.index();
						if ( totalList > 1 ) {
							$condition.remove();
							if ( conditionIdx === totalList-1 ) {
								var $and = $editCondition.find('.workflowCondActions .and:last');
								$and.unwrap().html('<a href="javascript:void(0);" class="addCondition" tabindex="-1">+ AND</a>');
							}
						} else {
							var $facet = $facetList.find('.facet:first');
							$facet.children('.wf-badge').remove();
							$facet.children('.facetInputs').hide();
						}
					}
				} else if ( $(this).hasClass('remove-action') ) {
					var $editAction = $(this).parents('.edit-actions:first'),
						$facetList = $(this).parents('.facetList:first');
					
					var totalList = $editAction.find('.workflowCondActions > div').length;
					var $action = $facetList.parents('div:first');
					var actionIdx = $action.index();
					if ( totalList > 1 ) {
						$action.remove();
						if ( actionIdx === totalList-1 ) {
							var $and = $editAction.find('.workflowCondActions .and:last');
							$and.unwrap().html('<a href="javascript:void(0);" class="addAction" tabindex="-1">+ AND</a>');
						}
					} else {
						var $facet = $facetList.find('.facet:first');
						$facet.children('.wf-badge').remove();
						$facet.children('.facetInputs').hide();
					}
				} else if ( $(this).hasClass('remove-event-type') ) {
					var $editTrigger = $(this).parents('.edit-event-types:first'),
						$facetList = $(this).parents('.facetList:first');
					
					var totalList = $editTrigger.find('.workflowCondActions > div').length;
					var $eventType = $facetList.parents('div:first');
					var eventTypeIdx = $eventType.index();
					if ( totalList > 1 ) {
						$eventType.remove();
						if ( eventTypeIdx === totalList-1 ) {
							var $and = $editTrigger.find('.workflowCondActions .and:last');
							$and.unwrap().html('<a href="javascript:void(0);" class="addTrigger" tabindex="-1">+ OR</a>');
						}
					} else {
						var $facet = $facetList.find('.facet:first');
						$facet.children('.wf-badge').remove();
						$facet.children('.facetInputs').hide();
					}
				}
			});
		});
	}
	
	/**
	 * Updates the conditions of this workflow.
	 */
	function updateConditions( successCallback, completeCallback ) {
		var $currentTab = $('#edit-workflow > div > .tab-content .tab-pane.active'),
			allConditions = [];
	
		$currentTab.find('.workflowCondActions').children().each(function() {
			var conditions = [];
			$(this).find('.facetList').children().each(function() {
				var operandType = $(this).find('.operand-types').val();
				var operator = $(this).find('.operators').val();
				var operand = $(this).find('.facetInputs .col:last select').val();

				var condition = {
					operandType: operandType,
					operator: operator,
					operand: operand
				};

				conditions.push(condition);
			});

			if ( conditions.length > 0 ) {
				allConditions.push(conditions);
			}
		});

		$.ajax({
			url: ajaxurl,
			method: 'POST',
			data: {
				action: 'update-workflow',
				id: $('#edit-workflow').attr('data-id'),
				conditions: allConditions
			},
			success: function( result ) {
				WORKFLOWS_SETTINGS.workflows[ $('#edit-workflow').attr('data-id') ].conditions = jQuery.extend({}, allConditions);
				successCallback( JSON.parse(result) );
			},
			complete: function() {
				completeCallback();
			}
		});
	}
	
	function updateActions( successCallback, completeCallback ) {
		var $currentTab = $('#edit-workflow > div > .tab-content .tab-pane.active'),
			actions = [];
	
		$currentTab.find('.facetList').children().each(function() {
			actions.push({
				actionID: $(this).find('.actions').val()
			});
		});

		$.ajax({
			url: ajaxurl,
			method: 'POST',
			data: {
				action: 'update-workflow',
				id: $('#edit-workflow').attr('data-id'),
				actions: actions
			},
			success: function( result ) {
				WORKFLOWS_SETTINGS.workflows[ $('#edit-workflow').attr('data-id') ].actions = actions;
				successCallback( JSON.parse(result) );
			},
			complete: function() {
				completeCallback();
			}
		});
	}
	
	function updateTriggers( successCallback, completeCallback ) {
		var $currentTab = $('#edit-workflow > div > .tab-content .tab-pane.active'),
			event_types = [];
	
		$currentTab.find('.facetList').children().each(function() {
			event_types.push( $(this).find('.event-types').val() );
		});

		$.ajax({
			url: ajaxurl,
			method: 'POST',
			data: {
				action: 'update-workflow',
				id: $('#edit-workflow').attr('data-id'),
				event_types: event_types
			},
			success: function( result ) {
				WORKFLOWS_SETTINGS.workflows[ $('#edit-workflow').attr('data-id') ].eventTypes = event_types;
				successCallback( JSON.parse(result) );
			},
			complete: function() {
				completeCallback();
			}
		});
	}

	/**
	 * Updates the contents (conditions, actions, and event types) of the summary view
	 */
	function updateSummary() {
		var $workflowSummary = $('#edit-workflow-summary').find('.workflow-summary');

		if ( 0 === $workflowSummary.length ) {
			$workflowSummary = createFromTemplate('workflow-summary');
			$('#edit-workflow-summary').prepend( $workflowSummary );
		} else {
			$workflowSummary.find('.list-group').html('');
		}

		var workflowId = $('#edit-workflow').data('id');
		var workflow = WORKFLOWS_SETTINGS.workflows[ workflowId ];

		var conditionsHtml = '';
		for ( var idx in workflow.conditions ) {
			if ( ! workflow.conditions.hasOwnProperty( idx ) ) {
				continue;
			}

			var conditions = [];

			var conditionsArr = workflow.conditions[ idx ];
			for ( var idx2 in conditionsArr ) {
				if ( ! conditionsArr.hasOwnProperty( idx2 ) ) {
					continue;
				}

				var singleCondition = conditionsArr[ idx2 ],
					operandId = singleCondition.operandType,
					operatorId = singleCondition.operator,
					operand = singleCondition.operand,
					condition = '';

				conditions.push(
					WORKFLOWS_SETTINGS.operandTypes[ operandId ].title
					+ ' ' + WORKFLOWS_SETTINGS.operators[ operatorId ].title
					+ ' ' + '<strong>' + WORKFLOWS_SETTINGS.operandTypes[ operandId ].value[ operand ].value + '</strong>');
			}

			var condition = '<li class="list-group-item">';

			if ( '' != conditionsHtml ) {
				condition += '<span class="label label-default and">and</span>';
			}

			condition += conditions.join('<span class="label label-default or">or</span>') + '</li>';

			conditionsHtml += condition;
		}

		var actionsHtml = '';
		for ( var idx in workflow.actions ) {
			if ( ! workflow.actions.hasOwnProperty( idx ) ) {
				continue;
			}

			var singleCondition = workflow.actions[ idx ],
				actionId = singleCondition.actionID;

			var action = '<li class="list-group-item">';

			if ( '' != actionsHtml ) {
				action += '<span class="label label-default and">and</span>';
			}

			action += WORKFLOWS_SETTINGS.actions[ actionId ].value + '</li>';

			actionsHtml += action;
		}

		var eventTypesHtml = '';
		for ( var idx in workflow.eventTypes ) {
			if ( ! workflow.eventTypes.hasOwnProperty( idx ) ) {
				continue;
			}

			var eventTypeId = workflow.eventTypes[ idx ];

			var eventType = '<li class="list-group-item">';

			if ( '' != eventTypesHtml ) {
				eventType += '<span class="label label-default and">or</span>';
			}

			eventType += WORKFLOWS_SETTINGS.eventTypes[ eventTypeId ].value + '</li>';

			eventTypesHtml += eventType;
		}

		$workflowSummary.find('.workflow-title').text(workflow.name);
		$workflowSummary.find('.list-group:eq(0)').html(conditionsHtml);
		$workflowSummary.find('.list-group:eq(1)').html(actionsHtml);
		$workflowSummary.find('.list-group:eq(2)').html(eventTypesHtml);
	}
	
	/**
	 * Displays the workflow summary: If ... Then ... When ...
	 */
	function showSummary() {
		$('.nav-pills').children().removeClass('active');
		$('a[href="#edit-workflow-summary"]').attr('data-toggle', 'tab').tab('show');
	}
	
	/**
	 * Clones a template element and sets the cloned element's ID and class based on the templates data attributes.
	 * 
	 * @returns object jQuery element object
	 */
	function createFromTemplate( templateClass, defaultValue ) {
		var $template = $('.workflow-template[data-class="' + templateClass + '"]').clone();
		$template.attr('class', templateClass);
		
		if ( $template.data('id') ) {
			$template.attr('id', $template.data('id'));
		}
		
		if ( defaultValue ) {
			$template.val( defaultValue );
		}
		
		return $template;
	}
	
	/**
	 * Creates the list of workflows and populates the operators dropdown list.
	 *
	 */
	function initWorkflows() {
		populateWorkflowsList();
		populateDropdownTemplate( 'actions', WORKFLOWS_SETTINGS.actions );
		populateDropdownTemplate( 'event-types', WORKFLOWS_SETTINGS.eventTypes );
		
		var $form	 = $('#rw-workflows-page'),
			$operandType = $('select.workflow-template[data-class="operand-types"]'),
			operands = WORKFLOWS_SETTINGS.operandTypes;
	
		for ( var operandId in operands ) {
			if ( ! operands.hasOwnProperty( operandId ) ) {
				continue;
			}

			var operand = operands[ operandId ];

			$operandType.append('<option value="' + operandId + '" data-slug="' + operand.slug + '">' + operand.title + '</option>');

			$form.append('<select class="workflow-template" data-class="' + operand.slug + '"></select>');

			populateDropdownTemplate( operand.slug, operand.value );
		}
		
		$('.workflow-template[data-class="facet"] > .col').append( createFromTemplate('operand-types') );
	}
	
	/**
	 * Creates the items of the workflows list. 
	 */
	function populateWorkflowsList() {
		var workflows		= WORKFLOWS_SETTINGS.workflows,
			$workflowsPanel = $('#workflows > .panel'),
			$listGroup		= $workflowsPanel.children('.list-group');

		if ( Object.keys( workflows ).length > 0 ) {
			$workflowsPanel.show().find( '> .panel-heading' ).text( WORKFLOWS_SETTINGS.text.has_workflows );
			
			var workflowIds = WORKFLOWS_SETTINGS.workflows_id_order;
			if ( ! workflowIds || 0 === workflowIds.length ) {
				workflowIds = Object.keys( workflows );
			}

			for ( var idx in workflowIds ) {
				var workflowId	= workflowIds[ idx ],
					workflow	= workflows[ workflowId ];

				var $listGroupItemTemplate = $('#list-group-item-template').clone();
				$listGroupItemTemplate.attr({
					'id' : '',
					'class' : 'list-group-item',
					'data-workflow-id' : workflowId
				});

				$listGroupItemTemplate.find('.list-group-item-content').html(workflow.name);

				$listGroup.append($listGroupItemTemplate);
			}
		} else {
			$workflowsPanel.find( '> .panel-heading' ).text( WORKFLOWS_SETTINGS.text.no_workflows );
		}
	}
	
	/**
	 * Populates a select HTML element based on the given HTML element class and values array.
	 * 
	 * @param String elementClass The HTML element's class
	 * @param Array itemsArray An array of values that will be added as options of the select HTML element.
	 */
	function populateDropdownTemplate( elementClass, itemsArray ) {
		var $element = $('select.workflow-template[data-class="' + elementClass + '"]');
		
		$element.html('');

		for ( var idx in itemsArray ) {
			if ( ! itemsArray.hasOwnProperty( idx ) ) {
				continue;
			}

			var itemValue	= itemsArray[ idx ];
					
			$element.append('<option value="' + itemValue.ID + '">' + itemValue.value +'</option>');
		}
	}
}) (jQuery);