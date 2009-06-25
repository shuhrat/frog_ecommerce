<?php include(CORE_ROOT.'/plugins/ecommerce/views/_nav.php');?>

<h1><?php echo __('Products'); ?></h1>

<p class="clear"><a class="button" href="<?php echo get_url('plugin/ecommerce/product_create'); ?>" onclick="this.blur();"><span><?php echo __('Add New Product'); ?></span></a></p>

<div id="search">
	<form action="" method="get">
		<input class="field" type="text" name="keywords" id="keywords" size="15" /> 
		<input class="button" type="submit" value="Search" />
	</form>
</div>

<?php if (count($products)) {?>
<div id="index">

<div id="pagination">
<?php
if ($pagination->total_rows > $pagination->per_page) : ?>
	<p>Pages: <?php echo str_replace('&page=/','&page=',$pagination->createLinks()); ?></p>
<?php endif;?>
</div>
<div class="clear"></div>
<table>
	<tr>
		<th></th>
		<th></th>
		<th><?php echo __('Title'); ?></th>
	</tr>
	<?php foreach($products as $product): ?>
	<tr class="<?php echo odd_even(); ?>">
    	<td><a class="edit" href="<?php echo get_url('plugin/ecommerce/product_update/'.$product->id); ?>"><?php echo __('Edit'); ?></a></td>
    	<td><a class="delete" href="<?php echo get_url('plugin/ecommerce/product_delete/'.$product->id); ?>" onclick="return confirmIt();"><?php echo __('Delete'); ?></a></td>
    	<td width="100%"><a style="text-decoration: none;color: #000;" href="<?php echo get_url('plugin/ecommerce/product_update/'.$product->id); ?>"><?php echo $product->title; ?></a></td>
    </tr>
	<?php endforeach; ?>
</table>
</div>
<?php } else { ?>
<p>There are no products to display.</p>
<?php } ?>