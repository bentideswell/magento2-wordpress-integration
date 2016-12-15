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
	protected $searchFactory = null;
	
    /**
     * Constructor
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(Context $context, SearchFactory $searchFactory)
    {
        parent::__construct($context);
        
        $this->searchFactory = $searchFactory;
    }	
    
    public function execute()
    {
		return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)
			->setUrl($this->searchFactory->create()->getUrl());
    }
}
