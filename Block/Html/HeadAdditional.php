<?php
/**
 *
 */
namespace FishPig\WordPress\Block\Html;

class HeadAdditional extends \Magento\Framework\View\Element\AbstractBlock
{
    /**
     * @param Context $contenxt
     * @param array   $data     = []
     */
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Framework\View\Layout $layout,
        \FishPig\WordPress\Model\UrlInterface $url,
        array $data = []
    ) {
        $this->moduleManager = $moduleManager;
        $this->layout = $layout;
        $this->url = $url;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->moduleManager->isEnabled('FishPig_WordPress_PluginShortcodeWidget')) {
            return '';
        }

        $siteUrl = rtrim($this->url->getSiteurl(), '/') . '/';
        $html = [];
        $layoutHandles = $this->layout->getUpdate()->getHandles();

        if (in_array('wordpress_post_view_default', $layoutHandles)) {
            $cssFiles = [
                'wp-block-library-css' => 'wp-includes/css/dist/block-library/style.min.css',
                'wp-block-library-theme-css' => 'wp-includes/css/dist/block-library/theme.min.css',
            ];

            foreach ($cssFiles as $cssTypeId => $cssFile) {
                $html[] = sprintf(
                    '<link rel="stylesheet" id="%s" href="%s" type="text/css" media="all"/>',
                    $cssTypeId,
                    $siteUrl . $cssFile
                );
            }
        }

        return $html ? implode(PHP_EOL, $html) : '';
    }
}
