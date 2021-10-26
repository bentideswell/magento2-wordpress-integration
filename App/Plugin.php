<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App;

class Plugin implements \FishPig\WordPress\Model\PluginManagerInterface
{
    /**
     * @return void
     */
    public function __construct(
        \FishPig\WordPress\Model\OptionRepository $optionRepository,
        \FishPig\WordPress\Model\NetworkInterface $network
    ) {
        $this->optionRepository = $optionRepository;
        $this->network = $network;
    }

    /**
     * @param  string $name
     * @return bool
     */
    public function isEnabled(string $name): bool
    {
        foreach ($this->getActivePlugins() as $a => $b) {
            if (strpos($a . '-' . $b, $name) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return []
     */
    public function getActivePlugins(): array
    {
        return $this->optionRepository->getUnserialized('active_plugins') ?? [];
    }
}
