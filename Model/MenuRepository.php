<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use FishPig\WordPress\Model\Menu;

class MenuRepository extends \FishPig\WordPress\Model\Repository\ModelRepository
{
    /**
     *
     */
    protected $objectFactory = null;

    /**
     *
     */
    private $theme = null;

    /**
     *
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \FishPig\WordPress\Model\MenuFactory $objectFactory,
        \FishPig\WordPress\App\Theme $theme,
        string $idFieldName = 'term_id'
    ) {
        $this->objectFactory = $objectFactory;
        $this->theme = $theme;
        parent::__construct($storeManager, $idFieldName);
    }

    /**
     * @param  int $id
     * @param  array|string $types
     * @return FishPig\WordPress\Model\Post
     */
    public function getByLocation(string $location): Menu
    {
        if ($cachedMenus = $this->getCachedList()) {
            foreach ($cachedMenus as $menu) {
                if ($location === $menu->getMenuLocation()) {
                    return $menu;
                }
            }
        }

        $locations = $this->getMenuLocations();

        if (!isset($locations[$location])) {
            throw new NoSuchEntityException(
                __(
                    'The WordPress menu location "%1" is not registered in WordPress.',
                    $location
                )
            );
        }

        return $this->get(
            (int)$locations[$location]
        )->setMenuLocation(
            $location
        );
    }

    /**
     *
     */
    private function getMenuLocations(): ?array
    {
        return $this->theme->getThemeMods('nav_menu_locations') ?: null;
    }
}
