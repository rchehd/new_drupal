<?php

namespace Drupal\pets_owners_storage\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

class AjaxExampleForm extends FormBase{

  public function getFormId() {
    return 'ajax_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['description'] = [
      '#type' => 'item',
      '#markup' => $this->t('Choose what do you want to see'),
    ];

    $form['choose'] = [
      '#title' => $this->t('Select item'),
      '#type' => 'select',
      '#empty_option' => $this->t('-select-'),
      '#options' => [
        'link' => $this->t('link'),
        'input' => $this->t('input'),
      ],
      '#ajax' => [
        'callback' => '::changeForm',
        'wrapper' => 'chosen-wrapper',
      ],
    ];
    $form['chosen_wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'chosen-wrapper'],
    ];

    $chosen_item=$form_state->getValue('choose');
    if(!empty($chosen_item)) {
      switch ($chosen_item) {
        case 'input':
          $form['chosen_wrapper']['chosen'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Input something'),
          ];
          break;
        case 'link':
          $form['chosen_wrapper']['chosen'] = [
            '#type' => 'link',
            '#title' => $this->t('Google'),
            '#url' => Url::fromUri('https://www.google.com'),
            '#attributes' => ['target' => '_blank'],
          ];
          break;

      }
    }
    $form['button'] = [
      '#type' => 'actions',
      'submit' => [
        '#type' => 'submit',
        '#value' => $this->t('Ok'),
      ],
    ];
    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    if (!empty($form_state->getValue('chosen'))) {
      $this->messenger()
        ->addMessage('Your input text: ' . $form_state->getValue('chosen'));
    }
  }
  public function changeForm(array $form, FormStateInterface $form_state) {
    return $form['chosen_wrapper'];
  }


}
