<?php
/**
 * Script d'importation directe des formules
 * 
 * Ce script permet d'importer des formules à partir de fichiers CSV placés dans un répertoire
 * prédéfini du plugin, sans avoir à spécifier un chemin absolu.
 */

// Empêcher l'accès direct au fichier
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Ajouter une page d'importation directe des formules dans le menu d'administration
 */
function rvk_add_import_direct_page()
{
    add_submenu_page(
        'rivka-traiteur', // Parent slug
        'Importation directe', // Titre de la page
        'Importation directe', // Titre du menu
        'manage_options', // Capacité requise
        'import-direct', // Slug de la page
        'rvk_render_import_direct_page' // Fonction de callback
    );
}
add_action('admin_menu', 'rvk_add_import_direct_page', 22);

/**
 * Afficher la page d'importation directe des formules
 */
function rvk_render_import_direct_page()
{
    // Vérifier les droits d'accès
    if (!current_user_can('manage_options')) {
        wp_die('Vous n\'avez pas les droits suffisants pour accéder à cette page.');
    }

    // Définir le répertoire d'importation dans le plugin
    $import_dir = plugin_dir_path(__FILE__) . 'import-data';

    // Créer le répertoire s'il n'existe pas
    if (!file_exists($import_dir)) {
        wp_mkdir_p($import_dir);
    }

    // Traiter le téléchargement de fichiers
    $upload_message = '';
    if (isset($_POST['upload_csv']) && check_admin_referer('rvk_upload_csv_action', 'rvk_upload_csv_nonce')) {
        if (!empty($_FILES['csv_file']['name'])) {
            $file_name = $_FILES['csv_file']['name'];
            $file_tmp = $_FILES['csv_file']['tmp_name'];

            // Vérifier si le nom du fichier correspond au format attendu
            if (preg_match('/Formule RVK ORG - (.+)\.csv/i', $file_name)) {
                $target_file = $import_dir . '/' . $file_name;

                // Déplacer le fichier téléchargé
                if (move_uploaded_file($file_tmp, $target_file)) {
                    $upload_message = '<div class="notice notice-success"><p>Le fichier ' . esc_html($file_name) . ' a été téléchargé avec succès.</p></div>';
                } else {
                    $upload_message = '<div class="notice notice-error"><p>Erreur lors du téléchargement du fichier.</p></div>';
                }
            } else {
                $upload_message = '<div class="notice notice-error"><p>Le nom du fichier doit être au format "Formule RVK ORG - [Catégorie].csv".</p></div>';
            }
        } else {
            $upload_message = '<div class="notice notice-error"><p>Veuillez sélectionner un fichier CSV à télécharger.</p></div>';
        }
    }

    // Traiter l'importation
    $import_results = array();
    if (isset($_POST['import_direct']) && check_admin_referer('rvk_import_direct_action', 'rvk_import_direct_nonce')) {
        // Inclure la fonction d'importation
        require_once(plugin_dir_path(__FILE__) . 'import-formules.php');

        // Parcourir les fichiers CSV du répertoire
        $files = glob($import_dir . '/*.csv');

        if (empty($files)) {
            $import_results['errors'][] = "Aucun fichier CSV trouvé dans le répertoire d'importation.";
        } else {
            foreach ($files as $file) {
                $filename = basename($file);
                // Extraire la catégorie du nom de fichier
                if (preg_match('/Formule RVK ORG - (.+)\.csv/i', $filename, $matches)) {
                    $category = $matches[1];
                    $result = import_formules_from_csv($file, $category);
                    $import_results[$filename] = $result;
                } else {
                    $import_results['errors'][] = "Format de nom de fichier non reconnu: $filename";
                }
            }
        }
    }

    // Traiter la suppression de fichiers
    if (isset($_GET['delete_file']) && check_admin_referer('rvk_delete_file_action', 'rvk_delete_file_nonce')) {
        $file_to_delete = sanitize_text_field($_GET['delete_file']);
        $file_path = $import_dir . '/' . $file_to_delete;

        if (file_exists($file_path) && unlink($file_path)) {
            $upload_message = '<div class="notice notice-success"><p>Le fichier ' . esc_html($file_to_delete) . ' a été supprimé avec succès.</p></div>';
        } else {
            $upload_message = '<div class="notice notice-error"><p>Erreur lors de la suppression du fichier.</p></div>';
        }
    }

    // Lister les fichiers CSV disponibles
    $available_files = glob($import_dir . '/*.csv');

    // Afficher la page
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

        <?php echo $upload_message; ?>

        <div class="card">
            <h2>Télécharger un fichier CSV</h2>
            <p>Téléchargez un fichier CSV au format "Formule RVK ORG - [Catégorie].csv".</p>

            <form method="post" enctype="multipart/form-data">
                <?php wp_nonce_field('rvk_upload_csv_action', 'rvk_upload_csv_nonce'); ?>

                <input type="file" name="csv_file" accept=".csv">
                <p class="submit">
                    <input type="submit" name="upload_csv" class="button button-primary" value="Télécharger">
                </p>
            </form>
        </div>

        <div class="card">
            <h2>Fichiers CSV disponibles</h2>

            <?php if (empty($available_files)): ?>
                <p>Aucun fichier CSV disponible. Veuillez télécharger des fichiers CSV pour l'importation.</p>
            <?php else: ?>
                <table class="widefat">
                    <thead>
                        <tr>
                            <th>Nom du fichier</th>
                            <th>Taille</th>
                            <th>Date de modification</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($available_files as $file): ?>
                            <tr>
                                <td><?php echo esc_html(basename($file)); ?></td>
                                <td><?php echo esc_html(size_format(filesize($file))); ?></td>
                                <td><?php echo esc_html(date('Y-m-d H:i:s', filemtime($file))); ?></td>
                                <td>
                                    <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=import-direct&delete_file=' . urlencode(basename($file))), 'rvk_delete_file_action', 'rvk_delete_file_nonce'); ?>"
                                        class="button button-small"
                                        onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce fichier ?');">Supprimer</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div class="card">
            <h2>Importer les formules</h2>
            <p>Cliquez sur le bouton ci-dessous pour importer les formules à partir des fichiers CSV disponibles.</p>

            <form method="post">
                <?php wp_nonce_field('rvk_import_direct_action', 'rvk_import_direct_nonce'); ?>
                <p class="submit">
                    <input type="submit" name="import_direct" class="button button-primary" value="Importer les formules"
                        <?php echo empty($available_files) ? 'disabled' : ''; ?>>
                </p>
            </form>
        </div>

        <?php if (!empty($import_results)): ?>
            <h2>Résultats de l'importation</h2>

            <?php if (!empty($import_results['errors'])): ?>
                <div class="notice notice-error">
                    <h3>Erreurs générales :</h3>
                    <ul>
                        <?php foreach ($import_results['errors'] as $error): ?>
                            <li><?php echo esc_html($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php foreach ($import_results as $filename => $result): ?>
                <?php if ($filename !== 'errors'): ?>
                    <div class="notice notice-info">
                        <h3>Fichier : <?php echo esc_html($filename); ?></h3>

                        <?php if ($result['success'] > 0): ?>
                            <p class="notice-success" style="padding: 10px;">Nombre de formules importées avec succès :
                                <?php echo esc_html($result['success']); ?></p>
                        <?php endif; ?>

                        <?php if (!empty($result['errors'])): ?>
                            <div class="notice-error" style="padding: 10px;">
                                <h4>Erreurs :</h4>
                                <ul>
                                    <?php foreach ($result['errors'] as $error): ?>
                                        <li><?php echo esc_html($error); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($result['messages'])): ?>
                            <div class="notice-success" style="padding: 10px;">
                                <h4>Messages :</h4>
                                <ul>
                                    <?php foreach ($result['messages'] as $message): ?>
                                        <li><?php echo esc_html($message); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * Créer le répertoire d'importation lors de l'activation du plugin
 */
function rvk_create_import_directory()
{
    $import_dir = plugin_dir_path(__FILE__) . 'import-data';
    if (!file_exists($import_dir)) {
        wp_mkdir_p($import_dir);
    }
}
register_activation_hook(plugin_dir_path(__FILE__) . '../rvk.php', 'rvk_create_import_directory');