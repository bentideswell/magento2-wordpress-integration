<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Api\App\Taxonomy;

interface TaxonomyRetrieverInterface
{
    /**
     * @return []
     */
    public function getData(): array;
}
