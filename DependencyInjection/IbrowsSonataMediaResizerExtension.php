<?php

namespace Ibrows\SonataMediaResizerBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class IbrowsSonataMediaResizerExtension extends Extension
{
    /**
     * @param array $configs
     * @param ContainerBuilder $container
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configPath = __DIR__ . '/../Resources/config';

        $fileLocator = new FileLocator($configPath);
        $finder = new Finder();

        $loader = new XmlFileLoader($container, $fileLocator);
        /** @var SplFileInfo $xml */
        foreach($finder->in($configPath)->name('*.xml') as $xml){
            $loader->load($xml->getRelativePathname());
        }

    }
}
