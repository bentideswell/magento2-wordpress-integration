<?php
/**
 * @category    FishPig
 * @package     FishPig_WordPress
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */
namespace FishPig\WordPress\Model\ResourceModel;

use FishPig\WordPress\Model\ResourceModel\Post\Attachment\AbstractAttachmentResource;

class Image extends AbstractAttachmentResource
{
    public function isImagePostName($postName)
    {
        $select = $this->_getReadAdapter()
            ->select()
            ->from($this->getMainTable(), 'ID')
            ->where('post_type=?', 'attachment')
            ->where('post_name=?', $postName)
            ->limit(1);

        return $this->_getReadAdapter()->fetchOne($select);
    }
}
