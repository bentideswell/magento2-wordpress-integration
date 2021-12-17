<?php
/**
 *
 */
namespace FishPig\WordPress\Plugin\Magento\Framework\App\PageCache;

class IdentifierPlugin
{
    /**
     *
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \FishPig\WordPress\Model\Post\PasswordManager $postPasswordManager,
        \Magento\Framework\Registry $registry
    ) {
        $this->request = $request;
        $this->postPasswordManager = $postPasswordManager;
        $this->registry = $registry;
    }
    
    /**
     *
     */
    public function afterGetValue(\Magento\Framework\App\PageCache\Identifier $subject, $value)
    {
        if ($pass = $this->postPasswordManager->getPostPassword()) {
            $value = sha1($value . $pass);
        }

        return $value;
    }
}
