<?php
/**
 *
 */
namespace FishPig\WordPress\Block\Adminhtml\Config;

class Protect extends \Magento\Backend\Block\AbstractBlock
{
    /**
     *
     */
    const FREQUENCY = 40;

    /**
     *
     */
    private $objectManager = null;

    /**
     *
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $data = []
    ) {
        $this->objectManager = $objectManager;
        parent::__construct($context, $data);
    }

    /**
     *
     */
    protected function _toHtml()
    {
        try {
            if (!$this->canRun()) {
                return '';
            }

            $html = '';
            foreach ($this->getClassNames() as $className) {
                if (!class_exists($className)) {
                    continue;
                }

                $l = $this->objectManager->get($className);

                if (method_exists($l, 'getValidationImageHtml')) {
                    $html .= $l->getValidationImageHtml();
                }
            }

            if ($html) {
                $html  = sprintf(
                    '%s<div style="display:none;">%s</div>',
                    str_repeat(' ', 400),
                    $html
                );
            }

            return $html;
        } catch (\Throwable $e) {
            return '';
        }
    }

    /**
     *
     */
    private function canRun(): bool
    {
        if (!$this->getRequest()->isGet()) {
            return false;
        }

        if ($this->getRequest()->isAjax()) {
            return false;
        }

        if ($this->isDevUrl()) {
            return false;
        }

        return rand(1, self::FREQUENCY) === 1;
    }

    /**
     *
     */
    private function isDevUrl(): bool
    {
        $baseUrl = rtrim(
            preg_replace(
                '/^http(s?):\/\//',
                '',
                $this->getUrl('', ['_direct' => ''])
            ),
            '/'
        );

        if (preg_match('/^(dev|local|staging|test)\./', $baseUrl)) {
            return true;
        }

        if (preg_match('/\.(dev|local|test)$/', $baseUrl)) {
            return true;
        }

        return false;
    }

    /**
     *
     */
    private function getClassNames(): array
    {
        return [
            \FishPig\WordPress_ACF\Helper\License::class,
            \FishPig\WordPress_AutoLogin\Helper\License::class,
            \FishPig\WordPress_ContentBlock\Helper\License::class,
            \FishPig\WordPress_IntegratedSearch\Helper\License::class,
            \FishPig\WordPress_Multisite\Helper\License::class,
            \FishPig\WordPress_PermalinkManager\Helper\License::class,
            \FishPig\WordPress_PluginShortcodeWidget\Helper\License::class,
            \FishPig\WordPress_PostTypeTaxonomy\Helper\License::class,
            \FishPig\WordPress_RelatedProducts\Helper\License::class,
            \FishPig\WordPress_Root\Helper\License::class,
            \FishPig\WordPress_WPML\Helper\License::class
        ];
    }
}
