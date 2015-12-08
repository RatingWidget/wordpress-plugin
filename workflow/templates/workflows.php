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
<div class="wrap rw-dir-ltr rw-wp-container">
<h2 class="nav-tab-wrapper rw-nav-tab-wrapper">
	<a href="#" class="nav-tab nav-tab-active"><?php _erw( 'workflows' ) ?></a>
	<a target="_blank" href="https://rating-widget.com/support/get-started-with-workflows/"
	   class="nav-tab"><?php _erw( 'workflows_get-started', WP_WF__SLUG ); ?></a>
</h2>

<form id="workflows-page" method="post" action="">
<div id="poststuff">
	<div class="postbox rw-body">
		<div class="inside rw-no-radius">
			<div>
				<div class="tab-content">
					<div id="workflows" class="tab-pane active">
						<p>
							<button data-toggle="tab" href="#edit-workflow" id="new-workflow"
							        class="button button-primary"><?php _erw( 'new-workflow' ) ?></button>
						</p>
						<div class="panel panel-default">
							<div class="panel-heading"></div>
							<div class="panel-body">
								<p><?php _erw( 'workflows_change-order' ) ?></p>
							</div>
							<div class="list-group"></div>
						</div>
					</div>
					<div id="edit-workflow" class="tab-pane">
						<div class="form-horizontal">
							<ul class="nav nav-pills">
								<li class="active"><a href="#edit-name"
								                      data-toggle="tab"><?php _erw( 'name' ) ?></a></li>
								<li class="disabled"><a
										href="#edit-conditions"><?php _erw( 'conditions' ) ?></a></li>
								<li class="disabled"><a
										href="#edit-actions"><?php _erw( 'actions' ) ?></a>
								</li>
								<li class="disabled"><a href="#edit-events"><?php _erw( 'events' ) ?></a>
								</li>
								<li class="disabled"><a
										href="#edit-summary"><?php _erw( 'summary' ) ?></a>
								</li>
							</ul>
							<div class="tab-content">
								<div id="edit-name" class="tab-pane workflow-step active" data-step="edit-workflow-name"
								     data-next-step="#edit-conditions">
									<h3><span><?php _erw( 'workflow-name' ) ?></span></h3>

									<div class="form-group">
										<div class="col-sm-5">
											<input type="text" class="form-control" id="workflow-name" placeholder="">
										</div>
									</div>
									<div class="form-group">
										<div class="col-sm-5">
											<button type="submit" class="button button-secondary next-step"
											        data-loading-text="<?php _erw( 'saving' ) ?>"><?php _erw( 'next-step' ) ?></button>
											<button type="submit" class="button button-secondary save"
											        href="#edit-summary"><?php _erw( 'update-name' ) ?></button>
											<button type="submit" class="button cancel-save" href="#edit-summary"
											        data-toggle="tab"><?php _erw( 'cancel' ) ?></button>
										</div>
									</div>
								</div>
								<div id="edit-conditions" class="tab-pane workflow-step">
									<button type="submit" class="button button-secondary next-step"
									        href="#edit-actions" data-loading-text="<?php _erw( 'saving' ) ?>"><?php _erw( 'next-step' ) ?></button>
									<button type="submit" class="button button-secondary save"
									        href="#edit-summary"><?php _erw( 'update-conditions' ) ?></button>
									<button type="submit" class="button cancel-save" href="#edit-summary"
									        data-toggle="tab"><?php _erw( 'cancel' ) ?></button>
									<p></p>
								</div>
								<div id="edit-actions" class="tab-pane workflow-step">
									<button type="submit" class="button button-secondary next-step"
									        href="#edit-events" data-loading-text="<?php _erw( 'saving' ) ?>"><?php _erw( 'next-step' ) ?></button>
									<button type="submit" class="button button-secondary save"
									        href="#edit-summary"><?php _erw( 'update-actions' ) ?></button>
									<button type="submit" class="button cancel-save" href="#edit-summary"
									        data-toggle="tab"><?php _erw( 'cancel' ) ?></button>
									<p></p>
								</div>
								<div id="edit-events" class="tab-pane workflow-step">
									<button type="submit" class="button button-secondary next-step"
									        href="#edit-summary" data-loading-text="<?php _erw( 'saving' ) ?>"><?php _erw( 'next-step' ) ?></button>
									<button type="submit" class="button button-secondary save"
									        href="#edit-summary"><?php _erw( 'update-events' ) ?></button>
									<button type="submit" class="button cancel-save" href="#edit-summary"
									        data-toggle="tab"><?php _erw( 'cancel' ) ?></button>
									<p></p>
								</div>
								<div id="edit-summary" class="tab-pane workflow-step">
									<button type="submit"
									        class="button button-secondary activate-workflow"><?php _erw( 'activate' ) ?></button>
									<button type="submit" class="button button-secondary view-workflows"
									        href="#workflows"
									        data-toggle="tab"><?php _erw( 'view-all-workflows' ) ?></button>
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

<!-- TEMPLATES -->
<!-- Edit workflow -->
<a class="workflow-template" href="#edit-workflow" data-class="list-group-item">
			<span class="pull-right">
				<button type="button" class="button button-small remove-workflow">
					<span class="glyphicon glyphicon-trash" aria-hidden="true"></span>
				</button>
				<label>
					<input type="checkbox" class="workflow-state"/>

					<div class="switch"></div>
				</label>
			</span>

	<div class="list-group-item-content"></div>
</a>

<!-- Edit conditions -->
<div class="workflow-template" data-class="edit-conditions">
	<h3><span><?php _erw( 'conditions' ) ?></span></h3>

	<div class="panel panel-default">
		<div class="panel-heading">
		</div>
	</div>
</div>

<!-- Edit actions -->
<div class="workflow-template" data-class="edit-actions">
	<h3><span><?php _erw( 'actions' ) ?></span></h3>

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
					<a href="javascript:void(0);" class="add-operation"
					   tabindex="-1">+ <?php echo strtoupper( __rw( 'and' ) ) ?></a>
				</p>
			</div>
		</div>
	</div>
</div>

<!-- Edit event types -->
<div class="workflow-template" data-class="edit-events">
	<h3><span><?php _erw( 'events' ) ?></span></h3>

	<div class="panel panel-default">
		<div class="panel-heading">
			<div class="and-operation-container">
				<div class="operations-container">
					<div class="operations-list">
						<div class="operation">
							<div class="col"></div>
							<div class="actions pull-right">
								<button type="button" class="button button-small">
									<span class="glyphicon glyphicon-minus-sign remove-operation"
									      aria-hidden="true"></span>
								</button>
							</div>
						</div>
					</div>
				</div>
				<p class="and-operation">
					<a href="javascript:void(0);" class="add-operation"
					   tabindex="-1">+ <?php echo strtoupper( __rw( 'OR' ) ) ?></a>
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
							<button class="button button-primary button-small edit-workflow edit-conditions"
							        data-toggle="tab"
							        href="#edit-conditions"><?php _erw( 'edit' ) ?></button>
						</span>

				<div><?php _erw( 'if' ) ?> ...</div>
			</div>
			<ul class="list-group"></ul>

			<!-- THEN part of the operation -->
			<div>
						<span class="pull-right">
							<button class="button button-primary button-small edit-workflow edit-actions"
							        data-toggle="tab"
							        href="#edit-actions"><?php _erw( 'edit' ) ?></button>
						</span>

				<div><?php _erw( 'then' ) ?> ...</div>
			</div>
			<ul class="list-group"></ul>

			<!-- WHEN part of the operation -->
			<div>
						<span class="pull-right">
							<button class="button button-primary button-small edit-workflow edit-events"
							        data-toggle="tab" href="#edit-events"><?php _erw( 'edit' ) ?></button>
						</span>

				<div><?php _erw( 'when' ) ?> ...</div>
			</div>
			<ul class="list-group"></ul>
		</div>
	</div>
</div>

<!-- Single operation -->
<div class="workflow-template" data-class="operation">
	<i class="badge or"><?php strtolower( __rw( 'or' ) ) ?></i>

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
		<a href="javascript:void(0);" class="add-operation" tabindex="-1">+ <?php echo strtoupper( __rw( 'and' ) ) ?></a>
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
		<a href="javascript:void(0);" class="add-operation" tabindex="-1">+ <?php echo strtoupper( __rw( 'and' ) ) ?></a>
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
		<a href="javascript:void(0);" class="add-operation" tabindex="-1">+ <?php echo strtoupper( __rw( 'or' ) ) ?></a>
	</p>
</div>
</form>

<?php
	// Allow other plugins to add HTML code below the <form> element. The Twitter add-on is using this action to add its own modal box that shows an information message after the add-on activation.
	do_action( 'after_workflows_page_form_element' );
?>
</div>