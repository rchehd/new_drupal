<?php

namespace Drupal\my_field\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Google_Client;
use Google_Service_YouTube;

/**
 * Plugin implementation of the 'youtube_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "youtube_formatter",
 *   label = @Translation("Show video as iframe on your page."),
 *   field_types = {
 *     "you_tube_field"
 *   }
 * )
 */

class YouTubeFormatter extends FormatterBase{

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    foreach ($items as $delta => $item) {
      $id = $item->getValue('id_video');
      $url=$id['id_video'];
      $id2=$this->getYoutubeIdFromUrl($url);
      $elements[$delta] = [
        '#type' => 'html_tag',
        '#tag' => 'iframe',
        '#attributes' => [
          'src' => "https://youtube.com/embed/$id2",
          'kind' => 'youtube#video',
          'width' => 600,
          'height' => 450,
        ]
      ];
    }

    return $elements;
  }

  /**
   * @param $url
   * Getting video ID from url.
   *
   */
  protected function getYoutubeIdFromUrl($url) {
    $parts = parse_url($url);
    if(isset($parts['query'])){
      parse_str($parts['query'], $qs);
      if(isset($qs['v'])){
        return $qs['v'];
      }else if(isset($qs['vi'])){
        return $qs['vi'];
      }
    }
    if(isset($parts['path'])){
      $path = explode('/', trim($parts['path'], '/'));
      return $path[count($path)-1];
    }
    return false;
  }

  /**
   * Test function to use Youtube Api.
   * @param $items
   */
  public function getSearchList($items) {
    $elements=[];
    $developKey = 'AIzaSyDMdsIAzIJButzPMSTsogKlwr5zq7OkdeA';
    $client = new Google_Client();
    $client->setDeveloperKey($developKey);
    $youtube = new Google_Service_YouTube($client);
    foreach ($items as $delta => $item) {
      $id = $item->getValue('id_video');
      $id2 = $id['id_video'];
      $elements[$delta] = $id2;
    }
    $searchResponse = $youtube->search->listSearch('id,snippet', [
      'q' => $elements,
      'maxResults' => 10,
    ]);
  }

}
