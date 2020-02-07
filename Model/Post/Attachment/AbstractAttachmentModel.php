<?php
/**
 * @category    FishPig
 * @package     FishPig_WordPress
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */
namespace FishPig\WordPress\Model\Post\Attachment;

use FishPig\WordPress\Model\Post;

abstract class AbstractAttachmentModel extends Post
{
    /**
     *
     *
     */
    public function _construct()
    {
        $this->setPostType('attachment');

        parent::_construct();
    }

    /**
     *
     *
     */
    protected function _afterLoad()
    {
        $this->loadSerializedData();

        return parent::_afterLoad();
    }

    /**
     * Load the serialized attachment data
     *
     */
    public function loadSerializedData()
    {
        if ($this->getId() > 0 && !$this->getIsFullyLoaded()) {
            $this->getResource()->loadSerializedData($this);
        }
    }

    /**
     *
     *
     */
    public function getMetaValue($key)
    {
        return parent::getMetaValue('_wp_attachment_' . $key);
    }
}
