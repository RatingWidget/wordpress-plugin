<?php
	$slug = $VARS['slug'];
	$wf = wf($slug);
?>

<div class="wrap">
	<div id="poststuff">
		<div id="wf_account_settings">
			<div class="has-sidebar has-right-sidebar">
				<div class="has-sidebar-content">
					<div class="postbox">
						<section id="content">
							<div id="hidden-title" style="display: none;"></div>
							<section id="workflows">
								<div id="conditions">
									<section class="workflowHeader">
										<section class="wfName">
											<h2><?php _e('Conditions', WP_RW_WF__SLUG); ?></h2>
										</section>
									</section>

									<section id="workflowCondActions">
										<div id="workflowConditions">
											<div>
												<div>
													<section class="condAction">
														<div class="facetList">
															<div class="facet">
																<div class="col">
																	<select class="type">
																		<option value="0">Select a Condition ...</option>
																		<optgroup label="People">
																			<option value="10">Customer Name</option>
																			<option value="20">Customer Email</option>
																			<option value="140">Help Scout User</option>
																		</optgroup>
																		<optgroup label="Message">
																			<option value="30">Type</option>
																			<option value="40">Status</option>
																			<option value="50">Assigned</option>
																			<option value="160" class="workflow_v2" style="display: none;">To</option>
																			<option value="170" class="workflow_v2" style="display: none;">Cc</option>
																			<option value="60">Subject</option>
																			<option value="150" class="workflow_v2" style="display: none;">Body</option>
																			<option value="70">Attachment(s)</option>
																			<option value="80">Tag(s)</option>
																			<option value="180" class="workflow_v2" style="display: none;">Rating</option>
																			<option value="190" class="workflow_v2" style="display: none;">Rating Comments</option>
																		</optgroup>
																		<optgroup label="Timeframe">
																			<option value="90">Date Created</option>
																			<option value="100">Last Updated</option>
																			<option value="110">Last User Reply</option>
																			<option value="111">Last Customer Reply</option>
																		</optgroup>
																		<optgroup label="Exact Date">
																			<option value="120">Date Created</option>
																			<option value="130">Last Updated</option>
																		</optgroup>
																	</select>
																</div>
																<div class="facetInputs">
																	<div>
																	</div>
																</div>
																<div class="actions">
																	<a href="javascript:void(0)" class="remove removeFacet" rel="tooltip" data-placement="bottom" data-original-title="Remove line" tabindex="-1">
																		<i class="icon-remove"></i>
																	</a>
																</div>
															</div>
														</div>
														<div class="add-or">
															<a href="javascript:void(0)" class="add addFacet" tabindex="-1">+ OR</a>
														</div>
													</section>
													<p class="and">
														<a href="javascript:void(0);" class="addCondition" tabindex="-1">+ AND</a>
													</p>
												</div>
											</div>
										</div>
									</section>
									<section class="form-actions">
										<button class="btn btn-success save-workflow" data-loading-text="Saving...">Save Conditions</button>

										<button id="cancelChanges" class="btn btn-link danger" data-loading-text="Canceling...">Cancel</button>

									</section></div></section>
						</section>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<div style="display: none;">
	<div class="condition-template">
		<section class="condAction">
    <div class="facetList"><div class="facet">
<div class="col">
    <select class="type">
        <option value="0">Select a Condition ...</option>
        <optgroup label="People">
            <option value="10">Customer Name</option>
            <option value="20">Customer Email</option>
            <option value="140">Help Scout User</option>
        </optgroup>
        <optgroup label="Message">
            <option value="30">Type</option>
            <option value="40">Status</option>
            <option value="50">Assigned</option>
            <option value="160" class="workflow_v2" style="display: none;">To</option>
            <option value="170" class="workflow_v2" style="display: none;">Cc</option>
            <option value="60">Subject</option>
            <option value="150" class="workflow_v2" style="display: none;">Body</option>
            <option value="70">Attachment(s)</option>
            <option value="80">Tag(s)</option>
            <option value="180" class="workflow_v2" style="display: none;">Rating</option>
            <option value="190" class="workflow_v2" style="display: none;">Rating Comments</option>
        </optgroup>
        <optgroup label="Timeframe">
            <option value="90">Date Created</option>
            <option value="100">Last Updated</option>
            <option value="110">Last User Reply</option>
            <option value="111">Last Customer Reply</option>
        </optgroup>
        <optgroup label="Exact Date">
            <option value="120">Date Created</option>
            <option value="130">Last Updated</option>
        </optgroup>
    </select>
</div>
<div class="facetInputs"><div></div></div>
<div class="actions">
    <a href="javascript:void(0)" class="remove removeFacet" rel="tooltip" data-placement="bottom" data-original-title="Remove line" tabindex="-1">
        <i class="icon-remove"></i>
    </a>
</div>
</div></div>
    <div class="add-or">
        <a href="javascript:void(0)" class="add addFacet" tabindex="-1">+ OR</a>
    </div>
</section>
<p class="and">
    
        <a href="javascript:void(0);" class="addCondition" tabindex="-1">+ AND</a>
    
</p></div>
	
<div class="facet-template">
	<i class="badge or">or</i>
	<div class="col">
		<select class="type">
			<option value="0">Select a Condition ...</option>
			<optgroup label="People">
				<option value="10">Customer Name</option>
				<option value="20">Customer Email</option>
				<option value="140">Help Scout User</option>
			</optgroup>
			<optgroup label="Message">
				<option value="30">Type</option>
				<option value="40">Status</option>
				<option value="50">Assigned</option>
				<option value="160" class="workflow_v2" style="display: none;">To</option>
				<option value="170" class="workflow_v2" style="display: none;">Cc</option>
				<option value="60">Subject</option>
				<option value="150" class="workflow_v2" style="display: none;">Body</option>
				<option value="70">Attachment(s)</option>
				<option value="80">Tag(s)</option>
				<option value="180" class="workflow_v2" style="display: none;">Rating</option>
				<option value="190" class="workflow_v2" style="display: none;">Rating Comments</option>
			</optgroup>
			<optgroup label="Timeframe">
				<option value="90">Date Created</option>
				<option value="100">Last Updated</option>
				<option value="110">Last User Reply</option>
				<option value="111">Last Customer Reply</option>
			</optgroup>
			<optgroup label="Exact Date">
				<option value="120">Date Created</option>
				<option value="130">Last Updated</option>
			</optgroup>
		</select>
	</div>
	<div class="facetInputs">
		<div>
		</div>
	</div>
	<div class="actions">
		<a href="javascript:void(0)" class="remove removeFacet" rel="tooltip" data-placement="bottom" data-original-title="Remove line" tabindex="-1">
			<i class="icon-remove"></i>
		</a>
	</div>
</div>
</div>

<?php fs_require_template('powered-by.php') ?>