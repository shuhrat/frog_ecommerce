<?php
/**
 * class EcommerceController
 *
 * @package frog
 * @subpackage plugin.ecommerce
 * @author Chris Earls <cgearls@gmail.com>
 * @since Frog version 0.9.5
 */

// include models
include('models/Product.php');
include('models/ProductType.php');
include('models/ProductVendor.php');
include('models/ProductImage.php');
include('models/ProductVariant.php');
include('models/ProductFile.php');
include('models/ProductVideo.php');
include('models/Order.php');
include('models/Collection.php');

// helpers
use_helper('Pagination');

class EcommerceController extends PluginController {
	
	public function __construct() {
		$this->setLayout('backend');
        $this->assignToLayout('sidebar', new View('../../plugins/ecommerce/views/sidebar'));
    }
	
	public function index() {
		$this->dashboard();
	}
	
	/**
	 * Dashboard
	 */
    public function dashboard() {
    	global $__FROG_CONN__;
    	
		$sql = 'select l.message, l.created_on, u.name from ecommerce_log l left join user u on u.id = l.user_id order by created_on desc limit 20';
		$stmt = $__FROG_CONN__->prepare($sql);
		$stmt->execute();
		$logs = $stmt->fetchAll();
    	
    	$this->display('ecommerce/views/dashboard', array(
			'logs' => $logs
		));
    }
	
	function _insert_log($message)
	{
		$log_data = array("message"=>$message,"user_id"=>AuthUser::getId());
		$record = Record::insert('ecommerce_log', $log_data);
	}
	
	/**
	 * Documentation
	 */
    public function documentation() {
        $this->display('ecommerce/views/documentation');
    }
	
	/**
	 * Settings
	 */
    function settings() {
        $this->display('ecommerce/views/settings');
    }
    
    /**
	 * Marketing / Promos
	 */
    function marketing() {
        $this->display('ecommerce/views/marketing');
    }
    
    public function get_promo($code) {
    	global $__FROG_CONN__;
		$sql = 'select discount, is_percent from ecommerce_promos where code = \''.mysql_escape_string($code).'\' and curdate() between start_date and end_date limit 0,1';
		$stmt = $__FROG_CONN__->prepare($sql);
		$stmt->execute();
		$promo = $stmt->fetchObject();
		return $promo;
    }
    
    /**
	 * Order
	 */
    public function order() {
       $this->order_index();
    }
    
    public function order_index() {
		$page = !empty($_GET['page']) ? $_GET['page']: 1;
		$keywords = !empty($_GET['keywords']) ? $_GET['keywords']: '';
		$per_page = 15;

		$orders_total = Order::find(array(
			'where' => 'first_name like \'%'.$keywords.'%\''
		));
		$orders = Order::find(array(
			'limit' => $per_page,
			'offset' => ($page-1)*($per_page),
			'where' => 'first_name like \'%'.$keywords.'%\' or last_name like \'%'.$keywords.'%\''
		));

		$pagination = new Pagination(array(
			'base_url' => '/'.ADMIN_DIR.'/plugin/ecommerce/order/?keywords='.$keywords.'&page=',
			'total_rows' => count($orders_total),
			'per_page' => $per_page,
			'num_links' => 4,
			'cur_page' => $page
		));

		$this->display('ecommerce/views/orders/index', array(
			'orders' => $orders,
			'pagination' => $pagination
		));
    }
	
	public function order_show($id) {
		$order = Record::findByIdFrom('Order', $id);
		
		global $__FROG_CONN__;
		$sql = 'select * from ecommerce_order_variant where order_id = '.$id.';';
		$stmt = $__FROG_CONN__->prepare($sql);
		$stmt->execute();
		$variants = $stmt->fetchAll();
		
		$this->display('ecommerce/views/orders/show', array(
			'order' => $order,
			'variants' => $variants
		));
	}
	
	public function order_fulfilled($id) {
		$order = Record::findByIdFrom('Order', $id);
		$order_data = array("fulfilled"=>1);
		$order->setFromData($order_data);
		if ($order->save())
			echo 'success';
	}
	
	public function order_not_fulfilled($id) {
		$order = Record::findByIdFrom('Order', $id);
		$order_data = array("fulfilled"=>0);
		$order->setFromData($order_data);
		if ($order->save())
			echo 'success';
	}
	
	/**
	 * Checkout
	 */
    public function checkout() {    	
    	$output = '';
    	if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    		$output = $this->_checkout_step_1();
    	}
    	else {
    		//valid helper
    		include'classes/valid.class.php';
    		$valid = new Valid();
    		//validation class
    		include'classes/validation.class.php';
    		
    		$step = $_POST['step'];
    		
    		if ($step == '1') {
    			//step 1 validation
    			$post = new Validation($_POST['order']);
    			$post->add_rules('first_name','required');
    			$post->add_rules('last_name','required');
    			$post->add_rules('company','required');
    			$post->add_rules('address','required');
    			$post->add_rules('city','required');
    			$post->add_rules('state','required');
				$post->add_rules('country','required');
    			$post->add_rules('zip','required');
    			$post->add_rules('phone','required', array($valid, 'phone'));
    			$post->add_rules('email', 'required', array($valid, 'email'));
    			$post->add_rules('rep_name','required');
    			
    			if (!isset($_POST['billing_is_shipping'])) {
    				$post->add_rules('ship_first_name','required');
	    			$post->add_rules('ship_last_name','required');
	    			$post->add_rules('ship_company','required');
	    			$post->add_rules('ship_address','required');
	    			$post->add_rules('ship_city','required');
	    			$post->add_rules('ship_state','required');
					$post->add_rules('ship_country','required');
	    			$post->add_rules('ship_zip','required');
	    			$post->add_rules('ship_phone','required', array($valid, 'phone'));
    			}
    			
    			$post->pre_filter('trim');
    			
    			//success, go to step 2
    			if ($post->validate()) {
    				//save order data
    				$_SESSION['order'] = $_POST['order'];
    				$output = $this->_checkout_step_2();
    			}
    			//errors, go back to step 1
    			else {
    				$errors = $post->errors();
    				$output = $this->_checkout_step_1($_POST,$errors);
    			}
    		}
    		elseif ($step == '2') {
    			//step 2 validation
    			$post = new Validation($_POST['order']);
    			$post->add_rules('cc_name','required');
    			$post->add_rules('cc_type','required');
    			$post->add_rules('cc_number','required', array($valid, 'credit_card'));
    			$post->add_rules('cc_cvv','required', 'length[3,4]', array($valid, 'digit'));
    			$post->add_rules('cc_exp_month','required');
    			$post->add_rules('cc_exp_year','required');
    			
    			if(isset($_POST['order']['cc_exp_month']) && isset($_POST['order']['cc_exp_year']))
    				$post->add_callbacks('cc_exp_year', array($this,'_validate_cc_exp_date'));
    			
    			$post->pre_filter('trim');
    			
    			if ($post->validate()) {
    				$cart = new Cart('shopping_cart');
    				
    				//order data array
    				$order_arr = array_merge($_SESSION['order'],$_POST['order']);
    				$full_cc_number = $order_arr['cc_number'];
    				$order_arr['cc_number'] = substr($order_arr['cc_number'], -4);
    				$order_arr['promo_discount'] = $cart->getDiscount($order_arr['promo_code']);
    				$order_arr['subtotal'] = $cart->getTotal();
    				$order_arr['tax'] = $cart->getTax();
    				
					//process payment
					include'merchants/firstdata.class.php';
					$merchant = new FirstData();
					
					//billing info
					$merchant->name = $order_arr['first_name'].' '.$order_arr['last_name'];
					$merchant->company = $order_arr['company'];
					$merchant->address = $order_arr['address'];
					$merchant->address2 = $order_arr['address2'];
					$merchant->city = $order_arr['city'];
					$merchant->state = $order_arr['state'];
					$merchant->country = $order_arr['country'];
					$merchant->phone = $order_arr['phone'];
					$merchant->fax = $order_arr['fax'];
					$merchant->email = $order_arr['email'];
					$merchant->zip = $order_arr['zip'];
					
					//shipping info
					$merchant->ship_name = $order_arr['ship_first_name'].' '.$order_arr['ship_last_name'];
					$merchant->ship_address = $order_arr['ship_address'];
					$merchant->ship_saddress2 = $order_arr['ship_address2'];
					$merchant->ship_city = $order_arr['ship_city'];
					$merchant->ship_state = $order_arr['ship_state'];
					$merchant->ship_country = $order_arr['ship_country'];
					$merchant->ship_zip = $order_arr['ship_zip'];
					
					//payment info
					$merchant->cc_number = $full_cc_number;
					$merchant->cc_exp_month = $order_arr['cc_exp_month'];
					$merchant->cc_exp_year = substr($order_arr['cc_exp_year'],-2);
					$merchant->cc_cvv = $order_arr['cc_cvv'];
					
					$merchant->subtotal = $order_arr['subtotal'];
					$merchant->shipping = 0;
					$merchant->tax = $order_arr['tax'];
					$merchant->total = ($order_arr['subtotal']+$order_arr['tax'])-$order_arr['promo_discount'];
					
					// set to GOOD for test or LIVE
					$merchant->result = 'LIVE';
					
					$merchant_success = false;
					$result = $merchant->sale();
					
					if ($result['r_approved'] == "APPROVED")
						$merchant_success = true;
					
					//merchant error
					if (!$merchant_success) {
						$errors = $post->errors();
						$this->set_flash($result['r_error'],'error');
						$output = $this->_checkout_step_2($_POST,$errors);
					}
					//merchant success
					else {
						//save order to database
						$record = Record::insert('ecommerce_order', $order_arr);
						$order_id = Record::lastInsertId();
	
						//save order items to database
						foreach ( $cart->getItems() as $variant_id=>$quantity ) :
							//get variant data
							$variant = Record::findByIdFrom('ProductVariant', $variant_id);
							$variant->order_id = $order_id;
							$variant->quantity = $quantity;
							$variant_arr = (array)$variant;
	
							//remove unneeded fields
							unset($variant_arr['id']);
							unset($variant_arr['created_on']);
							unset($variant_arr['updated_on']);
							unset($variant_arr['position']);
	
							//insert
							$record = Record::insert('ecommerce_order_variant', $variant_arr);
						endforeach;
	
						//save log
						$this->_insert_log('Order <a href="'.get_url('plugin/ecommerce/order_show/'.$order_id).'">'.$order_id.'</a> was placed.');
	
						//send emails to client and buyer
						$this->_send_order_email('info@emedamerica.com',$order_id,$order_arr,$variant_arr);
						$this->_send_order_email($order_arr['email'],$order_id,$order_arr,$variant_arr);
	
						//success
						$this->set_flash('Thank you for your order. You will receive a confirmation email shortly.','success');
	
						//clear cart and order session
						unset($_SESSION['order']);
						unset($_SESSION['Cart']);
					}
    			}
    			//errors, go back to step 2
    			else {
    				$errors = $post->errors();
    				$output = $this->_checkout_step_2($_POST,$errors);
    			}
    		}
    	}
    		
    	return $output;
    }
	
	function set_flash($msg,$type) {
		$output = '<div class="'.$type.'">'.$msg.'</div>';
		$_SESSION['ecommerce_flash'] = $output;
	}

	function get_flash() {
		return isset($_SESSION['ecommerce_flash']) ? $_SESSION['ecommerce_flash'] : null;
	}
	
	// checkout step 1 view
    function _checkout_step_1($values=null,$errors=null) {
    	$cart = new Cart('shopping_cart');
    	
    	$ship_checked = 'checked="checked"';
    	if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($values['billing_is_shipping']))
    		$ship_checked = '';
    	
    	$output = '	
		<div id="checkout">
		<div id="summary"><h3 class="price">$'.number_format($cart->getTotal(),2).'</h3></div>
		<p id="step">step 1 of 2</p>
		<form action="" method="post">
		<input type="hidden" name="step" value="1" />
		<h3>Billing Information</h3>
    	
		<table border="0">
	    	<tr>
	    		<td class="label"><label for="first_name">First Name:</label></td>
	    		<td class="field"><input type="text" name="order[first_name]" size="30" value="'.htmlentities($values['order']['first_name']).'" maxlength="50" /> '.$this->_field_error($errors,'first_name').'</td>
	    	</tr>
	    	<tr>
	    		<td class="label"><label for="last_name">Last Name:</label></td>
	    		<td class="field"><input type="text" name="order[last_name]" size="30" value="'.htmlentities($values['order']['last_name']).'" maxlength="50" /> '.$this->_field_error($errors,'last_name').'</td>
	    	</tr>
	    	<tr>
	    		<td class="label"><label for="company">Company:</label></td>
	    		<td class="field"><input type="text" name="order[company]" size="30" value="'.htmlentities($values['order']['company']).'" maxlength="50" /> '.$this->_field_error($errors,'company').'</td>
	    	</tr>
	    	<tr>
	    		<td class="label"><label for="address">Address:</label></td>
	    		<td class="field"><input type="text" name="order[address]" size="30" value="'.htmlentities($values['order']['address']).'" maxlength="50" /> '.$this->_field_error($errors,'address').'</td>
	    	</tr>
	    	<tr>
	    		<td class="label"><label for="address2">Address 2:</label></td>
	    		<td class="field"><input type="text" name="order[address2]" size="30" value="'.htmlentities($values['order']['address2']).'" maxlength="50" /> '.$this->_field_error($errors,'address2').'</td>
	    	</tr>
	    	<tr>
	    		<td class="label"><label for="city">City:</label></td>
	    		<td class="field"><input type="text" name="order[city]" size="30" value="'.htmlentities($values['order']['city']).'" maxlength="50" /> '.$this->_field_error($errors,'city').'</td>
	    	</tr>
	    	<tr>
	    		<td class="label"><label for="state">State:</label></td>
	    		<td class="field"><select name="order[state]">'.str_replace('value="'.$values['order']['state'].'"','value="'.$values['order']['state'].'" selected="selected"',$this->_state_select()).'</select> '.$this->_field_error($errors,'state').'</td>
	    	</tr>
			<tr>
				<td class="label"><label for="country">Country:</label></td>
				<td class="field"><input type="text" name="order[country]" size="30" value="'.htmlentities($values['order']['country']).'" maxlength="50" /> '.$this->_field_error($errors,'country').'</td>
			</tr>
	    	<tr>
	    		<td class="label"><label for="zip">Zip:</label></td>
	    		<td class="field"><input type="text" name="order[zip]" size="30" value="'.htmlentities($values['order']['zip']).'" maxlength="50" /> '.$this->_field_error($errors,'zip').'</td>
	    	</tr>
	    	<tr>
    			<td class="label"><label for="email">Email:</label></td>
    			<td class="field"><input type="text" name="order[email]" size="30" value="'.htmlentities($values['order']['email']).'" maxlength="50" /> '.$this->_field_error($errors,'email').'</td>
    		</tr>
    		<tr>
    			<td class="label"><label for="phone">Phone:</label></td>
    			<td class="field"><input type="text" name="order[phone]" size="30" value="'.htmlentities($values['order']['phone']).'" maxlength="50" /> (XXX-XXX-XXXX) '.$this->_field_error($errors,'phone').'</td>
    		</tr>
    		<tr>
    			<td class="label"><label for="fax">Fax:</label></td>
    			<td class="field"><input type="text" name="order[fax]" size="30" value="'.htmlentities($values['order']['fax']).'" maxlength="50" /> '.$this->_field_error($errors,'fax').'</td>
    		</tr>
    		<tr>
	    		<td class="label"><label for="rep_name">Rep. Name:</label></td>
	    		<td class="field"><input type="text" name="order[rep_name]" size="30" value="'.htmlentities($values['order']['rep_name']).'" maxlength="50" /> '.$this->_field_error($errors,'rep_name').'</td>
	    	</tr>
    	</table>
    	
    	<p>
    		<input type="checkbox" '.$ship_checked.' name="billing_is_shipping" id="shipping-toggle" value="1" /> 
    		Ship items to the above billing address
    	</p>
    	
    	<h3>Shipping Information</h3>
    	
    	<p id="shipping-same" class="notice">Items will be shipped to your billing address.</p>
    	
    	<div id="shipping_form" style="display: none;">
    	<table border="0" id="checkout">
	    	<tr>
	    		<td class="label"><label for="ship_first_name">First Name:</label></td>
	    		<td class="field"><input type="text" name="order[ship_first_name]" size="30" value="'.htmlentities($values['order']['ship_first_name']).'" maxlength="50" /> '.$this->_field_error($errors,'ship_first_name').'</td>
	    	</tr>
	    	<tr>
	    		<td class="label"><label for="ship_last_name">Last Name:</label></td>
	    		<td class="field"><input type="text" name="order[ship_last_name]" size="30" value="'.htmlentities($values['order']['ship_last_name']).'" maxlength="50" /> '.$this->_field_error($errors,'ship_last_name').'</td>
	    	</tr>
	    	<tr>
	    		<td class="label"><label for="ship_company">Company:</label></td>
	    		<td class="field"><input type="text" name="order[ship_company]" size="30" value="'.htmlentities($values['order']['ship_company']).'" maxlength="50" /> '.$this->_field_error($errors,'ship_company').'</td>
	    	</tr>
	    	<tr>
	    		<td class="label"><label for="ship_address">Address:</label></td>
	    		<td class="field"><input type="text" name="order[ship_address]" size="30" value="'.htmlentities($values['order']['ship_address']).'" maxlength="50" /> '.$this->_field_error($errors,'ship_address').'</td>
	    	</tr>
	    	<tr>
	    		<td class="label"><label for="ship_address2">Address 2:</label></td>
	    		<td class="field"><input type="text" name="order[ship_address2]" size="30" value="'.htmlentities($values['order']['ship_address2']).'" maxlength="50" /> '.$this->_field_error($errors,'ship_address2').'</td>
	    	</tr>
	    	<tr>
	    		<td class="label"><label for="ship_city">City:</label></td>
	    		<td class="field"><input type="text" name="order[ship_city]" size="30" value="'.htmlentities($values['order']['ship_city']).'" maxlength="50" /> '.$this->_field_error($errors,'ship_city').'</td>
	    	</tr>
	    	<tr>
	    		<td class="label"><label for="ship_state">State:</label></td>
	    		<td class="field"><select name="order[ship_state]">'.str_replace('value="'.$values['order']['ship_state'].'"','value="'.$values['order']['state'].'" selected="selected"',$this->_state_select()).'</select> '.$this->_field_error($errors,'ship_state').'</td>
	    	</tr>
			<tr>
				<td class="label"><label for="ship_country">Country:</label></td>
				<td class="field"><input type="text" name="order[ship_country]" size="30" value="'.htmlentities($values['order']['ship_country']).'" maxlength="50" /> '.$this->_field_error($errors,'ship_country').'</td>
			</tr>
	    	<tr>
	    		<td class="label"><label for="ship_zip">Zip:</label></td>
	    		<td class="field"><input type="text" name="order[ship_zip]" size="30" value="'.htmlentities($values['order']['ship_zip']).'" maxlength="50" /> '.$this->_field_error($errors,'ship_zip').'</td>
	    	</tr>
	    	<tr>
	    		<td class="label"><label for="ship_phone">Phone:</label></td>
	    		<td class="field"><input type="text" name="order[ship_phone]" size="30" value="'.htmlentities($values['order']['ship_phone']).'" maxlength="50" /> (XXX-XXX-XXXX) '.$this->_field_error($errors,'ship_phone').'</td>
	    	</tr>
    	</table>
    	
    	</div>
    	
    	<div id="promo">
    	<table border="0" id="checkout">
	    	<tr>
	    		<td class="label"><label for="promo_code">Promo Code:</label></td>
	    		<td class="field"><input type="text" name="order[promo_code]" size="25" value="'.htmlentities($values['order']['promo_code']).'" maxlength="25" /> '.$this->_field_error($errors,'promo_code').'</td>
	    	</tr>
	    </table>
	    </div>
    	
	    <hr />
	    
    	<p>
    		<input class="button" type="submit" value="Continue to next step" /> or <a href="/products/types">continue shopping</a>
    	</p>
    	
    	</form>
    	</div>
	    ';
	    return $output;
	}
	
	//checkout step 2 view
	function _checkout_step_2($values=null,$errors=null) {
    	$cart = new Cart('shopping_cart');
    	
    	//get promo discount
    	$discount_text = '';
		if (isset($_SESSION['order']['promo_code'])) {
			$discount = $cart->getDiscount($_SESSION['order']['promo_code']);
			if($discount > 0)
				$discount_text = '...including -$'.number_format($discount,2).' discount.';
		}
		else
			$discount = 0;
			
		//get total
    	$total = number_format(($cart->getTotal()+$cart->getTax())-$discount,2);
    	
    	//get taxes
    	$taxes = number_format($cart->getTax(),2);
		
		$output = '	
		<div id="checkout">
		<div id="summary"><h3 class="price">$'.$total.'</h3>
		<i>...including FREE shipping.<br />
		...including $'.$taxes.' taxes.<br />
		'.$discount_text.'</i></div>
		<p id="step">step 2 of 2</p>
		<form action="" method="post">
		<input type="hidden" name="step" value="2" />
		
		<h3>Shipping Information</h3>
		
		<p>Shipping is <strong>FREE</strong></p>
		
		<h3>Payment Information</h3>
    	
		<table border="0" id="checkout">
	    	<tr>
	    		<td class="label" nowrap="1"><label for="cc_name">Name:</label></td>
	    		<td class="field"><input type="text" name="order[cc_name]" size="30" value="'.htmlentities($values['order']['cc_name']).'" /> '.$this->_field_error($errors,'cc_name').'</td>
	    	</tr>
			<tr>
	    		<td class="label" nowrap="1"><label for="cc_type">Card Type:</label></td>
	    		<td class="field">
	    			<select name="order[cc_type]">
	    				<option value="">-- Select Card --</option>	
	    				'.str_replace('value="'.$values['order']['cc_type'].'"','value="'.$values['order']['cc_type'].'" selected="selected"',$this->_cc_type_options()).'
					</select> '.$this->_field_error($errors,'cc_type').'
	    		</td>
	    	</tr>
	    	<tr>
	    		<td class="label" nowrap="1"><label for="cc_number">Card Number:</label></td>
	    		<td class="field"><input type="text" name="order[cc_number]" size="30" value="'.htmlentities($values['order']['cc_number']).'" /> '.$this->_field_error($errors,'cc_number').'</td>
	    	</tr>
	    	<tr>
	    		<td class="label" nowrap="1"><label for="cc_cvv">CVV:</label></td>
	    		<td class="field"><input type="text" name="order[cc_cvv]" size="4" value="'.htmlentities($values['order']['cc_cvv']).'" /> '.$this->_field_error($errors,'cc_cvv').' <a href="javascript:void(0);" onclick="$(\'cvv\').toggle();">what is this?</a>
	    		
	    		<div id="cvv" style="display: none;">
	    			<p>For MasterCard or Visa, it\'s the last three digits in the signature area on the back of your card. For American Express, it\'s the four digits on the front of the card.
	    		</div>
	    		
	    		</td>
	    	</tr>
	    	<tr>
	    		<td class="label" nowrap="1"><label for="cc_exp_month">Expiration Date:</label></td>
	    		<td class="field">
	    			<select name="order[cc_exp_month]">
						<option value="">-- Month --</option>
	    				'.str_replace('value="'.$values['order']['cc_exp_month'].'"','value="'.$values['order']['cc_exp_month'].'" selected="selected"',$this->_cc_exp_month_options()).'
					</select> '.$this->_field_error($errors,'cc_exp_month').'
			
					<select name="order[cc_exp_year]">
						<option value="">-- Year --</option>	
						'.str_replace('value="'.$values['order']['cc_exp_year'].'"','value="'.$values['order']['cc_exp_year'].'" selected="selected"',$this->_cc_exp_year_options()).'
					</select> '.$this->_field_error($errors,'cc_exp_year').'
	    		</td>
	    	</tr>
	    </table>
    	
	    <hr />
	    
    	<p>
    		<input class="button" type="submit" value="Complete my purchase" /> or 
    		<a href="/products/types">cancel, and continue shopping</a>
    	</p>
    	
    	</form>
    	</div>
	    ';
    	return $output;
    }
    
    function _send_order_email($to_email,$order_id,$order_arr,$variant_arr) {
    	require_once('Mail.php');
    	
    	$subject = 'eMed America Online Order #'.$order_id;
		$headers = "From: info@emedamerica.com\r\nReply-To: info@emedamerica.com";
		
		$msg_line = "-------------------------------------------\r\n";
		
		$msg = "Thank you for shopping with us! Your order details are below.\r\n\r\n";
		
		//billing info
		$msg .= $msg_line."Billing Information\r\n".$msg_line;
		$msg .= $order_arr['first_name']." ".$order_arr['last_name']."\r\n";
		$msg .= $order_arr['address']."\r\n";
		if ($order_arr['address2'])
			$msg .= $order_arr['address2']."\r\n";
		$msg .= $order_arr['city'].", ".$order_arr['state']." ".$order_arr['zip']."\r\n";
		$msg .= "Phone: ".$order_arr['phone']."\r\n";
		if ($order_arr['fax'])
			$msg .= "Fax: ".$order_arr['fax']."\r\n";
		
		//shipping info
		if ($order_arr['ship_first_name']) {
			$msg .= $msg_line."Shipping Information\r\n".$msg_line;
			$msg .= $order_arr['ship_first_name']." ".$order_arr['ship_last_name']."\r\n";
			$msg .= $order_arr['ship_address']."\r\n";
			if ($order_arr['ship_address2'])
				$msg .= $order_arr['ship_address2']."\r\n";
			$msg .= $order_arr['ship_city'].", ".$order_arr['ship_state']." ".$order_arr['ship_zip']."\r\n";
			$msg .= "Phone: ".$order_arr['ship_phone']."\r\n";
		}
		
		//payment info
		$subtotal = $order_arr['subtotal'];
		$shipping = 0;
		$tax = $order_arr['tax'];
		$discount = $order_arr['promo_discount'];
		$total = ($subtotal + $shipping + $tax) - $discount;
		
		$msg .= "\r\n".$msg_line."Payment Information\r\n".$msg_line;
		
		$msg .= "Rep. Name: ".$order_arr['rep_name']."\r\n\r\n";
		
		$msg .= "Card Name: ".$order_arr['cc_name']."\r\n";
		$msg .= "Card Type: ".$order_arr['cc_type']."\r\n";
		$msg .= "Card Number: ".$order_arr['cc_number']."\r\n";
		$msg .= "Card Expiration: ".$order_arr['cc_exp_month']."/".$order_arr['cc_exp_year']."\r\n\r\n";
		
		$msg .= "Subtotal: $".number_format($subtotal,2)."\r\n";
		$msg .= "Shipping: $".number_format($shipping,2)."\r\n";
		$msg .= "Tax: $".number_format($tax,2)."\r\n";
		if ($order_arr['promo_discount']) {
			$msg .= "Discount: $".number_format($discount,2)."\r\n";
		}
		$msg .= $msg_line."Total: $".number_format($total,2)."\r\n".$msg_line;
		
		//item information
		$msg .= "\r\n".$msg_line."Item Information\r\n".$msg_line;
		
		global $__FROG_CONN__;
		$sql = 'select * from ecommerce_order_variant where order_id = '.$order_id.';';
		$stmt = $__FROG_CONN__->prepare($sql);
		$stmt->execute();
		$variants = $stmt->fetchAll();
		
		if ($variants) {
			foreach ($variants as $variant) : 
				$product = Product::findById($variant['product_id']);
				$msg .= $product->title.' ('.$variant['title'].') - $'.$variant['price'].' x '.$variant['quantity'].' = $'.number_format($variant['price']*$variant['quantity'],2)."\r\n";	
			endforeach;
		}
		
		$mail = Mail::factory('smtp', array('host'=>'mail.myhost.com', 'port'=>25, 'auth'=>'login', 'username'=>'username', 'password'=>'password'));
		$headers = array('To'=>$to_email, 'From'=>'from@email.com', 'Subject'=>$subject, 'Reply-To'=>'replyto@email.com', 'Return-Path'=>'return@email.com');
		
		if ($mail->send($to_email, $headers, $msg))
			return true;
		else
			return false;
    }
    
    function _validate_cc_exp_date(Validation $array, $field) {
    	if ((int)$_POST['order']['cc_exp_month'] < date('m') && (int)$_POST['order']['cc_exp_year'] <= date('Y')) {
    		$array->add_error($field, 'expired_card');
    	}
    }
    
    function _field_error($errors,$field_name) {
    	if(isset($errors[$field_name]))
    		return '<span class="field_error">'.$this->_error_message($errors[$field_name]).'</span>';
    	else
    		return null;
    }
    
    function _error_message($error) {
    	$messages = array
		(
			'required' => 'Field cannot be blank.',
			'alpha' => 'Only alphabetic characters are allowed.',
			'phone' => 'Enter a valid phone number.',
			'email' => 'Enter a valid email address.',
			'length' => 'Input isn\'t right length.',
			'credit_card' => 'Enter a valid number.',
			'digit' => 'Enter numeric characters only.',
			'expired_card' => 'Your card has expired.'
		);
		
		return $messages[$error];
    }
	
	function _state_select() {
		$output = '
		<option value="">-- Select State --</option>
		<option value="AL">Alabama</option>
		<option value="AK">Alaska</option>
		<option value="AZ">Arizona</option>
		<option value="AR">Arkansas</option>
		<option value="CA">California</option>
		<option value="CO">Colorado</option>
		<option value="CT">Connecticut</option>
		<option value="DE">Delaware</option>
		<option value="DC">District of Columbia</option>
		<option value="FL">Florida</option>
		<option value="GA">Georgia</option>
		<option value="HI">Hawaii</option>
		<option value="ID">Idaho</option>
		<option value="IL">Illinois</option>
		<option value="IN">Indiana</option>
		<option value="IA">Iowa</option>
		<option value="KS">Kansas</option>
		<option value="KY">Kentucky</option>
		<option value="LA">Louisiana</option>
		<option value="ME">Maine</option>
		<option value="MD">Maryland</option>
		<option value="MA">Massachusetts</option>
		<option value="MI">Michigan</option>
		<option value="MN">Minnesota</option>
		<option value="MS">Mississippi</option>
		<option value="MO">Missouri</option>
		<option value="MT">Montana</option>
		<option value="NE">Nebraska</option>
		<option value="NV">Nevada</option>
		<option value="NH">New Hampshire</option>
		<option value="NJ">New Jersey</option>
		<option value="NM">New Mexico</option>
		<option value="NY">New York</option>
		<option value="NC">North Carolina</option>
		<option value="ND">North Dakota</option>
		<option value="OH">Ohio</option>
		<option value="OK">Oklahoma</option>
		<option value="OR">Oregon</option>
		<option value="PA">Pennsylvania</option>
		<option value="RI">Rhode Island</option>
		<option value="SC">South Carolina</option>
		<option value="SD">South Dakota</option>
		<option value="TN">Tennessee</option>
		<option value="TX">Texas</option>
		<option value="UT">Utah</option>
		<option value="VT">Vermont</option>
		<option value="VA">Virginia</option>
		<option value="WA">Washington</option>
		<option value="WV">West Virginia</option>
		<option value="WI">Wisconsin</option>
		<option value="WY">Wyoming</option>';
		return $output;
	}
	
	function _cc_type_options() {
		$output = '
		<option value="Visa">Visa</option>
		<option value="MasterCard">MasterCard</option>
		<option value="Amex">American Express</option>
		<option value="Discover">Discover</option>
		';
		return $output;
	}
	
	function _cc_exp_month_options() {
		$output ='
		<option value="01" >January</option>
		<option value="02" >February</option>
		<option value="03" >March</option>
		<option value="04" >April</option>
		<option value="05" >May</option>
		<option value="06" >June</option>
		<option value="07" >July</option>
		<option value="08" >August</option>
		<option value="09" >September</option>
		<option value="10" >October</option>
		<option value="11" >November</option>
		<option value="12" >December</option>
		';
		return $output;
	}
	
	function _cc_exp_year_options() {
		$output = '';
		
		for ($year = date('Y'); $year <= date('Y')+6; $year++)
			$output .= '<option value="'.$year.'">'.$year.'</option>';
			
		return $output;
	}
    
    /**
	 * Product
	 */
    public function product() {
       $this->product_index();
    }
    
    public function product_index() {
    	$page = !empty($_GET['page']) ? $_GET['page']: 1;
    	$keywords = !empty($_GET['keywords']) ? $_GET['keywords']: '';
    	$per_page = 15;
    	
		$products_total = Product::find(array(
			'where' => 'title like \'%'.$keywords.'%\''
		));
    	$products = Product::find(array(
			'limit' => $per_page,
			'offset' => ($page-1)*($per_page),
			'where' => 'title like \'%'.$keywords.'%\''
		));
    	
    	$pagination = new Pagination(array(
			'base_url' => URL_PUBLIC.'/'.ADMIN_DIR.'/plugin/ecommerce/product/?keywords='.$keywords.'&page=',
			'total_rows' => count($products_total),
			'per_page' => $per_page,
			'num_links' => 4,
			'cur_page' => $page
		));
		
		$this->display('ecommerce/views/products/index', array(
			'products' => $products,
			'pagination' => $pagination
		));
    }
    
	// TODO: set product parent page id in a setting set vendor and type parent id's dynamically
    public function product_create()
    {
        if (get_request_method() == 'POST')
		{
	    	//get new type id if a new one was created
	    	if ($_POST['product_type']['title']) {
				//save type
		    	$type_id = $this->_product_save(null,'product_type','ProductType');
		    	$_POST['product']['type_id'] = $type_id;

				//add new type page
				$page_data = array("is_protected"=>1,"parent_id"=>87,"title"=>$_POST['product_type']['title'],"slug"=>$_POST['product_type']['slug'],"breadcrumb"=>$_POST['product_type']['title']);
		    	$page = new Page($page_data);
				$page->save();
		    }
	    	
	    	//get new vendor id if a new one was created
	    	if ($_POST['product_vendor']['title']) {
	    		//save vendor
				$vendor_id = $this->_product_save(null,'product_vendor','ProductVendor');
	    		$_POST['product']['vendor_id'] = $vendor_id;
	
				//add new vendor page
				$page_data = array("is_protected"=>1,"parent_id"=>86,"title"=>$_POST['product_vendor']['title'],"slug"=>$_POST['product_vendor']['slug'],"breadcrumb"=>$_POST['product_vendor']['title']);
		    	$page = new Page($page_data);
				$page->save();
	    	}
	    	
	    	//create new page
	    	//TODO: set product parent page id in a setting
	    	$page_data = array("is_protected"=>1,"parent_id"=>9,"title"=>$_POST['product']['title'],"slug"=>$_POST['product']['slug'],"breadcrumb"=>$_POST['product']['title']);
	    	$page = new Page($page_data);
	    	if ($page->save()) {
	    		//get page id
	    		$_POST['product']['page_id'] = $page->id;
	    	}
	    	
	    	//save product
	    	$product_id = $this->_product_save(null,'product','Product');
	    	
	    	//save product variant
	    	$_POST['product_variant']['product_id'] = $product_id;
	    	$this->_product_save(null,'product_variant','Product');
	    	
	    	//save images
	    	if (isset($_SESSION['product_images'])) {
	    		$this->_images_save($product_id,$_SESSION['product_images']);
	    		unset($_SESSION['product_images']);
	    	}
	    	
	    	//add log entry
	    	$this->_insert_log('New product: <a href="'.get_url('plugin/ecommerce/product_update/'.$product_id).'">'.$_POST['product']['title'].'</a>');
	    	
	    	redirect(get_url('plugin/ecommerce/product'));
	    }
	    
	    //pass types and vendors for select boxes
	    $types = ProductType::findAll();
	    $vendors = ProductVendor::findAll();
	    
        $this->display('ecommerce/views/products/create', array(
            'action'  => 'create',
            'types' => $types,
            'vendors' => $vendors
        ));
    }
    
    public function product_update($id=null)
    {
    	if (is_null($id))
            redirect(get_url('plugin/ecommerce'));
            
        if ( ! $product = Product::findById($id))
        {
            Flash::set('error', __('Product not found!'));
            redirect(get_url('plugin/ecommerce'));
        }
        
        if (get_request_method() == 'POST') {
            //get new type id if a new one was created
	    	if ($_POST['product_type']['title']) {
				//save type
		    	$type_id = $this->_product_save(null,'product_type','ProductType');
		    	$_POST['product']['type_id'] = $type_id;
		
				//add new type page
				$page_data = array("is_protected"=>1,"parent_id"=>87,"title"=>$_POST['product_type']['title'],"slug"=>$_POST['product_type']['slug'],"breadcrumb"=>$_POST['product_type']['title']);
		    	$page = new Page($page_data);
				$page->save();
		    }
	    	
	    	//get new vendor id if a new one was created
	    	if ($_POST['product_vendor']['title']) {
	    		//save vendor
				$vendor_id = $this->_product_save(null,'product_vendor','ProductVendor');
	    		$_POST['product']['vendor_id'] = $vendor_id;
	
				//add new vendor page
				$page_data = array("is_protected"=>1,"parent_id"=>86,"title"=>$_POST['product_vendor']['title'],"slug"=>$_POST['product_vendor']['slug'],"breadcrumb"=>$_POST['product_vendor']['title']);
		    	$page = new Page($page_data);
				$page->save();
	    	}
	    	
	    	//save product
	    	$product_id = $this->_product_save($id,'product','Product');
	    	
	    	//insert log
	    	$this->_insert_log('Product <a href="'.get_url('plugin/ecommerce/product_update/'.$product_id).'">'.$_POST['product']['title'].'</a> was updated.');
	    	
	    	//save images
	    	if (isset($_SESSION['product_images']))
	    		$this->_images_save($product_id,$_SESSION['product_images']);
	    	
	    	//save product page
	    	$product = Product::findById($id);
		    if ($product) {
		    	$page = Record::findByIdFrom('Page', $product->page_id);
		    	$page_data = array("is_protected"=>1,"title"=>$_POST['product']['title'],"slug"=>$_POST['product']['slug'],"breadcrumb"=>$_POST['product']['title'],"created_on_time"=>null,"published_on_time"=>null);
		    	$page->setFromData($page_data);
		    	$page->save();
		    }
            
            redirect(get_url('plugin/ecommerce/product'));
        }
        
	    $types = ProductType::findAll();
	    $vendors = ProductVendor::findAll();
	    $images = Record::findAllFrom('ProductImage', 'product_id=? order by position', array($id));
	    $variants = Record::findAllFrom('ProductVariant', 'product_id=? order by position', array($id));
		$files = Record::findAllFrom('ProductFile', 'product_id=? order by position', array($id));
		$videos = Record::findAllFrom('ProductVideo', 'product_id=? order by position', array($id));
        
        $this->display('ecommerce/views/products/update', array(
            'action'  => 'update',
            'product' => $product,
            'types' => $types,
            'vendors' => $vendors,
            'images' => $images,
            'variants' => $variants,
			'files' => $files,
			'videos' => $videos
        ));
    }
    
    public function product_delete($id)
    {
        if ($product = Record::findByIdFrom('Product', $id))
        {
        	$product_title = $product->title;
        	
        	//delete page for product
        	if ($page = Record::findByIdFrom('Page', $product->page_id))
            	$page->delete();
            
            //delete variants
            Record::deleteWhere('ecommerce_product_variant', 'product_id=?', array($id));
            
            //delete images
            Record::deleteWhere('ecommerce_product_image', 'product_id=?', array($id));
            
            //delete files
            Record::deleteWhere('ecommerce_product_file', 'product_id=?', array($id));
            
            //delete videos
            Record::deleteWhere('ecommerce_product_video', 'product_id=?', array($id));
        	
            if ($product->delete()) {
            	//add log entry
	    		$this->_insert_log('Product \''.$product_title.'\' was deleted.');
	    		
                Flash::set('success', __('Product has been deleted!'));
            }
            else
                Flash::set('error', __('Product has not been deleted!'));
        }
        else Flash::set('error', __('Product not found!'));
        
        redirect(get_url('plugin/ecommerce/product'));
    }
    
    public function product_show($slug) {
    	$output = '';
    	$product = Product::findBySlug($slug);
    	$images = ProductImage::findByProduct($slug);
    	$variants = ProductVariant::findByProduct($slug);
    	$files = ProductFile::findByProduct($slug);
    	$videos = ProductVideo::findByProduct($slug);
    	$related_products = $this->products_related($slug);
    	
    	if ($product) {
    		$output = '<div id="product_images">';
    		
    		if ($images) {
	    		foreach ($images as $image) : 
	    			$output .= '<img src="/public/ecommerce/images/products/'.$image->filename.'" />';
	    		endforeach;
			}
			
			if ($variants) {
				foreach ($variants as $variant) : 
					$description = '';
					if ($variant->description)
						$description = $variant->description.'<br />';
					
					$output .= '
					<form class="cart_form" action="/products/cart" method="post">
						<input type="hidden" name="variant_id" value="'.$variant->id.'" />
						<label>'.str_replace('Default','',$variant->title).'<br />'.$description.'</label>';
						
						if ($variant->price > 0) {
							$output .= '<span class="price">$'.number_format($variant->price,2).'</span> <input type="hidden" name="quantity" value="1" size="2" />
							<input type="submit" name="submit" value="Add to Cart" />';
						}
					$output .= '</form>';
				endforeach;
			}
			
			if ($files) {
				$output .= '<div id="files"><h3>Files</h3><ul>';
				foreach ($files as $file) : 
					$output .= '<li><a href="'.$file->filename.'" rel="external">'.$file->title.'</a></li>';
				endforeach;
				$output .= '</ul></div>';
			}
			
			if ($videos) {
				$output .= '<div id="videos"><h3>Videos</h3><ul>';
				foreach ($videos as $video) : 
					$output .= '<li><a href="'.$video->filename.'" rel="shadowbox">'.$video->title.'</a></li>';
				endforeach;
				$output .= '</ul></div>';
			}
			
			$output .= '</div>';
			
    		$output .= $product->description;
    		
    		if ($related_products) {
    			$output .= '<br clear="all"><h3>Related Products</h3>';
    			$output .= $this->products_related($slug);
    		}
    	}
    	
    	return $output;
    }
    
    public function products_related($slug) {
    	$product = Product::findBySlug($slug);
    	if (!empty($product->tags)) {
    		$products = Product::findRelated($product->tags,$slug);
    		if ($products)
    			return $this->_product_grid($products);
    		else
    			return null;
    	}
    	else
    		return null;
    }
    
    public function products_by_type($slug) {
    	$page = isset($_GET['page']) ? $_GET['page']: 1;
    	$per_page = 16;
		
    	$products_total = Product::findByTypeSlug(null,$slug);
		$products = Product::findByTypeSlug(array(
			'limit' => $per_page,
			'offset' => ($page-1)*($per_page)
		),$slug);
		
    	return $this->_product_grid($products,$products_total,$page,$per_page);
    }
    
    public function products_by_vendor($slug) {
    	$page = !empty($_GET['page']) ? $_GET['page']: 1;
    	$per_page = 16;
		
    	$products_total = Product::findByVendorSlug(null,$slug);
		$products = Product::findByVendorSlug(array(
			'limit' => $per_page,
			'offset' => ($page-1)*($per_page)
		),$slug);
		
    	return $this->_product_grid($products,$products_total,$page,$per_page);
    }
    
	public function products_search($keywords) {
		$page = isset($_GET['page']) ? $_GET['page']: 1;
		$per_page = 16;

		$products_total = Product::search(array(
			'where' => 'p.title like \'%'.$keywords.'%\' or p.description like \'%'.$keywords.'%\''
		));
		$products = Product::search(array(
			'limit' => $per_page,
			'offset' => ($page-1)*($per_page),
			'where' => 'p.title like \'%'.$keywords.'%\' or p.description like \'%'.$keywords.'%\''
		));

		return $this->_product_grid($products,$products_total,$page,$per_page,$keywords);
	}
	
    public function products_all() {
		$page = isset($_GET['page']) ? $_GET['page']: 1;
    	$per_page = 16;
		
    	$products_total = Product::findAll();
		$products = Product::findAll(array(
			'limit' => $per_page,
			'offset' => ($page-1)*($per_page)
		));
		
    	return $this->_product_grid($products,$products_total,$page,$per_page);
    }
    
    public function product_types_nav() {
    	$output = '';
    	$types = ProductType::nav();
    	
    	$output .= '<table class="ecommerce types">';
		
    	$i = 0;
    	$col_num = 4;
		foreach($types as $type) :
			if ($type->title != 'Bio-Medwash') {
				if ($i % $col_num == 0) {
					if ($i > 1) {
						$output .= '</tr>';
						$i = 0;
					}
					$output .= '<tr>';
				}
	
				$output .= '<td><a href="/products/types/'.$type->slug.'">'.$type->title.'<br />';
				if ($type->image)
					$output .= '<img src="'.$type->image.'" width="94" /></a><br /><br />';
					$output .= '<a href="/products/types/'.$type->slug.'" class="shop">Shop Now</a></td>';
				$i++;
			}
		endforeach;
		
		while($i%$col_num != 0) {
			$output .= '<td>&nbsp;</td>';
			$i++;
		}
		
		$output .= '</tr></table>';
    	
    	return $output;
    }
	
	
    
    public function get_cart_product($variant_id) {
    	$product = Product::getCartProduct($variant_id);
    	return $product;
    }
    
	function _product_grid($products,$products_total=null,$page=null,$per_page=null,$keywords=null) {
		$output = '';
		$pages_output = '';
		
		if ($products) {
			//pagination
			if ($products_total) {
				$pagination = new Pagination(array(
					'base_url' => '?page=',
					'total_rows' => count($products_total),
					'per_page' => $per_page,
					'num_links' => 9,
					'cur_page' => $page
				));
				
				if ($pagination->total_rows > $pagination->per_page) {
					$pages_output .= '<div id="pagination"><p>Pages: '.str_replace('?page=/','?page=',$pagination->createLinks()).'</p></div>';
					
					if($keywords)
						$pages_output = str_replace('?page=','?keywords='.$keywords.'&page=',$pages_output);
				}
			}
					
			$i = 0;
			$col_num = 4;
			$output .= '<table class="ecommerce">';
			
    		foreach($products as $product) :
    			if ($i % $col_num == 0) {
    				if ($i > 1)
    					$output .= '</tr>';
    				$output .= '<tr>';
    			}
    			
    			$output .= '<td>';
    			if ($product->image)
    				$output .= '<a href="/products/'.$product->slug.'"><img src="/public/ecommerce/images/products/'.str_replace('.','_tn.',$product->image).'" width="100" /></a><br />';
    			$output .= '<a href="/products/'.$product->slug.'">'.$product->title.'</a><br />';
				if ($product->price > 0)
					$output .= '$'.number_format($product->price,2);
    			
    			//$output .= '<br /><a class="more" href="/products/'.$product->slug.'">More Info &raquo;</a>';
    			$output .= '</td>';
    			$i++;
    		endforeach;
    		
    		while($i % $col_num != 0) {
				$output .= '<td>&nbsp;</td>';
				$i++;
			}
    		
    		$output .= '</tr></table>';
    	}
		
		$output = $pages_output . $output . $pages_output;
    	
		if (empty($output))
			$output = '<p>There are no products to display.</p>';
		
    	return $output;
	}
	
	function _product_save($id=null,$model,$model_name)
	{
		$saved = false;

		if ($id != null)
		{
			$record = Record::findByIdFrom($model, $id);
			$record->setFromData($_POST[$model]);
			if ($record->save())
				$saved = true;
		}
		else
		{
			$record = Record::insert('ecommerce_'.$model, $_POST[$model]);
			$saved = true;
		}

		if ($saved)
			Flash::set('success', __($model_name.' has been saved!'));
		else
			Flash::set('error', __($model_name.' has not been saved!'));

		if ($id)
			return $id;
		else
			return Record::lastInsertId();
	}
    
    /**
	 * Collections
	 */
    public function collection() {
       $this->collection_index();
    }
	
	public function collection_index()
	{
		$page = isset($_GET['page']) ? $_GET['page']: 1;
		$per_page = 15;

		$collections_total = Collection::findAll();
		$collections = Collection::find(array(
			'limit' => $per_page,
			'offset' => ($page-1)*($per_page)
			));

		$pagination = new Pagination(array(
			'base_url' => '/'.ADMIN_DIR.'/plugin/ecommerce/collection_index',
			'total_rows' => count($collections_total),
			'per_page' => $per_page,
			'num_links' => 8,
			'cur_page' => $page
		));

		$this->display('ecommerce/views/collections/index', array(
			'collections' => $collections,
			'pagination' => $pagination
		));
	}

	public function collection_create()
	{        
		if (get_request_method() == 'POST') {
			$collection_id = $this->_collection_save(null,'collection','Collection');

			//add log entry
			$this->_insert_log('New collection: <a href="'.get_url('plugin/ecommerce/collection_update/'.$collection_id).'">'.$_POST['collection']['title'].'</a>');

			redirect(get_url('plugin/ecommerce/collection_update/'.$collection_id));
		}

		$this->display('ecommerce/views/collections/create', array(
			'action'  => 'create'
		));
	}

	public function collection_update($id=null)
	{
		if (is_null($id))
			redirect(get_url('plugin/ecommerce/collection'));

		if ( ! $collection = Collection::findById($id))
		{
			Flash::set('error', __('Collection not found!'));
			redirect(get_url('plugin/ecommerce/collection'));
		}

		if (get_request_method() == 'POST') {
			$collection_id = $this->_collection_save($id,'collection','Collection');
			
			//insert log
			$this->_insert_log('Collection <a href="'.get_url('plugin/ecommerce/collection_update/'.$collection_id).'">'.$_POST['collection']['title'].'</a> was updated.');
			
			redirect(get_url('plugin/ecommerce/collection'));
		}
		
		//get products
		global $__FROG_CONN__;
		$sql = 'select pc.id, pc.collection_id, pc.product_id, p.title, pc.position from ecommerce_collection c inner join ecommerce_product_collection pc on c.id = pc.collection_id inner join ecommerce_product p on p.id = pc.product_id where c.id = '.$id.' order by pc.position;';
		$stmt = $__FROG_CONN__->prepare($sql);
		$stmt->execute();
		$products = $stmt->fetchAll();
		
		$this->display('ecommerce/views/collections/update', array(
			'action'  => 'update',
			'collection' => $collection,
			'products' => $products
		));
	}

	public function collection_delete($id)
	{
		if ($collection = Record::findByIdFrom('Collection', $id))
		{
			if ($collection->delete()) {
				Flash::set('success', __('Collection has been deleted!'));
				$this->_insert_log('Collection \''.$collection->title.'\' was deleted.');
			}
			else
				Flash::set('error', __('Collection has not been deleted!'));
		}
		else Flash::set('error', __('Collection not found!'));

		redirect(get_url('plugin/ecommerce/collection'));
	}
	
	public function collection_products_reorder()
	{
		global $__FROG_CONN__;
		parse_str($_POST['data']);
		for ($i = 0; $i < count($collection_products); $i++) {
			$pos = $i+1;
			$sql = 'update ecommerce_product_collection set position = '.$pos.' where id = '.$collection_products[$i].';';
			$stmt = $__FROG_CONN__->prepare($sql);
			$stmt->execute();
			if ($stmt)
				echo 'success'.$i;
		}
	}
	
	public function collection_product_delete($id)
	{
		global $__FROG_CONN__;
		$sql = 'delete from ecommerce_product_collection where id = '.$id.';';
		$stmt = $__FROG_CONN__->prepare($sql);
		$stmt->execute();
	}
	
	public function collection_product_search() {
		$keywords = !empty($_GET['keywords']) ? $_GET['keywords']: '';
		$collection_id = $_GET['collection_id'];
				
		$products = Product::search(array(
			'limit' => 10,
			'offset' => 0,
			'where' => 'p.title like \'%'.$keywords.'%\' or p.description like \'%'.$keywords.'%\''
		));
		
		if ($products) {
			$output = '<ul>';
			
			foreach ($products as $product) {
				$output .= '<li id="possible-product-'.$product->id.'"><form id="possible-product-'.$product->id.'-form"><input type="hidden" name="ecommerce_product_collection[collection_id]" value="'.$collection_id.'" /><input type="hidden" name="ecommerce_product_collection[product_id]" value="'.$product->id.'" /></form>';
				$output .= '<a href="#" onclick="collection_product_create('.$product->id.');return false;">'.$product->title.'</a></li>';
			}
	
			$output .= '</ul>';
		}
		else
			$output = '<p class="normal">No products matched your search.</p>';
		
		echo $output;
	}
	
	public function collection_product_create() {
		$record = Record::insert('ecommerce_product_collection', $_POST['ecommerce_product_collection']);
		echo Record::lastInsertId();
	}
	
	public function collection_product_html($id) {
		global $__FROG_CONN__;
		$sql = 'select p.id, p.title from ecommerce_product p inner join ecommerce_product_collection pc on p.id = pc.product_id where pc.id = '.$id.';';
		$stmt = $__FROG_CONN__->prepare($sql);
		$stmt->execute();
		$record = $stmt->fetchObject();
		
		if ($record) {
			echo '<div id="product_'.$id.'">
				<div class="tools" style="display: none;">
					<a href="#" onclick="collection_product_delete('.$id.');return false;"><img alt="Trash" src="/frog/plugins/ecommerce/images/trash.gif" /></a>
				</div>

				<h3>'.$record->title.' <img class="drag_handle" src="/'.ADMIN_DIR.'/images/drag_to_sort.gif" width="55" height="11" /></h3>
			</div>
			<script type="text/javascript" language="javascript" charset="utf-8">
			new ToolBox(\'product_'.$record->id.'\');
			</script>';
		}
	}
	
	public function collection_show($id) {
		global $__FROG_CONN__;
		$sql = 'select p.*, (select i.filename from ecommerce_product_image i where i.product_id = p.id order by position limit 0,1) as image, (select v.price from ecommerce_product_variant v where v.product_id = p.id order by position limit 0,1) as price from ecommerce_product p inner join ecommerce_product_collection pc on p.id = pc.product_id where pc.collection_id = '.$id.' order by pc.position limit 0,3;';
		$stmt = $__FROG_CONN__->prepare($sql);
		$stmt->execute();
		
		$products = array();
		while ($product = $stmt->fetchObject('Product'))
			$products[] = $product;

		return '<div id="ecommerce" class="featured">'.$this->_product_grid($products).'</div>';
	}
	
	function _collection_save($id=null,$model,$model_name)
	{
		$saved = false;

		if ($id != null) {
			$record = Record::findByIdFrom($model, $id);
			$record->setFromData($_POST[$model]);
			if ($record->save())
				$saved = true;
		}
		else {
			$record = Record::insert('ecommerce_'.$model, $_POST[$model]);
			$saved = true;
		}

		if ($saved)
			Flash::set('success', __($model_name.' has been saved!'));
		else
			Flash::set('error', __($model_name.' has not been saved!'));

		if ($id)
			return $id;
		else
			return Record::lastInsertId();
	}
    
    /**
	 * Images
	 */
    public function image_upload($id) {
    	$uploaddir = '/home/admin/public_html/yoursite/public/ecommerce/images/products/';
		$uploadfile = $uploaddir . basename($_FILES['product_image']['name']);
		
		if (move_uploaded_file($_FILES['product_image']['tmp_name'], $uploadfile)) {
			$_SESSION['product_images'][] = $_FILES;
			
			//create thumbnail
			$this->_image_create_thumb($uploadfile,str_replace('.','_tn.',$uploadfile),100,100);
			
			if ($id)
				$image_id = $this->_images_save($id,$_SESSION['product_images']);
			
			echo $image_id;
		}
		else
			echo "error";
    }
    
    public function image_reorder()
    {
    	global $__FROG_CONN__;
    	parse_str($_POST['data']);
		for ($i = 0; $i < count($product_images); $i++) {
			$pos = $i+1;
			$sql = 'update ecommerce_product_image set position = '.$pos.' where id = '.$product_images[$i].';';
			$stmt = $__FROG_CONN__->prepare($sql);
			$stmt->execute();
			if ($stmt)
				echo 'success'.$i;
		}
    }
    
    public function image_delete($id)
    {
        if ($image = Record::findByIdFrom('ProductImage', $id))
        	$image->delete();
    }
    
    public function image_html($id) {
    	$record = Record::findByIdFrom('ProductImage', $id);
		if ($record) {
			echo '<div class="sorting" id="image_'.$id.'">
				<img src="/public/ecommerce/images/products/'.str_replace('.','_tn.',$record->filename).'" width="100" height="100" /><br />
				<a class="delete" href="#" onclick="image_delete('.$id.');return false;">Delete</a>
			</div>';
		}
    }
	
	function _images_save($product_id,$images)
	{
		global $__FROG_CONN__;

		//get max position for existing product images
		$sql = 'select max(position) as position from ecommerce_product_image where product_id = '.$product_id.' limit 1;';
		$stmt = $__FROG_CONN__->prepare($sql);
		$stmt->execute();
		$max_pos = $stmt->fetchObject();

		if ($images) {
			if ($max_pos)
				$cnt = $max_pos->position + 1;
			else
				$cnt = 1;

			foreach($images as $image) :
				$image_data = array("product_id"=>$product_id,"filename"=>$image['product_image']['name'],"position"=>$cnt);
				$record = Record::insert('ecommerce_product_image', $image_data);
				$cnt++;
			endforeach;

			unset($_SESSION['product_images']);

			return Record::lastInsertId();
		}
	}
	
	function _image_create_thumb($name,$filename,$new_w,$new_h)
	{
		$system=explode(".",$name);

		if (preg_match("/jpg|jpeg|JPG/",$system[1])){$src_img=imagecreatefromjpeg($name);}
		if (preg_match("/png/",$system[1])){$src_img=imagecreatefrompng($name);}

		$old_x=imageSX($src_img);
		$old_y=imageSY($src_img);

		if ($old_x > $old_y) {
			$thumb_w=$new_w;
			$thumb_h=$old_y*($new_h/$old_x);
		}

		if ($old_x < $old_y) {
			$thumb_w=$old_x*($new_w/$old_y);
			$thumb_h=$new_h;
		}

		if ($old_x == $old_y) {
			$thumb_w=$new_w;
			$thumb_h=$new_h;
		}

		$dst_img=ImageCreateTrueColor($thumb_w,$thumb_h);
		imagecopyresampled($dst_img,$src_img,0,0,0,0,$thumb_w,$thumb_h,$old_x,$old_y); 

		if (preg_match("/png/",$system[1]))
			imagepng($dst_img,$filename); 
		else
			imagejpeg($dst_img,$filename); 

		imagedestroy($dst_img); 
		imagedestroy($src_img); 
	}
    
    /**
	 * Variants
	 */
    public function variant_create() {
		$record = Record::insert('ecommerce_product_variant', $_POST['product_variant']);
		echo Record::lastInsertId();
    }
    
    public function variant_update($id) {    	
    	$record = Record::findByIdFrom('ProductVariant', $id);
		$record->setFromData($_POST['product_variant']);
		if ($record->save())
			echo 'success';
    }
    
    public function variant_reorder()
    {
    	global $__FROG_CONN__;
    	parse_str($_POST['data']);
		for ($i = 0; $i < count($product_variants); $i++) {
			$pos = $i+1;
			$sql = 'update ecommerce_product_variant set position = '.$pos.' where id = '.$product_variants[$i].';';
			$stmt = $__FROG_CONN__->prepare($sql);
			$stmt->execute();
			if ($stmt)
				echo 'success'.$i;
		}
    }
    
    public function variant_delete($id)
    {
        if ($variant = Record::findByIdFrom('ProductVariant', $id))
        	$variant->delete();
    }
    
    public function variant_info_html($id) {
    	$record = Record::findByIdFrom('ProductVariant', $id);
		if ($record) {
			echo '<div id="variant_'.$id.'">
				<div class="tools" style="display: none;">
					<a href="#" onclick="variant_delete('.$id.');return false;"><img alt="Trash" src="/frog/plugins/ecommerce/images/trash.gif" /></a>&nbsp;
					<a href="#" onclick="variant_form_toggle('.$id.');return false;">Edit</a>
				</div>
				
				<h3><span id="variant_title_'.$id.'">'.$record->title.'</span> <img class="drag_handle" src="/'.ADMIN_DIR.'/images/drag_to_sort.gif" width="55" height="11" /></h3>
				<div id="variant_description_'.$id.'">'.$record->description.'</div>
				<table>
					<tr>
						<th>Price</th>
						<th>SKU</th>
						<th>Quantity</th>
						<th>Weight</th>
					</tr>
					<tr>
						<td id="variant_price_'.$id.'">$'.$record->price.'</td>
						<td id="variant_sku_'.$id.'">'.$record->sku.'</td>
						<td id="variant_quantity_'.$id.'">'.$record->quantity.'</td>
						<td id="variant_weight_'.$id.'">'.$record->weight.' lbs.</td>
					</tr>
				</table>
			</div>
			<script type="text/javascript" language="javascript" charset="utf-8">
			new ToolBox(\'variant_'.$id.'\');
			</script>';
		}
    }
    
    public function variant_form_html($id) {
    	$record = Record::findByIdFrom('ProductVariant', $id);
		if ($record) {
			echo '<div id="variant_form_'.$id.'" class="form" style="display: none;">
				<form method="post" action="/'.ADMIN_DIR.'/plugin/ecommerce/variant_update/'.$id.'" onsubmit="variant_update('.$id.');return false;">
				<p>
					<label for="title">'.__('Title').'</label>
					<input id="variant_form_title_'.$id.'" class="textbox" type="text" name="product_variant[title]" id="variant_title" size="30" value="'.htmlentities($record->title, ENT_COMPAT, 'UTF-8').'" />
				</p>
				
				<p>
					<label for="description">'.__('Description').'</label>
					<input id="variant_form_description_'.$id.'" class="textbox" type="text" name="product_variant[description]" id="variant_description" size="30" maxlength="255" value="'.htmlentities($record->description, ENT_COMPAT, 'UTF-8').'" />
				</p>
				
				<p>
					<label for="price">'.__('Price').'</label>
					<input id="variant_form_price_'.$id.'" class="textbox" type="text" name="product_variant[price]" id="variant_price" size="8" value="'.htmlentities($record->price, ENT_COMPAT, 'UTF-8').'" /> USD
				</p>
								
				<p>
					<label for="weight">'.__('Weight').'</label>
					<input id="variant_form_weight_'.$id.'" class="textbox" type="text" name="product_variant[weight]" id="variant_weight" size="8"  value="'.htmlentities($record->weight, ENT_COMPAT, 'UTF-8').'" /> lbs
				</p>
				
				<p>
					<label for="sku"><abbr title="'.__('Stock Keeping Unit').'">'.__('SKU').'</abbr></label>
					<input id="variant_form_sku_'.$id.'" class="textbox" type="text" name="product_variant[sku]" id="variant_sku" size="35" value="'.htmlentities($record->sku, ENT_COMPAT, 'UTF-8').'" />
				</p>
				
				<p>
					<label for="quantity">'.__('Quantity').'</label>
					<input id="variant_form_quantity_'.$id.'" class="textbox" type="text" name="product_variant[quantity]" id="variant_quantity" size="5" value="'.htmlentities($record->quantity, ENT_COMPAT, 'UTF-8').'" />
				</p>
				<p class="last">
					<input name="commit" type="submit" value="Save"> or <a href="#" onclick="variant_form_cancel('.$id.');return false;">Cancel</a>
				</p>
				</form>
			</div>';
		}
    }

	/**
	 * Files
	 */
    public function file_create() {
		$record = Record::insert('ecommerce_product_file', $_POST['product_file']);
		echo Record::lastInsertId();
    }
    
    public function file_update($id) {    	
    	$record = Record::findByIdFrom('ProductFile', $id);
		$record->setFromData($_POST['product_file']);
		if ($record->save())
			echo 'success';
    }
    
    public function file_reorder()
    {
    	global $__FROG_CONN__;
    	parse_str($_POST['data']);
		for ($i = 0; $i < count($product_files); $i++) {
			$pos = $i+1;
			$sql = 'update ecommerce_product_file set position = '.$pos.' where id = '.$product_files[$i].';';
			$stmt = $__FROG_CONN__->prepare($sql);
			$stmt->execute();
			if ($stmt)
				echo 'success'.$i;
		}
    }
    
    public function file_delete($id)
    {
        if ($file = Record::findByIdFrom('ProductFile', $id))
        	$file->delete();
    }
    
    public function file_info_html($id) {
    	$record = Record::findByIdFrom('ProductFile', $id);
		if ($record) {
			echo '<div id="file_'.$id.'">
				<div class="tools" style="display: none;">
					<a href="#" onclick="file_delete('.$id.');return false;"><img alt="Trash" src="/frog/plugins/ecommerce/images/trash.gif" /></a>&nbsp;
					<a href="#" onclick="file_form_toggle('.$id.');return false;">Edit</a>
				</div>
				
				<h3><span id="file_title_'.$id.'">'.$record->title.'</span> <img class="drag_handle" src="/'.ADMIN_DIR.'/images/drag_to_sort.gif" width="55" height="11" /></h3>
			</div>
			<script type="text/javascript" language="javascript" charset="utf-8">
			new ToolBox(\'file_'.$id.'\');
			</script>';
		}
    }
    
    public function file_form_html($id) {
    	$record = Record::findByIdFrom('ProductFile', $id);
		if ($record) {
			echo '<div id="file_form_'.$id.'" class="form" style="display: none;">
				<form method="post" action="/'.ADMIN_DIR.'/plugin/ecommerce/file_update/'.$id.'" onsubmit="file_update('.$id.');return false;">
				<p>
					<label for="title">'.__('Title').'</label>
					<input id="file_form_title_'.$id.'" class="textbox" type="text" name="product_file[title]" id="variant_title" size="30" value="'.htmlentities($record->title, ENT_COMPAT, 'UTF-8').'" />
				</p>
				
				<p>
					<label for="filename">'.__('File').'</label>
					<input id="file_form_filename_'.$id.'" class="textbox" type="text" name="product_file[filename]" id="variant_filename" size="30" maxlength="50" value="'.htmlentities($record->filename, ENT_COMPAT, 'UTF-8').'" />
					<a href="#" onclick="browse_server(\'file_form_filename_'.$id.');return false;"><img src="/frog/plugins/ecommerce/images/file_manager_16x16.gif" width="16" height="16" /></a>
				</p>
				
				<p class="last">
					<input name="commit" type="submit" value="Save"> or <a href="#" onclick="file_form_cancel('.$id.');return false;">Cancel</a>
				</p>
				</form>
			</div>';
		}
    }
    
    /**
	 * Videos
	 */
    public function video_create() {
		$record = Record::insert('ecommerce_product_video', $_POST['product_video']);
		echo Record::lastInsertId();
    }
    
    public function video_update($id) {    	
    	$record = Record::findByIdFrom('ProductVideo', $id);
		$record->setFromData($_POST['product_video']);
		if ($record->save())
			echo 'success';
    }
    
    public function video_reorder()
    {
    	global $__FROG_CONN__;
    	parse_str($_POST['data']);
		for ($i = 0; $i < count($product_videos); $i++) {
			$pos = $i+1;
			$sql = 'update ecommerce_product_video set position = '.$pos.' where id = '.$product_videos[$i].';';
			$stmt = $__FROG_CONN__->prepare($sql);
			$stmt->execute();
			if ($stmt)
				echo 'success'.$i;
		}
    }
    
    public function video_delete($id)
    {
        if ($video = Record::findByIdFrom('ProductVideo', $id))
        	$video->delete();
    }
    
    public function video_info_html($id) {
    	$record = Record::findByIdFrom('ProductVideo', $id);
		if ($record) {
			echo '<div id="video_'.$id.'">
				<div class="tools" style="display: none;">
					<a href="#" onclick="video_delete('.$id.');return false;"><img alt="Trash" src="/frog/plugins/ecommerce/images/trash.gif" /></a>&nbsp;
					<a href="#" onclick="video_form_toggle('.$id.');return false;">Edit</a>
				</div>
				
				<h3><span id="video_title_'.$id.'">'.$record->title.'</span> <img class="drag_handle" src="/'.ADMIN_DIR.'/images/drag_to_sort.gif" width="55" height="11" /></h3>
			</div>
			<script type="text/javascript" language="javascript" charset="utf-8">
			new ToolBox(\'video_'.$id.'\');
			</script>';
		}
    }
    
    public function video_form_html($id) {
    	$record = Record::findByIdFrom('ProductVideo', $id);
		if ($record) {
			echo '<div id="video_form_'.$id.'" class="form" style="display: none;">
				<form method="post" action="/'.ADMIN_DIR.'/plugin/ecommerce/video_update/'.$id.'" onsubmit="video_update('.$id.');return false;">
				<p>
					<label for="title">'.__('Title').'</label>
					<input id="video_form_title_'.$id.'" class="textbox" type="text" name="product_video[title]" id="variant_title" size="30" value="'.htmlentities($record->title, ENT_COMPAT, 'UTF-8').'" />
				</p>
				
				<p>
					<label for="filename">'.__('Video').'</label>
					<input id="video_form_filename_'.$id.'" class="textbox" type="text" name="product_video[filename]" id="variant_filename" size="30" maxlength="50" value="'.htmlentities($record->filename, ENT_COMPAT, 'UTF-8').'" />
					<a href="#" onclick="browse_server(\'video_form_filename_'.$id.');return false;"><img src="/frog/plugins/ecommerce/images/file_manager_16x16.gif" width="16" height="16" /></a>
				</p>
				
				<p class="last">
					<input name="commit" type="submit" value="Save"> or <a href="#" onclick="video_form_cancel('.$id.');return false;">Cancel</a>
				</p>
				</form>
			</div>';
		}
    }
} // end EcommerceController