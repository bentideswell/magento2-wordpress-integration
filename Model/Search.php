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
     * @auto
     */
    protected $url = null;

    /**
     * @auto
     */
    protected $postCollectionFactory = null;

    /**
     * @auto
     */
    protected $wpContext = null;

    /**
     * @auto
     */
    protected $postTypeRepository = null;

    /**
     * @auto
     */
    protected $request = null;

    /**
     * @auto
     */
    protected $restRequestManager = null;

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
        \FishPig\WordPress\App\Api\Rest\RequestManager $restRequestManager,
        array $data = []
    ) {
        $this->url = $wpContext->getUrl();
        $this->postCollectionFactory = $wpContext->getPostCollectionFactory();
        $this->postTypeRepository = $postTypeRepository;
        $this->request = $request;
        $this->restRequestManager = $restRequestManager;
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
        // TO revert to default search, return null from self::getSearchApiEndpoint
        if ($searchEndpoint = $this->getSearchApiEndpoint()) {
            $postIds = array_column(
                $this->restRequestManager->getJsonCached($searchEndpoint),
                'id'
            );

            $collection = $this->postCollectionFactory->create();

            if (!$postIds) {
                // No post IDs so force collection to be empty
                $collection->getSelect()->where('1=2')->limit(1);
                return $collection;
            } else {
                $collection->addFieldToFilter('ID', ['in' => $postIds]);
                $collection->getSelect()->order('FIELD(ID, ' . implode(',', $postIds) . ')');
            }
        } else {
            $collection = $this->postCollectionFactory->create()->addSearchStringFilter(
                $this->getParsedSearchString(),
                [
                    'post_title' => 5,
                    'post_content' => 1
                ]
            );
        }

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
    public function getSearchTerm(): string
    {
        if (!$this->getData('search_term')) {
            return (string)$this->request->getParam(self::VAR_NAME);
        }

        return (string)$this->getData('search_term');
    }

    /**
     * Get an array of post types
     *
     * @return array
     */
    public function getPostTypes()
    {
        return (array)$this->request->getParam(self::VAR_NAME_POST_TYPE);
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

    /**
     *
     */
    public function getSearchApiEndpoint(): ?string
    {
        return sprintf(
            '/wp/v2/search&search=%s&per_page=%d',
            urlencode($this->getSearchTerm()),
            100
        );
    }
}
