<?php
/**
 * Script pour copier les fichiers CSV vers le répertoire d'importation
 * 
 * Usage: php copy-csv-files.php /chemin/vers/repertoire/source
 */

// Vérifier si le script est exécuté en ligne de commande
if (php_sapi_name() !== 'cli') {
    echo "Ce script doit être exécuté en ligne de commande.\n";
    exit(1);
}

// Vérifier les arguments
if ($argc < 2) {
    echo "Usage: php copy-csv-files.php /chemin/vers/repertoire/source\n";
    exit(1);
}

// Récupérer le chemin du répertoire source
$source_dir = $argv[1];

// Vérifier si le répertoire source existe
if (!is_dir($source_dir)) {
    echo "Erreur: Le répertoire source '$source_dir' n'existe pas ou n'est pas accessible.\n";
    exit(1);
}

// Définir le répertoire de destination
$destination_dir = __DIR__ . '/import-data';

// Créer le répertoire de destination s'il n'existe pas
if (!is_dir($destination_dir)) {
    if (!mkdir($destination_dir, 0755, true)) {
        echo "Erreur: Impossible de créer le répertoire de destination '$destination_dir'.\n";
        exit(1);
    }
    echo "Répertoire de destination créé: $destination_dir\n";
}

// Parcourir les fichiers CSV du répertoire source
$files = glob($source_dir . '/*.csv');

if (empty($files)) {
    echo "Aucun fichier CSV trouvé dans le répertoire source '$source_dir'.\n";
    exit(1);
}

$copied_count = 0;
$error_count = 0;

echo "Début de la copie des fichiers CSV...\n";

foreach ($files as $file) {
    $filename = basename($file);
    $destination_file = $destination_dir . '/' . $filename;

    echo "Copie du fichier: $filename\n";

    // Vérifier si le nom du fichier correspond au format attendu
    if (preg_match('/Formule RVK ORG - (.+)\.csv/i', $filename)) {
        // Copier le fichier
        if (copy($file, $destination_file)) {
            echo "  Succès: Fichier copié vers $destination_file\n";
            $copied_count++;
        } else {
            echo "  Erreur: Impossible de copier le fichier vers $destination_file\n";
            $error_count++;
        }
    } else {
        echo "  Ignoré: Le nom du fichier ne correspond pas au format attendu (Formule RVK ORG - [Catégorie].csv)\n";
        $error_count++;
    }
}

echo "\nCopie terminée.\n";
echo "Fichiers copiés avec succès: $copied_count\n";
echo "Erreurs: $error_count\n";

if ($copied_count > 0) {
    echo "\nLes fichiers ont été copiés dans le répertoire d'importation du plugin.\n";
    echo "Vous pouvez maintenant importer les formules via l'interface d'administration :\n";
    echo "1. Connectez-vous à l'administration WordPress\n";
    echo "2. Accédez au menu \"Formules\" > \"Importation directe\"\n";
    echo "3. Cliquez sur \"Importer les formules\"\n";
}

exit($error_count > 0 ? 1 : 0);