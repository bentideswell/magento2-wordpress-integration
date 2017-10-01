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
     * Constructor
     *
     * @param Context $context
     * @param App
     * @param array $data
     */
    public function __construct(
    	\Magento\Framework\View\Element\Template\Context $context, 
    	\FishPig\WordPress\Block\Context $wpContext,
    	array $data = []
    )
    {
	    $this->_app = $wpContext->getApp()->init();
	    $this->_config = $wpContext->getConfig();
	    $this->_registry = $wpContext->getRegistry();
	    $this->_wpUrlBuilder = $wpContext->getUrlBuilder();
	    $this->_factory = $wpContext->getFactory();
	    $this->_viewHelper = $wpContext->getViewHelper();
	    
        parent::__construct($context, $data);
    }
    
	public function toHtml()
	{
		try {
			return parent::toHtml();
		}
		catch (\Exception $e) {
			throw $e;
		}
	}
}
