<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

namespace FishPig\WordPress\Block\Post\View\Comment;

class Form extends \FishPig\WordPress\Block\AbstractBlock
{
	/**
	 * Inject the comments js
	 *
	 * @return $this
	 */
	protected function _prepareLayout()
	{
		if (($head = $this->getLayout()->getBlock('head')) !== false) {
			$head->addJs('fishpig/wordpress/comments.js');
		}

		return parent::_prepareLayout();
	}
	
	/**
	 * Ensure a valid template is set
	 *
	 * @return $this
	 */
	protected function _beforeToHtml()
	{
		if (!$this->getTemplate()) {
			$this->setTemplate('post/view/comment/form.phtml');
		}
		
		return parent::_beforeToHtml();		
	}

	/**
	 * Retrieve the comment form action
	 *
	 * @return string
	 */
	public function getCommentFormAction()
	{
		return $this->_wpUrlBuilder->getSiteUrl('wp-comments-post.php');
	}

	/**
	 * Determine whether the customer needs to login before commenting
	 *
	 * @return bool
	 */
	public function customerMustLogin()
	{
		if ($this->_config->getOption('comment_registration')) {
			return !$this->_config->isLoggedIn();
		}
		
		return false;
	}

	/**
	 * Retrieve the link used to log the user in
	 * If redirect to dashboard after login is disabled, the user will be redirected back to the blog post
	 *
	 * @return string
	 */
	public function getLoginLink()
	{
		return $this->getUrl('customer/account/login', array(
			'referer' => base64_encode($this->getPost()->getPermalink() . '#respond'),
		));
	}

	/**
	 * Returns true if the user is logged in
	 *
	 * @return bool
	 */
	public function isCustomerLoggedIn()
	{
		return $this->_config->isLoggedIn();
	}
	
	/**
	 * Retrieve the current post object
	 *
	 * @return null|\FishPig\WordPress\Model\Post
	 */
	public function getPost()
	{
		return $this->hasPost() ? $this->_getData('post') : $this->_registry->registry('wordpress_post');
	}
	
	/**
	 * Returns the ID of the currently loaded post
	 *
	 * @return int|false
	 */
	public function getPostId()
	{
		return $this->getPost() ? $this->getPost()->getId() : false;
	}
}
