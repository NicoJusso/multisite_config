<?php

namespace Drupal\multisite_config;

use Drupal\Core\Cache\CacheFactoryInterface;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Drupal\multisite_config\Services\CacheFactory;
use Drupal\multisite_config\Services\ConfigFactory;
use Symfony\Component\DependencyInjection\Reference;

/**
 * The multi-site configuration service provider.
 */
class MultisiteConfigServiceProvider extends ServiceProviderBase {

  /**
   * Alter the configuration and cache services.
   *
   * @param \Drupal\Core\DependencyInjection\ContainerBuilder $container
   *   The dependency container.
   */
  final public function alter(ContainerBuilder $container): void {
    if ($container->hasDefinition('config.factory')) {
      $container->getDefinition('config.factory')->setClass(ConfigFactory::class);
    }

    foreach ($container->getDefinitions() as $key => $definition) {
      if ($definition->getDecoratedService()) {
        continue;
      }

      if ($definition->isSynthetic()) {
        continue;
      }

      $class = $definition->getClass();
      if (is_subclass_of($class, CacheFactory::class)) {
        continue;
      }

      if (is_subclass_of($class, CacheFactoryInterface::class)) {
        $container
          ->register("$key.multisite", CacheFactory::class)
          ->setDecoratedService($key)
          ->addArgument(new Reference("$key.multisite.inner"));
      }
    }
  }

}
