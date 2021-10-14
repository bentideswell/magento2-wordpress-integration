<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model;

class Factory
{
    /**
     * @var array
     */
    protected $factories = [];

    /**
     *
     */
    public function __construct(array $factories = [])
    {
        foreach ($factories as $factory) {
            $this->factories[get_class($factory)] = $factory;
        }
    }

    /**
     * Create an instance of $type
     *
     * @param  string $type
     * @return object
     */
    public function create($type, array $args = [])
    {
        echo __LINE__;exit;
        if ($className = $this->getClassNameFromType($type)) {
            return $this->getObjectManager()->create($className, $args);
        }

        return false;
    }

    /**
     * @param  string $type
     * @return object|false
     */
    public function get($type)
    {
        if ($className = $this->getClassNameFromType($type)) {
            return $this->getObjectManager()->get($className);
        }

        return false;
    }

    /**
     * @param  string $type
     * @return object|false
     */
    protected function getObjectManager()
    {
        return \Magento\Framework\App\ObjectManager::getInstance();
    }

    /**
     * @param  string $type
     * @return string
     */
    protected function getClassNameFromType($type)
    {
        if (trim($type) === '') {
            return false;
        }

        if (strpos($type, 'FishPig') !== 0) {
            $type   = trim($type, '\\');
            $prefix = __NAMESPACE__ . '\\';

            if (strpos($type, '\\') > 0) {
                $prefix = 'FishPig\WordPress\\';
            }

            $type = $prefix . $type;
        }

        return class_exists($type) ? $type : false;
    }
}
