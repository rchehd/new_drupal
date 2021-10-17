<?php
namespace Drupal\services_block\Plugin\Block;
use Drupal\Core\Block\BlockBase;

/**
 * @Block(
 *   id = "services_block",
 *   admin_label = @Translation("Services")
 *)
 */
class ServisecBlock extends BlockBase {

  public function build() {
    $query = \Drupal::entityQuery('node');
    $entity_ids = $query->condition('type', 'servises','=')
      ->range(0, 3)
      ->execute();
    $entity_type_manager = \Drupal::entityTypeManager();
    $node_view_builder = $entity_type_manager->getViewBuilder('node');

    foreach ($entity_ids as $id) {
      $node = $entity_type_manager->getStorage('node')->load($id);
      $list['servises'][$node->id()] = $node_view_builder->view($node, 'teaser');
    }

    $d = 'd';

    return [
      '#theme' => 'services_theme',
      '#list' => $list,
      '#attached'=>[
        'library'=>['services_block/table']
      ],
    ];
  }

  /**
   * Disable block cache.
   *
   * @return int
   *   Cache age life time.
   */
  public function getCacheMaxAge() {
    return 0;
  }

}
