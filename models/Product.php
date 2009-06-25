<?php
/**
 * class Product
 *
 * @package frog
 * @subpackage plugin.ecommerce
 * @author Chris Earls <cgearls@gmail.com>
 * @since Frog version 0.9.5
 */

class Product extends Record
{	
	const TABLE_NAME = 'ecommerce_product';
	
	public static function find($args = null)
	{
		$where    = isset($args['where']) ? trim($args['where']) : '';
		$order_by = isset($args['order']) ? trim($args['order']) : 'title';
		$offset   = isset($args['offset']) ? (int) $args['offset'] : 0;
		$limit    = isset($args['limit']) ? (int) $args['limit'] : 0;
		
		$where_string = empty($where) ? '' : "WHERE $where";
		$order_by_string = empty($order_by) ? '' : "ORDER BY $order_by";
		$limit_string = $limit > 0 ? "LIMIT $offset, $limit" : '';
		
		$sql = "SELECT ".self::TABLE_NAME.".* FROM ".self::TABLE_NAME." AS ".self::TABLE_NAME." ".
		"$where_string $order_by_string $limit_string";
		
		$stmt = self::$__CONN__->prepare($sql);
		$stmt->execute();
		
		if ($limit == 1)
			return $stmt->fetchObject('Product');
		else
		{
			$objects = array();
			while ($object = $stmt->fetchObject('Product'))
				$objects[] = $object;
			
			return $objects;
		}
	}
	
	public static function findAll($args = null)
	{
		$offset   = isset($args['offset']) ? (int) $args['offset'] : 0;
		$limit    = isset($args['limit']) ? (int) $args['limit'] : 0;
		$limit_string = $limit > 0 ? "LIMIT $offset, $limit" : '';
		
		$sql = 'select p.*, (select i.filename from ecommerce_product_image i where i.product_id = p.id order by position limit 0,1) as image, (select v.price from ecommerce_product_variant v where v.product_id = p.id order by position limit 0,1) as price from ecommerce_product p inner join ecommerce_product_type t on p.type_id = t.id inner join ecommerce_product_vendor v on p.vendor_id = v.id order by p.title '.$limit_string;
		$stmt = self::$__CONN__->prepare($sql);
		$stmt->execute();
		
		$objects = array();
		while ($object = $stmt->fetchObject('Product'))
			$objects[] = $object;
		
		return $objects;
	}
	
	public static function findById($id)
	{
		return self::find(array(
			'where' => Product::TABLE_NAME.'.id='.(int)$id,
			'limit' => 1
		));
	}
	
	public static function findBySlug($slug)
	{
		$sql = 'select p.* from ecommerce_product p WHERE p.slug=\''.$slug.'\' and is_published=1';
		$stmt = self::$__CONN__->prepare($sql);
		$stmt->execute();
		return $stmt->fetchObject('Product');
	}
	
	public static function findByTypeSlug($args = null,$slug)
	{
		$offset   = isset($args['offset']) ? (int) $args['offset'] : 0;
		$limit    = isset($args['limit']) ? (int) $args['limit'] : 0;
		$limit_string = $limit > 0 ? "LIMIT $offset, $limit" : '';
		
		$sql = 'select p.*, (select i.filename from ecommerce_product_image i where i.product_id = p.id order by position limit 0,1) as image, (select v.price from ecommerce_product_variant v where v.product_id = p.id order by position limit 0,1) as price from ecommerce_product p inner join ecommerce_product_type t on p.type_id = t.id inner join ecommerce_product_vendor v on p.vendor_id = v.id WHERE t.slug=\''.$slug.'\' and is_published = 1 order by p.title '.$limit_string;
		$stmt = self::$__CONN__->prepare($sql);
		$stmt->execute();
		
		$objects = array();
		while ($object = $stmt->fetchObject('Product'))
			$objects[] = $object;
		
		return $objects;
	}
	
	public static function findByVendorSlug($args = null,$slug)
	{
		$offset   = isset($args['offset']) ? (int) $args['offset'] : 0;
		$limit    = isset($args['limit']) ? (int) $args['limit'] : 0;
		$limit_string = $limit > 0 ? "LIMIT $offset, $limit" : '';
		
		$sql = 'select p.*, (select i.filename from ecommerce_product_image i where i.product_id = p.id order by position limit 0,1) as image, (select v.price from ecommerce_product_variant v where v.product_id = p.id order by position limit 0,1) as price from ecommerce_product p inner join ecommerce_product_type t on p.type_id = t.id inner join ecommerce_product_vendor v on p.vendor_id = v.id WHERE v.slug=\''.$slug.'\' order by p.title '.$limit_string;
		$stmt = self::$__CONN__->prepare($sql);
		$stmt->execute();
		
		$objects = array();
		while ($object = $stmt->fetchObject('Product'))
			$objects[] = $object;
		
		return $objects;
	}
	
	public static function getCartProduct($variant_id)
	{
		$sql = 'select p.*, v.title as variant_title, v.price from ecommerce_product p inner join ecommerce_product_variant v on p.id = v.product_id WHERE v.id=\''.$variant_id.'\'';
		$stmt = self::$__CONN__->prepare($sql);
		$stmt->execute();
		return $stmt->fetchObject('Product');
	}
	
	public static function findRelated($tags,$slug) {
		$tag_arr = explode(',',$tags);
		$objects = array();
		
		foreach ($tag_arr as $tag) :
			$sql = 'select p.*, (select i.filename from ecommerce_product_image i where i.product_id = p.id order by position limit 0,1) as image, (select v.price from ecommerce_product_variant v where v.product_id = p.id order by position limit 0,1) as price from ecommerce_product p where p.tags like \''.trim($tag).'\' and p.slug <> \''.$slug.'\' order by p.title';
			$stmt = self::$__CONN__->prepare($sql);
			$stmt->execute();
			
			while ($object = $stmt->fetchObject('Product')) {
				if (!in_array($object,$objects))
					$objects[] = $object;
			}
		endforeach;
		
		return $objects;
	}
	
	public static function search($args = null)
	{
		$where    = isset($args['where']) ? $args['where'] : '';
		$where_string = !empty($where) ? "WHERE ".$where : '';
		
		$offset   = isset($args['offset']) ? (int) $args['offset'] : 0;
		$limit    = isset($args['limit']) ? (int) $args['limit'] : 0;
		$limit_string = $limit > 0 ? "LIMIT $offset, $limit" : '';

		$sql = 'select p.*, (select i.filename from ecommerce_product_image i where i.product_id = p.id order by position limit 0,1) as image, (select v.price from ecommerce_product_variant v where v.product_id = p.id order by position limit 0,1) as price from ecommerce_product p inner join ecommerce_product_type t on p.type_id = t.id inner join ecommerce_product_vendor v on p.vendor_id = v.id '.$where_string.' order by p.title '.$limit_string;
		$stmt = self::$__CONN__->prepare($sql);
		$stmt->execute();

		$objects = array();
		while ($object = $stmt->fetchObject('Product'))
			$objects[] = $object;

		return $objects;
	}
}