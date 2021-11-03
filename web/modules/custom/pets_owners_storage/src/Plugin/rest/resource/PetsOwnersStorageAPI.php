<?php

namespace Drupal\pets_owners_storage\Plugin\rest\resource;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\pets_owners_storage\PetsOwnersRepository;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

/**
 * @RestResource(
 *   id = "pos_api",
 *   label = @Translation("Pets Owners Storage"),
 *   uri_paths = {
 *     "canonical" = "/pos/{id}"
 *   }
 * )
 */
class PetsOwnersStorageAPI extends ResourceBase {
  /**
   * A current user instance.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Repos to Pets Owners Storage.
   *
   * @var \Drupal\pets_owners_storage\PetsOwnersRepository
   */
  protected PetsOwnersRepository $repos;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              array $serializer_formats,
                              LoggerInterface $logger,
                              AccountProxyInterface $current_user,
                              PetsOwnersRepository $repos) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);

    $this->currentUser = $current_user;
    $this->repos = $repos;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container,
                                array $configuration,
                                $plugin_id,
                                $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('pets_owners_storage'),
      $container->get('current_user'),
      $container->get('pets_owners_storage.repository'),
    );
  }

  /**
   * Responds to GET requests.
   */
  public function get($id): ModifiedResourceResponse {
    $response = [];
    $owner = $this->repos->loadFromId($id);
    if ($owner) {
      $dop = [];
      foreach ($owner as $entry) {
        // Sanitize each entry.
        $dop[] = array_map('Drupal\Component\Utility\Html::escape', (array) $entry);
      }
      $rows = $dop[0];
      $response['Owner'] = [
        'name' => $rows['name'],
        'gender' => $rows['gender'],
        'prefix' => $rows['prefix'],
        'age' => $rows['age'],
        'mother_name' => $rows['mother_name'],
        'father_name' => $rows['father_name'],
        'name_of_pets' => $rows['name_of_pets'],
        'email' => $rows['email'],
      ];
      return new ModifiedResourceResponse($response);
    }
    else {
      return new ModifiedResourceResponse('Required parameter ID is not set.', 400);
    }
  }

}
