<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\Theme;

class PackagePublisher
{
    /**
     *
     */
    public function __construct(
        \FishPig\WordPress\App\Theme\PackageBuilder $packageBuilder,
        \Magento\Framework\Controller\ResultFactory $resultFactory,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Filesystem\File\ReadFactory $fileReadFactory
    ) {
        $this->packageBuilder = $packageBuilder;
        $this->resultFactory = $resultFactory;
        $this->request = $request;
        $this->fileReadFactory = $fileReadFactory;
    }

    /**
     * @return
     */
    public function publish()
    {
        $zipFile = $this->packageBuilder->getFilename();
        $fileRead = $this->fileReadFactory->create(
            $zipFile,
            \Magento\Framework\Filesystem\DriverPool::FILE
        );

        if (!($data = $fileRead->readAll($zipFile))) {
            throw new \FishPig\WordPress\App\Exception('Zip exists but data is corrupt or missing.');
        }

        // phpcs:ignore -- there's no harm in this
        $filename = basename($zipFile);

        return $this->resultFactory->create(
            $this->resultFactory::TYPE_RAW
        )->setContents(
            $data
        )->setHeader(
            'Content-Type',
            'application/zip'
        )->setHeader(
            'Content-Disposition',
            'attachment; filename="' . $filename . '"'
        );
    }
}
