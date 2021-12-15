<?php
/**
 * @category FishPig
 * @package  FishPig_WordPress
 * @author   Ben Tideswell <help@fishpig.co.uk>
 */
namespace FishPig\WordPress\Model\ResourceModel\Image;

use FishPig\WordPress\Model\ResourceModel\Post\Attachment\Collection\AbstractAttachmentCollection;

class Collection extends \FishPig\WordPress\Model\ResourceModel\Post\Attachment\Collection
{
    /**
     * Load an image
     * Ensure that only images are returned
     *
     * @param  bool $printQuery
     * @param  bool $logQuery
     * @return $this
     */
    public function load($printQuery = false, $logQuery = false)
    {
        $this->getSelect()->where("post_mime_type LIKE 'image%'");

        return parent::load($printQuery, $logQuery);
    }
}
