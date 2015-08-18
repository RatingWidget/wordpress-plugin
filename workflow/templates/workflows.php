<?php
/*
 * The template view for the Workflows page.
 */

$slug = $VARS['slug'];
$wf = wf($slug);
?>
<div class="wrap rw-dir-ltr">
	<form id="rw-workflows-page" method="post" action="">
		<div id="poststuff">
			<div class="postbox rw-body">
				<div class="inside rw-no-radius">
					<div>
						<div class="tab-content">
							<div id="workflows" class="tab-pane active">
								<p><button data-toggle="tab" href="#edit-workflow" id="new-workflow-btn" class="btn btn-primary"><?php _e( 'New Workflow', WP_WF__SLUG ); ?></button></p>
								<div class="panel panel-default">
									<div class="panel-heading"></div>
									<div class="panel-body">
										<p><?php _e( 'To change the order, drag the target workflow and drop into the desired position.', WP_WF__SLUG ); ?></p>
									</div>
									<div class="list-group">
									</div>
								</div>
							</div>
							<div id="edit-workflow" class="tab-pane">
								<div class="form-horizontal">
									<ul class="nav nav-pills">
										<li class="active"><a href="#edit-workflow-step1" data-toggle="tab"><?php _e( 'Name', WP_WF__SLUG ); ?></a></li>
										<li class="disabled"><a href="#edit-workflow-step2"><?php _e( 'Conditions', WP_WF__SLUG ); ?></a></li>
										<li class="disabled"><a href="#edit-workflow-step3"><?php _e( 'Actions', WP_WF__SLUG ); ?></a></li>
										<li class="disabled"><a href="#edit-workflow-step4"><?php _e( 'Triggers', WP_WF__SLUG ); ?></a></li>
										<li class="disabled"><a href="#edit-workflow-summary"><?php _e( 'Summary', WP_WF__SLUG ); ?></a></li>
									</ul>
									<div class="tab-content">
										<div id="edit-workflow-step1" class="tab-pane workflow-step active" data-step="enter-workflow-name">
											<h3><span><?php _e( 'Workflow Name', WP_WF__SLUG ); ?></span></h3>
											<div class="form-group">
												<div class="col-sm-5">
													<input type="text" class="form-control" id="workflow-name" placeholder="">
												</div>
											</div>
											<div class="form-group">
												<div class="col-sm-5">
													<button type="submit" class="btn btn-default edit-workflow-next-step" data-next-tab="#edit-workflow-step2" data-loading-text="Saving..." ><?php _e(' Next Step', WP_WF__SLUG ); ?></button>
													<button type="submit" class="btn btn-default save-name" href="#edit-workflow-summary"><?php _e( 'Update Name', WP_WF__SLUG ); ?></button>
													<button type="submit" class="btn btn-link cancel-save" href="#edit-workflow-summary" data-toggle="tab"><?php _e( 'Cancel', WP_WF__SLUG ); ?></button>
												</div>
											</div>
										</div>
										<div id="edit-workflow-step2" class="tab-pane workflow-step" data-step="select-condition">
											<button type="submit" class="btn btn-default edit-workflow-next-step" data-next-step="select-action" href="#edit-workflow-step3"><?php _e( 'Next Step', WP_WF__SLUG ) ; ?></button>
											<button type="submit" class="btn btn-default save-conditions" href="#edit-workflow-summary"><?php _e( 'Update Conditions', WP_WF__SLUG ); ?></button>
											<button type="submit" class="btn btn-link cancel-save" href="#edit-workflow-summary" data-toggle="tab"><?php _e( 'Cancel', WP_WF__SLUG ); ?></button>
											<p class="" style="margin-top: 20px;"></p>
										</div>
										<div id="edit-workflow-step3" class="tab-pane workflow-step" data-step="select-action">
											<button type="submit" class="btn btn-default edit-workflow-next-step" data-next-step="show-event-type" href="#edit-workflow-step4"><?php _e( 'Next Step', WP_WF__SLUG ); ?></button>
											<button type="submit" class="btn btn-default save-actions" href="#edit-workflow-summary"><?php _e( 'Update Actions', WP_WF__SLUG ); ?></button>
											<button type="submit" class="btn btn-link cancel-save" href="#edit-workflow-summary" data-toggle="tab"><?php _e( 'Cancel', WP_WF__SLUG ); ?></button>
											<p class="" style="margin-top: 20px;"></p>
										</div>
										<div id="edit-workflow-step4" class="tab-pane workflow-step" data-step="select-event-type">
											<button type="submit" class="btn btn-default edit-workflow-next-step" data-next-step="show-summary" href="#edit-workflow-summary"><?php _e( 'Next Step', WP_WF__SLUG ); ?></button>
											<button type="submit" class="btn btn-default save-event-types" href="#edit-workflow-summary"><?php _e( 'Update Triggers', WP_WF__SLUG ); ?></button>
											<button type="submit" class="btn btn-link cancel-save" href="#edit-workflow-summary" data-toggle="tab"><?php _e( 'Cancel', WP_WF__SLUG ); ?></button>
											<p class="" style="margin-top: 20px;"></p>
										</div>
										<div id="edit-workflow-summary" class="tab-pane workflow-step" data-step="show-summary">
											<button type="submit" class="btn btn-default activate-workflow"><?php _e( 'Activate', WP_WF__SLUG ); ?></button>
											<button type="submit" class="btn btn-default view-workflows" href="#workflows" data-toggle="tab"><?php _e( 'View all Workflows', WP_WF__SLUG ); ?></button>
											<p class="" style="margin-top: 20px;"></p>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- MODAL -->
		<div id="confirm-delete-workflow" class="modal fade" data-target-workflow-id="" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-body">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<?php _e('Are you sure you would like to delete this workflow?', WP_WF__SLUG); ?>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-primary">Delete</button>
						<button type="button" class="btn btn-default" data-dismiss="modal"><?php _e( 'Cancel', WP_WF__SLUG ); ?></button>
					</div>
				</div>
			</div>
		</div>
		
		<!-- TEMPLATES -->
		<a id="list-group-item-template" class="workflow-template" href="#edit-workflow" data-toggle="tab">
			<span class="pull-right">
				<button type="button" class="btn btn-link">
					<span class="glyphicon glyphicon-trash" aria-hidden="true"></span>
				</button>
			</span>
			<div class="list-group-item-content"></div>
		</a>
		<select class="workflow-template" data-class="operators"></select>
		<select class="workflow-template" data-class="actions"></select>
		<select class="workflow-template" data-class="event-types"></select>
		<select class="workflow-template" data-class="operand-types">
			<option value="-1"><?php _e( 'Select a Condition ...', WP_WF__SLUG ); ?></option>
		</select>
		<div class="workflow-template" data-class="edit-conditions">
			<h3><span><?php _e( 'Conditions', WP_WF__SLUG ); ?></span></h3>
			<div class="panel panel-default">
				<div class="panel-heading">
					<section class="workflowCondActions">
					</section>
				</div>
			</div>
		</div>
		<div class="workflow-template" data-class="edit-actions">
			<h3><span><?php _e( 'Actions', WP_WF__SLUG ); ?></span></h3>
			<div class="panel panel-default">
				<div class="panel-heading">
					<section class="workflowCondActions">
						<div>
							<section class="condAction">
								<div class="facetList">
									<div class="facet">
										<div class="col">
										</div>
										<div class="actions pull-right">
											<button type="button" class="btn btn-link btn-lg" style="padding-top: 0; padding-bottom: 0">
												<span class="glyphicon glyphicon-minus-sign remove-action" aria-hidden="true" style="vertical-align: middle;"></span>
											</button>
										</div>
									</div>
								</div>
							</section>
							<p class="and">
								<a href="javascript:void(0);" class="addAction" tabindex="-1"><?php _e( '+ AND', WP_WF__SLUG ); ?></a>
							</p>
						</div>
					</section>
				</div>
			</div>
		</div>
		<div class="workflow-template" data-class="edit-event-types">
			<h3><span><?php _e( 'Triggers', WP_WF__SLUG ); ?></span></h3>
			<div class="panel panel-default">
				<div class="panel-heading">
					<section class="workflowCondActions">
						<div>
							<section class="condAction">
								<div class="facetList">
									<div class="facet">
										<div class="col">
										</div>
										<div class="actions pull-right">
											<button type="button" class="btn btn-link btn-lg" style="padding-top: 0; padding-bottom: 0">
												<span class="glyphicon glyphicon-minus-sign remove-event-type" aria-hidden="true" style="vertical-align: middle;"></span>
											</button>
										</div>
									</div>
								</div>
							</section>
							<p class="and">
								<a href="javascript:void(0);" class="addTrigger" tabindex="-1"><?php _e( '+ OR', WP_WF__SLUG ); ?></a>
							</p>
						</div>
					</section>
				</div>
			</div>
		</div>
		<div class="workflow-template" data-class="workflow-summary">
			<h3 class="workflow-title"><span></span> <span class="label label-default label-workflow-status"></span></h3>
			<div class="panel panel-default">
				<div class="panel-heading">
					<div><span class="pull-right"><button class="btn btn-primary btn-xs edit-workflow edit-conditions" data-toggle="tab" href="#edit-workflow-step2"><?php _e( 'Edit', WP_WF__SLUG ); ?></button></span><div><?php _e( 'If ...', WP_WF__SLUG ); ?></div></div>
					<ul class="list-group"></ul>
					<div><span class="pull-right"><button class="btn btn-primary btn-xs edit-workflow edit-actions" data-toggle="tab" href="#edit-workflow-step3"><?php _e( 'Edit', WP_WF__SLUG ); ?></button></span><div><?php _e( 'Then ...', WP_WF__SLUG ); ?></div></div>
					<ul class="list-group"></ul>
					<div><span class="pull-right"><button class="btn btn-primary btn-xs edit-workflow edit-event-types" data-toggle="tab" href="#edit-workflow-step4"><?php _e( 'Edit', WP_WF__SLUG ); ?></button></span><div><?php _e( 'When ...', WP_WF__SLUG ); ?></div></div>
					<ul class="list-group"></ul>
				</div>
			</div>
		</div>
		<div class="workflow-template" data-class="facet">
			<i class="wf-badge or"><?php _e( 'or', WP_WF__SLUG ); ?></i>
			<div class="col">
			</div>
			<div class="facetInputs">
				<div>
					<div class="col">
						<select class="operator">
						</select>
					</div>
					<div class="col">
						<select class="value">
						</select>
					</div>
				</div>
			</div>
			<div class="actions pull-right">
				<button type="button" class="btn btn-link btn-lg" style="padding-top: 0; padding-bottom: 0">
					<span class="glyphicon glyphicon-minus-sign remove-condition" aria-hidden="true" style="vertical-align: middle;"></span>
				</button>
			</div>
		</div>
		<div class="workflow-template" data-class="action-template">
			<section class="condAction">
				<div class="facetList">
					<div class="facet">
						<div class="col">
						</div>
						<div class="actions pull-right">
							<button type="button" class="btn btn-link btn-lg" style="padding-top: 0; padding-bottom: 0">
								<span class="glyphicon glyphicon-minus-sign remove-action" aria-hidden="true" style="vertical-align: middle;"></span>
							</button>
						</div>
					</div>
				</div>
			</section>
			<p class="and">
				<a href="javascript:void(0);" class="addAction" tabindex="-1"><?php _e( '+ AND', WP_WF__SLUG ); ?></a>
			</p>
		</div>
		<div class="workflow-template" data-class="event-type-template">
			<section class="condAction">
				<div class="facetList">
					<div class="facet">
						<div class="col">
						</div>
						<div class="actions pull-right">
							<button type="button" class="btn btn-link btn-lg" style="padding-top: 0; padding-bottom: 0">
								<span class="glyphicon glyphicon-minus-sign remove-event-type" aria-hidden="true" style="vertical-align: middle;"></span>
							</button>
						</div>
					</div>
				</div>
			</section>
			<p class="and">
				<a href="javascript:void(0);" class="addTrigger" tabindex="-1"><?php _e( '+ OR', WP_WF__SLUG ); ?></a>
			</p>
		</div>
		<div class="workflow-template" data-class="condition-template">
			<section class="condAction">
				<div class="facetList">
				</div>
				<div class="add-or">
					<a href="javascript:void(0)" class="add addFacet" tabindex="-1"><?php _e( '+ OR', WP_WF__SLUG ); ?></a>
				</div>
			</section>
			<p class="and">
				<a href="javascript:void(0);" class="addCondition" tabindex="-1"><?php _e( '+ AND', WP_WF__SLUG ); ?></a>
			</p>
		</div>
	</form>
</div>