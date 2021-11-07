<?php

namespace Drupal\custom_cron\Plugin\QueueWorker;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A report worker.
 *
 * @QueueWorker(
 *   id = "CustomWorker",
 *   title = @Translation("Custom crone queue"),
 *   cron = {"time" = 10}
 * )
 */
class CustomWorker extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  use StringTranslationTrait;
  use MessengerTrait;

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * Logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected LoggerChannelFactoryInterface $loggerChannelFactor;

  /**
   * ReportWorkerBase constructor.
   *
   * @param array $configuration
   *   The configuration of the instance.
   * @param string $plugin_id
   *   The plugin id.
   * @param mixed $plugin_definition
   *   The plugin definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entityTypeManager.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerChannelFactor
   *   Logger.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              EntityTypeManagerInterface $entityTypeManager,
                              LoggerChannelFactoryInterface $loggerChannelFactor) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entityTypeManager;
    $this->loggerChannelFactor = $loggerChannelFactor;

  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('logger.factory'),
    );

  }

  /**
   * {@inheritdoc}
   */
  public function processItem($item) {
    $config = \Drupal::configFactory()->get('custom_cron.settings');
    if ($config->get('disable') == 0) {
      $node = $this->entityTypeManager->getStorage('node')->load($item->nid);
      if (!$node->isPublished() == FALSE && $node instanceof NodeInterface) {
        $this->messenger()->addMessage(
          $config->get('message') . ' ' . $item->nid
        );
        $node->setUnpublished(TRUE);
        $node->save();
      }

    }

  }

}
