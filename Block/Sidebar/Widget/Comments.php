<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

namespace FishPig\WordPress\Block\Sidebar\Widget;

class Comments extends AbstractWidget
{
	/**
	 * Retrieve the recent comments collection
	 *
	 * @return Fishpig_Wordpress_Model_Mysql4_Post_Comment_Collection
	 */
	public function getComments()
	{
		if (!$this->hasComments()) {
			$comments = $this->_factory->getFactory('Post\Comment')->create()->getCollection()
				->addCommentApprovedFilter()
				->addOrderByDate('desc');
			
			$comments->getSelect()->limit($this->getNumber() ? $this->getNumber() : 5 );
			
			$this->setComments($comments);
		}
		
		return $this->getData('comments');
	}
	
	/**
	 * Retrieve the default title
	 *
	 * @return string
	 */
	public function getDefaultTitle()
	{
		return __('Recent Comments');
	}
	
	/**
	 * Ensure template is set
	 *
	 * @return string
	 */
	protected function _beforeToHtml()
	{
		if (!$this->getTemplate()) {
			$this->setTemplate('sidebar/widget/comments.phtml');
		}
		
		return parent::_beforeToHtml();
	}
}
