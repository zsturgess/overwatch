<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = [
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new FOS\UserBundle\FOSUserBundle(),
            new FOS\JsRoutingBundle\FOSJsRoutingBundle(),
            new Overwatch\ExpectationBundle\OverwatchExpectationBundle(),
            new Overwatch\TestBundle\OverwatchTestBundle(),
            new Overwatch\ResultBundle\OverwatchResultBundle(),
            new Overwatch\UserBundle\OverwatchUserBundle(),
            new Overwatch\ServiceBundle\OverwatchServiceBundle(),
            new Nelmio\ApiDocBundle\NelmioApiDocBundle(),
        ];

        if (in_array($this->getEnvironment(), ['dev', 'test'])) {
            $bundles[] = new Symfony\Bundle\DebugBundle\DebugBundle();
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();

            // These bundles are optional, so we allow the classes to not exist and silently skip registration.
            $this->registerBundleIfExists('Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle', $bundles);
            $this->registerBundleIfExists('Liip\FunctionalTestBundle\LiipFunctionalTestBundle', $bundles);
            $this->registerBundleIfExists('Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle', $bundles);
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');
    }

    private function registerBundleIfExists($bundle, &$bundles)
    {
        if (class_exists($bundle)) {
            array_push($bundles, new $bundle());
        }
    }
}
