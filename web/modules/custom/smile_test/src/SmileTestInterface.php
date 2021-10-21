<?php

namespace Drupal\smile_test;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;
use Drupal\Core\Entity\EntityChangedInterface;

/**
 * @inheritDoc
 */
interface SmileTestInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

}
