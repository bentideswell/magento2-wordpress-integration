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
		if ($className = $this->getClassNameFromType($type)) {
			return $this->getObjectManager()->create($className, $args);
		}
		
		return false;
	}
	
	/*
	 *
	 *
	 * @param  string $type
	 * @return object|false
	 */
	public function get($type)
	{
		if ($className = $this->getClassNameFromType($type)) {
			return $this->getObjectManager()->get($className);
		}
		
		return false;
	}

	/*
	 *
	 *
	 * @param  string $type
	 * @return object|false
	 */
	protected function getObjectManager()
	{
		return \Magento\Framework\App\ObjectManager::getInstance();
	}

	/*
	 *
	 *
	 * @param  string $type
	 * @return string
	 */
	protected function getClassNameFromType($type)
	{
		if (strpos($type, 'FishPig') === 0) {
			return $type;
		}

		$type   = trim($type, '\\');
		$prefix = __NAMESPACE__ . '\\';
		
		if (strpos($type, '\\') > 0) {
			$prefix = 'FishPig\WordPress\\';
		}

		return $prefix . $type;
	}
}
