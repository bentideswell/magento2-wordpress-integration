<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App;

class Plugin
{
    /**
     * @return void
     */
    public function __construct(
        \FishPig\WordPress\Model\OptionRepository $optionRepository
    ) {
        $this->optionRepository = $optionRepository;
    }

    /**
     * @return []
     */
    public function getActivePlugins(): array
    {
        return $this->optionRepository->getUnserialized('active_plugins') ?? [];
    }
}
