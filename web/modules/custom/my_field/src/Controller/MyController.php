<?php
namespace Drupal\my_field\Controller;

use Drupal\Core\Controller\ControllerBase;

class MyController extends ControllerBase {
  /**
   * {@inheritdoc}
   */
  protected function getModuleName() {
    return 'field_example';
  }

  public function printDescription() {
    return [
      '#markup'=>t('This is my_module'),
    ];
  }

}
