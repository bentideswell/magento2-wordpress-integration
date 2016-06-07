<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

namespace FishPig\WordPress\Block;

abstract class AbstractBlock extends \Magento\Framework\View\Element\Template
{
	/**
	 * @var \FishPig\WordPress\Model\App
	**/
	protected $_app = null;
	
	/**
	 * @var \Magento\Framework\Registry
	**/
	protected $_registry = null;
	
	protected $_wpUrlBuilder = null;
	protected $_viewHelper = null;
	protected $_factory = null;
	protected $_config = null;
	
    /**
     * Constructor
     *
     * @param Context $context
     * @param App
     * @param array $data
     */
    public function __construct(
    	\Magento\Framework\View\Element\Template\Context $context, 
    	\FishPig\WordPress\Model\App $app,
    	\Magento\Framework\Registry $registry,
    	\FishPig\WordPress\Model\Config $config,
    	\FishPig\WordPress\Model\App\Url $urlBuilder,
    	\FishPig\WordPress\Model\App\Factory $factory,
    	\FishPig\WordPress\Helper\View $viewHelper,
    	array $data = []
    )
    {
	    $this->_app = $app;
	    $this->_config = $config;
	    $this->_registry = $registry;
	    $this->_wpUrlBuilder = $urlBuilder;
	    $this->_factory = $factory;
	    $this->_viewHelper = $viewHelper;
	    
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
	
	public function getRegistry()
	{
		return $this->_registry;
	}
	
	protected function _toHtml()
	{
		try {
			return parent::_toHtml();
		}
		catch (\Exception $e) {
			echo sprintf('<h1>Exception in %s</h1><p>%s</p><pre>%s</pre>', get_class($this), $e->getMessage(), $e->getTraceAsString());
			exit;
		}
	}
}
