<?php

namespace Drupal\custom_event\Event;

/**
 * Defines events for the custom_event module.
 */
final class CustomEvent {

  /**
   * Name of the event fired when a new incident is reported.
   */
  const NODE_SAVE_REPORT = 'custom_event.node_save';

}
