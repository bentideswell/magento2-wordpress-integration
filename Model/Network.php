<?php
/**
 * @category    FishPig
 * @package     FishPig_WordPress
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */
namespace FishPig\WordPress\Model;

class Network
{
    /**
     * Determine whether the Network is enabled
     *
     * @return bool
     */
    public function isEnabled()
    {
        return false;
    }

    /**
     * @return int
     */
    public function getSiteId()
    {
        return 1;
    }

    /**
     * @return int
     */
    public function getBlogId()
    {
        return 1;
    }

    /**
     * return false
     */
    public function getSiteAndBlogObjects()
    {
        return false;
    }

    /**
     * @return false
     */    
    public function getBlogTableValue($key)
    {        
        return false;
    }

    /**
     * @return false
     */    
    public function getSitePath()
    {        
        return false;
    }

    /**
     * @return false
     */
    public function getNetworkTables()
    {
        return false;
    }
}
