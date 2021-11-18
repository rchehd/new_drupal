<?php

namespace Drupal\smile_block\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @Block(
 *   id = "smile_block",
 *   admin_label = @Translation("Smile entity list")
 * )
 */
class SmileBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * Current User.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected AccountProxyInterface $currentUser;

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    $storage = $this->entityTypeManager->getStorage('smile_entity');
    $config = $this->getConfiguration();
    // If config is empty.
    $count = $config['number_entities'] ?? 10;
    // Query to get all needs entity.
    $query = $this->entityTypeManager->getStorage('smile_entity')->getQuery();
    $roles = $this->currentUser->getRoles();
    $id = $query
      ->condition('access_roles', $roles, 'IN')
      ->sort("created", "DESC")
      ->range(0, $count)
      ->execute();
    $list = [];
    $smile_entities = $storage->loadMultiple($id);
    // Get view of entities.
    foreach ($smile_entities as $se) {
      $list[$se->id()] = $this->entityTypeManager->getViewBuilder('smile_entity')
        ->view($se, 'Full');
    }
    return [
      '#theme'    => 'smile_block',
      '#list'     => $list,
      '#cache' => [
        'tags' => ['create_smile_entity'],
        'contexts' => ['user.roles'],
      ],
    ];
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
      $container->get('current_user'),
    );
  }

  /**
   * Construct a new controller.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              EntityTypeManagerInterface $entityTypeManager,
                              AccountProxyInterface $currentUser) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entityTypeManager;
    $this->currentUser = $currentUser;
  }

  /**
   * Add field to block form.
   */
  public function blockForm($form, FormStateInterface $form_state): array {
    $form = parent::blockForm($form, $form_state);
    $config = $this->getConfiguration();
    $form['number_entities'] = [
      '#type' => 'number',
      '#title' => $this->t('Number of smile entity'),
      '#default_value' => $config['number_entities'] ?? 0,
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $values = $form_state->getValues();
    $this->configuration['number_entities'] = $values['number_entities'];
  }

  /**
   * {@inheritdoc}
   */
  public function blockValidate($form, FormStateInterface $form_state) {
    if ($form_state->getValue('number_entities') <= 0) {
      $form_state->setErrorByName('number_entities', $this->t('Number of entities must be more then 0.'));
    }
  }

}
