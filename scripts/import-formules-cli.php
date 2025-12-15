<?php
/**
 * Script d'importation des formules en ligne de commande
 * 
 * Usage: php import-formules-cli.php /chemin/vers/repertoire/csv
 */

// Vérifier si le script est exécuté en ligne de commande
if (php_sapi_name() !== 'cli') {
    echo "Ce script doit être exécuté en ligne de commande.\n";
    exit(1);
}

// Vérifier les arguments
if ($argc < 2) {
    echo "Usage: php import-formules-cli.php /chemin/vers/repertoire/csv\n";
    exit(1);
}

// Récupérer le chemin du répertoire CSV
$csv_dir = $argv[1];

// Vérifier si le répertoire existe
if (!is_dir($csv_dir)) {
    echo "Erreur: Le répertoire '$csv_dir' n'existe pas ou n'est pas accessible.\n";
    exit(1);
}

// Charger WordPress
$wp_load_path = dirname(dirname(dirname(dirname(__FILE__)))) . '/wp-load.php';
if (!file_exists($wp_load_path)) {
    echo "Erreur: Impossible de trouver wp-load.php. Assurez-vous que ce script est placé dans le répertoire wp-content/plugins/rvk/scripts/.\n";
    exit(1);
}

require_once($wp_load_path);

// Inclure la fonction d'importation
require_once(plugin_dir_path(__FILE__) . 'import-formules.php');

// Parcourir les fichiers CSV du répertoire
$files = glob($csv_dir . '/*.csv');

if (empty($files)) {
    echo "Aucun fichier CSV trouvé dans le répertoire '$csv_dir'.\n";
    exit(1);
}

$total_success = 0;
$total_errors = 0;

echo "Début de l'importation des formules...\n";

foreach ($files as $file) {
    $filename = basename($file);
    echo "Traitement du fichier: $filename\n";

    // Extraire la catégorie du nom de fichier (format: "Formule RVK ORG - [Catégorie].csv")
    if (preg_match('/Formule RVK ORG - (.+)\.csv/i', $filename, $matches)) {
        $category = $matches[1];
        echo "  Catégorie: $category\n";

        // Importer les formules
        $result = import_formules_from_csv($file, $category);

        // Afficher les résultats
        echo "  Formules importées avec succès: {$result['success']}\n";

        if (!empty($result['errors'])) {
            echo "  Erreurs:\n";
            foreach ($result['errors'] as $error) {
                echo "    - $error\n";
                $total_errors++;
            }
        }

        $total_success += $result['success'];
    } else {
        echo "  Format de nom de fichier non reconnu. Le format attendu est 'Formule RVK ORG - [Catégorie].csv'.\n";
        $total_errors++;
    }

    echo "\n";
}

echo "Importation terminée.\n";
echo "Total des formules importées avec succès: $total_success\n";
echo "Total des erreurs: $total_errors\n";

exit($total_errors > 0 ? 1 : 0);