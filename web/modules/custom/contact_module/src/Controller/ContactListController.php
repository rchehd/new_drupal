<?php

namespace Drupal\contact_module\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller to message list.
 */
class ContactListController extends ControllerBase {

  /**
   * DB connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected Connection $connection;

  /**
   * Current User.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected AccountProxyInterface $curUs;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): ContactListController {
    $controller = new static(
      $container->get('database'),
      $container->get('current_user'),
    );
    $controller->setStringTranslation($container->get('string_translation'));
    return $controller;
  }

  /**
   * Construct a new controller.
   */
  public function __construct(Connection $database, AccountProxyInterface $curUs) {
    $this->connection = $database;
    $this->curUs = $curUs;
  }

  /**
   * Return page with data table.
   */
  public function entryList(): array {
    $content = [];
    $content['message'] = [
      '#markup' => $this->t('Messages of @user.', ['@user' => $this->curUs->getDisplayName()]),
    ];
    $rows = [];
    // Headers of table.
    $headers = [
      $this->t('Message theme'),
      $this->t('Body'),
      $this->t('Date'),
    ];
    // Load data from db.
    $data = $this->connection->select('contact_module', 'cm')
      ->condition('user_id', $this->curUs->id(), '=')
      ->fields('cm', ['type', 'message', 'created'])
      ->orderBy('created', 'DESC')
      ->execute()
      ->fetchAll();
    $rows = [];

    foreach ($data as $entry) {
      // Sanitize each entry.
      $rows[] = array_map('Drupal\Component\Utility\Html::escape', (array) $entry);
    }

    for ($i = 0; $i < count($rows); $i++) {
      $rows[$i]['created'] = date('Y-m-d H:i:s', $rows[$i]['created']);
    }

    $content['table'] = [
      '#type' => 'table',
      '#header' => $headers,
      '#rows' => $rows,
      '#empty' => $this->t('No entries available.'),
    ];
    return $content;
  }


}
