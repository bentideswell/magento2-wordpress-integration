<?php
/**
 *
 */

namespace FishPig\WordPress\Api\Data\Entity;

/**
 * Interface for all entities in the integration that are viewable on the frontend
 * By viewable, it means that the entity has it's own page (eg. posts, categories, tags, users etc)
 */
interface ViewableInterface
{
    /**
     * Get the item's name (for a post, this is the post title)
     *
     * @return  string
     */
    public function getName();

    /**
     *
     *
     * @return  string
     */
    public function getUrl();

    /**
     *
     *
     * @return  string
     */
    public function getContent();

    /**
     *
     *
     * @return \FishPig\WordPress\Model\Image
     */
    public function getImage();

    /**
     *
     *
     * @return  string
     */
    public function getPageTitle();

    /**
     *
     *
     * @return  string
     */    
    public function getMetaDescription();

    /**
     *
     *
     * @return  string
     */
    public function getMetaKeywords();

    /**
     *
     *
     * @return  string
     */
    public function getRobots();

    /**
     *
     *
     * @return  string
     */
    public function getCanonicalUrl();
}
