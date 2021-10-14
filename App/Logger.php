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
     * Extended to add in calling object data to context array
     */
    public function addRecord($level, $message, array $context = [])
    {
        if ($backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)) {
            $context['backtrace'] = array_pop($backtrace);
        }

        parent::addRecord($level, $message, $context);
    }
}
