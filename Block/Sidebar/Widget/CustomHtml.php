<?php
/**
 *
 */
namespace FishPig\WordPress\Block\Sidebar\Widget;

class CustomHtml extends AbstractWidget
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

    /**
    * Render html output
    *
    * @return string
     */
    protected function _beforeToHtml()
    {
        if (!$this->getTemplate()) {
            $this->setTemplate('FishPig_WordPress::sidebar/widget/custom-html.phtml');
        }

        return parent::_beforeToHtml();
    }
}
