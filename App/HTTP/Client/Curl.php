<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\HTTP\Client;

use FishPig\WordPress\App\HTTP\CurlException;

class Curl extends \Magento\Framework\HTTP\Client\Curl
{
    /**
     * @const string
     */
    const USERAGENT = 'Mozilla/5.0 (compatible; FishPig/1.0)';

    /**
     * @var bool
     */
    private $isHeadRequest = false;

    /**
     * @var string
     */
    private $authorisationKey = null;

    /**
     * @var int
     */
    private $realStatusCode = 0;

    /**
     *
     */
    public function __construct(
        \FishPig\WordPress\App\HTTP\AuthorisationKey $authorisationKey,
        \FishPig\WordPress\App\Integration\Mode $appMode,
        $sslVersion = null
    ) {
        $this->authorisationKey = $authorisationKey;
        $this->appMode = $appMode;
        parent::__construct($sslVersion);

        $this->setOption(CURLOPT_SSL_VERIFYHOST, false);
        $this->setOption(CURLOPT_SSL_VERIFYPEER, false);
        $this->setOption(CURLOPT_FOLLOWLOCATION, false);
        $this->setOption(CURLOPT_USERAGENT, self::USERAGENT);
//        $this->setOption(CURLOPT_MAXREDIRS, 1);

        $this->addHeader(
            \FishPig\WordPress\App\HTTP\AuthorisationKey::HTTP_HEADER_NAME,
            $this->authorisationKey->getKey()
        );

        // phpcs:disable -- Reusing HTTP auth details from Magento WordPress
        if (!empty($_SERVER['HTTP_AUTHORIZATION'])) {
            // If HTTP auth details present then include them in the WP HTTP requests
            // as it's safe to assume that WP is covered by the same http auth
            $this->addHeader('Authorization', $_SERVER['HTTP_AUTHORIZATION']);
        }
        // phpcs:enable
    }

    /**
     * @param  string $url
     * @return void
     */
    public function head($url)
    {
        $this->isHeadRequest = true;
        $this->makeRequest("HEAD", $url);
        $this->isHeadRequest = false;
    }

    /**
     * Stop CURLOPT_RETURNTRANSFER being set on HEAD requests
     * Also set NOBODY when it's a HEAD method and stop the CUSTOMREQUEST being set
     *
     * @param  string $name
     * @param  mixed $value
     * @return void
     */
    protected function curlOption($name, $value)
    {
        if ($name === CURLOPT_RETURNTRANSFER && $this->isHeadRequest) {
            $value = false;
        } elseif ($name === CURLOPT_CUSTOMREQUEST && $value === 'HEAD') {
            return $this->curlOption(CURLOPT_NOBODY, true);
        }

        parent::curlOption($name, $value);
    }

    /**
     * This updates the status code if a redirect happens
     *
     * @return int
     */
    protected function parseHeaders($ch, $data)
    {
        // phpcs:ignore
        $curlInfo = curl_getinfo($this->_ch);

        if (!empty($curlInfo['http_code'])) {
            $this->realStatusCode = $curlInfo['http_code'];
        }

        return parent::parseHeaders($ch, $data);
    }

    /**
     * Gets the real status code
     *
     * @return int
     */
    public function getStatus()
    {
        return $this->realStatusCode !== 0 ? $this->realStatusCode : parent::getStatus();
    }

    /**
     * @param  string $string
     * @return void
     */
    public function doError($string)
    {
        // phpcs:ignore -- what else can we do?
        $errNo = curl_errno($this->_ch);

        // phpcs:ignore -- closing CURL is godly!
        curl_close($this->_ch);

        if ($string === '') {
            $string = CurlException::getErrorMessageFromCode($errNo);
        }

        throw new CurlException($string, $errNo);
    }

    /**
     * @return array
     */
    public function getRequestHeaders(): array
    {
        return $this->_headers;
    }
}
