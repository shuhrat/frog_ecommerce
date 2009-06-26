<?php
/*
* @author Chris Earls <cgearls@gmail.com>
* @version 1.0.0
* @since Frog version 0.9.5
* @license http://www.gnu.org/licenses/agpl.html AGPL License
* @copyright Chris Earls, 2009
*/
$PDO = Record::getConnection();
$driver = strtolower($PDO->getAttribute(Record::ATTR_DRIVER_NAME));

if ($driver == mysql)
{	
	$PDO->exec("
		CREATE TABLE ecommerce_collection (
			id int(11) NOT NULL auto_increment PRIMARY KEY,
			title varchar(100) NOT NULL,
			created_on timestamp NULL default CURRENT_TIMESTAMP
		) ENGINE=MyISAM	DEFAULT CHARSET=utf8 ;
	
		CREATE TABLE ecommerce_log (
			id int(11) NOT NULL auto_increment PRIMARY KEY,
			message varchar(255) NOT NULL,
			user_id int(11) default NULL,
			created_on timestamp NULL default CURRENT_TIMESTAMP
		) ENGINE=MyISAM	DEFAULT CHARSET=utf8 ;
	
		CREATE TABLE ecommerce_order (
			id int(11) NOT NULL auto_increment PRIMARY KEY,
			first_name varchar(50) NOT NULL,
			last_name varchar(50) NOT NULL,
			company varchar(50) NOT NULL,
			address varchar(50) NOT NULL,
			address2 varchar(50) NOT NULL,
			city varchar(50) NOT NULL,
			state varchar(50) NOT NULL,
			zip varchar(50) NOT NULL,
			country varchar(50) NOT NULL,
			email varchar(50) NOT NULL,
			phone varchar(50) NOT NULL,
			fax varchar(50) NOT NULL,
			ship_first_name varchar(50) NOT NULL,
			ship_last_name varchar(50) NOT NULL,
			ship_company varchar(50) NOT NULL,
			ship_address varchar(50) NOT NULL,
			ship_address2 varchar(50) NOT NULL,
			ship_city varchar(50) NOT NULL,
			ship_state varchar(50) NOT NULL,
			ship_zip varchar(50) NOT NULL,
			ship_country varchar(50) NOT NULL,
			ship_phone varchar(50) NOT NULL,
			subtotal decimal(10,2) NOT NULL,
			shipping decimal(10,2) NOT NULL,
			tax decimal(10,2) NOT NULL,
			rep_name varchar(50) NOT NULL,
			promo_code varchar(25) NOT NULL,
			promo_discount decimal(10,2) NOT NULL,
			cc_name varchar(50) NOT NULL,
			cc_type varchar(25) NOT NULL,
			cc_number varchar(4) NOT NULL,
			cc_cvv varchar(4) NOT NULL,
			cc_exp_month varchar(2) NOT NULL,
			cc_exp_year varchar(4) NOT NULL,
			created_on timestamp NULL default CURRENT_TIMESTAMP,
			fulfilled tinyint(1) NOT NULL default 0
		) ENGINE=MyISAM	DEFAULT CHARSET=utf8 AUTO_INCREMENT=1000 ;
	
		CREATE TABLE ecommerce_order_variant (
			id int(11) NOT NULL auto_increment PRIMARY KEY,
			order_id int(11) NOT NULL,
			product_id int(11) NOT NULL,
			title varchar(100) NOT NULL,
			sku varchar(100) default NULL,
			quantity int(11) default NULL,
			price decimal(10,2) NOT NULL,
			weight int(11) default NULL,
			description varchar(255) NOT NULL,
			created_on timestamp NULL default CURRENT_TIMESTAMP
		) ENGINE=MyISAM	DEFAULT CHARSET=utf8 ;
	
		CREATE TABLE ecommerce_product (
			id int(11) NOT NULL auto_increment PRIMARY KEY,
			title varchar(255) NOT NULL,
			slug varchar(100) NOT NULL,
			description text,
			type_id int(11) default NULL,
			vendor_id int(11) default NULL,
			is_published tinyint(1) unsigned NOT NULL default 1,
			created_on timestamp NOT NULL default CURRENT_TIMESTAMP,
			updated_on datetime NOT NULL,
			page_id int(11) NOT NULL,
			tags text
		) ENGINE=MyISAM	DEFAULT CHARSET=utf8 ;
	
		CREATE TABLE ecommerce_product_collection (
			id int(11) NOT NULL auto_increment PRIMARY KEY,
			product_id int(11) NOT NULL,
			collection_id int(11) NOT NULL,
			position int(11) NOT NULL
		) ENGINE=MyISAM	DEFAULT CHARSET=utf8 ;
	
		CREATE TABLE ecommerce_product_file (
			id int(11) NOT NULL auto_increment PRIMARY KEY,
			product_id int(11) NOT NULL,
			title varchar(50) NOT NULL,
			filename varchar(255) default NULL,
			position int(11) default NULL,
			created_on timestamp NULL default CURRENT_TIMESTAMP
		) ENGINE=MyISAM	DEFAULT CHARSET=utf8 ;
	
		CREATE TABLE ecommerce_product_image (
			id int(11) NOT NULL auto_increment PRIMARY KEY,
			product_id int(11) NOT NULL,
			filename varchar(255) default NULL,
			position int(11) default NULL,
			created_on timestamp NULL default CURRENT_TIMESTAMP
		) ENGINE=MyISAM	DEFAULT CHARSET=utf8 ;
	
		CREATE TABLE ecommerce_product_type (
			id int(11) NOT NULL auto_increment PRIMARY KEY,
			title varchar(100) NOT NULL,
			created_on timestamp NULL default CURRENT_TIMESTAMP,
			slug varchar(100) default NULL
		) ENGINE=MyISAM	DEFAULT CHARSET=utf8 ;
	
		CREATE TABLE ecommerce_product_variant (
			id int(11) NOT NULL auto_increment PRIMARY KEY,
			product_id int(11) NOT NULL,
			title varchar(255) NOT NULL,
			sku varchar(100) default NULL,
			quantity int(11) default NULL,
			price decimal(10,2) NOT NULL,
			weight int(11) default NULL,
			created_on timestamp NULL default CURRENT_TIMESTAMP,
			updated_on timestamp NULL default NULL,
			position int(11) default NULL,
			description varchar(255) default NULL
		) ENGINE=MyISAM	DEFAULT CHARSET=utf8 ;
	
		CREATE TABLE ecommerce_product_vendor (
			id int(11) NOT NULL auto_increment PRIMARY KEY,
			title varchar(100) NOT NULL,
			created_on timestamp NULL default CURRENT_TIMESTAMP,
			slug varchar(100) default NULL
		) ENGINE=MyISAM	DEFAULT CHARSET=utf8 ;
	
		CREATE TABLE ecommerce_product_video (
			id int(11) NOT NULL auto_increment PRIMARY KEY,
			product_id int(11) NOT NULL,
			title varchar(50) NOT NULL,
			filename varchar(255) default NULL,
			position int(11) default NULL,
			created_on timestamp NULL default CURRENT_TIMESTAMP
		) ENGINE=MyISAM	DEFAULT CHARSET=utf8 ;
	
		CREATE TABLE ecommerce_promos (
			id int(11) NOT NULL auto_increment PRIMARY KEY,
			code varchar(25) NOT NULL,
			start_date datetime NOT NULL,
			end_date datetime NOT NULL,
			discount smallint(6) NOT NULL,
			is_percent tinyint(4) unsigned NOT NULL default 0
		) ENGINE=MyISAM	DEFAULT CHARSET=utf8 ;");
}