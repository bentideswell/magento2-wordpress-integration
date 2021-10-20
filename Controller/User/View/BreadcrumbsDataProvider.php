<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Controller\User\View;

class BreadcrumbsDataProvider implements \FishPig\WordPress\Api\Data\Controller\Action\BreadcrumbsDataProviderInterface
{
    /**
     * @param  \FishPig\WordPress\Api\Data\Entity\ViewableInterface $object
     * @return array
     */
    public function getData(\FishPig\WordPress\Api\Data\Entity\ViewableInterface $user): array 
    {
        return [
            $user::ENTITY => [
                'label' => __($user->getName()),
                'title' => __($user->getName())
            ]
        ];
    }
}
