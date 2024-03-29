<?php
use PHPBackend\Request;
use PHPBackend\Text\HtmlFormater;

/**
 * @var \PHPBackend\ToastMessage $message
 */
?>

<div class="alert alert-info">
	<strong>
		<span class="fa fa-info-circle"></span> Info
	</strong> : 
	We inform you that an update is in progress. In a few days we will see major changes in the presentation of the SHIVALIK platform.
	In the new version of the shivalik platform, we have taken into consideration all the comments that members have proposed, all while improving the user experience.
	<br/>Thank you for your trust in the SHIVALIK company.
</div>

<?php if (isset($_SESSION[Request::ATT_TOAST_MESSAGES]) && !empty($_SESSION[Request::ATT_TOAST_MESSAGES])) { ?>
<div class="modal fade" data-backdrop="false" id="modal-session-message">
	<div class="modal-dialog modal-lg" style="margin: auto;position: inherit;">
		<div class="modal-content">
			<div class="modal-header">				
				<button class="close" type="button" data-dismiss="modal">x</button>
				<h4>Alert</h4>
			</div>
			<div class="modal-body" style="max-height: 350px; overflow: auto;">
        		<?php foreach ($_SESSION[Request::ATT_TOAST_MESSAGES] as $key => $message) {?>
				<p class="text-<?php echo $message->getClassType(); ?>">
					<strong><?php echo htmlspecialchars($message->getTitle()); ?></strong>
					<br/><?php echo (HtmlFormater::toHTML($message->getDescription())); ?>
				</p>
        		<?php unset($_SESSION[Request::ATT_TOAST_MESSAGES][$key]); // on suprimer le message dans la session ?>
        		<?php }?>
			</div>
			<div class="modal-footer">
				<button class="btn btn-primary" type="button" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>
<?php }

