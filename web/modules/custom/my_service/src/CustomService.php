<?php

namespace Drupal\my_service;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;


/**
 * Implement custom service.
 */
class CustomService {
  use MessengerTrait;
  use StringTranslationTrait;

  protected $connection;
  protected $currentUser;
  protected $entityTypeManager;

  public function __construct(Connection $connection, TranslationInterface $translation,
                              AccountInterface $currentUser,EntityTypeManagerInterface $entityTypeManager,
                              MessengerInterface $messenger) {
    $this->connection = $connection;
    $this->setStringTranslation($translation);
    $this->currentUser = $currentUser;
    $this->entityTypeManager = $entityTypeManager;
    $this->setMessenger($messenger);
  }

  public function getAmountOfAllActive() {
    try {
      $result = $this->connection->select('users_field_data', 'us')
      ->fields('us',['status'])
      ->execute();
      $record = $result->fetchAll();
      $row = count($record);
      $str = "You are unique among $row users";
      return $this->t($str);
    }
    catch (\Exception $e) {
      $this->messenger()->addMessage($this->t('Select failed. Message = %message', [
        '%message' => $e->getMessage(),
      ]), 'error');
    }


  }

  public function getCurrentUserPosition() {
    $result = $this->connection->select('users_field_data', 'us')
      ->fields('us',['uid','created'])
      ->orderBy('created', 'DESC')
      ->execute();
    $record = $result->fetchAll();
    $count = 0;
    foreach ($record as $row){
      $id = $row->uid;
      $id2 = $this->getData();
      $count++;
      if($id == $id2){
        break;
      }
    };
    $str = "Your position of registration $count ";
    return $this->t($str);
  }
  public function getNode(){
    $nodes = $this->entityTypeManager->getStorage('node')
      ->loadMultiple();
    $id_node = array_rand($nodes,1);
    $node = $this->entityTypeManager->getStorage('node')->load($id_node);
    $result = $this->entityTypeManager->getViewBuilder('node')->view($node,'teaser');
    return $result;
  }
  public function getData() {
    return $this->currentUser->id();
  }
  public function getUserData() {
    return [
      'email' => $this->currentUser->getEmail(),
      'name' => $this->currentUser->getAccountName(),
      ];
  }


}
