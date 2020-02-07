<?php
/**
 *
 */
namespace FishPig\WordPress\Model;

class Logger extends \Monolog\Logger
{
    /**
     * Extended to add in calling object data to context array
     */
    public function addRecord($level, $message, array $context = array())
    {
        if ($backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)) {
            $context['backtrace'] = array_pop($backtrace);
        }

        parent::addRecord($level, $message, $context);
    }
}
