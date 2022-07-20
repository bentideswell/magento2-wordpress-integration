<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model\Config\Source\Logging;

class HTTP
{
    /**
     * @var int
     */
    const DISABLED = 0;
    const ENABLED = 1;
    const REDUCE = 2;

    /**
     *
     */
    private $options = [
        self::ENABLED => 'Enabled',
        self::REDUCE => 'Reduce (Only Errors)',
        self::DISABLED => 'Disabled'
    ];

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];

        foreach ($this->getOptions() as $value => $label) {
            $options[] = [
                'value' => $value,
                'label' => $label
            ];
        }

        return $options;
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }
}
