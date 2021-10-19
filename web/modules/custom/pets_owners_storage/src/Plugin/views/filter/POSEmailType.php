<?php
namespace Drupal\pets_owners_storage\Plugin\views\filter;

use Drupal\views\Plugin\views\filter\InOperator;
/**
 * Exposes log types to views module.
 *
 * @ViewsFilter("gender")
 */
class POSEmailType extends InOperator {
  /**
   * @inheritDoc
   */
  public function getValueOptions() {
    if (!isset($this->valueOptions)) {
      $this->valueOptions = _pos_get_gender2();
    }
    return $this->valueOptions;
  }
}
