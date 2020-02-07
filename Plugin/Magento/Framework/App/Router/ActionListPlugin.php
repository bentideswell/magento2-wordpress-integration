<?php
/**
 *
 */
namespace FishPig\WordPress\Plugin\Magento\Framework\App\Router;

use \Magento\Framework\App\Router\ActionList;

class ActionListPlugin
{
    /**
     * Magento 2 doesn't allow underscore in the module name
     * So this fixes that and allows module names like FishPig_WordPress_PostTypeTaxonomy to setup Controllers
     * In the above example, FishPig is the vendor name and WordPress_PostTypeTaxonomy is the module name
     *
     * @param ActionList $subject
     * @param Closure $callback
     * @param string $module
     * @param string $area
     * @param string $namespace
     * @param string $action
     * @return string|null
     */
    public function aroundGet(ActionList $subject, $callback, $module, $area, $namespace, $action)
    {
        if (strpos($module, 'FishPig_WordPress_') !== 0) {
            return $callback($module, $area, $namespace, $action);
        }

        return str_replace('FishPig_', 'FishPig\\', $module)
             . '\\Controller' 
             . ($area ? '\\' . $area : $area)
             . '\\' . ucwords($namespace)
             . '\\' . ucwords($action);
    }
}
