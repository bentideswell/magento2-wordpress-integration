<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model\Post;

class Attachment extends \FishPig\WordPress\Model\AbstractMetaModel
{
    /**
     * @return void
     */
    public function _construct()
    {
        $this->setPostType('attachment');

        parent::_construct();
    }

    /**
     * @return self
     */
    protected function _afterLoad()
    {
        if ((int)$this->getId()) {
            if ($metaData =$this->getMetaValue('metadata')) {
                $this->addData(
                    unserialize($metaData, ['allowed_classes' => false])
                );
            }
        }

        return parent::_afterLoad();
    }

    /**
     * @param  string $key
     * @param  mixed  $default = null
     * @return mixed
     */
    public function getMetaValue(string $key, $default = null)
    {
        return parent::getMetaValue(
            strpos($key, '_wp_attachment_') === false ? '_wp_attachment_' . $key : $key
        );
    }
}
