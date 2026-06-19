<?php
/**
 * @deprecated 3.0.0
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model;

class OptionManager
{
    /**
     *
     */
    private $optionRepository = null;

    /**
     * @param \FishPig\WordPress\Model\OptionRepository $optionRepository
     */
    public function __construct(
        \FishPig\WordPress\Model\OptionRepository $optionRepository
    ) {
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
        $isEmptyValue = '__!5_-3mpTy_!_££££_';

        return $this->optionRepository->get($key, $isEmptyValue) !== $isEmptyValue;
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
