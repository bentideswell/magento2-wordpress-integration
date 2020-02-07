<?php
/**
 * @category    FishPig
 * @package     FishPig_WordPress
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */
namespace FishPig\WordPress\Block\Sidebar\Widget;

class MediaImage extends AbstractWidget
{
    /**
     * Retrieve the default title
     *
     * @return string
     */
    public function getDefaultTitle()
    {
        return false;
    }

    /**
     * @return $this
     */
    protected function _beforeToHtml()
    {
        if (!$this->getTemplate()) {
            $this->setTemplate('FishPig_WordPress::sidebar/widget/image.phtml');
        }

        return parent::_beforeToHtml();
    }

    /**
     * @return string
     */
    public function getLinkUrl()
    {
        return $this->getData('link_url');
    }

    /**
     * @return string
     */
    public function getImageUrl()
    {
        return $this->getData('url');
    }

    /**
     * @return string
     */
    public function getLinkTarget()    
    {
        foreach(['link_target', 'linktarget'] as $key) {
            if ($value = $this->getData($key)) {
                return $value;
            }
        }

        if ($this->getData('link_target_blank')) {
            return 'blank';
        }

        return '';
    }

    /**
     * @return string
     */
    public function getCaption()    
    {
        return $this->getData('caption');
    }

    /**
     * @return string
     */
    public function getImageAlt()
    {
        return $this->getData('alt');
    }
}
