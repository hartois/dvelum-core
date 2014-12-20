<?php return array (
  'table' => 'menu',
  'engine' => 'MyISAM',
  'rev_control' => false,
  'save_history' => true,
  'link_title' => '',
  'system' => true,
  'fields' => 
  array (
    'code' => 
    array (
      'type' => '',
      'unique' => 'code',
      'db_isNull' => false,
      'required' => true,
      'db_type' => 'varchar',
      'db_default' => '',
      'db_len' => 255,
      'is_search' => true,
      'allow_html' => false,
    ),
    'title' => 
    array (
      'type' => '',
      'unique' => '',
      'db_isNull' => false,
      'required' => true,
      'db_type' => 'varchar',
      'db_default' => '',
      'db_len' => 255,
      'is_search' => true,
      'allow_html' => false,
    ),
  ),
  'indexes' => 
  array (
    'code' => 
    array (
      'columns' => 
      array (
        0 => 'code',
      ),
      'unique' => true,
      'fulltext' => false,
      'PRIMARY' => false,
    ),
  ),
  'connection' => 'default',
  'locked' => false,
  'readonly' => false,
  'disable_keys' => false,
); 