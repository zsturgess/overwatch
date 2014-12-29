<?php

namespace Overwatch\ExpectationBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Configuration
 * Defines expected config layout for our built-in expectations.
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
        $rootNode = $treeBuilder->root('overwatch_expectation');

        $rootNode
            ->children()
                ->arrayNode("to_ping")
                    ->children()
                        ->integerNode("timeout")
                            ->info("Time, in seconds, to wait for a ping response before timing out and marking as unmet")
                            ->defaultValue(2)
                            ->min(1)
                        ->end()
                        ->floatNode("unsatisfactory")
                            ->info("Time, in seconds, to wait for a ping response before marking as unsatisfactory")
                            ->defaultValue(1)
                            ->min(0)
                        ->end()
                    ->end()
                ->end()
                ->arrayNode("to_resolve_to")
                    ->children()
                        ->variableNode("record_types")
                        ->info("Array of record types to look at when resolving")
                        ->defaultValue(["A", "AAAA", "CNAME"])
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
