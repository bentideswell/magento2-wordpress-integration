<?php
/**
 *
 */
namespace FishPig\WordPress\Block;

use Magento\Framework\View\Element\Template\Context;
use FishPig\WordPress\Model\Theme;
use FishPig\WordPress\Model\PostFactory;

class AdditionalCss extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Theme
     */
    protected $theme;

    /**
     * @var PostFactory
     */
    protected $postFactory;

    /**
     * @param Context $context
     * @param Theme $theme
     */
    public function __construct(Context $context, Theme $theme, PostFactory $postFactory)
    {
        $this->theme = $theme;
        $this->postFactory = $postFactory;

        parent::__construct($context);
    }

    /**
     * @return string
     */
    public function toHtml()
    {
        if ($additionalCss = $this->getAdditionalCss()) {
            return $additionalCss;
        }

        return '';
    } 

    /**
     * @return string|false
     */
    public function getAdditionalCss()
    {
        if (!$this->canIncludeCss()) {
            return false;
        }

        $postId = (int)$this->theme->getThemeMods('custom_css_post_id');

        if (!$postId) {
            return false;
        }

        $post = $this->postFactory->create()->load($postId);

        if (!$post->getId()) {
            return false;
        }

        if ($customCss = trim($post->getData('post_content'))) {
            return '<style type="text/css" id="wp-custom-css">' . $customCss . '</style>';
        }

        return false;
    }

    /**
     * This can be changed via a plugin
     *
     * @return bool
     */
    public function canIncludeCss()
    {
        return true;
    } 
}
