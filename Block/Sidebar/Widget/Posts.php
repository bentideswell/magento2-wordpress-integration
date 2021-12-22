<?php
/**
 * @category FishPig
 * @package  FishPig_WordPress
 * @author   Ben Tideswell <help@fishpig.co.uk>
 */
namespace FishPig\WordPress\Block\Sidebar\Widget;

class Posts extends AbstractWidget
{
    /**
     * Cache for post collection
     *
     * @var FishPig_WordPressModel_Resource_Post_Collection
     */
    protected $collection = null;

    /**
     * @param  \Magento\Framework\View\Element\Template\Context $context,
     * @param  \FishPig\WordPress\Block\Context $wpContext,
     * @param  array $data = []
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \FishPig\WordPress\Block\Context $wpContext,
        \FishPig\WordPress\Model\ResourceModel\Post\CollectionFactory $postCollectionFactory,
        array $data = []
    ) {
        $this->postCollectionFactory = $postCollectionFactory;

        parent::__construct($context, $wpContext, $data);
    }
        
    /**
     * Set the posts collection
     */
    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();

        if (!$this->getTemplate()) {
            $this->setTemplate('FishPig_WordPress::sidebar/widget/posts.phtml');
        }

        return $this;
    }

    /**
     * Control the number of posts displayed
     *
     * @param  int $count
     * @return $this
     */
    public function setPostCount($count)
    {
        return $this->setNumber($count);
    }

    /**
     * Retrieve the number of posts to display
     * If the pager is enabled, this is posts per page
     *
     * @return int
     */
    public function getNumber()
    {
        return $this->_getData('number') ? $this->_getData('number') : 5;
    }

    /**
     *
     */
    public function getPosts()
    {
        return $this->_getPostCollection();
    }
    
    /**
     * Adds on cateogry/author ID filters
     *
     * @return FishPig_WordPressModel_Mysql4_Post_Collection
     */
    protected function _getPostCollection()
    {
        if ($this->collection === null) {
            $collection = $this->postCollectionFactory->create()
                ->setOrderByPostDate()
                ->addIsViewableFilter()
                ->setPageSize($this->getNumber())
                ->setCurPage(1);

            if ($categoryId = $this->getCategoryId()) {
                if (strpos($categoryId, ',') !== false) {
                    $categoryId = explode(',', trim($categoryId, ','));
                }

                $collection->addCategoryIdFilter($categoryId);
            }

            if ($authorId = $this->getAuthorId()) {
                $collection->addFieldToFilter('post_author', $authorId);
            }

            if ($tag = $this->getTag()) {
                $collection->addTermFilter($tag, 'post_tag', 'name');
            }

            if ($postTypes = $this->getPostType()) {
                $collection->addPostTypeFilter(explode(',', $postTypes));
            } else {
                $collection->addPostTypeFilter('post');
            }

            $this->collection = $collection;
        }

        return $this->collection;
    }

    /**
     * Retrieve the default title
     *
     * @return string
     */
    public function getDefaultTitle()
    {
        if ($this->getCategory()) {
            return $this->getCategory()->getName();
        }

        return __('Recent Posts');
    }

    /**
     * Retrieve the category model used to filter the posts
     *
     * @return \FishPig\WordPress\Model\Term|false
     */
    public function getCategory()
    {
        if (!$this->hasCategory()) {
            $this->setCategory(false);
            if ($this->getCategoryId()) {
                try {
                    $category = $this->termRepository->getWithTaxonomy((int)$this->getCategoryId(), 'category');
                    $this->setCategory($category)->setCategoryId($category->getId());
                } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                    $this->setCategory(false);
                }
            }
        }

        return $this->_getData('category');
    }

    /**
     * Retrieve the category ID
     *
     * return int|null
     */
    public function getCategoryId()
    {
        if ($categoryId = $this->_getData('category_id')) {
            return $categoryId;
        }

        return $this->_getData('cat');
    }

    /**
     * Retrieve the ID used for the list
     * This is necessary so multiple instances can be used
     *
     * @return string
     */
    public function getListId()
    {
        if (!$this->hasListId()) {
            // phpcs:ignore -- not cryptographic
            $hash = 'wp-' . md5(rand(1111, 9999) . $this->getCategoryId() . $this->getAuthorId() . $this->getTitle());

            $this->setListId(substr($hash, 0, 6));
        }

        return $this->_getData('list_id');
    }

    /**
     * Added to support 'Category Posts Widget' WP plugin
     */
    public function canDisplayCommentCount()
    {
        return $this->_getData('comment_num') == 'on';
    }

    /**
     * Determine whether we can display the date
     *
     * @return bool
     */
    public function canDisplayDate()
    {
        return $this->_getData('date') == 'on';
    }

    /**
     * Determine whether we can display the excerpt
     *
     * @return bool
     */
    public function canDisplayExcerpt()
    {
        return $this->getData('excerpt') == 'on';
    }

    /**
     * Determine whether we can display the image
     *
     * @return bool
     */
    public function canDisplayImage()
    {
        return $this->getData('thumb') === 'on';
    }

    /**
     * Determine whether we can display the title link
     *
     * @return bool
     */
    public function canDisplayTitleLink()
    {
        return $this->getData('title_link') == 'on';
    }

    /**
     * Retrieve the excerpt length
     *
     * @return null|int
     */
    public function getExcerptLength()
    {
        if ($this->canDisplayExcerpt()) {
            return $this->_getData('excerpt_length');
        }

        return null;
    }

    /**
     * Retrieve a string indicating the number of comments
     *
     * @param  \FishPig\WordPress\Model\Post $post
     * @return string
     */
    public function getCommentCountString(\FishPig\WordPress\Model\Post $post)
    {
        if ($post->getCommentCount() == 0) {
            return __('No Comments');
        } elseif ($post->getCommentCount() > 1) {
            return __('%s Comments', $post->getCommentCount());
        }

        return __('1 Comment');
    }
}
