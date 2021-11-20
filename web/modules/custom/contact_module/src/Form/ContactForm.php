<?php

namespace Drupal\contact_module\Form;

use Drupal\Core\Database\Connection;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form to send message.
 */
class ContactForm extends FormBase implements FormInterface, ContainerInjectionInterface {
  use MessengerTrait;

  /**
   * Entity type manager service.
   *
   * We'll need this service in order to get taxonomy term.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * The current user.
   *
   * We'll need this service in order to check user role.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected AccountProxyInterface $currentUser;

  /**
   * Connection to DB.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected Connection $connection;

  /**
   * Service to send email.
   *
   * @var \Drupal\Core\Mail\MailManagerInterface
   */
  protected MailManagerInterface $mailManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): ContactForm {
    $form = new static(
      $container->get('entity_type.manager'),
      $container->get('current_user'),
      $container->get('database'),
      $container->get('plugin.manager.mail'),
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
  public function __construct(EntityTypeManagerInterface $repository,
                              AccountProxyInterface $current_user,
                              Connection $database,
                              MailManagerInterface $mail_manager) {
    $this->entityTypeManager = $repository;
    $this->currentUser = $current_user;
    $this->connection = $database;
    $this->mailManager = $mail_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'contact_module_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $user_id = $this->currentUser->id();
    $user_email = $this->currentUser->getEmail();
    $treeData = $this->entityTypeManager->getStorage('taxonomy_term')->loadTree('help_message_type');
    $list_term = [];
    foreach ($treeData as $item) {
      $list_term[$item->name] = $item->name;
    }
    $form['description'] = [
      '#type' => 'item',
      '#markup' => $this->t('Send message to support.'),
    ];

    // User Id for authenticated.
    if ($user_id) {
      $form['id'] = [
        '#type' => 'label',
        '#title' => $this->t('Your ID: @id', ['@id' => $user_id]),
      ];
    }
    // Email.
    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email'),
      '#default_value' => $user_email,
      '#required' => TRUE,
    ];
    // Phone number.
    $form['phone_number'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Phone number'),
      '#description' => $this->t('Your age must be numeric or text.'),
      '#required' => TRUE,
      '#attributes' => [
        'placeholder' => '380...',
      ],
    ];

    // Message.
    $form['message'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Message'),
      '#attributes' => ['class' => ['ckeditor-toolbar-textarea']],
      '#required' => TRUE,
    ];
    // Taxonomy reference.
    $form['type'] = [
      '#type' => 'select',
      '#title' => $this->t('Theme of message:'),
      '#options' => $list_term,
      '#empty_option' => $this->t('--'),
    ];

    // Button.
    $form['button'] = [
      '#type' => 'submit',
      '#value' => 'Add',
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // $site_mail = \Drupal::config('system.site')->get('mail');
    $site_mail = $this->config('system.site')->get('mail');
    // Gather the current user so the new record has ownership.
    $account = $this->currentUser;
    try {
      $return = $this->connection->insert('contact_module')
        ->fields([
          'user_id' => $this->currentUser->id(),
          'email' => $form_state->getValue('email'),
          'phone_number' => $form_state->getValue('phone_number'),
          'message' => $form_state->getValue('message'),
          'type' => $form_state->getValue('type'),
          'created' => time(),
        ])
        ->execute();
    }
    catch (\Exception $e) {
      $this->messenger->addWarning($e);
    }
    if ($return) {
      $params = [
        'email_from' => $form_state->getValue('email'),
        'email_to' => $site_mail,
        'message' => $form_state->getValue('message'),
        'type' => $form_state->getValue('type'),
      ];
      $this->mailManager->mail(
        'contact_module',
        'contactMail',
        $site_mail,
        'en',
        $params,
        $reply = NULL,
        $send = TRUE
      );
      $this->messenger()->addMessage($this->t('Thanks for your message!'));
      $form_state->setRedirect('user.login');
    }
  }

  /**
   * Method to validate data on form.
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Validate email.
    $age = $form_state->getValue('email');
    $emailPattern = "/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/i";
    $valid = preg_match($emailPattern, $age);
    if ($valid == FALSE) {
      $form_state->setErrorByName('email', $this->t('Your email is not correct!'));
    }
    // Validate type of message.
    if ($form_state->getValue('type') == '') {
      $form_state->setErrorByName('type', $this->t('Message theme must be selected!'));
    }
    // Validate number on length.
    if (strlen($form_state->getValue('phone_number')) != 12) {
      $form_state->setErrorByName('phone_number', $this->t('Number must have 12 numbers!'));
    }
    // Validate number on numeric.
    if (!is_numeric($form_state->getValue('phone_number'))) {
      $form_state->setErrorByName('phone_number', $this->t('Number must be numeric!'));
    }
  }

}
