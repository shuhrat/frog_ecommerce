<?php include(CORE_ROOT.'/plugins/ecommerce/views/_nav.php');?>

<h1><?php echo __('Add Collection'); ?></h1>

<form action="<?php echo get_url('plugin/ecommerce/collection_create'); ?>" method="post">
	<div class="ec-form-area">
		<p class="last">
			<label for="title"><?php echo __('Title'); ?></label>
			<input class="textbox" type="text" name="collection[title]" id="collection_title" size="35" />
		</p>
	</div>
	<p class="buttons">
		<input class="button" name="commit" type="submit" accesskey="s" value="<?php echo __('Save'); ?>" />
		<?php echo __('or'); ?> <a href="<?php echo get_url('plugin/ecommerce/collection'); ?>"><?php echo __('Cancel'); ?></a>
	</p>
</form>