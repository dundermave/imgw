<?php

/**
 * @file
 * Contains the settings for administaring the IMGW module
 */

namespace Drupal\imgw\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\TypedConfigManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use \Drupal\Core\Messenger\MessengerInterface;

class IMGWSettingsForm extends ConfigFormBase {

  /**
   * Default value for meteorogical API on IMGW web
   *
   * @var string
   */
  const DEFAULT_API_URL_METEOROGICAL = "https://danepubliczne.imgw.pl/api/data/synop/";

  /**
   * Default value for hydrological API on IMGW web
   *
   * @var string
   */
  const DEFAULT_API_URL_HYDROLOGICAL = "https://danepubliczne.imgw.pl/api/data/hydro/";

  /**
   * Creates a new IMGWSettingsForm instance.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration factory.
   *
   * @param \Drupal\Core\Config\TypedConfigManagerInterface $typedConfigManager
   *   The typed configuration manager.
   *
   * @param \Drupal\Core\StringTranslation\TranslationInterface $stringTranslation
   *   The string translation.
   *
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    TypedConfigManagerInterface $typedConfigManager,
    TranslationInterface $stringTranslation,
    MessengerInterface $messenger,
  ){
    parent::__construct($config_factory, $typedConfigManager);
    $this->stringTranslation = $stringTranslation;
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('config.typed'),
      $container->get('string_translation'),
      $container->get('messenger'),
    );
  }

  /**
   * @inheritDoc
   */
  public function getFormId(): string {
    return 'imgw_admin_settings';
  }

  /**
   * @inheritDoc
   */
  protected function getEditableConfigNames(): array {
    return [
      'imgw.api_settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {

    // fields for API in settings with input id and its name
    $fields = [
      ['input' => 'api_url_meteorogical', 'name' => 'API URL Meteorological'],
      ['input' => 'api_url_hydrological', 'name' => 'API URL Hydrological'],
    ];

    foreach($fields as $field) {
      $url = $form_state->getValue($field['input']);

      // do not process invalid URLs
      if (!empty($url) && !filter_var($url, FILTER_VALIDATE_URL)) {
        $form_state->setErrorByName('url', $this->t(
          "The URL for '@name' is not valid.", ['@name' => $field['name']])
        );
      }
    }

  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {

    $config = $this->config('imgw.api_settings');

    $form = [];

    $form['api_url_meteorogical'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API URL Meteorological'),
      '#description' => $this->t(
        'API base URL for Meteorological data (empty for default value)'
      ),
      '#required' => false,
      '#default_value' => $config->get('api_url_meteorogical') ?: self::DEFAULT_API_URL_METEOROGICAL
    ];

    $form['api_url_hydrological'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API URL Hydrological'),
      '#description' => $this->t(
        'API base URL for Hydrological data (leave empty for default value)'
      ),
      '#required' => false,
      '#default_value' => $config->get('api_url_hydrological') ?: self::DEFAULT_API_URL_HYDROLOGICAL
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->config('imgw.api_settings')
      ->set(
        'api_url_meteorogical',
        (string) $form_state->getValue(
          'api_url_meteorogical', self::DEFAULT_API_URL_METEOROGICAL
        )
      )
      ->set(
        'api_url_hydrological',
        (string) $form_state->getValue(
          'api_url_hydrological', self::DEFAULT_API_URL_METEOROGICAL
        )
      )
      ->save();

    parent::submitForm($form, $form_state);
  }

}
