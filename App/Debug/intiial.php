<?php
/**
 * @author Ben Tideswell (ben@fishpig.com)
 */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use Magento\Framework\App\Bootstrap;
require dirname(__DIR__) . '/app/bootstrap.php';

set_error_handler(function($code, $msg, $file, $line) {
    throw new \Exception(
        "$msg in $file on line $line"
    );
});

$bootstrap = Bootstrap::create(BP, $_SERVER);
$objectManager = $bootstrap->getObjectManager();

try {
    $appState = $objectManager->get(\Magento\Framework\App\State::class);
    $appState->setAreaCode('frontend');

    $layout = $objectManager->get(\Magento\Framework\View\Layout::class);
    $moduleManager = $objectManager->get(\Magento\Framework\Module\Manager::class);


//    $objectManager->get(\FishPig\WordPress_PluginShortcodeWidget\App\Emulation::class);

    // Integration Tests
    $integrationTests = $objectManager->get(\FishPig\WordPress\App\Integration\Tests::class);
    $integrationTests->runTests();
    if ($warnings = $integrationTests->getWarnings()) {
        $errors = [];
        foreach ($warnings as $warning) {
            $errors[] = $warning->getMessage();
        }
        
        throw new \Exception('Integration Errors: ' . implode("\n", $errors));
    }


    // ACF
    if (true === $moduleManager->isEnabled('FishPig_WordPress_ACF')) {
        $acfData = [
            'post' => [
                'model' => $postRepository->get(1),
                'data_provider' => $objectManager->get(\FishPig\WordPress_ACF\Model\Post\DataProvider::class)
            ],/*
            'term' => [
                'model' => $termRepository->get(1),
                'data_provider' => $objectManager->get(\FishPig\WordPress_ACF\Model\Term\DataProvider::class)
            ]*/
        ];

        foreach ($acfData as $acfItem) {
            foreach (['text', 'number', 'image', 'select', 'post_object', 'taxonomy', 'user', 'flexible_content', 'repeater'] as $field) {
                $acfItem['data_provider']->get($field, $acfItem['model']->getId());            
                if (!$acfItem['model']->getMetaValue($field)) {
                    throw new \Exception('No value for ' . get_class($acfItem['model']) . '. Key was ' . $field);
                }
            }
        }
    }   




    // Version Images
    if ($files = trim(shell_exec('find ' . dirname(__DIR__) . '/app/code/FishPig -type f -name "Version.php"'))) {
        $configText = $objectManager->get(\Magento\Framework\Data\Form\Element\Text::class)
            ->setForm($objectManager->get(\Magento\Framework\Data\Form::class));
        foreach (array_filter(
            array_map(
                function($x) {
                    
                    if (strpos($x, 'Inject') !== false) {
                        return null;
                    }
                    
                    return str_replace(
                        '.php',
                        '',
                        str_replace('/', '\\', str_replace(dirname(__DIR__) . '/app/code/', '', $x))
                    );
                },
                explode("\n", $files)
            )
        ) as $class) {
            $objectManager->get($class)->render($configText);
        }
    }
    
    // Blocks
    $objectManager->get(\FishPig\WordPress\App\View\AssetProvider::class)->provideAssets(
        $objectManager->get(\Magento\Framework\App\RequestInterface::class),
        $objectManager->get(\Magento\Framework\App\ResponseInterface::class)
    );



    // AutoLogin
    if ($moduleManager->isEnabled('FishPig_WordPress_AutoLogin')) {
        $objectManager->get(\FishPig\WordPress_AutoLogin\App\AutoLogin::class)->getLoginUrlWithKey(
            $objectManager->get(\Magento\User\Model\UserFactory::class)->create()->load(1)
        );
    }
    
    exit(0);
} catch (\Exception $e) {
    echo "\n\033[41m" . trim($e->getMessage()) . "\033[0m\n";
    echo "\nTrace\n" . $e->getTraceAsString() . "\n\n";
    exit(1);
}