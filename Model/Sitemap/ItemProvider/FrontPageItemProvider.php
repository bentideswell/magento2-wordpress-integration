<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model\Sitemap\ItemProvider;

class FrontPageItemProvider extends AbstractItemProvider
{
    /**
     * @auto
     */
    protected $frontPageHelper = null;

    /**
     *
     */
    const PRIORITY = 0.75;
    const CHANGE_FREQUENCY = 'daily';

    /**
     *
     */
    public function __construct(
        \Magento\Sitemap\Model\SitemapItemInterfaceFactory $itemFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \FishPig\WordPress\App\Logger $logger,
        \FishPig\WordPress\Helper\FrontPage $frontPageHelper
    ) {
        $this->frontPageHelper = $frontPageHelper;
        parent::__construct($itemFactory, $storeManager, $logger);
    }

    /**
     * @param  int $storeId
     * @return array
     */
    public function getItems($storeId)
    {
        $urls = [];

        try {
            $urls[] = $this->frontPageHelper->getRealHomepageUrl();
        } catch (\Exception $e) {
            $this->logger->error($e);
        }

        foreach (['getFrontPage', 'getPostsPage'] as $getPostMethod) {
            try {
                $post = $this->frontPageHelper->$getPostMethod();

                if ($post && $post->getId()){
                    $urls[] = $post->getUrl();
                }
            } catch (\Exception $e) {
                $this->logger->error($e);
            }
        }

        $urls = array_filter(
            array_map(
                function ($url) {
                    return $this->makeUrlRelative($url);

                },
                array_unique($urls)
            )
        );

        if (!$urls) {
            // No URLs generated so return empty.
            return [];
        }

        $items = [];
        foreach ($urls as $url) {
            if (!$this->canAddToSitemap($url)) {
                continue;
            }

            $items[] = $this->itemFactory->create(
                [
                    'url' => $url,
                    'updatedAt' => $this->getModifiedDate($url),
                    'images' => $this->getImages($url),
                    'priority' => $this->getPriority($url),
                    'changeFrequency' => $this->getChangeFrequency($url),
                ]
            );
        }

        return $items;
    }

    /**
     *
     */
    protected function getCollection($storeId): iterable
    {
        return [];
    }
}
