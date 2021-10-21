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
     * @var \FishPig\WordPress\Model\Post
     */
    private $frontPage = null;
    private $postsPage = null;

    /**
     *
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context, 
        \FishPig\WordPress\Model\OptionRepository $optionRepository,
        \FishPig\WordPress\Model\PostRepository $postRepository
    ) {
        $this->optionRepository = $optionRepository;
        $this->postRepository = $postRepository;
        
        parent::__construct($context);
    }

    /**
     * @return \FishPig\WordPress\Model\Post|false
     */
    public function getFrontPage()
    {
        if ($this->frontPage === null) {
            $this->frontPage = $this->getFrontPageId()
                ? $this->postRepository->get($this->getFrontPageId()) 
                : false;
        }

        return $this->frontPage;
    }

    /**
     * @return \FishPig\WordPress\Model\Post|false
     */
    public function getPostsPage()
    {
        if ($this->postsPage === null) {
            $this->postsPage = $this->getPostsPageId()
                ? $this->postRepository->get($this->getPostsPageId()) 
                : false;
        }

        return $this->postsPage;
    }
    
    /**
     * @return bool
     */
    public function isFrontPageDefaultPostTypeArchive(): bool
    {
        return $this->getShowOnFront() === '';
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
    private function getShowOnFront(): string
    {
        return (string)$this->optionRepository->get('show_on_front');
    }
}
