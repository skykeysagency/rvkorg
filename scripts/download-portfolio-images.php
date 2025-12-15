<?php
/**
 * Script pour télécharger les images du portfolio à partir d'un fichier CSV
 * 
 * Ce script lit un fichier CSV contenant des URLs d'images et les télécharge
 * dans un dossier local pour faciliter leur importation dans WordPress.
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

// Créer le dossier pour stocker les images téléchargées
$upload_dir = wp_upload_dir();
$images_dir = $upload_dir['basedir'] . '/portfolio-images';

if (!file_exists($images_dir)) {
    mkdir($images_dir, 0755, true);
}

// Ouvrir le fichier CSV
$file = fopen($csv_file, 'r');

// Compteurs pour les statistiques
$total_images = 0;
$downloaded_images = 0;
$failed_images = 0;

// Lire la première ligne pour obtenir les en-têtes
$headers = fgetcsv($file);

// Trouver l'index de la colonne "Image"
$image_index = array_search('Image', $headers);

if ($image_index === false) {
    wp_die('La colonne "Image" n\'a pas été trouvée dans le fichier CSV.');
}

// Trouver l'index de la colonne "Titre"
$title_index = array_search('Titre', $headers);

echo '<div style="font-family: Arial, sans-serif; max-width: 800px; margin: 20px auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px;">';
echo '<h1>Téléchargement des images du portfolio</h1>';
echo '<p>Dossier de destination: ' . $images_dir . '</p>';

// Lire chaque ligne du CSV
while (($row = fgetcsv($file)) !== false) {
    // Vérifier si l'URL de l'image existe
    if (isset($row[$image_index]) && !empty($row[$image_index])) {
        $image_url = trim($row[$image_index]);
        $total_images++;

        // Obtenir le nom du fichier à partir de l'URL
        $filename = basename($image_url);

        // Si un titre est disponible, l'utiliser pour le nom du fichier
        if ($title_index !== false && isset($row[$title_index]) && !empty($row[$title_index])) {
            $title = sanitize_title($row[$title_index]);
            $extension = pathinfo($filename, PATHINFO_EXTENSION);
            $filename = $title . '.' . $extension;
        }

        // Chemin complet du fichier de destination
        $dest_file = $images_dir . '/' . $filename;

        echo '<div style="margin-bottom: 10px; padding: 10px; border-bottom: 1px solid #eee;">';
        echo '<p><strong>URL de l\'image:</strong> ' . $image_url . '</p>';
        echo '<p><strong>Fichier de destination:</strong> ' . $filename . '</p>';

        // Télécharger l'image avec cURL
        $ch = curl_init($image_url);
        $fp = fopen($dest_file, 'wb');

        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);

        $success = curl_exec($ch);

        if ($success) {
            echo '<p style="color: green;"><strong>Statut:</strong> Image téléchargée avec succès</p>';
            $downloaded_images++;
        } else {
            echo '<p style="color: red;"><strong>Statut:</strong> Échec du téléchargement: ' . curl_error($ch) . '</p>';
            $failed_images++;
        }

        curl_close($ch);
        fclose($fp);

        echo '</div>';
    }
}

// Fermer le fichier CSV
fclose($file);

// Afficher les statistiques
echo '<div style="margin-top: 20px; padding: 15px; background-color: #f5f5f5; border-radius: 5px;">';
echo '<h2>Résumé</h2>';
echo '<p>Total des images trouvées: ' . $total_images . '</p>';
echo '<p style="color: green;">Images téléchargées avec succès: ' . $downloaded_images . '</p>';
echo '<p style="color: red;">Échecs de téléchargement: ' . $failed_images . '</p>';
echo '</div>';

echo '<div style="margin-top: 20px;">';
echo '<h2>Étapes suivantes</h2>';
echo '<p>Les images ont été téléchargées dans le dossier: ' . $images_dir . '</p>';
echo '<p>Vous pouvez maintenant importer ces images dans WordPress et les associer aux posts du portfolio correspondants.</p>';
echo '</div>';

echo '</div>';
?>