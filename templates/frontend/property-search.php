<?php

/**
 * Frontend Template: Property Search
 *
 * @var string $result_url
 * @var array $locations
 * @var array $property_types
 * @var array $property_projects
 * @var array $price_ranges
 * @var string $selected_location
 * @var string $selected_type
 * @var string $selected_project
 * @var string $selected_price
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="custom-property-search">
    <style>
        .custom-property-search {
            width: 100%;
            margin: 24px 0;
        }

        .custom-property-search__form {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr)) 78px;
            align-items: center;
            min-height: 90px;
            padding: 16px 20px 16px 34px;
            border-radius: 999px;
            background: #fff;
            box-shadow: 0 16px 38px rgba(15, 23, 42, 0.12);
        }

        .custom-property-search__field {
            min-width: 0;
            padding: 0 34px;
            border-right: 1px solid #e6e6e6;
        }

        .custom-property-search__field:first-child {
            padding-left: 10px;
        }

        .custom-property-search__label {
            display: block;
            margin-bottom: 8px;
            color: #252525;
            font-size: 16px;
            font-weight: 700;
            line-height: 1.2;
        }

        .custom-property-search__select-wrap {
            position: relative;
        }

        .custom-property-search__select {
            width: 100%;
            min-height: 28px;
            padding: 0 28px 0 0;
            border: 0;
            border-radius: 0;
            background: transparent;
            color: #232323;
            font-size: 17px;
            line-height: 1.25;
            appearance: none;
            cursor: pointer;
        }

        .custom-property-search__select:focus {
            outline: 2px solid #e21b2d;
            outline-offset: 4px;
        }

        .custom-property-search__select-wrap::after {
            position: absolute;
            top: 50%;
            right: 2px;
            width: 9px;
            height: 9px;
            border-right: 2px solid #a4a4a4;
            border-bottom: 2px solid #a4a4a4;
            content: "";
            pointer-events: none;
            transform: translateY(-65%) rotate(45deg);
        }

        .custom-property-search__button-wrap {
            display: flex;
            justify-content: flex-end;
        }

        .custom-property-search__button {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 58px;
            height: 58px;
            border: 0;
            border-radius: 50%;
            background: #e21b2d;
            cursor: pointer;
        }

        .custom-property-search__button:focus {
            outline: 3px solid rgba(226, 27, 45, 0.35);
            outline-offset: 4px;
        }

        .custom-property-search__icon {
            position: relative;
            display: block;
            width: 20px;
            height: 20px;
            border: 3px solid #fff;
            border-radius: 50%;
        }

        .custom-property-search__icon::after {
            position: absolute;
            right: -8px;
            bottom: -5px;
            width: 11px;
            height: 3px;
            border-radius: 999px;
            background: #fff;
            content: "";
            transform: rotate(45deg);
        }

        .custom-property-search__button-text {
            position: absolute;
            width: 1px;
            height: 1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
        }

        @media (max-width: 900px) {
            .custom-property-search__form {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                border-radius: 28px;
            }

            .custom-property-search__field {
                padding: 14px 24px;
            }

            .custom-property-search__field:nth-child(2) {
                border-right: 0;
            }

            .custom-property-search__field:nth-child(4) {
                border-right: 0;
            }

            .custom-property-search__button-wrap {
                grid-column: 1 / -1;
            }
        }

        @media (max-width: 640px) {
            .custom-property-search__form {
                grid-template-columns: 1fr;
                gap: 12px;
                padding: 22px;
            }

            .custom-property-search__field,
            .custom-property-search__field:first-child {
                padding: 0 0 14px;
                border-right: 0;
                border-bottom: 1px solid #e6e6e6;
            }

            .custom-property-search__button-wrap {
                grid-column: auto;
                justify-content: stretch;
            }

            .custom-property-search__button {
                width: 100%;
                height: 52px;
                border-radius: 999px;
            }
        }
    </style>

    <form class="custom-property-search__form" method="get" action="<?php echo esc_url($result_url); ?>">
        <div class="custom-property-search__field">
            <label class="custom-property-search__label" for="property_location"><?php esc_html_e('Lokasi', 'custom-plugin'); ?></label>
            <div class="custom-property-search__select-wrap">
                <select class="custom-property-search__select" id="property_location" name="property_location">
                    <option value=""><?php esc_html_e('Pilih Opsi', 'custom-plugin'); ?></option>
                    <?php foreach ($locations as $location) : ?>
                        <option value="<?php echo esc_attr($location->slug); ?>" <?php selected($selected_location, $location->slug); ?>>
                            <?php echo esc_html($location->name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="custom-property-search__field">
            <label class="custom-property-search__label" for="property_type"><?php esc_html_e('Jenis Properti', 'custom-plugin'); ?></label>
            <div class="custom-property-search__select-wrap">
                <select class="custom-property-search__select" id="property_type" name="property_type">
                    <option value=""><?php esc_html_e('Pilih Opsi', 'custom-plugin'); ?></option>
                    <?php foreach ($property_types as $property_type) : ?>
                        <option value="<?php echo esc_attr($property_type->slug); ?>" <?php selected($selected_type, $property_type->slug); ?>>
                            <?php echo esc_html($property_type->name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="custom-property-search__field">
            <label class="custom-property-search__label" for="property_project"><?php esc_html_e('Proyek', 'custom-plugin'); ?></label>
            <div class="custom-property-search__select-wrap">
                <select class="custom-property-search__select" id="property_project" name="property_project">
                    <option value=""><?php esc_html_e('Pilih Opsi', 'custom-plugin'); ?></option>
                    <?php foreach ($property_projects as $property_project) : ?>
                        <option value="<?php echo esc_attr($property_project->slug); ?>" <?php selected($selected_project, $property_project->slug); ?>>
                            <?php echo esc_html($property_project->name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="custom-property-search__field">
            <label class="custom-property-search__label" for="property_price"><?php esc_html_e('Kisaran Harga', 'custom-plugin'); ?></label>
            <div class="custom-property-search__select-wrap">
                <select class="custom-property-search__select" id="property_price" name="property_price">
                    <option value=""><?php esc_html_e('Pilih Opsi', 'custom-plugin'); ?></option>
                    <?php foreach ($price_ranges as $price_key => $price_label) : ?>
                        <option value="<?php echo esc_attr($price_key); ?>" <?php selected($selected_price, $price_key); ?>>
                            <?php echo esc_html($price_label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="custom-property-search__button-wrap">
            <button class="custom-property-search__button" type="submit">
                <span class="custom-property-search__icon" aria-hidden="true"></span>
                <span class="custom-property-search__button-text"><?php esc_html_e('Cari Properti', 'custom-plugin'); ?></span>
            </button>
        </div>
    </form>
</div>