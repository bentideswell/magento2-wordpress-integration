<?php
/**
 *
 */
namespace FishPig\WordPress\Controller\Post;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use FishPig\WordPress\Model\Post\Password as PostPassword;
class Pass extends Action
{
    /**
     *
     */
    public function __construct(Context $context, PostPassword $postPassword)
    {
        parent::__construct($context);
        
        $this->postPassword = $postPassword;
    }

    /**
     *
     */
    public function execute()
    {
        $this->postPassword->setPassword(trim($this->_request->getPost('post_password')));

        $redirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
        
		if ($redirectTo = $this->_request->getPost('redirect_to')) {
            $redirect->setUrl($redirectTo);
		} else {
            $redirect->setUrl('/');
		}

        return $redirect;
    }
}
