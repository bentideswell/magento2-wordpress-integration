<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Controller\Forwarder;

class View extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \FishPig\WordPress\Model\UrlInterface
     */
    private $url;

    /**
     *
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \FishPig\WordPress\Model\UrlInterface $url
    ) {
        $this->url = $url;
        parent::__construct($context);
    }

    /**
     *
     */
    public function execute()
    {
        if (!($requestUri = trim($this->getRequest()->getParam('request_uri')))) {
            throw new \FishPig\WordPress\App\Exception('Request URI not set so cannot redirect to WordPress.');
        }

        return $this->resultFactory->create(
            $this->resultFactory::TYPE_REDIRECT
        )->setUrl(
            $this->url->getSiteurl($requestUri)
        )->setHttpResponseCode(
            301
        );
    }
}
