<?php

namespace Overwatch\ExpectationBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

/**
 * ExpectationCompilerPass
 * Finds all services tagged with EXPECTATION_TAG (i.e. all expection classes)
 * and notifies the EXPECTATION_MANAGER of their existance.
 */
class ExpectationCompilerPass implements CompilerPassInterface
{
    const EXPECTATION_MANAGER = 'overwatch_expectation.expectation_manager';
    const EXPECTATION_TAG = 'overwatch_expectation.expectation';

    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::EXPECTATION_MANAGER)) {
            return;
        }

        $definition = $container->getDefinition(self::EXPECTATION_MANAGER);

        $taggedServices = $container->findTaggedServiceIds(self::EXPECTATION_TAG);

        foreach ($taggedServices as $id => $tags) {
            foreach ($tags as $attributes) {
                $definition->addMethodCall(
                    'add',
                    [new Reference($id), $attributes['alias']]
                );
            }
        }
    }
}
