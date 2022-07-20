<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\HTTP\RequestManager;

use FishPig\WordPress\Model\Config\Source\Logging\HTTP as HTTPLoggingFlag;

class Logger extends \Monolog\Logger
{
    /**
     *
     */
    private $scopeConfig = null;

    /**
     * @param  array $requestData
     * @return void
     */
    public function logApiRequest(array $requestData): void
    {
        if (!$this->canSaveLog($requestData)) {
            return;
        }

        $longest = 0;
        foreach ($requestData as $key => $value) {
            if (strlen($key) > $longest) {
                $longest = strlen($key);
            }
        }

        if (!empty($requestData['Body'])) {
            $requestData['Body'] = "\n\n" . $requestData['Body'];
        }

        $requestData = array_filter($requestData, function ($x) {
            return is_array($x) ? count($x) > 0 : (string)$x !== '';
        });

        $requestData = array_map(function ($v, $i) use ($longest) {
            if (is_array($v)) {
                $v = trim(
                    implode(
                        "\n",
                        array_map(
                            function ($h, $i) use ($longest) {
                                return str_repeat(' ', $longest+3) . $i . ': ' . $h;
                            },
                            $v,
                            array_keys($v)
                        )
                    )
                );
            }

            return str_pad((string)$i, $longest, ' ', STR_PAD_LEFT) . ':  ' . $v;
        }, $requestData, array_keys($requestData));

        $logMsg = implode("\n", $requestData);

        $this->addRecord(self::INFO, $logMsg . "\n" . str_repeat('-', 90));
    }

    /**
     *
     */
    private function canSaveLog(array $requestData): bool
    {
        if ($this->scopeConfig === null) {
            // This uses the ObjectManager because injecting via the constructor is difficult
            // Because the constructor signature is different for PHP 7.4 and PHP 8.1
            // phpcs:ignore
            $this->scopeConfig = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\App\Config\ScopeConfigInterface::class
            );
        }

        $configValue = (int)$this->scopeConfig->getValue('wordpress/logging/http', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if ($configValue === HTTPLoggingFlag::REDUCE) {
            return !isset($requestData['Status']) || (int)$requestData['Status'] !== 200;
        } elseif ($configValue === HTTPLoggingFlag::DISABLED) {
            return false;
        }

        return true;
    }
}
