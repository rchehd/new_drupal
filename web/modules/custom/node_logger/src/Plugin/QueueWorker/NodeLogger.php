<?php

namespace Drupal\node_logger\Plugin\QueueWorker;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A report worker.
 *
 * @QueueWorker(
 *   id = "NodeLoggerWorker",
 *   title = @Translation("New node queue"),
 *   cron = {"time" = 10}
 * )
 */
class NodeLogger extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  use StringTranslationTrait;
  use MessengerTrait;

  /**
   * LoggerFactory.
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
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerChannelFactor
   *   The logger factory.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              LoggerChannelFactoryInterface $loggerChannelFactor) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
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
      $container->get('logger.factory')
    );

  }

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    $this->messenger()->addMessage(
      $this->t('User @username should be notified about new node ‘@node_title[@node_id]', [
        '@username' => $data->user,
        '@node_title' => $data->title,
        '@node_id' => $data->nid,
      ])
    );
    // $this->loggerChannelFactor->get('node_logger')->info('User @username should be notified about new node @node_title[@node_id]', [
    //   '@username' => $data->user,
    //   '@node_title' => $data->title,
    //   '@node_id' => $data->nid,
    // ]);
    $this->loggerChannelFactor->get('node_logger')->notice('User @username should be notified about new node @node_title[@node_id]', [
      '@username' => $data->user,
      '@node_title' => $data->title,
      '@node_id' => $data->nid,
    ]);

  }

}
