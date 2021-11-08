<?php

namespace Drupal\custom_cron\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 *
 */
class LoginSettingsForm extends FormBase {
  use StringTranslationTrait;
  use MessengerTrait;

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'login_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $val = \Drupal::state()->get('login_message');
    $form['description'] = [
      '#type' => 'item',
      '#markup' => $this->t('Text for login message'),
    ];


    $form['login_message'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Input your custom text'),
      '#default_value' => $val,
      '#required' => TRUE,
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $res = \Drupal::state()->set('login_message', $form_state->getValue('login_message'));
    $this->messenger()->addMessage($this->t('The configuration options have been saved.'));
  }

}
