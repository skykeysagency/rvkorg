<?php
/**
 * Plugin Name: RVK
 * Plugin URI: 
 * Description: Plugin personnalisé pour le site RVK
 * Version: 1.0
 * Author: 
 * Author URI: 
 * Text Domain: rvk
 */

// Empêcher l'accès direct au fichier
if (!defined('ABSPATH')) {
    exit;
}

// Vérifier si les fonctions existent déjà pour éviter les redéclarations
if (!function_exists('rvk_change_portfolio_labels')) {
    /**
     * Modifier les libellés du type de contenu Portfolio pour l'appeler "Salles"
     */
    function rvk_change_portfolio_labels()
    {
        global $wp_post_types;

        // Vérifier si le type de post existe
        if (!isset($wp_post_types['cpt_portfolio'])) {
            return;
        }

        // Modifier les libellés
        $labels = &$wp_post_types['cpt_portfolio']->labels;
        $labels->name = 'Salles';
        $labels->singular_name = 'Salle';
        $labels->add_new = 'Ajouter une salle';
        $labels->add_new_item = 'Ajouter une nouvelle salle';
        $labels->edit_item = 'Modifier la salle';
        $labels->new_item = 'Nouvelle salle';
        $labels->view_item = 'Voir la salle';
        $labels->view_items = 'Voir les salles';
        $labels->search_items = 'Rechercher des salles';
        $labels->not_found = 'Aucune salle trouvée';
        $labels->not_found_in_trash = 'Aucune salle trouvée dans la corbeille';
        $labels->parent_item_colon = 'Salle parente:';
        $labels->all_items = 'Toutes les salles';
        $labels->archives = 'Archives des salles';
        $labels->attributes = 'Attributs de la salle';
        $labels->insert_into_item = 'Insérer dans la salle';
        $labels->uploaded_to_this_item = 'Téléversé vers cette salle';
        $labels->filter_items_list = 'Filtrer la liste des salles';
        $labels->items_list_navigation = 'Navigation de la liste des salles';
        $labels->items_list = 'Liste des salles';
        $labels->item_published = 'Salle publiée.';
        $labels->item_published_privately = 'Salle publiée en privé.';
        $labels->item_reverted_to_draft = 'Salle reconvertie en brouillon.';
        $labels->item_scheduled = 'Salle planifiée.';
        $labels->item_updated = 'Salle mise à jour.';
        $labels->menu_name = 'Salles';
        $labels->name_admin_bar = 'Salle';

        // Modifier également le libellé du menu
        $wp_post_types['cpt_portfolio']->label = 'Salles';

        // Modifier les libellés de la taxonomie
        global $wp_taxonomies;
        if (isset($wp_taxonomies['cpt_portfolio_group'])) {
            $tax_labels = &$wp_taxonomies['cpt_portfolio_group']->labels;
            $tax_labels->name = 'Catégories de salles';
            $tax_labels->singular_name = 'Catégorie de salle';
            $tax_labels->search_items = 'Rechercher des catégories';
            $tax_labels->all_items = 'Toutes les catégories';
            $tax_labels->parent_item = 'Catégorie parente';
            $tax_labels->parent_item_colon = 'Catégorie parente:';
            $tax_labels->edit_item = 'Modifier la catégorie';
            $tax_labels->update_item = 'Mettre à jour la catégorie';
            $tax_labels->add_new_item = 'Ajouter une nouvelle catégorie';
            $tax_labels->new_item_name = 'Nom de la nouvelle catégorie';
            $tax_labels->menu_name = 'Catégories';
        }
    }
    add_action('init', 'rvk_change_portfolio_labels', 999);
}

if (!function_exists('rvk_change_portfolio_urls')) {
    /**
     * Modifier les URLs du type de contenu Portfolio et de sa taxonomie
     */
    function rvk_change_portfolio_urls()
    {
        global $wp_post_types, $wp_taxonomies;

        // Vérifier si le type de post existe
        if (isset($wp_post_types['cpt_portfolio'])) {
            // Modifier l'URL du type de contenu
            $wp_post_types['cpt_portfolio']->rewrite = array(
                'slug' => 'salles',
                'with_front' => false,
                'hierarchical' => false,
                'feeds' => true,
                'pages' => true
            );

            // Forcer la mise à jour du permalien
            $wp_post_types['cpt_portfolio']->permalink_epmask = EP_PERMALINK;

            // Ajouter des règles de réécriture personnalisées pour les salles
            add_rewrite_rule(
                'salles/([^/]+)/?$',
                'index.php?cpt_portfolio=$matches[1]',
                'top'
            );

            add_rewrite_rule(
                'salles/([^/]+)/page/?([0-9]{1,})/?$',
                'index.php?cpt_portfolio=$matches[1]&paged=$matches[2]',
                'top'
            );
        }

        // Vérifier si la taxonomie existe
        if (isset($wp_taxonomies['cpt_portfolio_group'])) {
            // Modifier l'URL de la taxonomie
            $wp_taxonomies['cpt_portfolio_group']->rewrite = array(
                'slug' => 'categories-salles',
                'with_front' => false,
                'hierarchical' => true
            );

            // Ajouter des règles de réécriture personnalisées pour les catégories de salles
            add_rewrite_rule(
                'categories-salles/([^/]+)/?$',
                'index.php?cpt_portfolio_group=$matches[1]',
                'top'
            );

            add_rewrite_rule(
                'categories-salles/([^/]+)/page/?([0-9]{1,})/?$',
                'index.php?cpt_portfolio_group=$matches[1]&paged=$matches[2]',
                'top'
            );
        }
    }
    add_action('init', 'rvk_change_portfolio_urls', 999);
}

if (!function_exists('rvk_update_existing_permalinks')) {
    /**
     * Mettre à jour les permaliens des posts existants
     */
    function rvk_update_existing_permalinks()
    {
        // Vérifier si l'utilisateur a les droits d'administration
        if (!current_user_can('manage_options')) {
            return;
        }

        // Vérifier si le bouton a été cliqué
        if (isset($_GET['rvk_update_permalinks']) && $_GET['rvk_update_permalinks'] == '1') {
            global $wpdb;

            // Récupérer tous les posts de type portfolio
            $posts = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT ID FROM {$wpdb->posts} WHERE post_type = %s",
                    'cpt_portfolio'
                )
            );

            // Mettre à jour chaque post
            if (is_array($posts) && !empty($posts)) {
                foreach ($posts as $post) {
                    // Forcer la mise à jour du permalien
                    $post_id = $post->ID;
                    $post_name = get_post_field('post_name', $post_id);

                    // Mettre à jour le post avec le même slug pour forcer la mise à jour du permalien
                    wp_update_post(array(
                        'ID' => $post_id,
                        'post_name' => $post_name
                    ));
                }
            }

            // Vider les règles de réécriture
            flush_rewrite_rules();

            // Ajouter un message de succès
            add_action('admin_notices', 'rvk_permalinks_success_notice');

            // Rediriger vers la page actuelle sans le paramètre
            $redirect_url = remove_query_arg('rvk_update_permalinks');
            $redirect_url = add_query_arg('rvk_permalinks_updated', '1', $redirect_url);

            wp_redirect($redirect_url);
            exit;
        }
    }
    add_action('admin_init', 'rvk_update_existing_permalinks');
}

if (!function_exists('rvk_permalinks_success_notice')) {
    /**
     * Afficher un message de succès après avoir mis à jour les permaliens
     */
    function rvk_permalinks_success_notice()
    {
        ?>
        <div class="notice notice-success is-dismissible">
            <p>Les permaliens des salles ont été mis à jour avec succès. Les nouvelles URLs sont maintenant actives.</p>
        </div>
        <?php
    }
}

if (!function_exists('rvk_flush_rewrite_rules')) {
    /**
     * Vider les règles de réécriture lors de l'activation du plugin
     */
    function rvk_flush_rewrite_rules()
    {
        // Vider les règles de réécriture
        flush_rewrite_rules();
    }

    // Enregistrer la fonction à exécuter lors de l'activation du plugin
    register_activation_hook(__FILE__, 'rvk_flush_rewrite_rules');

    // Vider les règles de réécriture après la modification des URLs
    add_action('init', 'rvk_flush_rewrite_rules', 1000);
}

if (!function_exists('rvk_add_flush_button')) {
    /**
     * Ajouter un bouton pour vider les règles de réécriture dans l'interface d'administration
     */
    function rvk_add_flush_button()
    {
        // Vérifier si l'utilisateur a les droits d'administration
        if (!current_user_can('manage_options')) {
            return;
        }

        // Vérifier si le bouton a été cliqué depuis la page des plugins
        if (isset($_GET['rvk_flush_rules']) && $_GET['rvk_flush_rules'] == '1' && !isset($_GET['page'])) {
            flush_rewrite_rules();
            add_action('admin_notices', 'rvk_flush_success_notice');
        }

        // Ajouter le bouton sur la page des plugins
        add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'rvk_add_flush_link');
    }
    add_action('admin_init', 'rvk_add_flush_button');
}

if (!function_exists('rvk_add_flush_link')) {
    /**
     * Ajouter un lien pour vider les règles de réécriture
     */
    function rvk_add_flush_link($links)
    {
        $flush_link = '<a href="' . admin_url('plugins.php?rvk_flush_rules=1') . '" style="color: #d54e21; font-weight: bold;">Vider les règles de réécriture</a>';
        array_unshift($links, $flush_link);
        return $links;
    }
}

if (!function_exists('rvk_flush_success_notice')) {
    /**
     * Afficher un message de succès après avoir vidé les règles de réécriture
     */
    function rvk_flush_success_notice()
    {
        ?>
        <div class="notice notice-success is-dismissible">
            <p>Les règles de réécriture ont été vidées avec succès. Les nouvelles URLs sont maintenant actives.</p>
        </div>
        <?php
    }
}

if (!function_exists('rvk_add_admin_menu')) {
    /**
     * Ajouter un menu d'administration pour le plugin RVK
     */
    function rvk_add_admin_menu()
    {
        add_menu_page(
            'RVK Options', // Titre de la page
            'RVK Options', // Texte du menu
            'manage_options', // Capacité requise
            'rvk-options', // Slug du menu
            'rvk_options_page', // Fonction de callback
            'dashicons-admin-generic', // Icône
            99 // Position
        );
    }
    add_action('admin_menu', 'rvk_add_admin_menu');
}

if (!function_exists('rvk_options_page')) {
    /**
     * Afficher la page d'options du plugin
     */
    function rvk_options_page()
    {
        ?>
        <div class="wrap">
            <h1>RVK Options</h1>

            <div class="card" style="max-width: 600px; padding: 20px; margin-top: 20px;">
                <h2>Gestion des URLs</h2>
                <p>Cliquez sur le bouton ci-dessous pour mettre à jour les URLs du site après avoir modifié les règles de
                    réécriture.</p>

                <a href="<?php echo admin_url('admin.php?page=rvk-options&rvk_flush_rules=1'); ?>"
                    class="button button-primary rvk-flush-rules-button">
                    Vider les règles de réécriture
                </a>

                <?php if (isset($_GET['rvk_flush_rules']) && $_GET['rvk_flush_rules'] == '1'):
                    flush_rewrite_rules();
                    ?>
                    <div class="notice notice-success" style="margin-top: 15px;">
                        <p>Les règles de réécriture ont été vidées avec succès. Les nouvelles URLs sont maintenant actives.</p>
                    </div>
                <?php endif; ?>

                <div style="margin-top: 30px;">
                    <h3>Mise à jour des permaliens existants</h3>
                    <p>Si les URLs des salles existantes n'ont pas été mises à jour, utilisez ce bouton pour forcer la mise à
                        jour de tous les permaliens.</p>

                    <a href="<?php echo admin_url('admin.php?page=rvk-options&rvk_update_permalinks=1'); ?>"
                        class="button button-secondary rvk-update-permalinks-button"
                        style="background-color: #d54e21; color: white; border-color: #d54e21;">
                        Mettre à jour tous les permaliens
                    </a>

                    <?php if (isset($_GET['rvk_permalinks_updated']) && $_GET['rvk_permalinks_updated'] == '1'): ?>
                        <div class="notice notice-success" style="margin-top: 15px;">
                            <p>Les permaliens des salles ont été mis à jour avec succès. Les nouvelles URLs sont maintenant actives.
                            </p>
                        </div>
                    <?php endif; ?>

                    <div class="notice notice-info" style="margin-top: 15px;">
                        <p><strong>Note importante :</strong> Après avoir mis à jour les permaliens, vous devrez peut-être
                            mettre à jour manuellement les liens dans vos menus et widgets.</p>
                    </div>
                </div>

                <div style="margin-top: 30px;">
                    <h3>URLs actuelles</h3>
                    <ul style="list-style-type: disc; margin-left: 20px;">
                        <li>URL des salles: <code><?php echo home_url('/salles/'); ?></code></li>
                        <li>URL des catégories: <code><?php echo home_url('/categories-salles/'); ?></code></li>
                    </ul>
                </div>
            </div>
        </div>
        <?php
    }
}

if (!function_exists('rvk_admin_bar_menu')) {
    /**
     * Ajouter un bouton directement dans la barre d'administration
     */
    function rvk_admin_bar_menu($admin_bar)
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        // Menu principal
        $admin_bar->add_menu(array(
            'id' => 'rvk-menu',
            'title' => 'RVK',
            'href' => admin_url('admin.php?page=rvk-options'),
            'meta' => array(
                'title' => 'Options RVK',
            ),
        ));

        // Sous-menu pour vider les règles
        $admin_bar->add_menu(array(
            'id' => 'rvk-flush-rules',
            'parent' => 'rvk-menu',
            'title' => 'Vider les règles de réécriture',
            'href' => admin_url('?rvk_flush_rules=1'),
            'meta' => array(
                'title' => 'Vider les règles de réécriture pour mettre à jour les URLs',
                'class' => 'rvk-admin-button',
            ),
        ));

        // Sous-menu pour mettre à jour les permaliens
        $admin_bar->add_menu(array(
            'id' => 'rvk-update-permalinks',
            'parent' => 'rvk-menu',
            'title' => 'Mettre à jour les permaliens',
            'href' => admin_url('?rvk_update_permalinks=1'),
            'meta' => array(
                'title' => 'Mettre à jour les permaliens des salles existantes',
                'class' => 'rvk-admin-button-update',
            ),
        ));

        // Sous-menu pour accéder à la page d'options
        $admin_bar->add_menu(array(
            'id' => 'rvk-options-page',
            'parent' => 'rvk-menu',
            'title' => 'Page d\'options',
            'href' => admin_url('admin.php?page=rvk-options'),
            'meta' => array(
                'title' => 'Accéder à la page d\'options RVK',
            ),
        ));
    }
    add_action('admin_bar_menu', 'rvk_admin_bar_menu', 100);
}

if (!function_exists('rvk_admin_bar_style')) {
    /**
     * Ajouter un style pour le bouton dans la barre d'administration
     */
    function rvk_admin_bar_style()
    {
        echo '<style>
            #wp-admin-bar-rvk-menu > .ab-item {
                background-color: #2271b1 !important;
                color: white !important;
                font-weight: bold !important;
            }
            #wp-admin-bar-rvk-flush-rules .ab-item {
                color: #d54e21 !important;
                font-weight: bold !important;
            }
            #wp-admin-bar-rvk-update-permalinks .ab-item {
                color: #d54e21 !important;
                font-weight: bold !important;
            }
        </style>';
    }
    add_action('admin_head', 'rvk_admin_bar_style');
    add_action('wp_head', 'rvk_admin_bar_style');
}

if (!function_exists('rvk_process_admin_bar_flush')) {
    /**
     * Traiter la demande de vidage des règles depuis la barre d'administration
     */
    function rvk_process_admin_bar_flush()
    {
        if (isset($_GET['rvk_flush_rules']) && $_GET['rvk_flush_rules'] == '1' && current_user_can('manage_options')) {
            flush_rewrite_rules();

            // Rediriger vers la page actuelle sans le paramètre
            $redirect_url = remove_query_arg('rvk_flush_rules');

            // Ajouter un paramètre pour afficher le message de succès
            $redirect_url = add_query_arg('rvk_flushed', '1', $redirect_url);

            wp_redirect($redirect_url);
            exit;
        }
    }
    add_action('admin_init', 'rvk_process_admin_bar_flush');
}

if (!function_exists('rvk_admin_bar_success_notice')) {
    /**
     * Afficher un message de succès après avoir vidé les règles depuis la barre d'administration
     */
    function rvk_admin_bar_success_notice()
    {
        if (isset($_GET['rvk_flushed']) && $_GET['rvk_flushed'] == '1') {
            ?>
            <div class="notice notice-success is-dismissible">
                <p>Les règles de réécriture ont été vidées avec succès. Les nouvelles URLs sont maintenant actives.</p>
            </div>
            <?php
        }
    }
    add_action('admin_notices', 'rvk_admin_bar_success_notice');
}

if (!function_exists('rvk_enqueue_scripts')) {
    /**
     * Enregistrer et charger les scripts et styles du plugin
     */
    function rvk_enqueue_scripts()
    {
        // Styles pour la partie publique
        wp_enqueue_style(
            'rvk-frontend',
            plugin_dir_url(__FILE__) . 'css/rvk-frontend.css',
            array(),
            '1.0.0'
        );

        // Scripts pour la partie publique
        wp_enqueue_script(
            'rvk-frontend',
            plugin_dir_url(__FILE__) . 'js/rvk-frontend.js',
            array('jquery'),
            '1.0.0',
            true
        );
    }
    add_action('wp_enqueue_scripts', 'rvk_enqueue_scripts');
}

if (!function_exists('rvk_admin_enqueue_scripts')) {
    /**
     * Enregistrer et charger les scripts et styles d'administration
     */
    function rvk_admin_enqueue_scripts($hook)
    {
        // Charger uniquement sur les pages d'administration du plugin
        if (strpos($hook, 'rvk') !== false || $hook === 'plugins.php') {
            // Styles pour l'administration
            wp_enqueue_style(
                'rvk-admin',
                plugin_dir_url(__FILE__) . 'css/rvk-admin.css',
                array(),
                '1.0.0'
            );

            // Scripts pour l'administration
            wp_enqueue_script(
                'rvk-admin',
                plugin_dir_url(__FILE__) . 'js/rvk-admin.js',
                array('jquery'),
                '1.0.0',
                true
            );
        }
    }
    add_action('admin_enqueue_scripts', 'rvk_admin_enqueue_scripts');
}

/**
 * Ajouter un message d'administration pour vider les permaliens
 */
function rvk_admin_notice_permalink_refresh()
{
    global $pagenow;

    // Afficher uniquement sur le tableau de bord et les pages d'administration principales
    if ($pagenow == 'index.php' || $pagenow == 'plugins.php') {
        echo '<div class="notice notice-warning is-dismissible">';
        echo '<p><strong>RVK Plugin:</strong> Si vous rencontrez des erreurs 404 en accédant aux salles, veuillez <a href="' . admin_url('options-permalink.php') . '">vider les permaliens</a> en sauvegardant simplement la page des permaliens.</p>';
        echo '</div>';
    }
}
add_action('admin_notices', 'rvk_admin_notice_permalink_refresh');

/*--------------------------------------------------------------
  GESTION DES EMAILS ET FORMULAIRES
--------------------------------------------------------------*/

// Ajouter une page d'options pour les emails
function rvk_add_email_settings_page()
{
    add_submenu_page(
        'rivka-traiteur', // Parent slug
        'Paramètres Email', // Titre de la page
        'Paramètres Email', // Titre du menu
        'manage_options', // Capacité requise
        'rvk-email-settings', // Slug de la page
        'rvk_render_email_settings_page' // Fonction de callback
    );
}
add_action('admin_menu', 'rvk_add_email_settings_page');

// Rendu de la page d'options pour les emails
function rvk_render_email_settings_page()
{
    // Vérifier si on demande d'afficher les logs
    if (isset($_GET['view_logs'])) {
        rvk_display_email_logs();
        return;
    }

    ?>
    <div class="wrap">
        <h1>Paramètres des Emails</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('rvk_email_settings');
            do_settings_sections('rvk-email-settings');
            submit_button();
            ?>
        </form>

        <div class="card" style="max-width: 600px; margin-top: 20px; padding: 20px;">
            <h2>Test d'envoi d'email</h2>
            <p>Utilisez ce formulaire pour tester l'envoi d'email avec les paramètres actuels.</p>

            <form method="get" action="<?php echo admin_url('admin.php'); ?>">
                <input type="hidden" name="page" value="rvk-email-settings">
                <input type="hidden" name="rvk_test_email" value="1">

                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="test_email">Adresse email de test</label></th>
                        <td>
                            <input type="email" name="test_email" id="test_email" class="regular-text"
                                value="<?php echo esc_attr(get_option('admin_email')); ?>" required>
                            <p class="description">L'email de test sera envoyé à cette adresse.</p>
                        </td>
                    </tr>
                </table>

                <p class="submit">
                    <input type="submit" name="submit" id="submit" class="button button-primary"
                        value="Envoyer un email de test">
                </p>
            </form>

            <?php
            // Afficher un message si un test a été effectué
            if (isset($_GET['email_test'])) {
                $status = $_GET['email_test'];
                $email_to = isset($_GET['email_to']) ? sanitize_email($_GET['email_to']) : '';

                if ($status === 'success') {
                    echo '<div class="notice notice-success is-dismissible"><p>';
                    echo 'Email de test envoyé avec succès à <strong>' . esc_html($email_to) . '</strong>. ';
                    echo 'Veuillez vérifier votre boîte de réception et vos dossiers spam/indésirables.';
                    echo '</p></div>';
                } else {
                    echo '<div class="notice notice-error is-dismissible"><p>';
                    echo 'Échec de l\'envoi de l\'email de test à <strong>' . esc_html($email_to) . '</strong>. ';
                    echo 'Veuillez vérifier les logs pour plus de détails.';
                    echo '</p></div>';
                }
            }

            // Afficher un message si un test SMTP a été effectué
            if (isset($_GET['smtp_test']) && $_GET['smtp_test'] === 'completed') {
                echo '<div class="notice notice-info is-dismissible"><p>';
                echo 'Le test de configuration SMTP a été effectué. Veuillez consulter les logs pour plus de détails.';
                echo '</p></div>';
            }
            ?>

            <h3>Diagnostic de la configuration SMTP</h3>
            <p>Utilisez ce bouton pour tester la configuration SMTP et diagnostiquer les problèmes d'envoi d'email.</p>

            <form method="get" action="<?php echo admin_url('admin.php'); ?>">
                <input type="hidden" name="page" value="rvk-email-settings">
                <input type="hidden" name="rvk_test_smtp" value="1">

                <p class="submit">
                    <input type="submit" name="submit" id="submit" class="button button-secondary"
                        value="Tester la configuration SMTP">
                </p>
            </form>

            <?php
            // Afficher les liens vers les fichiers de log
            $upload_dir = wp_upload_dir();
            $test_log_file = $upload_dir['basedir'] . '/rvk-email-test-log.txt';
            $form_log_file = $upload_dir['basedir'] . '/rvk-email-log.txt';
            $debug_log_file = $upload_dir['basedir'] . '/rvk-email-debug-log.txt';
            $smtp_log_file = $upload_dir['basedir'] . '/rvk-smtp-test-log.txt';

            echo '<div style="margin-top: 20px;">';
            echo '<h3>Logs d\'envoi d\'email</h3>';

            if (file_exists($test_log_file) || file_exists($form_log_file) || file_exists($debug_log_file) || file_exists($smtp_log_file)) {
                echo '<p>Cliquez sur les liens ci-dessous pour voir les logs d\'envoi d\'email :</p>';
                echo '<ul style="list-style-type: disc; margin-left: 20px;">';

                if (file_exists($test_log_file)) {
                    echo '<li><a href="' . esc_url(add_query_arg('view_logs', 'test', admin_url('admin.php?page=rvk-email-settings'))) . '">Voir les logs des tests d\'email</a></li>';
                }

                if (file_exists($form_log_file)) {
                    echo '<li><a href="' . esc_url(add_query_arg('view_logs', 'form', admin_url('admin.php?page=rvk-email-settings'))) . '">Voir les logs des envois depuis le formulaire</a></li>';
                }

                if (file_exists($debug_log_file)) {
                    echo '<li><a href="' . esc_url(add_query_arg('view_logs', 'debug', admin_url('admin.php?page=rvk-email-settings'))) . '"><strong>Voir les logs de débogage détaillés</strong></a> (recommandé pour diagnostiquer les problèmes)</li>';
                }

                if (file_exists($smtp_log_file)) {
                    echo '<li><a href="' . esc_url(add_query_arg('view_logs', 'smtp', admin_url('admin.php?page=rvk-email-settings'))) . '"><strong>Voir les logs de test SMTP</strong></a> (diagnostic de la configuration SMTP)</li>';
                }

                echo '</ul>';
            } else {
                echo '<p>Aucun fichier de log n\'a été trouvé. Effectuez un test d\'envoi d\'email ou soumettez le formulaire de devis pour générer des logs.</p>';
            }

            echo '</div>';
            ?>
        </div>
    </div>
    <?php
}

// Enregistrer les paramètres
function rvk_register_email_settings()
{
    register_setting('rvk_email_settings', 'rvk_email_recipient');
    register_setting('rvk_email_settings', 'rvk_email_subject');
    register_setting('rvk_email_settings', 'rvk_email_template');

    add_settings_section(
        'rvk_email_settings_section',
        'Configuration des emails',
        'rvk_email_settings_section_callback',
        'rvk-email-settings'
    );

    add_settings_field(
        'rvk_email_recipient',
        'Adresse email de réception',
        'rvk_email_recipient_callback',
        'rvk-email-settings',
        'rvk_email_settings_section'
    );

    add_settings_field(
        'rvk_email_subject',
        'Sujet de l\'email',
        'rvk_email_subject_callback',
        'rvk-email-settings',
        'rvk_email_settings_section'
    );

    add_settings_field(
        'rvk_email_template',
        'Template de l\'email',
        'rvk_email_template_callback',
        'rvk-email-settings',
        'rvk_email_settings_section'
    );
}
add_action('admin_init', 'rvk_register_email_settings');

// Callbacks pour les champs
function rvk_email_settings_section_callback()
{
    echo '<p>Configurez les paramètres des emails envoyés depuis le formulaire de devis.</p>';
}

function rvk_email_recipient_callback()
{
    $value = get_option('rvk_email_recipient', get_option('admin_email'));
    echo '<input type="email" id="rvk_email_recipient" name="rvk_email_recipient" value="' . esc_attr($value) . '" class="regular-text" />';
    echo '<p class="description">Adresse email qui recevra les demandes de devis.</p>';
}

function rvk_email_subject_callback()
{
    $value = get_option('rvk_email_subject', 'Nouvelle demande de devis - RVK');
    echo '<input type="text" id="rvk_email_subject" name="rvk_email_subject" value="' . esc_attr($value) . '" class="regular-text" />';
    echo '<p class="description">Sujet de l\'email de demande de devis.</p>';
}

function rvk_email_template_callback()
{
    $default_template = '<div style="font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e8e8e8; border-radius: 5px; background-color: #ffffff; color: #333333;">
    <div style="text-align: center; margin-bottom: 30px;">
        <h1 style="color: #c0392b; font-size: 24px; margin: 0; padding: 0;">Nouvelle demande de devis</h1>
        <div style="height: 3px; background-color: #c0392b; width: 100px; margin: 15px auto;"></div>
    </div>
    
    <div style="background-color: #f9f9f9; border-left: 4px solid #c0392b; padding: 15px; margin-bottom: 20px;">
        <h2 style="color: #c0392b; font-size: 18px; margin: 0 0 10px 0;">Formule sélectionnée</h2>
        <p style="margin: 0; font-size: 16px;"><strong>{formule_nom}</strong> - {formule_prix}</p>
    </div>
    
    <div style="margin-bottom: 20px;">
        <h2 style="color: #c0392b; font-size: 18px; border-bottom: 1px solid #e8e8e8; padding-bottom: 10px;">Informations du client</h2>
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 8px 0; width: 40%;"><strong>Nom :</strong></td>
                <td style="padding: 8px 0;">{nom}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0;"><strong>Email :</strong></td>
                <td style="padding: 8px 0;"><a href="mailto:{email}" style="color: #c0392b; text-decoration: none;">{email}</a></td>
            </tr>
            <tr>
                <td style="padding: 8px 0;"><strong>Téléphone :</strong></td>
                <td style="padding: 8px 0;">{telephone}</td>
            </tr>
        </table>
    </div>
    
    <div style="margin-bottom: 20px;">
        <h2 style="color: #c0392b; font-size: 18px; border-bottom: 1px solid #e8e8e8; padding-bottom: 10px;">Détails de l\'événement</h2>
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 8px 0; width: 40%;"><strong>Nombre de personnes :</strong></td>
                <td style="padding: 8px 0;">{nombre_personnes}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0;"><strong>Date de l\'événement :</strong></td>
                <td style="padding: 8px 0;">{date_evenement}</td>
            </tr>
        </table>
    </div>
    
    <div style="margin-bottom: 20px;">
        <h2 style="color: #c0392b; font-size: 18px; border-bottom: 1px solid #e8e8e8; padding-bottom: 10px;">Message</h2>
        <div style="background-color: #f9f9f9; padding: 15px; border-radius: 4px; font-style: italic;">
            {message}
        </div>
    </div>
    
    <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e8e8e8; font-size: 12px; color: #777777; text-align: center;">
        <p>Cet email a été envoyé depuis le formulaire de devis du site RVK.</p>
        <p>© ' . date('Y') . ' Rivka Organisation - Tous droits réservés</p>
    </div>
</div>';

    $value = get_option('rvk_email_template', $default_template);
    echo '<textarea id="rvk_email_template" name="rvk_email_template" rows="15" class="large-text code">' . esc_textarea($value) . '</textarea>';
    echo '<p class="description">Template HTML de l\'email. Utilisez les variables suivantes : {formule_nom}, {formule_prix}, {nom}, {email}, {telephone}, {nombre_personnes}, {date_evenement}, {message}.</p>';
}

/*--------------------------------------------------------------
  TEST D'ENVOI D'EMAIL
--------------------------------------------------------------*/
function rvk_test_email()
{
    if (isset($_GET['rvk_test_email']) && current_user_can('manage_options')) {
        // Récupérer l'adresse email de test
        $test_email = isset($_GET['test_email']) ? sanitize_email($_GET['test_email']) : get_option('admin_email');

        // Récupérer l'adresse email configurée
        $email_to = get_option('rvk_email_to', get_option('admin_email'));

        // Utiliser l'adresse de test si fournie, sinon utiliser l'adresse configurée
        $to = !empty($test_email) ? $test_email : $email_to;

        $subject = '[TEST] Email de test depuis RVK';
        $message = '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px;">
            <h2 style="color: #333; border-bottom: 2px solid #ddd; padding-bottom: 10px;">Email de test</h2>
            <p>Ceci est un email de test envoyé depuis le site RVK.</p>
            <p>Date et heure: ' . date('Y-m-d H:i:s') . '</p>
            <p>Si vous recevez cet email, cela signifie que la configuration d\'envoi d\'email fonctionne correctement.</p>
            <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #ddd; font-size: 12px; color: #777;">
                <p>Cet email a été envoyé depuis la page de paramètres du plugin RVK.</p>
            </div>
        </div>';

        // Définir les en-têtes
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: [DEV] Rivka Organisation <' . get_option('admin_email') . '>',
            'Reply-To: ' . get_option('admin_email')
        );

        // Vérifier si la fonction fcp_send_email existe
        $method_used = "wp_mail";
        $result = false;

        if (function_exists('fcp_send_email')) {
            $result = fcp_send_email($to, $subject, $message, $headers);
            $fcp_email_method = get_transient('fcp_email_method');
            if (!empty($fcp_email_method)) {
                $method_used = $fcp_email_method;
            }
        } else {
            // Utiliser wp_mail si fcp_send_email n'existe pas
            $result = wp_mail($to, $subject, $message, $headers);
        }

        // Journaliser le résultat
        $upload_dir = wp_upload_dir();
        $log_file = $upload_dir['basedir'] . '/rvk-email-test-log.txt';
        $log_message = "Tentative d'envoi d'email de test:\n";
        $log_message .= "Destinataire: " . $to . "\n";
        $log_message .= "Sujet: " . $subject . "\n";
        $log_message .= "From: " . $headers[1] . "\n";
        $log_message .= "Reply-To: " . $headers[2] . "\n";
        $log_message .= "Résultat de l'envoi: " . ($result ? 'Succès' : 'Échec') . "\n";
        $log_message .= "Méthode: " . $method_used . "\n";

        // Vérifier si un plugin SMTP est actif
        $smtp_plugin = '';
        if (function_exists('is_plugin_active')) {
            if (is_plugin_active('wp-mail-smtp/wp-mail-smtp.php')) {
                $smtp_plugin = 'WP Mail SMTP';
            } elseif (is_plugin_active('easy-wp-smtp/easy-wp-smtp.php')) {
                $smtp_plugin = 'Easy WP SMTP';
            }
        } else {
            include_once ABSPATH . 'wp-admin/includes/plugin.php';
            if (function_exists('is_plugin_active')) {
                if (is_plugin_active('wp-mail-smtp/wp-mail-smtp.php')) {
                    $smtp_plugin = 'WP Mail SMTP';
                } elseif (is_plugin_active('easy-wp-smtp/easy-wp-smtp.php')) {
                    $smtp_plugin = 'Easy WP SMTP';
                }
            }
        }

        if (!empty($smtp_plugin)) {
            $log_message .= "Plugin SMTP actif: " . $smtp_plugin . "\n";
        }

        $log_message .= "Date: " . date('Y-m-d H:i:s') . "\n";
        $log_message .= "------------------------------------------------\n";

        file_put_contents($log_file, $log_message, FILE_APPEND);

        // Rediriger avec un message de succès ou d'échec
        $redirect_url = add_query_arg(
            array(
                'page' => 'rvk-email-settings',
                'email_test' => $result ? 'success' : 'error',
                'email_to' => $to
            ),
            admin_url('admin.php')
        );

        wp_redirect($redirect_url);
        exit;
    }
}
add_action('admin_init', 'rvk_test_email');

// Fonction pour tester la configuration SMTP
function rvk_test_smtp_configuration()
{
    if (isset($_GET['rvk_test_smtp']) && current_user_can('manage_options')) {
        // Créer un fichier de log dans le dossier uploads
        $upload_dir = wp_upload_dir();
        $log_file = $upload_dir['basedir'] . '/rvk-smtp-test-log.txt';

        $log_message = "=== TEST DE CONFIGURATION SMTP (" . date('Y-m-d H:i:s') . ") ===\n";

        // Vérifier si un plugin SMTP est actif
        $smtp_plugin = 'Aucun';
        if (!function_exists('is_plugin_active')) {
            include_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        if (function_exists('is_plugin_active')) {
            if (is_plugin_active('wp-mail-smtp/wp-mail-smtp.php')) {
                $smtp_plugin = 'WP Mail SMTP';
            } elseif (is_plugin_active('easy-wp-smtp/easy-wp-smtp.php')) {
                $smtp_plugin = 'Easy WP SMTP';
            } elseif (is_plugin_active('post-smtp/postman-smtp.php')) {
                $smtp_plugin = 'Post SMTP';
            }
        }

        $log_message .= "Plugin SMTP actif: " . $smtp_plugin . "\n";

        // Tester la connexion au serveur SMTP si un plugin est actif
        if ($smtp_plugin !== 'Aucun') {
            $log_message .= "Un plugin SMTP est actif. La configuration SMTP devrait être gérée par ce plugin.\n";
        } else {
            $log_message .= "Aucun plugin SMTP n'est actif. WordPress utilisera la fonction mail() de PHP par défaut.\n";

            // Vérifier si la fonction mail() est disponible
            if (function_exists('mail')) {
                $log_message .= "La fonction mail() de PHP est disponible.\n";

                // Vérifier la configuration PHP
                $log_message .= "Configuration PHP pour l'envoi d'emails:\n";
                $log_message .= "- sendmail_path: " . ini_get('sendmail_path') . "\n";
                $log_message .= "- SMTP: " . ini_get('SMTP') . "\n";
                $log_message .= "- smtp_port: " . ini_get('smtp_port') . "\n";
            } else {
                $log_message .= "La fonction mail() de PHP n'est pas disponible. L'envoi d'emails ne fonctionnera pas.\n";
            }
        }

        // Vérifier les fonctions de débogage de WordPress
        $log_message .= "\nConfiguration WordPress:\n";
        $log_message .= "- WP_DEBUG: " . (defined('WP_DEBUG') && WP_DEBUG ? 'Activé' : 'Désactivé') . "\n";
        $log_message .= "- WP_DEBUG_LOG: " . (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG ? 'Activé' : 'Désactivé') . "\n";

        // Vérifier si le serveur peut se connecter à des services externes
        $log_message .= "\nTest de connectivité externe:\n";

        // Tester la connexion à Gmail SMTP
        $connection = @fsockopen('smtp.gmail.com', 587, $errno, $errstr, 5);
        if ($connection) {
            $log_message .= "- Connexion à smtp.gmail.com:587: Succès\n";
            fclose($connection);
        } else {
            $log_message .= "- Connexion à smtp.gmail.com:587: Échec (Erreur: $errno - $errstr)\n";
        }

        // Tester la connexion à Outlook SMTP
        $connection = @fsockopen('smtp-mail.outlook.com', 587, $errno, $errstr, 5);
        if ($connection) {
            $log_message .= "- Connexion à smtp-mail.outlook.com:587: Succès\n";
            fclose($connection);
        } else {
            $log_message .= "- Connexion à smtp-mail.outlook.com:587: Échec (Erreur: $errno - $errstr)\n";
        }

        $log_message .= "\nRecommandations:\n";
        if ($smtp_plugin === 'Aucun') {
            $log_message .= "- Installez un plugin SMTP comme WP Mail SMTP pour améliorer la délivrabilité des emails.\n";
            $log_message .= "- Configurez le plugin avec les paramètres SMTP de votre fournisseur d'email.\n";
        } else {
            $log_message .= "- Vérifiez la configuration de votre plugin SMTP.\n";
            $log_message .= "- Assurez-vous que les identifiants SMTP sont corrects.\n";
        }

        $log_message .= "- Vérifiez que votre hébergeur n'a pas de restrictions sur l'envoi d'emails.\n";
        $log_message .= "- Utilisez une adresse email professionnelle pour l'envoi (évitez les adresses gratuites comme Gmail ou Hotmail).\n";

        $log_message .= "\nDate du test: " . date('Y-m-d H:i:s') . "\n";
        $log_message .= "------------------------------------------------\n";

        file_put_contents($log_file, $log_message, FILE_APPEND);

        // Rediriger vers la page des paramètres avec un message
        wp_redirect(add_query_arg(
            array(
                'page' => 'rvk-email-settings',
                'smtp_test' => 'completed'
            ),
            admin_url('admin.php')
        ));
        exit;
    }
}
add_action('admin_init', 'rvk_test_smtp_configuration');

// Afficher les messages de test d'email
function rvk_display_email_test_message()
{
    if (isset($_GET['email_test'])) {
        if ($_GET['email_test'] === 'success') {
            echo '<div class="notice notice-success is-dismissible"><p>L\'email de test a été envoyé avec succès. Veuillez vérifier votre boîte de réception.</p></div>';
        } else {
            echo '<div class="notice notice-error is-dismissible"><p>L\'envoi de l\'email de test a échoué. Veuillez vérifier votre configuration d\'email.</p></div>';
        }
    }
}
add_action('admin_notices', 'rvk_display_email_test_message');

// Ajouter une section pour les informations de débogage d'email
function rvk_add_email_debug_section()
{
    add_settings_section(
        'rvk_email_debug_section',
        'Informations de débogage',
        'rvk_email_debug_section_callback',
        'rvk-email-settings'
    );
}
add_action('admin_init', 'rvk_add_email_debug_section');

// Callback pour la section de débogage d'email
function rvk_email_debug_section_callback()
{
    global $wp_version;

    echo '<div style="background-color: #f9f9f9; padding: 15px; border-radius: 5px; margin-bottom: 20px;">';

    // Informations sur WordPress
    echo '<h3>Informations système</h3>';
    echo '<ul>';
    echo '<li><strong>Version WordPress :</strong> ' . esc_html($wp_version) . '</li>';
    echo '<li><strong>Version PHP :</strong> ' . esc_html(phpversion()) . '</li>';
    echo '<li><strong>Serveur web :</strong> ' . esc_html($_SERVER['SERVER_SOFTWARE'] ?? 'Inconnu') . '</li>';
    echo '</ul>';

    // Informations sur la configuration d'email
    echo '<h3>Configuration d\'email</h3>';
    echo '<ul>';
    echo '<li><strong>Email admin WordPress :</strong> ' . esc_html(get_option('admin_email')) . '</li>';
    echo '<li><strong>Email de réception configuré :</strong> ' . esc_html(get_option('rvk_email_recipient', 'Non configuré')) . '</li>';

    // Vérifier si un plugin d'email est actif
    $email_plugins = array(
        'wp-mail-smtp/wp_mail_smtp.php' => 'WP Mail SMTP',
        'easy-wp-smtp/easy-wp-smtp.php' => 'Easy WP SMTP',
        'post-smtp/postman-smtp.php' => 'Post SMTP',
        'mailgun/mailgun.php' => 'Mailgun',
        'sendgrid-email-delivery-simplified/wpsendgrid.php' => 'SendGrid',
    );

    $active_email_plugin = 'Aucun';
    foreach ($email_plugins as $plugin_file => $plugin_name) {
        if (is_plugin_active($plugin_file)) {
            $active_email_plugin = $plugin_name;
            break;
        }
    }

    echo '<li><strong>Plugin d\'email actif :</strong> ' . esc_html($active_email_plugin) . '</li>';
    echo '</ul>';

    // Recommandations
    echo '<h3>Recommandations</h3>';
    echo '<p>Si vous rencontrez des problèmes d\'envoi d\'email, voici quelques recommandations :</p>';
    echo '<ol>';
    echo '<li>Installez et configurez un plugin d\'email comme <a href="https://wordpress.org/plugins/wp-mail-smtp/" target="_blank">WP Mail SMTP</a> pour améliorer la délivrabilité des emails.</li>';
    echo '<li>Vérifiez que l\'adresse email de réception est correcte et accessible.</li>';
    echo '<li>Vérifiez les dossiers de spam/indésirables de votre boîte email.</li>';
    echo '<li>Utilisez le bouton "Envoyer un email de test" ci-dessous pour tester la configuration.</li>';
    echo '</ol>';

    echo '</div>';
}

// Fonction pour afficher les logs d'email
function rvk_display_email_logs()
{
    // Vérifier si on demande d'effacer les logs
    if (isset($_GET['clear_logs']) && isset($_GET['view_logs'])) {
        $log_type = sanitize_text_field($_GET['view_logs']);
        $upload_dir = wp_upload_dir();
        $log_file = '';

        if ($log_type === 'test') {
            $log_file = $upload_dir['basedir'] . '/rvk-email-test-log.txt';
        } elseif ($log_type === 'form') {
            $log_file = $upload_dir['basedir'] . '/rvk-email-log.txt';
        } elseif ($log_type === 'debug') {
            $log_file = $upload_dir['basedir'] . '/rvk-email-debug-log.txt';
        } elseif ($log_type === 'smtp') {
            $log_file = $upload_dir['basedir'] . '/rvk-smtp-test-log.txt';
        }

        if (!empty($log_file) && file_exists($log_file)) {
            // Effacer le contenu du fichier
            file_put_contents($log_file, '');

            // Rediriger vers la page des logs avec un message
            wp_redirect(add_query_arg(array(
                'page' => 'rvk-email-settings',
                'view_logs' => $log_type,
                'logs_cleared' => '1'
            ), admin_url('admin.php')));
            exit;
        }
    }

    // Déterminer quel fichier de log afficher
    $log_type = isset($_GET['view_logs']) ? sanitize_text_field($_GET['view_logs']) : 'test';
    $upload_dir = wp_upload_dir();
    $log_file = '';
    $log_title = '';

    if ($log_type === 'test') {
        $log_file = $upload_dir['basedir'] . '/rvk-email-test-log.txt';
        $log_title = 'Logs des tests d\'email';
    } elseif ($log_type === 'form') {
        $log_file = $upload_dir['basedir'] . '/rvk-email-log.txt';
        $log_title = 'Logs des envois depuis le formulaire';
    } elseif ($log_type === 'debug') {
        $log_file = $upload_dir['basedir'] . '/rvk-email-debug-log.txt';
        $log_title = 'Logs de débogage détaillés des envois d\'email';
    } elseif ($log_type === 'smtp') {
        $log_file = $upload_dir['basedir'] . '/rvk-smtp-test-log.txt';
        $log_title = 'Logs de test de la configuration SMTP';
    }

    // Afficher le contenu du fichier de log
    ?>
    <div class="wrap">
        <h1><?php echo esc_html($log_title); ?></h1>

        <?php
        // Afficher un message si les logs ont été effacés
        if (isset($_GET['logs_cleared'])) {
            echo '<div class="notice notice-success is-dismissible"><p>Les logs ont été effacés avec succès.</p></div>';
        }
        ?>

        <div style="margin-bottom: 15px;">
            <a href="<?php echo esc_url(admin_url('admin.php?page=rvk-email-settings')); ?>" class="button">
                &larr; Retour aux paramètres
            </a>

            <?php if (file_exists($log_file) && filesize($log_file) > 0): ?>
                <a href="<?php echo esc_url(add_query_arg(array('clear_logs' => '1'), admin_url('admin.php?page=rvk-email-settings&view_logs=' . $log_type))); ?>"
                    class="button" style="margin-left: 10px;"
                    onclick="return confirm('Êtes-vous sûr de vouloir effacer tous les logs ?');">
                    Effacer les logs
                </a>
            <?php endif; ?>

            <?php if ($log_type === 'test'): ?>
                <a href="<?php echo esc_url(add_query_arg('view_logs', 'form', admin_url('admin.php?page=rvk-email-settings'))); ?>"
                    class="button" style="margin-left: 10px;">
                    Voir les logs du formulaire
                </a>
                <a href="<?php echo esc_url(add_query_arg('view_logs', 'debug', admin_url('admin.php?page=rvk-email-settings'))); ?>"
                    class="button" style="margin-left: 10px;">
                    Voir les logs de débogage détaillés
                </a>
                <a href="<?php echo esc_url(add_query_arg('view_logs', 'smtp', admin_url('admin.php?page=rvk-email-settings'))); ?>"
                    class="button" style="margin-left: 10px;">
                    Voir les logs de test SMTP
                </a>
            <?php elseif ($log_type === 'form'): ?>
                <a href="<?php echo esc_url(add_query_arg('view_logs', 'test', admin_url('admin.php?page=rvk-email-settings'))); ?>"
                    class="button" style="margin-left: 10px;">
                    Voir les logs des tests
                </a>
                <a href="<?php echo esc_url(add_query_arg('view_logs', 'debug', admin_url('admin.php?page=rvk-email-settings'))); ?>"
                    class="button" style="margin-left: 10px;">
                    Voir les logs de débogage détaillés
                </a>
                <a href="<?php echo esc_url(add_query_arg('view_logs', 'smtp', admin_url('admin.php?page=rvk-email-settings'))); ?>"
                    class="button" style="margin-left: 10px;">
                    Voir les logs de test SMTP
                </a>
            <?php elseif ($log_type === 'debug'): ?>
                <a href="<?php echo esc_url(add_query_arg('view_logs', 'test', admin_url('admin.php?page=rvk-email-settings'))); ?>"
                    class="button" style="margin-left: 10px;">
                    Voir les logs des tests
                </a>
                <a href="<?php echo esc_url(add_query_arg('view_logs', 'form', admin_url('admin.php?page=rvk-email-settings'))); ?>"
                    class="button" style="margin-left: 10px;">
                    Voir les logs du formulaire
                </a>
                <a href="<?php echo esc_url(add_query_arg('view_logs', 'smtp', admin_url('admin.php?page=rvk-email-settings'))); ?>"
                    class="button" style="margin-left: 10px;">
                    Voir les logs de test SMTP
                </a>
            <?php elseif ($log_type === 'smtp'): ?>
                <a href="<?php echo esc_url(add_query_arg('view_logs', 'test', admin_url('admin.php?page=rvk-email-settings'))); ?>"
                    class="button" style="margin-left: 10px;">
                    Voir les logs des tests
                </a>
                <a href="<?php echo esc_url(add_query_arg('view_logs', 'form', admin_url('admin.php?page=rvk-email-settings'))); ?>"
                    class="button" style="margin-left: 10px;">
                    Voir les logs du formulaire
                </a>
                <a href="<?php echo esc_url(add_query_arg('view_logs', 'debug', admin_url('admin.php?page=rvk-email-settings'))); ?>"
                    class="button" style="margin-left: 10px;">
                    Voir les logs de débogage détaillés
                </a>
            <?php endif; ?>
        </div>

        <div style="background-color: #f9f9f9; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
            <?php
            if (file_exists($log_file) && filesize($log_file) > 0) {
                $log_content = file_get_contents($log_file);
                echo '<pre style="white-space: pre-wrap; word-wrap: break-word; max-height: 500px; overflow-y: auto; padding: 10px; background-color: #fff; border: 1px solid #ddd;">';
                echo esc_html($log_content);
                echo '</pre>';
            } else {
                echo '<p>Aucun log disponible. Effectuez un test d\'envoi d\'email ou soumettez le formulaire de devis pour générer des logs.</p>';
            }
            ?>
        </div>
    </div>
    <?php
}
?>