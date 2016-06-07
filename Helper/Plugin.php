<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

namespace \FishPig\WordPress\Helper;

class Plugin extends AbstractHelper
{
	/**
	 * Install a plugin
	 * 
	 * @param string $target
	 * @param string $source
	 * @param bool $enable
	 * @return bool
	 */
	public function install($target, $source, $enable = false)
	{
		if (!is_file($source)) {
			return false;
		}

		$sourceData = @file_get_contents($source);
		
		if (!$sourceData) {
			return false;
		}
		
		@mkdir(dirname($target));
		
		if ((is_file($target) && is_writable($target)) || (!is_file($target) && is_writable(dirname($target)))) {
			@file_put_contents($target, $sourceData);

			if (is_file($target)) {
				return $enable
					? $this->enable(substr($target, strpos($target, 'wp-content/plugins/')+strlen('wp-content/plugins/')))
					: true;
			}
		}

		return false;
	}
	
	/**
	 * Enable a plugin
	 *
	 * @param string $plugin
	 * @return bool
	 */
	public function enable($plugin)
	{
		if ($this->isEnabled($plugin)) {
			return true;
		}
		
		if ($db = Mage::helper('wordpress/app')->getDbConnection()) {
			if ($plugins = $this->getWpOption('active_plugins')) {
				$db->update(
					Mage::getSingleton('core/resource')->getTableName('wordpress/option'),
					array('option_value' => serialize(array_merge(unserialize($plugins), array($plugin)))),
					$db->quoteInto('option_name=?', 'active_plugins')
				);
			}
			else {
				$db->insert(
					Mage::getSingleton('core/resource')->getTableName('wordpress/option'),
					array(
						'option_name' => 'active_plugins',
						'option_value' => serialize(array($plugin))
					)
				);
			}
			
			return true;			
		}
		
		return false;
	}
	
	/**
	 * Determine whether a WordPress plugin is enabled in the WP admin
	 *
	 * @param string $name
	 * @param bool $format
	 * @return bool
	 */
	public function isEnabled($name)
	{
		$plugins = array();

		if ($plugins = $this->getApp()->getOption('active_plugins')) {
			$plugins = unserialize($plugins);
		}
		
		if ($this->getApp()->isWordPressMU() && Mage::helper('wpmultisite')->canRun()) {
			if ($networkPlugins = Mage::helper('wpmultisite')->getWpSiteOption('active_sitewide_plugins')) {
				$plugins += (array)unserialize($networkPlugins);
			}
		}

		if ($plugins) {
			foreach($plugins as $a => $b) {
				if (strpos($a . '-' . $b, $name) !== false) {
					return true;
				}
			}
		}

		return false;
	}
	
	/**
	 * Retrieve a plugin option
	 *
	 * @param string $plugin
	 * @param string $key = null
	 * @return mixed
	 */
	public function getOption($plugin, $key = null)
	{
		$options = $this->getApp()->getWpOption($plugin);
		
		if (($data = @unserialize($options)) !== false) {
			if (is_null($key)) {
				return $data;
			}

			return isset($data[$key])
				? $data[$key]
				: null;
		}
		
		return $options;
	}
}
