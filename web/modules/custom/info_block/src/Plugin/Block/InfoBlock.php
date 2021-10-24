<?php
namespace Drupal\info_block\Plugin\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\my_service\CustomService;
use Psr\Container\ContainerInterface;

/**
 * @Block(
 *   id = "info_block",
 *   admin_label = @Translation("Info")
 * )
 */
class InfoBlock extends BlockBase implements ContainerFactoryPluginInterface{
  protected $repository;

  /**
   * Construct a new controller.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition,CustomService $repository) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->repository = $repository;
  }
  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('my_service.custom_service'),
    );
  }
  public function build(){
    $node = $this->repository->getNode();
    $user_data = $this->repository->getUserData();
    $all_active = $this->repository->getAmountOfAllActive();
    $number_of_user = $this->repository->getCurrentUserPosition();
    $content = $this->t('Content for you');

    $list['info']['user_info'] = $user_data;
    $list['info']['all_active'] = $all_active;
    $list['info']['number_of_user'] = $number_of_user;
    $list['info']['content'] = $content;
    $list['info']['node'] = $node;

    return [
      '#theme'    => 'info_block_theme',
      '#list'     => $list,
      '#attached' => [
        'library' => ['info_block/style'],
      ],
    ];

    }


  /**
  * Disable block cache.
  *
  * @return integer
  *   Cache age life time.
  */
  public function getCacheMaxAge(){
    return 0;
  }//end getCacheMaxAge()


}
