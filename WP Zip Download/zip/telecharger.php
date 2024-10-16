<?php 


if (!defined('ABSPATH')) {
    exit; 
}

add_action('wp_ajax_telecharger_zip', 'telecharger_zip');
function telecharger_zip() {
    check_ajax_referer('telecharger_zip_nonce', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_die('Pas autorisé');
    }

    if (!isset($_POST['media'])) {
        wp_die('Aucun média sélectionné');
    }

    $media_ids = array_map('intval', $_POST['media']);
    $upload_dir = wp_upload_dir();
    $zip_dir = $upload_dir['path'];

    if (!is_dir($zip_dir)) {
        wp_die('Le répertoire de destination n\'existe pas.');
    }

    $zip_filename = $zip_dir . '/media_' . time() . '.zip';
    $zip = new ZipArchive();

    if ($zip->open($zip_filename, ZipArchive::CREATE) !== TRUE) {
        wp_die('Impossible d\'ouvrir le fichier ZIP à cet emplacement : ' . $zip_filename);
    }

    foreach ($media_ids as $media_id) {
        $file_path = get_attached_file($media_id);
        if (file_exists($file_path)) {
            $zip->addFile($file_path, basename($file_path));
        } else {
            wp_die('Le fichier suivant n\'existe pas : ' . esc_html($file_path));
        }
    }

    if (!$zip->close()) {
        wp_die('Impossible de fermer le fichier ZIP.');
    }

    if (!file_exists($zip_filename)) {
        wp_die('Le fichier ZIP n\'a pas été créé.');
    }

    $zip_file_url = $upload_dir['url'] . '/' . basename($zip_filename);

    $interval_minutes = intval(get_option('wp_zip_cron_time', 5));
    wp_schedule_single_event(time() + $interval_minutes * 60, 'supprimer_zip_file', [$zip_filename]);

    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'data' => $zip_file_url]);
    exit();
}

add_action('wp_ajax_download_zip', 'download_zip');
function download_zip() {
    if (!current_user_can('manage_options')) {
        wp_die('Pas autorisé');
    }

    $file = sanitize_file_name($_GET['file']);
    $upload_dir = wp_upload_dir();
    $file_path = $upload_dir['path'] . '/' . $file;

    if (file_exists($file_path)) {
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename=' . $file);
        header('Content-Length: ' . filesize($file_path));
        readfile($file_path);
        unlink($file_path);
        exit();
    } else {
        wp_die('Le fichier ZIP est introuvable à l\'emplacement : ' . esc_html($file_path));
    }
}