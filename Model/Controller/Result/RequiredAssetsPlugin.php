<?php
/**
 *
**/
namespace FishPig\WordPress\Model\Controller\Result;

use \FishPig\WordPress\Helper\AssetInjectorFactory;
use \FishPig\WordPress\Helper\AssetInjector;
use \Magento\Framework\Controller\ResultInterface;
use \Magento\Framework\App\Response\Http as ResponseHttp;

class RequiredAssetsPlugin
{
	/*
	 *
	 * @var \FishPig\WordPress\Helper\AssetInjectorFactory
	 *
	 */
	protected $assetInjectorFactory;

	/*
	 *
	 * @param \FishPig\WordPress\Helper\AssetInjectorFactory
	 *
	 */
	public function __construct(AssetInjectorFactory $assetInjectorFactory)
	{
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
	public function afterRenderResult(ResultInterface $subject, ResultInterface $result, ResponseHttp $response)
	{
		if ($newBodyHtml = $this->assetInjectorFactory->create()->process($response->getBody())) {
			$response->setBody($newBodyHtml);
		}
		
		return $result;
	}
}
