<?php
/**
 *
 */
namespace FishPig\WordPress\Controller\Homepage;

use FishPig\WordPress\Controller\Action;
use FishPig\WordPress\Model\Homepage;
use FishPig\WordPress\Model\Post;
use Magento\Framework\Controller\ResultFactory;

class View extends Action
{    
    /**
     * @return Homepage
     */
    protected function _getEntity()
    {
        return $this->factory->get('Homepage');
    }

    /**
     * @return bool
     */
    protected function _canPreview()
    {
        return true;
    }

    /**
     * Get the blog breadcrumbs
     *
     * @return array
     */
    protected function _getBreadcrumbs()
    {
        $crumbs = parent::_getBreadcrumbs();

        if ($this->url->isRoot()) {
            $crumbs['blog'] = [
                'label' => __($this->_getEntity()->getName()),
                'title' => __($this->_getEntity()->getName())
            ];
        }
        else {
            unset($crumbs['blog']['link']);
        }

        return $crumbs;
    }

    /**
     * Set the 'wordpress_front_page' handle if this is the front page
     *
     *
     * @return array
     */
    public function getLayoutHandles()
    {
        $handles = ['wordpress_homepage_view'];

        if ($entity = $this->_getEntity()) {
            if (!$entity->getStaticFrontPageId()) {
                $handles[] = 'wordpress_front_page';
            }

            if ($page = $entity->getFrontStaticPage()) {
                if ($template = $page->getMetaValue('_wp_page_template')) {
                    if ($template !== 'default') {
                        $templateName = str_replace('.php', '', $template);

                        $handles[] = 'wordpress_post_view_' . $templateName;
                        $handles[] = 'wordpress_post_view_' . $templateName . '_' . $page->getId();
                    }
                } 
            }
        }

        return array_merge(parent::getLayoutHandles(), $handles);
    }
}
