<?php

namespace Drupal\my_service;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\StringTranslation\TranslationInterface;


/**
 * Implement custom service.
 */
class CustomService {

  use MessengerTrait;
  use StringTranslationTrait;

  /**
   * Connection to db.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected Connection $connection;

  /**
   * Current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected AccountInterface $currentUser;

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * Constructor.
   */
  public function __construct(Connection $connection,
                              TranslationInterface $translation,
                              AccountInterface $currentUser,
                              EntityTypeManagerInterface $entityTypeManager,
                              MessengerInterface $messenger) {
    $this->connection = $connection;
    $this->setStringTranslation($translation);
    $this->currentUser = $currentUser;
    $this->entityTypeManager = $entityTypeManager;
    $this->setMessenger($messenger);
  }

  /**
   * Get amount of users.
   */
  public function getAmountOfAllActive(): TranslatableMarkup {
    $result = $this->connection->select('users_field_data', 'us')
      ->condition('status', '1',)
      ->fields('us', ['status'])
      ->execute();
    $record = $result->fetchAll();
    $row = count($record);
    return $this->t("You are unique among @row users", ['@row' => $row]);

  }

  /**
   * Get current user position.
   */
  public function getCurrentUserPosition(): TranslatableMarkup {
    $result = $this->connection->select('users_field_data', 'us')
      ->condition('status', '1', '=')
      ->fields('us', ['uid', 'created'])
      ->orderBy('created', 'ASC')
      ->execute();
    $record = $result->fetchAll();
    $count = 0;
    foreach ($record as $row) {
      $id = $row->uid;
      $id2 = $this->getData();
      $count++;
      if ($id == $id2) {
        break;
      }
    };
    $str = "Your position of registration $count ";
    return $this->t($str);
  }

  /**
   * Get Node.
   */
  public function getNode(): array {
    $nodes = $this->entityTypeManager->getStorage('node')
      ->loadMultiple();
    $id_node = array_rand($nodes, 1);
    $node = $this->entityTypeManager->getStorage('node')->load($id_node);
    $result = $this->entityTypeManager->getViewBuilder('node')->view($node, 'teaser');
    return $result;
  }

  /**
   * Get Data.
   */
  public function getData(): int {
    return $this->currentUser->id();
  }

  /**
   * Get user data.
   */
  public function getUserData(): array {
    $date = date('F j, Y, G:i:s', $this->currentUser->getLastAccessedTime());
    return [
      'date' => 'Last visit: '.$date,
      'name' => 'Login: '.$this->currentUser->getAccountName(),
    ];

  }

}
