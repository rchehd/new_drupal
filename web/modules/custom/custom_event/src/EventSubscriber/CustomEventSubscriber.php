<?php

namespace Drupal\custom_event\EventSubscriber;

use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\custom_event\Event\CustomEvent;
use Drupal\custom_event\Event\CustomEventReporter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Subscribe to CustomEvents::NODE_SAVE_REPORT events and react to new reports.
 */
class CustomEventSubscriber implements EventSubscriberInterface {

  use StringTranslationTrait;
  use MessengerTrait;

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    $events[CustomEvent::NODE_SAVE_REPORT][] = ['getReport'];
    return $events;
  }

  /**
   * Handle incidents not handled by the other handlers.
   *
   * @param \Drupal\custom_event\Event\CustomEventReporter $event
   *   The event object containing the incident report.
   */
  public function getReport(CustomEventReporter $event) {
    $this->messenger()->addStatus($this->t('@type : @title saved!', [
      '@type' => $event->getType(),
      '@title' => $event->getTitle(),
    ]));
    $event->stopPropagation();
  }

}
