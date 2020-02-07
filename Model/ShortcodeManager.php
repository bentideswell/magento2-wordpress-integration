<?php
/**
 *
 */
namespace FishPig\WordPress\Model;

use FishPig\WordPress\Helper\Autop;
use Magento\Cms\Model\Template\FilterProvider;

class ShortcodeManager
{
    /**
     * @var array
     */
    protected $shortcodes = [];

    /**
     * @var Autop
     */
    protected $autop;

    /**
     * @var FilterProvider
     */
    protected $filterProvider;

    /**
     *
     *
     *
     */
    public function __construct(Autop $autop, FilterProvider $filterProvider, array $shortcodes = [])    
    {
        $this->autop          = $autop;
        $this->filterProvider = $filterProvider;

        foreach($shortcodes as $alias => $shortcode) {
            if (!method_exists($shortcode, 'isEnabled') || $shortcode->isEnabled()) {
                $this->shortcodes[$alias] = $shortcode;
            }
        }
    }

    /**
     *
     *
     *
     */
    public function renderShortcode($input, $args = [])
    {
        if ($args && is_object($args)) {
            $args = ['object' => $args];
        }

        // Apply Magento block/template filters
        $input = $this->filterProvider->getBlockFilter()->filter($input);

        if ($shortcodes = $this->getShortcodes()) {
            foreach($shortcodes as $shortcode) {
                // Legacy support. Old shortcodes returned false when not required
                if (($returnValue = $shortcode->renderShortcode($input, $args)) !== false) {
                    $input = $returnValue;
                }
            }
        }

        return $input;
    }

    /**
     *
     *
     *
     */
    public function getShortcodes()
    {
        return $this->shortcodes;
    }

    /**
     *
     *
     *
     */
    public function getShortcodesThatRequireAssets()
    {
        $buffer = [];

        foreach($this->shortcodes as $alias => $shortcode) {
            if (method_exists($shortcode, 'requiresAssetInjection') && $shortcode->requiresAssetInjection()) {
                $buffer[$alias] = $shortcode;
            }
        }

        return $buffer;
    }

    /**
     *
     *
     * @param  string $string
     * @return string
     */
    public function addParagraphTagsToString($string)
    {
        return $this->autop->addParagraphTagsToString($string);
    }
}
