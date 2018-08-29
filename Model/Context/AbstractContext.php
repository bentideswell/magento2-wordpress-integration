<?php
/*
 *
 */
namespace FishPig\WordPress\Model\Context;

abstract class AbstractContext
{
	/*
	 *
	 */
	protected $objects = [];
	
	/*
	 * Add an object
	 *
	 *
	 */
	protected function addObject($object, $name)
	{
		$this->objects[$name] = $object;
		
		return $this;
	}
	
	public function __get($name)
	{
		echo $name . '<br/>';
		echo __METHOD__;
		exit;
	}
}
