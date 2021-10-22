<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Api\Controller\Action;

interface BreadcrumbsDataProviderInterface
{
    /**
     * @param  \FishPig\WordPress\Api\Data\ViewableModelInterface $object
     * @return array
     */
    public function getData(
        \FishPig\WordPress\Api\Data\ViewableModelInterface $object
    ): array;
}
