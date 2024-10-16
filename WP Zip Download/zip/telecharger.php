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
    
    // Définir le chemin du répertoire du plugin
    $plugin_dir = plugin_dir_path(__DIR__);

    // Définir le chemin du fichier ZIP dans le répertoire du plugin
    $zip_filename = $plugin_dir . 'media_' . time() . '.zip';

    // Créer une liste des fichiers à zipper
    $file_list = [];
    foreach ($media_ids as $media_id) {
        $file_path = get_attached_file($media_id);
        if (file_exists($file_path)) {
            $file_list[] = escapeshellarg($file_path);
        } else {
            wp_die('Le fichier suivant n\'existe pas : ' . esc_html($file_path));
        }
    }

    // Utiliser la commande zip pour créer l'archive
    if (count($file_list) > 0) {
        $zip_command = 'zip -j ' . escapeshellarg($zip_filename) . ' ' . implode(' ', $file_list);
        shell_exec($zip_command);
    } else {
        wp_die('Aucun fichier à zipper.');
    }

    if (!file_exists($zip_filename)) {
        wp_die('Le fichier ZIP n\'a pas été créé.');
    }

    // Générer l'URL du fichier pour téléchargement
    $zip_file_url = plugin_dir_url(__DIR__) . basename($zip_filename);

    $interval_minutes = intval(get_option('wp_zip_cron_time', 5));
    wp_schedule_single_event(time() + $interval_minutes * 60, 'supprimer_zip_file', [$zip_filename]);

    // Collection des données de réponse
    $response = [
        'success' => true,
        'data' => $zip_file_url,
        'message' => __('ZIP créé avec succès.', 'textdomain') // Ajouter un message de succès
    ];

    header('Content-Type: application/json');
    echo json_encode($response);
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