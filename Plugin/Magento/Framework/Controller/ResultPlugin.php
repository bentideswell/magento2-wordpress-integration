<?php
/**
 *
 */
namespace FishPig\WordPress\Plugin\Magento\Framework\Controller;

use FishPig\WordPress\Model\AssetInjectorFactory;
use FishPig\WordPress\Model\AssetInjector;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\App\Response\Http as ResponseHttp;
use Magento\Framework\App\ResponseInterface;

class ResultPlugin
{
    /**
     *
     * @var \FishPig\WordPress\Model\AssetInjectorFactory
     *
     */
    protected $assetInjectorFactory;

    /**
     * This is required for Magento 2.1.9 and lower as 2.1.9 doesn't pass
     * method arguments to 'after' plugins. THis is fixed in 2.2.0
     *
     * @var \Magento\Framework\App\ResponseInterface
     *
     */
    protected $response;

    /**
     *
     * @param \FishPig\WordPress\Model\AssetInjectorFactory
     *
     */
    public function __construct(AssetInjectorFactory $assetInjectorFactory, ResponseHttp $response)
    {
        $this->response = $response;
        $this->assetInjectorFactory = $assetInjectorFactory;
    }

    /**
     * Inject any required assets into the response body
     *
     * @param  \Magento\Framework\Controller\ResultInterface $subject
     * @param  \Magento\Framework\Controller\ResultInterface $result
     * @param  \Magento\Framework\App\Response\Http $respnse
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function afterRenderResult(ResultInterface $subject, ResultInterface $result, ResponseInterface $response = null)
    {
        // If Magento 2.1.9 or lower, $response won't be passed so load it separately
        if (!$response) {
            $response = $this->response;
        }

        $bodyHtml = $this->transformHtml($response->getBody());

        /**
         * This is usually defined in the AssetInjector but moving it here stops the AssetInjector from being created so often
         */
        if (AssetInjector::isAbspathDefined()) {
            if ($newBodyHtml = $this->assetInjectorFactory->create()->process($bodyHtml)) {
                $bodyHtml = $newBodyHtml;
            }
        }

        $response->setBody($bodyHtml);

        return $result;
    }

    /**
     *
     */
    public function transformHtml($html)
    {
        return $html;
    }
}
