<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Block\Catalog\Product;

use Magento\Swatches\Block\Product\Renderer\Listing\Configurable as SwatchesConfigurableRendererBlock;

class ListProduct extends \Magento\Catalog\Block\Product\AbstractProduct
{
    /**
     *
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \FishPig\WordPress\Block\Context $wpContext,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        array $data = []
    ) {
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if (!($collection = $this->getCollection())) {
            return '';
        }

        try {
            $productListBlock = $this->getLayout()
                ->createBlock(\Magento\Catalog\Block\Product\ListProduct::class)
                    ->setTemplate($this->getData('product_list_template') ?: 'Magento_Catalog::product/list.phtml')
                    ->setCollection($collection);

            $this->fixProductListBlock($productListBlock);
            $this->applySwatches($productListBlock);
            $this->setProductListHtml($productListBlock->toHtml());
            
            if (!$this->getTemplate()) {
                return $this->getProductListHtml();
            }

            return parent::_toHtml();
        } catch (\Exception $e) {
            $this->logger->error($e);
        }

        return '';
    }

    /**
     * It's too late to add the page-products class via _preparLayout
     * So add it here using JS. This fixed the product styles
     *
     * @inheritDoc
     */
    protected function _afterToHtml($html)
    {
        return $html ? $html . "<script>document.body.classList.add('page-products')</script>" : '';
    }

    /**
     * @param  ListProduct $productListBlock
     * @return void
     */
    private function fixProductListBlock(\Magento\Catalog\Block\Product\ListProduct $productListBlock): void
    {
        // phpcs:ignore -- OM needed as not sure if ViewModel is available (older Magento installs)
        $om = \Magento\Framework\App\ObjectManager::getInstance();

        if (null === $productListBlock->getData('viewModel')) {
            // Adds view model to product list block
            if (class_exists(\Magento\Catalog\ViewModel\Product\OptionsData::class)) {
                $productListBlock->setData(
                    'viewModel',
                    $om->get(\Magento\Catalog\ViewModel\Product\OptionsData::class)
                );
            }
        }

        // Fixes setIsBottom fatal error
        $productListHelper = $om->get(\Magento\Catalog\Helper\Product\ProductList::class);
        
        $toolbarBlock = $this->getLayout()->createBlock(
            \Magento\Framework\View\Element\Template::class
        );

        $toolbarBlock->setCurrentMode(
            $productListHelper->getDefaultViewMode(
                $productListHelper->getAvailableViewMode()
            )
        );

        $productListBlock->setChild('toolbar', $toolbarBlock);
    }

    /**
     * @param  ListProduct $productListBlock
     * @return void
     */
    private function applySwatches(\Magento\Catalog\Block\Product\ListProduct $productListBlock): void
    {
        if (!$this->scopeConfig->isSetFlag('catalog/frontend/show_swatches_in_product_list')) {
            return;
        }

        $detailsRenderersBlockName = 'category.product.type.details.renderers';

        if (false === ($detailsRenderers = $this->getLayout()->getBlock($detailsRenderersBlockName))) {
            $detailsRenderers = $this->getLayout()->createBlock(
                \Magento\Framework\View\Element\RendererList::class,
                $detailsRenderersBlockName
            );

            $swatchesBlock = $this->getLayout()->createBlock(
                SwatchesConfigurableRendererBlock::class,
                'category.product.type.details.renderers.configurable'
            )->setTemplate(
                'Magento_Swatches::product/listing/renderer.phtml'
            )->setData(
                'configurable_view_model',
                \Magento\Framework\App\ObjectManager::getInstance()->get(
                    \Magento\Swatches\ViewModel\Product\Renderer\Configurable::class
                )
            );

            $detailsRenderersDefault = $this->getLayout()->createBlock(
                \Magento\Framework\View\Element\Template::class,
                'category.product.type.details.renderers.default'
            );

            $detailsRenderers->append($swatchesBlock, 'configurable');
            $detailsRenderers->append($detailsRenderersDefault, 'default');
        }

        $productListBlock->append($detailsRenderers, 'details.renderers');
    }
}
