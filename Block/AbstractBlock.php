<?php
/**
 *
 */
namespace FishPig\WordPress\Block;

abstract class AbstractBlock extends \Magento\Framework\View\Element\Template
{
    /**
     * @var
     */
    protected $wpContext;

    /**
     * @var OptionManager
     */
    protected $optionManager;

    /**
     * @var ShortcodeManager
     */
    protected $shortcodeManager;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var Url
     */
    protected $url;

    /**
     * @param Context $context
     * @param App
     * @param array   $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \FishPig\WordPress\Model\Context $wpContext,
        array $data = []
    ) {
        $this->wpContext = $wpContext;
        $this->optionManager = $wpContext->getOptionManager();
        $this->shortcodeManager = $wpContext->getShortcodeManager();
        $this->registry = $wpContext->getRegistry();
        $this->url = $wpContext->getUrl();

        parent::__construct($context, $data);
    }

    /**
     * Parse and render a shortcode
     *
     * @param  string $shortcode
     * @param  mixed  $object    = null
     * @return string
     */
    public function renderShortcode($shortcode, $object = null)
    {
        return $this->shortcodeManager->renderShortcode($shortcode, ['object' => $object]);
    }

    /**
     *
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
            $this->wpContext->getLogger()->error($e);

            throw $e;
        }
    }
    
    /**
     *
     */
    public function getWpUrl()
    {
        return $this->url;
    }
}
