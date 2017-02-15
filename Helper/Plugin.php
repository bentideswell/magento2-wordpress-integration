<?php
/**
 * @category Fishpig
 * @package Fishpig_Wordpress
 * @license http://fishpig.co.uk/license.txt
 * @author Ben Tideswell <ben@fishpig.co.uk>
 */
namespace FishPig\WordPress\Helper;

use \Magento\Framework\App\Helper\Context;
use \FishPig\WordPress\Model\App;
use \FishPig\WordPress\Model\App\ResourceConnection;
use \FishPig\WordPress\Model\Config;

class Plugin extends \Magento\Framework\App\Helper\AbstractHelper
{
	public function __construct(Context $context, App $app, ResourceConnection $wpResource, Config $config)
	{
		$this->_app = $app;
		$this->_wpResource = $wpResource;
		$this->_config = $config;
		
		parent::__construct($context);
	}
	
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
		
		if ($db = $this->_wpResource->getConnection()) {
			if ($plugins = $this->_config->getOption('active_plugins')) {
				$db->update(
					$this->_wpResource->getTable('wordpress_option'),
					array('option_value' => serialize(array_merge(unserialize($plugins), array($plugin)))),
					$db->quoteInto('option_name=?', 'active_plugins')
				);
			}
			else {
				$db->insert(
					$this->_wpResource->getTable('wordpress_option'),
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

		if ($plugins = $this->_config->getOption('active_plugins')) {
			$plugins = unserialize($plugins);
		}

		if ($this->_app->isMultisite()) {
			if ($networkPlugins = $this->_config->getSiteOption('active_sitewide_plugins')) {
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
		$options = $this->_config->getOption($plugin);
		
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
