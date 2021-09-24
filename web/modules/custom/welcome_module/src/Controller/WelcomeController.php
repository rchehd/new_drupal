<?php

namespace Drupal\welcome_module\Controller;

use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Controller for custom module.
 */
class WelcomeController extends ControllerBase{

  /**
   * Return string to menu.
   */
  public function myMenu() {
    return [
      '#markup' => 'Welcome to my_menu.',
    ];
  }

  /**
   * Return string to sub menu.
   */
  public function subMenu() {
//    return [
//      '#markup' => 'Welcome to sub_menu.',
//    ];
    $response = new RedirectResponse('http://new_drupal.docker.localhost/form_to_pets_owners');
    $response->send();
    return;
  }

  /**
   * Return string on main page.
   */
  public function welcome() {
    return [
      '#markup' => 'Welcome to my Website.',
    ];
  }

  /**
   * Return string on page 'Smile_test'.
   */
  public function smileTest() {
    return [
      '#markup' => 'It is my first route ever',
    ];
  }

  /**
   * Node rendered function with node id.
   */
  public function nodeRender($nid) {
    try {
      $node = \Drupal::entityTypeManager()
        ->getStorage('node')
        ->load($nid);
      $element = \Drupal::entityTypeManager()
        ->getViewBuilder('node')
        ->view($node, 'teaser');
      $output = render($element);
      return [
        '#markup' => $output,
      ];
    }
    catch (InvalidPluginDefinitionException | PluginNotFoundException $e) {
      return $e->getMessage();
    }
  }

  /**
   * Function to control access to page 'node_render/{nid}'.
   */
  public function hasAccessOfSuperuser() {
    return \Drupal::currentUser()->hasPermission('superuser');
  }

}
