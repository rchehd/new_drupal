<?php
namespace Drupal\pets_owners_storage\EventSubscriber;

use Drupal\views_bulk_operations\Service\ViewsBulkOperationsActionManager;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class POSEventSubscriber implements EventSubscriberInterface{

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [];
    // The next line prevents hard dependency on VBO module.
    if (class_exists(ViewsBulkOperationsEvent::class)) {
      $events['views_bulk_operations.view_data'][] = ['provideViewData', 0];
    }
    return $events;
  }

  /**
   * Provide entity type data and entity getter to VBO.
   *
   * @param \Drupal\views_bulk_operations\ViewsBulkOperationsEvent $event
   *   The event object.
   */
  public function provideViewData(ViewsBulkOperationsEvent $event) {
    if ($event->getProvider() === 'pets_owners_storage') {
      $event->setEntityTypeIds(['node']);
//      $event->setEntityGetter([
//        'file' => drupal_get_path('module', 'pets_owners_storage') . '/src/someClass.php',
//        'callable' => '\Drupal\pets_owners_storage\someClass::getEntityFromRow',
//      ]);
    }
  }





}
