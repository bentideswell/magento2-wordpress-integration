<?php
/**
 *
 */
namespace FishPig\WordPress\Controller\Forwarder;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\RedirectFactory;
use FishPig\WordPress\Model\Url;
use Exception;

class View extends Action
{
    /**
     * @var 
     */
    protected $url;

    /**
     * @var
     */
    protected $resultRedirectFactory;

    /**
     *
     */
    public function __construct(Context $context, Url $url, RedirectFactory $resultRedirectFactory)
    {
        parent::__construct($context);

        $this->url = $url;
        $this->resultRedirectFactory = $resultRedirectFactory;
    }

    /**
     *
     */
    public function execute()
    {
        if (!($requestUri = trim($this->getRequest()->getParam('request_uri')))) {
            throw new Exception('Request URI not set so cannot redirect to WordPress.');
        }

        $redirectUrl = $this->url->getSiteurl($requestUri);

        return $this->resultRedirectFactory->create()->setUrl($redirectUrl)->setHttpResponseCode(301);
    }
}
