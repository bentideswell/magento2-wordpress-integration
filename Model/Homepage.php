<?php
/**
 *
 */
namespace FishPig\WordPress\Model;

use FishPig\WordPress\Model\AbstractResourcelessModel;
use FishPig\WordPress\Api\Data\Entity\ViewableInterface;

class Homepage extends AbstractResourcelessModel implements ViewableInterface
{
    /**
     * @const string
     */
    const ENTITY = 'wordpress_homepage';
    const CACHE_TAG = 'wordpress_homepage';

    /**
     * @var
     */    
    protected $staticPage;

    /**
     * @return  string
     */
    public function getName()
    {
        if ($staticPage = $this->getFrontStaticPage()) {
            return $staticPage->getName();
        }

        return $this->getBlogName();
    }

    /**
     * @return  string
     */
    public function getUrl()
    {
        if ($staticPage = $this->getFrontStaticPage()) {
            return $staticPage->getUrl();
        }

        return $this->url->getUrl();
    }

    /**
     * @return  string
     */
    public function getContent()
    {
        if ($staticPage = $this->getFrontStaticPage()) {
            return $staticPage->getContent();
        }

        return $this->getBlogDescription();
    }

    /**
     *
     */
    public function getMetaDescription()
    {
        if ($staticPage = $this->getFrontStaticPage()) {
            return $staticPage->getMetaDescription();
        }

        return $this->getBlogDescription();
    }

    /**
     * @return 
     */
    public function getFrontStaticPage()
    {
        if ($this->staticPage !== null) {
            return $this->staticPage;
        }

        $this->staticPage = false;

        if ((int)$this->getPageForPostsId() > 0) {
            $staticPage = $this->factory->create('FishPig\WordPress\Model\Post')->load(
                $this->getPageForPostsId()
            );

            if ($staticPage->getId()) {
                $this->staticPage = $staticPage;
            }
        }

        return $this->staticPage;
    }

    /**
     * If a page is set as a custom homepage, get it's ID
     *
     * @return int|false

     */
    public function getFrontPageId()
    {
        if ($this->optionManager->getOption('show_on_front') === 'page') {
            if ($pageId = $this->optionManager->getOption('page_on_front')) {
                return $pageId;
            }
        }

        return false;
    }

    /**
     * If a page is set as a custom homepage, get it's ID
     *
     * @return int|false
     */
    public function getPageForPostsId()
    {
        if ($this->optionManager->getOption('show_on_front') === 'page') {
            if ($pageId = $this->optionManager->getOption('page_for_posts')) {
                return $pageId;
            }
        }

        return false;
    }

    /**
     *
     */
    public function load($modelId, $field = null)
    {
        return $this;
    }

    /**
     * @return string
     */
    public function getRealHomepageUrl()
    {
        if (!$this->hasRealHomepageUrl()) {
            $this->setRealHomepageUrl($this->getUrl());

            if ($this->getFrontPageId()) {
                $page = $this->factory->create('FishPig\WordPress\Model\Post')->setTaxonomy('page')->load($this->getFrontPageId());

                if ($page->getId()) {
                    $this->setRealHomepageUrl($page->getUrl());
                }
            }
        }

        return $this->_getData('real_homepage_url');
    }
}
