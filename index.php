<?php
/*
 * @author Chris Earls <cgearls@gmail.com>
 * @version 1.0.0
 * @since Frog version 0.9.5
 * @license http://www.gnu.org/licenses/agpl.html AGPL License
 * @copyright Chris Earls, 2009
 */

Plugin::setInfos(array(
    'id'          => 'ecommerce',
    'title'       => 'Ecommerce',
    'description' => 'Ecommerce solution for Frog CMS',
    'version'     => '1.0.0',
   	'license'     => 'AGPL',
	'author'      => 'Chris Earls',
    'website'     => 'http://www.chrisearls.net/',
    'require_frog_version' => '0.9.5'
));

Plugin::addController('ecommerce', 'Ecommerce');
Plugin::addJavascript('ecommerce', 'javascripts/ajaxupload.2.4.js');
Plugin::addJavascript('ecommerce', 'javascripts/tagger.js');

Observer::observe('page_found', 'ecommerce_frontend');

function collection($id) {
	$ec = new EcommerceController;
	echo $ec->collection_show($id);
}

function cart_actions() {

}

function ecommerce_frontend($page)
{
	$ec = new EcommerceController;
	include('classes/cart.class.php');
	
	ob_start();
    $page->_executeLayout();
    $output = ob_get_contents();
    ob_end_clean();
    
    $uri_arr = explode('/',$page->url);
    
    if (in_array('products',$uri_arr)) {
    	$cart = new Cart('shopping_cart');
    	
		// search
		if (in_array('search',$uri_arr)) {
			$keywords = !empty($_GET['keywords']) ? $_GET['keywords']: '';
			$output = str_replace('<!-- ecommerce -->',$ec->products_search($keywords),$output);
		}
		
    	// cart
	    if (in_array('cart',$uri_arr)) {
			
			// add item to cart
			if ( !empty($_POST['variant_id']) && !empty($_POST['quantity']) ) {
				$quantity = $cart->getItemQuantity($_POST['variant_id'])+$_POST['quantity'];
				$cart->setItemQuantity($_POST['variant_id'], $quantity);
			}
			
			//update cart item quantity
			if ( !empty($_POST['quantity']) ) {
				if (is_array($_POST['quantity'])) {
					foreach ( $_POST['quantity'] as $variant_id=>$quantity ) {
						$cart->setItemQuantity($variant_id, $quantity);
					}
				}
			}
			
			// remove item from cart
			if ( !empty($_POST['remove']) ) {
				foreach ( $_POST['remove'] as $variant_id ) {
					$cart->setItemQuantity($variant_id, 0);
				}
			}
			
			if (!empty($_POST['variant_id']) || !empty($_POST['quantity']) || !empty($_POST['remove']))
				$cart->save();
			
	    	$output = str_replace('<!-- ecommerce -->',$cart->display(),$output);
	    }
	    	
	    // checkout
	    if (in_array('checkout',$uri_arr))
	    	if ($cart->hasItems())
	    		$output = str_replace('<!-- ecommerce -->',$ec->checkout(),$output);
	    	else
	    		$output = str_replace('<!-- ecommerce -->','<p>You have no items in your cart.</p>',$output);
    	
    	//product display
    	if (count($uri_arr) == 1)
    		// products main page display
    		$output = str_replace('<!-- ecommerce -->',$ec->products_all(),$output);
    	else {
	    	// display products by type
	    	if (in_array('types',$uri_arr)) {
	    		if ($page->slug == 'types')
	    			$output = str_replace('<!-- ecommerce -->',$ec->product_types_nav(),$output);
	    		else
	    			$output = str_replace('<!-- ecommerce -->',$ec->products_by_type($page->slug),$output);
		    }
	    	// display products by vendor
	    	else if (in_array('vendors',$uri_arr))
	    		$output = str_replace('<!-- ecommerce -->',$ec->products_by_vendor($page->slug),$output);
	    	// display product detail
	    	else
	    		$output = str_replace('<!-- ecommerce -->',$ec->product_show($page->slug),$output);
	    }
		
		//add cart actions
		if ($cart->hasItems()) {
			$cart_actions = '<div id="cart-actions"><ul>';
			if (!in_array('cart',$uri_arr))
				$cart_actions .= '<li><a href="/products/cart">View Cart</a></li>';
			if (!in_array('checkout',$uri_arr) && !in_array('cart',$uri_arr))
				$cart_actions .= '<li class="last"><a href="https://www.emedamerica.com/products/checkout">Checkout</a></li>';	
			$cart_actions .= '</ul></div>';
			$output = str_replace('<!-- ecommerce cart actions -->',$cart_actions,$output);
		}
    }
    
    // add ecommerce stylesheet to the frontend layout
	$output = str_replace('</head>','<link rel="stylesheet" href="/public/ecommerce/ecommerce.css" type="text/css" /></head>',$output);
	
	//add ecommerce js file
	$output = str_replace('</head>','<script type="text/javascript" charset="utf-8" src="/public/ecommerce/ecommerce.js"></script></head>',$output);
	
	// add flash to the frontend
	if ($ec->get_flash()):
		$output = str_replace('<!-- ecommerce flash -->',$ec->get_flash(),$output);
	endif;
	
    // write frontend output
    echo $output;
    
	// clear the flash
	$_SESSION['ecommerce_flash'] = '';
	
    die();
}