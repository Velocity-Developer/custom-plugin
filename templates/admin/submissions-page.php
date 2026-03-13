<?php

/**
 * Submissions page template
 */

// Prevent direct access
if (!defined('ABSPATH')) {
  exit;
}

global $wpdb;
$table_name = $wpdb->prefix . 'custom_plugin_data';
$results = $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_at DESC");

?>

<div class="wrap">
  <h1 class="wp-heading-inline mb-6">Kiriman Formulir</h1>

  <div class="custom-plugin-container max-w-full mt-5">

    <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200">

      <?php if (empty($results)) : ?>
        <div class="p-8 text-center">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada kiriman ditemukan</h3>
          <p class="mt-1 text-sm text-gray-500">Mulai dengan mengirimkan formulir di frontend.</p>
        </div>
      <?php else : ?>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  ID
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Tanggal
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Nama
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Email
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Pesan
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <?php foreach ($results as $row) : ?>
                <tr>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    #<?php echo esc_html($row->id); ?>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($row->created_at))); ?>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    <?php echo esc_html($row->name); ?>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <a href="mailto:<?php echo esc_attr($row->email); ?>" class="text-blue-600 hover:text-blue-900"><?php echo esc_html($row->email); ?></a>
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-500">
                    <div class="max-w-xs break-words">
                      <?php echo nl2br(esc_html($row->message)); ?>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>

    </div>

  </div>
</div>