<?php
/**
 * Script pour importer les données du portfolio à partir d'un fichier CSV
 * 
 * Ce script lit un fichier CSV contenant des données de salles et les importe
 * dans WordPress en tant que posts de type "cpt_portfolio".
 */

// Vérifier si le script est exécuté dans l'environnement WordPress
if (!defined('ABSPATH')) {
    // Charger WordPress si exécuté directement
    $wp_load_path = dirname(dirname(dirname(dirname(__FILE__)))) . '/wp-load.php';
    if (file_exists($wp_load_path)) {
        require_once($wp_load_path);
    } else {
        die('WordPress non trouvé. Ce script doit être exécuté dans l\'environnement WordPress.');
    }
}

// Vérifier les droits d'administration
if (!current_user_can('manage_options')) {
    wp_die('Vous n\'avez pas les droits suffisants pour exécuter ce script.');
}

// Définir le chemin vers le fichier CSV
$csv_file = dirname(dirname(dirname(dirname(__FILE__)))) . '/Donn_es_Optimis_es.csv';

// Vérifier si le fichier CSV existe
if (!file_exists($csv_file)) {
    wp_die('Le fichier CSV n\'existe pas: ' . $csv_file);
}

// Ouvrir le fichier CSV
$file = fopen($csv_file, 'r');

// Compteurs pour les statistiques
$total_rows = 0;
$imported_rows = 0;
$updated_rows = 0;
$skipped_rows = 0;

// Lire la première ligne pour obtenir les en-têtes
$headers = fgetcsv($file);

// Créer un tableau associatif des en-têtes pour faciliter l'accès aux colonnes
$header_indices = array();
foreach ($headers as $index => $header) {
    $header_indices[$header] = $index;
}

// Vérifier si les colonnes requises existent
$required_columns = array('Titre', 'Description', 'Ville', 'Nombre de places assises', 'Prix');
foreach ($required_columns as $column) {
    if (!isset($header_indices[$column])) {
        wp_die('La colonne "' . $column . '" n\'a pas été trouvée dans le fichier CSV.');
    }
}

echo '<div style="font-family: Arial, sans-serif; max-width: 800px; margin: 20px auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px;">';
echo '<h1>Importation des données du portfolio</h1>';

// Lire chaque ligne du CSV
while (($row = fgetcsv($file)) !== false) {
    $total_rows++;

    // Vérifier si le titre existe
    if (!isset($row[$header_indices['Titre']]) || empty($row[$header_indices['Titre']])) {
        echo '<div style="margin-bottom: 10px; padding: 10px; border-bottom: 1px solid #eee; color: orange;">';
        echo '<p><strong>Ligne ' . $total_rows . ':</strong> Ignorée - Titre manquant</p>';
        echo '</div>';
        $skipped_rows++;
        continue;
    }

    $title = trim($row[$header_indices['Titre']]);

    // Vérifier si un post avec ce titre existe déjà
    $existing_post = get_page_by_title($title, OBJECT, 'cpt_portfolio');

    // Préparer les données du post
    $post_data = array(
        'post_title' => $title,
        'post_content' => isset($row[$header_indices['Description']]) ? trim($row[$header_indices['Description']]) : '',
        'post_status' => 'publish',
        'post_type' => 'cpt_portfolio',
    );

    // Préparer les champs ACF
    $acf_fields = array();

    // Ajouter les champs ACF en fonction des colonnes du CSV
    if (isset($header_indices['Ville']) && isset($row[$header_indices['Ville']])) {
        $acf_fields['ville'] = trim($row[$header_indices['Ville']]);
    }

    if (isset($header_indices['Nombre de places assises']) && isset($row[$header_indices['Nombre de places assises']])) {
        $acf_fields['nombre_places'] = trim($row[$header_indices['Nombre de places assises']]);
    }

    if (isset($header_indices['Houppa']) && isset($row[$header_indices['Houppa']])) {
        $acf_fields['houppa'] = trim($row[$header_indices['Houppa']]);
    }

    if (isset($header_indices['Prix']) && isset($row[$header_indices['Prix']])) {
        $acf_fields['prix'] = trim($row[$header_indices['Prix']]);
    }

    if (isset($header_indices['Parking']) && isset($row[$header_indices['Parking']])) {
        $acf_fields['parking'] = trim($row[$header_indices['Parking']]);
    }

    if (isset($header_indices['Décoration']) && isset($row[$header_indices['Décoration']])) {
        $acf_fields['decoration'] = trim($row[$header_indices['Décoration']]);
    }

    if (isset($header_indices['Particularité']) && isset($row[$header_indices['Particularité']])) {
        $acf_fields['particularite'] = trim($row[$header_indices['Particularité']]);
    }

    // Traiter l'image
    $image_url = '';
    if (isset($header_indices['Image']) && isset($row[$header_indices['Image']]) && !empty($row[$header_indices['Image']])) {
        $image_url = trim($row[$header_indices['Image']]);
    }

    echo '<div style="margin-bottom: 10px; padding: 10px; border-bottom: 1px solid #eee;">';
    echo '<p><strong>Titre:</strong> ' . $title . '</p>';

    // Insérer ou mettre à jour le post
    if ($existing_post) {
        $post_data['ID'] = $existing_post->ID;
        $post_id = wp_update_post($post_data);
        echo '<p style="color: blue;"><strong>Statut:</strong> Post mis à jour (ID: ' . $post_id . ')</p>';
        $updated_rows++;
    } else {
        $post_id = wp_insert_post($post_data);
        echo '<p style="color: green;"><strong>Statut:</strong> Nouveau post créé (ID: ' . $post_id . ')</p>';
        $imported_rows++;
    }

    // Mettre à jour les champs ACF
    if ($post_id && !is_wp_error($post_id)) {
        foreach ($acf_fields as $field_key => $field_value) {
            if (!empty($field_value)) {
                update_field($field_key, $field_value, $post_id);
                echo '<p><strong>' . ucfirst($field_key) . ':</strong> ' . $field_value . '</p>';
            }
        }

        // Traiter l'image si elle existe
        if (!empty($image_url)) {
            echo '<p><strong>Image URL:</strong> ' . $image_url . '</p>';

            // Vérifier si l'image est déjà téléchargée localement
            $upload_dir = wp_upload_dir();
            $images_dir = $upload_dir['basedir'] . '/portfolio-images';
            $filename = basename($image_url);
            $local_file = $images_dir . '/' . $filename;

            // Si un titre est disponible, vérifier aussi avec le nom de fichier basé sur le titre
            $title_filename = sanitize_title($title) . '.' . pathinfo($filename, PATHINFO_EXTENSION);
            $local_file_title = $images_dir . '/' . $title_filename;

            if (file_exists($local_file) || file_exists($local_file_title)) {
                $file_to_use = file_exists($local_file) ? $local_file : $local_file_title;
                echo '<p style="color: green;"><strong>Image:</strong> Utilisation de l\'image locale: ' . basename($file_to_use) . '</p>';

                // Importer l'image dans la médiathèque et l'attacher au post
                $file_array = array(
                    'name' => basename($file_to_use),
                    'tmp_name' => $file_to_use,
                    'error' => 0,
                    'size' => filesize($file_to_use)
                );

                $overrides = array(
                    'test_form' => false,
                    'test_size' => true,
                );

                // Utiliser la fonction WordPress pour gérer le téléchargement
                $time = current_time('mysql');
                $file = wp_handle_sideload($file_array, $overrides, $time);

                if (!isset($file['error'])) {
                    // Insérer l'image dans la médiathèque
                    $attachment = array(
                        'post_mime_type' => $file['type'],
                        'post_title' => $title,
                        'post_content' => '',
                        'post_status' => 'inherit'
                    );

                    $attach_id = wp_insert_attachment($attachment, $file['file'], $post_id);

                    // Générer les métadonnées pour l'image
                    require_once(ABSPATH . 'wp-admin/includes/image.php');
                    $attach_data = wp_generate_attachment_metadata($attach_id, $file['file']);
                    wp_update_attachment_metadata($attach_id, $attach_data);

                    // Définir l'image comme image à la une
                    set_post_thumbnail($post_id, $attach_id);

                    echo '<p style="color: green;"><strong>Image:</strong> Importée avec succès et définie comme image à la une</p>';
                } else {
                    echo '<p style="color: red;"><strong>Erreur d\'importation de l\'image:</strong> ' . $file['error'] . '</p>';
                }
            } else {
                echo '<p style="color: orange;"><strong>Image:</strong> Fichier local non trouvé. Exécutez d\'abord le script de téléchargement des images.</p>';
            }
        }
    } else {
        echo '<p style="color: red;"><strong>Erreur:</strong> Impossible de créer/mettre à jour le post</p>';
    }

    echo '</div>';
}

// Fermer le fichier CSV
fclose($file);

// Afficher les statistiques
echo '<div style="margin-top: 20px; padding: 15px; background-color: #f5f5f5; border-radius: 5px;">';
echo '<h2>Résumé</h2>';
echo '<p>Total des lignes traitées: ' . $total_rows . '</p>';
echo '<p style="color: green;">Nouveaux posts créés: ' . $imported_rows . '</p>';
echo '<p style="color: blue;">Posts mis à jour: ' . $updated_rows . '</p>';
echo '<p style="color: orange;">Lignes ignorées: ' . $skipped_rows . '</p>';
echo '</div>';

echo '<div style="margin-top: 20px;">';
echo '<h2>Étapes suivantes</h2>';
echo '<p>Les données ont été importées avec succès. Vous pouvez maintenant:</p>';
echo '<ul>';
echo '<li>Vérifier les posts importés dans l\'interface d\'administration</li>';
echo '<li>Mettre à jour les permaliens pour utiliser le format "salles" au lieu de "portfolio"</li>';
echo '<li>Associer les posts aux catégories appropriées</li>';
echo '</ul>';
echo '</div>';

echo '</div>';

/**
 * Traite l'importation du portfolio à partir d'un fichier CSV téléchargé
 * 
 * @param string $file_path Chemin vers le fichier CSV temporaire
 * @return void
 */
function process_portfolio_import($file_path)
{
    if (!file_exists($file_path)) {
        echo '<div class="error"><p>Le fichier CSV n\'existe pas: ' . esc_html($file_path) . '</p></div>';
        return;
    }

    // Ouvrir le fichier CSV
    $file = fopen($file_path, 'r');

    // Compteurs pour les statistiques
    $total_rows = 0;
    $imported_rows = 0;
    $updated_rows = 0;
    $skipped_rows = 0;

    // Lire la première ligne pour obtenir les en-têtes
    $headers = fgetcsv($file);

    // Créer un tableau associatif des en-têtes pour faciliter l'accès aux colonnes
    $header_indices = array();
    foreach ($headers as $index => $header) {
        $header_indices[$header] = $index;
    }

    // Vérifier si les colonnes requises existent
    $required_columns = array('Titre', 'Description', 'Ville', 'Nombre de places assises', 'Prix');
    foreach ($required_columns as $column) {
        if (!isset($header_indices[$column])) {
            echo '<div class="error"><p>La colonne "' . esc_html($column) . '" n\'a pas été trouvée dans le fichier CSV.</p></div>';
            fclose($file);
            return;
        }
    }

    echo '<div style="font-family: Arial, sans-serif; max-width: 800px; margin: 20px auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px;">';
    echo '<h1>Importation des données du portfolio</h1>';

    // Lire chaque ligne du CSV
    while (($row = fgetcsv($file)) !== false) {
        $total_rows++;

        // Vérifier si le titre existe
        if (!isset($row[$header_indices['Titre']]) || empty($row[$header_indices['Titre']])) {
            echo '<div style="margin-bottom: 10px; padding: 10px; border-bottom: 1px solid #eee; color: orange;">';
            echo '<p><strong>Ligne ' . $total_rows . ':</strong> Ignorée - Titre manquant</p>';
            echo '</div>';
            $skipped_rows++;
            continue;
        }

        $title = trim($row[$header_indices['Titre']]);

        // Vérifier si un post avec ce titre existe déjà
        $existing_post = get_page_by_title($title, OBJECT, 'cpt_portfolio');

        // Préparer les données du post
        $post_data = array(
            'post_title' => $title,
            'post_content' => isset($row[$header_indices['Description']]) ? trim($row[$header_indices['Description']]) : '',
            'post_status' => 'publish',
            'post_type' => 'cpt_portfolio',
        );

        // Préparer les champs ACF
        $acf_fields = array();

        // Ajouter les champs ACF en fonction des colonnes du CSV
        if (isset($header_indices['Ville']) && isset($row[$header_indices['Ville']])) {
            $acf_fields['ville'] = trim($row[$header_indices['Ville']]);
        }

        if (isset($header_indices['Nombre de places assises']) && isset($row[$header_indices['Nombre de places assises']])) {
            $acf_fields['nombre_places'] = trim($row[$header_indices['Nombre de places assises']]);
        }

        if (isset($header_indices['Prix']) && isset($row[$header_indices['Prix']])) {
            $acf_fields['prix'] = trim($row[$header_indices['Prix']]);
        }

        // Créer ou mettre à jour le post
        if ($existing_post) {
            // Mettre à jour le post existant
            $post_data['ID'] = $existing_post->ID;
            $post_id = wp_update_post($post_data);
            $action = 'mis à jour';
            $updated_rows++;
        } else {
            // Créer un nouveau post
            $post_id = wp_insert_post($post_data);
            $action = 'créé';
            $imported_rows++;
        }

        echo '<div style="margin-bottom: 20px; padding: 15px; border-bottom: 1px solid #eee;">';
        echo '<h3>' . esc_html($title) . '</h3>';
        echo '<p><strong>Statut:</strong> Post ' . $action . ' avec succès (ID: ' . $post_id . ')</p>';

        // Mettre à jour les champs ACF si le post a été créé/mis à jour avec succès
        if ($post_id && !is_wp_error($post_id)) {
            foreach ($acf_fields as $field_name => $field_value) {
                update_field($field_name, $field_value, $post_id);
                echo '<p><strong>' . esc_html(ucfirst($field_name)) . ':</strong> ' . esc_html($field_value) . '</p>';
            }

            // Traiter l'image si disponible
            if (isset($header_indices['Image']) && isset($row[$header_indices['Image']]) && !empty($row[$header_indices['Image']])) {
                $image_url = trim($row[$header_indices['Image']]);
                $image_name = basename($image_url);
                $upload_dir = wp_upload_dir();
                $image_path = $upload_dir['path'] . '/' . $image_name;

                // Vérifier si l'image existe localement
                if (file_exists($image_path)) {
                    // Préparer les données pour l'attachement
                    $file = array(
                        'name' => $image_name,
                        'type' => mime_content_type($image_path),
                        'tmp_name' => $image_path,
                        'error' => 0,
                        'size' => filesize($image_path)
                    );

                    // Importer l'image dans la médiathèque
                    $attachment = array(
                        'post_mime_type' => $file['type'],
                        'post_title' => sanitize_file_name($image_name),
                        'post_content' => '',
                        'post_status' => 'inherit'
                    );

                    $attach_id = wp_insert_attachment($attachment, $file['tmp_name'], $post_id);

                    // Générer les métadonnées pour l'image
                    require_once(ABSPATH . 'wp-admin/includes/image.php');
                    $attach_data = wp_generate_attachment_metadata($attach_id, $file['tmp_name']);
                    wp_update_attachment_metadata($attach_id, $attach_data);

                    // Définir l'image comme image à la une
                    set_post_thumbnail($post_id, $attach_id);

                    echo '<p style="color: green;"><strong>Image:</strong> Importée avec succès et définie comme image à la une</p>';
                } else {
                    echo '<p style="color: orange;"><strong>Image:</strong> Fichier local non trouvé. Exécutez d\'abord le script de téléchargement des images.</p>';
                }
            }
        } else {
            echo '<p style="color: red;"><strong>Erreur:</strong> Impossible de créer/mettre à jour le post</p>';
        }

        echo '</div>';
    }

    // Fermer le fichier CSV
    fclose($file);

    // Afficher les statistiques
    echo '<div style="margin-top: 20px; padding: 15px; background-color: #f5f5f5; border-radius: 5px;">';
    echo '<h2>Résumé</h2>';
    echo '<p>Total des lignes traitées: ' . $total_rows . '</p>';
    echo '<p style="color: green;">Nouveaux posts créés: ' . $imported_rows . '</p>';
    echo '<p style="color: blue;">Posts mis à jour: ' . $updated_rows . '</p>';
    echo '<p style="color: orange;">Lignes ignorées: ' . $skipped_rows . '</p>';
    echo '</div>';

    echo '</div>';
}
?>