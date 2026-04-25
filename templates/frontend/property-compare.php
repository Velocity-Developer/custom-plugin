<?php

/**
 * Frontend Template: Property Compare
 *
 * @var array $properties
 * @var int $first_id
 * @var int $second_id
 * @var array $compare_rows
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="custom-property-compare">
    <style>
        .custom-property-compare {
            margin: 24px 0;
        }

        .custom-property-compare__form {
            display: grid;
            gap: 16px;
            grid-template-columns: repeat(2, minmax(0, 1fr)) auto;
            align-items: end;
            margin-bottom: 24px;
        }

        .custom-property-compare__field label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .custom-property-compare__field select {
            width: 100%;
            min-height: 42px;
            padding: 8px 12px;
        }

        .custom-property-compare__button {
            min-height: 42px;
            padding: 8px 18px;
            border: 0;
            background: #1f2937;
            color: #fff;
            cursor: pointer;
            font-weight: 600;
        }

        .custom-property-compare__notice {
            padding: 14px 16px;
            border-left: 4px solid #1f2937;
            background: #f8fafc;
        }

        .custom-property-compare__table-wrap {
            overflow-x: auto;
        }

        .custom-property-compare__table {
            width: 100%;
            border-collapse: collapse;
        }

        .custom-property-compare__table th,
        .custom-property-compare__table td {
            padding: 14px;
            border: 1px solid #e5e7eb;
            text-align: left;
            vertical-align: top;
        }

        .custom-property-compare__table th {
            background: #f8fafc;
            font-weight: 700;
        }

        .custom-property-compare__image {
            display: block;
            width: 100%;
            max-width: 260px;
            height: auto;
        }

        @media (max-width: 720px) {
            .custom-property-compare__form {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <?php if (empty($properties)) : ?>
        <div class="custom-property-compare__notice">
            <?php esc_html_e('Belum ada properti yang tersedia untuk dibandingkan.', 'custom-plugin'); ?>
        </div>
    <?php else : ?>
        <form class="custom-property-compare__form" method="get" action="<?php echo esc_url(get_permalink()); ?>">
            <div class="custom-property-compare__field">
                <label for="compare_property_1"><?php esc_html_e('Properti Pertama', 'custom-plugin'); ?></label>
                <select id="compare_property_1" name="compare_property_1">
                    <option value=""><?php esc_html_e('Pilih properti', 'custom-plugin'); ?></option>
                    <?php foreach ($properties as $property) : ?>
                        <option value="<?php echo esc_attr($property->ID); ?>" <?php selected($first_id, $property->ID); ?>>
                            <?php echo esc_html(get_the_title($property)); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="custom-property-compare__field">
                <label for="compare_property_2"><?php esc_html_e('Properti Kedua', 'custom-plugin'); ?></label>
                <select id="compare_property_2" name="compare_property_2">
                    <option value=""><?php esc_html_e('Pilih properti', 'custom-plugin'); ?></option>
                    <?php foreach ($properties as $property) : ?>
                        <option value="<?php echo esc_attr($property->ID); ?>" <?php selected($second_id, $property->ID); ?>>
                            <?php echo esc_html(get_the_title($property)); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button class="custom-property-compare__button" type="submit">
                <?php esc_html_e('Bandingkan', 'custom-plugin'); ?>
            </button>
        </form>

        <?php if ($first_id && $second_id && $first_id === $second_id) : ?>
            <div class="custom-property-compare__notice">
                <?php esc_html_e('Pilih dua properti yang berbeda untuk dibandingkan.', 'custom-plugin'); ?>
            </div>
        <?php elseif ($first_id && $second_id && !empty($compare_rows)) : ?>
            <div class="custom-property-compare__table-wrap">
                <table class="custom-property-compare__table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Detail', 'custom-plugin'); ?></th>
                            <th>
                                <a href="<?php echo esc_url(get_permalink($first_id)); ?>">
                                    <?php echo esc_html(get_the_title($first_id)); ?>
                                </a>
                            </th>
                            <th>
                                <a href="<?php echo esc_url(get_permalink($second_id)); ?>">
                                    <?php echo esc_html(get_the_title($second_id)); ?>
                                </a>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($compare_rows as $row) : ?>
                            <tr>
                                <th scope="row"><?php echo esc_html($row['label']); ?></th>
                                <td><?php echo wp_kses_post($row['first']); ?></td>
                                <td><?php echo wp_kses_post($row['second']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php elseif ($first_id || $second_id) : ?>
            <div class="custom-property-compare__notice">
                <?php esc_html_e('Pilih dua properti untuk melihat perbandingan.', 'custom-plugin'); ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>