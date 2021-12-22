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
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
    ) {
        $this->url = $url;
        $this->deploymentConfig = $deploymentConfig;
        $this->storeManager = $storeManager;
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
    }

    public function isPostUnlocked(\FishPig\WordPress\Model\Post $post): bool
    {
        if (!($postPassword = $post->getPostPassword())) {
            return true;
        }

        return $this->getPostPassword() === $this->hashPassword($postPassword);
    }
    
    /**
     * @param  ?string $password = null
     * @return void
     */
    public function setPostPassword(?string $password = null): void
    {
        if ($password === null) {
            $this->cookieManager->deleteCookie($this->getSessionKey());
        } else {
            $this->cookieManager->setPublicCookie(
                $this->getSessionKey(),
                $this->hashPassword($password),
                $this->cookieMetadataFactory->createPublicCookieMetadata()
                    ->setDurationOneYear()
                    ->setPath('/')
                    ->setHttpOnly(true)
            );
        }
    }
    
    /**
     * @param  \FishPig\WordPress\Model\Post $post
     * @return string|false
     */
    public function getPostPassword()
    {
        return $this->cookieManager->getCookie($this->getSessionKey());
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
        // phpcs:ignore -- not cryptographic
        return md5(
            $this->deploymentConfig->get('crypt/key') . $this->url->getSiteUrl() . $password
        );
    }
}
