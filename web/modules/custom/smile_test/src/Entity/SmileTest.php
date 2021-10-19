<?php

namespace Drupal\smile_test\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\smile_test\ContactInterface;
use Drupal\smile_test\SmileTestInterface;
use Drupal\user\UserInterface;
use Drupal\Core\Entity\EntityChangedTrait;

/**
 * @ContentEntityType(
 *   id = "smile_test",
 *   label = @Translation("Smile test entity"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\smile_test\Entity\Controller\SmileListBuilder",
 *     "form" = {
 *       "default" = "Drupal\smile_test\Form\SmileTestForm",
 *       "delete" = "Drupal\smile_test\Form\SmileTestDeleteForm",
 *     },
 *     "access" = "Drupal\smile_test\SmileTestAccessControlHandler",
 *   },
 *   list_cache_contexts = { "user" },
 *   base_table = "smile_test",
 *   admin_permission = "administer smile test entity",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "client_name",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/smile_test/{smile_test}",
 *     "edit-form" = "/smile_test/{smile_test}/edit",
 *     "delete-form" = "/client/{smile_test}/delete",
 *     "collection" = "/smile_test/list"
 *   },
 *   field_ui_base_route = "smile_test.smile_test_settings",
 * )
 *
 */
class SmileTest extends ContentEntityBase implements SmileTestInterface {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   *
   * When a new entity instance is added, set the user_id entity reference to
   * the current user as the creator of the instance.
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += [
      'user_id' => \Drupal::currentUser()->id(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('user_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {


    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setDescription(t('The ID of the Client.'))
      ->setReadOnly(TRUE);


    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The UUID of the Client.'))
      ->setReadOnly(TRUE);

    $fields['client_name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Client name'))
      ->setDescription(t('The name of Client.'))
      ->setSettings([
        'max_length' => 255,
        'text_processing' => 0,
      ])
      // Set no default value.
      ->setDefaultValue(NULL)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -6,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -6,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['prefered_brand'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Prefered Brand'))
      ->setDescription(t('The prefered brand of Client.'))
      ->setSettings([
        'allowed_values' => [
          'Emoji' => 'Emoji',
          'Logo' => 'Logo',
          'Smile' => 'Smile',
        ],
      ])
      // Set the default value of this field to 'user'.
      ->setDefaultValue(NULL)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -2,
      ])
      ->setDisplayOptions('form', [
        'type' => 'options_select',
        'weight' => -2,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['products_owned_count'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Products owned count'))
      ->setDescription(t('The count of products of Client.'))
      ->setSettings([
        'unsigned' => TRUE
      ])
      // Set no default value.
      ->setDefaultValue(NULL)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -6,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -6,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['registration_date'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Registration date'))
      ->setDescription(t('The date of registration of Client.'))
      ->setReadOnly(TRUE);

    $fields['langcode'] = BaseFieldDefinition::create('language')
      ->setLabel(t('Language code'))
      ->setDescription(t('The language code of Client.'));

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    return $fields;
  }

}
