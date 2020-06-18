<?php

declare(strict_types=1);

/*
 * This file is part of the Lakion package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lakion\Behat\MinkDebugExtension\ServiceContainer;

use Behat\Testwork\EventDispatcher\ServiceContainer\EventDispatcherExtension;
use Behat\Testwork\ServiceContainer\Extension as ExtensionInterface;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Lakion\Behat\MinkDebugExtension\Listener\FailedStepListener;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Kamil Kokot <kamil.kokot@lakion.com>
 */
class MinkDebugExtension implements ExtensionInterface
{
    /**
     * @param ContainerBuilder $container
     * @param array<string, mixed> $config
     */
    public function load(ContainerBuilder $container, array $config): void
    {
        $this->loadStepFailureListener($container);

        $this->removeAllExistingLogsIfRequested($config);

        $container->setParameter('mink_debug.directory', $config['directory']);
        $container->setParameter('mink_debug.screenshot', $config['screenshot']);
        $container->setParameter('mink_debug.clean_start', $config['clean_start']);
    }

    public function configure(ArrayNodeDefinition $builder): void
    {
        $builder
            ->children()
                ->scalarNode('directory')->isRequired()->end()
                ->booleanNode('screenshot')->defaultFalse()->end()
                ->booleanNode('clean_start')->defaultTrue()->end()
            ->end();
    }

    public function getConfigKey(): string
    {
        return 'mink_debug';
    }

    public function initialize(ExtensionManager $extensionManager): void
    {
    }

    public function process(ContainerBuilder $container): void
    {
    }

    private function loadStepFailureListener(ContainerBuilder $container): void
    {
        $definition = new Definition(FailedStepListener::class, [
            new Reference('mink'),
            '%mink_debug.directory%',
            '%mink_debug.screenshot%',
        ]);

        $definition->addTag(EventDispatcherExtension::SUBSCRIBER_TAG, ['priority' => 0]);

        $container->setDefinition('mink_debug.listener.step_failure', $definition);
    }

    /**
     * @param array<string, mixed> $config
     */
    private function removeAllExistingLogsIfRequested(array $config): void
    {
        if ($config['clean_start']) {
            array_map('unlink', glob($config['directory'] . '/*.log'));
            array_map('unlink', glob($config['directory'] . '/*.png'));
        }
    }
}
