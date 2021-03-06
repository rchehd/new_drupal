<?php

namespace Drupal\pets_owners_storage\Controller;
use Drupal\Core\Controller\ControllerBase;
use Drupal\pets_owners_storage\PetsOwnersRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Link;

class PetsOwnersController extends ControllerBase {
  protected $repository;
  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $controller = new static($container->get('pets_owners_storage.repository'));
    $controller->setStringTranslation($container->get('string_translation'));
    return $controller;
  }
  /**
   * Construct a new controller.
   */
  public function __construct(PetsOwnersRepository $repository) {
    $this->repository = $repository;
  }
  /**
   * Return page with data table.
   * @return array
   */
  public function entryList() {
    $content = [];
    $content['message'] = [
      '#markup' => $this->t('List of data form database.'),
    ];
    $rows = [];
    // Headers of table.
    $headers = [
      $this->t('Id'),
      $this->t('Name'),
      $this->t('Gender'),
      $this->t('Prefix'),
      $this->t('Age'),
      $this->t('Mother_name'),
      $this->t('Father_name'),
      $this->t('name_of_pets'),
      $this->t('Email'),
      $this->t('Actions'),
    ];
    // Load data from db.
    $entries = $this->repository->load();

    foreach ($entries as $entry) {
      // Sanitize each entry.
      $rows[] = array_map('Drupal\Component\Utility\Html::escape', (array) $entry);
    }
    // Add links for 'Delete' and 'Update' data.
    for($i = 0; $i < count($rows); $i++){
      $id = $rows[$i]['id'];
      $link_1 = "<a data-dialog-type='dialog' href='/pets_owners_storage/update/$id'>Update</a>";
      $link_2 = "<a class='use-ajax' data-dialog-type = 'modal' href='/pets_owners_storage/delete/$id'>Delete</a>";
      //$link_1 = Link::createFromRoute($this->t('Update'),'pets_owners_storage.update',['data'=>$id],[])->toString();
      //$link_2 = Link::createFromRoute($this->t('Delete'),'pets_owners_storage.delete',['test_param'=>$id],)->toString();
      $link_concat = ['#markup' => $link_1 . ' | ' . $link_2];
      $rows[$i]['actions']=\Drupal::service('renderer')->render($link_concat);
    }
    foreach ($rows as $row){}
    $content['table'] = [
      '#type' => 'table',
      '#header' => $headers,
      '#rows' => $rows,
      '#empty' => $this->t('No entries available.'),
    ];
    return $content;
  }

  /**
   * Test module.
   */
  public function testCallback($test_param) {
    $result=$this->repository->delete($test_param);
    return $this->entryList();
  }

}
