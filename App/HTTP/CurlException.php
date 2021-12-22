<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\HTTP;

class CurlException extends \FishPig\WordPress\App\Exception
{
    /**
     * @param  int $code
     * @return string
     */
    public static function getErrorMessageFromCode(int $code) // phpcs:ignore -- static method
    {
        $map = [
            3 => 'The URL was not properly formatted.',
            6 => "Couldn't resolve host.",
            7 => 'Failed to connect() to host or proxy.',
            27 => 'A memory allocation request failed.',
            28 => 'Operation timeout.',
            35 => 'A problem occurred somewhere in the SSL/TLS handshake.',
            47 => 'Too many redirects.',
            48 => 'An option passed to libcurl is not recognized/known.',
            51 => "The remote server's SSL certificate or SSH md5 fingerprint was deemed not OK.",
            58 => 'Problem with the local client certificate.'
        ];

        return $map[$code] ?? '';
    }
}
