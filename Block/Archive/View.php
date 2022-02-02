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
     * @param  Archive $archive
     * @return self
     */
    public function setArchive(Archive $archive): self
    {
        $this->archive = $archive;
        return $this;
    }

    /**
     * @return \FishPig\WordPress\Model\ResourceModel\Post\Collection
     */
    protected function getBasePostCollection(): \FishPig\WordPress\Model\ResourceModel\Post\Collection
    {
        return $this->getArchive()->getPostCollection();
    }
    
    /**
     * @deprecated 3.0 use self::getArchive
     */
    public function getEntity()
    {
        return $this->getArchive();
    }
}
