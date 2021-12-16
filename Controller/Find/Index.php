<?php
/**
 *
 */
namespace FishPig\WordPress\Controller\Find;

class Index extends \Magento\Framework\App\Action\Action
{
    /**
     *
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \FishPig\WordPress\App\Integration\Tests $integrationTests,
        \FishPig\WordPress\Helper\FrontPage $frontPageHelper,
        \FishPig\WordPress\Model\UrlInterface $url
    ) {
        $this->integrationTests = $integrationTests;
        $this->frontPageHelper = $frontPageHelper;
        $this->url = $url;
        parent::__construct($context);
    }
    
    /**
     *
     */
    public function execute()
    {
        if ($this->integrationTests->runTests() === false) {
            $this->messageManager->addError(__('Integration tests failed.'));
            return $this->_forward('noRoute');
        }

        if ($frontPage = $this->frontPageHelper->getFrontPage()) {
            $this->redirectTo($frontPage->getUrl());
        }
        
        if ($postsPage = $this->frontPageHelper->getPostsPage()) {
            $this->redirectTo($postsPage->getUrl());
        }
            
        return $this->redirectTo($this->url->getHomeUrl());
    }
    
    /**
     * @param  string $url
     * @param  $httpCode = 302
     * @return
     */
    private function redirectTo($url, $httpCode = 302)
    {
        return $this->resultFactory
            ->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT)
            ->setHttpResponseCode($httpCode)
            ->setUrl($url);
    }
}
