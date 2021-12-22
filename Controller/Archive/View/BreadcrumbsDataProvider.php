<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Controller\Archive\View;

class BreadcrumbsDataProvider implements \FishPig\WordPress\Api\Controller\Action\BreadcrumbsDataProviderInterface
{
    /**
     * @param  \FishPig\WordPress\Api\Data\ViewableModelInterface $object
     * @return array
     */
    public function getData(
        \FishPig\WordPress\Api\Data\ViewableModelInterface $archive
    ): array {
        return [
            $archive::ENTITY => [
                'label' => __($archive->getName()),
                'title' => __($archive->getName())
            ]
        ];
    }
}
