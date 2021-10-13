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
        \Magento\Framework\Filesystem $filesystem
    ) {
        $this->packageBuilder = $packageBuilder;
        $this->resultFactory = $resultFactory;
        $this->request = $request;
        $this->filesystem = $filesystem;
    }

    /**
     * @return 
     */
    public function publish()
    {
        $zipFile = $this->packageBuilder->getFilename();

        if (!$zipFile || !is_file($zipFile)) {
            throw new \Exception('Unable to generate theme package.');
        }

        $zipDir = dirname($zipFile);
        $filename = basename($zipFile);
        $dir = $this->filesystem->getDirectoryReadByPath($zipDir);

        if (!$dir->isFile($filename)) {
            throw new \Exception('Unable to find ' . $zipFile);
        }

        if (!($data = $dir->readFile($filename))) {
            throw new \Exception('Zip exists but data is corrupt.');
        }

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
