<?php
/**
 *
 */
namespace FishPig\WordPress\Model\Post;

use FishPig\WordPress\Model\OptionManager;

class Password
{
    /**
     *
     */
    public function __construct(OptionManager $optionManager)
    {
        $this->optionManager = $optionManager;
    }

    /**
     * @return string|false
     */
    public function getPassword()
    {
        $cookieName = $this->getCookieName();
        
        return isset($_COOKIE[$cookieName]) ? $_COOKIE[$cookieName] : false;
    }
    
    /**
     * @return bool
     */
    public function doesPasswordMatch($pass) : bool
    {
        $hasher = new PasswordHash(8, true);

        return $hasher->CheckPassword($pass, $this->getPassword());
    }
    
    /**
     * @return string
     */
    private function getCookieName() : string
    {
        if (!($siteUrl = $this->optionManager->getSiteOption('siteurl'))) {
            $siteUrl = $this->optionManager->getOption('siteurl');
        }
        
        return 'wp-postpass_' . md5($siteUrl);
    }
}
