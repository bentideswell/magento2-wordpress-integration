<?php
/*
 * @category FishPig
 * @package FishPig_WordPress
 * @author Ben Tideswell
 * @Obfuscate
 */

namespace FishPig\WordPress\Model\Shortcode;

use FishPig\WordPress\Model\App;
use Magento\Framework\View\Element\Context;
use FishPig\WordPress\Helper\Core;
use FishPig\WordPress\Api\Data\ShortcodeInterface;

abstract class AbstractShortcode implements ShortcodeInterface
{
	/*
	 * @var \FishPig\WordPress\Helper\Core
	 */
	protected $coreHelper;
	
	/*
	 * @var array
	 */
	protected $inlineJs = array();
	
	/*
	 * Determine whether the plugin in WordPress is enabled and core is active
	 *
	 * @return bool
	 */
	public function isEnabled()
	{
		return $this->isPluginEnabled() && $this->_getCoreHelper()->isActive();
	}

	/*
	 * Determine whether the plugin in WordPress is enabled
	 *
	 * @return bool
	 */
	public function isPluginEnabled()
	{
		if (!$this->pluginName) {
			return true;
		}

		return \Magento\Framework\App\ObjectManager::getInstance()
			->get('\FishPig\WordPress\Helper\Plugin')
				->isEnabled($this->pluginName);
	}
	
	/*
	 * Get the core helper
	 *
	 * @return \FishPig\WordPress\Helper\Core
	 */
	protected function _getCoreHelper()
	{
		if (is_null($this->coreHelper)) {
			$this->coreHelper = \Magento\Framework\App\ObjectManager::getInstance()->get('\FishPig\WordPress\Helper\Core');
		}
		
		return $this->coreHelper;
	}
	
	/*
	 *
	 *
	 * @return string
	 */
  protected function _getHtml()
  {
	  return $this->_getCoreHelper()->getHtml();
  }

	/*
	 *
	 *
	 * @return bool
	 */
	public function requiresAssetInjection()
	{
		return true;
	}

	/*
	 *
	 *
	 * @return array
	 */
	public function getInlineJs()
	{
		return $this->_cleanAssetArray($this->inlineJs);
	}
	
	/*
	 * Clean the array of assets
	 *
	 * @param array $assets
	 * @return array|false
	 */
	protected function _cleanAssetArray($assets)
	{
		if (!is_array($assets)) {
			return $assets;
		}

		$buffer = [];
		
		foreach($assets as $asset) {
			if (is_array($asset)) {
				foreach($this->_cleanAssetArray($asset) as $line) {
					$buffer[] = $line;
				}
			}
			else if (trim($asset)) {
				$buffer[] = trim($asset);
			}
		}
		
		return $buffer;
	}
}
