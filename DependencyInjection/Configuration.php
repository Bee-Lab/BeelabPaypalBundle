<?php

namespace Beelab\PaypalBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('beelab_paypal');
        // BC layer for symfony/config < 4.2
        $rootNode = \method_exists($treeBuilder, 'getRootNode') ? $treeBuilder->getRootNode() : $treeBuilder->root('beelab_paypal');
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
                    ->setDeprecated('The "service_class" option is deprecated. Define your class as service instead.')
                ->end()
                ->booleanNode('test_mode')
                    ->defaultValue(false)
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
