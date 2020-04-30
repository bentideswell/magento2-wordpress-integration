<?php
/**
 *
 */
namespace FishPig\WordPress\Block\Sidebar\Widget;

class Text extends AbstractWidget
{
    /**
     * Retrieve the default title
     *
     * @return string
     */
    public function getDefaultTitle()
    {
        return null;
    }

    protected function _beforeToHtml()
    {
        if (!$this->getTemplate()) {
            $this->setTemplate('FishPig_WordPress::sidebar/widget/text.phtml');
        }

        return parent::_beforeToHtml();
    }
}
