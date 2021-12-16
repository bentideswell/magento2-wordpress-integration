<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Block\Widget;

class ListPosts extends \Magento\Framework\View\Element\Template implements \Magento\Widget\Block\BlockInterface
{
    /**
     *
     */
    private $collection;

    /**
     *
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \FishPig\WordPress\Model\ResourceModel\Post\CollectionFactory $collectionFactory,
        \FishPig\WordPress\App\Integration\Tests $integrationManager,
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->integrationManager = $integrationManager;
        
        parent::__construct($context, $data);
    }

    /**
     *
     */
    public function getPostCollection()
    {
        if (!$this->collection) {
            $this->collection = $this->collectionFactory->create()
                ->addPostTypeFilter('post')
                ->setOrderByPostDate()
                ->addIsViewableFilter()
                ->setCurPage(1)
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
    
    /**
     *
     */
    public function toHtml()
    {
        if (!$this->getTemplate()) {
            if ($template = $this->getWidgetTemplate()) {
                $this->setTemplate($template);
            } else {
                $this->setTemplate('FishPig_WordPress::widget/post/list.phtml');
            }
        }

        try {
            if ($this->integrationManager->runTests() === true) {
                return parent::toHtml();
            }
            
            return '';
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
