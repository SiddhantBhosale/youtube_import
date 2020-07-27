<?php

namespace Drupal\youtube_import\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure  settings for this site.
 */
class YoutubeSearchSettingsForm extends ConfigFormBase {

  /**
   * Config settings.
   *
   * @var string
   */
  const SETTINGS = 'youtube_api.settings';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'youtube_api_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      static::SETTINGS,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(static::SETTINGS);

    $form['example_thing'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Things'),
      '#default_value' => $config->get('example_thing'),
    ];

    $form['youtube_api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Other things'),
      '#default_value' => $config->get('other_things'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Retrieve the configuration.
    $this->configFactory->getEditable(static::SETTINGS)
      // Set the submitted configuration setting.
      ->set('example_thing', $form_state->getValue('example_thing'))
      // You can set multiple configurations at once by making
      // multiple calls to set().
      ->set('youtube_api_key', $form_state->getValue('youtube_api_key'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
