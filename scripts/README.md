# Scripts d'importation des formules RVK

Ce répertoire contient des scripts pour importer des formules à partir de fichiers CSV dans le site WordPress RVK.

## Structure des fichiers CSV

Les fichiers CSV doivent respecter le format suivant :

- Nom du fichier : `Formule RVK ORG - [Catégorie].csv` (ex: `Formule RVK ORG - Bar mitsva.csv`)
- Colonnes : `Formule,Prix,Contenu`
- La première ligne doit contenir les en-têtes des colonnes

Exemple de contenu CSV :

```
Formule,Prix,Contenu
Formule 60 €,60,"Traiteur
Poste d'accueil

Cocktail

Buffet dinatoire

Buffet Dessert

Pièce montée

Vin

Prix proposer pour un minimum de 100 pers en IDF, hors IDF nous contacter"
```

## Méthodes d'importation

### 1. Importation directe (recommandée)

Cette méthode est la plus simple et la plus sécurisée :

1. Connectez-vous à l'administration WordPress
2. Accédez au menu "Formules" > "Importation directe"
3. Téléchargez vos fichiers CSV via le formulaire
4. Cliquez sur "Importer les formules"

Les fichiers sont stockés directement dans le plugin, ce qui facilite la gestion.

### 2. Via les réglages d'importation

Cette méthode permet de configurer un répertoire permanent pour l'importation et de planifier des importations automatiques :

1. Connectez-vous à l'administration WordPress
2. Accédez au menu "Formules" > "Réglages d'importation"
3. Configurez le répertoire contenant les fichiers CSV
4. Optionnellement, activez l'importation automatique (quotidienne, hebdomadaire ou mensuelle)
5. Cliquez sur "Enregistrer les réglages"
6. Pour importer immédiatement, cliquez sur "Importer maintenant"

### 3. Via l'interface d'administration classique

1. Connectez-vous à l'administration WordPress
2. Accédez au menu "Formules" > "Importer des formules"
3. Indiquez le chemin complet vers le répertoire contenant les fichiers CSV
4. Cliquez sur "Importer les formules"

### 4. Via la ligne de commande

Utilisez le script `import-formules-cli.php` pour importer les formules en ligne de commande :

```bash
php wp-content/plugins/rvk/scripts/import-formules-cli.php /chemin/vers/repertoire/csv
```

## Fonctionnement

Le script d'importation effectue les opérations suivantes :

1. Parcourt tous les fichiers CSV du répertoire spécifié
2. Pour chaque fichier, extrait la catégorie à partir du nom du fichier
3. Lit les données du fichier CSV
4. Pour chaque ligne du fichier :
   - Crée ou met à jour une formule avec le titre "[Titre de la formule] - [Catégorie]"
   - Ajoute les informations de prix et de contenu à la liste des prix de la formule
   - Associe la catégorie à la formule

## Importation automatique

Si vous avez configuré l'importation automatique via les réglages, le système vérifiera périodiquement le répertoire spécifié et importera les nouvelles formules selon la fréquence choisie :

- **Quotidienne** : L'importation s'exécute une fois par jour
- **Hebdomadaire** : L'importation s'exécute une fois par semaine
- **Mensuelle** : L'importation s'exécute une fois par mois

## Résolution des problèmes

Si vous rencontrez des problèmes lors de l'importation, vérifiez les points suivants :

1. Le format des fichiers CSV est correct (encodage UTF-8, séparateur virgule)
2. Les noms des fichiers respectent le format attendu
3. Les fichiers CSV contiennent les colonnes requises
4. Les prix sont des valeurs numériques valides
5. Les permissions des fichiers permettent leur lecture

Pour plus d'informations, consultez les messages d'erreur affichés lors de l'importation.
