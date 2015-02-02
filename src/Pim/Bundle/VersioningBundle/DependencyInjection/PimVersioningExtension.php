<?php

namespace Pim\Bundle\VersioningBundle\DependencyInjection;

use Akeneo\Bundle\StorageUtilsBundle\DependencyInjection\StorageHelper;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Yaml\Yaml;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class PimVersioningExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('entities.yml');
        $loader->load('guessers.yml');
        $loader->load('managers.yml');
        $loader->load('builders.yml');
        $loader->load('event_subscribers.yml');

        $helper = new StorageHelper($container);
        $helper->loadStorageConfigFiles('catalog_product', __DIR__);
/*
        $versionMappingsPass = $helper->getMappingsPass(
            'catalog_product',
            [ realpath(__DIR__ . '/Resources/config/model/doctrine') => 'Pim\Bundle\VersioningBundle\Model' ]
        );
        $container->addCompilerPass($versionMappingsPass);
*/

        $file = __DIR__.'/../Resources/config/pim_versioning_entities.yml';
        $entities = Yaml::parse(realpath($file));
        $container->setParameter('pim_versioning.versionable_entities', $entities['versionable']);

        $this->loadSerializerConfig($configs, $container);
    }

    /**
     * Load serializer related configuration
     *
     * @param array            $configs
     * @param ContainerBuilder $container
     */
    protected function loadSerializerConfig(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config/serializer'));
        $loader->load('serializer.yml');
        $loader->load('structured.yml');
        $loader->load('flat.yml');
    }
}
