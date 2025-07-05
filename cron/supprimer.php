<?php 


if (!defined('ABSPATH')) {
    exit; 
}

add_action('supprimer_zip_file', 'supprimer_zip_file');
function supprimer_zip_file($file_path) {
    if (file_exists($file_path)) {
        unlink($file_path);
    }
}