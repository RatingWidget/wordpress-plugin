<?php
    /**
     * Template file called from RW_Workflows->_workflows_page_render method.
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
<div class="wrap rw-dir-ltr">
	<form id="workflows-page" method="post" action="">
		<div id="poststuff">
			<div class="postbox rw-body">
				<div class="inside rw-no-radius">
					<div>
						<div class="tab-content">
							<div id="workflows" class="tab-pane active">
								<p><button data-toggle="tab" href="#edit-workflow" id="new-workflow" class="button button-primary"><?php _e( 'New Workflow', WP_WF__SLUG ); ?></button></p>
								<div class="panel panel-default">
									<div class="panel-heading"></div>
									<div class="panel-body">
										<p><?php _e( 'To change the order, drag the target workflow and drop into the desired position.', WP_WF__SLUG ); ?></p>
									</div>
									<div class="list-group"></div>
								</div>
							</div>
							<div id="edit-workflow" class="tab-pane">
								<div class="form-horizontal">
									<ul class="nav nav-pills">
										<li class="active"><a href="#edit-name" data-toggle="tab"><?php _e( 'Name', WP_WF__SLUG ); ?></a></li>
										<li class="disabled"><a href="#edit-conditions"><?php _e( 'Conditions', WP_WF__SLUG ); ?></a></li>
										<li class="disabled"><a href="#edit-actions"><?php _e( 'Actions', WP_WF__SLUG ); ?></a></li>
										<li class="disabled"><a href="#edit-events"><?php _e( 'Events', WP_WF__SLUG ); ?></a></li>
										<li class="disabled"><a href="#edit-summary"><?php _e( 'Summary', WP_WF__SLUG ); ?></a></li>
									</ul>
									<div class="tab-content">
										<div id="edit-name" class="tab-pane workflow-step active" data-step="edit-workflow-name" data-next-step="#edit-conditions">
											<h3><span><?php _e( 'Workflow Name', WP_WF__SLUG ); ?></span></h3>
											<div class="form-group">
												<div class="col-sm-5">
													<input type="text" class="form-control" id="workflow-name" placeholder="">
												</div>
											</div>
											<div class="form-group">
												<div class="col-sm-5">
													<button type="submit" class="button button-secondary next-step" data-loading-text="Saving..." ><?php _e(' Next Step', WP_WF__SLUG ); ?></button>
													<button type="submit" class="button button-secondary save" href="#edit-summary"><?php _e( 'Update Name', WP_WF__SLUG ); ?></button>
													<button type="submit" class="button cancel-save" href="#edit-summary" data-toggle="tab"><?php _e( 'Cancel', WP_WF__SLUG ); ?></button>
												</div>
											</div>
										</div>
										<div id="edit-conditions" class="tab-pane workflow-step">
											<button type="submit" class="button button-secondary next-step" href="#edit-actions"><?php _e( 'Next Step', WP_WF__SLUG ) ; ?></button>
											<button type="submit" class="button button-secondary save" href="#edit-summary"><?php _e( 'Update Conditions', WP_WF__SLUG ); ?></button>
											<button type="submit" class="button cancel-save" href="#edit-summary" data-toggle="tab"><?php _e( 'Cancel', WP_WF__SLUG ); ?></button>
											<p></p>
										</div>
										<div id="edit-actions" class="tab-pane workflow-step">
											<button type="submit" class="button button-secondary next-step" href="#edit-events"><?php _e( 'Next Step', WP_WF__SLUG ); ?></button>
											<button type="submit" class="button button-secondary save" href="#edit-summary"><?php _e( 'Update Actions', WP_WF__SLUG ); ?></button>
											<button type="submit" class="button cancel-save" href="#edit-summary" data-toggle="tab"><?php _e( 'Cancel', WP_WF__SLUG ); ?></button>
											<p></p>
										</div>
										<div id="edit-events" class="tab-pane workflow-step">
											<button type="submit" class="button button-secondary next-step" href="#edit-summary"><?php _e( 'Next Step', WP_WF__SLUG ); ?></button>
											<button type="submit" class="button button-secondary save" href="#edit-summary"><?php _e( 'Update Events', WP_WF__SLUG ); ?></button>
											<button type="submit" class="button cancel-save" href="#edit-summary" data-toggle="tab"><?php _e( 'Cancel', WP_WF__SLUG ); ?></button>
											<p></p>
										</div>
										<div id="edit-summary" class="tab-pane workflow-step">
											<button type="submit" class="button button-secondary activate-workflow"><?php _e( 'Activate', WP_WF__SLUG ); ?></button>
											<button type="submit" class="button button-secondary view-workflows" href="#workflows" data-toggle="tab"><?php _e( 'View all Workflows', WP_WF__SLUG ); ?></button>
											<p></p>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- MODALS -->
		<div class="rw-wf-modal no-body" id="confirm-delete-workflow" aria-hidden="true">
			<div class="rw-wf-modal-dialog">
				<div class="rw-wf-modal-header">
					<p><?php _e( 'Are you sure you would like to delete this workflow?', WP_WF__SLUG ); ?></p>
					<a href="#close" class="rw-wf-button-close" aria-hidden="true">&times;</a>
				</div>
				<div class="rw-wf-modal-footer">
					<button href="#" class="button button-primary"><?php _e( 'Delete', WP_WF__SLUG ); ?></button>
					<button href="#" class="button button-close"><?php _e( 'Cancel', WP_WF__SLUG ); ?></button>
				</div>
			</div>
		</div>
		
		<div class="rw-wf-modal no-body" id="information-message" aria-hidden="true">
			<div class="rw-wf-modal-dialog">
				<div class="rw-wf-modal-header">
					<p></p>
					<a href="#close" class="rw-wf-button-close" aria-hidden="true">&times;</a>
				</div>
				<div class="rw-wf-modal-footer">
					<button href="#" class="button button-close"><?php _e( 'Close', WP_WF__SLUG ); ?></button>
				</div>
			</div>
		</div>
		
		<!-- TEMPLATES -->
		<!-- Edit workflow -->
		<a class="workflow-template" href="#edit-workflow" data-class="list-group-item">
			<span class="pull-right">
				<button type="button" class="button button-small remove-workflow">
					<span class="glyphicon glyphicon-trash" aria-hidden="true"></span>
				</button>
				<label>
					<input type="checkbox" class="workflow-state" />
					<div class="switch"></div>
				</label>
			</span>
			<div class="list-group-item-content"></div>
		</a>
		
		<!-- Edit conditions -->
		<div class="workflow-template" data-class="edit-conditions">
			<h3><span><?php _e( 'Conditions', WP_WF__SLUG ); ?></span></h3>
			<div class="panel panel-default">
				<div class="panel-heading">
				</div>
			</div>
		</div>
		
		<!-- Edit actions -->
		<div class="workflow-template" data-class="edit-actions">
			<h3><span><?php _e( 'Actions', WP_WF__SLUG ); ?></span></h3>
			<div class="panel panel-default">
				<div class="panel-heading">
					<div class="and-operation-container">
						<div class="operations-container">
							<div class="operations-list">
								<div class="operation">
									<div class="col"></div>
									<div class="actions pull-right">
										<button type="button" class="button button-small">
											<span class="glyphicon glyphicon-minus-sign remove-operation"></span>
										</button>
									</div>
								</div>
							</div>
						</div>
						<p class="and-operation">
							<a href="javascript:void(0);" class="add-operation" tabindex="-1"><?php _e( '+ AND', WP_WF__SLUG ); ?></a>
						</p>
					</div>
				</div>
			</div>
		</div>
		
		<!-- Edit event types -->
		<div class="workflow-template" data-class="edit-events">
			<h3><span><?php _e( 'Events', WP_WF__SLUG ); ?></span></h3>
			<div class="panel panel-default">
				<div class="panel-heading">
					<div class="and-operation-container">
						<div class="operations-container">
							<div class="operations-list">
								<div class="operation">
									<div class="col"></div>
									<div class="actions pull-right">
										<button type="button" class="button button-small">
											<span class="glyphicon glyphicon-minus-sign remove-operation" aria-hidden="true"></span>
										</button>
									</div>
								</div>
							</div>
						</div>
						<p class="and-operation">
							<a href="javascript:void(0);" class="add-operation" tabindex="-1"><?php _e( '+ OR', WP_WF__SLUG ); ?></a>
						</p>
					</div>
				</div>
			</div>
		</div>
		
		<!-- Workflow summary -->
		<div class="workflow-template" data-class="workflow-summary">
			<h3 class="workflow-title"><span></span> <span class="label label-default label-workflow-status"></span></h3>
			<div class="panel panel-default">
				<div class="panel-heading">
					<!-- IF part of the operation -->
					<div>
						<span class="pull-right">
							<button class="button button-primary button-small edit-workflow edit-conditions" data-toggle="tab" href="#edit-conditions"><?php _e( 'Edit', WP_WF__SLUG ); ?></button>
						</span>
						<div><?php _e( 'If ...', WP_WF__SLUG ); ?></div>
					</div>
					<ul class="list-group"></ul>
					
					<!-- THEN part of the operation -->
					<div>
						<span class="pull-right">
							<button class="button button-primary button-small edit-workflow edit-actions" data-toggle="tab" href="#edit-actions"><?php _e( 'Edit', WP_WF__SLUG ); ?></button>
						</span>
						<div><?php _e( 'Then ...', WP_WF__SLUG ); ?></div>
					</div>
					<ul class="list-group"></ul>
					
					<!-- WHEN part of the operation -->
					<div>
						<span class="pull-right">
							<button class="button button-primary button-small edit-workflow edit-events" data-toggle="tab" href="#edit-events"><?php _e( 'Edit', WP_WF__SLUG ); ?></button>
						</span>
						<div><?php _e( 'When ...', WP_WF__SLUG ); ?></div>
					</div>
					<ul class="list-group"></ul>
				</div>
			</div>
		</div>
		
		<!-- Single operation -->
		<div class="workflow-template" data-class="operation">
			<i class="badge or"><?php _e( 'or', WP_WF__SLUG ); ?></i>
			<div class="col"></div>
			<div class="operation-inputs">
				<div>
					<div class="col">
						<select class="operator"></select>
					</div>
					<div class="col">
						<select class="value"></select>
					</div>
				</div>
			</div>
			<div class="actions pull-right">
				<button type="button" class="button button-small">
					<span class="glyphicon glyphicon-minus-sign remove-operation" aria-hidden="true"></span>
				</button>
			</div>
		</div>
		
		<!-- Single condition -->
		<div class="workflow-template" data-class="condition and-operation-container">
			<div class="operations-container">
				<div class="operations-list">
				</div>
				<div class="add-or">
					<a href="javascript:void(0)" class="add add-operation" tabindex="-1">+ OR</a>
				</div>
			</div>
			<p class="and-operation">
				<a href="javascript:void(0);" class="add-operation" tabindex="-1"><?php _e( '+ AND', WP_WF__SLUG ); ?></a>
			</p>
		</div>
		
		<!-- Single action -->
		<div class="workflow-template" data-class="action-template operations-container">
			<div class="operations-container">
				<div class="operations-list">
					<div class="operation">
						<div class="col"></div>
						<div class="actions pull-right">
							<button type="button" class="button button-small">
								<span class="glyphicon glyphicon-minus-sign remove-operation"></span>
							</button>
						</div>
					</div>
				</div>
			</div>
			<p class="and-operation">
				<a href="javascript:void(0);" class="add-operation" tabindex="-1"><?php _e( '+ AND', WP_WF__SLUG ); ?></a>
			</p>
		</div>
		
		<!-- Single event type -->
		<div class="workflow-template" data-class="event-type-template">
			<div class="operations-container">
				<div class="operations-list">
					<div class="operation">
						<div class="col"></div>
						<div class="actions pull-right">
							<button type="button" class="button button-small">
								<span class="glyphicon glyphicon-minus-sign remove-operation"></span>
							</button>
						</div>
					</div>
				</div>
			</div>
			<p class="and-operation">
				<a href="javascript:void(0);" class="add-operation" tabindex="-1"><?php _e( '+ OR', WP_WF__SLUG ); ?></a>
			</p>
		</div>
	</form>
</div>