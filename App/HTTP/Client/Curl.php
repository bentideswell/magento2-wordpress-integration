<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\HTTP\Client;

class Curl extends \Magento\Framework\HTTP\Client\Curl
{
    /**
     * @var bool
     */
    private $isHeadRequest = false;

    /**
     * @var string
     */
    private $authToken = null;

    /**
     * @var int
     */
    private $realStatusCode = 0;
    
    /**
     *
     */
    public function __construct(
        \FishPig\WordPress\App\Api\AuthToken $apiAuthToken,
        $sslVersion = null
    ) {
        $this->apiAuthToken = $apiAuthToken;
        parent::__construct($sslVersion);
        
        $this->setOption(CURLOPT_SSL_VERIFYHOST, false);
        $this->setOption(CURLOPT_SSL_VERIFYPEER, false);
        $this->setOption(CURLOPT_FOLLOWLOCATION, true);

        $this->addHeader(
            \FishPig\WordPress\App\Api\AuthToken::HTTP_HEADER_NAME, 
            $this->apiAuthToken->getToken()
        );
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
     *
     * @param  string $name
     * @param  mixed $value
     * @return void
     */
    protected function curlOption($name, $value)
    {
        if ($name === CURLOPT_RETURNTRANSFER && $this->isHeadRequest) {
            $value = false;
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
        if ($this->realStatusCode !== 0) {
            return $this->realStatusCode; 
        }
        
        return parent::getStatus();
    }
}
