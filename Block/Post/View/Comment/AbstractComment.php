<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

namespace FishPig\WordPress\Block\Post\View\Comment;

abstract class AbstractComment extends \FishPig\WordPress\Block\Post
{
	/**
	 * Returns a collection of comments for the current post
	 *
	 * @return Fishpig_Wordpress_Model_Resource_Post_Comments_Collection
	 */
	public function getComments()
	{
		if ($this->hasComments()) {
			return $this->_getData('comments');
		}

		$this->setComments(array());
			
		if ($this->getCommentCount() > 0 && ($post = $this->getPost()) !== false) {
			$this->setComments($post->getResource()->getPostComments($post));

			if (($pager = $this->getChildBlock('pager')) !== false) {
				$this->_getData('comments')->setPageSize($pager->getLimit());
			}
		}
		
		return $this->_getData('comments');
	}

	/**
	 * Retrieve the amount of comments for the current post
	 *
	 * @return int
	 */
	public function getCommentCount()
	{
		return $this->getPost() ? $this->getPost()->getCommentCount() : 0;
	}
	
	/**
	 * Determine whether comments are enabled
	 *
	 * @return bool
	 */
	public function isCommentsEnabled()
	{
		return $this->getPost() && $this->getPost()->getCommentStatus() !== 'closed';
	}
		
	/**
	 * Get the comment content
	 * Filter out certain HTML tags
	 *
	 * @param \FishPig\WordPress\Model\Post\Comment $comment
	 * @return string
	 */
	public function getCommentContent(\FishPig\WordPress\Model\Post\Comment $comment)
	{
		$content = strip_tags(trim($comment->getCommentContent()), $this->getAllowedHtmlTags());
		
		return $this->canConvertNewLines()
			? nl2br($content)
			: $content;
	}
	
	
	public function getCommentsFormHtml()
	{
		return $this->getFormHtml();
	}
	
	/**
	 * Returns the HTML for the comment form
	 *
	 * @return string
	 */
	public function getFormHtml()
	{
		if ($this->isCommentsEnabled()) {
			return $this->getChildHtml('form');
		}
		
		return '';
	}
	
	/**
	 * Get the HTML for the pager block
	 *
	 * @return null|string
	 */
	public function getPagerHtml()
	{
		if ($this->_config->getOption('page_comments', false)) {
			return $this->getChildHtml('pager');
		}
	}
	
	/**
	 * Retrieve the allowed HTML tags as a string
	 *
	  * @return string
	  */
	public function getAllowedHtmlTags()
	{
		if (!$this->hasAllowedHtmlTags()) {
			return '<a><abbr><acronym><b><blockquote><cite><code><del><em><i><q><strike><strong>';
		}
		
		return $this->_getData('allowed_html_tags');
	}
	
	/**
	 * Determine whether to convert new lines to <br /> tags
	 * To disable this feature, call self::setConvertNewLines(false)
	 *
	 * @return bool
	 */
	public function canConvertNewLines()
	{
		return !$this->hasConvertNewLines() || (int)$this->getConvertNewLines() !== false;
	}
}
