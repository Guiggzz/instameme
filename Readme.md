# InstaMeme

InstaMeme est une plateforme de partage d'images inspirée d'Instagram, conçue pour partager et interagir avec des mèmes et autres contenus visuels. Le projet utilise PHP, MySQL et Tailwind CSS pour une expérience utilisateur moderne et responsive.

![InstaMeme Logo](img/icon.png)

## Table des matières

- [Prérequis](#prérequis)
- [Installation](#installation)
- [Configuration de la base de données](#configuration-de-la-base-de-données)
- [Structure du projet](#structure-du-projet)
- [Fonctionnalités](#fonctionnalités)
- [Problèmes courants](#problèmes-courants)
- [Licence](#licence)

## Prérequis

Pour faire fonctionner InstaMeme, vous aurez besoin de :

- PHP 7.4 ou plus récent
- MySQL 5.7 ou plus récent
- Serveur web (Apache, Nginx, etc.) ou Laragon
- Navigateur web moderne
- Gestionnaire de paquets Composer (optionnel)

## Installation

1. Clonez ce dépôt ou téléchargez-le sous forme de fichier ZIP et extrayez-le dans votre répertoire web (par exemple, `www` ou `htdocs`) :

```bash
git clone https://github.com/Guiggzz/instameme.git
```

2. Créez deux dossiers s'ils n'existent pas déjà :
   - Un dossier `images` à la racine du projet pour stocker les images téléchargées
   - Un dossier `img` pour les éléments graphiques de l'application (logo, icônes, etc.)

```bash
mkdir -p images img
```

3. Assurez-vous que les permissions sont correctement configurées pour permettre à l'application de télécharger des images :

```bash
chmod 755 images
```

## Configuration de la base de données

1. Créez une base de données MySQL nommée `instameme` :

```sql
CREATE DATABASE instameme;
```

2. Importez le schéma de la base de données fourni dans le fichier `instameme.sql` :

```bash
mysql -u username -p instameme < instameme.sql
```

3. Modifiez les informations de connexion à la base de données dans le fichier `db.php` pour correspondre à votre configuration :

```php
<?php
$servername = "localhost";
$username = "votre_nom_utilisateur";
$password = "votre_mot_de_passe";
$database = "instameme";

// Créer une connexion
$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("La connexion a échoué : " . $conn->connect_error);
}
?>
```

## Structure du projet

Voici la structure des fichiers principaux du projet :

```
instameme/
│
├── db.php                  # Configuration de la connexion à la base de données
├── header.php              # En-tête commun à toutes les pages
├── index.php               # Page d'accueil avec le flux de publications
├── connexion.php           # Page de connexion et d'inscription
├── deco.php                # Script de déconnexion
├── creerpos.php            # Page de création de publication
├── view_comments.php       # Page de visualisation des commentaires
├── user.php                # Page de profil utilisateur
├── search.php              # Page de recherche
├── creacom.php             # Script de création de commentaire
├── like_action.php         # Script de gestion des likes
│
├── images/                 # Dossier pour les images téléchargées
├── img/                    # Dossier pour les éléments graphiques de l'application
│   └── icon.png            # Logo InstaMeme
│
└── instameme.sql           # Schéma de la base de données
```

## Fonctionnalités

InstaMeme offre les fonctionnalités suivantes :

- **Authentification** : Inscription, connexion et déconnexion
- **Création de contenu** : Téléchargement d'images avec descriptions
- **Interactions sociales** : Likes et commentaires sur les publications
- **Profils utilisateurs** : Consultation des profils et des publications d'un utilisateur
- **Recherche** : Recherche d'utilisateurs et de contenus
- **Interface responsive** : S'adapte aux appareils mobiles et ordinateurs

## Problèmes courants

### Les images ne s'affichent pas

Vérifiez que :
1. Le dossier `images` existe et a les permissions appropriées (755)
2. Le chemin d'accès aux images est correct dans le code
3. Les images sont bien téléchargées dans le dossier `images`

### Les sessions ne fonctionnent pas

Si vous rencontrez des problèmes avec les sessions, vérifiez que :
1. PHP est configuré pour utiliser les sessions
2. Vous n'avez pas de sortie HTML avant `session_start()`
3. Les cookies sont activés dans votre navigateur

### Erreur de connexion à la base de données

Si vous ne pouvez pas vous connecter à la base de données :
1. Vérifiez les informations de connexion dans `db.php`
2. Assurez-vous que le service MySQL/MariaDB est en cours d'exécution
3. Vérifiez que l'utilisateur a les permissions nécessaires sur la base `instameme`

## Licence

Ce projet est sous licence GNU General Public License v3.0 - voir le fichier [LICENSE](LICENSE) pour plus de détails.
