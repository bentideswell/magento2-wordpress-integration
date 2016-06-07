<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

class Fishpig_Wordpress_Block_Sidebar_Widget_Image extends Fishpig_Wordpress_Block_Sidebar_Widget_Abstract
{
	/**
	 * Set the posts collection
	 *
	 */
	protected function _beforeToHtml()
	{
		parent::_beforeToHtml();

		if (!$this->getTemplate()) {
			$this->setTemplate('wordpress/sidebar/widget/image.phtml');
		}

		return $this;
	}
	
	public function getImage()
	{
		$this->setImage(false);
		
		if ($this->getAttachmentId()) {
			$image = Mage::getModel('wordpress/image')->load($this->getAttachmentId());
			
			if ($image->getId()) {
				$this->setImage($image);
			}
		}
		
		return $this->_getData('image');
	}
	
	public function getImageUrl()
	{
		if ($image = $this->getImage()) {
			if ($imageUrl = $image->getImageByType($this->getSize())) {
				return $imageUrl;
			}
			
			return $image->getFullSizeImage();
		}
		
		return false;
	}
	
	public function getLink()
	{
		return ($link = $this->_getData('link'))
			? $link
			: '#';
	}
	
	public function getDefaultTitle()
	{
		return null;
	}
}
