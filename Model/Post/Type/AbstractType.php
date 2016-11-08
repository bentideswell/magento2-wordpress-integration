<?php
/**
  *
 **/
namespace FishPig\WordPress\Model\Post\Type;

use FishPig\WordPress\Model\App;

abstract class AbstractType extends \Magento\Framework\DataObject
{
	/**
	  *
	 **/
    protected $_app = null;
	 
	/**
	  *
	 **/
	protected $_resource = null;
	
	/**
	  *
	 **/ 
	protected $_factory = null;
	
	/**
	  *
	 **/
    public function __construct(
    	\FishPig\WordPress\Model\App $app, 
    	\FishPig\WordPress\Model\App\ResourceConnection $resourceConnection, 
    	\FishPig\WordPress\Model\App\Factory $factory, 
    	$data = []
    )
    {
	    parent::__construct($data);
	    
	    $this->_app = $app;
    	$this->_resource = $resourceConnection;
    	$this->_factory = $factory;
    }
	
	/**
	 * Generate an array of URI's based on $results
	 *
	 * @param array $results
	 * @return array
	 */
	protected function _generateRoutesFromArray($results, $prefix = '')
	{
		$objects = array();
		$byParent = array();

		foreach($results as $key => $result) {
			if (!$result['parent']) {
				$objects[$result['id']] = $result;
			}
			else {
				if (!isset($byParent[$result['parent']])) {
					$byParent[$result['parent']] = array();
				}

				$byParent[$result['parent']][$result['id']] = $result;
			}
		}
		
		if (count($objects) === 0) {
			return false;
		}

		$routes = array();
		
		foreach($objects as $objectId => $object) {
			if (($children = $this->_createArrayTree($objectId, $byParent)) !== false) {
				$objects[$objectId]['children'] = $children;
			}

			$routes += $this->_createLookupTable($objects[$objectId], $prefix);
		}
		
		return $routes;
	}
	
	/**
	 * Create a lookup table from an array tree
	 *
	 * @param array $node
	 * @param string $idField
	 * @param string $field
	 * @param string $prefix = ''
	 * @return array
	 */
	protected function _createLookupTable(&$node, $prefix = '')
	{
		if (!isset($node['id'])) {
			return array();
		}

		$urls = array(
			$node['id'] => ltrim($prefix . '/' . urldecode($node['url_key']), '/')
		);

		if (isset($node['children'])) {
			foreach($node['children'] as $childId => $child) {
				$urls += $this->_createLookupTable($child, $urls[$node['id']]);
			}
		}

		return $urls;
	}
	
	/**
	 * Create an array tree. This is used for creating static URL lookup tables
	 * for categories and pages
	 *
	 * @param int $id
	 * @param array $pool
	 * @param string $field = 'parent'
	 * @return false|array
	 */
	protected function _createArrayTree($id, &$pool)
	{
		if (isset($pool[$id]) && $pool[$id]) {
			$children = $pool[$id];
			
			unset($pool[$id]);
			
			foreach($children as $childId => $child) {
				unset($children[$childId]['parent']);
				if (($result = $this->_createArrayTree($childId, $pool)) !== false) {
					$children[$childId]['children'] = $result;
				}
			}

			return $children;
		}
		
		return false;
	}
	
	/**
	 * @return 
	**/
	public function getContent()
	{
		return '';
	}

	/**
	 * @return 
	**/	
	public function getImage()
	{
		return false;
	}

	/**
	 * @return 
	**/
	public function getPageTitle()
	{
		return $this->getName();
	}

	/**
	 * @return 
	**/	
	public function getMetaDescription()
	{
		return '';
	}

	/**
	 * @return 
	**/
	public function getMetaKeywords()
	{
		return '';
	}
	
	/**
	 * @return 
	**/
	public function getCanonicalUrl()
	{
		return $this->getUrl();
	}
	
	/**
	 * @return 
	**/
	public function getRobots()
	{
		return null;
	}
}
