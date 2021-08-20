<?php
/**
 *
 */
namespace FishPig\WordPress\Block\Adminhtml\System\Config\Form\Field;

use Magento\Framework\Data\Form\Element\AbstractElement;

class GetAddon extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     *
     */
    const INSTALL_URL_BASE = 'https://fishpig.co.uk/';
    
    /**
     *
     *
     * @param  AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $addonModule = trim(str_replace('wordpress_addon_FishPig_', '', $element->getId()));

        return '<span style="display:inline-block;border:1px solid #ccc;background:#f6f6f6;line-height:1em;padding:10px;font-size:13px;color:#04260d;width:80%;margin-bottom:2px;"><a href="' . $this->getInstallUrl($addonModule) . '" target="_blank">Install Now</a></span>';
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

    /**
     *
     */
    public function getInstallUrl($addonModule)
    {
        if ($addonModule === 'WordPress_ACF') {
            return self::INSTALL_URL_BASE . 'magento/wordpress-integration/advanced-custom-fields/';
        } elseif ($addonModule === 'WordPress_Multisite') {
            return self::INSTALL_URL_BASE . 'magento/wordpress-integration/multisite/';            
        } elseif ($addonModule === 'WordPress_PermalinkManager') {
            return self::INSTALL_URL_BASE . 'magento/wordpress-integration/permalink-manager/';            
        } elseif ($addonModule === 'WordPress_PostTypeTaxonomy') {
            return self::INSTALL_URL_BASE . 'magento/wordpress-integration/post-types-taxonomies/';            
        } elseif ($addonModule === 'WordPress_RelatedProducts') {
            return self::INSTALL_URL_BASE . 'magento/wordpress-integration/related-products/';
        } elseif ($addonModule === 'WordPress_Root') {
            return self::INSTALL_URL_BASE . 'magento/wordpress-integration/root/';
        } elseif ($addonModule === 'WordPress_PluginShortcodeWidgets') {
            return self::INSTALL_URL_BASE . 'magento/wordpress-integration/shortcodes-widgets/';
        } elseif ($addonModule === 'WordPress_WPML') {
            return self::INSTALL_URL_BASE . 'magento/wordpress-integration/wpml/';            
        } elseif ($addonModule === 'WordPress_Multisite') {
            return self::INSTALL_URL_BASE . 'magento/wordpress-integration/multisite/';
        } elseif ($addonModule === 'PageSpeed') {
            return self::INSTALL_URL_BASE . 'magento/extensions/page-speed/';
        } elseif ($addonModule === 'NoBots') {
            return self::INSTALL_URL_BASE . 'magento/extensions/block-robots-stop-spam/';
        }

        return '#';
    }
}
