<?php

namespace Drupal\pets_owners_storage;

use Drupal\Core\Database\Connection;
use Drupal\Core\Database\DatabaseNotFoundException;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;

/**
 *
 */
class PetsOwnersRepository {
  use MessengerTrait;
  use StringTranslationTrait;

  /**
   * The database connection.
   */
  protected $connection;

  /**
   * Construct a repository object.
   */
  public function __construct(Connection $connection, TranslationInterface $translation, MessengerInterface $messenger) {
    $this->connection = $connection;
    $this->setStringTranslation($translation);
    $this->setMessenger($messenger);
  }

  /**
   * Save an entry in the database.
   */
  public function insert(array $entry) {
    try {
      $return_value = $this->connection->insert('pets_owners_storage')
        ->fields($entry)
        ->execute();
    }
    catch (\Exception $e) {
      $this->messenger()->addMessage($this->t('Insert failed. Message = %message', [
        '%message' => $e->getMessage(),
      ]), 'error');
    }
    return $return_value ?? NULL;
  }

  /**
   * Update an entry in the database.
   */
  public function update(array $entry) {
    try {
      // Connection->update()...->execute() returns the number of rows updated.
      $count = $this->connection->update('pets_owners_storage')
        ->fields($entry)
        ->condition('id', $entry['id'])
        ->execute();
    }
    catch (\Exception $e) {
      $this->messenger()->addMessage($this->t('Update failed. Message = %message, query= %query', [
        '%message' => $e->getMessage(),
        '%query' => $e->query_string,
      ]
      ), 'error');
    }
    return $count ?? 0;
  }

  /**
   * Delete an entry from the database.
   */
  public function delete(int $id) {
    try {
      $this->connection->delete('pets_owners_storage')
        ->condition('id', $id)
        ->execute();
    }
    catch (DatabaseNotFoundException $e) {
      return $e->getMessage();
    }
  }

  /**
   * Read from the database using a filter array.
   */
  public function load(array $entry = []) {
    // Read all the fields from the dbtng_example table.
    $select = $this->connection
      ->select('pets_owners_storage')
      // Add all the fields into our select query.
      ->fields('pets_owners_storage');

    // Add each field and value as a condition to this query.
    foreach ($entry as $field => $value) {
      $select->condition($field, $value);
    }
    // Return the result in object format.
    return $select->execute()->fetchAll();
  }

  /**
   *
   */
  public function loadFromId($id) {
    // Read all the fields from the dbtng_example table.
    $select = $this->connection
      ->select('pets_owners_storage')
      ->fields('pets_owners_storage')
      ->condition('id', $id);

    // Return the result in object format.
    return $select->execute()->fetchAll();
  }

}
