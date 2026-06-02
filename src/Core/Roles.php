<?php

namespace CustomPlugin\Core;

if (!defined('ABSPATH')) {
  exit;
}

class Roles
{
  const COURIER_ROLE = 'kurir';

  public static function register_roles()
  {
    add_role(
      self::COURIER_ROLE,
      'Kurir',
      array(
        'read'         => true,
        'upload_files' => true,
      )
    );
  }

  public static function remove_roles()
  {
    remove_role(self::COURIER_ROLE);
  }
}
