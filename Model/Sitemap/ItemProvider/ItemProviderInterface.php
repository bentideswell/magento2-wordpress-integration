<?php
namespace FishPig\WordPress\Model\Sitemap\ItemProvider;

interface ItemProviderInterface
{

    public function getItems($storeId);
}