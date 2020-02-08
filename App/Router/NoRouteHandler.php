<?php
/**
 *
 */
namespace FishPig\WordPress\App\Router;

use Magento\Framework\App\Response\Http as Response;
use FishPig\WordPress\Model\OptionManager;
use Magento\Framework\App\RequestInterface;

class NoRouteHandler implements \Magento\Framework\App\Router\NoRouteHandlerInterface
{
    /**
     * @var Response
     */
    protected $response;

    /**
     * @var OptionManager
     */
    protected $optionManager;

    /**
     * @param Response $response
     * @param OptionManager $optionManager
     */
    public function __construct(Response $response, OptionManager $optionManager)
    {
        $this->response = $response;
        $this->optionManager = $optionManager;
    }

    /**
     * @param RequestInterface $request
     * @return bool
     */
    public function process(RequestInterface $request)
    {
        if (!($pageId = (int)$this->optionManager->getOption('custom_404_page_id'))) {
            return false;
        }

        $this->response->setHttpResponseCode(404);

        $request->setModuleName('wordpress')
            ->setControllerName('post')
            ->setActionName('view')
            ->setParam('id', $pageId);

        return true;
    }
}
