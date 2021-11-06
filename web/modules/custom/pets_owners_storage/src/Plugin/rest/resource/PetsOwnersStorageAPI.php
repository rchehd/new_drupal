<?php

namespace Drupal\pets_owners_storage\Plugin\rest\resource;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\pets_owners_storage\PetsOwnersRepository;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Symfony\Component\HttpFoundation\Request;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

/**
 * @RestResource(
 *   id = "pos_api",
 *   label = @Translation("Pets Owners Storage"),
 *   uri_paths = {
 *     "canonical" = "/get_pos/{id}",
 *     "create" = "/create_pos",
 *   }
 * )
 */
class PetsOwnersStorageAPI extends ResourceBase {
  /**
   * A current user instance.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected AccountProxyInterface $currentUser;

  /**
   * Repos to Pets Owners Storage.
   *
   * @var \Drupal\pets_owners_storage\PetsOwnersRepository
   */
  protected PetsOwnersRepository $repos;

  /**
   * Request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected Request $currentRequest;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              array $serializer_formats,
                              LoggerInterface $logger,
                              AccountProxyInterface $current_user,
                              PetsOwnersRepository $repos,
                              Request $currentRequest) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);

    $this->currentUser = $current_user;
    $this->repos = $repos;
    $this->currentRequest = $currentRequest;
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
      $container->get('request_stack')->getCurrentRequest(),
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

  /**
   * Responds to Post request.
   */
  public function post(): ModifiedResourceResponse {
    try {
      $entry = [
        'name' => $this->currentRequest->get('name'),
        'gender' => $this->currentRequest->get('gender'),
        'prefix' => $this->currentRequest->get('prefix'),
        'age' => $this->currentRequest->get('age'),
        'mother_name' => $this->currentRequest->get('mother_name'),
        'father_name' => $this->currentRequest->get('father_name'),
        'name_of_pets' => $this->currentRequest->get('name_of_pets'),
        'email' => $this->currentRequest->get('email'),

      ];
      $this->repos->insert($entry);
      return new ModifiedResourceResponse("Creating was successfully", 200);
    }
    catch (\Exception $e) {
      return new ModifiedResourceResponse($e->getMessage(), $e->getCode());
    }

  }

  /**
   * Responds to Delete request.
   */
  public function delete($id): ModifiedResourceResponse {
    try {
      $this->repos->delete($id);
      return new ModifiedResourceResponse("Data with ID = $id was deleted", 200);
    }
    catch (\Exception $e) {
      return new ModifiedResourceResponse($e->getMessage(), $e->getCode());
    }
  }

}
