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
        $collection = $this->postCollectionFactory->create()->addSearchStringFilter(
            $this->_getParsedSearchString(), 
            [
                'post_title' => 5, 
                'post_content' => 1
            ]
        );

        // Post Types
        $searchablePostTypes = $this->getRequest()->getParam('post_type');

        if (!$searchablePostTypes) {
            $postTypes = $this->postTypeRepository->getAll();
            $searchablePostTypes = [];

            foreach ($postTypes as $postType) {
                if ($postType->isSearchable()) {
                    $searchablePostTypes[] = $postType->getPostType();
                }
            }
        }

        if (!$searchablePostTypes) {
            $searchablePostTypes = ['post', 'page'];
        }

        $collection->addPostTypeFilter($searchablePostTypes);

        // Category
        if ($categorySlug = $this->getRequest()->getParam('cat')) {
            $collection->addTermFilter($categorySlug, 'category');
        }

        // Tag
        if ($tagSlug = $this->getRequest()->getParam('tag')) {
            $collection->addTermFilter($tagSlug, 'post_tag');
        }
        
        return $collection;
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
     * Retrieve a parsed version of the search string
     * If search by single word, string will be split on each space
     *
     * @return array
     */
    private function _getParsedSearchString()
    {
        $words = explode(' ', $this->getSearchTerm());

        if (count($words) > 15) {
            $words = array_slice($words, 0, $maxWords);
        }

        foreach ($words as $it => $word) {
            if (strlen($word) < 3) {
                unset($words[$it]);
            }
        }

        return $words;
    }
}
