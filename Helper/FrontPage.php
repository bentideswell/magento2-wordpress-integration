<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Helper;

class FrontPage extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var []
     */
    private $frontPage = [];

    /**
     * @var []
     */
    private $postsPage = [];

    /**
     *
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \FishPig\WordPress\Model\UrlInterface $url,
        \FishPig\WordPress\Model\OptionRepository $optionRepository,
        \FishPig\WordPress\Model\PostRepository $postRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->url = $url;
        $this->optionRepository = $optionRepository;
        $this->postRepository = $postRepository;
        $this->storeManager = $storeManager;
        
        parent::__construct($context);
    }

    /**
     * @return \FishPig\WordPress\Model\Post|false
     */
    public function getFrontPage()
    {
        $storeId = $this->getStoreId();
        
        if (!isset($this->frontPage[$storeId])) {
            $this->frontPage[$storeId] = $this->getFrontPageId()
                ? $this->postRepository->getQuietly($this->getFrontPageId())
                : false;
        }

        return $this->frontPage[$storeId];
    }

    /**
     * @return \FishPig\WordPress\Model\Post|false
     */
    public function getPostsPage()
    {
        $storeId = $this->getStoreId();

        if (!isset($this->postsPage[$storeId])) {
            $this->postsPage[$storeId] = $this->getPostsPageId()
                ? $this->postRepository->getQuietly($this->getPostsPageId())
                : false;
        }

        return $this->postsPage[$storeId];
    }
    
    /**
     * @return bool
     */
    public function isFrontPageDefaultPostTypeArchive(): bool
    {
        return $this->getShowOnFront() === 'posts';
    }
    
    /**
     * @return bool
     */
    public function isFrontPageStaticPage(): bool
    {
        return $this->getShowOnFront() === 'page';
    }

    /**
     * @return int|false
     */
    public function getFrontPageId()
    {
        return $this->isFrontPageStaticPage() && ($pageId = (int)$this->optionRepository->get('page_on_front'))
            ? $pageId
            : false;
    }

    /**
     * @return int|false
     */
    public function getPostsPageId()
    {
        return $this->isFrontPageStaticPage() && ($pageId = (int)$this->optionRepository->get('page_for_posts'))
            ? $pageId
            : false;
    }
    
    /**
     * @return string
     */
    public function getRealHomepageUrl(): string
    {
        if ($frontPage = $this->getFrontPage()) {
            if ($frontPage->isPublished()) {
                return $frontPage->getUrl();
            }
        }
        
        return $this->url->getHomeUrl();
    }
    
    /**
     * @return string
     */
    private function getShowOnFront(): string
    {
        return (string)$this->optionRepository->get('show_on_front');
    }
    
    /**
     * @return int
     */
    private function getStoreId(): int
    {
        return (int)$this->storeManager->getStore()->getId();
    }
}
