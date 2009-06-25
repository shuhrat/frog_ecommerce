<?php
/**
 * class Cart
 *
 * @package frog
 * @subpackage plugin.ecommerce
 * @author Chris Earls <cgearls@gmail.com>
 * @since Frog version 0.9.5
 */
class Cart {
	var $cart_name;
	var $items = array();
	
	function __construct($name) {
		$this->cart_name = $name;
		
		if (isset($_SESSION[$this->cart_name]))
			$this->items = $_SESSION[$this->cart_name];
		else
			$_SESSION[$this->cart_name] = $this->items;
	}
	
	function setItemQuantity($variant_id, $quantity) {
		$this->items[$variant_id] = $quantity;
	}
	
	function getItemPrice($variant_id) {
		$ec = new EcommerceController;
		$product = $ec->get_cart_product($variant_id);
		return $product->price;
	}
	
	function getItemName($variant_id) {
		$ec = new EcommerceController;
		$product = $ec->get_cart_product($variant_id);
		$product_title = $product->title;
		if($product->variant_title!='Default')
			$product_title .= ' - '.$product->variant_title;
		return $product_title;
	}
	
	function getItemSlug($variant_id) {
		$ec = new EcommerceController;
		$product = $ec->get_cart_product($variant_id);
		return $product->slug;
	}
	
	function getItems() {
		return $this->items;
	}
	
	function hasItems() {
		return (bool) $this->items;
	}
	
	function getItemQuantity($variant_id) {
		if (isset($this->items[$variant_id]))
			return (int) $this->items[$variant_id];
		else
			return (int) 0;
	}
	
	function clean() {
		if ($this->items) {
			foreach ( $this->items as $variant_id=>$quantity ) {
				if ( $quantity < 1 )
					unset($this->items[$variant_id]);
			}
		}
	}
	
	function save() {
		$this->clean();
		$_SESSION[$this->cart_name] = $this->items;
	}
	
	function getTotal() {
		if ($this->items) {
			$total_price = $i = 0;
			foreach ( $this->getItems() as $variant_id=>$quantity ) :
				$total_price += $quantity*$this->getItemPrice($variant_id);
			endforeach;
			return $total_price;
		}
		else
			return 0;
	}
	
	function getDiscount($code) {
		$ec = new EcommerceController;
		$promo = $ec->get_promo($code);
		
		if ($promo)
			if ($promo->is_percent == 1)
				return ($promo->discount / 100) * $this->getTotal();
			else
				return $promo->discount;
		else
			return 0;
	}
	
	function getTax() {
		if(isset($_SESSION['order'])) {
			if ($_SESSION['order']['state'] == 'AR')
				return $this->getTotal() * .06;
			else
				return 0;
		}
		else
			return 0;
	}
	
	function display() {
		if ( $this->hasItems() ) :
			$output = '
			<div id="cart">
			<form action="/products/cart" method="post">
				<table>
					<tr>
						<th>Quantity</th>
						<th>Item</th>
						<th>Unit Price</th>
						<th>Total</th>
						<th>Remove</th>
					</tr>';

					$total_price = $i = 0;
					foreach ( $this->getItems() as $variant_id=>$quantity ) :
						$total_price += $quantity*$this->getItemPrice($variant_id);

						$output .= $i++%2==0 ? '<tr>' : '<tr class="odd">';
						$output .='	<td class="quantity center"><input type="text" name="quantity['.$variant_id.']" size="3" value="'.$quantity.'" tabindex="'.$i.'" /></td>
							<td class="item_name"><a href="/products/'.$this->getItemSlug($variant_id).'">'.$this->getItemName($variant_id).'</a></td>
							<td class="unit_price">$'.number_format($this->getItemPrice($variant_id),2).'</td>
							<td class="extended_price">$'.number_format(($this->getItemPrice($variant_id)*$quantity),2).'</td>
							<td class="remove center"><input type="checkbox" name="remove[]" value="'.$variant_id.'" /></td>
						</tr>';
					endforeach;
					$output .= '<tr><td colspan="3"></td><td id="total_price"><strong>$'.number_format($total_price,2).'</strong></td><td></td></tr>
				</table>
				<a class="checkout" href="https://www.yoursite.com/products/checkout">Checkout</a>
				<input class="button" type="submit" name="update" value="Update Cart" />
			</form>
			</div>';
		else:
			$output = '<p>You have no items in your cart.</p>';
		endif;

		return $output;
	}
}