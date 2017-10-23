<?php
/**
 *
**/
namespace FishPig\WordPress\Model\App;

class Factory
{
	/*
	 * @var array
	 */
	protected $factories = array();
	
	/*
	 *
	 * @param string $class
	 */
	public function getFactory($class)
	{
		if (strpos($class, __NAMESPACE__) === 0) {
			$class = substr($class, strlen(__NAMESPACE__) + 1);
		}
		
		$class = ltrim($class, '\\');
		
		if (strpos($class, 'FishPig') !== 0) {
			$class = 'FishPig\WordPress\Model\\' . $class;			
		}
		
		$class .= 'Factory';

		if (!isset($this->factories[$class])) {
			$this->factories[$class] = \Magento\Framework\App\ObjectManager::getInstance()->get($class);
		}
		
		return isset($this->factories[$class]) ? $this->factories[$class] : false;
	}
}
