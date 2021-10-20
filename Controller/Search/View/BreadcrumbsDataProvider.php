<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Controller\Search\View;

class BreadcrumbsDataProvider implements \FishPig\WordPress\Api\Data\Controller\Action\BreadcrumbsDataProviderInterface
{
    /**
     * @param  \FishPig\WordPress\Api\Data\Entity\ViewableInterface $object
     * @return array
     */
    public function getData(\FishPig\WordPress\Api\Data\Entity\ViewableInterface $search): array 
    {
        return [
            $search::ENTITY => [
                'label' => __($search->getSearchTerm()),
            ]
        ];
    }
}
