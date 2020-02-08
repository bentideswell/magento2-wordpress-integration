<?php
/**
 *
 */
namespace FishPig\WordPress\Model\Integration;

use FishPig\WordPress\Model\OptionManager;
use FishPig\WordPress\Model\Integration\IntegrationException;

class PermalinkTest
{
    /**
     * @var OptionManager
     */
    protected $optionManager;

    /**
     * @param  OptionManager $optionManager
     */
    public function __construct(OptionManager $optionManager)
    {
        $this->optionManager = $optionManager;
    }

    /**
     * @return 
     */
    public function runTest()
    {
        $optionName = 'permalink_structure';

        if (!$this->optionManager->getOption($optionName)) {
            $this->optionManager->setOption($optionName, '/%postname%/');

            if (!$this->optionManager->getOption($optionName)) {
                IntegrationException::throwException(sprintf(
                    'A custom permalink structure is not set. Please set a custom permalink structure in the WordPress Admin',
                    'https://fishpig.co.uk/magento/wordpress-integration/installation/'
                ));
            }
        }

        return $this;
    }
}
