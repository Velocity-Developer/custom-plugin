<?php

/**
 * Admin page template
 */

// Prevent direct access
if (!defined('ABSPATH')) {
  exit;
}

?>

<div class="wrap">
  <h1 class="wp-heading-inline mb-6"><?php echo esc_html(get_admin_page_title()); ?></h1>

  <div class="custom-plugin-container max-w-4xl mt-5">

    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
      <h2 class="text-xl font-semibold mb-4 border-b pb-2 text-gray-800">Informasi Sistem</h2>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

        <!-- PHP Version -->
        <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
          <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-1">Versi PHP</h3>
          <p class="text-2xl font-bold text-gray-800"><?php echo PHP_VERSION; ?></p>
        </div>

        <!-- WordPress Version -->
        <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
          <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-1">Versi WordPress</h3>
          <p class="text-2xl font-bold text-gray-800"><?php echo get_bloginfo('version'); ?></p>
        </div>

        <!-- Server Software -->
        <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
          <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-1">Perangkat Lunak Server</h3>
          <p class="text-lg font-medium text-gray-800"><?php echo esc_html($_SERVER['SERVER_SOFTWARE']); ?></p>
        </div>

        <!-- User Agent -->
        <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
          <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-1">User Agent</h3>
          <p class="text-sm text-gray-600"><?php echo esc_html($_SERVER['HTTP_USER_AGENT']); ?></p>
        </div>

      </div>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
      <h2 class="text-xl font-semibold mb-4 border-b pb-2 text-gray-800">Informasi Database</h2>
      <div class="grid grid-cols-1 gap-4">
        <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
          <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-1">Host Database</h3>
          <p class="text-sm font-mono text-gray-700 break-all"><?php echo DB_HOST; ?></p>
        </div>
        <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
          <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-1">Nama Database</h3>
          <p class="text-lg font-bold text-gray-800"><?php echo DB_NAME; ?></p>
        </div>
      </div>
    </div>

  </div>
</div>