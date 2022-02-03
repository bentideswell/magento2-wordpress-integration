<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\HTTP;

class InvalidStatusException extends \FishPig\WordPress\App\Exception
{
    /**
     *
     */
    public function __construct(string $message = "", int $code = 0, ?\Throwable $previous = null)
    {
        if ($message === '') {
            if ($code === 401) {
                $message = 'WordPress API requires HTTP authentication but credentials not present.'
                    . ' If Magento and WordPress use the same HTTP auth credentials, these are applied automatically.';
            } else {
                $message = 'WordPress HTTP request failed with HTTP response code ' . $code . '.';
            }
        }

        parent::__construct($message, $code, $previous);
    }
    
    /**
     * @param ?string $url
     * @param return self
     */
    public function setUrl(?string $url): self
    {
        if ($this->message) {
            $this->message .= ' The requested URL was ' . $url;
        }

        return $this;
    }
}
