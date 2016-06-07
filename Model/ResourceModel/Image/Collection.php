<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

namespace FishPig\WordPress\Model\ResourceModel\Image;

class Collection extends \FishPig\WordPress\Model\ResourceModel\Post\Attachment\Collection\AbstractCollection
{

	public function _construct()
	{
		parent::_construct();
		
        $this->_init('FishPig\WordPress\Model\Image', 'FishPig\WordPress\Model\ResourceModel\Image');
	}
	
	/**
	 * Load an image
	 * Ensure that only images are returned
	 *
	 * @param bool $printQuery
	 * @param bool $logQuery
	 * @return $this
	 */
    public function load($printQuery = false, $logQuery = false)
    {
		$this->getSelect()->where("post_mime_type LIKE 'image%'");
		
		return parent::load($printQuery, $logQuery);
    }
}
