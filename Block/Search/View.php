<?php
/**
 *
 */
namespace FishPig\WordPress\Block\Search;

use FishPig\WordPress\Block\Post\PostList\Wrapper\AbstractWrapper;

class View extends AbstractWrapper
{
    /**
     *
     *
     * @return
     */
    public function getEntity()
    {
        if ($this->getData('entity')) {
            return $this->getData('entity');
        }

        return $this->registry->registry('wordpress_search');
    }

    /**
     * Generates and returns the collection of posts
     *
     * @return FishPig\WordPress\Model_Mysql4_Post_Collection
     */
    protected function _getPostCollection()
    {
        $collection = parent::_getPostCollection()    
            ->addSearchStringFilter($this->_getParsedSearchString(), array('post_title' => 5, 'post_content' => 1));

        $searchablePostTypes = $this->getRequest()->getParam('post_type');

        if (!$searchablePostTypes) {
            $postTypes = $this->wpContext->getPostTypeManager()->getPostTypes();
            $searchablePostTypes = array();

            foreach($postTypes as $postType) {
                if ($postType->isSearchable()) {
                    $searchablePostTypes[] = $postType->getPostType();
                }
            }
        }

        if (!$searchablePostTypes) {
            $searchablePostTypes = array('post', 'page');
        }

        return $collection->addPostTypeFilter($searchablePostTypes);
    }

    /**
     * Retrieve a parsed version of the search string
     * If search by single word, string will be split on each space
     *
     * @return array
     */
    protected function _getParsedSearchString()
    {
        $words = explode(' ', $this->getSearchTerm());

        if (count($words) > 15) {
            $words = array_slice($words, 0, $maxWords);
        }

        foreach($words as $it => $word) {
            if (strlen($word) < 3) {
                unset($words[$it]);
            }
        }

        return $words;
    }

    /**
     * Retrieve the current search term
     *
     * @param bool $escape = false
     * @return string
     */
    public function getSearchTerm($escape = false)
    {
        return $this->getEntity() ? $this->getEntity()->getSearchTerm($escape) : '';
    }

    /**
     * Retrieve the search variable
     *
     * @return string
     */
    public function getSearchVar()
    {
        return $this->_getData('search_var') ? $this->_getData('search_var') : 's';
    }
}
