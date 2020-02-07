<?php
/**
 * @category    FishPig
 * @package     FishPig_WordPress
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */
namespace FishPig\WordPress\Block\Archive;

use \FishPig\WordPress\Model\Archive;

class View extends \FishPig\WordPress\Block\Post\PostList\Wrapper\AbstractWrapper
{
    /**
     * @return \FishPig\WordPress\Model\Archive
     */
    public function getEntity()
    {
        return $this->getArchive();
    }

    /**
     * Caches and returns the archive model
     *
     * @return FishPig\WordPress\Model_Archive
     */
    public function getArchive()
    {
        if (!$this->hasArchive()) {
            $this->setArchive($this->registry->registry('wordpress_archive'));
        }

        return $this->_getData('archive');
    }

    /**
     * Retrieve the Archive ID
     *
     * @return false|int
     */
    public function getArchiveId()
    {
        if ($archive = $this->getArchive()) {
            return $archive->getId();
        }

        return false;
    }

    /**
     * Generates and returns the collection of posts
     *
     * @return FishPig\WordPress\Model_Mysql4_Post_Collection
     */
    protected function _getPostCollection()
    {
        $postCollection = parent::_getPostCollection()->addPostTypeFilter('post');

        if ($this->getArchive()) {
            $postCollection->addArchiveDateFilter($this->getArchiveId(), $this->getArchive()->getIsDaily());
        }
        else {
            $postCollection->forceEmpty();
        }

        return $postCollection;
    }

    /**
     * Split a date by spaces and translate
     *
     * @param string $date
     * @param string $splitter = ' '
     * @return string
     */
    public function translateDate($date, $splitter = ' ')
    {
        return $this->wpContext->getDateHelper()->translateDate($date, $splitter);
    }
}
