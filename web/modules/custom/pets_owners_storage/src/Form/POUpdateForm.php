<?php
namespace Drupal\pets_owners_storage\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\pets_owners_storage\Form\PODeleteForm;
use Drupal\pets_owners_storage\PetsOwnersRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;


class POUpdateForm extends FormBase{
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
  /**
   * @return string
   */
  public function getFormId() {
    return 'pets_owners_update_form';
  }

  /**
   *
   */
  public function buildForm(array $form, FormStateInterface $form_state,$data = NULL) {
    $this->id = $data;
    $entries = $this->repository->loadFromId($this->id);
    $rows=[];
    foreach ($entries as $entry) {
      // Sanitize each entry.
      $rows[] = array_map('Drupal\Component\Utility\Html::escape', (array) $entry);
    }
    $arr=$rows[0];
    $form['description'] = [
      '#type' => 'item',
      '#markup' => $this->t('Change your data here.'),
    ];

    // Name.
    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name'),
      '#description' => $this->t('Name must be at least 5 characters in length.'),
      '#required' => TRUE,
      '#default_value' => $arr['name'],
    ];

    // Gender.
    $form['settings']['gender'] = [
      '#type' => 'radios',
      '#title' => $this->t('Gender'),
      '#options' => [
        'Male' => $this->t('Male'),
        'Female' => $this->t('Female'),
        'Unknown' => $this->t('Unknown'),
      ],
      '#default_value' => $arr['gender'],
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
      '#empty_option' => $arr['prefix'],

    ];

    // Form age.
    $form['age'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Age'),
      '#description' => $this->t('Your age must be numeric or text.'),
      '#required' => TRUE,
      '#default_value' => $arr['age'],
    ];

    // Parent form.
    $form['parent'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Parents'),
    ];

    $form['parent']['mother'] = [
      '#type' => 'textfield',
      '#title' => $this->t("Mother's name"),
      '#default_value' => $arr['mother_name'],
    ];

    $form['parent']['father'] = [
      '#type' => 'textfield',
      '#title' => $this->t("Father's name"),
      '#default_value' => $arr['father_name'],
    ];

    // Have you some pets?.
    $form['have_pets'] = [
      '#type' => 'checkbox',
      '#title' => 'Have you some pets?',
      '#default_value' => FALSE,
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
      '#default_value' => $arr['name_of_pets'],
    ];

    // Email.
    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email'),
      '#required' => TRUE,
      '#default_value' => $arr['email'],
    ];

    // Button.
    $form['button'] = [
      '#type' => 'submit',
      '#value' => 'Update',
    ];

    $form['button_delete'] = [
      '#type' => 'link',
      '#title' => 'Delete',
      '#url'=>Url::fromRoute('pets_owners_storage.delete',['test_param'=>$this->id]),
      '#options' => [
        'attributes' => [
          'class' => ['use-ajax','button'],
          'data-dialog-type' => 'modal',
        ]
      ],
      ];



    return $form;
  }

  /**
   * Update user data.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

      $entry = [
        'id' => $this->id,
        'name' => $form_state->getValue('name'),
        'gender' => $form_state->getValue('gender'),
        'prefix' => $form_state->getValue('prefix'),
        'age' => $form_state->getValue('age'),
        'mother_name' => $form_state->getValue('mother'),
        'father_name' => $form_state->getValue('father'),
        'name_of_pets' => $form_state->getValue('have'),
        'email' => $form_state->getValue('email'),

      ];
      $return = $this->repository->update($entry);
      if ($return) {
        $this->messenger()
          ->addMessage($this->t('Congratulations! You successfully update your data!'));
        $form_state->setRedirect('pets_owners_storage.list');
      }

  }
  /**
   * Validate form.
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




}
