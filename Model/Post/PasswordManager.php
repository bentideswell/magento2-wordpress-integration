<?php
/**
 *
 */
namespace FishPig\WordPress\Model\Post;

class PasswordManager
{
    /**
     * @const string
     */
    const PASSWORD_STORE_KEY_PREFIX = 'post_password_';

    /**
     *
     */
    public function __construct(
        \FishPig\WordPress\Model\UrlInterface $url,
        \Magento\Framework\App\DeploymentConfig $deploymentConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->url = $url;
        $this->deploymentConfig = $deploymentConfig;
        $this->storeManager = $storeManager;
    }

    public function isPostUnlocked(\FishPig\WordPress\Model\Post $post): bool
    {
        if (!($postPassword = $post->getPostPassword())) {
            return true;
        }

        $submittedHash = $this->getPostPassword();
        $expectedHash = $this->hashPassword($postPassword);

        return $submittedHash === $expectedHash;
    }
    
    /**
     * @param  ?string $password = null
     * @return void
     */
    public function setPostPassword(?string $password = null): void
    {
        $sessionKey = $this->getSessionKey();
        if ($password === null) {
            unset($_COOKIE[$sessionKey]);
        } else {
            $_COOKIE[$sessionKey] = $this->hashPassword($password);
        }
    }
    
    /**
     * @param  \FishPig\WordPress\Model\Post $post
     * @return string|false
     */
    public function getPostPassword()
    {
        $sessionKey = $this->getSessionKey();
        return !empty($_COOKIE[$sessionKey]) ? $_COOKIE[$sessionKey] : false;
    }
    
    /**
     * @param  \FishPig\WordPress\Model\Post $post
     * @return string
     */
    private function getSessionKey(): string
    {
        return self::PASSWORD_STORE_KEY_PREFIX . $this->storeManager->getStore()->getId();
    }
    
    /**
     * @param  string $password
     * @return string
     */
    private function hashPassword(string $password): string
    {
        return md5( // phpcs:ignore
            $this->deploymentConfig->get('crypt/key') . $this->url->getSiteUrl() . $password
        ); // md5() here is not for cryptographic use.
    }
}
