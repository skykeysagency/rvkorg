<?php
/**
 * Intégration de l'importateur de formules dans l'interface d'administration WordPress
 */

// Empêcher l'accès direct au fichier
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Ajouter une page d'importation de formules dans le menu d'administration
 */
function rvk_add_import_formules_page()
{
    add_submenu_page(
        'rivka-traiteur', // Parent slug
        'Importer des formules', // Titre de la page
        'Importer des formules', // Titre du menu
        'manage_options', // Capacité requise
        'import-formules', // Slug de la page
        'rvk_render_import_formules_page' // Fonction de callback
    );
}
add_action('admin_menu', 'rvk_add_import_formules_page', 20);

/**
 * Afficher la page d'importation de formules
 */
function rvk_render_import_formules_page()
{
    // Vérifier les droits d'accès
    if (!current_user_can('manage_options')) {
        wp_die('Vous n\'avez pas les droits suffisants pour accéder à cette page.');
    }

    // Inclure le script d'importation
    include_once(plugin_dir_path(__FILE__) . 'import-formules.php');
}

/**
 * Ajouter un lien d'action rapide pour l'importation des formules
 */
function rvk_add_import_formules_action_link($links)
{
    $import_link = '<a href="' . admin_url('admin.php?page=import-formules') . '">Importer des formules</a>';
    array_unshift($links, $import_link);
    return $links;
}
add_filter('plugin_action_links_rvk/rvk.php', 'rvk_add_import_formules_action_link');