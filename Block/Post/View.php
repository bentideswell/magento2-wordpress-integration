<?php
/**
 *
 */
namespace FishPig\WordPress\Block\Post;

use FishPig\WordPress\Block\Post;

class View extends Post
{
    /**
     *
     */
    protected function _beforeToHtml()
    {
        if (!$this->getPost()) {
            return false;
        }

        $this->getPost()->getContent();
        
        if (!$this->getTemplate()) {
            $postType = $this->getPost()->getTypeInstance();
            $this->setTemplate('FishPig_WordPress::post/view.phtml');

            if ($postType->getPostType() !== 'post') {
                $postTypeTemplate = 'FishPig_WordPress::' . $postType->getPostType() . '/view.phtml';

                if ($this->getTemplateFile($postTypeTemplate)) {
                    $this->setTemplate($postTypeTemplate);
                }
            }
        }

        return parent::_beforeToHtml();
    }
}
