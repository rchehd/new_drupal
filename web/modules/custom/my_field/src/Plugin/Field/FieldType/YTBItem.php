<?php

namespace Drupal\my_field\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'you_tube_field' field type.
 *
 * @FieldType(
 *   id = "you_tube_field",
 *   label = @Translation("YouTube fild"),
 *   module = "my_fild",
 *   description = @Translation("For adding youtube player."),
 *   default_widget = "youtube_widget",
 *   default_formatter = "youtube_formatter"
 * )
 */
class YTBItem extends FieldItemBase{
  /**
   * @inheritDoc
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['id_video'] = DataDefinition::create('string')
      ->setLabel(t('Video ID'));
    return $properties;
  }
  /**
   * @inheritDoc
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      'columns' => [
        'id_video' => [
          'type' => 'text',
          'size' => 'tiny',
          'not null' => FALSE,
        ],
      ],
    ];
  }
  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $value = $this->get('id_video')->getValue();
    return $value === NULL || $value === '';
  }
}
