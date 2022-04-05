<?php
use Applications\Root\Modules\Settings\SettingsController;
?>
<div class="row">
    <div class="col-lg-12">
    	<h3 class="page-header"><i class="fa fa-users"></i> <?php echo ($_REQUEST[SettingsController::ATT_VIEW_TITLE]); ?></h3>
    	<ol class="breadcrumb">
    		<li>
    			<i class="fa fa-users"></i>
    			<a href="/office/members/">Members</a>
			</li>
    		<li><i class="fa fa-plus"></i>New Acount</li>
    	</ol>
    </div>
</div>
<div class="row">
	<div class="col-xs-12">
		<?php require_once '_form-member.php';?>
	</div>
</div>