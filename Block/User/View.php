<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Block\User;

class View extends \FishPig\WordPress\Block\Post\PostList\Wrapper\AbstractWrapper
{
    /**
     * @var \FishPig\WordPress\Model\User
     */
    private $user = null;
    
    /**
     * @return \FishPig\WordPress\Model\User|false
     */
    public function getUser()
    {
        if ($this->user === null) {
            $this->user = $this->registry->registry(\FishPig\WordPress\Model\User::ENTITY) ?? false;
        }
        
        return $this->user;
    }
    
    /**
     * @param  \FishPig\WordPress\Model\User $user
     * @return self
     */
    public function setUser(\FishPig\WordPress\Model\User $user): self
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return \FishPig\WordPress\Model\ResourceModel\Post\Collection
     */
    protected function getBasePostCollection(): \FishPig\WordPress\Model\ResourceModel\Post\Collection
    {
        return $this->getUser()->getPostCollection();
    }
    
    /**
     * @deprecated 3.0 use self::getUser
     */
    public function getEntity()
    {
        return $this->getUser();
    }
}
