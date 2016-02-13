<?php

namespace Beelab\PaypalBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('beelab_paypal');

        $rootNode
            ->children()
                ->scalarNode('username')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('password')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('signature')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('currency')
                    ->cannotBeEmpty()
                    ->defaultValue('EUR')
                ->end()
                ->scalarNode('return_route')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('cancel_route')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('service_class')
                    ->cannotBeEmpty()
                    ->defaultValue('Beelab\PaypalBundle\Paypal\Service')
                ->end()
                ->booleanNode('test_mode')
                    ->defaultValue(false)
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
