<?php

namespace Drupal\multisite_config\Services;

use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Site\Settings;
use Drupal\Core\Config\StorageInterface;
use Drupal\Core\Config\TypedConfigManagerInterface;
use Drupal\multisite_config\Config;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Drupal\Core\Config\ConfigFactory as ConfigFactorBase;

/**
 * The multi-site configuration factory.
 */
class ConfigFactory extends ConfigFactorBase {

  /**
   * The multi-site configuration key.
   *
   * @var string
   */
  private string $key;

  public function __construct(StorageInterface $storage, EventDispatcherInterface $event_dispatcher, TypedConfigManagerInterface $typed_config) {
    parent::__construct($storage, $event_dispatcher, $typed_config);
    $key = Settings::get('multisite_config_key');
    if (!empty($key)) {
      $this->key = $key;
    }
  }

  /**
   * {@inheritDoc}
   */
  final public function getEditable($name): Config|ImmutableConfig {
    if (!isset($this->key)) {
      return parent::getEditable($name);
    }

    $config = parent::getEditable("$this->key.$name");
    if (!$config->isNew()) {
      return $config;
    }

    foreach (parent::get($name)->get() as $key => $value) {
      if (!in_array($key, ['_core', 'uuid'])) {
        $config->set($key, $value);
      }
    }
    return $config;
  }

  /**
   * {@inheritDoc}
   */
  final protected function createConfigObject($name, $immutable): Config|ImmutableConfig {
    if ($immutable) {
      return parent::createConfigObject($name, TRUE);
    }

    return new Config($name, $this->storage, $this->eventDispatcher, $this->typedConfigManager);
  }

}
