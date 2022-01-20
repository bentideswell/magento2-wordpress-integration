<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Controller\JsonApi;

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
        $restUrl = $this->url->getRestUrl(
            $this->getRequest()->getParam('json_route_data') ?: '/'
        );

        return $this->resultFactory->create(
            $this->resultFactory::TYPE_REDIRECT
        )->setUrl(
            $restUrl
        )->setHttpResponseCode(
            302
        );
    }
}
