<?php
/**
 * @ 
**/

namespace FishPig\WordPress\Controller;

abstract class Action extends \Magento\Framework\App\Action\Action
{
	/**
	 * @var 
	**/
	protected $_app = null;
	
	/**
	 * @var 
	**/
	protected $_registry = null;

	/**
	 * @var 
	**/	
	protected $_entity = null;
	
	protected $_resultPageFactory = null;
	protected $_resultPage = null;
	protected $_factory = null;
	
	/**
	 * @var 
	**/
	abstract protected function _getEntity();

    /**
     * Constructor
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
	    \Magento\Framework\App\Action\Context $context, 
	    \Magento\Framework\View\Result\PageFactory $resultPageFactory, 
	    \Magento\Framework\Registry $registry, 
	    \FishPig\WordPress\Model\App $app,
	    \FishPig\WordPress\Model\App\Factory $factory
	   )
    {
        $this->_resultPageFactory = $resultPageFactory;
		$this->_registry = $registry;
		$this->_app = $app;
		$this->_factory = $factory;
		
        parent::__construct($context);
    }	

    /**
     * Load the page defined in view/frontend/layout/samplenewpage_index_index.xml
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
	    try {

		    $this->_beforeExecute();

		    $this->_initLayout();

		    $this->_afterExecute();

		    return $this->getPage();
		}
		catch (\Exception $e) {
			echo 'Exception: ' . $e->getMessage();
			exit;
		}
    }
    
	protected function _beforeExecute()
	{
	    if (($entity = $this->_getEntity()) === false) {
		    throw new \Exception('Entity not found!');
	    }
	    
	    if ($entity !== null) {
		    $this->_getRegistry()->register($entity::ENTITY, $entity);
		}

		return $this;	
	}
	
    /**
	 * @
	**/
    protected function _initLayout()
    {
		// Add blog feed URL
		// Add blog comments feed URL
		// Add canonical
		// Add breadcrumbs
	    $this->getPage()->getConfig()->addBodyClass('is-blog');
	    
	    $this->getPage()->addHandle('wordpress_default');

		if ($breadcrumbsBlock = $this->_view->getLayout()->getBlock('breadcrumbs')) {	    
		    if ($crumbs = $this->_getBreadcrumbs()) {
			    foreach($crumbs as $key => $crumb) {
				    $breadcrumbsBlock->addCrumb($key, $crumb);
			    }
		    }
		}

	    return $this;
    }
    
    /**
	  * Get the breadcrumbs
	  *
	  * @return array
	 **/
    protected function _getBreadcrumbs()
    {
	    return [
		    'home' => [
			'label' => __('Home'),
			'title' => __('Go to Home Page'),
			'link' => $this->_app->getWpUrlBuilder()->getMagentoUrl()
		],
		'blog' => [
			'label' => __('Blog'),
			'link' => $this->_app->getWpUrlBuilder()->getHomeUrl()
		]];
    }
    
    /**
	 * @
	**/
	protected function _afterExecute()
	{
		return $this;
	}
    
	/**
	 * @var 
	**/
	public function getPage()
	{
		if ($this->_resultPage === null) {
			$this->_resultPage = $this->_resultPageFactory->create();
		}
		
		return $this->_resultPage;
	}

	/**
	 * @var 
	**/
	public function getEntityObject()
    {
	    if ($this->_entity !== null) {
		    return $this->_entity;
	    }

	    return $this->_entity = $this->_getEntityObject();
    }
    
	/**
	 * @var 
	**/
    protected function _getRegistry()
    {
	    return $this->_registry;
    }
    
	/**
	 * @var 
	**/
    public function getApp()
    {
	    return $this->_app;
    }
}
