<?php

namespace Drupal\multisite_config\Services;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Cache\CacheFactoryInterface;
use Drupal\Core\Site\Settings;

/**
 * The multi-site configuration cache factory.
 */
class CacheFactory implements CacheFactoryInterface {

  /**
   * The multi-site configuration key.
   *
   * @var string|null
   */
  private ?string $key;

  /**
   * Constructs the cache factory decorator.
   *
   * @param \Drupal\Core\Cache\CacheFactoryInterface $origin
   *   The decorated cache factory.
   */
  public function __construct(private readonly CacheFactoryInterface $origin) {
    $this->key = Settings::get('multisite_config_key');
  }

  /**
   * {@inheritDoc}
   */
  final public function get($bin): CacheBackendInterface {
    if (!isset($this->key)) {
      return $this->origin->get($bin);
    }

    $needle = ".multisite_$this->key";
    if (str_ends_with($bin, $needle)) {
      return $this->origin->get($bin);
    }

    return $this->origin->get($bin . $needle);
  }

}
