<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Block;

abstract class AbstractBlock extends \Magento\Framework\View\Element\Template
{
    /**
     * @var OptionManager
     */
    protected $optionManager;

    /**
     * @var \FishPig\WordPress\Block\ShortcodeFactory
     */
    protected $shortcodeFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @param  \Magento\Framework\View\Element\Template\Context $context,
     * @param  \FishPig\WordPress\Block\Context $wpContext,
     * @param  array $data = []
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \FishPig\WordPress\Block\Context $wpContext,
        array $data = []
    ) {
        $this->logger = $wpContext->getLogger();
        $this->registry = $wpContext->getRegistry();
        $this->shortcodeFactory = $wpContext->getShortcodeFactory();
        $this->optionRepository = $wpContext->getOptionRepository();
        $this->url = $wpContext->getUrl();

        parent::__construct($context, $data);
    }

    /**
     * @param  string $shortcode
     * @param  \Magento\Framework\DataObject  $object = null
     * @return string
     */
    public function renderShortcode($shortcode, $object = null)
    {
        return $this->shortcodeFactory->create(
            /**/
        )->setShortcode(
            $shortcode
        )->setPost(
            $object
        )->toHtml();
    }

    /**
     * @param  string $shortcode
     * @param  \Magento\Framework\DataObject  $object = null
     * @return string
     */
    public function doShortcode($shortcode, $object = null)
    {
        return $this->renderShortcode($shortcode, $object);
    }

    /**
     * Catch and log any excep§tions to var/log/wordpress.log
     */
    public function toHtml()
    {
        try {
            return parent::toHtml();
        } catch (\Exception $e) {
            $this->logger->error($e);

            throw $e;
        }
    }
}
