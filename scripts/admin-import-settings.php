<?php
/**
 * Intégration de l'importateur de formules dans les réglages du plugin
 */

// Empêcher l'accès direct au fichier
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Ajouter une section de réglages pour l'importation des formules
 */
function rvk_register_import_settings()
{
    // Enregistrer les réglages
    register_setting('rvk_import_settings', 'rvk_import_csv_directory', 'sanitize_text_field');
    register_setting('rvk_import_settings', 'rvk_import_auto_schedule', 'intval');

    // Ajouter une section pour les réglages d'importation
    add_settings_section(
        'rvk_import_section',
        'Réglages d\'importation des formules',
        'rvk_import_section_callback',
        'rvk_import_settings'
    );

    // Ajouter les champs de réglages
    add_settings_field(
        'rvk_import_csv_directory',
        'Répertoire des fichiers CSV',
        'rvk_import_csv_directory_callback',
        'rvk_import_settings',
        'rvk_import_section'
    );

    add_settings_field(
        'rvk_import_auto_schedule',
        'Importation automatique',
        'rvk_import_auto_schedule_callback',
        'rvk_import_settings',
        'rvk_import_section'
    );
}
add_action('admin_init', 'rvk_register_import_settings');

/**
 * Callback pour la description de la section d'importation
 */
function rvk_import_section_callback()
{
    echo '<p>Configurez les paramètres d\'importation des formules depuis les fichiers CSV.</p>';
}

/**
 * Callback pour le champ de répertoire CSV
 */
function rvk_import_csv_directory_callback()
{
    $directory = get_option('rvk_import_csv_directory', '');
    ?>
    <input type="text" id="rvk_import_csv_directory" name="rvk_import_csv_directory"
        value="<?php echo esc_attr($directory); ?>" class="regular-text" />
    <p class="description">Chemin complet vers le répertoire contenant les fichiers CSV des formules.</p>
    <?php
}

/**
 * Callback pour le champ d'importation automatique
 */
function rvk_import_auto_schedule_callback()
{
    $schedule = get_option('rvk_import_auto_schedule', 0);
    ?>
    <select id="rvk_import_auto_schedule" name="rvk_import_auto_schedule">
        <option value="0" <?php selected($schedule, 0); ?>>Désactivé</option>
        <option value="1" <?php selected($schedule, 1); ?>>Quotidien</option>
        <option value="2" <?php selected($schedule, 2); ?>>Hebdomadaire</option>
        <option value="3" <?php selected($schedule, 3); ?>>Mensuel</option>
    </select>
    <p class="description">Planifier l'importation automatique des formules.</p>
    <?php
}

/**
 * Ajouter une page de réglages d'importation dans le menu d'administration
 */
function rvk_add_import_settings_page()
{
    add_submenu_page(
        'rivka-traiteur', // Parent slug
        'Réglages d\'importation', // Titre de la page
        'Réglages d\'importation', // Titre du menu
        'manage_options', // Capacité requise
        'import-settings', // Slug de la page
        'rvk_render_import_settings_page' // Fonction de callback
    );
}
add_action('admin_menu', 'rvk_add_import_settings_page', 21);

/**
 * Afficher la page de réglages d'importation
 */
function rvk_render_import_settings_page()
{
    // Vérifier les droits d'accès
    if (!current_user_can('manage_options')) {
        wp_die('Vous n\'avez pas les droits suffisants pour accéder à cette page.');
    }

    // Traiter l'importation si demandée
    $import_results = array();
    if (isset($_POST['import_now']) && check_admin_referer('rvk_import_now_action', 'rvk_import_now_nonce')) {
        $csv_dir = get_option('rvk_import_csv_directory', '');
        if (!empty($csv_dir) && is_dir($csv_dir)) {
            // Inclure la fonction d'importation
            require_once(plugin_dir_path(__FILE__) . 'import-formules.php');

            // Parcourir les fichiers CSV du répertoire
            $files = glob($csv_dir . '/*.csv');

            if (empty($files)) {
                $import_results['errors'][] = "Aucun fichier CSV trouvé dans le répertoire spécifié.";
            } else {
                foreach ($files as $file) {
                    $filename = basename($file);
                    // Extraire la catégorie du nom de fichier (format: "Formule RVK ORG - [Catégorie].csv")
                    if (preg_match('/Formule RVK ORG - (.+)\.csv/i', $filename, $matches)) {
                        $category = $matches[1];
                        $result = import_formules_from_csv($file, $category);
                        $import_results[$filename] = $result;
                    } else {
                        $import_results['errors'][] = "Format de nom de fichier non reconnu: $filename";
                    }
                }
            }
        } else {
            $import_results['errors'][] = "Le répertoire spécifié n'existe pas ou n'est pas valide.";
        }
    }

    // Afficher la page de réglages
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

        <form method="post" action="options.php">
            <?php
            settings_fields('rvk_import_settings');
            do_settings_sections('rvk_import_settings');
            submit_button('Enregistrer les réglages');
            ?>
        </form>

        <hr>

        <h2>Importation manuelle</h2>
        <p>Cliquez sur le bouton ci-dessous pour lancer l'importation des formules depuis le répertoire configuré.</p>

        <form method="post" action="">
            <?php wp_nonce_field('rvk_import_now_action', 'rvk_import_now_nonce'); ?>
            <input type="submit" name="import_now" class="button button-primary" value="Importer maintenant">
        </form>

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
 * Configurer les tâches planifiées pour l'importation automatique
 */
function rvk_setup_import_cron()
{
    $schedule = get_option('rvk_import_auto_schedule', 0);

    // Supprimer la tâche existante
    wp_clear_scheduled_hook('rvk_import_formules_cron');

    // Configurer la nouvelle tâche si nécessaire
    if ($schedule > 0) {
        $recurrence = 'daily';
        if ($schedule == 2) {
            $recurrence = 'weekly';
        } elseif ($schedule == 3) {
            $recurrence = 'monthly';
        }

        if (!wp_next_scheduled('rvk_import_formules_cron')) {
            wp_schedule_event(time(), $recurrence, 'rvk_import_formules_cron');
        }
    }
}
add_action('update_option_rvk_import_auto_schedule', 'rvk_setup_import_cron');

/**
 * Ajouter la récurrence mensuelle si elle n'existe pas
 */
function rvk_add_monthly_cron_schedule($schedules)
{
    if (!isset($schedules['monthly'])) {
        $schedules['monthly'] = array(
            'interval' => 30 * 24 * 60 * 60, // 30 jours
            'display' => 'Une fois par mois'
        );
    }
    return $schedules;
}
add_filter('cron_schedules', 'rvk_add_monthly_cron_schedule');

/**
 * Fonction exécutée par la tâche cron pour importer les formules
 */
function rvk_import_formules_cron_callback()
{
    $csv_dir = get_option('rvk_import_csv_directory', '');
    if (!empty($csv_dir) && is_dir($csv_dir)) {
        // Inclure la fonction d'importation
        require_once(plugin_dir_path(__FILE__) . 'import-formules.php');

        // Parcourir les fichiers CSV du répertoire
        $files = glob($csv_dir . '/*.csv');

        if (!empty($files)) {
            foreach ($files as $file) {
                $filename = basename($file);
                // Extraire la catégorie du nom de fichier
                if (preg_match('/Formule RVK ORG - (.+)\.csv/i', $filename, $matches)) {
                    $category = $matches[1];
                    import_formules_from_csv($file, $category);
                }
            }
        }

        // Journaliser l'importation
        error_log('Importation automatique des formules effectuée le ' . date('Y-m-d H:i:s'));
    }
}
add_action('rvk_import_formules_cron', 'rvk_import_formules_cron_callback');