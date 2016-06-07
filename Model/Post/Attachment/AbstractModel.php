<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

namespace FishPig\WordPress\Model\Post\Attachment;

abstract class AbstractModel extends \FishPig\WordPress\Model\Post
{
	public function _construct()
	{
		$this->setPostType('attachment');

		parent::_construct();
	}
	
	protected function _afterLoad()
	{
		$this->loadSerializedData();
		
		return parent::_afterLoad();
	}
	
	/**
	 * Load the serialized attachment data
	 *
	 */
	public function loadSerializedData()
	{
		if ($this->getId() > 0 && !$this->getIsFullyLoaded()) {
			$this->getResource()->loadSerializedData($this);
		}
	}
	
	public function getMetaValue($key)
	{
		return parent::getMetaValue('_wp_attachment_' . $key);
	}
}
