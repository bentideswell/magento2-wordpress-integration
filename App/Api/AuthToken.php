<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\Api;

class AuthToken
{
    /**
     * @var string
     */
    private $token = null;

    /**
     * @const string
     */
    const TOKEN_OPTION_NAME = 'fishpig_auth_token';
    const PREVIOUS_TOKEN_OPTION_NAME = 'fishpig_auth_token_previous';
    
    /**
     * @const string
     */
    const TOKEN_DATE_FORMAT = 'YmdH';
    const TOKEN_DATE_DIFFERENCE = '-1 hour';
    
    /**
     * @const string
     */
    const HTTP_HEADER_NAME = 'X-FishPig-Auth';
    
    public function __construct(
        \FishPig\WordPress\App\Option $option,
        \Magento\Framework\App\DeploymentConfig $deploymentConfig
    ) {
        $this->option = $option;
        $this->deploymentConfig = $deploymentConfig;
    }
    
    /**
     * @return string
     */
    public function getToken(): string
    {
        $token = $this->option->get(self::TOKEN_OPTION_NAME);
        
        if ($token && $this->isValidToken($token)) {
            return $token;
        }
        
        if ($token) {
            $this->option->set(self::PREVIOUS_TOKEN_OPTION_NAME, $token);
        }
        
        $newToken = $this->generateToken();

        $this->option->set(self::TOKEN_OPTION_NAME, $newToken);
        
        return $newToken;
    }
    
    /**
     * @param  string $token
     * @return bool
     */    
    private function isValidToken($token): bool
    {
        return $token && (
            $token === $this->generateToken() ||
            $token === $this->generateToken(strtotime(self::TOKEN_DATE_DIFFERENCE))
        );
    }

    /**
     * @param  string $dateOffset = null
     * @return string
     */
    private function generateToken($dateOffset = null): string
    {
        return sha1(
            $this->deploymentConfig->get('crypt/key') . date(self::TOKEN_DATE_FORMAT, $dateOffset ?? time())
        );
    }
}
