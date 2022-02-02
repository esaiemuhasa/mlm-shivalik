<?php
?>
<section class="panel">
    <header class="panel-heading">Product <?php echo (isset($_GET['id'])? 'edition':'creation') ?> form</header>
    <div class="panel-body">
    	<form role="form" action="" method="POST" enctype="multipart/form-data">
    		<?php if (isset($_REQUEST['result'])) : ?>
    		<div class="alert alert-<?php echo (isset($_REQUEST['errors']) && empty($_REQUEST['errors'])? 'success':'danger')?>">
    			<strong><?php echo ($_REQUEST['result']);?></strong>
    			<?php if (isset($_REQUEST['errors']['message'])) : ?>
    			<p><?php echo htmlspecialchars($_REQUEST['errors']['message']);?></p>
    			<?php endif;?>
    		</div>
    		<?php endif;?>
    		<fieldset>
    			<legend>Product profile</legend>
        		<div class="form-group <?php echo (isset($_REQUEST['errors']['name'])? 'has-error':'');?>">
        			<label class="form-label" for="product-name">Name <span class="text-danger">*</span></label>
        			<input type="text" name="name" value="<?php echo htmlspecialchars(isset($_REQUEST['product'])? $_REQUEST['product']->name:'');?>" id="product-name" class="form-control" placeholder="put here product name" autocomplete="off"/>
        			<?php if (isset($_REQUEST['errors']['name'])){?>
        			<p class="help-block"><?php echo htmlspecialchars($_REQUEST['errors']['name']);?></p>
        			<?php }?>
        		</div>
        		<div class="row">
        			<div class="col-md-6">
                		<div class="form-group <?php echo (isset($_REQUEST['errors']['picture'])? 'has-error':'');?>">
                			<label class="form-label" for="product-picture">Picture <span class="text-danger">*</span></label>
                			<input type="file" name="picture" id="product-picture" class="form-control"/>
                			<?php if (isset($_REQUEST['errors']['picture'])){?>
                			<p class="help-block"><?php echo htmlspecialchars($_REQUEST['errors']['picture']);?></p>
                			<?php }?>
                		</div>
        			</div>
        			<div class="col-md-6">
                		<div class="form-group <?php echo (isset($_REQUEST['errors']['defaultUnitPrice'])? 'has-error':'');?>">
                			<label class="form-label" for="product-defaultUnitPrice">Default unit price<span class="text-danger">*</span></label>
                			<span class="input-group">
                    			<input type="text" name="defaultUnitPrice" value="<?php echo htmlspecialchars(isset($_REQUEST['product'])? ($_REQUEST['product']->defaultUnitPrice):'');?>" id="product-defaultUnitPrice" class="form-control" autocomplete="off"/>
                    			<span class="input-group-addon">$</span>
                			</span>
                			<?php if (isset($_REQUEST['errors']['defaultUnitPrice'])){?>
                			<p class="help-block"><?php echo htmlspecialchars($_REQUEST['errors']['defaultUnitPrice']);?></p>
                			<?php }?>
                		</div>
        			</div>
        		</div>
        		<div class="form-group <?php echo (isset($_REQUEST['errors']['description'])? 'has-error':'');?>">
        			<label class="form-label" for="product-description">Short description<span class="text-danger">*</span></label>
        			<textarea rows="5" cols="20"name="description" id="product-description" class="form-control" placeholder="put here the short description"><?php echo htmlspecialchars(isset($_REQUEST['product'])? ($_REQUEST['product']->description):'');?></textarea>
        			<?php if (isset($_REQUEST['errors']['description'])){?>
        			<p class="help-block"><?php echo htmlspecialchars($_REQUEST['errors']['description']);?></p>
        			<?php }?>
        		</div>
    		</fieldset>
    		    		
    		<div class="text-center">
        		<button class="btn btn-primary"><span class="fa fa-send"></span> Save <?php echo (isset($_GET['id'])? 'modification':'') ?></button>
    		</div>
    	</form>
    </div>
</section>