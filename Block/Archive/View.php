<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Block\Archive;

use \FishPig\WordPress\Model\Archive;

class View extends \FishPig\WordPress\Block\Post\PostList\Wrapper\AbstractWrapper
{
    /**
     * @var Archive
     */
    private $archive = null;

    /**
     * @return Archive
     */
    public function getArchive(): ?Archive
    {
        if ($this->archive === null) {
            $this->archive = $this->registry->registry(Archive::ENTITY);
        }
        
        return $this->archive;
    }

    /**
     * @return \FishPig\WordPress\Model\ResourceModel\Post\Collection
     */
    protected function getBasePostCollection(): \FishPig\WordPress\Model\ResourceModel\Post\Collection
    {
        $archive = $this->getArchive();
        
        return $this->postCollectionFactory->create()->addArchiveDateFilter(
            $archive->getId(), 
            $archive->getIsDaily()
        );
    }
}
