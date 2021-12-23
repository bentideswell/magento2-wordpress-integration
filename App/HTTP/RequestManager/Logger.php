<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\HTTP\RequestManager;

class Logger extends \Monolog\Logger
{
    /**
     * @param  array $requestData
     * @return void
     */
    public function logApiRequest(array $requestData): void
    {
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
}
