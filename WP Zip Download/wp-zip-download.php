<?php

/*
Plugin Name: WP Zip Download
Plugin URI: https://kevin-benabdelhak.fr/plugins/wp-zip-download/
Description: Ajoutez une fonctionnalité à votre site WordPress permettant de télécharger des fichiers médias en tant qu'archive ZIP directement depuis la bibliothèque de médias. Configurez un intervalle de temps pour supprimer automatiquement les fichiers ZIP avec une option de réglage personnalisée accessible via le tableau de bord, permettant une gestion flexible et efficace de l'espace de stockage.
Version: 1.0
Author: Kevin BENABDELHAK
Author URI: https://kevin-benabdelhak.fr/
Contributors: kevinbenabdelhak
*/

if (!defined('ABSPATH')) {
    exit; 
}

require_once plugin_dir_path(__FILE__) . 'options/options.php';
require_once plugin_dir_path(__FILE__) . 'options/bulk.php';
require_once plugin_dir_path(__FILE__) . 'zip/telecharger.php';
require_once plugin_dir_path(__FILE__) . 'cron/supprimer.php';