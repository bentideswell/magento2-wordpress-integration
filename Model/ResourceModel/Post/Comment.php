<?php
/**
 * @category    FishPig
 * @package     FishPig_WordPress
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */
namespace FishPig\WordPress\Model\ResourceModel\Post;

class Comment extends \FishPig\WordPress\Model\ResourceModel\Meta\AbstractMeta
{
    public function _construct()
    {
        $this->_init('wordpress_post_comment', 'comment_ID');
    }
}
