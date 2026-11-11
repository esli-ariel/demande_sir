# Démarrage local — Gestion des DMA

Configuration utilisée le 2026-11-10 : PHP 8.4, Node.js 22 et MySQL installé sur le Mac. Docker n’est pas utilisé pour ce démarrage.

## Application

Dans le terminal de VS Code ouvert à la racine du dépôt :

```sh
php artisan serve --host=127.0.0.1 --port=8000
```

Ouvrir <http://127.0.0.1:8000>. Arrêter le serveur avec Ctrl+C. Si le port est déjà occupé par le serveur du projet, utiliser le serveur existant.

Les ressources ont déjà été compilées avec `npm run build`. Après une modification des styles ou scripts, relancer cette commande ou utiliser `npm run dev` dans un second terminal.

## Base de données

Le serveur MySQL existant est utilisé : hôte `127.0.0.1`, port `3306`, base `bd_demande1`. Les identifiants de connexion restent dans `.env`, ignoré par Git. Ne pas publier ce fichier.

MySQL Workbench permet de consulter cette base avec la connexion locale correspondante. Si le serveur est arrêté après un redémarrage du Mac, le relancer via les réglages MySQL de macOS avant de lancer l’application.

```sh
php artisan migrate:status
```

Au premier contrôle : toutes les migrations du dépôt sont déjà appliquées, 28 utilisateurs et 34 demandes présents. Lors du démarrage initial, aucune migration ni aucun seeder n’avaient été exécutés. À la demande du porteur du projet, `php artisan migrate --seed --no-interaction --no-ansi` a ensuite été exécuté : aucune migration en attente, `RoleSeeder` et `UserSeeder` réussis. Après exécution : 28 utilisateurs, 34 demandes et 9 rôles. Préserver les données existantes ; ne pas utiliser `migrate:fresh`, `db:wipe` ni les seeders pour simplement démarrer le projet.

Les comptes de connexion existants sont conservés. Aucun mot de passe utilisateur n’a été réinitialisé.

## Préparation déjà effectuée

- Dépendances PHP installées depuis `composer.lock`.
- Dépendances front installées avec `npm ci --no-audit --no-fund`.
- Ressources compilées avec `npm run build`.
- `.env` local configuré et clé d’application générée.

## Vérification effectuée

Le formulaire de connexion et ses fichiers CSS/JS répondent en HTTP 200. Le circuit complet après authentification reste à vérifier.

## Notifications dans l’application

Les notifications sont enregistrées dans la base MySQL existante, au moment des nouvelles actions du circuit. Aucune relance des seeders ni migration supplémentaire n’est nécessaire pour cette fonctionnalité. Aucun serveur mail ou worker de file d’attente n’est requis.

La page `/notifications` permet de filtrer les messages non lus, de les marquer comme lus et d’ouvrir la DMA associée. Le compteur dans la navbar se met à jour toutes les 30 secondes lorsque l’onglet est visible ; la liste se recharge en naviguant ou en actualisant la page. Les actions antérieures à l’ajout de cette fonctionnalité ne génèrent pas de nouvelles alertes rétroactives.
