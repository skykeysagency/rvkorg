<?php
/**
 * Script pour copier les fichiers CSV depuis un répertoire externe
 */

// Empêcher l'accès direct au fichier
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Ajouter une page pour copier les fichiers CSV dans le menu d'administration
 */
function rvk_add_copy_files_page()
{
    add_submenu_page(
        'rivka-traiteur', // Parent slug
        'Copier les fichiers CSV', // Titre de la page
        'Copier les fichiers CSV', // Titre du menu
        'manage_options', // Capacité requise
        'copy-csv-files', // Slug de la page
        'rvk_render_copy_files_page' // Fonction de callback
    );
}
add_action('admin_menu', 'rvk_add_copy_files_page', 23);

/**
 * Afficher la page de copie des fichiers CSV
 */
function rvk_render_copy_files_page()
{
    // Vérifier les droits d'accès
    if (!current_user_can('manage_options')) {
        wp_die('Vous n\'avez pas les droits suffisants pour accéder à cette page.');
    }

    // Définir le répertoire de destination
    $destination_dir = plugin_dir_path(__FILE__) . 'import-data';

    // Créer le répertoire de destination s'il n'existe pas
    if (!is_dir($destination_dir)) {
        wp_mkdir_p($destination_dir);
    }

    // Traiter la copie des fichiers
    $copy_results = array();
    if (isset($_POST['copy_files']) && check_admin_referer('rvk_copy_files_action', 'rvk_copy_files_nonce')) {
        $source_dir = isset($_POST['source_directory']) ? sanitize_text_field($_POST['source_directory']) : '';

        if (empty($source_dir) || !is_dir($source_dir)) {
            $copy_results['errors'][] = "Le répertoire source spécifié n'existe pas ou n'est pas valide.";
        } else {
            // Parcourir les fichiers CSV du répertoire source
            $files = glob($source_dir . '/*.csv');

            if (empty($files)) {
                $copy_results['errors'][] = "Aucun fichier CSV trouvé dans le répertoire source.";
            } else {
                $copied_count = 0;
                $error_count = 0;

                foreach ($files as $file) {
                    $filename = basename($file);
                    $destination_file = $destination_dir . '/' . $filename;

                    // Vérifier si le nom du fichier correspond au format attendu
                    if (preg_match('/Formule RVK ORG - (.+)\.csv/i', $filename)) {
                        // Copier le fichier
                        if (copy($file, $destination_file)) {
                            $copy_results['messages'][] = "Fichier copié avec succès: $filename";
                            $copied_count++;
                        } else {
                            $copy_results['errors'][] = "Impossible de copier le fichier: $filename";
                            $error_count++;
                        }
                    } else {
                        $copy_results['warnings'][] = "Fichier ignoré (format non reconnu): $filename";
                    }
                }

                $copy_results['summary'] = "Fichiers copiés avec succès: $copied_count, Erreurs: $error_count";
            }
        }
    }

    // Afficher la page
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

        <div class="card">
            <h2>Copier les fichiers CSV</h2>
            <p>Ce formulaire vous permet de copier des fichiers CSV depuis un répertoire externe vers le répertoire
                d'importation du plugin.</p>

            <form method="post" action="">
                <?php wp_nonce_field('rvk_copy_files_action', 'rvk_copy_files_nonce'); ?>

                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="source_directory">Répertoire source</label></th>
                        <td>
                            <input type="text" id="source_directory" name="source_directory" class="regular-text"
                                value="<?php echo isset($_POST['source_directory']) ? esc_attr($_POST['source_directory']) : ''; ?>"
                                required>
                            <p class="description">Chemin complet vers le répertoire contenant les fichiers CSV à copier
                                (ex: /Users/macdedylan/Downloads/import formule)</p>
                        </td>
                    </tr>
                </table>

                <p class="submit">
                    <input type="submit" name="copy_files" class="button button-primary" value="Copier les fichiers">
                </p>
            </form>
        </div>

        <?php if (!empty($copy_results)): ?>
            <div class="card">
                <h2>Résultats de la copie</h2>

                <?php if (!empty($copy_results['summary'])): ?>
                    <p class="notice notice-info"><?php echo esc_html($copy_results['summary']); ?></p>
                <?php endif; ?>

                <?php if (!empty($copy_results['errors'])): ?>
                    <div class="notice notice-error">
                        <h3>Erreurs :</h3>
                        <ul>
                            <?php foreach ($copy_results['errors'] as $error): ?>
                                <li><?php echo esc_html($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if (!empty($copy_results['warnings'])): ?>
                    <div class="notice notice-warning">
                        <h3>Avertissements :</h3>
                        <ul>
                            <?php foreach ($copy_results['warnings'] as $warning): ?>
                                <li><?php echo esc_html($warning); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if (!empty($copy_results['messages'])): ?>
                    <div class="notice notice-success">
                        <h3>Fichiers copiés :</h3>
                        <ul>
                            <?php foreach ($copy_results['messages'] as $message): ?>
                                <li><?php echo esc_html($message); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if (!empty($copy_results['messages'])): ?>
                    <div class="notice notice-info">
                        <h3>Étapes suivantes :</h3>
                        <p>Les fichiers ont été copiés dans le répertoire d'importation du plugin. Vous pouvez maintenant importer
                            les formules :</p>
                        <ol>
                            <li>Accédez au menu "Formules" > "Importation directe"</li>
                            <li>Vérifiez que les fichiers CSV sont bien listés</li>
                            <li>Cliquez sur "Importer les formules"</li>
                        </ol>
                        <p><a href="<?php echo admin_url('admin.php?page=import-direct'); ?>" class="button">Aller à l'importation
                                directe</a></p>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php
}