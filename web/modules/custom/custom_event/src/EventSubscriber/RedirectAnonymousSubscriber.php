<?php

namespace Drupal\custom_event\EventSubscriber;

use Drupal\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Redirect Anonymous to login page.
 */
class RedirectAnonymousSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    $events[KernelEvents::REQUEST][] = ['checkAuthStatus'];
    return $events;
  }

  /**
   * Check and redirect to login page.
   */
  public function checkAuthStatus(Event $event) {
    if (
      \Drupal::currentUser()->isAnonymous() &&
      \Drupal::routeMatch()->getRouteName() != 'user.login'
    ) {
      $response = new RedirectResponse('/user/login', 302);
      $response->send();
    }
  }

}
