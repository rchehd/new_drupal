<?php
/**
 * @file
 * Provide views data for pets_owners_storage.module.
 */
/**
 * Implements hook_views_data().
 */
function pets_owners_storage_views_data() {
  $data = [];

  $data['pets_owners_storage']['table']['group'] = t('Pets owners');
  //$data['pets_owners_storage']['table']['wizard_id'] = 'pets_owners_storage';

  $data['pets_owners_storage']['table']['base'] = [
    'field' => 'id',
    'title' => t('Id owners'),
    'help' => t('Id of owners ets.'),
  ];

  $data['pets_owners_storage']['id'] = [
    'title' => t('ID'),
    'help' => t('Unique pets owners ID.'),
    'field' => [
      'id' => 'standard',
    ],
    'filter' => [
      'id' => 'numeric',
    ],
    'argument' => [
      'id' => 'numeric',
    ],
    'sort' => [
      'id' => 'standard',
    ],
  ];

  $data['pets_owners_storage']['name'] = [
    'title' => t('Name'),
    'help' => t('Pets owners name.'),
    'field' => [
      'id' => 'standard',
    ],
    'argument' => [
      'id' => 'string',
    ],
    'filter' => [
      'id' => 'string',
    ],
    'sort' => [
      'id' => 'standard',
    ],
  ];

  $data['pets_owners_storage']['gender'] = [
    'title' => t('Gender'),
    'help' => t('Pets owners gender.'),
    'field' => [
      'id' => 'standard',
    ],
    'argument' => [
      'id' => 'string',
    ],
    'filter' => [
      'id' => 'string',
    ],
    'sort' => [
      'id' => 'standard',
    ],
  ];

  $data['pets_owners_storage']['prefix'] = [
    'title' => t('Prefix'),
    'help' => t('Pets owners prefix.'),
    'field' => [
      'id' => 'standard',
    ],
    'argument' => [
      'id' => 'string',
    ],
    'filter' => [
      'id' => 'gender',
    ],
    'sort' => [
      'id' => 'standard',
    ],
  ];

  $data['pets_owners_storage']['age'] = [
    'title' => t('Age'),
    'help' => t('Pets owners age.'),
    'field' => [
      'id' => 'standard',
    ],
    'argument' => [
      'id' => 'numeric',
    ],
    'filter' => [
      'id' => 'numeric',
    ],
    'sort' => [
      'id' => 'numeric',
    ],
  ];

  $data['pets_owners_storage']['mother_name'] = [
    'title' => t('Mother name'),
    'help' => t('Pets owners mother name.'),
    'field' => [
      'id' => 'standard',
    ],
    'argument' => [
      'id' => 'string',
    ],
    'filter' => [
      'id' => 'string',
    ],
    'sort' => [
      'id' => 'standard',
    ],
  ];

  $data['pets_owners_storage']['father_name'] = [
    'title' => t('Father name'),
    'help' => t('Pets owners father name.'),
    'field' => [
      'id' => 'standard',
    ],
    'argument' => [
      'id' => 'string',
    ],
    'filter' => [
      'id' => 'string',
    ],
    'sort' => [
      'id' => 'standard',
    ],
  ];

  $data['pets_owners_storage']['name_of_pets'] = [
    'title' => t('Pets name'),
    'help' => t('Pets owners pets name.'),
    'field' => [
      'id' => 'standard',
    ],
    'argument' => [
      'id' => 'string',
    ],
    'filter' => [
      'id' => 'string',
    ],
    'sort' => [
      'id' => 'standard',
    ],
  ];

  $data['pets_owners_storage']['email'] = [
    'title' => t('Email'),
    'help' => t('Pets owners pets email.'),
    'field' => [
      'id' => 'standard',
    ],
    'argument' => [
      'id' => 'string',
    ],
    'filter' => [
      'id' => 'string',
    ],
    'sort' => [
      'id' => 'standard',
    ],
  ];

  return $data;

}
