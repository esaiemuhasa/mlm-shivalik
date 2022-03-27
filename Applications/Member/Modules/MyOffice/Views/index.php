<?php
use Applications\Member\Modules\MyOffice\MyOfficeController;
use Core\Shivalik\Entities\Office;
use Core\Shivalik\Filters\SessionMemberFilter;
use PHPBackend\AppConfig;
use PHPBackend\Request;

/**
 * @var AppConfig $config
 */
$config = $_REQUEST[Request::ATT_APP_CONFIG];
/**
 * @var Office $office
 */
$office = $_SESSION[SessionMemberFilter::MEMBER_CONNECTED_SESSION]->officeAccount;

?>


<div class="row">
	<div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
        <div class="info-box green-bg">
            <i class="fa fa-users"></i>
            <div class="count"><?php echo ($_REQUEST[MyOfficeController::ATT_COUNT_MEMEBERS]); ?></div>
            <div class="title">Members</div>
        </div>
        <!--/.info-box-->
    </div>
    <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
        <div class="info-box green-bg">
            <i class="fa fa-graduation-cap"></i>
            <div class="count"><?php echo ($office->countUpgrades()); ?></div>
            <div class="title">Upgrades</div>
        </div>
        <!--/.info-box-->
    </div>
</div>

<div class="row">
	 <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12">
        <div class="info-box blue-bg">
            <i class="glyphicon glyphicon-ok"></i>
            <div class="count"><?php echo "{$office->getAvailableVirtualMoney()} {$config->get('devise')}"; ?></div>
            <div class="title">virtual</div>
        </div>
        <!--/.info-box-->
    </div>
    <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12">
        <div class="info-box blue-bg">
            <i class="fa fa-money"></i>
            <div class="count"><?php echo "{$office->getSoldRequestWithdrawals()} {$config->get('devise')}"; ?></div>
            <div class="title">Requested</div>
        </div>
        <!--/.info-box-->
    </div>
    
    <?php if ($office->getSoldAcceptWithdrawals() > 0) : ?>
     <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12">
        <div class="info-box blue-bg">
            <i class="glyphicon glyphicon-ok-circle"></i>
            <div class="count"><?php echo "{$office->getSoldAcceptWithdrawals()} {$config->get('devise')}"; ?></div>
            <div class="title">Served</div>
        </div>
        <!--/.info-box-->
    </div>
    <?php endif;?>
    
    <?php if ($office->hasDebts()) : ?>
    <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12">
        <div class="info-box red-bg">
            <i class="glyphicon glyphicon-warning-sign"></i>
            <div class="count"><?php echo ("{$office->getDebts()} {$config->get('devise')}"); ?></div>
            <div class="title">Debts</div>
        </div>
        <!--/.info-box-->
    </div>
    <?php endif; ?>
    
    <?php if ($_REQUEST[MyOfficeController::ATT_CAN_SEND_RAPORT]) : ?>
    <div class="col-xs-12">
    	<div class="alert alert-info">
    		<h2 class="alert-title">Alert</h2>
    		<p>You have <?php echo "{$office->getSoldAcceptWithdrawals()} {$config->get('devise')}"; ?> that you have already made matched to the adhering members of the society. By clicking on the button below, the report will be sent directly to the hierarchy.</p>
    		<a class="btn btn-primary" href="/member/office/send-matched-money.html">
    			<span class="fa fa-send"></span> Send report
    		</a>
    	</div>
    </div>
    <?php endif; ?>
</div>
