<?php

namespace Drupal\pets_owners_form\Form;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\pets_owners_storage\PetsOwnersRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class for create form 'Pets owners form'.
 */
class PetsOwnersForm extends FormBase implements FormInterface, ContainerInjectionInterface{
  use StringTranslationTrait;
  use MessengerTrait;

  /**
   * Our database repository service.
   */
  protected $repository;

  /**
   * The current user.
   *
   * We'll need this service in order to check if the user is logged in.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;
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


  /**
   * Method to build form.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['description'] = [
      '#type' => 'item',
      '#markup' => $this->t('Form for pets owners'),
    ];

    // Name.
    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name'),
      '#description' => $this->t('Name must be at least 5 characters in length.'),
      '#required' => TRUE,
    ];

    // Gender.
    $form['settings']['gender'] = [
      '#type' => 'radios',
      '#title' => $this->t('Gender'),
      '#options' => [
        'Male'=>$this->t('Male'),
        'Female'=>$this->t('Female'),
        'Unknown'=>$this->t('Unknown'),
      ],
      '#default_value' => 2,
    ];

    // Prefix.
    $form['settings']['prefix'] = [
      '#type' => 'select',
      '#title' => $this->t('Prefix'),
      '#options' => [
        'Mr' => $this->t('Mr'),
        'Mrs' => $this->t('Mrs'),
        'Ms' => $this->t('Ms'),
      ],
      '#empty_option' => $this->t(' '),
    ];

    // Form age.
    $form['age'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Age'),
      '#description' => $this->t('Your age must be numeric or text.'),
      '#required' => TRUE,
    ];

    // Parent form.
    $form['parent'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Parents'),
    ];

    $form['parent']['mother'] = [
      '#type' => 'textfield',
      '#title' => $this->t("Mother's name"),
    ];

    $form['parent']['father'] = [
      '#type' => 'textfield',
      '#title' => $this->t("Father's name"),
    ];

    // Have you some pets?.
    $form['have_pets'] = [
      '#type' => 'checkbox',
      '#title' => 'Have you some pets?',
    ];
    $form['pets'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => 'pets',
      ],
      '#states' => [
        'invisible' => [
          ':input[name="have_pets"]' => ['checked' => FALSE],
        ],
      ],
    ];
    $form['pets']['have'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Names of your pets'),
    ];

    // Email.
    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email'),
      '#required' => TRUE,
    ];

    // Button.
    $form['button'] = [
      '#type' => 'submit',
      '#value' => 'Add',
    ];
    return $form;
  }

  /**
   * Set id to our form.
   */
  public function getFormId() {
    return 'pets_owners_form';
  }

  /**
   * Method to validate data on form.
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Validate name.
    $name = $form_state->getValue('name');
    if (strlen($name) > 100) {
      $form_state->setErrorByName('Name', $this->t('Your Name must be 100 symbols max.'));
    }
    // Validate age.
    $age = $form_state->getValue('age');
    if (!is_numeric($age)) {
      $form_state->setErrorByName('Age', $this->t('Your age must be numeric.'));
    }
    elseif ($age < 0 || $age > 120) {
      $form_state->setErrorByName('Age', $this->t('Your age should be more than 0 and less than 120'));
    }
    // Validate email.
    $age = $form_state->getValue('email');
    $emailPattern = "/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/i";
    $valid = preg_match($emailPattern, $age);
    if ($valid == FALSE) {
      $form_state->setErrorByName('Age', $this->t('Your email is not correct!'));
    }
  }
  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Gather the current user so the new record has ownership.
    $account = $this->currentUser;
    // Save the submitted entry.
    $entry = [
      'name' => $form_state->getValue('name'),
      'gender' => $form_state->getValue('gender'),
      'prefix' => $form_state->getValue('prefix'),
      'age' => $form_state->getValue('age'),
      'mother_name' => $form_state->getValue('mother'),
      'father_name' => $form_state->getValue('father'),
      'name_of_pets' => $form_state->getValue('have'),
      'email' => $form_state->getValue('email'),

    ];
    $return = $this->repository->insert($entry);
    if ($return) {
      //$this->messenger()->addMessage($this->t('Created entry @entry', ['@entry' => print_r($entry, TRUE)]));
      $this->messenger()->addMessage($this->t('Congratulations! You successfully saved your data!'));
      $form_state->setRedirect('pets_owners_storage.list');
    }
    }

}

