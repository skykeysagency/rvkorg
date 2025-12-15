<?php
/**
 * Script pour ajouter une page d'importation dans l'administration WordPress
 * Ce fichier est inclus par le plugin Formules Custom Plugin
 */

// Empêcher l'accès direct au fichier
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Ajoute une page d'importation dans le menu d'administration
 */
function fcp_add_import_page()
{
    add_submenu_page(
        'edit.php?post_type=rivka-traiteur',
        'Importer des données',
        'Importer des données',
        'manage_options',
        'fcp-import',
        'fcp_render_import_page'
    );
}
add_action('admin_menu', 'fcp_add_import_page');

/**
 * Affiche le contenu de la page d'importation
 */
function fcp_render_import_page()
{
    // Vérifier les permissions
    if (!current_user_can('manage_options')) {
        wp_die(__('Vous n\'avez pas les permissions suffisantes pour accéder à cette page.'));
    }

    // Traiter le formulaire d'importation si soumis
    if (isset($_POST['fcp_import_submit']) && isset($_FILES['fcp_import_file'])) {
        // Vérifier le nonce
        check_admin_referer('fcp_import_action', 'fcp_import_nonce');

        $file = $_FILES['fcp_import_file'];

        // Vérifier s'il y a des erreurs
        if ($file['error'] !== UPLOAD_ERR_OK) {
            echo '<div class="error"><p>Erreur lors du téléchargement du fichier. Code: ' . $file['error'] . '</p></div>';
        } else {
            // Vérifier le type de fichier (doit être un CSV)
            $file_type = wp_check_filetype(basename($file['name']), array('csv' => 'text/csv'));

            if ($file_type['ext'] == 'csv' || $file_type['type'] == 'text/csv' || $file_type['type'] == 'application/vnd.ms-excel') {
                // Traiter le fichier CSV
                echo '<div class="updated"><p>Fichier CSV reçu avec succès: ' . esc_html($file['name']) . '</p></div>';

                // Rediriger vers le script d'importation spécifique si nécessaire
                if (isset($_POST['import_type']) && $_POST['import_type'] === 'portfolio') {
                    // Vérifier si le fichier import-portfolio.php existe
                    $import_file = plugin_dir_path(dirname(__FILE__)) . 'scripts/import-portfolio.php';
                    if (file_exists($import_file)) {
                        include_once $import_file;
                        // Appeler la fonction d'importation si elle existe
                        if (function_exists('process_portfolio_import')) {
                            process_portfolio_import($file['tmp_name']);
                        } else {
                            echo '<div class="error"><p>La fonction d\'importation n\'existe pas.</p></div>';
                        }
                    } else {
                        echo '<div class="error"><p>Le fichier d\'importation n\'existe pas.</p></div>';
                    }
                } else {
                    echo '<div class="error"><p>Type d\'importation non spécifié ou non pris en charge.</p></div>';
                }
            } else {
                echo '<div class="error"><p>Veuillez télécharger un fichier CSV valide.</p></div>';
            }
        }
    }

    // Afficher le formulaire d'importation
    ?>
    <div class="wrap">
        <h1>Importer des données</h1>

        <form method="post" enctype="multipart/form-data">
            <?php wp_nonce_field('fcp_import_action', 'fcp_import_nonce'); ?>

            <table class="form-table">
                <tr>
                    <th scope="row"><label for="fcp_import_file">Fichier CSV</label></th>
                    <td>
                        <input type="file" name="fcp_import_file" id="fcp_import_file" accept=".csv" required>
                        <p class="description">Sélectionnez un fichier CSV à importer.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="import_type">Type d'importation</label></th>
                    <td>
                        <select name="import_type" id="import_type">
                            <option value="portfolio">Portfolio</option>
                        </select>
                        <p class="description">Sélectionnez le type de données à importer.</p>
                    </td>
                </tr>
            </table>

            <p class="submit">
                <input type="submit" name="fcp_import_submit" class="button button-primary" value="Importer">
            </p>
        </form>
    </div>
    <?php
}
