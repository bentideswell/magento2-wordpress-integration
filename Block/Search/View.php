<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Block\Search;

class View extends \FishPig\WordPress\Block\Post\PostList\Wrapper\AbstractWrapper
{
    /**
     * @param  \Magento\Framework\View\Element\Template\Context $context,
     * @param  \FishPig\WordPress\Block\Context $wpContext,
     * @param  array $data = []
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \FishPig\WordPress\Block\Context $wpContext,
        \FishPig\WordPress\Model\ResourceModel\Post\CollectionFactory $postCollectionFactory,
        \FishPig\WordPress\Model\Search $searchModel,
        \FishPig\WordPress\Model\PostTypeRepository $postTypeRepository,
        array $data = []
    ) {
        $this->searchModel = $searchModel;
        $this->postTypeRepository = $postTypeRepository;

        parent::__construct($context, $wpContext, $postCollectionFactory, $data);
    }
    
    /**
     * @return \FishPig\WordPress\Model\Search
     */
    public function getSearchModel(): \FishPig\WordPress\Model\Search
    {
        return $this->searchModel;
    }

    /**
     * @return \FishPig\WordPress\Model\ResourceModel\Post\Collection
     */
    protected function getBasePostCollection(): \FishPig\WordPress\Model\ResourceModel\Post\Collection
    {
        return $this->getSearchModel()->getPostCollection();
    }

    /**
     * @param  bool $escape = false
     * @return string
     */
    public function getSearchTerm($escape = false): string
    {
        return $this->searchModel->getSearchTerm($escape);
    }

    /**
     * @return string
     */
    public function getSearchVar(): string
    {
        return $this->_getData('search_var') ? $this->_getData('search_var') : 's';
    }
    
    /**
     * @deprecated 3.0 use self::getSearchModel
     */
    public function getEntity()
    {
        return $this->getSearchModel();
    }
}
