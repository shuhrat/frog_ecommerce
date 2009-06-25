<?php include(CORE_ROOT.'/plugins/ecommerce/views/_nav.php');?>

<h1><?php echo __('Collections'); ?></h1>

<p class="clear"><a class="button" href="<?php echo get_url('plugin/ecommerce/collection_create'); ?>" onclick="this.blur();"><span><?php echo __('Add New Collection'); ?></span></a></p>

<?php if (count($collections)): ?>
<div id="index">

<div id="pagination">
<?php
if ($pagination->total_rows > $pagination->per_page)
	echo '<p>Pages: '.$pagination->createLinks().'</p>';
?>
</div>

<table>
	<tr>
		<th></th>
		<th></th>
		<th>ID</th>
		<th>Title</th>
	</tr>
	<?php foreach($collections as $collection): ?>
	<tr class="<?php echo odd_even(); ?>">
		<td><a class="edit" href="<?php echo get_url('plugin/ecommerce/collection_update/'.$collection->id); ?>">Edit</a></td>
		<td><a class="delete" href="<?php echo get_url('plugin/ecommerce/collection_delete/'.$collection->id); ?>" onclick="return confirmIt();">Delete</a></td>
		<td><?php echo $collection->id; ?></td>
		<td width="100%"><?php echo $collection->title; ?></td>
	</tr>
	<?php endforeach; ?>
</table>
</div>

<?php else: ?>
<p><strong><?php echo __('No collections found.'); ?></strong></p>
<?php endif; ?>

