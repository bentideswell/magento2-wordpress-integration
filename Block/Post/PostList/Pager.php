<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

namespace FishPig\WordPress\Block\Post\PostList;

use Magento\Framework\View\Element\Template\Context;
use FishPig\WordPress\Model\App;
use FishPig\WordPress\Model\Config;

class Pager extends \Magento\Theme\Block\Html\Pager 
{
	/**
	 * @var \FishPig\WordPress\Model\App
	**/
	protected $_app = null;

	/**
	 * @var \FishPig\WordPress\Model\Config
	**/	
	protected $_config = null;
	
    /**
     * Constructor
     *
     * @param Context $context
     * @param App
     * @param array $data
     */
    public function __construct(Context $context, App $app, Config $config, array $data = [])
    {
	    $this->_app = $app;
	    $this->_config = $config;

        parent::__construct($context, $data);
    }
    
    /**
	 * Get the App model
	 *
	 * @return \FishPig\WordPress\Model\App
	**/
	public function getApp()
	{
		return $this->_app;
	}
	
	/**
	 * Construct the pager and set the limits
	 *
	 */
	protected function _construct()
	{
		parent::_construct();	

		$this->setPageVarName('page');

		$baseLimit = $this->_config->getOption('posts_per_page', 10);

		$this->setDefaultLimit($baseLimit);
		$this->setLimit($baseLimit);
		
		$this->setAvailableLimit(array(
			$baseLimit => $baseLimit,
		));
		
		$this->setFrameLength(5);
	}
	
	/**
	 * Return the URL for a certain page of the collection
	 *
	 * @return string
	 */
	public function getPagerUrl($params = array())
	{
		$pageVarName = $this->getPageVarName();

		if (isset($params[$pageVarName])) {
			$slug = '/' . $pageVarName . '/' . $params[$pageVarName] . '/';
			unset($params[$pageVarName]);
		}
		else {
			$slug = '';
		}
		
		$pagerUrl = parent::getPagerUrl($params);
		
		if (strpos($pagerUrl, '?') !== false) {
			$pagerUrl = rtrim(substr($pagerUrl, 0, strpos($pagerUrl, '?')), '/') . $slug . substr($pagerUrl, strpos($pagerUrl, '?'));
		}
		else {
			$pagerUrl = rtrim($pagerUrl, '/') . $slug;
		}
		
		return $pagerUrl;
	}
}
