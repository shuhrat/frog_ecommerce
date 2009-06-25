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

if ($driver == 'mysql')
{
	/*$PDO->exec("CREATE TABLE ".TABLE_PREFIX."ecommerce_product (
	  id int not null auto_increment primary key,
	  title varchar(100) not null,
	  slug varchar(100) not null,
	  description text null,
	  type_id int null,
	  vendor_id int null,
	  is_published tinyint(1) unsigned NOT NULL default '1',
	  created_on datetime not null,
	  updated_on datetime not null
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8");
	*/
}