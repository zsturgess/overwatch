<?php

namespace Overwatch\ResultBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * ResultReporterCompilerPass
 * Finds all services tagged with REPORTER_TAG (i.e. all ResultReporter classes)
 * and notifies the REPORTER_MANAGER of their existance.
 */
class ResultReporterCompilerPass implements CompilerPassInterface
{
    const RESULT_REPORTER_MANAGER = 'overwatch_result.result_reporter_manager';

    const RESULT_REPORTER_TAG = 'overwatch_result.result_reporter';

    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::RESULT_REPORTER_MANAGER)) {
            return;
        }

        $definition = $container->getDefinition(self::RESULT_REPORTER_MANAGER);
        $taggedServices = $container->findTaggedServiceIds(self::RESULT_REPORTER_TAG);

        foreach ($taggedServices as $id => $tags) {
            $definition->addMethodCall(
                'add',
                [new Reference($id)]
            );
        }
    }
}
