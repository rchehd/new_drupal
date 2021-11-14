<?php

namespace Drupal\smile_entity\Entity\Controller;

use Drupal\Component\Render\PlainTextOutput;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 */
class SmileEntityListBuilder extends EntityListBuilder {

  /**
   * The url generator.
   *
   * @var \Drupal\Core\Routing\UrlGeneratorInterface
   */
  protected $urlGenerator;

  /**
   * Date formatter.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected DateFormatterInterface $dateFormatter;

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity_type.manager')->getStorage($entity_type->id()),
      $container->get('url_generator'),
      $container->get('date.formatter'),
    );
  }

  /**
   * Constructs a new SmileTestListBuilder object.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The entity storage class.
   * @param \Drupal\Core\Routing\UrlGeneratorInterface $url_generator
   *   The url generator.
   */
  public function __construct(EntityTypeInterface $entity_type,
                              EntityStorageInterface $storage,
                              UrlGeneratorInterface $url_generator,
                              DateFormatterInterface $date_formatter) {
    parent::__construct($entity_type, $storage);
    $this->urlGenerator = $url_generator;
    $this->dateFormatter = $date_formatter;

  }

  /**
   * {@inheritdoc}
   *
   * We override ::render() so that we can add our own content above the table.
   * parent::render() is where EntityListBuilder creates the table using our
   * buildHeader() and buildRow() implementations.
   */
  public function render() {
    $build['description'] = [
      '#markup' => $this->t('Smile Entity', [
        '@adminlink' => $this->urlGenerator->generateFromRoute('smile_entity.smile_entity_settings'),
      ]),
    ];
    $build['table'] = parent::render();
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    // Enable language column and filter if multiple languages are added.
    $header = [
      'title' => $this->t('Title'),
      'author' => $this->t('Author'),
      'changed' => $this->t('Updated'),
      'created' => $this->t('Created'),
    ];
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['title']['data'] = [
      '#type' => 'link',
      '#title' => $entity->label(),
      '#url' => $entity->toUrl(),
    ];
    $row['author'] = $entity->getOwner()->get('name')->value;
    $row['changed'] = $this->dateFormatter->format($entity->getChangedTime(), 'short');
    $row['created'] = $this->dateFormatter->format($entity->get('created')->getValue()[0]['value'], 'short');
    $row['operations']['data'] = $this->buildOperations($entity);
    return $row + parent::buildRow($entity);
  }

}
