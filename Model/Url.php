<?php
/**
 * 
 */
namespace FishPig\WordPress\Model;

use FishPig\WordPress\Model\OptionManager;
use FishPig\WordPress\Model\Network;
use FishPig\WordPress\Model\WPConfig;
use FishPig\WordPress\Model\PostFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Url
{
    /**
     * @var OptionManager
     */
    protected $optionManger;

    /**
     * @var WPConfig
     */
    protected $wpConfig;

    /**
     * @var Network
     */
    protected $network;

    /**
     * @var 
     */
    protected $storeManager;

    /**
     *
     */
    protected $postFactory;

    /**
     * @var string
     */
    protected $magentoUrl = [];

    /**
     * @var array
     */
    protected $front = [];

    /**
     *
     */
    public function __construct(
        OptionManager $optionManager,
        Network $network,
        WPConfig $wpConfig,
        StoreManagerInterface $storeManager,
        PostFactory $postFactory,
        ScopeConfigInterface $scopeConfig
    )
    {
        $this->optionManager = $optionManager;
        $this->wpConfig = $wpConfig;
        $this->network = $network;
        $this->storeManager = $storeManager;
        $this->postFactory = $postFactory;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Get the Magento base URL
     *
     * @return string
     */
    public function getMagentoUrl()
    {
        $store = $this->storeManager->getStore();
        $storeId = (int)$store->getId();

        if (!isset($this->magentoUrl[$storeId])) {
            // Determine whether Magento uses secure or unsecure URL on frontend
            $useSecure = $this->scopeConfig->isSetFlag(
                'web/secure/use_in_frontend',
                ScopeInterface::SCOPE_STORE,
                (int)$this->storeManager->getStore()->getId()
            );

            $magentoUrl = rtrim(
                str_ireplace(
                    'index.php',
                    '',
                    $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_LINK, $useSecure)
                ),
                '/'
            );

            /**
            if ($store->isUseStoreInUrl()) {
                if (preg_match('/(.*)' . $store->getCode() . '[\/]*$/', $magentoUrl, $matches)) {
                    $magentoUrl = $matches[1];
                }
            }*/

            if ($this->ignoreStoreCode()) {
                $storeCode = $this->storeManager->getStore()->getCode();

                if (substr($magentoUrl, -strlen($storeCode)) === $storeCode) {
                    $magentoUrl = substr($magentoUrl, 0, -strlen($storeCode)-1);
                }
            }

            $this->magentoUrl[$storeId] = rtrim($magentoUrl, '/');
        }

        return $this->magentoUrl[$storeId];
    }

    /**
     * @return bool
     */
    public function ignoreStoreCode()
    {
        return (int)$this->scopeConfig->getValue(
            'wordpress/setup/ignore_store_code', 
            ScopeInterface::SCOPE_STORE, 
            (int)$this->storeManager->getStore()->getId()
        ) === 1;
    }

    /**
     * @return string
     */
    public function getBlogRoute()
    {
        return trim(substr($this->getHomeUrl(), strlen($this->getMagentoUrl())), '/');
    }

    /**
     * Generate a WordPress frontend URL
     *
     * @param string $uri = ''
     * @return string
     */
    public function getUrl($uri = '')
    {
        $url = $this->getHomeUrl()    . '/' . $uri;

        if (!$this->hasTrailingSlash()) {
            $url = rtrim($url, '/');
        }

        return $url;
    }

    /**
     * Generate a WordPress frontend URL with the Front var in it
     *
     * @param string $uri = ''
     * @return string
     */
    public function getUrlWithFront($uri = '')
    {
        if ($front = $this->getFront()) {
            $uri = ltrim($front . '/' . $uri, '/');
        }

        return $this->getUrl($uri);
    }

    /**
     * Determine whether to use a trailing slash on URLs
     *
     * @return bool
     */
    public function hasTrailingSlash()
    {
        if ($permalinkStructure = $this->optionManager->getOption('permalink_structure')) {
            return substr($permalinkStructure, -1) === '/';
        }

        return false;
    }

    /**
     * @return string
     */
    public function getSiteurl($extra = '')
    {
        if (!($siteUrl = $this->wpConfig->getData('WP_SITEURL'))) {
            $siteUrl = $this->optionManager->getOption('siteurl');
        }

        return rtrim(rtrim($siteUrl, '/') . '/' . ltrim($extra, '/'), '/');
    }

    /**
     * @return string
     */
    public function getHomeUrl()
    {
        if (!($home = $this->wpConfig->getData('WP_HOME'))) {
            $home = $this->optionManager->getOption('home');
        }

        return rtrim($home, '/');
    }

    /**
     * @return 
     */
    public function getBaseFileUploadUrl()
    {
        return rtrim($this->getWpContentUrl(), '/') . '/uploads/';
    }

    /**
     * @return 
     */
    public function getWpContentUrl()
    {
        if (!($contentUrl = $this->wpConfig->getData('WP_CONTENT_URL'))) {
            $contentUrl = $this->getSiteUrl() . '/wp-content/';
        }

        return $contentUrl;
    }

    /**
     * Retrieve the upload URL
     *
     * @return string
     */
    public function getFileUploadUrl()
    {
        $url = $this->optionManager->getOption('fileupload_url');

        if (!$url) {
            foreach(array('upload_url_path', 'upload_path') as $config) {
                if ($value = $this->optionManager->getOption($config)) {
                    if (strpos($value, 'http') === false) {
                        if (substr($value, 0, 1) !== '/') {
                            $url = $this->getSiteurl() . $value;
                        }
                    }
                    else {
                        $url = $value;
                    }

                    break;
                }
            }

            if (!$url) {
                $url = $this->getBaseFileUploadUrl();
            }
        }

        return rtrim($url, '/') . '/';
    }

    /**
     * @return bool
     */
    public function isRoot()
    {
        return false;
    }

    /**
     * @return int
     */
    protected function getStoreId()
    {
        return (int)$this->storeManager->getStore()->getId();
    }

    /**
     * @return string
     */
    public function getFront()
    {
        $storeId = $this->getStoreId();

        if (!isset($this->front[$storeId])) {
            $this->front[$storeId] = '';

            if ($this->isRoot()) {
                $postPermalink = $this->postFactory->create()->setPostType('post')->getTypeInstance()->getPermalinkStructure();

                if (substr($postPermalink, 0, 1) !== '%') {
                    $this->front[$storeId] = trim(substr($postPermalink, 0, strpos($postPermalink, '%')), '/');
                }
            }
        }

        return $this->front[$storeId];
    }
}
