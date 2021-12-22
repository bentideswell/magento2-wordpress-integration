<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model;

use FishPig\WordPress\Api\Data\PostCollectionGeneratorInterface;
use FishPig\WordPress\Api\Data\ViewableModelInterface;

class Search extends \Magento\Framework\DataObject implements ViewableModelInterface, PostCollectionGeneratorInterface
{
    /**
     * @const string
     */
    const ENTITY = 'wordpress_search';
    const CACHE_TAG = 'wordpress_search';
    const VAR_NAME = 's';
    const VAR_NAME_POST_TYPE = 'post_type';

    /**
     * @param  \Magento\Framework\App\RequestInterface $request
     * @param  array $data = []
     */
    public function __construct(
        \FishPig\WordPress\Model\Context $wpContext,
        \FishPig\WordPress\Model\PostTypeRepository $postTypeRepository,
        \Magento\Framework\App\RequestInterface $request,
        array $data = []
    ) {
        $this->url = $wpContext->getUrl();
        $this->postCollectionFactory = $wpContext->getPostCollectionFactory();
        $this->postTypeRepository = $postTypeRepository;
        $this->request = $request;
        parent::__construct($data);
    }

    /**
     * Get the name of the search
     *
     * @return string
     */
    public function getName()
    {
        return __('Search results for \'%1\'', $this->getSearchTerm());
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        if (!($searchTerm = trim($this->getSearchTerm()))) {
            return false;
        }
        
        $extra = [];

        if ($postTypes = $this->getPostTypes()) {
            foreach ($postTypes as $postType) {
                $extra[] = self::VAR_NAME_POST_TYPE . '[]=' . urlencode($postType) . '&';
            }
        }

        foreach (['cat', 'tag'] as $key) {
            if ($value = $this->request->getParam($key)) {
                if (is_array($value)) {
                    foreach ($values as $v) {
                        $extra[] = $key . '[]=' . $v;
                    }
                } else {
                    $extra[] = $key . '=' . $value;
                }
            }
        }

        $extra = rtrim('?' . implode('&', $extra), '?');

        return $this->url->getHomeUrlWithFront(
            'search/' . urlencode(strtolower($this->getSearchTerm())) . '/' . $extra
        );
    }

    /**
     * @return \FishPig\WordPress\Model\ResourceModel\Post\Collection
     */
    public function getPostCollection(): \FishPig\WordPress\Model\ResourceModel\Post\Collection
    {
        $collection = $this->postCollectionFactory->create()->addSearchStringFilter(
            $this->getParsedSearchString(),
            [
                'post_title' => 5,
                'post_content' => 1
            ]
        );

        // Post Types
        $searchablePostTypes = $this->request->getParam('post_type');

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
        if ($categorySlug = $this->request->getParam('cat')) {
            $collection->addTermFilter($categorySlug, 'category');
        }

        // Tag
        if ($tagSlug = $this->request->getParam('tag')) {
            $collection->addTermFilter($tagSlug, 'post_tag');
        }
        
        return $collection;
    }

    /**
     * Get the search term
     *
     * @return string
     */
    public function getSearchTerm()
    {
        if (!$this->getData('search_term')) {
            return $this->request->getParam(self::VAR_NAME);
        }

        return $this->getData('search_term');
    }
    
    /**
     * Get an array of post types
     *
     * @return array
     */
    public function getPostTypes()
    {
        return $this->request->getParam(self::VAR_NAME_POST_TYPE);
    }
    
    /**
     * Retrieve a parsed version of the search string
     * If search by single word, string will be split on each space
     *
     * @return array
     */
    private function getParsedSearchString()
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
