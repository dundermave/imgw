<?php

/**
 * @file
 * Class responsible for handling API requests.
 */

namespace Drupal\imgw;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Utility\Error;
use \GuzzleHttp\ClientInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use \Psr\Log\LoggerInterface;

class IMGWApiConnector {

  /**
   * Configuration object for this builder.
   *
   * @var \Psr\Log\LoggerInterface $imgwLogger
   */
  protected readonly LoggerInterface $imgwLogger;

  /**
   * EnablerService constructor.
   *
   * @param \GuzzleHttp\ClientInterface $http_client
   *   The HTTP client.
   *
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerFactory
   *   A logger instance.
   */
  public function __construct(
    protected ClientInterface $httpClient,
    protected LoggerChannelFactoryInterface $loggerFactory,
  ) {
    $this->imgwLogger = $this->loggerFactory->get('imgw');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('http_client'),
      $container->get('logger.factory'),
    );
  }

  /**
   * Fetches data from the IMGW Api.
   *
   * @param string $uri
   *   End point to the API.
   *
   * @return array
   *   The meteorological data.
   *
   * @throws \Exception|\GuzzleHttp\Exception\GuzzleException
   *   If there's a problem and data cannot be fetched.
   */
  public function fetchDataFromAPI(string $uri): array
  {
    try {
      $request = $this->httpClient->request('GET', $uri);
      $result = $request->getBody()->getContents();

      return json_decode($result, true);
    }
    catch (\Exception $e) {
      Error::logException($this->imgwLogger, $e);
      return [];
    }
  }


}
