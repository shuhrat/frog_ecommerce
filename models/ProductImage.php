<?php
/**
 * class ProductImage
 *
 * @package frog
 * @subpackage plugin.ecommerce
 * @author Chris Earls <cgearls@gmail.com>
 * @since Frog version 0.9.5
 */

class ProductImage extends Record
{	
	const TABLE_NAME = 'ecommerce_product_image';
	
	public static function find($args = null)
	{
		$where    = isset($args['where']) ? trim($args['where']) : '';
		$order_by = isset($args['order']) ? trim($args['order']) : 'title';
		$offset   = isset($args['offset']) ? (int) $args['offset'] : 0;
		$limit    = isset($args['limit']) ? (int) $args['limit'] : 0;
		
		$where_string = empty($where) ? '' : "WHERE $where";
		$order_by_string = empty($order_by) ? '' : "ORDER BY $order_by";
		$limit_string = $limit > 0 ? "LIMIT $offset, $limit" : '';
		
		$sql = "SELECT ".ProductVendor::TABLE_NAME.".* FROM ".ProductVendor::TABLE_NAME." AS ".ProductVendor::TABLE_NAME." ".
		"$where_string $order_by_string $limit_string";
		
		$stmt = self::$__CONN__->prepare($sql);
		$stmt->execute();
		
		if ($limit == 1)
		{
			return $stmt->fetchObject('ProductImage');
		}
		else
		{
			$objects = array();
			while ($object = $stmt->fetchObject('ProductImage'))
				$objects[] = $object;
			
			return $objects;
		}
	}
	
	public static function findAll($args = null)
	{
		return self::find($args);
	}
	
	public static function findById($id)
	{
		return self::find(array(
			'where' => ProductImage::TABLE_NAME.'.id='.(int)$id,
			'limit' => 1
		));
	}
	
	public static function findByProduct($slug)
	{
		$sql = 'select i.filename from ecommerce_product_image i inner join ecommerce_product p on p.id = i.product_id WHERE p.slug=\''.$slug.'\'';
		$stmt = self::$__CONN__->prepare($sql);
		$stmt->execute();
		
		$objects = array();
		while ($object = $stmt->fetchObject('ProductImage'))
			$objects[] = $object;
		
		return $objects;
	}
	
}

 