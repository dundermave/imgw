<?php

/**
 * @file
 * Class responsible for formating data and returning to Controller.
 */

namespace Drupal\imgw;

use Drupal\Core\Config\Config;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Pager\PagerManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class IMGWDataFormatter {
  use StringTranslationTrait;

  /**
   * Configuration object for this builder.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected readonly Config $config; //add readonly

  /**
   * Constructs a new MyController object.
   *
   * @param \Drupal\imgw\IMGWApiConnector $IMGWApiConnector
   *   The IMGWApiConnector.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $typedConfigManager
   *   The typed configuration manager.
   *
   * @param \Drupal\Core\StringTranslation\TranslationInterface $stringTranslation
   *   The string translation.
   *
   * @param \Drupal\Core\Pager\PagerManagerInterface $pagerManager
   *   The pager manager.
   */
  public function __construct(
    protected IMGWApiConnector $IMGWApiConnector,
    protected ConfigFactoryInterface $configFactory,
    TranslationInterface $stringTranslation,
    protected PagerManagerInterface $pagerManager
  ) {
    $this->config = $configFactory->get('imgw.api_settings');
    $this->stringTranslation = $stringTranslation;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('imgw.api_connector'),
      $container->get('config.factory'),
      $container->get('string_translation'),
      $container->get('pager.manager'),

    );
  }

  /**
   * Formats data from the API so that it can be displayed in a table.
   *
   * @param $dataFromApi
   * @param $fields
   * @return array
   */
  protected function getRowsFromData($dataFromApi, $fieldMapping): array
  {
    $fields = array_column($fieldMapping, 'field');

    $rows = [];

    // loop each row from api respond
    foreach($dataFromApi as $rowsFromApi) {
      $row = [];

      // iterate each element in row and get only values that we need
      foreach($rowsFromApi as $key => $value) {
        if (in_array($key, $fields)) {

          // if needed because of some empty values
          if (empty($value) || $value == '') {
            $row[] = '-';
          } else {
            $row[] = $value;
          }

        }
      }

      $rows[] = $row;
    }

    return $rows;
  }

  /**
   * Format headers to match correct format.
   *
   * @param $dataFromApi
   * @param $fields
   * @return array
   */
  protected function getHeadersFromData($fieldMapping): array
  {
    return array_combine(
      array_map(fn($map) => $map['headerKey'], $fieldMapping),
      array_map(fn($map) => $map['headerText'], $fieldMapping)
    );
  }


  /**
   * Get data from the API for Meteorogical and format them
   * to display in view.
   *
   * @return array
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function getMeteorogicalDataTable(): array
  {
    $uri = $this->config->get('api_url_meteorogical');

    $dataFromApi = $this->IMGWApiConnector->fetchDataFromAPI($uri);

    $fieldMapping = [
        [
          'field' => 'stacja',
          'headerKey' => 'station',
          'headerText' => $this->t('Station')
        ],
        [
          'field' => 'data_pomiaru',
          'headerKey' => 'date',
          'headerText' => $this->t('Date')
        ],
        [
          'field' => 'godzina_pomiaru',
          'headerKey' => 'hour',
          'headerText' => $this->t('Hour')
        ],
        [
          'field' => 'temperatura',
          'headerKey' => 'temperature',
          'headerText' => $this->t('Temperature')
        ],
        [
          'field' => 'predkosc_wiatru',
          'headerKey' => 'wind_speed',
          'headerText' => $this->t('Wind Speed')
        ],
        [
          'field' => 'wilgotnosc_wzgledna',
          'headerKey' => 'relative_humidity',
          'headerText' => $this->t('Relative Humidity')
        ],
        [
          'field' => 'suma_opadu',
          'headerKey' => 'rainfall',
          'headerText' => $this->t('Rainfall')
        ],
        [
          'field' => 'cisnienie',
          'headerKey' => 'pressure',
          'headerText' => $this->t('Pressure')
        ],
    ];


    return [
      'headers' => $this->getHeadersFromData($fieldMapping),
      'rows' => $this->getRowsFromData($dataFromApi, $fieldMapping)
    ];
  }

  /**
   * Get data from the API for Meteorogical and format them
   * to display in view.
   *
   * @return array
   */
  public function getHydrologicalDataTable(): array
  {
    $uri = $this->config->get('api_url_hydrological');

    $dataFromApi = $this->IMGWApiConnector->fetchDataFromAPI($uri);

    $fieldMapping = [
        [
          'field' => 'stacja',
          'headerKey' => 'station',
          'headerText' => $this->t('Station')
        ],
        [
          'field' => 'rzeka',
          'headerKey' => 'river',
          'headerText' => $this->t('River')
        ],
        [
          'field' => 'województwo',
          'headerKey' => 'voivodeship',
          'headerText' => $this->t('Voivodeship')
        ],
        [
          'field' => 'stan_wody',
          'headerKey' => 'water_level',
          'headerText' => $this->t('Water level')
        ],
        [
          'field' => 'stan_wody_data_pomiaru',
          'headerKey' => 'water_level_date',
          'headerText' => $this->t('Water level date')
        ],
        [
          'field' => 'temperatura_wody',
          'headerKey' => 'water_temperature',
          'headerText' => $this->t('Water temperature')
        ],
        [
          'field' => 'temperatura_wody_data_pomiaru',
          'headerKey' => 'water_temperature_date',
          'headerText' => $this->t('Water temperature date')
        ],
    ];

    return [
      'headers' => $this->getHeadersFromData($fieldMapping),
      'rows' => $this->getRowsFromData($dataFromApi, $fieldMapping)
    ];
  }

  /**
   * Accepts table rows and limit to create paginations
   *
   * @param array $rows
   * @param integer $limit
   * @return array
   */
  public function paginateData($rows, $limit): array
  {
    $countRows = count($rows);

    $pager = $this->pagerManager->createPager($countRows, $limit);
    $currentPage = $pager->getCurrentPage();

    return array_slice($rows, $currentPage * $limit, $limit);
  }

}
