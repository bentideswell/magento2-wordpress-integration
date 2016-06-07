<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

namespace FishPig\WordPress\Model\ResourceModel;

class Image extends \FishPig\WordPress\Model\ResourceModel\Post\Attachment\AbstractResource
{
	public function isImagePostName($postName)
	{
		$select = $this->_getReadAdapter()
			->select()
			->from($this->getMainTable(), 'ID')
			->where('post_type=?', 'attachment')
			->where('post_name=?', $postName)
			->limit(1);
			
		return $this->_getReadAdapter()->fetchOne($select);
	}
}
