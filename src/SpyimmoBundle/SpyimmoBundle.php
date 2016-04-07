<?php

namespace SpyimmoBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use SpyimmoBundle\DependencyInjection\CompilerPass\CrawlerPass;

class SpyimmoBundle extends Bundle
{

    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new CrawlerPass());
    }
}
