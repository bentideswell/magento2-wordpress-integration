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
use FishPig\WordPress\App\Debug\TestPool;

class RunTestsCommand extends \Symfony\Component\Console\Command\Command
{
    /**
     * @const string
     */
    const ARG_TESTS = 'tests';
    
    /**
     * @const string
     */
    const OPT_STORE = 'store';
    const OPT_BLOCKS = 'blocks';
    const OPT_QUICK = 'quick';
    const OPT_EXCLUDE = 'exclude';

    /**
     * @var \FishPig\WordPress\App\Debug\TestPoolFactory
     */
    private $testPoolFactory = null;

    /**
     *
     */
    public function __construct(
        \Magento\Framework\App\State $state,
        \FishPig\WordPress\App\Debug\TestPoolFactory $testPoolFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Store\Model\App\Emulation $emulation,
        string $name = null
    ) {
        $this->appState = $state;
        $this->testPoolFactory = $testPoolFactory;
        $this->storeManager = $storeManager;
        $this->emulation = $emulation;
        parent::__construct($name);
    }

    /**
     * @return $this
     */
    protected function configure()
    {
        try {
        $this->setName('fishpig:wordpress:test');
        $this->setDescription('Run some basic tests on the code and data.');
        $this->setDefinition(
            [
                new InputOption(
                    self::OPT_STORE,
                    null,
                    InputOption::VALUE_OPTIONAL,
                    'Store ID'
                ),
                new InputOption(
                    self::OPT_BLOCKS,
                    null,
                    InputOption::VALUE_OPTIONAL,
                    'Whether to run block tests',
                    1
                ),
                new InputOption(
                    self::OPT_QUICK,
                    null,
                    InputOption::VALUE_NONE,
                    'If set, entity limit is set to 5',
                ),
                new InputOption(
                    self::OPT_EXCLUDE,
                    null,
                    InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                    'Test codes to exclude',
                    []
                )
            ]
        );
        
        $this->addArgument('tests', InputOption::VALUE_OPTIONAL, 'Test codes');
        } catch (\Exception $e) {
            exit($e);
        }
        return parent::configure();
    }

    /**
     * @param  InputInterface $input
     * @param  OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {            
            // Set the area code to stop errors in tests
            $this->appState->setAreaCode(
                \Magento\Framework\App\Area::AREA_FRONTEND
            );
            
            // Get the store
            $store = $this->storeManager->getStore(
                (int)($input->getOption(self::OPT_STORE) ?: $this->storeManager->getDefaultStoreView()->getId())
            );

            // Emulate the store
            $this->emulation->startEnvironmentEmulation(
                $store->getId(),
                \Magento\Framework\App\Area::AREA_FRONTEND
            );

            $excludedTestCodes = $input->getOption(self::OPT_EXCLUDE);

            // Get is quick
            $isQuick = (int)$input->getOption(self::OPT_QUICK) === 1;

            // Generate test options
            $testOptions = [
                TestPool::RUN_BLOCK_TESTS => (int)$input->getOption(self::OPT_BLOCKS) === 1,
                TestPool::ENTITY_LIMIT => $isQuick ? 2 : 0,
                'storeId' => $store->getId(),
            ];

            $testPool = $this->testPoolFactory->create();
            $codes = $input->getArgument('tests') ?: $testPool->getCodes();

            if ($excludedTestCodes) {
                $codes = array_diff($codes, $excludedTestCodes);
            }

            $longest = $this->getLongestString($codes);
            
            $output->writeLn(__('Running %1 test(s) on store %2:', count($codes), $store->getId()));

            foreach ($codes as $code) {
                $output->write('<comment>' . str_pad($code, $longest, ' ') . '</comment>  ');
                $testPool->run($code, $testOptions);
                $output->writeLn('<info>Done</info>');    
            }
        } catch (\Exception $e) {
            $output->writeLn('<error>' . $e->getMessage() . '</error>');
            $output->writeLn("\nTrace:");
            $output->writeLn($e->getTraceAsString());
        }
    }
    
    /**
     * @return int
     */
    private function getLongestString(array $strs): int
    {
        foreach ($strs as $s) {
            if (!isset($longest) || strlen($s) > $longest) {
                $longest = strlen($s);
            }
        }
        
        return isset($longest) ? $longest : 0;
    }
}
