<?php

namespace Drupal\multisite_config\Hook;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Site\Settings;

/**
 * Hooks for form alteration.
 */
class FormHooks {

  /**
   * Alters the structure of a form based on specific conditions.
   *
   * @param array &$form
   *   A structured array containing the form's elements, passed by reference.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   An object containing the current state of the form.
   * @param string $form_id
   *   A unique string identifier for the form being altered.
   *
   * @return void
   *   This method does not return any value.
   */
  #[Hook("form_alter")]
  public function formAlter(array &$form, FormStateInterface $form_state): void {
    if (!Settings::get('multisite_config_key')) {
      return;
    }

    if (!$form_state->getFormObject() instanceof ConfigFormBase) {
      return;
    }

    if (isset($form['#after_build'])) {
      $form['#after_build'] = array_filter($form['#after_build'], fn($callback) => $callback != '::checkConfigOverrides');
    }
  }

}
