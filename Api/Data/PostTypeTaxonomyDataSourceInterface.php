<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Api\Data;

interface PostTypeTaxonomyDataSourceInterface
{
    /**
     * @param  string $id
     * @return array|false
     */
    public function get($id);

    /**
     * @return []
     */
    public function getAll(): array;
}
