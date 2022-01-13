<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\Debug\Test;

use FishPig\WordPress\App\Debug\TestPool;

class ArchiveTest implements \FishPig\WordPress\App\Debug\TestInterface
{
    /**
     *
     */
    public function __construct(
        \FishPig\WordPress\Model\ArchiveFactory $archiveFactory,
        \FishPig\WordPress\Model\ResourceModel\Post\CollectionFactory $postCollectionFactory,
        \Magento\Framework\View\Layout $layout
    ) {
        $this->archiveFactory = $archiveFactory;
        $this->postCollectionFactory = $postCollectionFactory;
        $this->layout = $layout;
    }
    
    /**
     * @return void
     */
    public function run(array $options = []): void
    {
        $posts = $this->postCollectionFactory->create()
            ->addIsViewableFilter()
            ->addPostTypeFilter('post')
            ->setPageSize(1)
            ->load();
        
        if (count($posts) === 0) {
            throw new \InvalidArgumentException(
                'Unable to run tests. Could not load post.'
            );
        }

        $post = $posts->getFirstItem();

        foreach (['Y', 'Y/m', 'Y/m/d'] as $dateFormat) {
            $archive = $this->archiveFactory->create()->load($post->getPostDate($dateFormat));
            $archive->getName();
            $archive->getUrl();
            $archive->getDatePart('Y/m/d H:i:s');
            $archive->hasPosts();
            $archive->getPostCollection();
            $archive->getId();
            $archive->getResource();
            
            if (isset($options[TestPool::RUN_BLOCK_TESTS]) && $options[TestPool::RUN_BLOCK_TESTS] === true) {
                $this->layout->createBlock(
                    \FishPig\WordPress\Block\Archive\View::class
                )->setArchive($archive)->toHtml();
            }
        }
    }
}
