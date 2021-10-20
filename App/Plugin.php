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
    public function __construct(\FishPig\WordPress\Model\OptionRepository $optionRepository)
    {
        $this->optionRepository = $optionRepository;
    }

    /**
     * @param  string $name
     * @return bool
     */
    public function isEnabled($name): bool
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
        /* ToDo: 
        if ($this->network->isEnabled()) {
            if ($networkPlugins = $this->option->getSiteOption('active_sitewide_plugins')) {
                $plugins += (array)unserialize($networkPlugins);
            }
        }
        */
        
        return $this->optionRepository->getUnserialized('active_plugins') ?? [];
    }
}
