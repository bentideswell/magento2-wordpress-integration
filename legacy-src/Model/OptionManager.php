<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model;

class OptionManager
{
    /**
     * @param \FishPig\WordPress\App\OptionRepository $optionRepository
     */
    public function __construct(\FishPig\WordPress\Model\OptionRepository $optionRepository)
    {
        $this->optionRepository = $optionRepository;
    }

    /**
     *
     */
    public function getOption($key)
    {
        return $this->optionRepository->get($key);
    }

    /**
     *
     */
    public function optionExists($key)
    {
        return $this->optionRepository->exists($key);
    }

    /**
     *
     */
    public function setOption($key, $value)
    {
        return $this->optionRepository->set($key, $value);
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
