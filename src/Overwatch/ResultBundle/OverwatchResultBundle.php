<?php

namespace Overwatch\ResultBundle;

use Overwatch\ResultBundle\DependencyInjection\ResultReporterCompilerPass;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OverwatchResultBundle extends Bundle
{
    public function build(ContainerBuilder $container) {
        parent::build($container);
        $container->addCompilerPass(new ResultReporterCompilerPass);
    }
}
