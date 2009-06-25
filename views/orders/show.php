<?php include(CORE_ROOT.'/plugins/ecommerce/views/_nav.php');?>

<h1><?php echo __('Order #'.$order->id); ?> - <?php echo date('n/j/y g:i a',strtotime($order->created_on)); ?></h1>

<div class="ec-order">
<form>

<div id="billing_info">
	<fieldset>
		<legend>Billing Information</legend> 
		
		<p>
			<label>Name:</label>
			<?php echo $order->first_name; ?> 
			<?php echo $order->last_name; ?>
		</p>
		
		<p>
			<label>Address:</label>
			<?php echo $order->address; ?>
		</p>
		
		<p>
			<label>Address 2:</label>
			<?php echo $order->address2; ?>&nbsp;
		</p>
		
		<p>
			<label>City:</label>
			<?php echo $order->city; ?>
		</p>
		
		<p>
			<label>State:</label>
			<?php echo $order->state; ?>
		</p>
		
		<p>
			<label>Zip:</label>
			<?php echo $order->zip; ?>
		</p>
		
		<p>
			<label>Country:</label>
			<?php echo $order->country; ?>
		</p>
		
		<p>
			<label>Email:</label>
			<a href="mailto:<?php echo $order->email; ?>"><?php echo $order->email; ?></a>
		</p>

		<p>
			<label>Phone:</label>
			<?php echo $order->phone; ?>
		</p>

		<p>
			<label>Fax:</label>
			<?php echo $order->fax; ?>&nbsp;
		</p>
		
	</fieldset>
</div>

<div id="shipping_info">
	<fieldset>
		<legend>Shipping Information</legend>
		
		<p>
			<label>Name:</label>
			<?php echo $order->ship_first_name; ?>&nbsp; 
			<?php echo $order->ship_last_name; ?>
		</p>

		<p>
			<label>Address:</label>
			<?php echo $order->ship_address; ?>&nbsp;
		</p>

		<p>
			<label>Address 2:</label>
			<?php echo $order->ship_address2; ?>&nbsp;
		</p>

		<p>
			<label>City:</label>
			<?php echo $order->ship_city; ?>&nbsp;
		</p>

		<p>
			<label>State:</label>
			<?php echo $order->ship_state; ?>&nbsp;
		</p>

		<p>
			<label>Zip:</label>
			<?php echo $order->ship_zip; ?>&nbsp;
		</p>

		<p>
			<label>Country:</label>
			<?php echo $order->ship_country; ?>&nbsp;
		</p>
		
		<p>
			<label>Phone:</label>
			<?php echo $order->ship_phone; ?>&nbsp;
		</p>
		
		<p>&nbsp;</p>
		<p>&nbsp;</p>
		
	</fieldset>
</div>

<div class="clear"></div>

<div id="payment_info">
	<fieldset>
		<legend>Payment Information</legend>
		
		<p>
			<label>Subtotal:</label>
			$<?php echo number_format($order->subtotal,2); ?>
		</p>

		<p>
			<label>Shipping:</label>
			$<?php echo number_format($order->shipping,2); ?>
		</p>

		<p>
			<label>Tax:</label>
			$<?php echo number_format($order->tax,2); ?>
		</p>

		<p>
			<label>Discount:</label>
			$<?php echo number_format($order->promo_discount,2); ?>
		</p>

		<p>
			<label>Total:</label>
			$<?php echo number_format(($order->subtotal + $order->shipping + $order->tax) - $order->promo_discount,2); ?>
		</p>
		
		<p>&nbsp;</p>
		
		<p>
			<label>Name:</label>
			<?php echo $order->cc_name; ?>
		</p>
		
		<p>
			<label>Type:</label>
			<?php echo $order->cc_type; ?>
		</p>
		
		<p>
			<label>Number:</label>
			************<?php echo $order->cc_number; ?>
		</p>
		
		<p>
			<label>CVV:</label>
			<?php echo $order->cc_cvv; ?>
		</p>
		
		<p>
			<label>Expiration:</label>
			<?php echo $order->cc_exp_month; ?>/<?php echo $order->cc_exp_year; ?>
		</p>
		
	</fieldset>
</div>

<div id="item_info">
	<fieldset>
		<legend>Items Bought</legend>
		
		<div id="items">
			<table>
				<tr>
					<th>Product</th>
					<th>Quantity</th>
					<th>Price</th>
				</tr>
				<?php
				if ($variants) {
					foreach ($variants as $variant) : 
						$product = Product::findById($variant['product_id']);?>
						<tr>
							<td><?php echo $product->title.' ('.$variant['title'].')'; ?></td>
							<td align="center"><?php echo $variant['quantity']; ?></td>
							<td align="center">$<?php echo $variant['price']; ?></td>
						</tr><?	
					endforeach;
				}?>
			</table>
		</div>
		
	</fieldset>
</div>

<div class="clear"></div>

</form>
</div>