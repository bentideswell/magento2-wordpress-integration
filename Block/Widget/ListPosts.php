<?php
/**
 *
 */
namespace FishPig\WordPress\Block\Widget;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Widget\Block\BlockInterface;
use FishPig\WordPress\Model\ResourceModel\Post\CollectionFactory;

class ListPosts extends Template implements BlockInterface
{
    /**
     *
     */
    protected $_template = "FishPig_WordPress::widget/post/list.phtml";
    
    /**
     *
     */
    protected $collection;

    /**
     *
     */
    public function __construct(Context $context, CollectionFactory $collectionFactory, array $data = [])
    {
        $this->collectionFactory = $collectionFactory;
        
        parent::__construct($context, $data);
    }

    /**
     *
     */
    public function getPostCollection()
    {
        if (!$this->collection) {
            $this->collection = $this->collectionFactory->create()
                ->setPageSize((int)$this->getPostLimit());
        }
        
        return $this->collection;
    }
    
    /**
     *
     */
    public function getLoadedPostCollection()
    {
        return $this->getPostCollection()->load();
    }
}
