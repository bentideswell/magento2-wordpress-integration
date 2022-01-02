<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

class SetOptionCommand extends \Symfony\Component\Console\Command\Command
{
    /**
     * @const string
     */
    const OPTION = 'option';
    const VALUE = 'value';
    const STORE = 'store';

    /**
     *
     */
    public function __construct(
        \FishPig\WordPress\Model\OptionRepository $optionRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Store\Model\App\Emulation $storeEmulation,
        string $name = null
    ) {
        $this->optionRepository = $optionRepository;
        $this->storeManager = $storeManager;
        $this->storeEmulation = $storeEmulation;
        parent::__construct($name);
    }

    /**
     * @return $this
     */
    protected function configure()
    {
        $this->setName('fishpig:wordpress:set-option');
        $this->setDescription('Set a WordPress option from the CLI.');
        $this->setDefinition([
            new InputOption(self::OPTION, null, InputOption::VALUE_REQUIRED, 'Option name'),
            new InputOption(self::VALUE, null, InputOption::VALUE_REQUIRED, 'Option value'),
            new InputOption(self::STORE, null, InputOption::VALUE_OPTIONAL, 'Store ID'),
        ]);
        
        return parent::configure();
    }

    /**
     * @param  InputInterface $input
     * @param  OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $optionName = trim($input->getOption(self::OPTION));
        $optionValue = trim($input->getOption(self::VALUE));
        $storeId = (int)$input->getOption(self::STORE);

        if (!in_array($optionName, $this->getAllowedOptionNames())) {
            throw new \InvalidArgumentException(
                'The "' . $optionName . '" option is not allowed. Verify and try again.'
                . ' Allowed options are ' . implode(', ', $this->getAllowedOptionNames()) . '.'
            );
        }

        if ($storeId === 0) {
            $stores = $this->storeManager->getStores();
            if (count($stores) > 1) {
                throw new \InvalidArgumentException(
                    'More than 1 store found. Please specify store using --store {{STORE-ID}}'
                );
            }
            
            $storeId = (int)$stores[0]->getId();
        }

        if (($optionValue = $this->prepareValue($optionName, $optionValue)) === '') {
            throw new \InvalidArgumentException('Option value must not be empty.');
        }

        $this->storeEmulation->startEnvironmentEmulation($storeId, 'frontend');

        try {
            $this->optionRepository->set($optionName, $optionValue);
        } finally {
            $this->storeEmulation->stopEnvironmentEmulation();
        }

        $output->writeLn('<info>Value was saved.</info>');
    }
    
    /**
     *
     */
    private function prepareValue($optionName, $optionValue)
    {
        if (in_array($optionName, ['home', 'siteurl'])) {
            $optionValue = rtrim($optionValue, '/');
        }

        return $optionValue;
    }

    /**
     * @return array
     */
    private function getAllowedOptionNames(): array
    {
        return [
            'home',
            'siteurl',
            'template',
            'stylesheet',
            'permalink_structure'
        ];
    }
}
