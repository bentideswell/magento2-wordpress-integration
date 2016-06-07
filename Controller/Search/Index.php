<?php
/**
 * @var 
**/
namespace FishPig\WordPress\Controller\Search;

use \Magento\Framework\App\Action\Context;
use \Magento\Framework\Controller\ResultFactory;
use \FishPig\WordPress\Model\SearchFactory;

class Index extends \Magento\Framework\App\Action\Action
{
	/**
	 * @var
	**/
	protected $_resultFactory = null;
	
	/**
	 * @var
	**/
	protected $_searchFactory = null;
	
    /**
     * Constructor
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(Context $context, ResultFactory $resultFactory, SearchFactory $searchFactory)
    {
        parent::__construct($context);
        
        $this->_resultFactory = $resultFactory;
        $this->_searchFactory = $searchFactory;
    }	
    
    public function execute()
    {
		return $this->_resultFactory->create(ResultFactory::TYPE_REDIRECT)
			->setUrl($this->_searchFactory->create()->getUrl());
    }
}
