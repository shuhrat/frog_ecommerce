<?php include(CORE_ROOT.'/plugins/ecommerce/views/_nav.php');?>

<h1><?php echo __('Orders'); ?></h1>

<div id="search">
	<form action="" method="get">
		<input class="field" type="text" name="keywords" id="keywords" size="15" /> 
		<input class="button" type="submit" value="Search" />
	</form>
</div>

<?php if (count($orders)) {?>
<div id="index">

<div id="pagination">
<?php
if ($pagination->total_rows > $pagination->per_page) : ?>
	<p>Pages: <?php echo str_replace('&page=/','&page=',$pagination->createLinks()); ?></p>
<?php endif;?>
</div>
<div class="clear"></div>
<table id="orders">
	<tr>
		<th></th>
		<th><?php echo __('Fulfilled'); ?></th>
		<th><?php echo __('Order #'); ?></th>
		<th><?php echo __('Date'); ?></th>
		<th><?php echo __('Total'); ?></th>
		<th><?php echo __('Name'); ?></th>
		<th><?php echo __('City'); ?></th>
		<th><?php echo __('State'); ?></th>
		<th><?php echo __('Zip'); ?></th>
	</tr>
	<?php foreach($orders as $order): ?>
	<tr id="order_row_<?php echo $order->id; ?>" class="<?php echo odd_even(); ?> <?php if ($order->fulfilled):?>fulfilled<? endif; ?>">
    	<td><a class="file" href="<?php echo get_url('plugin/ecommerce/order_show/'.$order->id); ?>"><?php echo __('View'); ?></a></td>
		<td align="center"><form action="" name="order" method="post"><input type="checkbox" name="order[fulfilled]" id="fulfilled_<?php echo $order->id; ?>" <?php if ($order->fulfilled):?>checked="checked"<? endif; ?> onclick="order_fulfilled(<?php echo $order->id; ?>);" /></form></td>
		<td><?php echo $order->id; ?></td>
		<td><?php echo date('n/j/y g:i a',strtotime($order->created_on)); ?></td>
		<td class="total">$<?php echo number_format(($order->subtotal + $order->shipping + $order->tax) - $order->promo_discount,2); ?></td>
    	<td><?php echo $order->first_name. ' '.  $order->last_name; ?></td>
		<td><?php echo $order->city; ?></td>
		<td><?php echo $order->state; ?></td>
		<td><?php echo $order->zip; ?></td>
    </tr>
	<?php endforeach; ?>
</table>
</div>
<?php } else { ?>
<p>There are no orders to display.</p>
<?php } ?>