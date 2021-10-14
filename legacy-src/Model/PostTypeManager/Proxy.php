<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model\PostTypeManager;

use FishPig\WordPress\Model\PostTypeManager;

class Proxt
{
    /**
     * @var PostTypeManager
     */
    private $proxiedObject = null;

    /**
     * @return false|PostType
     */
    public function getPostType($type = null)
    {
        return $this->getProxiedObject()->getPostType($type);
    }

    /**
     * @return array
     */
    public function getPostTypes(): array
    {
        return $this->getProxiedObject->getPostTypes();
    }
    
    /**
     * @return PostTypeManager
     */
    private function getProxiedObject()
    {
        if ($this->proxiedObject === null) {
            $this->proxiedObject = \Magento\Framework\App\ObjectManager::getInstance()->get(PostTypeManager::class);
        }
        
        return $this->proxiedObject;
    }
}
