# IPOS - PHP/MySQL POS (Bootstrap)
Contenu du projet **ipos** prêt à déployer avec XAMPP / Laragon.

**Prérequis**
- PHP 7.4+ (ou compatible)
- MySQL / MariaDB
- Serveur local (XAMPP, Laragon, etc.)

**Installation rapide**
1. Importer `sql/pos_db.sql` dans MySQL (via phpMyAdmin ou CLI).
2. Copier le dossier `public/` dans le répertoire web (ex: `C:/xampp/htdocs/ipos`).
3. Vérifier `includes/db.php` et adapter les identifiants MySQL (host, user, pass, db).
4. Accéder à `http://localhost/ipos/index.php`.
5. Admin automatique créé : téléphone `767741008` / mot de passe `admin123`.

**Structure**
- public/ : fichiers accessibles via le web
- includes/ : db.php, auth.php, header/footer
- sql/ : script SQL pour créer la base
