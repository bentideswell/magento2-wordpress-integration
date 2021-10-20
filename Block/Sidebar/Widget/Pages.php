<?php
/**
 * @category FishPig
 * @package  FishPig_WordPress
 * @author   Ben Tideswell <help@fishpig.co.uk>
 */
namespace FishPig\WordPress\Block\Sidebar\Widget;

class Pages extends AbstractWidget
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
        array $data = []
    ) {
        $this->postCollectionFactory = $postCollectionFactory;

        parent::__construct($context, $wpContext, $data);
    }
    
    /**
     * Returns the currently loaded page model
     *
     * @return FishPig\WordPress\Model\Post
     */
    public function getPost()
    {
        if (!$this->hasPost()) {
            $this->setPost(false);

            if ($post = $this->registry->registry('wordpress_post')) {
                if ($post->getPostType() === 'page') {
                    $this->setPost($post);
                }
            }
        }

         return $this->_getData('post');
    }

    /**
     * Retrieve a collection  of pages
     *
     * @return FishPig\WordPress\Model\ResourceModel\Post\Collection
     */
    public function getPages()
    {
        return $this->getPosts();
    }

    /**
     * @return false|array
     */
    public function getExcludedPageIds()
    {
        if (!$this->hasData('excluded_page_ids')) {
            $this->setData('excluded_page_ids', false);

            $excluded = explode(',', trim($this->getData('exclude')));
            
            foreach ($excluded as $key => $value) {
                if (($value = (int)trim($value)) === 0) {
                    unset($excluded[$key]);
                } else {
                    $excluded[$key] = $value;
                }
            }
            
            if ($excluded) {
                $this->setData('excluded_page_ids', $excluded);
            }
        }
        
        return $this->getData('excluded_page_ids');
    }

    /**
     *
     */
    public function getPosts()
    {
        $posts = $this->postCollectionFactory->create()->addPostTypeFilter('page')->addIsViewableFilter();

        if ($this->hasParentId()) {
            $posts->addPostParentIdFilter($this->getParentId());
        } elseif ($this->getPost() && $this->getPost()->hasChildren()) {
            $posts->addPostParentIdFilter($this->getPost()->getId());
        } else {
            $posts->addPostParentIdFilter(0);
        }
        
        if ($excludedIds = $this->getExcludedPageIds()) {
            $posts->addFieldToFilter('main_table.ID', ['nin' => $excludedIds]);
        }

        return $posts->load();
    }

    /**
     * Retrieve the block title
     *
     * @return string
     */
    public function getTitle()
    {
        if ($this->getPost() && $this->getPost()->hasChildren()) {
            return $this->getPost()->getPostTitle();
        }

        return parent::getTitle();
    }

    /**
     * Retrieve the default title
     *
     * @return string
     */
    public function getDefaultTitle()
    {
        return __('Pages');
    }

    protected function _beforeToHtml()
    {
        if (!$this->getTemplate()) {
            $this->setTemplate('FishPig_WordPress::sidebar/widget/pages.phtml');
        }

        return parent::_beforeToHtml();
    }
}
