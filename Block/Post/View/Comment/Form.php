<?php
/**
 *
 */
namespace FishPig\WordPress\Block\Post\View\Comment;

use FishPig\WordPress\Block\AbstractBlock;

class Form extends AbstractBlock
{
    /**
     * @param  \Magento\Framework\View\Element\Template\Context $context,
     * @param  \FishPig\WordPress\Block\Context $wpContext,
     * @param  array $data = []
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \FishPig\WordPress\Block\Context $wpContext,
        \Magento\Customer\Model\Session $customerSession,
        array $data = []
    ) {
        $this->customerSession = $customerSession;

        parent::__construct($context, $wpContext, $data);
    }

    /**
     * Ensure a valid template is set
     *
     * @return $this
     */
    protected function _beforeToHtml()
    {
        if (!$this->getTemplate()) {
            $this->setTemplate('FishPig_WordPress::post/view/comment/form.phtml');
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
        return $this->url->getSiteUrl('wp-comments-post.php');
    }

    /**
     * @return bool
     */
    public function customerMustLogin(): bool
    {
        return $this->optionRepository->get('comment_registration') && !$this->isCustomerLoggedIn();
    }

    /**
     * @return string
     */
    public function getRedirectToUrl()
    {
        return $this->_urlBuilder->getUrl('wordpress/post_comment/submit', ['post_id' => $this->getPost()->getId()]);
    }

    /**
     * Retrieve the link used to log the user in
     * If redirect to dashboard after login is disabled, the user will be redirected back to the blog post
     *
     * @return string
     */
    public function getLoginLink()
    {
        $ref = $this->getPost() ? base64_encode($this->getPost()->getPermalink() . '#respond') : '';

        return $this->getUrl('customer/account/login', ['referer' => $ref]);
    }

    /**
     * Returns true if the user is logged in
     *
     * @return bool
     */
    public function isCustomerLoggedIn()
    {
        return $this->customerSession->isLoggedIn();
    }

    /**
     * Retrieve the current post object
     *
     * @return null|\FishPig\WordPress\Model\Post
     */
    public function getPost()
    {
        return $this->hasPost() ? $this->_getData('post') : $this->registry->registry('wordpress_post');
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
