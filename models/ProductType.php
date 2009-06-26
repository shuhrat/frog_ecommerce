<?php
/**
 * class ProductType
 *
 * @package frog
 * @subpackage plugin.ecommerce
 * @author Chris Earls <cgearls@gmail.com>
 * @since Frog version 0.9.5
 */

class ProductType extends Record
{	
	const TABLE_NAME = 'ecommerce_product_type';
	
	public static function find($args = null)
	{
		$where    = isset($args['where']) ? trim($args['where']) : '';
		$order_by = isset($args['order']) ? trim($args['order']) : 'title';
		$offset   = isset($args['offset']) ? (int) $args['offset'] : 0;
		$limit    = isset($args['limit']) ? (int) $args['limit'] : 0;
		
		$where_string = empty($where) ? '' : "WHERE $where";
		$order_by_string = empty($order_by) ? '' : "ORDER BY $order_by";
		$limit_string = $limit > 0 ? "LIMIT $offset, $limit" : '';
		
		$sql = "SELECT ".ProductType::TABLE_NAME.".* FROM ".ProductType::TABLE_NAME." AS ".ProductType::TABLE_NAME." ".
		"$where_string $order_by_string $limit_string";
		
		$stmt = self::$__CONN__->prepare($sql);
		$stmt->execute();
		
		if ($limit == 1)
		{
			return $stmt->fetchObject('ProductType');
		}
		else
		{
			$objects = array();
			while ($object = $stmt->fetchObject('ProductType'))
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
			'where' => ProductType::TABLE_NAME.'.id='.(int)$id,
			'limit' => 1
		));
	}
	
	public static function nav()
	{		
		//$sql = 'select distinct t.title, t.slug, (select i.filename from ecommerce_product_image i inner join ecommerce_product p on i.product_id = p.id where p.type_id = t.id order by p.title,i.position limit 0,1) as image from ecommerce_product_type t group by t.title order by t.title';
		$sql = 'select title, slug from ecommerce_product_type order by title';
		$stmt = self::$__CONN__->prepare($sql);
		$stmt->execute();
		
		$objects = array();
		while ($object = $stmt->fetchObject('ProductType'))
			$objects[] = $object;
		
		return $objects;
	}
	
}

