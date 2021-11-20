<?php

namespace Drupal\custom_cron\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 *
 */
class CustomCronSettingsForm extends ConfigFormBase {
  use StringTranslationTrait;
  use MessengerTrait;

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return ['custom_cron.set_parameters'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'custom_cron_settings_form';
  }

  /**
   * Form for set config to cron.
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $config = \Drupal::configFactory()->get('custom_cron.set_parameters');
    $form['description'] = [
      '#type' => 'item',
      '#markup' => $this->t('Parameters for job'),
    ];

    // Period.
    $form['period'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Period(how many days ago should be lastly changed node to recognize as requested one)'),
      '#default_value' => $this->config('custom_cron.set_parameters')->get('period'),
      '#required' => TRUE,
      '#attributes' => [
        ' type' => 'number',
      ],
    ];

    // Items.
    $form['item'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Items to create'),
      '#default_value' => $this->config('custom_cron.set_parameters')->get('items'),
      '#required' => TRUE,
      '#attributes' => [
        ' type' => 'number',
      ],
    ];

    // Disabled.
    $form['disable'] = [
      '#type' => 'radios',
      '#title' => $this->t('Disable'),
      '#required' => TRUE,
      '#default_value' => $this->config('custom_cron.set_parameters')->get('disable') == 0 ? 'No' : 'Yes',
      '#options' => [
        'Yes' => $this->t('Yes'),
        'No' => $this->t('No'),
      ],
    ];

    // Unpublished label.
    $form['message'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Unpublished label'),
      '#required' => TRUE,
      '#default_value' => $this->config('custom_cron.set_parameters')->get('message'),
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save configuration'),
      '#button_type' => 'primary',
    ];

    // By default, render the form using system-config-form.html.twig.
    $form['#theme'] = 'system_config_form';
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if ($form_state->getValue('period') < 1) {
      $form_state->setErrorByName('period', $this->t('Period must be more then 1 days!'));
    }

    if ($form_state->getValue('item') < 5 | $form_state->getValue('item') > 25) {
      $form_state->setErrorByName('item', $this->t('Items must be more then 5 and less then 25!'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
//    $config = \Drupal::configFactory()->getEditable('custom_cron.set_parameters');
//    $config
    $this->config('custom_cron.set_parameters')
      ->set('period', $form_state->getValue('period'))
      ->set('items', $form_state->getValue('item'))
      ->set('disable', $form_state->getValue('disable') == 'Yes' ? 1 : 0)
      ->set('message', $form_state->getValue('message'))
      ->save();
    $this->messenger()->addMessage($this->t('The configuration options have been saved.'));
  }

}
