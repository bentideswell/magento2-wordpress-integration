<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */
 
namespace FishPig\WordPress\Model\ResourceModel;

class Setup extends \FishPig\WordPress\Model\ResourceModel\AbstractResource
{

	/**
	 * If Legacy add-on extension installed
	 * Apply legacy hacks
	 *
	 * @param string $resourceName
	 * @return void
	 */
	public function __construct($resourceName)
	{
		if (Mage::helper('wordpress')->isLegacy()) {
			if ($helper = Mage::helper('wp_addon_legacy')) {
				$helper->applyLegacyHacks();
			}
		}
		
		parent::__construct($resourceName);
	}
}
