<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model;

class PostTypeManager
{
    /**
     * @var \FishPig\WordPress\Model\PostTypeRepositiory
     */
    private $postTypeRepository;

    /**
     * @param \FishPig\WordPress\Model\PostTypeRepository $postTypeRepository
     */
    public function __construct(\FishPig\WordPress\Model\PostTypeRepository $postTypeRepository)
    {
        $this->postTypeRepository = $postTypeRepository;
    }

    /**
     * @return false|PostType
     */
    public function getPostType($type = null)
    {
        return $this->postTypeRepository->get($type);
    }

    /**
     * @return array
     */
    public function getPostTypes(): array
    {
        return $this->postTypeRepository->getAll();
    }
}
