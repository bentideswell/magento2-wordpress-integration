<?php
/**
 *
 */
namespace FishPig\WordPress\Model\Post;

use Magento\Framework\Session\SessionManagerInterface;

class Password
{
    /**
     * @const string
     */
    const SESSION_KEY = 'wordpress_post_password';
    
    /**
     * @static string
     */
    static $password = '';
    
    /**
     *
     */
    public function __construct(SessionManagerInterface $coreSession)
    {
        $this->coreSession = $coreSession;
    }
    
    /**
     *
     */
    public function setPassword($pass)
    {
        self::$password = $pass;

        $this->coreSession->setData(self::SESSION_KEY, $pass);
        
        return $this;
    }   

    /**
     *
     */
    public function getPassword()
    {
        if (self::$password) {
            return self::$password;
        }

        return self::$password = $this->coreSession->getData(self::SESSION_KEY);
    } 
}
