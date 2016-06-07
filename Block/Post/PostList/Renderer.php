<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

namespace FishPig\WordPress\Block\Post\PostList;

class Renderer extends \FishPig\WordPress\Block\Post
{
	/**
	 * Retrieve the correct block to prepare posts
	 *
	 * @return Fishpig_Wordpress_Block_Post_List
	 */
	protected function _getBlockForPostPrepare()
	{
		return $this->getParentBlock();
	}
	
	protected function _toHtml()
	{
		return $this->getChildHtml();
	}
}
