<?php
/**
 *
 */
namespace FishPig\WordPress\Model\ResourceModel\Post\Attachment;

use FishPig\WordPress\Model\ResourceModel\Post as PostResource;

abstract class AbstractAttachmentResource extends PostResource
{
    /**
     *
     *
     */
    public function loadSerializedData($attachment)
    {
        $attachment->setIsFullyLoaded(true);

        $select = $this->getConnection()
            ->select()
            ->from($this->getTable('wordpress_post_meta'), 'meta_value')
            ->where('meta_key=?', '_wp_attachment_metadata')
            ->where('post_id=?', $attachment->getId())
            ->limit(1);

        $data = unserialize($this->getConnection()->fetchOne($select));

        if (is_array($data)) {
            foreach($data as $key => $value) {
                $attachment->setData($key, $value);
            }            
        }

        return $this;
    }
}
