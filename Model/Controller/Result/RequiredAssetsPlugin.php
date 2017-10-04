<?php
/**
 *
**/
namespace FishPig\WordPress\Model\Controller\Result;

use \FishPig\WordPress\Helper\AssetInjectorFactory;
use \FishPig\WordPress\Helper\AssetInjector;
use \Magento\Framework\Controller\ResultInterface;
use \Magento\Framework\App\Response\Http as ResponseHttp;
use Magento\Framework\App\ResponseInterface;

class RequiredAssetsPlugin
{
	/*
	 *
	 * @var \FishPig\WordPress\Helper\AssetInjectorFactory
	 *
	 */
	protected $assetInjectorFactory;
	
	/*
	 * This is required for Magento 2.1.9 and lower as 2.1.9 doesn't pass
	 * method arguments to 'after' plugins. THis is fixed in 2.2.0
	 *
	 * @var \Magento\Framework\App\ResponseInterface
	 *
	 */
	protected $response;

	/*
	 *
	 * @param \FishPig\WordPress\Helper\AssetInjectorFactory
	 *
	 */
	public function __construct(AssetInjectorFactory $assetInjectorFactory, ResponseHttp $response)
	{
		$this->response = $response;
		$this->assetInjectorFactory = $assetInjectorFactory;
	}
	
	/*
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
		
		if ($newBodyHtml = $this->assetInjectorFactory->create()->process($response->getBody())) {
			$response->setBody($newBodyHtml);
		}
		
		return $result;
	}
}
