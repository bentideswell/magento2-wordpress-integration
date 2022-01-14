<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Block;

class Shortcode extends \Magento\Framework\View\Element\AbstractBlock
{
    /**
     * @var array
     */
    private $shortcodeRendererPool = [];

    /**
     * @param \Magento\Framework\View\Element\Context $context
     * @param array $data = []
     */
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        array $shortcodeRendererPool = [],
        array $data = []
    ) {
        $this->shortcodeRendererPool = $shortcodeRendererPool;
        parent::__construct($context, $data);
    }
    
    /**
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->_beforeToHtml()) {
            return '';
        }

        if (!($shortcode = $this->getShortcode())) {
            return '';
        }

        foreach ($this->shortcodeRendererPool as $shortcodeRenderer) {
            if ($shortcodeRenderer instanceof \FishPig\WordPress\Api\Block\ShortcodeRendererInterface) {
                $shortcode = $shortcodeRenderer->render($shortcode, $this->getCallback() ?: null);
            }
        }
        
        return $shortcode;
    }

    /**
     * @return string
     */
    public function getShortcode(): string
    {
        return str_replace("\\\"", '"', (string)$this->getData('shortcode'));
    }
}
