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
        $origin = apply_filters(
            "fishpig:cors:allow-origin",
            $this->getAllowedOrigin()
        );

        $corsHeaders = [];

        if ($origin) {
            $corsHeaders['Access-Control-Allow-Origin'] = $origin;

            if ($origin !== '*') {
                $corsHeaders['Access-Control-Allow-Credentials'] = 'true';
            }
            
            $corsHeaders['Access-Control-Allow-Methods'] = ['GET', 'POST', 'OPTIONS'];
        }

        if ($this->isOptionsRequest()) {
            $corsHeaders['Access-Control-Allow-Headers'] = ['Origin', 'X-Requested-With', 'Content-Type', 'Accept'];
            
            if ($this->sendCorsHeaders($corsHeaders)) {
                exit;
            }
        }

        if ($corsHeaders) {
            $this->sendCorsHeaders($corsHeaders);
        }
    }

    /**
     *
     */
    private function sendCorsHeaders(array $corsHeaders): bool
    {
        $corsHeaders = apply_filters(
            "fishpig:cors:headers",
            $corsHeaders
        );

        if (!$corsHeaders) {
            return false;
        }

        foreach ($corsHeaders as $header => $value) {
            if (is_array($value)) {
                $value = implode(', ', $value);
            }

            if ($value === true) {
                $value = 'true';
            } elseif ($value === false) {
                $value = 'false';
            }

            header($header . ': ' . $value);
        }

        return true;
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

        if (!($homeUrl = $this->getHomeUrl())) {
            return null;
        }

        if (($originKey = $this->getOriginKey($origin)) && $originKey === $this->getOriginKey($homeUrl)) {
            return $origin;
        }

        return null;
    }

    /**
     * Normalise a URL to scheme://host:port for exact origin comparison
     */
    private function getOriginKey(string $url): ?string
    {
        $parts = parse_url(strtolower($url));

        if (empty($parts['scheme']) || empty($parts['host'])) {
            return null;
        }

        $port = $parts['port'] ?? ($parts['scheme'] === 'https' ? 443 : 80);

        return $parts['scheme'] . '://' . $parts['host'] . ':' . $port;
    }
}
// phpcs:ignoreFile -- this file is a WordPress theme file and will not run in Magento
