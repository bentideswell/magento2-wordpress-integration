<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Block\Adminhtml\Form\Renderer\Config;

class MagentoBaseUrlSelector extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @param  \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        if (count($element->getValues()) <= 1) {
            return '';
        }
        
        return parent::render($element);
    }
}
