<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model;

class Search extends \Magento\Framework\DataObject implements \FishPig\WordPress\Api\Data\Entity\ViewableInterface
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
        \FishPig\WordPress\Model\UrlInterface $url,
        \Magento\Framework\App\RequestInterface $request,
        array $data = []
    ) {
        $this->url = $url;
        $this->request = $request;
        parent::__construct($data);
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
     * Get the name of the search
     *
     * @return string
     */
    public function getName()
    {
        return __('Search results for %1', $this->getSearchTerm());
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
            'search/' . urlencode($this->getSearchTerm()) . '/' . $extra
        );
    }
}
