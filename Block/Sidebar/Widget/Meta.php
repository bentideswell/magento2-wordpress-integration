<?php
/**
 * @category FishPig
 * @package  FishPig_WordPress
 * @author   Ben Tideswell <help@fishpig.co.uk>
 */
namespace FishPig\WordPress\Block\Sidebar\Widget;

class Meta extends AbstractWidget
{
    /**
     * Retrieve the default title
     *
     * @return string
     */
    public function getDefaultTitle()
    {
        return __('Meta');
    }

    protected function _beforeToHtml()
    {
        if (!$this->getTemplate()) {
            $this->setTemplate('FishPig_WordPress::sidebar/widget/meta.phtml');
        }

        return parent::_beforeToHtml();
    }
}
