<?php
/*
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

namespace FishPig\WordPress\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Helper\AbstractHelper;

class Router extends AbstractHelper
{
	/*
	 *
	 *
	 * @return void
	 */
	public function __construct(Context $context)
	{
		parent::__construct($context);
	}
	
	/**
	 * Generate an array of URI's based on $results
	 *
	 * @param array $results
	 * @return array
	 */
	public function generateRoutesFromArray($results, $prefix = '')
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
}
