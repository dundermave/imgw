<?php

/**
 * @file
 * IMGW Controller responsible for displaying meteorological and hydrological data.
 */

namespace Drupal\imgw\Controller;

use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\imgw\IMGWDataFormatter;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

class IMGWController extends ControllerBase {
  use StringTranslationTrait;

  /**
   * Constructs a new IMGWController object.
   *
   * @param \Drupal\imgw\IMGWDataFormatter $IMGWDataFormatter
   *   Service which formats data from API
   *
   * @param \Drupal\Core\StringTranslation\TranslationInterface $stringTranslation
   *   The string translation.
   */
  public function __construct(
    protected IMGWDataFormatter $IMGWDataFormatter,
    TranslationInterface $stringTranslation,
  ) {
    $this->stringTranslation = $stringTranslation;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('imgw.data_formatter'),
      $container->get('string_translation'),
    );
  }

  /**
   * Returns view with meteorogical formatted data.
   *
   * @return array
   *   Returns array that will display view.
   */
  public function listMeteorogical(): array {
    $build = [];
    $build['#cache']['max-age'] = 0;

    $formattedData = $this->IMGWDataFormatter->getMeteorogicalDataTable();

    $rows = $formattedData['rows'];
    $paginatedRows = $this->IMGWDataFormatter->paginateData($rows, 10);

    $build['table'] = [
      '#type' => 'table',
      '#header' => $formattedData['headers'],
      '#rows' => $paginatedRows,
      '#empty' => $this->t('No entries available'),
    ];

    $build['pager'] = [
      '#type' => 'pager',
    ];

    return [
      '#theme' => 'imgw-table',
      '#content' => $build,
      '#attached' => [
        'library' => [
          'imgw/imgw-styling',
        ]
      ]
    ];
  }

  /**
   * Returns view with hydrological formatted data.
   *
   * @return array
   */
  public function listHydrological() {
    $build = [];
    $build['#cache']['max-age'] = 0;

    $formattedData = $this->IMGWDataFormatter->getHydrologicalDataTable();

    $rows = $formattedData['rows'];
    $paginatedRows = $this->IMGWDataFormatter->paginateData($rows, 20);

    $build['table'] = [
      '#type' => 'table',
      '#header' => $formattedData['headers'],
      '#rows' => $paginatedRows,
      '#empty' => $this->t('No entries available'),
    ];

    $build['pager'] = [
      '#type' => 'pager',
    ];

    return [
      '#theme' => 'imgw-table',
      '#content' => $build,
      '#attached' => [
        'library' => [
          'imgw/imgw-styling',
        ]
      ]
    ];
  }

}
