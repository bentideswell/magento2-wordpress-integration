<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model;

use FishPig\WordPress\Model\Repository\AbstractRepository;

class RepositoryPool
{
    /**
     * @auto
     */
    protected $repositories = null;

    /**
     * @var []
     */
    private $pool = [];

    /**
     * @param \FishPig\WordPress\Model\PostFactory $postFactory
     */
    public function __construct(
        array $repositories = []
    ) {
        foreach ($repositories as $id => $repository) {
            if ($repository instanceof AbstractRepository) {
                $this->pool[$id] = $repository;
            }
        }
    }

    /**
     * @return ?AbstractRepository
     */
    public function get(string $id): ?AbstractRepository
    {
        if ($id === 'post_type') {
            $id = 'postType';
        }

        return isset($this->pool[$id]) ? $this->pool[$id] : null;
    }

    /**
     * @return []
     */
    public function getAll(): array
    {
        return $this->pool;
    }
}
