<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\Logger;

class BacktraceProcessor implements \Monolog\Processor\ProcessorInterface
{
    /**
     * @const int
     */
    const BACKTRACE_FRAMES_LIMIT = 5;
    const BACKTRACE_FRAMES_OFFSET = 3;
    
    /**
     * {@inheritDoc}
     */
    public function __invoke(array $record): array
    {
        if (!isset($record['context']['backtrace']) && strpos($record['message'], 'Stack trace:') === false) {
            if ($backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, self::BACKTRACE_FRAMES_LIMIT)) {
                if (count($backtrace) > self::BACKTRACE_FRAMES_OFFSET) {
                    $record['context']['backtrace'] = array_slice($backtrace, self::BACKTRACE_FRAMES_OFFSET);
                }
            }
        }

        return $record;
    }
}
