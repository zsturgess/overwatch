<?php

namespace Overwatch\ServiceBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
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
        $rootNode = $treeBuilder->root('overwatch_service');

        $rootNode
            ->children()
                ->arrayNode("to_ping")
                    ->children()
                        ->floatNode("timeout")
                            ->info("Time, in seconds, to wait for a ping response before timing out and marking as unmet.")
                            ->defaultValue(2)
                            ->min(0)
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
                ->arrayNode("email_reporter")
                    ->children()
                        ->booleanNode("enabled")
                            ->info("Send email reports when the results of a test change")
                            ->defaultTrue()
                        ->end()
                        ->variableNode("report_from")
                            ->info("E-mail address to send reports from")
                            ->defaultValue("overwatch@example.com")
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
