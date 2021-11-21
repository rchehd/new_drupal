<?php

namespace Drupal\login_only_mode\EventSubscriber;

use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\State\StateInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Subscriber to check login after enable login mode.
 */
class CheckLoginStatusSubscriber implements EventSubscriberInterface {

  /**
   * The state.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected StateInterface $state;

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
                              RouteMatchInterface $route,
                              StateInterface $state) {
    $this->account = $currentUser;
    $this->route = $route;
    $this->messenger = $messenger;
    $this->state = $state;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = ['checkLoginMode'];
    return $events;
  }

  /**
   * Check and redirect if login mode is enable.
   */
  public function checkLoginMode(RequestEvent $event) {
    if ($this->state->get('login_only_mode') == 1) {
      $routes1 = [
        'user.login',
        'user.reset.login',
        'user.reset',
        'user.reset.form',
        'user.pass',
        'user.register',
      ];
      $routes2 = [
        'user.page',
        'entity.user.canonical',
        'entity.user.edit_form',
        'user.logout',
        'contact_module.contact_form',
        'contact_module.contact_list',
        'login_only_mode.settings_form',
      ];
      // For Anonymous user - redirect on login page.
      if ($this->account->isAnonymous() && !in_array($this->route->getRouteName(), $routes1)) {
        $event->setResponse(new RedirectResponse('/user/login', 302));
      }
      // For Authenticated user, except Administrator.
      if ($this->account->isAuthenticated() && !in_array('administrator', $this->account->getRoles()) && !in_array($this->route->getRouteName(), $routes2)) {
        $url = '/user/' . $this->account->id();
        $event->setResponse(new RedirectResponse($url, 302));
      }
    }
  }

}
