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
//        \FishPig\WordPress\Model\Homepage $wpHomepage,
        \FishPig\WordPress\Model\UrlInterface $wpUrl
    ) {
        $this->integrationTests = $integrationTests;
//        $this->wpHomepage = $wpHomepage;
        $this->wpUrl = $wpUrl;
        
        parent::__construct($context);
    }
    
    /**
     *
     */
    public function execute()
    {
        if ($this->integrationTests->runTests() === false) {
            return $this->_forward('noRoute');
        }

//        if ($defaultPostArchiveUrl = $this->wpHomepage->getUrl()) {
//            return $this->redirectTo($defaultPostArchiveUrl);
//        }
            
        return $this->redirectTo($this->wpUrl->getHomeUrl());
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
