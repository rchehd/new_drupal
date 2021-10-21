<?php

namespace Drupal\my_field\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\field_example\Plugin\Field\FieldWidget\TextWidget;

/**
 * Plugin implementation of the 'youtube_widget' widget.
 *
 * @FieldWidget(
 *   id = "youtube_widget",
 *   module = "my_fild",
 *   label = @Translation("Enter id of video."),
 *   field_types = {
 *     "you_tube_field"
 *   }
 * )
 */

class MyYoutubeWidget extends TextWidget{
  /**
   * {@inheritDoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $value = isset($items[$delta]->id_video) ? $items[$delta]->id_video : '';
    $element += array(
      '#type' => 'textfield',
      '#size' => 32,
      '#default_value' => $value,
      '#placeholder' => 'https://www.youtube.com/',
    );
    return  ['id_video' => $element];
  }
}


