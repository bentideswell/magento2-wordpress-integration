<?php
/**
 *
**/

namespace FishPig\WordPress\Api\Data\Entity;

/**
 * Interface for all entities that have a meta table (custom fields)
**/
interface MetaInterface
{
	/**
	 *
	 *
	 * @return  string
	**/
	public function getMetaTableAlias();
	
	/**
	 *
	 *
	 * @return  string
	**/
	public function getMetaTableObjectField();
}
