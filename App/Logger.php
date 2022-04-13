<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App;

class Logger extends \Monolog\Logger
{
    /**
     * @var \Magento\Framework\App\State
     */
    private $appState;

    /**
     * @param string $name
     * @param \Magento\Framework\App\State $appState
     * @param array $handlers = []
     * @param array $processors = []
     * @return void
     */
    public function __construct(
        $name,
        \Magento\Framework\App\State $appState,
        array $handlers = [],
        array $processors = []
    ) {
        $this->appState = $appState;
        parent::__construct($name, $handlers, $processors);
    }

    /**
     * @return bool
     */
    public function isDeveloperMode(): bool
    {
        return $this->appState->getMode() == \Magento\Framework\App\State::MODE_DEVELOPER;
    }
}
