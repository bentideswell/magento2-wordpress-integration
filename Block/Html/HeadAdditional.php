<?php
/**
 *
 */
namespace FishPig\WordPress\Block\Html;

use Magento\Framework\View\Element\Context;
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Framework\View\Layout;
use FishPig\WordPress\Model\Url as WPUrl;
use Magento\Framework\View\Element\AbstractBlock;

class HeadAdditional extends AbstractBlock
{
    /**
     * @var ModuleManager
     */
    protected $moduleManager;

    /**
     * @var Layout
     */
    protected $layout;

    /**
     * @var WPUrl
     */
    protected $wpUrl;

    /**
     * @param Context $contenxt
     * @param array   $data     = []
     */
    public function __construct(
        Context $context,
        ModuleManager $moduleManager,
        Layout $layout,
        WPUrl $wpUrl,
        array $data = []
    ) {
        $this->moduleManager = $moduleManager;
        $this->layout = $layout;
        $this->wpUrl = $wpUrl;

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

        $siteUrl = rtrim($this->wpUrl->getSiteurl(), '/') . '/';
        $html = [];
        $layoutHandles = $this->layout->getUpdate()->getHandles();

        if (in_array('wordpress_post_view_default', $layoutHandles)) {
            $cssFiles = [
                'wp-block-library-css' => 'wp-includes/css/dist/block-library/style.min.css',
                'wp-block-library-theme-css' => 'wp-includes/css/dist/block-library/theme.min.css',
            ];

            foreach ($cssFiles as $cssTypeId => $cssFile) {
                $html[] = sprintf('<link rel="stylesheet" id="%s" href="%s"  type="text/css" media="all"/>', $cssTypeId, $siteUrl . $cssFile);
            }
        }

        return $html ? implode(PHP_EOL, $html) : '';
    }
}
