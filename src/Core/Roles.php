<?php

namespace CustomPlugin\Core;

if (!defined('ABSPATH')) {
  exit;
}

class Roles
{
  const COURIER_ROLE = 'kurir';
  const CUSTOMER_ROLE = 'customer';

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

    add_role(
      self::CUSTOMER_ROLE,
      'Customer',
      array(
        'read' => true,
      )
    );
  }

  public static function remove_roles()
  {
    remove_role(self::COURIER_ROLE);
    remove_role(self::CUSTOMER_ROLE);
  }
}
