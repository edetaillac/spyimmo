<?php

namespace SpyimmoBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class CrawlerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {

        $definition = $container->findDefinition(
            'test.crawler.service'
        );

        $taggedServices = $container->findTaggedServiceIds(
            'offer.crawler'
        );
        foreach ($taggedServices as $id => $tags) {
            $definition->addMethodCall(
                'addCrawler',
                array(new Reference($id))
            );
        }

    }
}