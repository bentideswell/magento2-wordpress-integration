<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Block\Post;

use FishPig\WordPress\Model\ResourceModel\Post\Collection as PostCollection;
use FishPig\WordPress\Model\ResourceModel\Post\CollectionFactory as PostCollectionFactory;

class ListPost extends \FishPig\WordPress\Block\Post
{
    /**
     * @var
     */
    private $postCollectionFactory;

    /**
     * @var PostCollection
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
     * Returns the collection of posts
     *
     * @return
     */
    public function getPosts()
    {
        if ($this->postCollection === null) {
            $this->setPostCollection($this->postCollectionFactory->create());
        }

        return $this->postCollection;
    }

    /**
     * @param  PostCollection $collection
     * @return self
     */
    public function setPostCollection(PostCollection $collection): self
    {
        if ($this->postCollection !== null) {
            throw new \FishPig\WordPress\App\Exception('The post collection is already set in this block.');
        }

        $this->postCollection = $collection;
        
        if ($pager = $this->getChildBlock('pager')) {
            $pager->setPostListBlock(
                $this
            )->setCollection(
                $this->postCollection
            );
        }

        return $this;
    }

    /**
     * Get the HTML for the pager block
     *
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * Retrieve the correct renderer and template for $post
     *
     * @param  \FishPig\WordPress\Model\Post $post
     * @return FishPig\WordPress\Block_Post_List_Renderer
     */
    public function renderPost(\FishPig\WordPress\Model\Post $post)
    {
        // Create post block
        $postBlock = $this->getLayout()->createBlock(\FishPig\WordPress\Block\Post::class)->setPost($post);

        $vendors = ['FishPig_WordPress'];

        // First try post type specific template then fall back to default
        $templates = [
          'post/list/renderer/' . $post->getPostType() . '.phtml',
          'post/list/renderer/default.phtml',
        ];

        $templatesToTry = [];

        foreach ($templates as $template) {
            foreach ($vendors as $vendor) {
                if ($vendor) {
                    $templatesToTry[] = $vendor . '::' . $template;
                }
            }
        }

        if ($rendererTemplate = $this->getData('renderer_template')) {
            array_unshift($templatesToTry, $rendererTemplate);
        }

        foreach ($templatesToTry as $templateToTry) {
            if ($this->getTemplateFile($templateToTry)) {
                $postBlock->setTemplate($templateToTry);
                break;
            }
        }

        // Get HTML and return
        return $postBlock->toHtml();
    }

    /**
     *
     */
    protected function _beforeToHtml()
    {
        if (!$this->getTemplate()) {
            $this->setTemplate('FishPig_WordPress::post/list.phtml');
        }

        return parent::_beforeToHtml();
    }
}
