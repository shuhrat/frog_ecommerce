<?php include(CORE_ROOT.'/plugins/ecommerce/views/_nav.php');?>

<h1><?php echo __('Edit Collection'); ?></h1>

<form action="<?php echo get_url('plugin/ecommerce/collection_update/'.$collection->id); ?>" method="post">
	<div class="ec-form-area">
		<p class="last">
			<label for="title"><?php echo __('Title'); ?></label>
			<input class="textbox" type="text" name="collection[title]" id="collection_title" size="35" maxlength="100" value="<?php echo htmlentities($collection->title, ENT_COMPAT, 'UTF-8'); ?>" />
		</p>
	</div>
	<p class="buttons">
		<input class="button" name="commit" type="submit" accesskey="s" value="<?php echo __('Save'); ?>" />
		<?php echo __('or'); ?> <a href="<?php echo get_url('plugin/ecommerce/collection'); ?>"><?php echo __('Cancel'); ?></a>
	</p>
	<br />
</form>

<div class="ec-form-area">
	<fieldset>
		<legend>Add products to collection</legend>
		
		<p class="normal"><strong><?php echo __('1. Begin entering the name of your product'); ?></strong></p>
		<p class="normal"><?php echo __('You can narrow down the list of products by typing a few letters of the product title.'); ?></p>
		
		<div id="product-search">
			<form action="" id="product_search_form" method="post" onsubmit="collection_product_search();return false;">
				<input type="hidden" name="collection_id" value="<?php echo $collection->id; ?>" />
				<p>
					<input class="field" type="text" name="keywords" id="keywords" size="25" /> 
					<input class="button" type="submit" value="Search" /> 
					<img id="spinner" style="display:none;" src="../frog/plugins/ecommerce/images/loading_indicator.gif" />
				</p>
			</form>
		</div>
		
		<p><strong><?php echo __('2. Select products you want to include from the list below'); ?></strong></p>
		
		<div id="product-select">
		</div>
		
		<p class="normal"><strong>Products in the '<?php echo $collection->title; ?>' collection</strong></p>
		
		<div id="cpspinner" style="display:none;"><img src="../frog/plugins/ecommerce/images/loading_indicator.gif" /></div>
		
		<div id="collection_products">			
			<?php if ($products) : ?>
			<?php foreach ($products as $product) : ?>
			<div id="product_<?php echo $product['id']; ?>">
				<div class="tools" style="display: none;">
					<a href="#" onclick="collection_product_delete(<?php echo $product['id']; ?>);return false;"><img alt="Trash" src="/frog/plugins/ecommerce/images/trash.gif" /></a>
				</div>

				<h3><?php echo $product['title']; ?> <img class="drag_handle" src="/pilot/images/drag_to_sort.gif" width="55" height="11" /></h3>
			</div>
			<script type="text/javascript" language="javascript" charset="utf-8">
			new ToolBox('product_<?php echo $product['id']; ?>');
			</script>
			<?php endforeach; ?>
			<?php endif; ?>
		</div>
		
	</fieldset>
</div>