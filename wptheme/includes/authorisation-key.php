<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\X;

class AuthorisationKey
{    
    /**
     * @const string
     */
    const KEY_HEADER_NAME = 'X-FishPig-Auth';
    const KEY_OPTION_NAME = 'fishpig_auth_key';
    const KEY_OPTION_NAME_PREVIOUS = 'fishpig_auth_key_previous';

    /**
     * @var array
     */
    static private $keys = null;

    /**
     * @return bool
     */
    static public function getKeys(): array
    {
        if (self::$keys === null) {
            self::$keys = array_values(
                array_unique(
                    array_filter(
                        [
                            get_option(self::KEY_OPTION_NAME),
                            get_option(self::KEY_OPTION_NAME_PREVIOUS),
                        ]
                    )
                )
            );
        }
        
        return self::$keys;
    }

    /**
     * @return ?string
     */
    static public function getKey(): ?string
    {
        return ($keys = self::getKeys()) ? (string)$keys[0] : null;
    }
    
    /**
     * @param  string $key
     * @return bool
     */
    static public function isKeyValid(string $key): bool
    {
        return $key && in_array($key, self::getKeys());
    }

    /**
     * @return bool
     */
    static public function isRestRequestAuthorised(\WP_REST_Request $request): bool
    {
        return self::isKeyValid($request->get_header(self::KEY_HEADER_NAME));
    }
    
    /**
     * @return bool
     */
    static public function isAuthorised(): bool
    {
        $serverHeaderKey = 'HTTP_' . str_replace('-', '_', strtoupper(self::KEY_HEADER_NAME));


        if (empty($_SERVER[$serverHeaderKey])) {
            return false;
        }
        
        return self::isKeyValid($_SERVER[$serverHeaderKey]);
    }
}
