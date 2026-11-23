# IT Shop

IT Shop est un catalogue de produits technologiques avec une interface publique et un espace d’administration. Ce projet académique de développement web met en pratique l’intégration d’une interface responsive, l’accès à MySQL, la gestion des droits et le traitement de formulaires.

## Fonctionnalités

- **Catalogue public** — sélection des produits disponibles les mieux notés à l’accueil, recherche par nom et fiches détaillées.
- **Gestionnaire** — consultation et tri du catalogue d’administration, ajout, modification et suppression des produits. Gestion du prix, du stock, du statut, de la note et de la photo.
- **Assistant** — consultation et tri du catalogue d’administration, accès aux fiches et modification de la description.
- **Contact** — validation côté serveur et envoi via SMTP avec PHPMailer lorsque le service est configuré.

## Technologies

PHP 8, MySQL, HTML, CSS, Bootstrap, Font Awesome, jQuery et Parsley.js. Composer installe PHPMailer et Valitron. Les ressources front-end servies par CDN nécessitent une connexion Internet.

## Structure

| Fichiers | Rôle |
| --- | --- |
| `index.php`, `produit.php`, `detail.php` | Accueil, catalogue et fiche publique |
| `auth.php`, `login.php`, `logout.php`, `session.php` | Authentification, sessions et jetons CSRF |
| `admin.php`, `detail_produit.php`, `ajouter_produit.php`, `modifier_produit.php`, `supprimer.php` | Administration du catalogue |
| `image_produit.php`, `img/` | Gestion des photos et ressources visuelles |
| `contact.php`, `traitement_contact.php` | Formulaire et envoi SMTP |
| `db.php`, `connect.php`, `config.example.php` | Configuration locale et connexion MySQL |
| `it_shop.sql`, `migration_mots_de_passe.sql`, `creer_admin.php` | Installation, migration et création de comptes |

## Installation locale

1. Installez PHP avec `mysqli` et `mbstring`, MySQL et Composer. Le projet a été vérifié avec PHP 8.3 et MySQL 8.3.
2. Placez-vous dans le dossier du projet et lancez `composer install`.
3. Pour une installation neuve, exécutez `mysql -u <utilisateur> -p`, puis `SOURCE chemin/vers/it_shop.sql;` dans le client. Dans phpMyAdmin, utilisez l’onglet **Importer** puis sélectionnez `it_shop.sql`. Le script crée la base `it_shop`, vérifiée à partir du schéma de la base locale, et installe 40 produits de démonstration. Il faut disposer du droit de créer la base ou demander sa création à l’administrateur MySQL.
4. Copiez `config.example.php` vers `config.local.php` et remplissez `db`. Utilisez un compte MySQL dédié avec les droits `SELECT`, `INSERT`, `UPDATE` et `DELETE` sur `it_shop`. Le fichier local est ignoré par Git.
5. Créez les comptes nécessaires. Chaque commande demande un mot de passe de 12 à 72 octets.

   ```sh
   php creer_admin.php gestion gerant
   php creer_admin.php aide assistant
   ```

6. Vérifiez que PHP peut écrire dans `img/`. Pour accepter 5 Mo par image, réglez `upload_max_filesize` sur au moins `5M` et `post_max_size` sur une valeur supérieure.
7. Lancez le serveur de développement, puis ouvrez `http://127.0.0.1:8000`. Pour l’administration, rendez-vous sur `/auth.php`.

   ```sh
   php -S 127.0.0.1:8000
   ```

### Base existante et contact

- Pour reprendre une ancienne installation, appliquez `migration_mots_de_passe.sql` après avoir sélectionné sa base. L’application convertit chaque empreinte SHA-256 lors de la première connexion réussie. Remplacez les anciens mots de passe de démonstration.
- Renseignez les paramètres `smtp` dans `config.local.php` pour activer l’envoi. Utilisez un serveur STARTTLS, une adresse expéditrice autorisée et une adresse de réception. L’adresse saisie dans le formulaire sert d’adresse de réponse.
- Les images ajoutées dans l’administration sont stockées localement. Sauvegardez le dossier `img/` avec la base si vous déplacez une installation.

## Améliorations possibles

Pagination du catalogue, limitation des tentatives de connexion et des envois du formulaire, et automatisation des tests d’intégration.

## Auteur

**Youba Ayachi**
