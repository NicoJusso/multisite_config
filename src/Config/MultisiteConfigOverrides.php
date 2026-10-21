<?php

namespace Drupal\multisite_config\Config;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ConfigFactoryOverrideInterface;
use Drupal\Core\Config\StorageInterface;
use Drupal\Core\Site\Settings;

/**
 * Override the configuration with the specific site configuration.
 */
class MultisiteConfigOverrides implements ConfigFactoryOverrideInterface {

  /**
   * The multi-site configuration key.
   *
   * @var string|null
   */
  private ?string $key;

  public function __construct(private readonly ConfigFactoryInterface $configFactory) {
    $this->key = Settings::get('multisite_config_key');
  }

  /**
   * {@inheritDoc}
   */
  final public function loadOverrides($names): array {
    if (!$this->key) {
      return [];
    }

    $overrides = [];
    $prefix = "$this->key.";
    foreach ($names as $name) {
      if (str_starts_with($name, $prefix)) {
        continue;
      }

      $item = $this->configFactory->get($prefix . $name);
      if ($item) {
        $overrides[$name] = $item->get();
      }
    }
    return $overrides;
  }

  /**
   * {@inheritDoc}
   */
  final public function getCacheSuffix(): string {
    return '_multisite_config_key:' . (Settings::get('multisite_config_key') ?? 'DEFAULT');
  }

  /**
   * {@inheritDoc}
   */
  final public function createConfigObject($name, $collection = StorageInterface::DEFAULT_COLLECTION): null {
    return NULL;
  }

  /**
   * {@inheritDoc}
   */
  final public function getCacheableMetadata($name): CacheableMetadata {
    return new CacheableMetadata();
  }

}
