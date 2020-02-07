<?php
/**
 *
 */
namespace FishPig\WordPress\Controller\Search;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use FishPig\WordPress\Model\SearchFactory;
use Magento\Framework\Controller\ResultFactory;

class Index extends Action
{
    /**
     * @var
     */
    protected $searchFactory;

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
        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setUrl($this->searchFactory->create()->getUrl());
    }
}
