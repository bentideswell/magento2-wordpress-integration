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
    static public function getErrorMessageFromCode(int $code)
    {
        $map = [
            3 => 'The URL was not properly formatted.',
            6 => "Couldn't resolve host. The given remote host was not resolved.",
            7 => 'Failed to connect() to host or proxy.',
            27 => 'A memory allocation request failed. This is serious badness and things are severely screwed up if this ever occurs.',
            28 => 'Operation timeout. The specified time-out period was reached according to the conditions.',
            35 => 'A problem occurred somewhere in the SSL/TLS handshake.',
            47 => 'Too many redirects. When following redirects, libcurl hit the maximum amount. Set your limit with CURLOPT_MAXREDIRS.',
            48 => 'An option passed to libcurl is not recognized/known.',
            51 => "The remote server's SSL certificate or SSH md5 fingerprint was deemed not OK.",
            58 => 'Problem with the local client certificate.'
        ];

        return $map[$code] ?? '';
    }
}
