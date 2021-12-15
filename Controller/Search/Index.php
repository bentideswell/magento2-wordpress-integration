<?php
/**
 *
 */
namespace FishPig\WordPress\Controller\Search;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use FishPig\WordPress\Model\SearchFactory;
use FishPig\WordPress\Model\UrlInterface as WpUrl;
use Magento\Framework\Controller\ResultFactory;

class Index extends Action
{
    /**
     * @var SearchFactory
     */
    protected $searchFactory;

    /**
     * @param Context     $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(Context $context, SearchFactory $searchFactory, WpUrl $wpUrl)
    {
        parent::__construct($context);

        $this->searchFactory = $searchFactory;
        $this->wpUrl = $wpUrl;
    }

    /**
     * @return
     */
    public function execute()
    {
        if (!($redirectUrl = $this->searchFactory->create()->getUrl())) {
            $redirectUrl = $this->wpUrl->getHomeUrl();
        }

        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)
            ->setUrl($redirectUrl);
    }
}
