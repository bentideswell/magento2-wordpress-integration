<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Block\Post\PostList\Wrapper;

abstract class AbstractWrapper extends \FishPig\WordPress\Block\AbstractBlock
{
    /**
     * @return \FishPig\WordPress\Model\ResourceModel\Post\Collection
     */
    abstract protected function getBasePostCollection(): \FishPig\WordPress\Model\ResourceModel\Post\Collection;
    
    /**
     *
     */
    private $postCollection = null;
    
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
     * @return \FishPig\WordPress\Model\ResourceModel\Post\Collection
     */
    public function getPostCollection(): \FishPig\WordPress\Model\ResourceModel\Post\Collection
    {
        if ($this->postCollection === null) {
            $this->postCollection = $this->getBasePostCollection()
                ->addIsViewableFilter()
                ->addOrder(
                    'post_date',
                    'desc'
                );
        }

        return $this->postCollection;
    }

    /**
     * Returns the HTML for the post collection
     *
     * @return string
     */
    public function getPostListHtml()
    {
        if (($postListBlock = $this->getPostListBlock()) !== false) {
            return $postListBlock->toHtml();
        }

        return '';
    }

    /**
     * Gets the post list block
     *
     * @return \FishPig\WordPress\Block\ListPost
     */
    public function getPostListBlock()
    {
        if (!($postListBlock = $this->getChildBlock('wp.post.list'))) {
            $postListBlock = $this->getLayout()
                ->createBlock(\FishPig\WordPress\Block\Post\ListPost::class)
                ->setTemplate('FishPig_WordPress::post/list.phtml');

                $this->setChild('wp.post.list', $postListBlock);
        }

        $postListBlock->setPostCollection($this->getPostCollection());

        return $postListBlock;
    }

    /**
     * Ensure a template is set
     *
     * @return $this
     */
    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();

        if (!$this->getTemplate()) {
            $this->setTemplate('FishPig_WordPress::post/list/wrapper.phtml');
        }

        return $this;
    }
    
    /**
     * @deprecated since 3.0
     * @return string
     */
    public function getIntroText()
    {
        return $this->getDescription();
    }
}
