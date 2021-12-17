<?php
/**
 *
 */
namespace FishPig\WordPress\Model\Post;

/* ToDo: check */
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
        \FishPig\WordPress\Model\Session $session,
        \FishPig\WordPress\Model\UrlInterface $url,
        \Magento\Framework\App\DeploymentConfig $deploymentConfig
    ) {
        $this->session = $session;
        $this->url = $url;
        $this->deploymentConfig = $deploymentConfig;
    }

    public function isPostUnlocked(\FishPig\WordPress\Model\Post $post): bool
    {
        if (!($postPassword = $post->getPostPassword())) {
            return true;
        }
        
        $submittedHash = $this->session->getData($this->getSessionKey($post));
        $expectedHash = $this->hashPassword($postPassword);

        return $submittedHash === $expectedHash;
    }
    
    /**
     * @param  \FishPig\WordPress\Model\Post $post
     * @param  ?string $password = null
     * @return void
     */
    public function setPostPassword(\FishPig\WordPress\Model\Post $post, ?string $password = null): void
    {
        if ($password === null) {
            $this->session->unsetData($this->getSessionKey($post));
        } else {
            $this->session->setData(
                $this->getSessionKey($post), 
                $this->hashPassword($password)
            );
        }
    }
    
    /**
     * @param  \FishPig\WordPress\Model\Post $post
     * @return string
     */
    private function getSessionKey(\FishPig\WordPress\Model\Post $post): string
    {
        return self::PASSWORD_STORE_KEY_PREFIX . $post->getId();
    }
    
    /**
     * @param  string $password
     * @return string
     */
    private function hashPassword(string $password): string
    {
        return md5(
            $this->deploymentConfig->get('crypt/key') . $this->url->getSiteUrl() . $password
        ); // md5() here is not for cryptographic use.
    }
}
