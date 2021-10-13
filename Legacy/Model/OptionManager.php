<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Legacy\Model;

class OptionManager
{
    /**
     * @param \FishPig\WordPress\App\Option $option
     */
    public function __construct(\FishPig\WordPress\App\Option $option)
    {
        $this->option = $option;
    }

    /**
     * Get option value
     *
     * @param  string $key
     * @return mixed
     */
    public function getOption($key)
    {
        return $this->option->get($key);
    }

    /**
     *
     */
    public function optionExists($key)
    {
        return $this->option->exists($key);
    }

    /**
     *
     */
    public function setOption($key, $value)
    {
        return $this->option->set($key, $value);
    }

    /**
     * @param  string $key
     * @return mixed
     */
    public function getSiteOption($key)
    {
        return false;
    }
}
