<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.com/magento-2-wordpress-integration
 */
namespace FishPig\WordPress\X;

class Cors
{
    /**
     * 
     */
    private $origin = null;
    private $homeUrl = null;

    /**
     *
     */
    public function __construct()
    {
        $this->addHeaders();
    }

    /**
     *
     */
    private function addHeaders(): void
    {
        if ($origin = $this->getAllowedOrigin()) {
            header('Access-Control-Allow-Origin: ' . $origin);
            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        }

        if ($this->isOptionsRequest()) {
            header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
            exit;
        }
    }

    /**
     * 
     */
    private function getOrigin(): ?string
    {
        if ($this->origin === null) {
            $this->origin = $_SERVER['HTTP_ORIGIN'] ?? false;
        }

        return $this->origin ?: null;
    }

    /**
     * 
     */
    private function getHomeUrl(): ?string
    {
        if ($this->homeUrl === null) {
            $this->homeUrl = get_option('home') ?: false;
        }

        return $this->homeUrl ?: null;
    }

    /**
     * 
     */
    private function isOptionsRequest(): bool
    {
        return ($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS';
    }

    /**
     * 
     */
    public function getAllowedOrigin(): ?string
    {
        if (!($origin = $this->getOrigin())) {
            return null;
        }

        $homeUrl = $this->getHomeUrl();

        if (strpos($origin, $homeUrl) === 0 || strpos($homeUrl, $origin) === 0) {
            return $origin;
        }

        return null;
    }
}
// phpcs:ignoreFile -- this file is a WordPress theme file and will not run in Magento
