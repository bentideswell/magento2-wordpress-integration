<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Block\PostType;

use FishPig\WordPress\Model\PostType;

class View extends \FishPig\WordPress\Block\Post\PostList\Wrapper\AbstractWrapper
{
    /**
     * @var PostType
     */
    private $postType = null;

    /**
     * @var \FishPig\WordPress\Helper\BlogInfo
     */
    private $blogInfo = null;

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
        $this->blogInfo = $wpContext->getBlogInfo();
        parent::__construct($context, $wpContext, $postCollectionFactory, $data);
    }

    /**
     * @return PostType
     */
    public function getPostType(): PostType
    {
        if ($this->postType === null) {
            if ($postType = $this->registry->registry(PostType::ENTITY)) {
                $this->postType = $postType;
            } else {
                throw new \Magento\Framework\Exception\NoSuchEntityException(
                    __("PostType not set in block '%1'.", $this->getNameInLayout())
                );
            }
        }
        
        return $this->postType;
    }

    /**
     * @return \FishPig\WordPress\Model\ResourceModel\Post\Collection
     */
    protected function getBasePostCollection(): \FishPig\WordPress\Model\ResourceModel\Post\Collection
    {
        $postType = $this->getPostType();
        $collection = $postType->getPostCollection();
        
        if ($postType->isFrontPage() || $postType->getPostType() === 'post') {
            $collection->addStickyPostsToCollection();
        }

        return $collection;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return (string)$this->blogInfo->getBlogDescription();
        return (string)$this->getTerm()->getDescription();
    }

    /**
     * @deprecated 3.0 use self::getPostType
     */
    public function getEntity()
    {
        return $this->getPostType();
    }
}
