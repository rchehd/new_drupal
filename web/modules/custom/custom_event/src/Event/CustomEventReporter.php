<?php

namespace Drupal\custom_event\Event;

use Symfony\Contracts\EventDispatcher\Event;

/**
 *
 */
class CustomEventReporter extends Event {

  /**
   * Type of node.
   *
   * @var string
   */
  protected string $type;

  /**
   * Title of node.
   *
   * @var string
   */
  protected string $title;

  /**
   * Constructs an incident report event object.
   *
   * @param string $type
   *   The incident report type.
   * @param string $title
   *   A detailed description of the incident provided by the reporter.
   */
  public function __construct($type, $title) {
    $this->type = $type;
    $this->title = $title;
  }

  /**
   * Get the type of node.
   *
   * @return string
   *   Type of node.
   */
  public function getType(): string {
    return $this->type;
  }

  /**
   * Get the tittle of node.
   *
   * @return string
   *   Title of node.
   */
  public function getTitle(): string {
    return $this->title;
  }

}
