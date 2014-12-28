<?php

namespace Overwatch\ExpectationBundle;

use Overwatch\ExpectationBundle\DependencyInjection\ExpectationCompilerPass;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OverwatchExpectationBundle extends Bundle
{
    public function build(ContainerBuilder $container) {
        parent::build($container);
        $container->addCompilerPass(new ExpectationCompilerPass());
    }
}
