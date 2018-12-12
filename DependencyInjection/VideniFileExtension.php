<?php


namespace Videni\Bundle\FileBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;

final class VideniFileExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yaml');

        $container->setParameter('videni_file.asset_endpoint', $config['asset_endpoint']);

        $this->registerCacheStrategy($container, $config);
    }

    protected function registerCacheStrategy(ContainerBuilder $container, array $config): void
    {
        if ('none' === $config['metadata']['cache']) {
            $container->removeAlias('videni_file.metadata.cache');
        } elseif ('file' === $config['metadata']['cache']) {
            $container
                ->getDefinition('videni_file.metadata.cache.file_cache')
                ->addArgument($config['metadata']['file_cache']['dir'])
            ;

            $dir = $container->getParameterBag()->resolveValue($config['metadata']['file_cache']['dir']);
            if (!file_exists($dir) && !@mkdir($dir, 0777, true)) {
                throw new \RuntimeException(sprintf('Could not create cache directory "%s".', $dir));
            }
        } else {
            $container->setAlias('videni_file.metadata.cache', new Alias($config['metadata']['cache'], false));
        }
    }
}
