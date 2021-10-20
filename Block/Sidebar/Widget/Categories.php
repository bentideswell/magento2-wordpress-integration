<?php
/**
 * @category FishPig
 * @package  FishPig_WordPress
 * @author   Ben Tideswell <help@fishpig.co.uk>
 */
namespace FishPig\WordPress\Block\Sidebar\Widget;

class Categories extends AbstractWidget
{
    /**
     * @param  \Magento\Framework\View\Element\Template\Context $context,
     * @param  \FishPig\WordPress\Block\Context $wpContext,
     * @param  array $data = []
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \FishPig\WordPress\Block\Context $wpContext,
        \FishPig\WordPress\Model\ResourceModel\Term\CollectionFactory $termCollectionFactory,
        array $data = []
    ) {
        $this->termCollectionFactory = $termCollectionFactory;

        parent::__construct($context, $wpContext, $data);
    }
    
    /**
     * Returns the current category collection
     *
     * @return FishPig\WordPress\Model_Resource_Term_Collection
     */
    public function getCategories()
    {
        $collection = $this->termCollectionFactory->create()
            ->addTaxonomyFilter($this->getTaxonomy())
            ->addParentIdFilter($this->getParentId());
            
        if (!$this->canShowEmpty()) {
            $collection->addHasObjectsFilter();
        }

        $collection->getSelect()->reset('order')->order('name ASC');

        return $collection;
    }

    /**
     *
     */
    public function getTaxonomy()
    {
        return $this->_getData('taxonomy') ? $this->_getData('taxonomy') : 'category';
    }

    /**
     * Returns the parent ID used to display categories
     * If parent_id is not set, 0 will be returned and root categories displayed
     *
     * @return int
     */
    public function getParentId()
    {
        return number_format($this->getData('parent_id'), 0, '', '');
    }

    /**
     * Determine whether the category is the current category
     *
     * @param  FishPig\WordPress\Model_Category $category
     * @return bool
     */
    public function isCurrentCategory($category)
    {
        if ($this->getCurrentCategory()) {
            return (int)$category->getId() === (int)$this->getCurrentCategory()->getId();
        }

        return false;
    }

    /**
     * Retrieve the current category
     *
     * @return FishPig\WordPress\Model_Category
     */
    public function getCurrentCategory()
    {
        if (!$this->hasCurrentCategory()) {
            $this->setCurrentCategory($this->registry->registry('wordpress_term'));
        }

        return $this->getData('current_category');
    }

    /**
     * Retrieve the default title
     *
     * @return string
     */
    public function getDefaultTitle()
    {
        return __('Categories');
    }

    /**
     * Set the posts collection
     */
    protected function _beforeToHtml()
    {
        if (!$this->getTemplate()) {
            $this->setTemplate('FishPig_WordPress::sidebar/widget/categories.phtml');
        }

        return parent::_beforeToHtml();
    }
    
    /**
     * @return bool
     */
    public function canShowEmpty(): bool
    {
        return true;
    }
}
