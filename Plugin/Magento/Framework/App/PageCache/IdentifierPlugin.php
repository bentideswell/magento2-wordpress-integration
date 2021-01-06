<?php
/**
 *
 */
namespace FishPig\WordPress\Plugin\Magento\Framework\App\PageCache;

use Magento\Framework\App\PageCache\Identifier;
use Magento\Framework\App\RequestInterface;
use FishPig\WordPress\Model\Post\Password as PostPassword;

class IdentifierPlugin
{
    /**
     *
     */
    public function __construct(RequestInterface $request, PostPassword $postPassword)
    {
        $this->request = $request;
        $this->postPassword = $postPassword;
    }
    
    /**
     *
     */
    public function afterGetValue(Identifier $subject, $value)
    {
        if ($pass = $this->postPassword->getPassword()) {
            $value = sha1($value . $pass);
        }

        return $value;
    }
}
