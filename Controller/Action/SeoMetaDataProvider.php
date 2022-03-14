<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Controller\Action;

abstract class SeoMetaDataProvider implements \FishPig\WordPress\Api\Controller\Action\SeoMetaDataProviderInterface
{
    /**
     * @var \Magento\Framework\View\Result\Page
     */
    protected $resultPage = null;

    /**
     * @var \FishPig\WordPress\Helper\BlogInfo
     */
    private $blogInfo;

    /**
     * @param \FishPig\WordPress\Helper\BlogInfo $blogInfo
     */
    public function __construct(
        \FishPig\WordPress\Helper\BlogInfo $blogInfo
    ) {
        $this->blogInfo = $blogInfo;
    }

    /**
     * @param  \Magento\Framework\View\Result\Page $resultPage,
     * @param  \FishPig\WordPress\Api\Data\ViewableModelInterface $object
     * @return void
     */
    public function addMetaData(
        \Magento\Framework\View\Result\Page $resultPage,
        \FishPig\WordPress\Api\Data\ViewableModelInterface $object
    ): void {
        $this->resultPage = $resultPage;
        
        if (!$this->getBlogInfo()->isBlogPublic()) {
            $this->setRobots('NOINDEX,NOFOLLOW');
        }

        if ($blogDescription = $this->getBlogInfo()->getBlogDescription()) {
            $this->resultPage->getConfig()->setDescription($blogDescription);
        }
    }
    
    /**
     * @param  string $metaTitle
     * @return void
     */
    protected function setMetaTitle(string $metaTitle): void
    {
        $this->resultPage->getConfig()->getTitle()->set(
            $metaTitle
        );
    }
    
    /**
     * @param  string $metaDescription
     * @return void
     */
    protected function setMetaDescription(string $metaDescription): void
    {
        $this->resultPage->getConfig()->setDescription(
            $this->stripHtmlTags($metaDescription)
        );
    }

    /**
     * @param  string $pageTitle
     * @return void
     */
    protected function setPageTitle(string $pageTitle): void
    {
        if ($pageMainTitle = $this->resultPage->getLayout()->getBlock('page.main.title')) {
            $pageMainTitle->setPageTitle(
                $this->stripHtmlTags($pageTitle)
            );
        }
    }

    /**
     * @param  string $url
     * @return void
     */
    protected function setCanonicalUrl(string $url): void
    {
        $this->resultPage->getConfig()->addRemotePageAsset(
            $url,
            'canonical',
            ['attributes' => ['rel' => 'canonical']]
        );
    }
    
    /**
     * @param  string $metaTitle
     * @return void
     */
    protected function setMetaTitleWithBlogName(string $metaTitle): void
    {
        $this->setMetaTitle($metaTitle . ' | ' . $this->getBlogInfo()->getBlogName());
    }
    
    /**
     * @param  string|array $robots
     * @return void
     */
    protected function setRobots($robots): void
    {
        if (is_array($robots)) {
            $robots = implode(',', array_filter($robots));
        }

        if (stripos($this->resultPage->getConfig()->getRobots(), 'noindex') === false) {
            $this->resultPage->getConfig()->setRobots($robots);
        }
    }
    
    /**
     * @return \FishPig\WordPress\Helper\BlogInfo
     */
    protected function getBlogInfo(): \FishPig\WordPress\Helper\BlogInfo
    {
        return $this->blogInfo;
    }
    
    /**
     * @param mixed
     * @return ?string
     */
    private function stripHtmlTags($s)
    {
        return $s ? strip_tags((string)$s) : $s;
    }
}
