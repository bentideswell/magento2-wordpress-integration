<?php
/**
 *
 */
namespace FishPig\WordPress\Api\Data;

/**
 * Interface for all entities in the integration that are viewable on the frontend
 * By viewable, it means that the entity has its own page (e.g. posts, categories, tags, users, etc.)
 */
interface ViewableModelInterface
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getUrl();
}
