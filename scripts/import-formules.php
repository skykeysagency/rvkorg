<?php
/**
 * Script d'importation des formules depuis des fichiers CSV
 * 
 * Ce script permet d'importer des formules à partir de fichiers CSV et de les ajouter
 * à la liste des prix dans WordPress.
 */

// Empêcher l'accès direct au fichier
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(dirname(dirname(dirname(__FILE__)))) . '/');
    require_once(ABSPATH . 'wp-load.php');
}

// Vérifier si l'utilisateur est connecté et a les droits d'administration
if (!current_user_can('manage_options')) {
    wp_die('Vous n\'avez pas les droits suffisants pour accéder à cette page.');
}

/**
 * Fonction pour importer les formules depuis un fichier CSV
 * 
 * @param string $file_path Chemin vers le fichier CSV
 * @param string $category Catégorie de la formule (ex: Service traiteur, Bar mitsva, etc.)
 * @return array Résultats de l'importation
 */
function import_formules_from_csv($file_path, $category)
{
    $results = array(
        'success' => 0,
        'errors' => array(),
        'messages' => array()
    );

    // Vérifier si le fichier existe
    if (!file_exists($file_path)) {
        $results['errors'][] = "Le fichier $file_path n'existe pas.";
        return $results;
    }

    // Ouvrir le fichier CSV
    $handle = fopen($file_path, 'r');
    if (!$handle) {
        $results['errors'][] = "Impossible d'ouvrir le fichier $file_path.";
        return $results;
    }

    // Lire l'en-tête
    $header = fgetcsv($handle, 0, ',', '"', '"');
    if (!$header || count($header) < 3) {
        $results['errors'][] = "Format de fichier CSV invalide. L'en-tête doit contenir au moins 3 colonnes.";
        fclose($handle);
        return $results;
    }

    // Vérifier si les colonnes requises existent
    if ($header[0] !== 'Formule' || $header[1] !== 'Prix' || $header[2] !== 'Contenu') {
        $results['errors'][] = "Format de fichier CSV invalide. Les colonnes doivent être 'Formule', 'Prix', 'Contenu'.";
        fclose($handle);
        return $results;
    }

    // Créer une formule pour la catégorie
    $formule_title = "Formule - $category";
    $existing_formule = get_page_by_title($formule_title, OBJECT, 'formule');

    $formule_data = array(
        'post_title' => $formule_title,
        'post_content' => '',
        'post_status' => 'publish',
        'post_type' => 'formule',
    );

    if ($existing_formule) {
        $formule_data['ID'] = $existing_formule->ID;
        $formule_id = wp_update_post($formule_data);
        $action = 'mise à jour';
    } else {
        $formule_id = wp_insert_post($formule_data);
        $action = 'création';
    }

    if (is_wp_error($formule_id)) {
        $results['errors'][] = "Erreur lors de la $action de la formule: " . $formule_id->get_error_message();
        fclose($handle);
        return $results;
    }

    // Initialiser la pricing list
    $pricing_list = array();

    // Lire les données
    $row_number = 1; // Commencer à 1 pour l'en-tête
    $success_count = 0;

    while (($data = fgetcsv($handle, 0, ',', '"', '"')) !== FALSE) {
        $row_number++;

        // Vérifier si la ligne a suffisamment de colonnes
        if (count($data) < 3) {
            $results['errors'][] = "Ligne $row_number: Nombre de colonnes insuffisant.";
            continue;
        }

        $title = trim($data[0]);
        $price = trim($data[1]);
        $content = trim($data[2]);

        // Vérifier si les données sont valides
        if (empty($title)) {
            $results['errors'][] = "Ligne $row_number: Le titre de la formule est vide.";
            continue;
        }

        if (!is_numeric($price)) {
            $results['errors'][] = "Ligne $row_number: Le prix n'est pas un nombre valide.";
            continue;
        }

        // Ajouter l'élément à la pricing list
        $pricing_item = array(
            'title' => $title,
            'price' => $price,
            'content' => wpautop($content) // Convertir les sauts de ligne en paragraphes
        );

        $pricing_list[] = $pricing_item;
        $success_count++;

        $results['messages'][] = "Ligne $row_number: Élément de prix \"$title\" ajouté à la formule.";
    }

    // Mettre à jour les métadonnées de la formule
    update_post_meta($formule_id, '_fcp_pricing_list', $pricing_list);

    // Ajouter la catégorie comme métadonnée
    update_post_meta($formule_id, '_fcp_category', $category);

    $results['success'] = 1; // Une formule créée/mise à jour
    $results['messages'][] = "$action réussie pour la formule \"$formule_title\" avec $success_count éléments de prix.";

    fclose($handle);
    return $results;
}

// Traitement du formulaire
$import_results = array();
$csv_dir = '';

if (isset($_POST['submit'])) {
    // Vérifier le nonce
    if (!isset($_POST['import_formules_nonce']) || !wp_verify_nonce($_POST['import_formules_nonce'], 'import_formules_action')) {
        wp_die('Vérification de sécurité échouée.');
    }

    // Récupérer le répertoire des fichiers CSV
    $csv_dir = isset($_POST['csv_directory']) ? sanitize_text_field($_POST['csv_directory']) : '';

    if (empty($csv_dir) || !is_dir($csv_dir)) {
        $import_results['errors'][] = "Le répertoire spécifié n'existe pas ou n'est pas valide.";
    } else {
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
    }
}

// Affichage de l'interface
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Importation des formules</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 20px;
            background-color: #f5f5f5;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #23282d;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }

        form {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        input[type="text"] {
            width: 100%;
            padding: 8px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        input[type="submit"] {
            background-color: #0073aa;
            color: #fff;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #005177;
        }

        .success {
            color: #46b450;
            background-color: #ecf7ed;
            border-left: 4px solid #46b450;
            padding: 10px;
            margin-bottom: 10px;
        }

        .error {
            color: #dc3232;
            background-color: #fbeaea;
            border-left: 4px solid #dc3232;
            padding: 10px;
            margin-bottom: 10px;
        }

        .info {
            color: #00a0d2;
            background-color: #e5f5fa;
            border-left: 4px solid #00a0d2;
            padding: 10px;
            margin-bottom: 10px;
        }

        pre {
            background-color: #f1f1f1;
            padding: 10px;
            border-radius: 4px;
            overflow: auto;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Importation des formules depuis des fichiers CSV</h1>

        <form method="post" action="">
            <?php wp_nonce_field('import_formules_action', 'import_formules_nonce'); ?>

            <label for="csv_directory">Répertoire des fichiers CSV :</label>
            <input type="text" id="csv_directory" name="csv_directory" value="<?php echo esc_attr($csv_dir); ?>"
                placeholder="/chemin/complet/vers/repertoire" required>

            <p>Le répertoire doit contenir des fichiers CSV au format "Formule RVK ORG - [Catégorie].csv".</p>
            <p>Chaque fichier CSV doit avoir les colonnes suivantes : Formule, Prix, Contenu.</p>

            <input type="submit" name="submit" value="Importer les formules">
        </form>

        <?php if (!empty($import_results)): ?>
            <h2>Résultats de l'importation</h2>

            <?php if (!empty($import_results['errors'])): ?>
                <div class="error">
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
                    <div class="info">
                        <h3>Fichier : <?php echo esc_html($filename); ?></h3>

                        <?php if ($result['success'] > 0): ?>
                            <p class="success">Nombre de formules importées avec succès : <?php echo esc_html($result['success']); ?>
                            </p>
                        <?php endif; ?>

                        <?php if (!empty($result['errors'])): ?>
                            <div class="error">
                                <h4>Erreurs :</h4>
                                <ul>
                                    <?php foreach ($result['errors'] as $error): ?>
                                        <li><?php echo esc_html($error); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($result['messages'])): ?>
                            <div class="success">
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
</body>

</html>