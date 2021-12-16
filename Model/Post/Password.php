<?php
/**
 *
 */
namespace FishPig\WordPress\Model\Post;

/* ToDo: check */
class Password
{
    /**
     *
     */
    public function __construct(\FishPig\WordPress\Model\OptionRepository $optionRepository)
    {
        $this->optionRepository = $optionRepository;
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
//        if (!($siteUrl = $this->optionRepository->getSiteOption('siteurl'))) {
            $siteUrl = $this->optionRepository->get('siteurl');
//        }
        
        return 'wp-postpass_' . md5($siteUrl);
    }
}
