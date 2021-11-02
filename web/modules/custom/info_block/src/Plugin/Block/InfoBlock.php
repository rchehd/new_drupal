<?php
namespace Drupal\info_block\Plugin\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\my_service\CustomService;
use Psr\Container\ContainerInterface;

/**
 * @Block(
 *   id = "info_block",
 *   admin_label = @Translation("Info")
 * )
 */
class InfoBlock extends BlockBase implements ContainerFactoryPluginInterface{

  /**
   * @var \Drupal\my_service\CustomService
   */
  protected $repository;

  /**
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Construct a new controller.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              CustomService $repository,
                              AccountInterface $currentUser) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->repository = $repository;
    $this->currentUser = $currentUser;

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
      $container->get('current_user'),
    );
  }

  /**
   * Build.
   */
  public function build() {
    $currentUser = $this->currentUser->getDisplayName();
    $node = $this->repository->getNode();
    $user_data = $this->repository->getUserData();
    $all_active = $this->repository->getAmountOfAllActive();
    $number_of_user = $this->repository->getCurrentUserPosition();
    $content = $this->t('Content for you');

    $list['info']['current _user'] = $currentUser;
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
      '#cache' => [
        'tags' => $node['#node']->getCacheTags(),
        'contexts' => ['user.roles:authenticated'],
      ],
    ];

  }



//  public function getCacheMaxAge(){
//    return 0;
//  }//end getCacheMaxAge()


}
