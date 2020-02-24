<?php
/**
 *
 */
namespace FishPig\WordPress\Block\Adminhtml\System\Config\Form\Field;

use Magento\Framework\Data\Form\Element\AbstractElement;

class Addon extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     *
     *
     * @param  AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $addonModule = trim(str_replace('wordpress_addon_FishPig_', '', $element->getId()));

        try {
            $configBlock = \Magento\Framework\App\ObjectManager::getInstance()
                ->create('FishPig\\' . $addonModule . '\Block\Adminhtml\System\Config\Form\Field\Version');
        }
        catch (\ReflectionException $e) {
            try {
                $configBlock = \Magento\Framework\App\ObjectManager::getInstance()
                    ->create('FishPig\\' . $addonModule . '\Block\Adminhtml\System\Config\Form\Field\Addon');
            }
            catch (\Exception $e) {
                return '';
            }
        }

        if (!isset($configBlock)) {
            return '';
        }

        return $configBlock->getElementHtml($element);
    }

    /**
     * @param  AbstractElement $element
     * @return string
     */
    protected function _renderScopeLabel(AbstractElement $element)
    {
        return '';
    }

    /**
     * @param  AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        return str_replace('class="label"', 'style="vertical-align: middle;" class="label"', parent::render($element));
    }
}
