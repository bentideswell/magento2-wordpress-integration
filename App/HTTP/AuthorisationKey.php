<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\HTTP;

class AuthorisationKey
{
    /**
     * @auto
     */
    protected $optionRepository = null;

    /**
     * @auto
     */
    protected $deploymentConfig = null;

    /**
     * @auto
     */
    protected $scopeConfig = null;

    /**
     * @var string
     */
    private $key = null;

    /**
     * @const string
     */
    const KEY_OPTION_NAME = 'fishpig_auth_key';
    const PREVIOUS_KEY_OPTION_NAME = 'fishpig_auth_key_previous';
    const URL_PARAM = '__fpk';


    /**
     * @const string
     */
    const KEY_DATE_FORMAT = 'YmdH';
    const KEY_DATE_DIFFERENCE = '-1 hour';

    /**
     * @const string
     */
    const HTTP_HEADER_NAME = 'X-FishPig-Auth';

    /**
     *
     */
    public function __construct(
        \FishPig\WordPress\Model\OptionRepository $optionRepository,
        \Magento\Framework\App\DeploymentConfig $deploymentConfig,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->optionRepository = $optionRepository;
        $this->deploymentConfig = $deploymentConfig;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        $key = $this->optionRepository->get(self::KEY_OPTION_NAME);

        if ($key && $this->isValidKey($key)) {
            return $key;
        }

        if ($key) {
            $this->optionRepository->set(self::PREVIOUS_KEY_OPTION_NAME, $key);
        }

        $newKey = $this->generateKey();

        $this->optionRepository->set(self::KEY_OPTION_NAME, $newKey);

        return $newKey;
    }

    /**
     * @param  string $key
     * @return bool
     */
    public function isValidKey($key): bool
    {
        return $key && (
            $key === $this->generateKey() ||
            $key === $this->generateKey(strtotime(self::KEY_DATE_DIFFERENCE))
        );
    }

    /**
     *
     */
    public function addKeyToUrl(string $url): string
    {
        if (!$this->scopeConfig->isSetFlag('wordpress/setup/auth_key_in_url')) {
            return $url;
        }

        $arg = self::URL_PARAM . '=' . $this->getKey();

        if (strpos($url, '?') === false) {
            // No query string so just append
            $url .= '?' . $arg;
        } elseif (substr($url, -1) === '&') {
            $url .= $arg;
        } else {
            $url .= '&' . $arg;
        }

        return $url;
    }

    /**
     * @param  string $dateOffset = null
     * @return string
     */
    private function generateKey($dateOffset = null): string
    {
        return sha1(
            $this->deploymentConfig->get('crypt/key') . date(self::KEY_DATE_FORMAT, $dateOffset ?? time())
        );
    }
}
