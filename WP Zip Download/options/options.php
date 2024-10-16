<?php 

if (!defined('ABSPATH')) {
    exit; 
}


function wp_zip_download_menu() {
    add_options_page(
        'Réglages WP Zip Download',
        'WP Zip Download',
        'manage_options',
        'wp_zip_download_settings',
        'wp_zip_download_settings_page'
    );
}
add_action('admin_menu', 'wp_zip_download_menu');

function wp_zip_download_settings_page() {
    ?>
    <div class="wrap">
        <h1><?php _e('WP Zip Download', 'textdomain'); ?></h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('wp_zip_download_options');
            do_settings_sections('wp_zip_download_settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

function wp_zip_download_settings_init() {
    register_setting('wp_zip_download_options', 'wp_zip_cron_time', ['default' => 5]);

    add_settings_section('wp_zip_download_settings_section', '', null, 'wp_zip_download_settings');

    add_settings_field(
        'wp_zip_cron_time',
        __('Temps de suppression du fichier ZIP (minutes)', 'textdomain'),
        'wp_zip_cron_time_field_render',
        'wp_zip_download_settings',
        'wp_zip_download_settings_section'
    );
}
add_action('admin_init', 'wp_zip_download_settings_init');

function wp_zip_cron_time_field_render() {
    $value = get_option('wp_zip_cron_time', 5);
    echo '<input type="number" name="wp_zip_cron_time" value="' . esc_attr($value) . '" min="1" />';
}
