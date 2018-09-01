<?php
/*
 *
 */
namespace FishPig\WordPress\Model;

class Factory
{
	/*
	 * @var array
	 */
	protected $factories = [];
	
	/*
	 * Create an instance of $type
	 *
	 * @param  string $type
	 * @return object
	 */
	public function create($type, array $args = [])
	{
		if ($className = $this->getClassnameFromType($type)) {
			return $this->getObjectManager()->create($type, $args);
		}
		
		return false;
	}
	
	public function get($type)
	{
		if ($className = $this->getClassnameFromType($type)) {
			return $this->getObjectManager()->get($type);
		}
		
		return false;
	}
	
	protected function getObjectManager()
	{
		return \Magento\Framework\App\ObjectManager::getInstance();
	}
	
	protected function getClassnameFromType($type)
	{
		$type = trim($type, '\\');
		$prefix = __NAMESPACE__ . '\\';
		
		if (strpos($type, '\\') > 0) {
			$prefix = 'FishPig\WordPress\\';
		}
		
		return $prefix . $type;
	}
}