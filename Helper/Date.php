<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Helper;

class Date extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \FishPig\WordPress\App\Option
     */
    private $option;

    /**
     *
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \FishPig\WordPress\Model\OptionRepository $optionRepository
    ) {
        parent::__construct($context);

        $this->optionRepository = $optionRepository;
    }

    /**
     * Formats a WordPress date string
     *
     * @return
     */
    public function formatDate($date, $format = null, $f = false)
    {
        if ($format == null) {
            $format = $this->getDefaultDateFormat();
        }

        /**
         * This allows you to translate month names rather than whole date strings
         * eg. "March","Mars"
         */
        $len = strlen($format);
        $out = '';

        for ($i = 0; $i < $len; $i++) {
            $out .= __(date($format[$i], strtotime($date)));
        }

        return $out;
    }

    /**
     * Formats a WordPress date string
     */
    public function formatTime($time, $format = null)
    {
        if ($format == null) {
            $format = $this->getDefaultTimeFormat();
        }

        return $this->formatDate($time, $format);
    }

    /**
     * Split a date by spaces and translate
     *
     * @param  string $date
     * @param  string $splitter = ' '
     * @return string
     */
    public function translateDate($date, $splitter = ' ')
    {
        $dates = explode($splitter, $date);

        foreach ($dates as $it => $part) {
            $dates[$it] = __($part);
        }

        return implode($splitter, $dates);
    }

    /**
     * Return the default date formatting
     */
    public function getDefaultDateFormat()
    {
        return $this->optionRepository->get('date_format', 'F jS, Y');
    }

    /**
     * Return the default time formatting
     */
    public function getDefaultTimeFormat()
    {
        return $this->optionRepository->get('time_format', 'g:ia');
    }
}
