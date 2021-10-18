<?php
/**
 *
 */
namespace FishPig\WordPress\Block;

use FishPig\WordPress\Block\AbstractBlock;
use Magento\Framework\DataObject\IdentityInterface;

class Post extends AbstractBlock implements IdentityInterface
{
    /**
     * @return ?\FishPig\WordPress\Model\Post
     */
    public function getPost()
    {
        return $this->_getData('post') ?? $this->registry->registry('wordpress_post');
    }

    /**
     * @return int|false
     */
    public function getPostId()
    {
        return $this->getPost() ? $this->getPost()->getId() : false;
    }

    /**
     * Returns true if comments are enabled for this post
     *
     * @return bool
     */
    public function canComment()
    {
        return $this->getPost() && $this->getPost()->getCommentStatus() === 'open';
    }

    /**
     * If post view, setup the post with child blocks
     *
     * @return $this
     */
    protected function _beforeToHtmlIgnore()
    {
        if ($this->getPost() && $this->_getBlockForPostPrepare() !== false) {
            $this->_prepareChildBlocks($this->_getBlockForPostPrepare());
        }

        return parent::_beforeToHtml();
    }

    /**
     * Set the post as the current post in all child blocks
     *
     * @param  \FishPig\WordPress\Model\Post $post
     * @return $this
     */
    protected function _prepareChildBlocks($rootBlock)
    {
        if (is_string($rootBlock)) {
            $rootBlock = $this->getChildBlock($rootBlock);
        }

        if ($rootBlock) {
            foreach ($rootBlock->getChildNames() as $name) {
                if ($block = $rootBlock->getChildBlock($name)) {
                    $block->setPost($this->getPost());

                    $this->_prepareChildBlocks($block);
                } elseif ($containerBlockNames = $this->getLayout()->getChildNames($name)) {
                    foreach ($containerBlockNames as $containerBlockName) {
                        if ($block = $this->getLayout()->getBlock($containerBlockName)) {
                            $block->setPost($this->getPost());

                            $this->_prepareChildBlocks($block);
                        }
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Retrieve the block used to prepare the post
     * This should be the root post block
     *
     * @return FishPig\WordPress\Block_Post_Abstract
     */
    protected function _getBlockForPostPrepare()
    {
        return $this;
    }

    /**
     * Return identifiers for produced content
     *
     * @return array
     */
    public function getIdentities()
    {
        return $this->getPost() ? $this->getPost()->getIdentities() : [];
    }
    
    /**
     *
     */
    public function getPasswordProtectHtml($post = null)
    {
        if (is_null($post)) {
            $post = $this->getPost();
        }

        return $this->getLayout()
            ->createBlock(self::class)
            ->setTemplate('FishPig_WordPress::post/protected.phtml')
            ->setEntityType('post')
            ->setPost($post)
            ->toHtml();
    }
}
