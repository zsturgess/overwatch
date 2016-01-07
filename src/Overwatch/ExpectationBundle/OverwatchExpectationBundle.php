<?php

namespace Overwatch\ExpectationBundle;

use Overwatch\ExpectationBundle\DependencyInjection\ExpectationCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @codeCoverageIgnore
 */
class OverwatchExpectationBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new ExpectationCompilerPass());
    }
}
