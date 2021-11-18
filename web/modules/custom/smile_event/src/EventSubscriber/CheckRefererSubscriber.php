<?php

namespace Drupal\smile_event\EventSubscriber;

use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Redirect Anonymous to login page.
 */
class CheckRefererSubscriber implements EventSubscriberInterface {

  /**
   * Current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $account;

  /**
   * Messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected MessengerInterface $messenger;

  /**
   * Route.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected RouteMatchInterface $route;

  /**
   * {@inheritdoc}
   */
  public function __construct(MessengerInterface $messenger,
                              AccountInterface $currentUser,
                              RouteMatchInterface $route) {
    $this->account = $currentUser;
    $this->route = $route;
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    $events[KernelEvents::REQUEST][] = ['checkReferer'];
    return $events;
  }

  /**
   * Check and redirect to login page.
   */
  public function checkReferer(RequestEvent $event) {

    $routes = [
      'entity.smile_entity.canonical',
    ];
    $site_url = 'http://drupal2.docker.localhost/';
    $url = $event->getRequest()->headers->get('referer');
    if ((strpos($url, $site_url) === FALSE || $url = NULL) && in_array($this->route->getRouteName(), $routes)) {
      $event->setResponse(new RedirectResponse('/user/login', 302));
    }
  }

}
