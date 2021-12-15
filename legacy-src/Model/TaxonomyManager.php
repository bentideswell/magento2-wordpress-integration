<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model;

class TaxonomyManager
{
    /**
     * @var
     */
    private $dataSource;

    /**
     *
     * @param  ModuleManaher $moduleManaher
     * @return void
     */
    public function __construct(
        \FishPig\WordPress\Model\TaxonomyRepository $dataSource
    ) {
        $this->dataSource = $dataSource;
    }

    /**
     * @return $this
     */
    public function load()
    {
        return $this;
    }

    /**
     * @return false|TaxonomyModel
     */
    public function getTaxonomy($taxonomy = null)
    {
        return $this->dataSource->get($taxonomy);
    }

    /**
     * @return array
     */
    public function getTaxonomies(): array
    {
        return $this->dataSource->getAll();
    }
}
