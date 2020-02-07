<?php
/**
 *
 */
namespace FishPig\WordPress\Api\Data\Plugin;

interface SeoInterface
{    
    /**
     *
     *
     * @return  string
     */
    public function aroundGetPageTitle($post, $callback);

    /**
     *
     *
     * @return  string
     */    
    public function aroundGetMetaDescription($post, $callback);

    /**
     *
     *
     * @return  string
     */
    public function aroundGetMetaKeywords($post, $callback);

    /**
     *
     *
     * @return  string
     */
    public function aroundGetRobots($post, $callback);

    /**
     *
     *
     * @return  string
     */
    public function aroundGetCanonicalUrl($post, $callback);
}
