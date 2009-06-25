<?php
/**
 * class FirstData
 *
 * @package frog
 * @subpackage plugin.ecommerce
 * @author Chris Earls <cgearls@gmail.com>
 * @since Frog version 0.9.5
 */

include("lphp.php");

class FirstData {

	public $debug = false;
	public $result = '';
	
	//billing info
	public $name = '';
	public $company = '';
	public $address = '';
	public $address2 = '';
	public $city = '';
	public $state = '';
	public $country = '';
	public $phone = '';
	public $fax = '';
	public $email = '';
	public $zip = '';
	
	//shipping info
	public $ship_name = '';
	public $ship_address = '';
	public $ship_address2 = '';
	public $ship_city = '';
	public $ship_state = '';
	public $ship_country = '';
	public $ship_zip = '';
	
	//payment info
	public $subtotal = '';
	public $shipping = '';
	public $tax = '';
	public $total = '';
	
	public $cc_number = '';
	public $cc_exp_month = '';
	public $cc_exp_year = '';
	public $cc_cvv = '';
	
	private $lphp = '';
	private $order = array();
	
	public function __construct() {
		$this->lphp = new lphp;
		$this->order["host"]       = "secure.linkpt.net";
		$this->order["port"]       = "1129";
		$this->order["keyfile"]    = "/path/to/file.pem";
		$this->order["configfile"] = "1111111111"; #store number
		$this->order["debugging"]  = $this->debug;
	}
	
	public function sale() {
		$this->order["name"]          = $this->name;
		$this->order["company"]       = $this->company;
		$this->order["address1"]      = $this->address;
		$this->order["address2"]      = $this->address2;
		$this->order["city"]          = $this->city;
		$this->order["state"]         = $this->state;
		$this->order["country"]       = $this->country;
		$this->order["phone"]         = $this->phone;
		$this->order["fax"]           = $this->fax;
		$this->order["email"]         = $this->email;
		$this->order["zip"]           = $this->zip;
		
		$this->order["sname"]         = $this->ship_name;
		$this->order["saddress1"]     = $this->ship_address;
		$this->order["saddress2"]     = $this->ship_address2;
		$this->order["scity"]         = $this->ship_city;
		$this->order["sstate"]        = $this->ship_state;
		$this->order["scountry"]      = $this->ship_country;
		$this->order["szip"]          = $this->ship_zip;
		
		$this->order["subtotal"]      = $this->subtotal;
		$this->order["tax"]           = $this->tax;
		$this->order["shipping"]      = $this->shipping;
		$this->order["chargetotal"]   = $this->total;
		
		$this->order["cardnumber"]    = $this->cc_number;
		$this->order["cardexpmonth"]  = $this->cc_exp_month;
		$this->order["cardexpyear"]   = $this->cc_exp_year;
		$this->order["cvmvalue"]      = $this->cc_cvv;
		$this->order["cvmvalue"]      = 'provided';
		
		$this->order["ordertype"]     = "SALE";
		$this->order["result"]        = $this->result;
		
		$result = $this->lphp->curl_process($this->order);
		
		return $result;
	}
}
?>