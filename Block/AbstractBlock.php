<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */
namespace FishPig\WordPress\Block;

use \Magento\Framework\View\Element\Template\Context as MagentoContext;
use \FishPig\WordPress\Block\Context as WPContext;

abstract class AbstractBlock extends \Magento\Framework\View\Element\Template
{
  /**
   * Constructor
   *
   * @param Context $context
   * @param App
   * @param array $data
   */
  public function __construct(MagentoContext $context, WPContext $wpContext, array $data = [])
  {
    $this->_app = $wpContext->getApp()->init();
    $this->_config = $wpContext->getConfig();
    $this->_registry = $wpContext->getRegistry();
    $this->_wpUrlBuilder = $wpContext->getUrlBuilder();
    $this->_factory = $wpContext->getFactory();
    $this->_viewHelper = $wpContext->getViewHelper();
    $this->_pluginHelper = $wpContext->getPluginHelper();
    $this->filterHelper = $wpContext->getFilterHelper();
    
    parent::__construct($context, $data);
  }
  
  public function getFilter()
  {
	  return $this->filterHelper;
  }
  
  public function doShortcode($shortcode, $object = null)
  {
	  return $this->filterHelper->doShortcode($shortcode, $object);
  }

	public function toHtml()
	{
		try {
			return parent::toHtml();
		}
		catch (\Exception $e) {
			echo sprintf('<h1>%s</h1><pre>%s</pre>', $e->getMessage(), $e->getTraceAsString());
			exit;
			
			throw $e;
		}
	}
}
