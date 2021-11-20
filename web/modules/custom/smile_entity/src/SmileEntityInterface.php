<?php
namespace Drupal\smile_entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;
use Drupal\Core\Entity\EntityChangedInterface;

/**
 * @inheritDoc
 */
interface SmileEntityInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

}
