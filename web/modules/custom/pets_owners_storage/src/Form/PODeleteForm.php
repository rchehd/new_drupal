<?php

namespace Drupal\pets_owners_storage\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\pets_owners_storage\PetsOwnersRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;


class PODeleteForm extends ConfirmFormBase {
  use StringTranslationTrait;
  use MessengerTrait;
  /**
   * Our database repository service.
   */
  protected $repository;
  protected $id;
  public static function create(ContainerInterface $container) {
    $form = new static(
      $container->get('pets_owners_storage.repository'),
      $container->get('current_user')
    );
    // The StringTranslationTrait trait manages the string translation service
    // for us. We can inject the service here.
    $form->setStringTranslation($container->get('string_translation'));
    $form->setMessenger($container->get('messenger'));
    return $form;
  }
  /**
   * Construct the new form object.
   */
  public function __construct(PetsOwnersRepository $repository, AccountProxyInterface $current_user) {
    $this->repository = $repository;
    $this->currentUser = $current_user;
  }
  public function getQuestion() {
    return $this->t('Do you want delete data?');
  }

  public function getCancelUrl() {
    return Url::fromRoute('pets_owners_storage.list');
  }


  public function getFormId() {
    return 'po_delete_form';
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $result=$this->repository->delete($this->id);
    $this->messenger()->addMessage($this->t('Congratulations! You successfully delete your data!'));
    $form_state->setRedirect('pets_owners_storage.list');
    return ;

  }
  public function buildForm(array $form, FormStateInterface $form_state, $test_param=null) {
    $this->id = $test_param;

    return parent::buildForm($form, $form_state);
  }


}
