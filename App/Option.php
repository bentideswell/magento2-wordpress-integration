<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App;

class Option
{
    /**
     *
     */
    public function __construct(
        \FishPig\WordPress\App\Option\ValueResolver $valueResolver
    ) {
        $this->valueResolver = $valueResolver;
    }
    
    /**
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return $this->valueResolver->resolve()->get($key, $default);
    }
    
    /**
     * @return []
     */
    public function getUnserialized($key): array
    {
        if ($data = $this->get($key)) {
            return unserialize($data, [false]);
        }
        
        return [];
    }
    
    /**
     * @deprecated since version 3
     */
    public function getOption($key, $default = null)
    {
        return $this->get($key, $default);
    }
}
