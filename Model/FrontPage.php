<?php
/**
 *
 */
namespace FishPig\WordPress\Model;


class FrontPage extends \Magento\Framework\DataObject implements \FishPig\WordPress\Api\Data\Entity\ViewableInterface
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
     *
     */
    public function __construct(
        \FishPig\WordPress\App\Option $option,
        \FishPig\WordPress\Model\PostRepository $postRepository
    ) {
        $this->option = $option;
        $this->postRepository = $postRepository;
    }

    /**
     * @return string
     */
    public function getName()
    {
        if ($staticPage = $this->getFrontStaticPage()) {
            return $staticPage->getName();
        }

        return $this->getBlogName();
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        if ($staticPage = $this->getFrontStaticPage()) {
            return $staticPage->getUrl();
        }

        return $this->url->getUrl();
    }

    /**
     * @return string
     */
    public function getContent()
    {
        if ($staticPage = $this->getFrontStaticPage()) {
            return $staticPage->getContent();
        }

        return $this->getBlogDescription();
    }

    /**
     * @return false|string|FishPig\WordPress\Model\Image
     */
    public function getImage()
    {
        return false;
    }
    
    /**
     * @return
     */
    public function getFrontStaticPage()
    {
        if ($this->staticPage === null) {
            $this->staticPage = (int)$this->getPageForPostsId() > 0 
                                    ? $this->postRepository->get($this->getPageForPostsId()) 
                                    : false;
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
        if ($this->option->get('show_on_front') === 'page') {
            if ($pageId = $this->option->get('page_on_front')) {
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
        if ($this->option->get('show_on_front') === 'page') {
            if ($pageId = $this->option->get('page_for_posts')) {
                return $pageId;
            }
        }

        return false;
    }

    /**
     * @return string
     */
    public function getRealHomepageUrl()
    {
        if (!$this->hasRealHomepageUrl()) {
            $this->setRealHomepageUrl($this->getUrl());

            if ($frontPageId = $this->getFrontPageId()) {
                $this->setRealHomepageUrl($this->postRepository->get($frontPageId, 'page')->getUrl());
            }
        }

        return $this->_getData('real_homepage_url');
    }
}
