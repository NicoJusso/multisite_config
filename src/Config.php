<?php

namespace Drupal\multisite_config;

use Drupal\Core\Site\Settings;
use Drupal\Core\Config\Config as ConfigBase;

/**
 * Defines the multi-site configuration object.
 */
class Config extends ConfigBase {

  /**
   * {@inheritDoc}
   */
  final public function getName(): string {
    $name = parent::getName();
    $key = Settings::get('multisite_config_key');
    if (!$key) {
      return $name;
    }

    if (str_starts_with($name, "$key.")) {
      return substr($name, strlen($key) + 1);
    }

    return $name;
  }

}
