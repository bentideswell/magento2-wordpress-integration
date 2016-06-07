<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

abstract class Fishpig_Wordpress_Helper_Plugin_Abstract extends Varien_Object
{
	/**
	 * Store for the plugins options
	 *
	 */
	protected $_options = null;
	
	/**
	 * Prefix for options field in options table
	 *
	 * @var string|null
	 */
	protected $_optionsFieldPrefix = null;
	
	/**
	 * Postfix for options field in options table
	 *
	 * @var string|null
	 */
	protected $_optionsFieldPostfix = '_options';
	
	/**
	 * Prefix for options value keys
	 *
	 * @var string
	 */
	protected $_optionsValuePrefix = '';
	
	/**
	 * Flag determine whther to fix option keys
	 *
	 * @var bool
	 */
	protected $_fixOptionKeys = true;
	
	/**
	 * Automatically load the plugin options
	 *
	 */
	protected function _construct()
	{
		if (!is_null($this->_optionsFieldPrefix)) {
			$options = Mage::helper('wordpress')->getWpOption($this->_optionsFieldPrefix . $this->_optionsFieldPostfix);
			
			if ($options) {
				$options = unserialize($options);
				
				foreach($options as $key => $value) {
					if ($this->_fixOptionKeys === true) {
						$key = trim(str_replace($this->_optionsValuePrefix, '', $key), '-_ ');
						$key = @preg_replace('/([A-Z]{1})([A-Z]{1,})/e', "$1 . strtolower($2);", $key);
						$key = @preg_replace('/([A-Z]{1})/e', "'_' . strtolower($1);", $key);
					}
					
					if (is_array($value) || trim($value) !== '') {
						$this->setData($key, $value);
					}
				}
			}
		}
	}
}
