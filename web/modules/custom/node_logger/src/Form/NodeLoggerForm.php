<?php

namespace Drupal\node_logger\Form;

use Drupal\Core\Batch\BatchBuilder;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Queue\QueueInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\Queue\QueueFactory;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form to process all pending items from previous queue in foreground.
 */
class NodeLoggerForm extends FormBase implements ContainerInjectionInterface {
  use StringTranslationTrait;
  use MessengerTrait;

  /**
   * Queue object.
   *
   * @var \Drupal\Core\Queue\QueueInterface
   */
  protected QueueInterface $queue;

  /**
   * Numbers of updates.
   *
   * @var int
   */
  protected int $count;

  /**
   * Batch Builder.
   *
   * @var \Drupal\Core\Batch\BatchBuilder
   */
  protected $batchBuilder;

  /**
   * LoggerFactory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected LoggerChannelFactoryInterface $loggerChannelFactor;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): NodeLoggerForm {
    return new static(
      $container->get('queue'),
      $container->get('logger.factory'),
    );
  }

  /**
   * Construct the new form object.
   */
  public function __construct(QueueFactory $queue, LoggerChannelFactoryInterface $loggerChannelFactor) {
    $this->queue = $queue->get('NodeLoggerWorker');
    $this->loggerChannelFactor = $loggerChannelFactor;
    $this->count = $this->queue->numberOfItems();
    $this->batchBuilder = new BatchBuilder();

  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'node_logger_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['description'] = [
      '#type' => 'item',
      '#markup' => $this->t('Form for pets owners'),
    ];

    // Name.
    $form['list'] = [
      '#type' => 'label',
      '#title' => $this->t('@number of logs are available', ['@number' => $this->count]),
    ];

    $form['progress_container'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'progress-container'],
    ];

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['run'] = [
      '#type' => 'submit',
      '#value' => $this->t('Run batch'),
      '#button_type' => 'primary',
    ];
    return $form;
  }

  /**
   * Get queue and write items to logger.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->batchBuilder
      ->setTitle($this->t('Processing'))
      ->setInitMessage($this->t('Initializing.'))
      ->setProgressMessage($this->t('Completed @current of @total.'))
      ->setErrorMessage($this->t('An error has occurred.'));

    $this->batchBuilder->setFile(drupal_get_path('module', 'node_logger') . '/src/Form/NodeLoggerForm.php');
    $this->batchBuilder->addOperation([$this, 'processItems'], [$this->queue]);
    $this->batchBuilder->setFinishCallback([$this, 'finished']);

    batch_set($this->batchBuilder->toArray());

  }

  /**
   * Process items function.
   */
  public function processItems($queue, array &$context) {
    // Elements per operation.
    $limit = 1;

    // Set default progress values.
    if (empty($context['sandbox']['progress'])) {
      $context['sandbox']['progress'] = 0;
      $context['sandbox']['max'] = $this->count;
      $context['sandbox']['total'] = $this->count;
    }

    $counter = 0;
    if ($queue->numberOfItems() != 0) {
      for ($i = $counter; $i < $counter + $limit; $i++) {
        if ($counter != $limit) {
          $items = $queue->claimItem();
          $this->processItem($items);
          $queue->deleteItem($items);
          $counter++;
          $context['sandbox']['progress']++;
          $context['message'] = $this->t('Now processing node :progress of :count', [
            ':progress' => $context['sandbox']['progress'],
            ':count' => $context['sandbox']['max'],
          ]);

          // Increment total processed item values. Will be used in finished
          // callback.
          $context['results']['processed'] = $context['sandbox']['progress'];
        }
      }
    }

    // If not finished all tasks, we count percentage of process. 1 = 100%.
    if ($context['sandbox']['progress'] != $context['sandbox']['max']) {
      $context['finished'] = $context['sandbox']['progress'] / $context['sandbox']['max'];
    }

  }

  /**
   * Process single item.
   */
  public function processItem($item) {
    $this->loggerChannelFactor->get('node_logger')
      ->notice('User @username should be notified about new node @node_title[@node_id]', [
        '@username' => $item->data->user,
        '@node_title' => $item->data->title,
        '@node_id' => $item->data->nid,
      ]);
  }

  /**
   * Exit from batch.
   */
  public function finished($success, $results, $operations) {
    $message = $this->t('Number of created logs: @count', [
      '@count' => $results['processed'],
    ]);

    $this->messenger()
      ->addStatus($message);
  }

}
