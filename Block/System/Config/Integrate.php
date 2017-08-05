<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace FishPig\WordPress\Block\System\Config;

use \Magento\Backend\Block\Template\Context;
use \FishPig\WordPress\Model\AppFactory;
use \FishPig\WordPress\Model\App\Url as WpUrlBuilder;
use \Magento\Store\Model\StoreManager;
use \Magento\Store\Model\App\Emulation;
use \Magento\Framework\Module\Manager as ModuleManager;
use \FishPig\WordPress\Helper\Plugin as PluginHelper;

class Integrate extends \Magento\Backend\Block\Template
{
	/*
	 *
	 * @const string
	 *
	 */
	const YOAST_SEO_PLUGIN_URL = 'https://wordpress.org/plugins/wordpress-seo/';
	
	/*
	 *
	 * @const string
	 *
	 */
	const YOAST_SEO_MODULE_URL = 'https://github.com/bentideswell/magento2-wordpress-integration-yoastseo';

	/*
	 *
	 * @var \FishPig\WordPress\Model\App
	 *
	 */
	protected $app = null;
	
	/*
	 *
	 * @var \FishPig\WordPress\Model\App\Url
	 *
	 */
	protected $wpUrlBuilder = null;
	
	/*
	 *
	 * @var \Magento\Store\Model\StoreManager
	 *
	 */
	protected $storeManager = null;
	
	/*
	 *
	 * @var \Magento\Store\Model\App\Emulation
	 *
	 */
	protected $emulator = null;

	/*
	 *
	 * @var \FishPig\WordPress\Helper\Plugin
	 *
	 */
	protected $pluginHelper = null;

	/*
	 *
	 * @var \Magento\Framework\Module\Manager
	 *
	 */
	protected $moduleManager = null;

	/*
	 *
	 * 
	 *
	 */
  public function __construct(Context $context, AppFactory $appFactory, WpUrlBuilder $urlBuilder, StoreManager $storeManager, Emulation $emulator, ModuleManager $moduleManager, PluginHelper $pluginHelper, array $data = [])
  {
		parent::__construct($context, $data);

		$this->wpUrlBuilder = $urlBuilder;	   
		$this->storeManager = $storeManager; 
		$this->emulator = $emulator;
		$this->moduleManager = $moduleManager;
		$this->pluginHelper = $pluginHelper;
		
		if ($this->_request->getParam('section') === 'wordpress') {
			try {
				$storeId = 0;

				if (($websiteId = (int)$this->_request->getParam('website')) !== 0) {
					$storeId = (int)$this->storeManager->getWebsite($websiteId)->getDefaultStore()->getId();
				}

				if ($storeId === 0) {
					$storeId = (int)$this->_request->getParam('store');
				}

				if ($storeId === 0) {
					$storeId = (int)$this->storeManager->getDefaultStoreView()->getId();
				}

				$this->emulator->startEnvironmentEmulation($storeId);

				$this->app = $appFactory->create()->init();

				$this->emulator->stopEnvironmentEmulation();
			} 
			catch (\Exception $e) {
				$this->emulator->stopEnvironmentEmulation();
			}
		}
	}

	/*
	 *
	 * 
	 *
	 */
	protected function _toHtml()
	{
		if (!$this->app) {
			return '';
		}

		$messages = [];

		if ($exception = $this->app->getException()) {
			if ($exception instanceof \FishPig\WordPress\Model\App\Integration\Exception) {
				$msg = $exception->getFullMessage();
			}
			else {
				$msg = 'Unknown Error: ' . $exception->getMessage();
			}

			$messages[] = $this->_getMessage($msg, 'error');
		}
		else {
			$url = $this->wpUrlBuilder->getUrl();
			$msg = sprintf('WordPress Integration is active. View your blog at <a href="%s" target="_blank">%s</a>.', $url, $url);

			$messages[] = $this->_getMessage($msg);
			
			if ($msg = $this->_getYoastSeoMessage()) {
				$messages[] = $msg;
			}
		}

		return '<div class="messages">' . implode("\n", $messages) . '</div>';
	}
	
	/*
	 *
	 *
	 *
	 */
	protected function _getYoastSeoMessage()
	{
		$yoastPluginEnabled = $this->pluginHelper->isEnabled('wordpress-seo/wp-seo.php');
		$yoastModuleEnabled = $this->moduleManager->isEnabled('FishPig_WordPress_Yoast');

		if (!$yoastPluginEnabled && !$yoastModuleEnabled) {
			return $this->_getMessage(
				sprintf(
					'For the best SEO results, you should install the free <a href="%s" target="_blank">Yoast SEO WordPress plugin</a> and the free <a href="%s" target="_blank">Yoast SEO Magento extension</a>.', 
					self::YOAST_SEO_PLUGIN_URL,
					self::YOAST_SEO_MODULE_URL
				),
				'notice'
			);
		} 
		
		if (!$yoastPluginEnabled) {
			return $this->_getMessage(
				sprintf('For the best SEO results, you should install the free <a href="%s" target="_blank">Yoast SEO WordPress plugin</a>.', 'https://wordpress.org/plugins/wordpress-seo/'),
				'notice'
			);
		}
		
		if (!$yoastModuleEnabled) {
			return $this->_getMessage(
				sprintf(
					'You have installed the Yoast SEO plugin in WordPress. To complete the SEO integration, install the free <a href="%s" target="_blank">Yoast SEO Magento extension</a>.', 
					self::YOAST_SEO_MODULE_URL
				),
				'notice'
			);
		}
	}
	
	/*
	 *
	 * 
	 *
	 */
	protected function _getMessage($msg, $type = 'success')
	{
		return sprintf('<div class="message message-%s %s"><div>%s</div></div>', $type, $type, $msg);
	}

	/*
	 *
	 * 
	 *
	 */
	protected function _prepareLayout()
	{
		return parent::_prepareLayout();
	}
}
