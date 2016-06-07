<?php

namespace FishPig\WordPress\Model;

class Config
{
	protected $_reader = null;
	protected $_db = null;
	protected $_scopeConfig = null;
	protected $_customerSession = null;
	protected $_options = array();
	
	public function __construct(
		\FishPig\WordPress\Model\Config\Reader $reader,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\FishPig\WordPress\Model\App\ResourceConnection $resourceConnection,
		\Magento\Customer\Model\Session $customerSession
	)
	{
		$this->_reader = $reader;
		$this->_scopeConfig = $scopeConfig;
		$this->_resource = $resourceConnection;
		$this->_customerSession = $customerSession;
	}	
	
    public function getStoreConfigValue($key)
    {
	    return $this->_scopeConfig->getValue($key);
    }
    
    /**
	 * Get a WordPress option value
	 *
	 * @return mixed
	 */
    public function getOption($key)
    {
	    if (!isset($this->_options[$key])) {
		    $select = $this->_resource->getConnection()->select()
		    	->from($this->_resource->getTable('wordpress_option'), 'option_value')
		    	->where('option_name = ?', $key);
		    
		    $this->_options[$key] = $this->_resource->getConnection()->fetchOne($select);
		}
		
		return $this->_options[$key];
    }
    
    public function getDbTableMapping($when = 'before_connect')
    {
	    if ($config = $this->_reader->getValue('database/tables/table')) {
		    $map = array();

		    foreach($config as $key => $value) {
				$value = $value['@attributes'];
				
				if ($value['when'] === $when) {
					$map[$value['id']] = $value['name'];

					if (isset($value['meta'])) {
						$map[$value['id'] . '_meta'] = $value['meta'];
					}
				}
		    }

		    if (count($map) > 0) {
			    return $map;
			}
	    }
	    
	    return false;
    }
    
    public function getShortcodes()
    {
	    if ($config = $this->_reader->getValue('shortcodes')) {
		    $shortcodes = array();

			foreach($config['shortcode'] as $shortcode) {
				$shortcode = $shortcode['@attributes'];
				
				if (!isset($shortcode['sortOrder'])) {
					$shortcode['sortOrder'] = 9999;
				}
				
				$sortOrder = (int)$shortcode['sortOrder'];
				
				if (!isset($shortcodes[$sortOrder])) {
					$shortcodes[$sortOrder] = array();
				}
				
				$shortcodes[$sortOrder][$shortcode['id']] = $shortcode['class'];
			}
			
			$final = array();
			
			foreach($shortcodes as $groupedShortcodes) {
				$final = array_merge($final, $groupedShortcodes);
			}
			
			return $final;
		}
		
		return false;
    }
    
    public function getWidgets()
    {
	    if ($config = $this->_reader->getValue('sidebar/widgets')) {
		    $widgets = array();

		    foreach($config['widget'] as $widget) {
			    $widgets[$widget['@attributes']['id']] = $widget['@attributes']['class'];
		    }
		    
		    return $widgets;
	    }
		
		return false;
    }
    
    public function isLoggedIn()
    {
	    return $this->_customerSession->isLoggedIn();
    }
}
