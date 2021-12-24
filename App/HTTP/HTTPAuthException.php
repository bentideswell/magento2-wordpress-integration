<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\HTTP;

class HTTPAuthException extends \FishPig\WordPress\App\Exception
{
    /**
     * @const string
     */
    const MSG = 'WordPress API requires HTTP authentication but credentials not present.'
                . ' If Magento and WordPress use the same HTTP auth credentials, these are applied automatically.';

    /**
     *
     */
    public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct(
            $message ?: self::MSG,
            $code,
            $previous
        );
    }
}
