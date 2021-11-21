<?php

namespace Drupal\login_only_mode\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\State\StateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form to enable login only mode.
 */
class LoginOnlyModForm extends FormBase {

  /**
   * The state.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected StateInterface $state;

  /**
   * Constructs a new maintenance mode service.
   *
   * @param \Drupal\Core\State\StateInterface $state
   *   The state.
   */
  public function __construct(StateInterface $state) {
    $this->state = $state;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): LoginOnlyModForm {
    $form = new static(
      $container->get('state'),
    );
    // The StringTranslationTrait trait manages the string translation service
    // for us. We can inject the service here.
    $form->setStringTranslation($container->get('string_translation'));
    $form->setMessenger($container->get('messenger'));
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'login_only_mod';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $d=0;
    $form['description'] = [
      '#type' => 'item',
      '#markup' => $this->t('Enable/Disable login mode.'),
    ];
    // Check state value.
    $value = 0;
    if ($this->state->get('login_only_mode')) {
      $value = $this->state->get('login_only_mode');
    }

    $form['value'] = [
      '#type' => 'radios',
      '#title' => t("Set enable to turn on 'Login Only Mode'"),
      '#options' => [
        1 => 'Enable',
        0 => 'Disable',
      ],
      '#default_value' => $value,
      '#required' => TRUE,
    ];

    // Button.
    $form['button'] = [
      '#type' => 'submit',
      '#value' => 'Save',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->state->set('login_only_mode', $form_state->getValue('value'));
    $this->messenger()->addMessage(
      $this->t('Login Only Mode is @value',
        ['@value' => ($form_state->getValue('value') == 0) ? 'Disable' : 'Enable']
      )
    );
  }

}
