<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model;

class Homepage
{
    /**
     * @param \FishPig\WordPress\Helper\FrontPage $frontPage
     */
    public function __construct(
        \FishPig\WordPress\Helper\FrontPage $frontPage
    ) {
        $this->frontPage = $frontPage;
    }
    
    /**
     * @return
     */
    public function getFrontStaticPage()
    {
        return $this->frontPage->getFrontPage();
    }

    /**
     * @return int|false
     */
    public function getFrontPageId()
    {
        return $this->frontPage->getFrontPageId();
    }

    /**
     * @return int|false
     */
    public function getPageForPostsId()
    {
        return $this->frontPage->getPostsPageId();
    }

    /**
     * @return string
     */
    public function getRealHomepageUrl()
    {
        return $this->frontPage->getRealHomepageUrl();
    }
}
